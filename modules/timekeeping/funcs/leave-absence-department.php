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

$page_title = $lang_module['report_absence_department'];
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

// Kiểm tra lãnh đạo, trưởng nhóm, trưởng phòng or văn phòng
if (!isset($leader_array[$user_info['userid']]) and empty($department_array[$HR->user_info['department_id']]['view_all_absence'])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);
$departments_allowed_1 = [];
if (!empty($department_array[$HR->user_info['department_id']]['view_all_absence'])) {
    foreach ($department_array as $department) {
        $departments_allowed_1[] = $department['id'];
    }
    $departments_allowed = $departments_allowed_1;
}
$search = [];
$search['department_id'] = $nv_Request->get_absint('department_id', 'post', 0);
if ($search['department_id'] and !in_array($search['department_id'], $departments_allowed)) {
    $search['department_id'] = 0;
}

$init_from = mktime(0, 0, 0, 1, 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, 12, cal_days_in_month(CAL_GREGORIAN, 12, intval(date('Y'))), intval(date('Y')));
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
$sum_day_by_user = [];
$sum_day_approved_by_user = [];
$sum_day_denied_by_user = [];
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

        $sql = "SELECT tb1.*, tb2.department_id, tb3.username, tb3.first_name, tb3.last_name
            FROM " . $db_config['prefix'] . "_" . $module_data . "_leave_absence tb1
            INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid = tb2.userid
            INNER JOIN " . NV_USERS_GLOBALTABLE . " tb3 ON tb1.userid = tb3.userid
            WHERE ((tb1.date_leave >= " . $search['from'] . " AND tb1.date_leave <= " . $search['to'] . ")
            OR (tb1.start_leave >= " . $search['from'] . " AND tb1.start_leave <= " . $search['to'] . ")) AND
            tb3.active  = 1 AND tb2.department_id IN(" . implode(',', GetDepartmentidInParent($search['department_id'])) . ")";

        $result = $db->query($sql);
        while ($row = $result->fetch()) {
            $department_name = $department_array[$row['department_id']]['title'];
            $fullname = nv_show_name_user($row['first_name'], $row['last_name']);
            if (!isset($array[$row['userid']])) {
                $array[$row['userid']] = [
                    'total_day_approved' => 0,
                    'total_day_denied' => 0,
                    'total_day_not_confirm' => 0,
                    'total_day_all' => 0,
                    'fullname' => $fullname,
                    'department_name' => $department_name,
                    'link_list_leave_absence' => '',
                    'num_absence_year' => 0
                ];
            }
            $link = NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=leave-absence&amp;d=" . $row['department_id'] . "&u=" . $row['userid'] . '&r=' .urlencode($search['range']), true);
            $array[$row['userid']]['link_list_leave_absence'] = $link;
            $array[$row['userid']]['total_day_all'] += $row['num_day'];

            if ($row['use_absence_year'] == 1 and $row['confirm'] == 1) {
                $array[$row['userid']]['num_absence_year'] += $row['num_day'];
            }
            if ($row['confirm'] == 1) {
                $array[$row['userid']]['total_day_approved'] += $row['num_day'];
            } else if ($row['confirm'] == 2) {
                $array[$row['userid']]['total_day_denied'] += $row['num_day'];
            } else {
                $array[$row['userid']]['total_day_not_confirm'] += $row['num_day'];
            }
        }
    }
}

$xtpl = new XTemplate('leave-absence-department.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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
        $department['selected'] = ($department['id'] == $search['department_id']) ? ' selected="selected"' : '';
        $xtpl->assign('DEPARTMENT', $department);
        $xtpl->parse('main.department');
    }
}

if (!empty($array)) {
    $stt = 1;
    $total = 0;
    foreach ($array as $rows) {
        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $xtpl->assign('VIEW', $rows);
        $xtpl->parse('main.data.loop');
    }
    $xtpl->parse('main.data');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
