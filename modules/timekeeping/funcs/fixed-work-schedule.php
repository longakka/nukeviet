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

$page_title = $lang_module['schedule'];

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

// Kiểm tra lãnh đạo, trưởng phòng
if (!isset($manager_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Các phòng ban được quản lý
$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);

// Thay đổi hoạt động
if ($nv_Request->get_title('changestatus', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_int('id', 'post', 0);

    // Kiểm tra tồn tại
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE id = " . $id . " AND
    department_id IN(" . implode(',', $departments_allowed) . ")";
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }

    $status = empty($array['status']) ? 1 : 0;

    $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_work_schedule SET status = " . $status . " WHERE id = " . $id;
    $db->query($sql);

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CHANGE_STATUS_SCHEDULE', json_encode($array), $user_info['userid']);
    $nv_Cache->delMod($module_name);

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
        $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE id = " . $id . " AND
        department_id IN(" . implode(',', $departments_allowed) . ")";
        $array = $db->query($sql)->fetch();
        if (!empty($array)) {
            nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_DELETE_SCHEDULE', json_encode($array), $user_info['userid']);

            // Xóa
            $sql = "DELETE FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE id=" . $id;
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
        $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE id = " . $id . " AND
        department_id IN(" . implode(',', $departments_allowed) . ")";
        $array = $db->query($sql)->fetch();
        if (!empty($array)) {
            nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CANCEL_SCHEDULE', json_encode($array), $user_info['userid']);

            // Hủy
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_work_schedule SET deleted=1 WHERE id=" . $id;
            $db->query($sql);
        }
    }

    $nv_Cache->delMod($module_name);
    nv_htmlOutput("OK");
}

$per_page = 20;
$page = $nv_Request->get_int('page', 'get', 1);

// Phần tìm kiếm
$array_search = [];
$array_search['q'] = $nv_Request->get_title('q', 'get', '');
$array_search['from'] = $nv_Request->get_title('f', 'get', '');
$array_search['to'] = $nv_Request->get_title('t', 'get', '');

// Xử lý dữ liệu tìm kiếm
if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/', $array_search['from'], $m)) {
    $array_search['from'] = mktime(0, 0, 0, intval($m[2]), intval($m[1]), intval($m[3]));
} else {
    $array_search['from'] = 0;
}
if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/', $array_search['to'], $m)) {
    $array_search['to'] = mktime(23, 59, 59, intval($m[2]), intval($m[1]), intval($m[3]));
} else {
    $array_search['to'] = 0;
}

$db->sqlreset()->select('COUNT(*)')->from($db_config['prefix'] . '_' . $module_data . '_work_schedule');

$where = [];
if (!defined('NV_IS_GODADMIN')) {
    $where[] = 'deleted=0';
}
$where[] = 'department_id IN(' . implode(',', $departments_allowed) . ')';
if (!empty($array_search['q'])) {
    $base_url .= '&amp;q=' . urlencode($array_search['q']);
    $dblikekey = $db->dblikeescape($array_search['q']);
    $where[] = "(
        title LIKE '%" . $dblikekey . "%'
    )";
}
if (!empty($array_search['from'])) {
    $base_url .= '&amp;f=' . nv_date('d-m-Y', $array_search['from']);
    $where[] = "add_time>=" . $array_search['from'];
}
if (!empty($array_search['to'])) {
    $base_url .= '&amp;t=' . nv_date('d-m-Y', $array_search['to']);
    $where[] = "add_time<=" . $array_search['to'];
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
    $order = $array_order['field'] . ' ' . $array_order['value'];
} else {
    $order = 'id DESC';
}
$db->select('*')->order($order)->limit($per_page)->offset(($page - 1) * $per_page);
$result = $db->query($db->sql());

$page_url = $base_url;
if ($page > 1) {
    $page_url .= '&amp;page=' . $page;
}
$canonicalUrl = getCanonicalUrl($page_url);

$xtpl = new XTemplate('fixed-work-schedule.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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

$xtpl->assign('LINK_ADD_NEW', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=fixed-work-schedule-content');

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

while ($row = $result->fetch()) {
    $row['url_edit'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=fixed-work-schedule-content&amp;id=' . $row['id'];
    $row['status_render'] = $row['status'] ? ' checked="checked"' : '';
    $row['add_time'] = nv_date('d/m/Y H:i', $row['add_time']);
    $row['edit_time'] = $row['edit_time'] ? nv_date('d/m/Y H:i', $row['edit_time']) : '';

    $xtpl->assign('ROW', $row);

    // Quản trị tối cao, truy cập vào đây thì mới được xóa
    if (defined('NV_IS_GODADMIN')) {
        $xtpl->parse('main.loop.delete');
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
