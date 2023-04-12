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

$page_title = $lang_module['asswork'];

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

// Kiểm tra trưởng nhóm, trưởng bộ phận
if (!isset($leader_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Các phòng ban được quản lý
$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);

if ($nv_Request->isset_request('nv_staffs', 'get,post') && NV_IS_AJAX) {
    $search = $nv_Request->get_title('search', 'post');
    $array_staffs = nv_get_staffs_in_departments($departments_allowed, $search);
    array_walk($array_staffs, function(&$item, $userid) {
        $item = ['id' => $userid, 'text' => $item];
    });
    $array_staffs = array_values($array_staffs);
    nv_jsonOutput($array_staffs);
}

// Cập nhật hoàn thành hoặc hủy hoàn thành công việc
if ($nv_Request->isset_request('is_completed', 'post') &&  ($nv_Request->get_title('checksess', 'post', '') === NV_CHECK_SESSION)) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $listid = $nv_Request->get_title('listid', 'post', '');
    $listid = $listid . ',' . $id;
    $listid = array_filter(array_unique(array_map('intval', explode(',', $listid))));
    $completed = $nv_Request->get_int('completed', 'post', 0);

    // Kiểm tra nhân viên được thay đổi có thuộc quản lý của thành viên này không
    if (empty($departments_allowed)) {
        nv_htmlOutput("ERROR");
    }
    foreach ($listid as $task_assign_id) {
        $where = [];
        $where[] = 'tb1.id=' . $task_assign_id;
        $where[] = 'tb2.still_working=1';
        $where[] = 'tb2.department_id IN(' . implode(',', $departments_allowed) . ')';
        $db->sqlreset()
            ->select('COUNT(*)')
            ->from($db_config['prefix'] . "_" . $module_data . "_task_assign tb1")
            ->join("INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid = tb2.userid")
            ->where(implode(' AND ', $where));
        $num = $db->query($db->sql())->fetchColumn();
        if ($num == 0) {
            continue;
        }

        // Nếu đã đúng là nhân viên được quản lý thì cập nhật thay đổi
        $sql = 'UPDATE ' . $db_config['prefix'] . "_" . $module_data . "_task_assign SET completed = " . $completed . " WHERE id = " . $task_assign_id;
        $db->query($sql);
        nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_UPDATE_ASWORK', $sql, $user_info['userid']);
    }
    nv_htmlOutput("OK");
}

// Xóa bỏ 1 hoặc nhiều
if ($nv_Request->get_title('deleterow', 'post', '') === NV_CHECK_SESSION and defined('NV_IS_GODADMIN')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $listid = $nv_Request->get_title('listid', 'post', '');
    $listid = $listid . ',' . $id;
    $listid = array_filter(array_unique(array_map('intval', explode(',', $listid))));

    foreach ($listid as $id) {
        // Kiểm tra tồn tại
        $sql = "SELECT tb1.* FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign tb1
        INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
        WHERE tb1.id = " . $id . " AND
        tb2.department_id IN(" . implode(',', $departments_allowed) . ")";
        $array = $db->query($sql)->fetch();
        if (!empty($array)) {
            nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_DELETE_ASWORK', json_encode($array), $user_info['userid']);

            // Xóa
            $sql = "DELETE FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign WHERE id=" . $id;
            $db->query($sql);

            // Xóa log
            $sql = "DELETE FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change WHERE assign_id=" . $id;
            $db->query($sql);
        }
    }

    $nv_Cache->delMod($module_name);
    nv_htmlOutput("OK");
}

