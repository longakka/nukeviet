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

use NukeViet\Module\timekeeping\Email\SubjectTag;

$page_title = $lang_module['view_diary_penalize'];
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

// Xóa nhật ký phạt
if ($nv_Request->get_int('delete_penalize', 'post', 0)) {
    $id_del = $nv_Request->get_int('delete_penalize', 'post', 0);
    $sql = 'DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize WHERE id = ' . $id_del;
    $statement =$db->query($sql);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

// Thêm liên kết thực hiện hình phạt
if ($nv_Request->get_int('add_link', 'post', 0)) {
    $id_del = $nv_Request->get_int('add_link', 'post', 0);
    $link = $nv_Request->get_title('link', 'post', 0);
    $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize SET link = :link, add_link_time = ' . NV_CURRENTTIME .' WHERE id = ' . $id_del;
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':link', $link, PDO::PARAM_STR);
    $statement = $stmt->execute();
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

// Xác nhận - từ chối - hủy xác nhận
if ($nv_Request->get_int('change_confirm', 'get', 0)) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $confirm_status = $nv_Request->get_int('confirm_status', 'post', 0);
    $confirm_reason = $nv_Request->get_title('confirm_reason', 'post', 0);
    $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize
        SET confirm = ' . $confirm_status . ', userid_confirm = ' . $HR->user_info['userid'] . ', confirm_reason = :confirm_reason, confirm_time = ' . NV_CURRENTTIME .' WHERE id = ' . $id;
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':confirm_reason', $confirm_reason, PDO::PARAM_STR);
    $statement = $stmt->execute();

    // Gửi email cho người thêm nhật ký phạt, người bị phạt (nếu xác nhận)
    if ($statement) {
        $sql_1 = 'SELECT tb1.userid_add, tb1.userid, tb1.reason, tb1.department_id, tb1.penalize_time, tb2.first_name, tb2.last_name, tb2.username
        FROM ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize tb1
        INNER JOIN ' . NV_USERS_GLOBALTABLE . ' tb2 ON tb1.userid = tb2.userid WHERE tb1.id = ' . $id;
        $results = $db->query($sql_1)->fetch(2);
        $user_list_id = [$results['userid_add'], $results['userid']];
        $sql = 'SELECT email, userid FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid IN (' . implode(',', $user_list_id) . ')';
        $rs = $db->query($sql);

        $user_add_info = $db->query('SELECT first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $results['userid_add'])->fetch(2);
        if ($user_add_info) {
            $full_name_user_add = nv_show_name_user($user_add_info['first_name'], $user_add_info['last_name'], $user_add_info['username']);
        } else {
            $full_name_user_add = 'User #'.$results['userid_add'];
        }

        $full_name = nv_show_name_user($results['first_name'], $results['last_name'], $results['username']);
        $leader_fullname = nv_show_name_user($HR->user_info['first_name'], $HR->user_info['last_name'], $HR->user_info['username']);
        $subject = $full_name_user_add . ' ' . $lang_module['sbj_email_penalize'] . ' ' . $full_name;
        $subject_email = SubjectTag::getDiaryPenalize($full_name, date('Y', $results['penalize_time']), $results['userid']);
        $reason = $results['reason'];
        $department = $results['department_id'];
        while ($row = $rs->fetch()) {
            $message = "<h1>$subject</h1>";
            $message .= "<p><strong>" . $lang_module['reason'] . ": </strong> $reason</p>";
            if ($row['userid'] == $results['userid']) {
                $message .= "<p><strong>". $lang_module['had_confirmed'] . ":</strong> $leader_fullname </p>";
                $message .= "<p>" . $lang_module['mess_email_1'] . " <a target=\"_blank\" href=\"" . NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=diary-penalize&amp;d=" . $department . "&u=" . $results['userid'], true) . "\">" . strtolower($lang_module['link_1']) . "</a> " . $lang_module['mess_email_3'] . ".</p>";
                if ($confirm_status == 1) {
                    nv_sendmail($global_config['smtp_username'], $row['email'], $subject_email, $message, null, null, null);
                }
            } else {
                if ($confirm_status == 1) {
                    //Xác nhận
                    $message .= "<p><strong>". $lang_module['had_confirmed'] . ":</strong> $leader_fullname </p>";
                } else if ($confirm_status == 2){
                    // Từ chối
                    $message .= "<p><strong>" . $lang_module['deny_by'] . ":</strong> $leader_fullname </p>";
                    $message .= "<p><strong>" . $lang_module['reason_deny'] . ":</strong> $confirm_reason </p>";
                } else {
                    //Hủy xác nhận
                    $message .= "<p><strong>" . $lang_module['had_not_confirmed'] . ":</strong> $leader_fullname </p>";
                }
                nv_sendmail($global_config['smtp_username'], $row['email'], $subject_email, $message, null, null, null);
            }

        }
    }
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

// Lấy danh sách nhân viên có nhật ký phạt được thêm bởi nhân viên đang đăng nhập
$_sql_0 = 'SELECT d.userid, d.department_id FROM ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize AS d
    INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_human_resources AS h
    ON d.userid_add = h.userid
    WHERE d.userid_add = ' . $user_info['userid'] . ' GROUP BY d.userid';
$rs1 = $db->query($_sql_0);
$list_user_penalize = [];
$list_department_penalize = [];
while ($row = $rs1->fetch()) {
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

$_sql_1 = 'SELECT view_diary_penalize FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources WHERE userid = ' . $user_info['userid'];
$view_diary_penalize = $db->query($_sql_1)->fetch(3);
if (!empty($view_diary_penalize)) {
    $view_diary_penalize = $view_diary_penalize[0];
} else {
    $view_diary_penalize = "";
}
$sql = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id
FROM " . NV_USERS_GLOBALTABLE . " tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
WHERE tb1.active=1";

$view_yourself = false;
$view_yourself_department = false;
$where_pen = '';
$department_arr = GetDepartmentidInParent($HR->user_info['department_id']);
$department_had_viewed = [];
if (isset($leader_array[$user_info['userid']])) {
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
} else {
    if (!empty($view_diary_penalize)) {
        // Xem các phòng ban mình được phép xem, của chính mình và của những nhân viên mình yêu cầu phạt
        $view_yourself_department = true;
        $department_had_viewed = array_map('intval', explode(',', $view_diary_penalize));
        $sql .= " AND tb2.department_id IN (" . implode(',', $department_had_viewed) . ") OR tb2.userid = ". $user_info['userid'] . "";
        if (!empty($list_user_penalize)) {
            $list_user_id = $list_user_penalize;
            $list_user_id[] = $user_info['userid'];
            $sql .= " OR tb2.userid IN (" . implode(',', $list_user_id) . ")";
        }
    } else {
        // Xem chính mình và của những nhân viên mình yêu cầu phạt
        $view_yourself = true;
        $department_had_viewed[] = $HR->user_info['department_id'];
        if (!empty($list_user_penalize)) {
            $list_user_id = $list_user_penalize;
            $list_user_id[] = $user_info['userid'];
            $where_pen = 't1.userid_add = ' . $user_info['userid'];
            $sql .= " AND tb2.userid IN (" . implode(',', $list_user_id) . ")";
        } else {
            $sql .= " AND tb2.userid=" . $user_info['userid'];
        }
    }
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
if (!empty($search['department_id'])) {
    $list_department = GetDepartmentidInParent($search['department_id']);
        $where[] = 't1.department_id IN (' . implode(',', $list_department) . ')';
    $is_search++;
} else {
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
$xtpl = new XTemplate('diary-penalize.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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
    foreach ($array as $view) {
        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));
        $view['name'] = nv_show_name_user($view['first_name'], $view['last_name']);
        $view['created_date'] = (empty($view['penalize_time'])) ? '' : nv_date('d/m/Y', $view['penalize_time']);
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
        $view['link_href'] = '';
        if ($view['link'] != '') {
            $view['link_href'] = '<p>' . $lang_module['had_handle'] . ': <a class="cls_link_penalize" href="'. $view['link'] .'" target="_blank">' . $lang_module['link_1'] . '</a></p>';
            if ($HR->user_info['userid'] == $view['userid']) {
                $xtpl->assign('LINK_INFO', $view);
                $xtpl->parse('main.view.loop.edit_deleted_link');
            }
        } else {
            $view['link_href'] = $lang_module['not_handle'];
        }
        $xtpl->assign('VIEW', $view);
        $view['show_hide_button_deny'] = "";
        $leader_full_name = '';
        if (!empty($view['userid_confirm'])) {
            $leader = $HR->getById($view['userid_confirm'], 'first_name, last_name, username');
            $leader_full_name = nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']);
        } else if ($view['confirm'] == 1) {
            $leader_full_name = $lang_module['system'];
        }
        if ($view['confirm'] == 2) {
            $view['show_hide_button_deny'] = "style='display:none'";
        }

        $had_confirm = [];
        $tooltip = '';
        $tooltip_class = '';
        $is_show_tooltip = '';
        $confirm_time = (empty($view['confirm_time'])) ? nv_date('d/m/Y H:i:s', $view['confirm_time']) : $view['penalize_time'];
        if ($view['confirm'] == 1) {
            $view['confirm_title'] = $lang_module['cancel_confirm'];
            $view['status'] = 0;
            $had_confirm['confirm_status'] = $lang_module['confirmed_ic'];
            $tooltip = $lang_module['confirm_by'] . $leader_full_name . '<br/>' .$lang_module['time_create'] . ': ' . $confirm_time;
            $tooltip_class = 'label-info';
        } else if ($view['confirm'] == 2) {
            $view['confirm_title'] = $lang_module['confirm'];
            $view['status'] = 1;
            $had_confirm['confirm_status'] = $lang_module['denied_ic'];
            $tooltip = $lang_module['denied_by'] . $leader_full_name . '<br/>' .$lang_module['time_create'] . ': ' . $confirm_time;
            $tooltip .= '<br/>' . $lang_module['reason_deny'] . ': ' . $view['confirm_reason'];
            $tooltip_class = 'label-danger';
        } else {
            $view['confirm_title'] = $lang_module['confirm'];
            $view['status'] = 1;
            $is_show_tooltip = 'style="display:none"';
            $leader_full_name = '';
        }
        $had_confirm['leader_full_name'] = $leader_full_name;
        $had_confirm['tooltip'] = $tooltip;
        $had_confirm['tooltip_class'] = $tooltip_class;
        $had_confirm['is_show_tooltip'] = $is_show_tooltip;

        // Người thêm nhật ký phạt thì hiển thị nút xóa, sửa
        if ($HR->user_info['userid'] == $view['userid_add']) {
            $xtpl->parse('main.view.loop.edit_deleted');
        }

        if (!empty($HR->user_info['leader']) and in_array($HR->user_info['department_id'], $department_arr) and in_array($view['department_id'], $department_arr)) {
            // Nếu leader thì hiển thị nút xác nhận - từ chối - hủy xác nhận
            if ($HR->user_info['userid'] == $view['userid'] and $view['link'] == '' and $view['confirm'] == 1) {
                $xtpl->parse('main.view.loop.add_link');
            }
            if ($view['confirm'] == 0) {
                $view['confirm_or_deny'] = '';
            }
            $xtpl->assign('CONFIRM', $view);
            $xtpl->parse('main.view.loop.change_confirm');
        } else {
            // Người bị phạt và chưa thêm liên kết thì hiển thị nút thêm liên kết thực hiện hình phạt
            if ($HR->user_info['userid'] == $view['userid'] and $view['link'] == '' and $view['confirm'] == 1) {
                $xtpl->parse('main.view.loop.add_link');
            }
            if ($view['confirm'] == 0) {
                $had_confirm['leader_full_name'] = $lang_module['wait_confirm'];
            }
        }
        $xtpl->assign('HAD_CONFIRM', $had_confirm);
        $xtpl->parse('main.view.loop.had_confirm');
        $xtpl->parse('main.view.loop');
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
