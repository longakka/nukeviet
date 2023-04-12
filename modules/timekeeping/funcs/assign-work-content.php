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

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

// Kiểm tra lãnh đạo, trưởng phòng
if (!isset($leader_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Các phòng ban được quản lý
$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);

/*
 * Lấy toàn bộ danh sách nhân viên mình có thể giao việc
 * - Tất cả nếu phòng ban của mình được phép xem tất cả
 * - Nhân viên phòng ban mình hoặc phòng ban con của mình
 */
$sql = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id
FROM " . NV_USERS_GLOBALTABLE . " tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
WHERE tb1.active=1 AND tb2.still_working=1
AND tb2.department_id IN(" . implode(',', $departments_allowed) . ")";

$sql .= " ORDER BY tb2.weight ASC, " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC";
$result = $db->query($sql);

$array_staffs = [];
while ($row = $result->fetch()) {
    $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name']);
    if (empty($array_staffs[$row['userid']])) {
        $array_staffs[$row['userid']] = $row['username'];
    } else {
        $array_staffs[$row['userid']] .= ' (' . $row['username'] . ')';
    }
}

// Load lịch làm việc cố định
if (isset($_POST['loadtaskcat']) and $nv_Request->get_title('loadtaskcat', 'post', '') == NV_CHECK_SESSION) {
    $respon = [];
    $userid = $nv_Request->get_absint('userid', 'post', 0);

    // Lấy các dự án đang hoạt động mà mình đang join
    $_sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE status = 1 AND (
        project_public=1 OR FIND_IN_SET(' . $userid . ', users_assigned)
    )';
    $_query = $db->query($_sql);
    while ($_row = $_query->fetch()) {
        $respon[] = [
            'id' => $_row['id'],
            'title' => $_row['title']
        ];
    }
    nv_jsonOutput($respon);
}

// Lấy id công việc cần sửa nếu có
$id = $nv_Request->get_int('id', 'get', 0);
$is_copy = $nv_Request->get_int('copy', 'post,get', 0);

$error = [];

if (!empty($id)) {
    // Kiểm tra công việc sửa
    $sql = "SELECT tb1.* FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign tb1
    INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
    WHERE tb1.id = " . $id . " AND
    tb2.department_id IN(" . implode(',', $departments_allowed) . ")";
    $result = $db->query($sql);
    $array = $result->fetch();

    if (empty($array)) {
        nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content']);
    }
    $page_title = $lang_module['asswork_edit'];
    $form_action = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $id;
    if ($is_copy) {
        $id = 0;
        $array['add_time'] = NV_CURRENTTIME;
        $page_title = $lang_module['asswork_add'];
    }
    $array_old = $array;
} else {
    $array = $array_old = [
        'taskcat_id' => 0,
        'userid' => 0,
        'from_time' => 0,
        'to_time' => 0,
        'work_time' => 0,
        'change_work_time' => '',
        'title' => '',
        'link' => '',
        'work_content' => '',
        'work_note' => '',
        'status' => 1,
        'add_time' => NV_CURRENTTIME
    ];

    $page_title = $lang_module['asswork_add'];
    $form_action = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
}

$page_url = $form_action;
$canonicalUrl = getCanonicalUrl($page_url);
$array_mod_title[] = [
    'catid' => 0,
    'title' => $lang_module['asswork'],
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=assign-work'
];
$array_mod_title[] = [
    'catid' => 1,
    'title' => $page_title,
    'link' => $page_url
];

if ($nv_Request->get_title('saveform', 'post', '') === NV_CHECK_SESSION) {
    // Lấy dữ liệu
    $array['title'] = nv_substr($nv_Request->get_title('title', 'post', ''), 0, 190);
    $array['link'] = nv_substr($nv_Request->get_title('link', 'post', ''), 0, 255);
    $array['work_content'] = $nv_Request->get_string('work_content', 'post', '');
    $array['work_note'] = $nv_Request->get_string('work_note', 'post', '');
    $array['change_work_time'] = $nv_Request->get_string('change_work_time', 'post', '');
    $array['status'] = (int) $nv_Request->get_bool('status', 'post', false);

    if (empty($id)) {
        $array['add_time'] = $nv_Request->get_absint('add_time', 'post', NV_CURRENTTIME);
        $array['userid'] = $nv_Request->get_absint('userid', 'post', 0);
    }
    $array['work_time'] = $nv_Request->get_float('work_time', 'post', 0);

    $from_time = $nv_Request->get_string('from_time', 'post', '');
    $array['from_time'] = 0;
    if (!empty($from_time)) {
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $from_time, $m)) {
            $array['from_time'] = mktime(0, 0, 0, intval($m[2]), intval($m[1]), intval($m[3]));
        }
    }

    $to_time = $nv_Request->get_string('to_time', 'post', '');
    $array['to_time'] = 0;
    if (!empty($to_time)) {
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $to_time, $m)) {
            $array['to_time'] = mktime(0, 0, 0, intval($m[2]), intval($m[1]), intval($m[3]));
        }
    }
    if ($array['to_time'] < $array['from_time']) {
        $array['to_time'] = $array['from_time'];
    }
    if (!empty($array['to_time'])) {
        $array['to_time'] += 86399;
    }

    $array['taskcat_id'] = $nv_Request->get_absint('taskcat_id', 'post', 0);

    // Xử lý dữ liệu
    $array['work_content'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['work_content'])));
    $array['work_note'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['work_note'])));
    $array['change_work_time'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['change_work_time'])));

    // Kiểm tra trùng
    $is_exists = false;
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign
    WHERE title=:title AND add_time=" . $array['add_time'] . "" . ($id ? ' AND id != ' . $id : '');
    $sth = $db->prepare($sql);
    $sth->bindParam(':title', $array['title'], PDO::PARAM_STR);
    $sth->execute();
    if ($sth->fetchColumn()) {
        $is_exists = true;
    }

    if (empty($array['title'])) {
        $error[] = $lang_module['schedule_error_title'];
    } elseif ($is_exists) {
        $error[] = $lang_module['schedule_error_exists'];
    }

    if (empty($array['work_content'])) {
        $error[] = $lang_module['schedule_error_work_content'];
    }
    if ($array['work_time'] <= 0) {
        $error[] = $lang_module['schedule_error_work_time'];
    }
    if (!isset($array_staffs[$array['userid']])) {
        $error[] = $lang_module['asswork_userid_error'];
    }
    if (empty($array['from_time'])) {
        $error[] = $lang_module['asswork_error_from_time'];
    }
    if (empty($array['to_time'])) {
        $error[] = $lang_module['asswork_error_to_time'];
    }

    // Check dự án
    $sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE status = 1 AND (
        project_public=1 OR FIND_IN_SET(' . $array['userid'] . ', users_assigned)
    ) AND id=' . $array['taskcat_id'];
    if (!$db->query($sql)->fetch()) {
        $error[] = $lang_module['asswork_error_taskcat'];
    }

    if ($id and $array_old['work_time'] != $array['work_time'] and empty($array['change_work_time'])) {
        $error[] = $lang_module['asswork_change_work_time2'];
    }

    if (empty($error)) {
        if (!$id) {
            $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_task_assign (
                taskcat_id, userid, assignee_userid, title, link, from_time, to_time,
                work_content, work_note, work_time, add_time, edit_time
            ) VALUES (
                " . $array['taskcat_id'] . ", " . $array['userid'] . ", " . $user_info['userid'] . ", :title, :link,
                " . $array['from_time'] . ", " . $array['to_time'] . ",
                :work_content, :work_note, " . $array['work_time'] . ", " . $array['add_time'] . ", 0
            )";
        } else {
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task_assign SET
                taskcat_id=" . $array['taskcat_id'] . ",
                title=:title, link=:link,
                from_time=" . $array['from_time'] . ",
                to_time=" . $array['to_time'] . ",
                work_time=" . $array['work_time'] . ",
                work_content=:work_content, work_note=:work_note,
                edit_time=" . NV_CURRENTTIME . "
            WHERE id = " . $id;
        }

        try {
            $sth = $db->prepare($sql);
            $sth->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $sth->bindParam(':link', $array['link'], PDO::PARAM_STR);
            $sth->bindParam(':work_content', $array['work_content'], PDO::PARAM_STR, strlen($array['work_content']));
            $sth->bindParam(':work_note', $array['work_note'], PDO::PARAM_STR, strlen($array['work_note']));
            $sth->execute();

            if ($id) {
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_EDIT_TASK_ASSIGN', $id . ': ' . $array['title'], $user_info['userid']);
            } else {
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_ADD_TASK_ASSIGN', $array['title'], $user_info['userid']);
            }

            // Lưu lịch sử nguyên nhân thay đổi giờ
            if ($id and $array_old['work_time'] != $array['work_time']) {
                $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change (
                    assign_id, userid, add_time, time_new, time_old, change_reason
                ) VALUES (
                    :assign_id, :userid, :add_time, :time_new, :time_old, :change_reason
                )";
                $array_insert = [
                    'assign_id' => $id,
                    'userid' => $user_info['userid'],
                    'add_time' => NV_CURRENTTIME,
                    'time_new' => $array['work_time'],
                    'time_old' => $array_old['work_time'],
                    'change_reason' => $array['change_work_time'],
                ];
                $db->insert_id($sql, 'id', $array_insert);
            }

            $nv_Cache->delMod($module_name);
            nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=assign-work');
        } catch (PDOException $e) {
            trigger_error(print_r($e, true));
            $error[] = $lang_module['errorsave'];
        }
    }
}

// Xử lý status cho checkbox
$array['status'] = empty($array['status']) ? '' : ' checked="checked"';
$array['from_time'] = empty($array['from_time']) ? '' : date('d/m/Y', $array['from_time']);
$array['to_time'] = empty($array['to_time']) ? '' : date('d/m/Y', $array['to_time']);

$array['work_content'] = nv_br2nl($array['work_content']);
$array['work_note'] = nv_br2nl($array['work_note']);

$xtpl = new XTemplate($op . '.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('FORM_ACTION', $form_action);
$xtpl->assign('DATA', $array);
$xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
$xtpl->assign('TITLE', $page_title);
$xtpl->assign('OP', $op);
$xtpl->assign('IS_COPY', $is_copy);

// Hiển thị lỗi
if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->assign('NO_CHAGE_USER', $id ? ' disabled="disabled"' : '');
if ($id) {
    $xtpl->parse('main.change_work_time');
}

// Xuất nhân viên
foreach ($array_staffs as $staff_id => $staff_name) {
    $staff = [
        'id' => $staff_id,
        'title' => $staff_name,
        'selected' => $staff_id == $array['userid'] ? ' selected="selected"' : '',
    ];

    $xtpl->assign('STAFF', $staff);
    $xtpl->parse('main.staff');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