// Hủy bỏ 1 hoặc nhiều
if ($nv_Request->get_title('cancel', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $listid = $nv_Request->get_title('listid', 'post', '');
    $listid = $listid . ',' . $id;
    $listid = array_filter(array_unique(array_map('intval', explode(',', $listid))));

    foreach ($listid as $id) {
        // Kiểm tra tồn tại
        $sql = "SELECT tb1.* FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign tb1
        INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
        WHERE tb1.id = " . $id . " AND
        tb2.department_id IN(" . implode(',', $departments_allowed) . ")";
        $array = $db->query($sql)->fetch();
        if (!empty($array)) {
            nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CANCEL_ASWORK', json_encode($array), $user_info['userid']);

            // Hủy
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task_assign SET deleted=1 WHERE id=" . $id;
            $db->query($sql);
        }
    }

    $nv_Cache->delMod($module_name);
    nv_htmlOutput("OK");
}

// Load thông tin thay đổi
if ($nv_Request->get_title('loadworkchange', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_absint('id', 'post', 0);

    $sql = "SELECT tb1.* FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
    WHERE tb1.id = " . $id . " AND
    tb2.department_id IN(" . implode(',', $departments_allowed) . ")";
    $task_assign = $db->query($sql)->fetch();
    if (empty($task_assign)) {
        nv_htmlOutput('Not exists!!!');
    }

    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change
    WHERE assign_id=" . $id . " ORDER BY add_time DESC";
    $result = $db->query($sql);

    $xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('GLANG', $lang_global);

    $array = $array_userids = [];
    while ($row = $result->fetch()) {
        $array[] = $row;
        if (!empty($row['userid'])) {
            $array_userids[$row['userid']] = $row['userid'];
        }
    }

    // Lấy thành viên
    $array_users = [];
    if (!empty($array_userids)) {
        $sql = "SELECT userid, username, first_name, last_name, email
        FROM " . NV_USERS_GLOBALTABLE . " WHERE userid IN(" . implode(',', $array_userids) . ")";
        $result = $db->query($sql);

        while ($row = $result->fetch()) {
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name']);
            $row['show_name'] = $row['username'];
            if (!empty($row['full_name'])) {
                $row['show_name'] .= ' (' . $row['full_name'] . ')';
            }
            $array_users[$row['userid']] = $row;
        }
    }

    foreach ($array as $row) {
        $row['user'] = isset($array_users[$row['userid']]) ? $array_users[$row['userid']]['full_name'] : ('#' . $row['userid']);
        $row['add_time'] = nv_date('H:i d/m/Y', $row['add_time']);

        $xtpl->assign('ROW', $row);
        $xtpl->parse('change.loop');
    }

    $xtpl->parse('change');
    $contents = $xtpl->text('change');

    include NV_ROOTDIR . '/includes/header.php';
    echo $contents;
    include NV_ROOTDIR . '/includes/footer.php';
}

$per_page = 20;
$page = $nv_Request->get_int('page', 'get', 1);

// Phần tìm kiếm
$array_search = [];
$array_search['q'] = $nv_Request->get_title('q', 'get', '');

$init_from = mktime(0, 0, 0, intval(date('n')), 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, intval(date('n')), cal_days_in_month(CAL_GREGORIAN, intval(date('n')), intval(date('Y'))), intval(date('Y')));
$init_range = date('d-m-Y', $init_from) . ' - ' . date('d-m-Y', $init_to);
$array_search['range'] = $nv_Request->get_string('r', 'get', $init_range);
$array_search['from'] = 0;
$array_search['to'] = 0;

$array_search['progress_work'] = $nv_Request->get_title('progress_work', 'get', '');
$array_search['worked_finsh'] = $nv_Request->get_int('worked_finsh', 'get', 0);
$array_search['userid'] = $nv_Request->get_int('userid', 'get', 0);

// Xử lý dữ liệu tìm kiếm
if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})[\s]*\-[\s]*([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/i', $array_search['range'], $m)) {
    $m = array_map('intval', $m);

    $array_search['from'] = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    $array_search['to'] = mktime(23, 59, 59, $m[5], $m[4], $m[6]);
    if ($array_search['from'] >= $array_search['to']) {
        $array_search['from'] = 0;
        $array_search['to'] = 0;
        $array_search['range'] = '';
    }
} else {
    $array_search['range'] = '';
}

$db->sqlreset()->select('COUNT(tb1.id)')->from($db_config['prefix'] . '_' . $module_data . '_task_assign tb1');
$db->join("INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid");

$where = [];
if (!defined('NV_IS_GODADMIN')) {
    $where[] = 'tb1.deleted=0';
}
$where[] = 'tb2.department_id IN(' . implode(',', $departments_allowed) . ')';
if (!empty($array_search['q'])) {
    $base_url .= '&amp;q=' . urlencode($array_search['q']);
    $dblikekey = $db->dblikeescape($array_search['q']);
    $where[] = "(
        tb1.title LIKE '%" . $dblikekey . "%'
    )";
}
if (!empty($array_search['range'])) {
    $base_url .= '&amp;r=' . urlencode($array_search['range']);
}

if (!empty($array_search['from'])) {
    $where[] = "tb1.from_time>=" . $array_search['from'];
}
if (!empty($array_search['to'])) {
    $where[] = "tb1.to_time<=" . $array_search['to'];
}
$staff_sl = array(
    'userid' => 0,
    'username' => '',
    'first_name' => '',
    'last_name' => '',
    'full_name' => '',
);

if (!empty($array_search['userid'])) {
    $base_url .= '&amp;userid=' . $array_search['userid'];
    $where[] = "tb2.userid = " . $array_search['userid'];
    list($staff_sl['userid'], $staff_sl['username'], $staff_sl['first_name'], $staff_sl['last_name']) = $db->query('SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name FROM ' . NV_USERS_GLOBALTABLE . ' tb1 WHERE userid = ' . $array_search['userid'])->fetch(3);
    $staff_sl['full_name'] = nv_show_name_user($staff_sl['first_name'], $staff_sl['last_name'], $staff_sl['username']);
}

