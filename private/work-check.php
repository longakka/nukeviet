<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */

use Modules\timekeeping\classs\Cron;

$console_starttime = microtime(true);

define('NV_SYSTEM', true);
define('NV_IS_CONSOLE', true);

if (preg_match('/^([A-Z]{1})\:/', __DIR__)) {
    // Path có dấu : trên windows thì lấy tại thư mục cha của nó
    define('NV_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __FILE__), PATHINFO_DIRNAME) . '/..')));
    define('NV_IS_LOCAL_CONSOLE', true);
} else {
    // Trên máy chủ thì lấy thư mục public_html
    define('NV_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __FILE__), PATHINFO_DIRNAME) . '/../public_html')));
}

define('NV_CONSOLE_DIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __FILE__), PATHINFO_DIRNAME))));

require NV_CONSOLE_DIR . '/server.php';
if (defined('NV_IS_LOCAL_CONSOLE')) {
    $_SERVER['HTTP_HOST'] = 'module-timekeeping.vinades.my';
    $_SERVER['HTTPS'] = 'off';
}
require NV_ROOTDIR . '/includes/mainfile.php';
date_default_timezone_set($global_config['statistics_timezone']);
/*
 * Kiểm tra không chạy đè các tiến trình vào nhau.
 * Tiến trình này chạy xong mới chạy cái khác
 */
$check_runfile = NV_CONSOLE_DIR . '/run-work-check.log';
$checktime = file_exists($check_runfile) ? file_get_contents($check_runfile) : 0;
$checktime = intval($checktime);
if (!empty($checktime) and (NV_CURRENTTIME - $checktime) < (30 * 60)) {
    exit('There is another process running' . PHP_EOL);
}
file_put_contents($check_runfile, NV_CURRENTTIME, LOCK_EX);

$site_mods = nv_site_mods();
$module_name = 'timekeeping';

echo('Console start!' . PHP_EOL);

try {
    if (!isset($site_mods[$module_name])) {
        throw new Exception('No module ' . $module_name);
    }
    $module_info = $site_mods[$module_name];
    $module_data = $module_info['module_data'];

    require NV_CONSOLE_DIR . '/functions.php';
    $cron = new Cron();

    // Xác định các phòng ban có cảnh báo công việc
    $department_ids = [];
    foreach ($global_array_departments as $department) {
        if (!empty($department['check_work'])) {
            $department_ids[$department['id']] = $department['id'];
        }
    }
    if (empty($department_ids)) {
        throw new Exception('There are no departments that need to check the work');
    }

    $time = mktime(0, 0, 0, intval(date('n')), intval(date('j')), intval(date('Y'))) - 1;
    $month = intval(date('n', $time));
    $day = intval(date('j', $time));
    $year = intval(date('Y', $time));

    $sql = "SELECT tb4.username, tb4.email, tb4.first_name, tb4.last_name, tb4.userid, tb2.office_id, tb2.department_id
    FROM " . $db_config['prefix'] . "_" . $module_data . "_checkio tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
    INNER JOIN " . NV_USERS_GLOBALTABLE . " tb4 ON tb1.userid=tb4.userid
    WHERE
        tb1.day=" . $day . " AND tb1.month=" . $month . " AND tb1.year=" . $year . " AND
        tb2.department_id IN(" . implode(',', $department_ids) . ") AND
        NOT EXISTS(
            SELECT tb3.id FROM " . $db_config['prefix'] . "_" . $module_data . "_task tb3
            WHERE tb3.userid_assigned=tb1.userid AND
            tb3.time_create>=" . ($time - 86399) . " AND tb3.time_create<=" . $time . "
        )
    GROUP BY tb1.userid";
    $result = $db->query($sql);

    // Gửi mail thông báo cho các nhân viên đã được lọc ở trên
    $array_employees = [];
    $diary_penalize_reason = 'Không ghi báo cáo công việc ngày ' . date('d/m/Y', $time);
    while ($row = $result->fetch()) {
        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
        $array_employees[] = $row['full_name'] . ' (<@' . nv_strtolower($row['username']) . '>) ';
        // Lưu dữ liệu và CSDL
        $sql_insert = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_diary_penalize (
            userid, department_id, reason, userid_add, userid_confirm, confirm, confirm_reason, confirm_time, link, penalize_time
        ) VALUES (
            " . $row['userid'] . ", " . $row['department_id'] . ",
            '" . $diary_penalize_reason . "', 0, 0, 1, '', " . NV_CURRENTTIME . ", '', " . NV_CURRENTTIME . "
        )";
        $new_id = $db->insert_id($sql_insert, 'id');
    }
    if (!empty($array_employees)) {
        $message = "Mời " . implode(', ', $array_employees) . " chống đẩy 20 cái nộp trên group facebook công ty đồng thời bổ sung báo cáo.\n Nguyên nhân: Ngày " . date('d/m/Y', $time) . " có làm việc nhưng không ghi báo cáo!";

        echo $message . "\n";
        $message = urlencode($message);
        $cron->sendSlack($message, '#vinades');
    } else {
        echo "No employees\n";
    }
} catch (Exception $e) {
    echo print_r($e, true) . "\n";
}

$console_endtime = microtime(true);
$execution_time = getConsoleExecuteTime($console_starttime, $console_endtime);

echo('Execution time: ' . $execution_time . PHP_EOL);
echo('Console end!' . PHP_EOL);
unlink($check_runfile);
