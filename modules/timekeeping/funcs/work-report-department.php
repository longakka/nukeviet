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
/**
 * Lấy dữ liệu các dự án theo công việc
 */
function data_project_working_department($search)
{
    global $db, $db_config, $module_data;
    $sql = "SELECT SUM(tb1.work_time) work_time, SUM(tb1.over_time) over_time,
    SUM(tb1.accepted_time) accepted_time, tb1.taskcat_id,
    tb3.userid, tb3.username, tb3.first_name, tb3.last_name, tb3.email, tb2.office_id, tb2.department_id
    FROM " . $db_config['prefix'] . "_" . $module_data . "_task tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid_assigned=tb2.userid
    INNER JOIN " . NV_USERS_GLOBALTABLE . " tb3 ON tb1.userid_assigned=tb3.userid
    WHERE tb1.time_create>=" . $search['from'] . " AND tb1.time_create<=" . $search['to'] . " AND
    tb3.active=1 AND tb2.department_id IN(" . implode(',', GetDepartmentidInParent($search['department_id'])) . ")
    GROUP BY tb1.taskcat_id, tb1.userid_assigned";

    $result = $db->query($sql);
    while ($row = $result->fetch()) {
        $username = nv_show_name_user($row['first_name'], $row['last_name']);
        if (empty($username)) {
            $username = $row['username'];
        } else {
            $username .= ' (' . $row['username'] . ')';
        }
        $time = round((($row['work_time'] + $row['over_time']) / 8), 2, PHP_ROUND_HALF_UP);
        if (!isset($array[$row['taskcat_id']])) {
            $array[$row['taskcat_id']] = [
                'total' => 0,
                'total_accepted' => 0,
                'rows' => []
            ];
        }
        $accepted_time = round(($row['accepted_time'] / 8),  2, PHP_ROUND_HALF_UP);
        $array[$row['taskcat_id']]['rows'][$row['userid']] = [
            'time' => $time,
            'accepted_time' => $accepted_time,
            'name' => $username,
        ];
        $array[$row['taskcat_id']]['total'] += $time;
        $array[$row['taskcat_id']]['total_accepted'] += $accepted_time;
    }
    return $array;
}
/**
 * Tổng hợp các dự án hạch toán vào cùng nhóm
 */
function synthetic_accounting_project_relative($id, $in_list_department = []) {
    global $global_array_project;
    $arr_syn_id = [$id];
    $project = $global_array_project[$id];
    if ($project['numsubcat'] == 0 || empty($project['subcatid'])) {
        return $arr_syn_id;
    }
    $arr_subcatid = explode(',', $project['subcatid']);
    foreach ($arr_subcatid as $cat_id) {
        if (!empty($in_list_department) && !in_array($global_array_project[$cat_id]['department'], $in_list_department)) {
            continue;
        }
        $arr_syn_child = synthetic_accounting_project_relative($cat_id, $in_list_department);
        $arr_syn_id = array_merge($arr_syn_id, $arr_syn_child);
    }
    return $arr_syn_id;
}
/***
 * Lấy dữ liệu các dự án theo hạch toán
 */
