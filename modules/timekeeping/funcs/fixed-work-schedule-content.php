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
if (!isset($manager_array[$user_info['userid']])) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Các phòng ban được quản lý
$departments_allowed = GetDepartmentidInParent($HR->user_info['department_id']);

// Lấy id công việc cần sửa nếu có
$id = $nv_Request->get_int('id', 'get', 0);

$error = [];

if (!empty($id)) {
    // Kiểm tra công việc sửa
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE id = " . $id . " AND
    department_id IN(" . implode(',', $departments_allowed) . ")";
    $result = $db->query($sql);
    $array = $result->fetch();

    if (empty($array)) {
        nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content']);
    }

    // Chuyển các dạng text trong DB thành dạng quản lý
    $array['apply_departments'] = empty($array['apply_departments']) ? [] : array_unique(array_filter(array_map('intval', explode(',', $array['apply_departments']))));
    $array['apply_days'] = empty($array['apply_days']) ? [] : array_unique(array_filter(array_map('intval', explode(',', $array['apply_days']))));

    $page_title = $lang_module['schedule_edit'];
    $form_action = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $id;
} else {
    $array = [
        'title' => '',
        'link' => '',
        'apply_departments' => [],
        'apply_days' => [],
        'work_content' => '',
        'work_note' => '',
        'work_time' => 0,
        'status' => 1,
        'apply_allsubs' => 1,
        'add_time' => NV_CURRENTTIME
    ];

    $page_title = $lang_module['schedule_add'];
    $form_action = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
}

$page_url = $form_action;
$canonicalUrl = getCanonicalUrl($page_url);
$array_mod_title[] = [
    'catid' => 0,
    'title' => $lang_module['schedule'],
    'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=fixed-work-schedule'
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
    $array['apply_departments'] = $nv_Request->get_typed_array('apply_departments', 'post', 'int', []);
    $array['apply_days'] = $nv_Request->get_typed_array('apply_days', 'post', 'int', []);
    $array['work_content'] = $nv_Request->get_string('work_content', 'post', '');
    $array['work_note'] = $nv_Request->get_string('work_note', 'post', '');
    $array['work_time'] = $nv_Request->get_float('work_time', 'post', 0);
    $array['status'] = (int) $nv_Request->get_bool('status', 'post', false);
    $array['apply_allsubs'] = (int) $nv_Request->get_bool('apply_allsubs', 'post', false);

    if (empty($id)) {
        $array['add_time'] = $nv_Request->get_absint('add_time', 'post', NV_CURRENTTIME);
    }

    // Xử lý dữ liệu
    $array['apply_departments'] = array_intersect($array['apply_departments'], $departments_allowed);
    $array['apply_days'] = array_intersect($array['apply_days'], [1, 2, 3, 4, 5, 6, 7]);

    $array['work_content'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['work_content'])));
    $array['work_note'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['work_note'])));

    // Kiểm tra trùng
    $is_exists = false;
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule
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
    if (empty($array['apply_days'])) {
        $error[] = $lang_module['schedule_error_apply_days'];
    }

    if (empty($error)) {

        if (!$id) {
            $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_work_schedule (
                userid, title, link, department_id, apply_departments, apply_allsubs,
                apply_days, work_content, work_note, work_time, add_time, edit_time, status
            ) VALUES (
                " . $user_info['userid'] . ", :title, :link,
                " . $HR->user_info['department_id'] . ", " . $db->quote(implode(',', $array['apply_departments'])) . ",
                " . $array['apply_allsubs'] . ", " . $db->quote(implode(',', $array['apply_days'])) . ",
                :work_content, :work_note, " . $array['work_time'] . ", " . $array['add_time'] . ", 0,
                " . $array['status'] . "
            )";
        } else {
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_work_schedule SET
                title=:title, link=:link,
                apply_departments=" . $db->quote(implode(',', $array['apply_departments'])) . ",
                apply_allsubs=" . $array['apply_allsubs'] . ",
                apply_days=" . $db->quote(implode(',', $array['apply_days'])) . ",
                work_content=:work_content, work_note=:work_note,
                work_time=" . $array['work_time'] . ",
                status=" . $array['status'] . ",
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
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_EDIT_SCHEDULE', $id . ': ' . $array['title'], $user_info['userid']);
            } else {
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_ADD_SCHEDULE', $array['title'], $user_info['userid']);
            }

            $nv_Cache->delMod($module_name);
            nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=fixed-work-schedule');
        } catch (PDOException $e) {
            trigger_error(print_r($e, true));
            $error[] = $lang_module['errorsave'];
        }
    }
}

// Xử lý status cho checkbox
$array['status'] = empty($array['status']) ? '' : ' checked="checked"';
$array['apply_allsubs'] = empty($array['apply_allsubs']) ? '' : ' checked="checked"';

$array['work_content'] = nv_br2nl($array['work_content']);
$array['work_note'] = nv_br2nl($array['work_note']);

$xtpl = new XTemplate('fixed-work-schedule-content.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('FORM_ACTION', $form_action);
$xtpl->assign('DATA', $array);
$xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
$xtpl->assign('TITLE', $page_title);

$xtpl->assign('CSS_APPLY_DEPARTMENTS', empty($array['apply_allsubs']) ? '' : ' hidden');

// Xuất phòng ban
foreach ($department_array as $department) {
    if (in_array($department['id'], $departments_allowed) and $department['id'] != $HR->user_info['department_id']) {
        $department['title'] = nv_get_full_department_title($department['id']);
        $department['checked'] = in_array($department['id'], $array['apply_departments']) ? ' checked="checked"' : '';
        $xtpl->assign('DEPARTMENT', $department);
        $xtpl->parse('main.department');
    }
}

// Xuất thứ trong tuần
for ($i = 1; $i < 8; $i++) {
    $day = [
        'key' => $i,
        'title' => $lang_module['schedule_apply_days' . $i],
        'checked' => in_array($i, $array['apply_days']) ? ' checked="checked"' : '',
    ];
    $xtpl->assign('DAY', $day);
    $xtpl->parse('main.day');
}

// Hiển thị lỗi
if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
