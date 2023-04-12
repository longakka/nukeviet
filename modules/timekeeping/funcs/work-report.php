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

$page_title = $lang_module['view_report_department'];

$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 0,
    'title' => $page_title,
    'link' => $base_url
];

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

/*
 * Lấy toàn bộ danh sách nhân viên được phép xem
 * - Là chính mình
 * - Tất cả nếu phòng ban của mình được phép xem tất cả
 * - Nhân viên phòng ban mình hoặc phòng ban con của mình
 */
$sql = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id
FROM " . NV_USERS_GLOBALTABLE . " tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
WHERE tb1.active=1";

$list_department_report = !empty($HR->user_info['view_department_report']) ? " OR FIND_IN_SET(tb2.department_id, '" . $HR->user_info['view_department_report'] . "')" : "";

if (!empty($department_array[$HR->user_info['department_id']]['view_all_task'])) {
    // Toàn bộ nhân viên
} elseif (isset($leader_array[$user_info['userid']])) {
    // Xem trong phòng ban mình và phòng ban con
    $sql .= " AND (tb2.department_id IN(" . implode(',', GetDepartmentidInParent($HR->user_info['department_id'])) . ")" . $list_department_report . ")";
} else {
    // Xem chính mình
    $sql .= " AND (tb2.userid=" . $user_info['userid'] . $list_department_report . ")";
}

$sql .= " ORDER BY " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC";
$result = $db->query($sql);

// Xác định quyền sửa chấm công
$departments_allowed = [];
if (isset($leader_array[$user_info['userid']])) {
    $departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);
}

// Sửa chấm công
if (isset($_POST['setacceptedtime']) and $nv_Request->get_title('setacceptedtime', 'post', '') == NV_CHECK_SESSION and !empty($departments_allowed)) {
    $respon = [
        'message' => ''
    ];
    $id = $nv_Request->get_absint('id', 'post', 0);
    $accepted_time = $nv_Request->get_float('accepted_time', 'post', 0);
    $change_note = nv_nl2br(nv_htmlspecialchars(strip_tags($nv_Request->get_string('change_note', 'post', ''))));

    if ($accepted_time < 0) {
        $respon['message'] = $lang_module['work_report_err2'];
        nv_jsonOutput($respon);
    }
    if (empty($change_note)) {
        $respon['message'] = $lang_module['work_report_err4'];
        nv_jsonOutput($respon);
    }

    $sql = "SELECT tb1.* FROM " . $db_config['prefix'] . "_" . $module_data . "_task tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid_assigned=tb2.userid
    WHERE tb1.id=" . $id . " AND tb2.department_id IN(" . implode(',', $departments_allowed) . ")";
    $row = $db->query($sql)->fetch();
    if (empty($row)) {
        $respon['message'] = 'Data not exists!!!';
        nv_jsonOutput($respon);
    }
    if ($row['accepted_time'] == $accepted_time) {
        $respon['message'] = $lang_module['work_report_err3'];
        nv_jsonOutput($respon);
    }

    // Ghi log
    $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_task_change (
        task_id, userid, add_time, time_new, time_old, change_reason
    ) VALUES (
        :task_id, :userid, :add_time, :time_new, :time_old, :change_reason
    )";
    $array_insert = [
        'task_id' => $id,
        'userid' => $user_info['userid'],
        'add_time' => NV_CURRENTTIME,
        'time_new' => $accepted_time,
        'time_old' => $row['accepted_time'],
        'change_reason' => $change_note,
    ];
    $db->insert_id($sql, 'id', $array_insert);

    // Cập nhật lại số công
    $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task SET
        accepted_time=" . $accepted_time . ",
        set_time=" . $accepted_time . ",
        set_userid=" . $user_info['userid'] . ",
        set_date=" . NV_CURRENTTIME . "
    WHERE id=" . $id;
    $db->query($sql);

    nv_jsonOutput($respon);
}

