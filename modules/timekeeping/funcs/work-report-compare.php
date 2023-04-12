<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_MOD_TIMEKEEPING')) {
    die('Stop!!!');
}

$page_title = $lang_module['work_report_compare'];

$base_url = $page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 0,
    'title' => $page_title,
    'link' => $base_url
];

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

// Kiểm tra lãnh đạo, trưởng nhóm, trưởng phòng
if (!isset($leader_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);

$search = [];
$search['department_id'] = $nv_Request->get_absint('department_id', 'post', 0);
if ($search['department_id'] and !in_array($search['department_id'], $departments_allowed)) {
    $search['department_id'] = 0;
}

$init_from = mktime(0, 0, 0, intval(date('n')), 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, intval(date('n')), cal_days_in_month(CAL_GREGORIAN, intval(date('n')), intval(date('Y'))), intval(date('Y')));
$init_range = date('d-m-Y', $init_from) . ' - ' . date('d-m-Y', $init_to);
$search['range'] = $nv_Request->get_string('r', 'post', $init_range);
$search['from'] = 0;
$search['to'] = 0;

if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})[\s]*\-[\s]*([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/i', $search['range'], $m)) {
    $m = array_map('intval', $m);
    $search['from'] = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    $search['to'] = mktime(23, 59, 59, $m[5], $m[4], $m[6]);
    $month_from = $m[2];
    $month_to = $m[5];
    $year_from = $m[3];
    $year_to = $m[6];
    if ($search['from'] >= $search['to']) {
        $search['from'] = 0;
        $search['to'] = 0;
        $search['range'] = '';
    }
} else {
    $search['range'] = '';
}

$error = $array_user = [];
$is_submit = false;

if ($nv_Request->get_title('tokend', 'post', '') === NV_CHECK_SESSION) {
    $is_submit = true;
    if (empty($search['department_id'])) {
        $error[] = $lang_module['wrl_error1'];
    }
    if (empty($search['from'])) {
        $error[] = $lang_module['wrl_error2'];
    }
    if (empty($search['to'])) {
        $error[] = $lang_module['wrl_error3'];
    }
    if (empty($error)) {

        // Lấy tổng số công theo check in check out và tổng số công theo báo cáo
        $sql = 'SELECT tb4.userid, tb4.workday, tb4.username, tb4.first_name, tb4.last_name, tb5.work_time, tb5.over_time, tb5.accepted_time
            FROM
                (SELECT tb1.userid as userid, SUM(tb1.workday) AS workday, tb3.username, tb3.first_name, tb3.last_name
                FROM ' . $db_config['prefix'] . '_' . $module_data . '_checkio tb1
                INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_human_resources tb2 ON tb1.userid = tb2.userid
                INNER JOIN ' . NV_USERS_GLOBALTABLE . ' tb3 ON tb1.userid = tb3.userid
                WHERE tb1.checkin_real >= ' . $search['from'] . ' AND tb1.checkin_real <= ' . $search['to'] . '
                AND tb2.department_id IN(' . implode(',', GetDepartmentidInParent($search['department_id'])) . ')
                GROUP BY tb1.userid) tb4
            LEFT JOIN
                (SELECT userid_assigned, SUM(work_time) work_time, SUM(over_time) over_time, SUM(accepted_time) accepted_time
                FROM nv4_timekeeping_task WHERE time_create >= ' . $search['from'] . ' AND time_create <= ' . $search['to'] . ' GROUP BY userid_assigned) tb5
            ON tb4.userid = tb5.userid_assigned';
        $result = $db->query($sql);
        while ($row = $result->fetch()) {
            $time_report = round((($row['work_time'] + $row['over_time']) / 8), 1, PHP_ROUND_HALF_UP);
            $time_accepted = round(($row['accepted_time'] / 8), 1, PHP_ROUND_HALF_UP);
            $row['time_report'] = $time_report;
            $row['time_accepted'] = $time_accepted;
            $row['time_checkin_out'] = round($row['workday'] / 100, 1);
            $array_user[] = $row;
        }
    }
}

$xtpl = new XTemplate('work-report-compare.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('FORM_ACTION', $base_url);
$xtpl->assign('TOKEND', NV_CHECK_SESSION);
$xtpl->assign('SEARCH', $search);

// Xuất lỗi
if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

if (empty($error) and $is_submit and empty($array_user)) {
    $xtpl->parse('main.empty');
}

// Xuất phòng ban
foreach ($department_array as $department) {
    if (in_array($department['id'], $departments_allowed)) {
        $department['title'] = nv_get_full_department_title($department['id']);
        $department['selected'] = ($department['id'] == $search['department_id']) ? ' selected="selected"' : '';
        $xtpl->assign('DEPARTMENT', $department);
        $xtpl->parse('main.department');
    }
}

if (!empty($array_user)) {
    $stt = 1;
    foreach ($array_user as $rows) {
        $rows['fullname'] = nv_show_name_user($rows['first_name'], $rows['last_name'], $rows['username']);
        $rows['bg1'] = $rows['bg2'] = '';
        if (abs($rows['time_report'] - $rows['time_checkin_out']) >= 0.5) {
            $rows['bg1'] = ' bg-warning';
        }
        if (abs($rows['time_accepted'] - $rows['time_checkin_out']) >= 0.5) {
            $rows['bg2'] = ' bg-danger';
        }
        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $xtpl->assign('ROW', $rows);
        $xtpl->parse('main.data.loop');
    }
    $xtpl->parse('main.data');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
