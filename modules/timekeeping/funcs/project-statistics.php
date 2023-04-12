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

// Kiểm tra có phải lãnh đạo hay không
if (!in_array($user_info['userid'], array_keys($leader_array))) {
    $page_title = $lang_module['info'];
    $url = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=users&amp;' . NV_OP_VARIABLE . '=login&amp;nv_redirect=' . nv_redirect_encrypt($client_info['selfurl']), true);
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission'], 'info', $url, $lang_module['info_redirect_click'], 5);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

$project_id = $nv_Request->get_absint('id', 'get', 0);
$in_department = $department_array[$leader_array[$user_info['userid']]['department_id']];

$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat
WHERE id=' . $project_id . ' AND department=' . $in_department['id'];
$project = $db->query($sql)->fetch();

if (empty($project)) {
    nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content'], 404);
}
$page_title = $lang_module['pstat_title'] . ' ' . $project['title'];
$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $project_id;

$xtpl = new XTemplate('project-statistics.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('TEMPLATE', $module_info['template']);
$xtpl->assign('MODULE_FILE', $module_file);
$xtpl->assign('PROJECT', $project);
$xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);

if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
    $xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
    $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
    $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
    $xtpl->assign('MODULE_NAME', $module_name);
    $xtpl->assign('OP', $op);
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
}

$search = [];
$search['range'] = $nv_Request->get_string('r', 'get', '');
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
if (empty($search['range'])) {
    $search['range'] = '01-01-2010 - ' . nv_date('d-m-Y');
}
$base_url .= '&amp;r=' . urlencode($search['range']);

$xtpl->assign('SEARCH', $search);

$where = [];
$where[] = 'taskcat_id=' . $project_id;
if ($search['from']) {
    $where[] = 'time_create>=' . $search['from'];
}
if ($search['to']) {
    $where[] = 'time_create<=' . $search['to'];
}

// Tính số công theo nhân sự
$sql = "SELECT userid_assigned, SUM(work_time) work_time, SUM(over_time) over_time
FROM " . $db_config['prefix'] . "_" . $module_data . "_task
WHERE " . implode(' AND ', $where) . " GROUP BY userid_assigned";

$result = $db->query($sql);

$array = $array_userids = [];
$array_total = [
    'work_time' => 0,
    'over_time' => 0,
    'all' => 0,
];
while ($row = $result->fetch()) {
    $array[$row['userid_assigned']] = $row;
    $array_userids[$row['userid_assigned']] = $row['userid_assigned'];
    $array_total['all'] += $row['work_time'];
    $array_total['all'] += $row['over_time'];
    $array_total['work_time'] += $row['work_time'];
    $array_total['over_time'] += $row['over_time'];
}

$array_users = [];
if (!empty($array_userids)) {
    $sql = "SELECT userid, username, first_name, last_name, email
    FROM " . NV_USERS_GLOBALTABLE . " WHERE userid IN(" . implode(',', $array_userids) . ")";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name']);
        if (!empty($row['full_name'])) {
            $row['show_name'] = $row['full_name'] . ' (' . $row['username'] . ')';
        } else {
            $row['show_name'] = $row['username'];
        }
        $array_users[$row['userid']] = $row;
    }
}

$stt = 1;
foreach ($array as $row) {
    $row['work_time_day'] = number_format(($row['work_time'] / 8), 2, '.', ',');
    $row['work_time_day'] = rtrim($row['work_time_day'], '0');
    $row['work_time_day'] = rtrim($row['work_time_day'], '.');

    $row['over_time_day'] = number_format(($row['over_time'] / 8), 2, '.', ',');
    $row['over_time_day'] = rtrim($row['over_time_day'], '0');
    $row['over_time_day'] = rtrim($row['over_time_day'], '.');

    $row['staff'] = isset($array_users[$row['userid_assigned']]) ? $array_users[$row['userid_assigned']]['show_name'] : ('#' . $row['userid_assigned']);

    $xtpl->assign('STT', $stt++);
    $xtpl->assign('ROW', $row);

    if ($row['work_time'] > 0) {
        $xtpl->parse('main.loop.work_time');
    }
    if ($row['over_time'] > 0) {
        $xtpl->parse('main.loop.over_time');
    }

    $xtpl->parse('main.loop');
}

$array_total['work_time_day'] = number_format(($array_total['work_time'] / 8), 2, '.', ',');
$array_total['work_time_day'] = rtrim($array_total['work_time_day'], '0');
$array_total['work_time_day'] = rtrim($array_total['work_time_day'], '.');

$array_total['over_time_day'] = number_format(($array_total['over_time'] / 8), 2, '.', ',');
$array_total['over_time_day'] = rtrim($array_total['over_time_day'], '0');
$array_total['over_time_day'] = rtrim($array_total['over_time_day'], '.');

$array_total['all_day'] = number_format(($array_total['all'] / 8), 2, '.', ',');
$array_total['all_day'] = rtrim($array_total['all_day'], '0');
$array_total['all_day'] = rtrim($array_total['all_day'], '.');

$xtpl->assign('TOTAL', $array_total);

if ($array_total['work_time'] > 0) {
    $xtpl->parse('main.work_time');
}
if ($array_total['over_time'] > 0) {
    $xtpl->parse('main.over_time');
}
if ($array_total['all'] > 0) {
    $xtpl->parse('main.all');
}

$array_mod_title[] = [
    'catid' => 1,
    'title' => $lang_module['project_alias'],
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=project'
];
$array_mod_title[] = [
    'catid' => 2,
    'title' => $page_title,
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $project_id
];
$page_url = $base_url;
$canonicalUrl = getCanonicalUrl($page_url);

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