// Việc chưa làm: số giờ làm theo báo cáo (worked_time) bằng 0
if ($array_search['progress_work'] == 'worked_yet') {
    $base_url .= '&amp;progress_work=' . $array_search['progress_work'];
    $where[] = "tb1.worked_time = 0 AND tb1.accepted_time=0";
}
// Việc đang làm: số giờ làm theo báo cáo (worked_time) lớn hơn 0 và chưa kết thúc công việc
if ($array_search['progress_work'] == 'working') {
    $base_url .= '&amp;progress_work=' . $array_search['progress_work'];
    $where[] = "tb1.worked_time > 0 AND tb1.completed = 0";
}
// Việc làm đủ giờ: số giờ làm theo báo cáo (worked_time) nhỏ hơn hoặc bằng số giờ giao việc và việc đã kết thúc
if ($array_search['progress_work'] == 'worked_enough') {
    $base_url .= '&amp;progress_work=' . $array_search['progress_work'];
    $where[] = "tb1.worked_time = tb1.work_time  AND tb1.completed = 1";
} 
/**
 * Việc làm quá giờ: khi số giờ làm theo báo cáo (worked_time) lớn hơn số giờ giao việc
 * Không phân biệt việc đó đang làm hay đã kết thúc
 */
if ($array_search['progress_work'] == 'worked_out') {
    $base_url .= '&amp;progress_work=' . $array_search['progress_work'];;
    $where[] = "tb1.worked_time > tb1.work_time";
}

if (!empty($array_search['worked_finsh'])) {
    $base_url .= '&amp;worked_finsh=1';
    $where[] = "tb1.completed = 1";
} else if(!in_array($array_search['progress_work'], ['working', 'worked_enough', 'worked_out'])) {
    $where[] = "tb1.completed = 0";
}

// Phần sắp xếp
$array_order = [];
$array_order['field'] = $nv_Request->get_title('of', 'get', '');
$array_order['value'] = $nv_Request->get_title('ov', 'get', '');
$base_url_order = $base_url;
if ($page > 1) {
    $base_url_order .= '&amp;page=' . $page;
}

// Định nghĩa các field và các value được phép sắp xếp
$order_fields = ['title', 'add_time'];
$order_values = ['asc', 'desc'];

if (!in_array($array_order['field'], $order_fields)) {
    $array_order['field'] = '';
}
if (!in_array($array_order['value'], $order_values)) {
    $array_order['value'] = '';
}

if (!empty($where)) {
    $db->where(implode(' AND ', $where));
}

$num_items = $db->query($db->sql())->fetchColumn();

if (!empty($array_order['field']) and !empty($array_order['value'])) {
    $order = 'tb1.' . $array_order['field'] . ' ' . $array_order['value'];
} else {
    $order = 'tb1.id DESC';
}
$db->select('tb1.*')->order($order)->limit($per_page)->offset(($page - 1) * $per_page);
$result = $db->query($db->sql());

$page_url = $base_url;
if ($page > 1) {
    $page_url .= '&amp;page=' . $page;
}
$canonicalUrl = getCanonicalUrl($page_url);

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_FILE', $module_file);
$xtpl->assign('OP', $op);

$xtpl->assign('LINK_ADD_NEW', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work-content');
$xtpl->assign('checked_worked_yet', !empty($array_search['worked_yet']) ? 'checked' : '');
$xtpl->assign('checked_working', !empty($array_search['working']) ? 'checked' : '');
$xtpl->assign('checked_worked_enough', !empty($array_search['worked_enough']) ? 'checked' : '');
$xtpl->assign('checked_worked_out', !empty($array_search['worked_out']) ? 'checked' : '');
$xtpl->assign('checked_worked_finsh', !empty($array_search['worked_finsh']) ? 'checked' : '');

if (!empty($staff_sl['userid'])) {
    $xtpl->assign('STAFF', $staff_sl);
    $xtpl->parse('main.staff');
}

$arr_progress_work = [
    [
        'alias' => 'worked_yet',
        'title' => $lang_module['worked_yet']
    ], 
    [
        'alias' => 'working',
        'title' => $lang_module['working']
    ], 
    [
        'alias' => 'worked_enough',
        'title' => $lang_module['worked_enough']
    ], 
    [
        'alias' => 'worked_out',
        'title' => $lang_module['worked_out']
    ], 
];
foreach ($arr_progress_work as $progress_work) {
    $xtpl->assign('PROGRESS_WORK', $progress_work);
    $xtpl->assign('selected', $array_search['progress_work'] == $progress_work['alias'] ? 'selected' : '');
    $xtpl->parse('main.progress_work');
}

if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', $page_url);
}

