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

$page_title = $lang_module['view_penalize_summary'];
$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
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
// trang này chỉ cho phép lãnh đạo vào xem
if (!isset($leader_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Lấy danh sách nhân viên có nhật ký phạt được thêm bởi nhân viên đang đăng nhập
$sql_string = 'SELECT d.userid, d.department_id FROM ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize AS d
    INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_human_resources AS h
    ON d.userid_add = h.userid
    WHERE d.userid_add = ' . $user_info['userid'] . ' GROUP BY d.userid';
$query_result = $db->query($sql_string);
$list_user_penalize = [];
$list_department_penalize = [];
while ($row = $query_result->fetch()) {
    $list_user_penalize[] = $row['userid'];
    $list_department_penalize[] = $row['department_id'];
}
$list_department_penalize = array_unique($list_department_penalize);

/*
 * Lấy toàn bộ danh sách nhân viên được phép xem
 * - Là chính mình
 * - Tất cả nếu phòng ban của mình được phép xem tất cả
 * - Nhân viên phòng ban mình hoặc phòng ban con của mình
 */

 // danh sách các phòng ban/department được xem
$sql_string = 'SELECT view_diary_penalize FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources WHERE userid = ' . $user_info['userid'];
$view_diary_penalize = $db->query($sql_string)->fetch(PDO::FETCH_NUM);
if (!empty($view_diary_penalize)) {
    $view_diary_penalize = $view_diary_penalize[0];
} else {
    $view_diary_penalize = "";              // về nguyên tắc thì field này ko bao giờ trống
}

// Toàn bộ nhân viên
$sql = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb2.department_id
FROM " . NV_USERS_GLOBALTABLE . " tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
WHERE tb1.active=1";        // vẫn còn làm việc

$view_yourself = false;
$where_pen = '';
$department_arr = GetDepartmentidInParent($HR->user_info['department_id']);
$department_had_viewed = [];

if (!empty($view_diary_penalize)) {
    // Xem các phòng ban mình được phép xem
    $department_view_penalize = array_map('intval', explode(',', $view_diary_penalize));
    $department_had_viewed = array_unique(array_merge($department_view_penalize, $department_arr));

    if (!empty($list_department_penalize) and !empty($list_user_penalize)) {
        $sql .= " AND tb2.department_id IN(" . implode(',', $department_had_viewed) . ")
            OR ( tb2.department_id IN(" . implode(',', $list_department_penalize) . ") AND tb2.userid IN (" . implode(',', $list_user_penalize) . "))";
    } else {
        $sql .= " AND tb2.department_id IN(" . implode(',', $department_had_viewed) . ")";
    }
} else {
    // Xem trong phòng ban mình và phòng ban con nếu là leader
    if (!empty($list_department_penalize) and !empty($list_user_penalize)) {
        $sql .= " AND tb2.department_id IN(" . implode(',', $department_arr) . ")
            OR ( tb2.department_id IN(" . implode(',', $list_department_penalize) . ") AND tb2.userid IN (" . implode(',', $list_user_penalize) . "))";
    } else {
        $sql .= " AND tb2.department_id IN(" . implode(',', $department_arr) . ")";
    }
    $department_had_viewed = $department_arr;
}
$sql .= " ORDER BY " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC";

$result = $db->query($sql);
$array_staffs = [];
$array_department_staffs = [];
$list_userid_view = [];
while ($row = $result->fetch()) {
    $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name']);
    if (empty($array_staffs[$row['userid']])) {
        $array_staffs[$row['userid']] = $row['username'];
    } else {
        $array_staffs[$row['userid']] .= ' (' . $row['username'] . ')';
    }
    $array_department_staffs[0][$row['userid']] = $array_staffs[$row['userid']];
    $array_department_staffs[$row['department_id']][$row['userid']] = $array_staffs[$row['userid']];
    $list_userid_view[] = $row['userid'];
}
$search = [];
if (isset($leader_array[$user_info['userid']])) {
    $search['userid'] = $nv_Request->get_absint('u', 'get', 0);
} else {
    $search['userid'] = $nv_Request->get_absint('u', 'get', $user_info['userid']);
}

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

// Xếp lại thứ tự
$json_department_staffs = [];
$json_department_staffs[0] = [
    'title' => $lang_module['all'],
    'staffs' => $array_department_staffs[0]
];
$list_department_view = [];
foreach ($department_array as $department) {
    if (isset($array_department_staffs[$department['id']])) {
        $json_department_staffs[$department['id']] = [
            'title' => nv_get_full_department_title($department['id']),
            'staffs' => $array_department_staffs[$department['id']]
        ];
        $list_department_view[] = $department['id'];
    }
}

// Kiểm tra dữ liệu chuẩn
if (!empty($search['userid'])) {
    if (!isset($array_department_staffs[$search['department_id']][$search['userid']])) {
        $search['userid'] = 0;
        $search['department_id'] = 0;
    }
}
$is_search = 0;
$where = [];
if (!empty($search['userid'])) {
    $where[] = 't1.userid=' . $search['userid'];
    $is_search++;
}
if (!empty($search['department_id'])) {                         // phòng được chỉ định,
    $list_department = GetDepartmentidInParent($search['department_id']);
    $where[] = 't1.department_id IN (' . implode(',', $list_department) . ')';
    $is_search++;
} else {                                                        // phòng không chỉ định, lấy tất cả phòng được phép xem
    if (!empty($list_department_view)) {
        $where[] = 't1.department_id IN (' . implode(",", $list_department_view) . ')';
        if (empty($search['userid'])) {
            $where[] = 't1.userid IN (' . implode(",", $list_userid_view) . ')';
        }
    }
}
if (!empty($search['from'])) {
    $where[] = 't1.penalize_time>=' . $search['from'];
    $is_search++;
}
if (!empty($search['to'])) {
    $where[] = 't1.penalize_time<=' . $search['to'];
    $is_search++;
}

if ($where_pen != '' && $search['userid'] != $user_info['userid']) {
    $where[] = $where_pen;
}
if ($view_yourself and $search['userid'] == 0) {
    $is_search = 0;
}
$page = $nv_Request->get_absint('page', 'get', 1);
$per_page = 99999; // 99999 - Xem như không có phân trang
$num_items = 0;
$array = [];

if (!empty($search['from']) and !empty($search['to']) and $is_search > 0) {
    $base_url .= '&amp;d=' . $search['department_id'];
    $base_url .= '&amp;u=' . $search['userid'];
    $base_url .= '&amp;r=' . urlencode($search['range']);

    $db->sqlreset()
    ->select('COUNT(*)')
    ->from($db_config['prefix'] . '_' . $module_data . '_diary_penalize AS t1')
    ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid')
    ->where(implode(' AND ', $where));
    $sth = $db->query($db->sql());
    $sth->execute();
    $num_items = $sth->fetchColumn();

    betweenURLs($page, ceil($num_items/$per_page), $base_url, '&amp;page=', $prevPage, $nextPage);
    $page_url = $base_url;
    if ($page > 1) {
        $page_url .= '&amp;page=' . $page;
    }

    $db->select('*')
    ->order('penalize_time ASC, id DESC')
    ->limit($per_page)
    ->offset(($page - 1) * $per_page);
    $sth = $db->prepare($db->sql());

    $sth->execute();

    while ($row = $sth->fetch()) {
        if (!in_array($row['department_id'], $department_had_viewed)) {
            //Nếu phòng ban có nhân viên bị phạt không nằm trong các phòng ban được xem
            if ($row['userid_add'] == $user_info['userid'] || $row['userid'] == $user_info['userid']) {
                $array[$row['id']] = $row;
            }
        } else {
            $array[$row['id']] = $row;
        }
    }
} else {
    $page_url = $base_url;
}

$xtpl = new XTemplate('penalize-summary.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
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
$xtpl->assign('MODULE_URL', nv_url_rewrite($base_url_user, true));

// Form tìm kiếm
if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
}


// thêm danh sách phòng vào html[select]
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
if ($is_search > 0 and empty($array)) {
    $xtpl->parse('main.empty');
}

// chưa search thì hiện thông báo mời search
if (!$is_search) {
    $xtpl->parse('main.please_choose');
}

// Phân trang
$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

// $array là danh sách người bị phạt tìm được
// đổ dữ liệu vào table
if (!empty($array)) {
    $search_from = date('d/m/Y', $search['from']);
    $search_to = date('d/m/Y', $search['to']);

    $xtpl->assign('SEARCH_FROM', $search_from);
    $xtpl->assign('SEARCH_TO', $search_to);
    if(isset($array_staffs[$search['userid']]))
        $xtpl->assign('STAFF', $array_staffs[$search['userid']]);
    $department_name = '';
    if (empty($search['userid']) and !empty($search['department_id'])) {
        $department_name = $lang_module['room'] . ' ' . $json_department_staffs[$search['department_id']]['title'];
    }
    $xtpl->assign('DEPARTMENT_NAME', $department_name);
    $number = $page > 1 ? ($per_page * ($page - 1)) + 1 : 1;
    $stt = 1;

    // thống kê tình trạng xử phạt
    $penalize_summary = [];
    foreach ($array as $view) {
        $_id = $view['userid'];
        if($view['confirm'] == 1) {         
            if(!isset($penalize_summary[$_id]['penalize_count']))
                $penalize_summary[$_id]['penalize_count'] = 0;                  // đã xác nhận
            $penalize_summary[$_id]['penalize_count']++;        // tăng 1 lần xử phạt
            if (empty($view['link'])) {                              // link=="" là chưa xử lý
                if(!isset($penalize_summary[$_id]['not_handled']))
                    $penalize_summary[$_id]['not_handled'] = 0;
                $penalize_summary[$_id]['not_handled']++;
            } else {
                if(!isset($penalize_summary[$_id]['handled']))
                    $penalize_summary[$_id]['handled'] = 0;
                $penalize_summary[$_id]['handled']++;
            }
        }
    }

    $penalize_shown = [];       // danh sách những người bị phạt đã hiển thị, tránh đưa 1 người ra 2 lần
    foreach ($array as $view) {
        $particular_penalize = $penalize_summary[$view['userid']];
        // hiển thị thống kê lỗi
        if (empty($particular_penalize['penalize_count'])       // không có lỗi
            || in_array($view['userid'], $penalize_shown))      // đã show rồi
            continue;                                           // bỏ qua người này
        $view['penalize_count'] = $particular_penalize['penalize_count'];

        if(isset($particular_penalize['handled'])) {
            $view['penalize_handled_count'] = $particular_penalize['handled'];
        } else {
            $view['penalize_handled_count'] = 0;
        }
        
        if(isset($particular_penalize['not_handled'])) {
            $view['penalize_not_handled_count'] = $particular_penalize['not_handled'];
        } else {
            $view['penalize_not_handled_count'] = 0;
        }

        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $view['name'] = nv_show_name_user($view['first_name'], $view['last_name']);
        $view['penalize_time'] = (empty($view['penalize_time'])) ? '' : nv_date('d/m/Y H:i:s', $view['penalize_time']);
        if ($view['userid_add'] != 0) {
            $user_add_info = $db->query('SELECT first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $view['userid_add'])->fetch(2);
            if ($user_add_info) {
                $user_add_fullname = nv_show_name_user($user_add_info['first_name'], $user_add_info['last_name'], $user_add_info['username']);
                $view['user_add_reason'] = $user_add_fullname . ' '. $lang_module['request_penalize'] . ': ' . $view['reason'];
            } else {
                $view['user_add_reason'] = 'User #' .  $view['userid_add']. ' '. $lang_module['request_penalize'] . ': ' . $view['reason'];
            }
        } else {
            $view['user_add_reason'] = $lang_module['system'] . ' '. $lang_module['request_penalize'] . ': ' . $view['reason'];
        }

        $view['department'] = '';
        if (!empty($view['department_id'])) {
            $view['department'] = $json_department_staffs[$view['department_id']]['title'];
        }

        $xtpl->assign('VIEW', $view);
        $xtpl->parse('main.view.loop');
        $penalize_shown[] = $view['userid'];        // người này đã show
    }
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