function data_project_accounting_department($search)
{
    global $db, $db_config, $module_data, $global_array_project;
    $list_project = [];
    $list_department = GetDepartmentidInParent($search['department_id']);
    $synthetic_project = [];
    foreach ($global_array_project as $key => $project) {
        if ($project['parentid'] == 0) {
            $synthetic_project[$key] = synthetic_accounting_project_relative($key, $list_department);
        }
    }
    $synthetic_project_lookup = [];
    foreach ($synthetic_project as $key => $list_id) {
        foreach ($list_id as $cat_id) {
            $synthetic_project_lookup[$cat_id] = $key;
        }
    }

    $sql = "SELECT SUM(tb1.work_time) work_time, SUM(tb1.over_time) over_time,
    SUM(tb1.accepted_time) accepted_time, tb1.taskcat_id,
    tb3.userid, tb3.username, tb3.first_name, tb3.last_name, tb3.email, tb2.office_id, tb2.department_id
    FROM " . $db_config['prefix'] . "_" . $module_data . "_task tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid_assigned=tb2.userid
    INNER JOIN " . NV_USERS_GLOBALTABLE . " tb3 ON tb1.userid_assigned=tb3.userid
    WHERE tb1.time_create>=" . $search['from'] . " AND tb1.time_create<=" . $search['to'] . " AND
    tb3.active=1 AND tb2.department_id IN(" . implode(',', GetDepartmentidInParent($search['department_id'])) . ")
    GROUP BY tb1.taskcat_id, tb1.userid_assigned";

    $data = [];
    $result = $db->query($sql);
    while ($row = $result->fetch()) {
        if (empty($synthetic_project_lookup[$row['taskcat_id']])) {
            continue;
        }
        $taskcat_id = $synthetic_project_lookup[$row['taskcat_id']];
        if (empty($data[$taskcat_id])) {
            $data[$taskcat_id] = [
                'total' => 0,
                'total_accepted' => 0,
                'rows' => []
            ];
        }

        if (empty($data[$taskcat_id]['rows'][$row['userid']])) {
            $data[$taskcat_id]['rows'][$row['userid']] = [
                'time' => 0,
                'accepted_time' => 0,
                'name' => nv_show_name_user($row['first_name'], $row['last_name'], $row['username'])
            ];
        }
        $time = round((($row['work_time'] + $row['over_time']) / 8), 2, PHP_ROUND_HALF_UP);
        $accepted_time = round(($row['accepted_time'] / 8),  2, PHP_ROUND_HALF_UP);

        $data[$taskcat_id]['rows'][$row['userid']]['time'] += $time;
        $data[$taskcat_id]['rows'][$row['userid']]['accepted_time'] += $accepted_time;

        $data[$taskcat_id]['total'] += $time;
        $data[$taskcat_id]['total_accepted'] += $accepted_time;

    }
     return $data;
}

$page_title = $lang_module['wrd_title'];

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
$search['department_id'] = $nv_Request->get_absint('department_id', 'post', 0);
if ($search['department_id'] and !in_array($search['department_id'], $departments_allowed)) {
    $search['department_id'] = 0;
}
$search['type_project'] = $nv_Request->get_absint('type_project', 'post', 1);

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
$taskcat_ids = [];

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

    $array = empty($error) && empty($search['type_project']) ? data_project_working_department($search) : $array;
    $array = empty($error) && !empty($search['type_project']) ? data_project_accounting_department($search) : $array;
}

$xtpl = new XTemplate('work-report-department.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('FORM_ACTION', $base_url);
$xtpl->assign('TOKEND', NV_CHECK_SESSION);

$xtpl->assign('SEARCH', $search);
$xtpl->assign('type_project_working_selected', empty($search['type_project']) ? 'selected="selected"' : '');
$xtpl->assign('type_project_accounting_selected', !empty($search['type_project']) ? 'selected="selected"' : '');
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
    $array_project = [];
    $result = $db->query('SELECT id, title FROM ' . $db_config['prefix'] . "_" . $module_data . '_taskcat WHERE id IN (' . implode(',', array_keys($array)) . ')');
    while ($row = $result->fetch(2)) {
        $array_project[$row['id']] = $row['title'];
    }
    $stt = 1;
    $total = 0;
    $total_accepted = 0;
    foreach ($array as $taskcat_id => $rows) {
        $first = true;
        $rowspan = sizeof($rows['rows']);

        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $xtpl->assign('ROWSPAN', $rowspan);
        $xtpl->assign('TASKCAT', $array_project[$taskcat_id]);
        $xtpl->assign('TOTAL', number_format($rows['total_accepted'], 0, ',', '.'));

        $total += $rows['total'];
        $total_accepted += $rows['total_accepted'];

        foreach ($rows['rows'] as $row) {
            // Xử lý cột đầu tiên
            if ($first) {
                $first = false;
                // Hiển thị cột ngày tháng ở dòng đầu
                if ($rowspan > 1) {
                    $xtpl->parse('main.data_working.loop_cat.loop.col_title.rowspan1');
                    $xtpl->parse('main.data_working.loop_cat.loop.col_title.rowspan2');
                    $xtpl->parse('main.data_working.loop_cat.loop.col_title.rowspan3');
                }
                $xtpl->parse('main.data_working.loop_cat.loop.col_title');
            }

            $xtpl->assign('ROW', $row);
            $xtpl->parse('main.data_working.loop_cat.loop');
        }

        $xtpl->parse('main.data_working.loop_cat');
    }

    $xtpl->assign('ALL_TOTAL', number_format($total, 0, ',', '.'));
    $xtpl->assign('ALL_TOTAL_ACCEPTED', number_format($total_accepted, 0, ',', '.'));
    $xtpl->parse('main.data_working');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