// Chuyển tìm kiếm sang ngày tháng
$array_search['from'] = empty($array_search['from']) ? '' : nv_date('d-m-Y', $array_search['from']);
$array_search['to'] = empty($array_search['to']) ? '' : nv_date('d-m-Y', $array_search['to']);

$xtpl->assign('SEARCH', $array_search);

$array = $array_userids = [];
while ($row = $result->fetch()) {
    $array[$row['id']] = $row;

    if (!empty($row['userid'])) {
        $array_userids[$row['userid']] = $row['userid'];
    }
    if (!empty($row['assignee_userid'])) {
        $array_userids[$row['assignee_userid']] = $row['assignee_userid'];
    }
}

// Lấy các thành viên
$array_users = [];
if (!empty($array_userids)) {
    $sql = "SELECT userid, username, first_name, last_name, email
    FROM " . NV_USERS_GLOBALTABLE . " WHERE userid IN(" . implode(',', $array_userids) . ")";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name']);
        $row['show_name'] = $row['username'];
        if (!empty($row['full_name'])) {
            $row['show_name'] .= ' (' . $row['full_name'] . ')';
        }
        $array_users[$row['userid']] = $row;
    }
}

// Lấy số chỉnh sửa đã xảy ra
$array_changes = [];
if (!empty($array)) {
    $sql = "SELECT assign_id, COUNT(assign_id) num FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change
    WHERE assign_id IN(" . implode(',', array_keys($array)) . ") GROUP BY assign_id";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $array_changes[$row['assign_id']] = $row['num'];
    }
}

foreach ($array as $row) {
    $row['url_edit'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work-content&amp;id=' . $row['id'];
    $row['url_copy'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work-content&amp;id=' . $row['id'] . '&amp;copy=1';
    $row['url_completed'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work&amp;id=' . $row['id'] . '&amp;is_complete=1&amp;completed=1';
    $row['url_complete_cancel'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work&amp;id=' . $row['id'] . '&amp;is_complete=1&amp;completed=0';
    $row['add_time'] = nv_date('d/m/Y H:i', $row['add_time']);
    $row['edit_time'] = $row['edit_time'] ? nv_date('d/m/Y H:i', $row['edit_time']) : '';

    $row['user_name'] = isset($array_users[$row['userid']]) ? $array_users[$row['userid']]['full_name'] : ('#' . $row['userid']);
    $row['assignee_name'] = isset($array_users[$row['assignee_userid']]) ? $array_users[$row['assignee_userid']]['full_name'] : ('#' . $row['assignee_userid']);
    $row['workmess'] = sprintf($lang_module['asswork_workmess'], $row['worked_time'], $row['work_time']);

    $xtpl->assign('ROW', $row);

    // Quản trị tối cao, truy cập vào đây thì mới được xóa
    if (defined('NV_IS_GODADMIN')) {
        $xtpl->parse('main.loop.delete');
    }

    // Hiển thị nếu có sửa thời gian giao việc
    if (isset($array_changes[$row['id']])) {
        $xtpl->assign('EDIT_MESSAGE', sprintf($lang_module['asswork_change_note'], $array_changes[$row['id']]));
        $xtpl->parse('main.loop.isedit');
    }

    if (!empty($row['completed'])) {
        $xtpl->parse('main.loop.complete_cancel');
    } else {
        $xtpl->parse('main.loop.completed');
    }

    $xtpl->parse('main.loop');
}

// Quản trị tối cao, truy cập vào đây thì mới được xóa
if (defined('NV_IS_GODADMIN')) {
    $xtpl->parse('main.delete');
}

// Xuất phân trang
$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

// Xuất các phần sắp xếp
foreach ($order_fields as $field) {
    $url = $base_url_order;
    if ($array_order['field'] == $field) {
        if (empty($array_order['value'])) {
            $url .= '&amp;of=' . $field . '&amp;ov=asc';
            $icon = '<i class="fa fa-sort" aria-hidden="true"></i>';
        } elseif ($array_order['value'] == 'asc') {
            $url .= '&amp;of=' . $field . '&amp;ov=desc';
            $icon = '<i class="fa fa-sort-asc" aria-hidden="true"></i>';
        } else {
            $icon = '<i class="fa fa-sort-desc" aria-hidden="true"></i>';
        }
    } else {
        $url .= '&amp;of=' . $field . '&amp;ov=asc';
        $icon = '<i class="fa fa-sort" aria-hidden="true"></i>';
    }

    $xtpl->assign(strtoupper('URL_ORDER_' . $field), $url);
    $xtpl->assign(strtoupper('ICON_ORDER_' . $field), $icon);
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
