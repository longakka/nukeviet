<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */

if (!defined('NV_IS_MOD_TIMEKEEPING')) {
    die('Stop!!!');
}

use NukeViet\Module\timekeeping\Email\SubjectTag;

$page_title = $lang_module['view_list_leave_absence'];
$base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
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

// Xóa 1 đơn xin nghỉ phép
if ($nv_Request->get_int('delete_absence', 'post', 0)) {
    $id_del = $nv_Request->get_int('delete_absence', 'post', 0);
    if (!empty($id_del)) {
        $detail = $db->query('SELECT id FROM ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence
        WHERE id = ' . $id_del . ' AND userid = '. $user_info['userid'] . ' AND confirm=0')->fetch(3);
        if (empty($detail)) {
            nv_htmlOutput($lang_module['error_not_found']);
        } else {
            // lấy thông tin về ngày phép trước khi xóa trên DB
            $db->sqlreset()
            ->select('t1.*, t2.email, t2.first_name, t2.last_name, t2.username')
            ->from($db_config['prefix'] . '_' . $module_data . '_leave_absence t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid')
            ->where('t1.id = ' . $id_del);
            $employee = $db->query($db->sql())->fetch();
            $date_leave = (empty($employee['date_leave'])) ? '' : nv_date('d/m/Y', $employee['date_leave']);
            $start_leave = (empty($employee['start_leave'])) ? '' : nv_date('d/m/Y', $employee['start_leave']);
            $end_leave = (empty($employee['end_leave'])) ? '' : nv_date('d/m/Y', $employee['end_leave']);
            if ($employee['type_leave'] == '1') {
                // Nghỉ cả ngày
                $title_date_str = '';
                $title_date_end = '';
                if ($employee['half_day_start'] == 1) {
                    $title_date_str = $lang_module['morning'] . ' ';
                } else if ($employee['half_day_start'] == 2) {
                    $title_date_str = $lang_module['afternoon'] . ' ';
                }
                if ($employee['half_day_end'] == 1) {
                    $title_date_end = $lang_module['morning'] . ' ';
                } else if ($employee['half_day_end'] == 2) {
                    $title_date_end = $lang_module['afternoon'] . ' ';
                }
                $time = $title_date_str . $start_leave . ' - ' . $title_date_end . $end_leave;
                $year = date('Y', $employee['start_leave']);
            } else {
                // Nghỉ nửa ngày
                $time = $date_leave;
                $year = date('Y', $employee['date_leave']);
            }
            $total_day = $employee['num_day'];
            $reason = $employee['reason_leave'];

            // xóa thông tin nghỉ phép trên leave_absence
            $sql = 'DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence WHERE id = ' . $id_del;
            $statement =$db->query($sql);

            // xóa nghỉ phép thành công
            // gửi đi cái email
            $list_manager = $HR->getManager();
            if ($statement and !empty($list_manager)) {
                // Lấy quản lý cấp gần nhất
                $managers = array_shift($list_manager);
                $name_manager = $to = [];
                foreach ($managers as $manager) {
                    $name_manager[] = $manager['full_name'] . ' (' . $manager['office_title'] . ')';
                    $to[] = $manager['email'];
                }
                $name_manager = implode(', ', $name_manager);
                $to = array_values(array_unique($to));

                $subject = SubjectTag::getLeaveAbsence($HR->user_info['full_name'], $year, $HR->user_info['userid']);
                $message = "<h1>$subject</h1>";
                $message .= "<p><strong>" . $lang_module['email_workout_ct1'] . ": </strong> " . $name_manager . "</p>";
                $message .= "<p><strong>" . $lang_module['reason'] . ": </strong> $reason</p>";
                $message .= "<p><strong>" . $lang_module['time_leave'] . ": </strong> $time</p>";
                $message .= "<p><strong>" . $lang_module['total_day'] . ": </strong> $total_day</p>";
                $message .= "<p><strong>" . $lang_module['email_absence_cancelled'] . "</strong> " . $lang_module['email_cancelled_indicator'] . " " . nv_date('h:i:s d/m/Y', NV_CURRENTTIME) . "</p>";

                $from = [
                    $global_config['site_name'],
                    $global_config['site_email']
                ];
                nv_sendmail($from, $to, $subject, $message, null, null, null);
            }

            nv_htmlOutput($statement ? "OK" : 'ERROR');
        }
    } else {
        nv_htmlOutput($lang_module['error_not_found']);
    }
}

// Xác nhận - từ chối - hủy xác nhận
if ($nv_Request->get_int('change_confirm', 'get', 0)) {
    $id = $nv_Request->get_int('id', 'post', 0);
    if (!empty($id)) {
        $detail = $db->query('SELECT tb1.id, tb1.userid, tb2.department_id, tb2.office_id, tb3.leader
        FROM ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence tb1
        INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_human_resources tb2 ON tb1.userid=tb2.userid
        INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_office tb3 ON tb3.id = tb2.office_id
        WHERE tb1.id = ' . $id)->fetch(2);

        if (empty($detail)) {
            nv_htmlOutput($lang_module['error_not_found']);
        } else {
            $allow_confirm = nvCheckAllowLeaveApproval($HR->user_info, $detail);
            if ($allow_confirm) {
                $start_of_the_year = mktime(0, 0, 0, 1, 1, date('Y'));
                $end_of_the_year = mktime(0, 0, 0, 1, 1, date('Y') + 1) - 1;
                $result = $db->query('SELECT type_leave, date_leave, start_leave, end_leave, num_day
                                      FROM ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence 
                                      WHERE confirm = 1 AND use_absence_year = 1 AND userid = ' . $detail['userid']);
                $num_absence_year = 0;
                while ($row = $result->fetch()) {
                    // Loại bỏ những đợt nghỉ của năm ngoái hoặc năm sau
                    if ($row['type_leave'] == 1) {
                        if (($row['start_leave'] < $end_of_the_year) && ($row['end_leave'] > $start_of_the_year)) {
                            $num_absence_year += $row['num_day'];
                        }
                    } else if ($row['type_leave'] == 0.5) {
                        if (($row['date_leave'] > $start_of_the_year) && ($row['date_leave'] < $end_of_the_year)) {
                            $num_absence_year += 0.5;
                        }
                    }
                }

                $confirm_status = $nv_Request->get_int('confirm_status', 'post', 0);
                $confirm_reason = $nv_Request->get_title('confirm_reason', 'post', 0);

                $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence SET
                    confirm = ' . $confirm_status . ',
                    userid_approved = ' . $HR->user_info['userid'] . ',
                    deny_reason = :deny_reason,
                    approved_time = ' . NV_CURRENTTIME . '
                WHERE id = ' . $id;
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':deny_reason', $confirm_reason, PDO::PARAM_STR);
                $statement = $stmt->execute();

                // Gửi email cho nhân viên xin nghỉ phép và văn phòng
                if ($statement) {
                    $email_vanphong = '';
                    if (!empty($module_config[$module_name]['email_vp'])) {
                        $email_vanphong = $module_config[$module_name]['email_vp'];
                    }

                    $db->sqlreset()
                        ->select('t1.*, t2.email, t2.first_name, t2.last_name, t2.username')
                        ->from($db_config['prefix'] . '_' . $module_data . '_leave_absence t1')
                        ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid')
                        ->where('t1.id = ' . $id);
                    $employee = $db->query($db->sql())
                        ->fetch(PDO::FETCH_ASSOC);

                    $date_leave = (empty($employee['date_leave'])) ? '' : nv_date('d/m/Y', $employee['date_leave']);
                    $start_leave = (empty($employee['start_leave'])) ? '' : nv_date('d/m/Y', $employee['start_leave']);
                    $end_leave = (empty($employee['end_leave'])) ? '' : nv_date('d/m/Y', $employee['end_leave']);

                    if ($employee['type_leave'] == '1') {
                        $title_date_str = '';
                        $title_date_end = '';
                        if ($employee['half_day_start'] == 1) {
                            $title_date_str = $lang_module['morning'] . ' ';
                        } else if ($employee['half_day_start'] == 2) {
                            $title_date_str = $lang_module['afternoon'] . ' ';
                        }
                        if ($employee['half_day_end'] == 1) {
                            $title_date_end = $lang_module['morning'] . ' ';
                        } else if ($employee['half_day_end'] == 2) {
                            $title_date_end = $lang_module['afternoon'] . ' ';
                        }
                        $time = $title_date_str . $start_leave . ' - ' . $title_date_end . $end_leave;
                        $year = date('Y', $employee['start_leave']);
                    } else {
                        $time = $date_leave;
                        $year = date('Y', $employee['date_leave']);
                    }

                    $total_day = $employee['num_day'];
                    if ($confirm_status == 0) {
                        $num_absence_year = $num_absence_year - $total_day;
                        if ($num_absence_year < 0) {
                            $num_absence_year = 0;
                        }
                    }

                    $reason = $employee['reason_leave'];
                    $employee_fullname = nv_show_name_user($employee['first_name'], $employee['last_name'], $employee['username']);
                    $leader_fullname = nv_show_name_user($HR->user_info['first_name'], $HR->user_info['last_name'], $HR->user_info['username']);
                    $email = $employee['email'];
                    $subject = SubjectTag::getLeaveAbsence($employee_fullname, $year, $HR->user_info['userid']);

                    $message = "<h1>$subject</h1>";
                    $message .= "<p><strong>" . $lang_module['reason'] . ": </strong> $reason</p>";
                    $message .= "<p><strong>" . $lang_module['time_leave'] . ": </strong> $time</p>";
                    $message .= "<p><strong>" . $lang_module['total_day'] . ": </strong> " . $total_day . " " . $lang_module['date'] . "</p>";
                    $message .= "<p><strong>" . $lang_module['use_absence_year'] . ": </strong> " . (($employee['use_absence_year'] == 1) ? $lang_module['yes'] : $lang_module['no']) . "</p>";
                    $message .= "<p><strong>" . $lang_module['num_absence_year'] . ": </strong> " . $num_absence_year . " " . $lang_module['date'] . "</p>";

                    $from = [
                        $global_config['site_name'],
                        $global_config['site_email']
                    ];
                    if ($confirm_status == 2) {
                        $deny_reason = $employee['deny_reason'];
                        $message .= "<p><strong>" . $lang_module['deny_by'] . ":</strong> $leader_fullname </p>";
                        $message .= "<p><strong>" . $lang_module['reason_deny'] . ":</strong> $deny_reason </p>";
                        nv_sendmail($from, $email, $subject, $message, null, null, null);
                    } else {
                        $message .= "<p><strong>" . ($confirm_status ? $lang_module['had_confirmed'] : $lang_module['had_not_confirmed']) . ":</strong> $leader_fullname </p>";
                        nv_sendmail($from, $email, $subject, $message, null, null, null, $email_vanphong);
                    }
                }
                nv_htmlOutput($statement ? "OK" : 'ERROR');
            } else {
                nv_htmlOutput($lang_module['error_not_permission_confirm']);
            }
        }
    } else {
        nv_htmlOutput($lang_module['error_not_found']);
    }

}

/*
 * Xác định các phòng ban và nhân viên thuộc phòng ban được xem
 * - Xem chính mình
 * - Trưởng phòng xem cấp dưới: Tính cả phòng ban con
 * - Người được cấu hình xem bộ phận khác (tính độc lập) $HR->user_info['view_leave_absence']
 */
$array_department_staffs = [];
$array_staffs = [];
$department_ids = [];

if (isset($leader_array[$user_info['userid']])) {
    $department_ids = GetDepartmentidInParent($HR->user_info['department_id']);
}
$view_leave_absence = array_filter(array_unique(array_map('intval', explode(',', $HR->user_info['view_leave_absence']))));
if (!empty($view_leave_absence)) {
    $department_ids = array_merge($department_ids, $view_leave_absence);
}

$db->sqlreset()
->select('t1.userid, t1.department_id, t2.first_name, t2.last_name, t2.username, t2.email')
->from($db_config['prefix'] . '_' . $module_data . '_human_resources t1')
->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid');

$where = [];
if (!empty($department_ids)) {
    $where[] = 't1.department_id IN(' . implode(',', $department_ids) . ')';
}
$where[] = 't1.userid=' . $user_info['userid'];
$db->where(implode(' OR ', $where));
$result = $db->query($db->sql());
while ($row = $result->fetch(2)) {
    $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
    $array_department_staffs[0][$row['userid']] = $array_staffs[$row['userid']];
    $array_department_staffs[$row['department_id']][$row['userid']] = $array_staffs[$row['userid']];
}

// Xếp lại thứ tự
$json_department_staffs = [];
$json_department_staffs[0] = [
    'title' => $lang_module['all'],
    'staffs' => $array_department_staffs[0] ?? []
];

foreach ($department_array as $department) {
    if (in_array($department['id'], $department_ids) or $department['id'] == $HR->user_info['department_id']) {
        $json_department_staffs[$department['id']] = [
            'title' => nv_get_full_department_title($department['id']),
            'staffs' => $array_department_staffs[$department['id']] ?? []
        ];
    }
}

$search = [];
$init_from = mktime(0, 0, 0, intval(date('n')), 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, intval(date('n')), cal_days_in_month(CAL_GREGORIAN, intval(date('n')), intval(date('Y'))), intval(date('Y')));
$init_range = date('d-m-Y', $init_from) . ' - ' . date('d-m-Y', $init_to);

$search['userid'] = $nv_Request->get_absint('u', 'get', !empty($department_ids) ? 0 : $user_info['userid']);
$search['department_id'] = $nv_Request->get_absint('d', 'get', $HR->user_info['department_id']);
$search['range'] = $nv_Request->get_string('r', 'get', $init_range);

// Nếu không có to-date thì tự thêm chính start-date vào $search['range']
if (sizeof(preg_split('/-/', $search['range']), 1) < 6) {
    $search['range'] .= "-" . $search['range'];
}

$search['from'] = 0;
$search['to'] = 0;
$from = $to = '';
if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})[\s]*\-[\s]*([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/i', $search['range'], $m)) {
    $m = array_map('intval', $m);
    $search['from'] = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    $search['to'] = mktime(23, 59, 59, $m[5], $m[4], $m[6]);
    $from = date('d-m-Y', $search['from']);
    $to = date('d-m-Y', $search['to']);
    if ($search['from'] >= $search['to']) {
        $search['from'] = 0;
        $search['to'] = 0;
        $search['range'] = '';
    }
} else {
    $search['range'] = '';
}

$db->sqlreset()->select('COUNT(tb1.id)')->from($db_config['prefix'] . '_' . $module_data . '_leave_absence tb1');
$db->join("INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid");

$is_search = 0;
$arr_where = [];
$arr_where[] =  '(tb1.start_leave >= ' . $search['from'] . ' OR tb1.date_leave >= ' . $search['from'] . ')';
$arr_where[] = '(tb1.start_leave <= ' . $search['to'] . ' OR tb1.date_leave <= ' . $search['to'] . ')';

$where_or = [];
if (!empty($department_ids)) {
    $where_or[] = 'tb2.department_id IN(' . implode(',', $department_ids) . ')';
}
$where_or[] = 'tb1.userid=' . $user_info['userid'];
$arr_where[] = '(' . implode(' OR ', $where_or) . ')';

// Xem theo thành viên
if (!empty($search['userid']) and isset($array_department_staffs[0][$search['userid']])) {
    $arr_where[] = 'tb1.userid=' . $search['userid'] ;
    $is_search++;
    $base_url .= '&amp;u=' . $search['userid'];
}
// Xem toàn phòng ban
if (!empty($search['department_id']) and in_array($search['department_id'], $department_ids)) {
    $arr_where[] = 'tb2.department_id IN(' . implode(',', array_intersect(GetDepartmentidInParent($search['department_id']), $department_ids)) . ')';
    $is_search++;
    $base_url .= '&amp;d=' . $search['department_id'];
}
if ($init_range != $search['range']) {
    $base_url .= '&amp;r=' . urlencode($search['range']);
}
$db->where(implode(' AND ', $arr_where));

$sth = $db->query($db->sql());
$sth->execute();
$num_items = $sth->fetchColumn();

$page = $nv_Request->get_absint('page', 'get', 1);
$per_page = 30;
betweenURLs($page, ceil($num_items / $per_page), $base_url, '&amp;page=', $prevPage, $nextPage);

$page_url = $base_url;
if ($page > 1) {
    $page_url .= '&amp;page=' . $page;
}
$canonicalUrl = getCanonicalUrl($page_url);

$array = [];
$db->select('tb1.*, tb2.department_id, tb2.office_id')
->order('tb1.created_time ASC, tb1.id DESC')
->limit($per_page)
->offset(($page - 1) * $per_page);
$sth = $db->prepare($db->sql());
$sth->execute();

$array_leave_userids = $array_userids = [];
while ($row = $sth->fetch()) {
    $array[$row['id']] = $row;
    $array_leave_userids[$row['userid']] = $row['userid'];
    if (!empty($row['userid_approved'])) {
        $array_userids[$row['userid_approved']] = $row['userid_approved'];
    }
}

// Lấy họ tên những người xác nhận
$array_users = [];
if (!empty($array_userids)) {
    $sql = "SELECT userid, username, first_name, last_name, email
    FROM " . NV_USERS_GLOBALTABLE . " WHERE userid IN(" . implode(',', $array_userids) . ")";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
        $array_users[$row['userid']] = $row;
    }
}

