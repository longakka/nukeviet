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

$page_title = $lang_module['wrl_title'];

$base_url = $page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 0,
    'title' => $page_title,
    'link' => $base_url
];
$canonicalUrl = getCanonicalUrl($page_url);

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
$search['department_id'] = $nv_Request->get_typed_array('department_id', 'post', 'int', []);
$search['department_id'] = array_intersect($search['department_id'], $departments_allowed);

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
    if ($search['from'] >= $search['to']) {
        $search['from'] = 0;
        $search['to'] = 0;
        $search['range'] = '';
    }
} else {
    $search['range'] = '';
}

$error = $array = [];
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
        // SQL lấy danh sách nhân sự
        $sql = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id
        FROM " . NV_USERS_GLOBALTABLE . " tb1
        INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
        WHERE tb1.active=1 AND tb2.department_id IN(" . implode(',', $search['department_id']) . ") AND tb2.still_working=1
        ORDER BY " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC";
        $result = $db->query($sql);

        $array_userids = [];
        while ($row = $result->fetch()) {
            $array[$row['userid']] = [
                'name' => nv_show_name_user($row['first_name'], $row['last_name']),
                'link' => NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=work-report&amp;d=0&amp;u=' . $row['userid'] . '&amp;r=' . urlencode($search['range']), true),
                'time_work' => 0,
                'time_accepted' => 0
            ];
            if (empty($array[$row['userid']]['name'])) {
                $array[$row['userid']]['name'] = $row['username'];
            } else {
                $array[$row['userid']]['name'] .= ' (' . $row['username'] . ')';
            }
            $array_userids[$row['userid']] = $row['userid'];
        }

        // Lấy số công trong tháng
        if (!empty($array_userids)) {
            $sql = "SELECT SUM(work_time) work_time, SUM(over_time) over_time,
            SUM(accepted_time) accepted_time, userid_assigned
            FROM " . $db_config['prefix'] . "_" . $module_data . "_task
            WHERE time_create>=" . $search['from'] . " AND time_create<=" . $search['to'] . " AND
            userid_assigned IN(" . implode(',', $array_userids) . ")
            GROUP BY userid_assigned";
            $result = $db->query($sql);
            while ($row = $result->fetch()) {
                $array[$row['userid_assigned']]['time_work'] = round((($row['work_time'] + $row['over_time']) / 8),  2, PHP_ROUND_HALF_UP);
                $array[$row['userid_assigned']]['time_accepted'] = round(($row['accepted_time'] / 8),  2, PHP_ROUND_HALF_UP);
            }
        }
    }
}

$xtpl = new XTemplate('work-report-list.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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

if (empty($error) and $is_submit and empty($array)) {
    $xtpl->parse('main.empty');
}

// Xuất phòng ban
foreach ($department_array as $department) {
    if (in_array($department['id'], $departments_allowed)) {
        $department['title'] = nv_get_full_department_title($department['id']);
        $department['selected'] = in_array($department['id'], $search['department_id']) ? ' selected="selected"' : '';
        $xtpl->assign('DEPARTMENT', $department);
        $xtpl->parse('main.department');
    }
}

if (!empty($array)) {
    $stt = 1;
    foreach ($array as $row) {
        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $xtpl->assign('ROW', $row);
        $xtpl->parse('main.data.loop');
    }
    $xtpl->parse('main.data');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
