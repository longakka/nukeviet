<?php

use NukeViet\Module\timekeeping\Role;

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 07/30/2013 10:27
 */

if (!defined('NV_ADMIN')) {
    die('Stop!!!');
}

$admin_id = $admin_info['admin_id'];

$allow_func = [];
$allow_func[] = 'main';

// Lấy thông tin nhân viên của chính quản trị vào đây
$sql = "SELECT tb1.*, tb2.title department_name FROM " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_department tb2 ON tb1.department_id=tb2.id
WHERE tb1.userid=" . $admin_info['admin_id'];
$HR_info = $db->query($sql)->fetch();
if (!empty($HR_info)) {
    $HR_info['role_id'] = empty($HR_info['role_id']) ? [] : explode(',', $HR_info['role_id']);
}

// Bộ phận văn phòng được xem, xuất bảng công
if (defined('NV_IS_SPADMIN') or (!empty($HR_info) and in_array(Role::ROLE_ACCOUNTING_DEPARTMENT, $HR_info['role_id']))) {
    $allow_func[] = 'statistics';
    $allow_func[] = 'statistics_user';
    $submenu['statistics'] = $lang_module['statistics'];
}

if (defined('NV_IS_SPADMIN')) {
    $allow_func[] = 'office';
    $allow_func[] = 'departments';
    $allow_func[] = 'taskcat_add';
    $allow_func[] = 'taskcat';
    $allow_func[] = 'config';
    $allow_func[] = 'config_member';
    $allow_func[] = 'config_member_add';
    $allow_func[] = 'config_email';
    $allow_func[] = 'ranks';

    $submenu['config'] = $lang_module['config'];
    $submenu['config_email'] = $lang_module['config_email'];
    $submenu['config_member'] = array(
        'title' => $lang_module['config_member'],
        'submenu' => array(
            'config_member' => $lang_module['list'],
            'config_member_add' => $lang_module['config_member_add'],
        )
    );
    $submenu['office'] = $lang_module['office'];
    $submenu['departments'] = $lang_module['department'];
    $submenu['ranks'] = $lang_module['rank_manager'];
}

// Bộ phận nhân sự được quản lý nhân sự
if (defined('NV_IS_SPADMIN') or (!empty($HR_info) and in_array(Role::ROLE_HR_DEPARTMENT, $HR_info['role_id']))) {
    $allow_func[] = 'human_resources';
    $allow_func[] = 'human_resources_add';
    $submenu['human_resources'] = array(
        'title' => $lang_module['human_resources'],
        'submenu' => array(
            'human_resources' => $lang_module['list_human_resources'],
            'human_resources_add' => $lang_module['human_resources_add']
        )
    );
}