$array_staffs = [];
$array_department_staffs = [];
$array_staff_departments = [];
while ($row = $result->fetch()) {
    $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name']);
    if (empty($array_staffs[$row['userid']])) {
        $array_staffs[$row['userid']] = $row['username'];
    } else {
        $array_staffs[$row['userid']] .= ' (' . $row['username'] . ')';
    }
    $array_department_staffs[0][$row['userid']] = $array_staffs[$row['userid']];
    $array_department_staffs[$row['department_id']][$row['userid']] = $array_staffs[$row['userid']];
    $array_staff_departments[$row['userid']] = $row['department_id'];
}

$search = [];
$search['userid'] = $nv_Request->get_absint('u', 'get', $user_info['userid']);
$search['department_id'] = $nv_Request->get_absint('d', 'get', 0);

$init_from = mktime(0, 0, 0, intval(date('n')), 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, intval(date('n')), cal_days_in_month(CAL_GREGORIAN, intval(date('n')), intval(date('Y'))), intval(date('Y')));
$init_range = date('d-m-Y', $init_from) . ' - ' . date('d-m-Y', $init_to);

$search['range'] = $nv_Request->get_string('r', 'get', $init_range);
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

// Kiểm tra dữ liệu chuẩn
if (!isset($array_department_staffs[$search['department_id']][$search['userid']])) {
    $search['userid'] = 0;
    $search['department_id'] = 0;
}

$is_search = 0;
$where = [];
if (!empty($search['userid'])) {
    $where[] = 'userid_assigned=' . $search['userid'];
    $is_search++;
}
if (!empty($search['from'])) {
    $where[] = 'time_create>=' . $search['from'];
    $is_search++;
}
if (!empty($search['to'])) {
    $where[] = 'time_create<=' . $search['to'];
    $is_search++;
}

$page = $nv_Request->get_absint('page', 'get', 1);
$per_page = 9999; // Xem như không có phân trang
$num_items = 0;

$array = [];
$taskcat_ids = $taskcats = [];

if ($is_search >= 3) {
    $base_url .= '&amp;d=' . $search['department_id'];
    $base_url .= '&amp;u=' . $search['userid'];
    $base_url .= '&amp;r=' . urlencode($search['range']);

    $db->sqlreset()
    ->select('COUNT(*)')
    ->from($db_config['prefix'] . '_' . $module_data . '_task');
    $db->where(implode(' AND ', $where));
    $sth = $db->prepare($db->sql());

    $sth->execute();
    $num_items = $sth->fetchColumn();

    betweenURLs($page, ceil($num_items/$per_page), $base_url, '&amp;page=', $prevPage, $nextPage);
    $page_url = $base_url;
    if ($page > 1) {
        $page_url .= '&amp;page=' . $page;
    }

    $db->select('*')
    ->order('time_create ASC, id DESC')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);
    $sth = $db->prepare($db->sql());

    $sth->execute();

    while ($row = $sth->fetch()) {
        $array[$row['id']] = $row;
        if (!empty($row['taskcat_id'])) {
            $taskcat_ids[$row['taskcat_id']] = $row['taskcat_id'];
        }
    }
} else {
    $page_url = $base_url;
}

$list_id_work_completed = [];
foreach ($array as $id => $item) {
    if (($item['status'] == 2) && !empty($item['assigned_task'])) {
        $list_id_work_completed[$item['assigned_task']] = $id;
    }
}

if (!empty($list_id_work_completed)) {
    $db->sqlreset()
        ->select('id, work_time, worked_time')
        ->from($db_config['prefix'] . '_' . $module_data . '_task_assign')
        ->where('id IN (' . implode(',', array_keys($list_id_work_completed)) . ')');
    $result = $db->query($db->sql());
    while ($row = $result->fetch(2)) {
        $progress_status =  0;
        $progress_status = $row['work_time'] < $row['worked_time'] ? 2 : $progress_status;
        $progress_status = $row['work_time'] > $row['worked_time'] ? 1 : $progress_status;
        $array[$list_id_work_completed[$row['id']]]['progress_status'] = $progress_status;
    }
}