// Xác định tổng số ngày nghỉ trong khoảng thời gian tìm đó
$sum_day_by_user = [];
if (!empty($array_leave_userids)) {
    $sql = "SELECT userid, SUM(num_day) num_day FROM " . $db_config['prefix'] . "_" . $module_data . "_leave_absence
    WHERE userid IN(" . implode(',', $array_leave_userids) . ") AND
    (start_leave >= " . $search['from'] . " OR date_leave >= " . $search['from'] . ") AND
    (start_leave <= " . $search['to'] . " OR date_leave <= " . $search['to'] . ")
    GROUP BY userid";
    $result = $db->query($sql);
    while ($row = $result->fetch()) {
        $sum_day_by_user[$row['userid']] = $row['num_day'];
    }
}

$xtpl = new XTemplate('leave-absence.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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

// Phòng ban và dữ liệu nhân viên
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
// if (!$is_search) {
//     $xtpl->parse('main.please_choose');
// }

// Phân trang
$generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
if (!empty($generate_page)) {
    $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
    $xtpl->parse('main.generate_page');
}

// Xuất danh sách
if (!empty($array)) {
    $search_from = date('d/m/Y', $search['from']);
    $search_to = date('d/m/Y', $search['to']);

    $xtpl->assign('SEARCH_FROM', $search_from);
    $xtpl->assign('SEARCH_TO', $search_to);
    if (empty($search['userid'])) {
        $xtpl->assign('STAFF', '');
    } else {
        $xtpl->assign('STAFF', $array_staffs[$search['userid']]);
    }

    $department_name = '';
    if (empty($search['userid']) and !empty($search['department_id'])) {
        $department_name = $lang_module['room'] . ' ' . $json_department_staffs[$search['department_id']]['title'];
    }
    $xtpl->assign('DEPARTMENT_NAME', $department_name);

    $number = $page > 1 ? ($per_page * ($page - 1)) + 1 : 1;
    $stt = 1;
    foreach ($array as $view) {
        $xtpl->assign('TT', number_format($stt++, 0, ',', '.'));

        $view['name'] = $array_staffs[$view['userid']] ?? ('U#' . $view['userid']);
        $view['created_time'] = (empty($view['created_time'])) ? '' : nv_date('d/m/Y H:i:s', $view['created_time']);
        $date_leave = (empty($view['date_leave'])) ? '' : nv_date('d/m/Y', $view['date_leave']);
        $start_leave = (empty($view['start_leave'])) ? '' : nv_date('d/m/Y', $view['start_leave']);
        $end_leave = (empty($view['end_leave'])) ? '' : nv_date('d/m/Y', $view['end_leave']);
        if ($view['type_leave'] == '1') {
            $title_date_str = '';
            $title_date_end = '';
            if ($view['half_day_start'] == 1) {
                $title_date_str = $lang_module['morning'] . ' ';
            } else if ($view['half_day_start'] == 2) {
                $title_date_str = $lang_module['afternoon'] . ' ';
            }
            if ($view['half_day_end'] == 1) {
                $title_date_end = $lang_module['morning'] . ' ';
            } else if ($view['half_day_end'] == 2) {
                $title_date_end = $lang_module['afternoon'] . ' ';
            }
            $view['time'] = $title_date_str . $start_leave . ' - ' . $title_date_end . $end_leave;
        } else {
            $view['time'] = $date_leave;
        }
        $view['total_day'] = $view['num_day'];
        $view['total_all_day'] = $sum_day_by_user[$view['userid']];
        $view['total_all_day_tooltip'] = $lang_module['total_all_day'] . ' ' . $lang_module['view_report_from'] . ' ' .$from . ' '. $lang_module['view_report_to'] . ' '. $to;

        $view['department'] = '';
        if (!empty($view['department_id'])) {
            $view['department'] = $json_department_staffs[$view['department_id']]['title'] ?? ('D#' . $view['department_id']);
        }
        $xtpl->assign('VIEW', $view);

        $view['show_hide_button_deny'] = "";
        $leader_full_name = '';
        if (!empty($view['userid_approved'])) {
            $leader_full_name = $array_users[$view['userid_approved']]['full_name'] ?? ('U#' . $view['userid_approved']);
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
        $approved_time = (empty($view['approved_time'])) ? nv_date('d/m/Y H:i:s', $view['approved_time']) : $view['created_time'];
        if ($view['confirm'] == 1) {
            $view['confirm_title'] = $lang_module['cancel_confirm'];
            $view['status'] = 0;
            $had_confirm['confirm_status'] = $lang_module['confirmed_ic'];
            $tooltip = $lang_module['confirm_by'] . $leader_full_name . '<br/>' .$lang_module['time_create'] . ': ' . $approved_time;
            $tooltip_class = 'label-info';
        } else if ($view['confirm'] == 2) {
            $view['confirm_title'] = $lang_module['confirm'];
            $view['status'] = 1;
            $had_confirm['confirm_status'] = $lang_module['denied_ic'];
            $tooltip = $lang_module['denied_by'] . $leader_full_name . '<br/>' .$lang_module['time_create'] . ': ' . $approved_time;
            $tooltip .= '<br/>' . $lang_module['reason_deny'] . ': ' . $view['deny_reason'];
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

        // Xem chính mình thì hiển thị nút xóa, sửa
        $current_time = time();
        $not_edit_del = '';
        if ($HR->user_info['userid'] == $view['userid']) {
            if ($view['confirm'] == 1) {
                $not_edit_del = 'style="display:none"';
            }
            $xtpl->assign('not_edit_del', $not_edit_del);
            $xtpl->parse('main.view.loop.edit_deleted');
        }

        $allow_confirm = nvCheckAllowLeaveApproval($HR->user_info, $view);
        if ($allow_confirm) {
            // Nếu leader thì hiển thị nút xác nhận - từ chối - hủy xác nhận
            if ($view['confirm'] == 0) {
                $view['confirm_or_deny'] = '';
            }
            $xtpl->assign('CONFIRM', $view);
            $xtpl->parse('main.view.loop.change_confirm');
        } else {
            if ($view['confirm'] == 0) {
                $had_confirm['leader_full_name'] = $lang_module['wait_confirm'];
            }
        }
        $xtpl->assign('NOT_EDIT_DEL', $not_edit_del);
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