$canonicalUrl = getCanonicalUrl($page_url);

// Lấy tên các dự án
if (!empty($taskcat_ids)) {
    $sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE id IN(' . implode(',', $taskcat_ids) . ')';
    $result = $db->query($sql);
    while ($row = $result->fetch()) {
        $taskcats[$row['id']] = $row['title'];
    }
}

$xtpl = new XTemplate('work-report.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
$xtpl->assign('OP', $op);
$xtpl->assign('PAGE_LINK', NV_MY_DOMAIN . nv_url_rewrite($base_url, true));

// Form tìm kiếm
if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
}

// Xếp lại thứ tự
$json_department_staffs = [];
$json_department_staffs[0] = [
    'title' => $lang_module['all'],
    'staffs' => $array_department_staffs[0]
];
foreach ($department_array as $department) {
    if (isset($array_department_staffs[$department['id']])) {
        $json_department_staffs[$department['id']] = [
            'title' => nv_get_full_department_title($department['id']),
            'staffs' => $array_department_staffs[$department['id']]
        ];
    }
}
$xtpl->assign('JSON_DATA', json_encode($json_department_staffs));
foreach ($json_department_staffs as $department_id => $department_data) {
    $xtpl->assign('DEPARTMENT', [
        'id' => $department_id,
        'title' => $department_data['title'],
        'selected' => $department_id == $search['department_id'] ? ' selected="selected"' : '',
    ]);
    $xtpl->parse('main.department');
}

// Lọc không có dữ liệu
if ($is_search >= 3 and empty($array)) {
    $xtpl->parse('main.empty');
}
if (!$is_search) {
    $xtpl->parse('main.please_choose');
}

// Phân trang
$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

if (!empty($array)) {
    $array_mod_title[] = [
        'catid' => 0,
        'title' => $array_staffs[$search['userid']],
        'link' => $base_url
    ];

    $search_from = date('d/m/Y', $search['from']);
    $search_to = date('d/m/Y', $search['to']);

    $xtpl->assign('SEARCH_FROM', $search_from);
    $xtpl->assign('SEARCH_TO', $search_to);
    $xtpl->assign('STAFF', $array_staffs[$search['userid']]);

    $page_title = $lang_module['view_report_detail'] . ' ' . $array_staffs[$search['userid']];
    $page_title .= ' ' . $lang_module['view_report_from'] . ' ' . $search_from;
    $page_title .= ' ' . $lang_module['view_report_to'] . ' ' . $search_to;

    $number = $page > 1 ? ($per_page * ($page - 1)) + 1 : 1;

    $work_time = [];
    $over_time = [];
    $accepted_time = [];

    foreach ($array as $view) {
        $view['number'] = $number++;
        $view['time_create'] = (empty($view['time_create'])) ? '' : nv_date('d/m/Y', $view['time_create']);

        $work_time[$view['taskcat_id']] = empty($work_time[$view['taskcat_id']]) ? $view['work_time'] : $work_time[$view['taskcat_id']] + $view['work_time'];
        $over_time[$view['taskcat_id']] = empty($over_time[$view['taskcat_id']]) ? $view['over_time'] : $over_time[$view['taskcat_id']] + $view['over_time'];
        $accepted_time[$view['taskcat_id']] = empty($accepted_time[$view['taskcat_id']]) ? $view['accepted_time'] : $accepted_time[$view['taskcat_id']] + $view['accepted_time'];

        $view['taskcat_id'] = isset($taskcats[$view['taskcat_id']]) ? $taskcats[$view['taskcat_id']] : 'N/A';
        $view['status'] = $global_array_status[$view['status']];
        $view['content'] = nl2br($view['content']);
        $view['description'] = nl2br($view['description']);

        $view['work_time_day'] = number_format(($view['work_time'] / 8), 2, '.', ',');
        $view['work_time_day'] = rtrim($view['work_time_day'], '0');
        $view['work_time_day'] = rtrim($view['work_time_day'], '.');

        $view['over_time_day'] = number_format(($view['over_time'] / 8), 2, '.', ',');
        $view['over_time_day'] = rtrim($view['over_time_day'], '0');
        $view['over_time_day'] = rtrim($view['over_time_day'], '.');

        $view['accepted_time_day'] = number_format(($view['accepted_time'] / 8), 2, '.', ',');
        $view['accepted_time_day'] = rtrim($view['accepted_time_day'], '0');
        $view['accepted_time_day'] = rtrim($view['accepted_time_day'], '.');

        $view['progress_color'] = '';
        $view['progress_color'] = $view['progress_status'] == 1 ? 'info' : $view['progress_color'];
        $view['progress_color'] = $view['progress_status'] == 2 ? 'danger' : $view['progress_color'];

        $xtpl->assign('VIEW', $view);

        if ($view['work_time'] > 0) {
            $xtpl->parse('main.view.loop.work_time');
        }
        if ($view['over_time'] > 0) {
            $xtpl->parse('main.view.loop.over_time');
        }
        if ($view['accepted_time'] > 0) {
            $xtpl->parse('main.view.loop.accepted_time');
        }

        // Quyền sửa chấm công
        if (isset($array_staff_departments[$view['userid_assigned']]) and in_array($array_staff_departments[$view['userid_assigned']], $departments_allowed)) {
            $xtpl->parse('main.view.loop.edit_accepted_time');
        } else {
            $xtpl->parse('main.view.loop.show_accepted_time');
        }

        $xtpl->parse('main.view.loop');
    }

    $total_worktime = 0;
    $total_overtime = 0;
    $total_acceptedtime = 0;
    foreach ($work_time as $task_catid => $value) {
        $project = [
            'name' => isset($taskcats[$task_catid]) ? $taskcats[$task_catid] : 'N/A',
            'work_time' => $value,
            'over_time' => $over_time[$task_catid],
            'accepted_time' => $accepted_time[$task_catid]
        ];

        $project['work_time_day'] = number_format(($project['work_time'] / 8), 2, '.', ',');
        $project['work_time_day'] = rtrim($project['work_time_day'], '0');
        $project['work_time_day'] = rtrim($project['work_time_day'], '.');

        $project['over_time_day'] = number_format(($project['over_time'] / 8), 2, '.', ',');
        $project['over_time_day'] = rtrim($project['over_time_day'], '0');
        $project['over_time_day'] = rtrim($project['over_time_day'], '.');

        $project['accepted_time_day'] = number_format(($project['accepted_time'] / 8), 2, '.', ',');
        $project['accepted_time_day'] = rtrim($project['accepted_time_day'], '0');
        $project['accepted_time_day'] = rtrim($project['accepted_time_day'], '.');

        $xtpl->assign('PROJECT', $project);

        if ($project['work_time'] > 0) {
            $xtpl->parse('main.view.total_loop.work_time');
        }
        if ($project['over_time'] > 0) {
            $xtpl->parse('main.view.total_loop.over_time');
        }

        $xtpl->parse('main.view.total_loop');

        $total_worktime += $value;
        $total_overtime += $over_time[$task_catid];
        $total_acceptedtime += $accepted_time[$task_catid];
    }

    $xtpl->assign('TOTAL_WORKTIME', round($total_worktime / 8, 1));
    $xtpl->assign('TOTAL_OVERTIME', round($total_overtime / 8, 1));
    $xtpl->assign('TOTAL_ACCEPTEDTIME', round($total_acceptedtime / 8, 1));

    $xtpl->parse('main.view');
}

$search['from'] = empty($search['from']) ? '' : date('d-m-Y', $search['from']);
$search['to'] = empty($search['to']) ? '' : date('d-m-Y', $search['to']);

$xtpl->assign('SEARCH', $search);

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
