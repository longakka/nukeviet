<?php

/**
 * @Project BDS 4.x
 * @Author PHAN TAN DUNG <writeblabla@gmail.com>
 * @Copyright (C) 2021 PHAN TAN DUNG. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 12 Jun 2016 05:02:54 GMT
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$page_title = $lang_module['department_manager'];

// Thay đổi thứ tự
if ($nv_Request->isset_request('changeweight', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $new_weight = $nv_Request->get_int('new_weight', 'post', 0);

    // Kiểm tra tồn tại
    $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id=' . $id;
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }
    if (empty($new_weight)) {
        nv_htmlOutput('NO_' . $id);
    }

    $sql = 'SELECT id FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id!=' . $id . ' AND parentid=' . $array['parentid'] . ' ORDER BY weight ASC';
    $result = $db->query($sql);

    $weight = 0;
    while ($row = $result->fetch()) {
        ++$weight;
        if ($weight == $new_weight) {
            ++$weight;
        }
        $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_department SET weight=' . $weight . ' WHERE id=' . $row['id'];
        $db->query($sql);
    }

    $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_department SET weight=' . $new_weight . ' WHERE id=' . $id;
    $db->query($sql);

    nv_fix_department_order();

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CHANGE_WEIGHT_DEPARTMENT', $id . ': ' . $array['title'], $admin_info['admin_id']);
    $nv_Cache->delMod($module_name);
    nv_htmlOutput('OK_' . $id);
}

// Xóa
if ($nv_Request->isset_request('deleterow', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);

    // Kiểm tra tồn tại
    $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id=' . $id;
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }

    // Lấy hết ID chủ đề con và ID chính nó
    $catids = GetDepartmentidInParent($id);

    $sql = 'DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id IN(' . implode(',', $catids) . ')';
    $db->query($sql);

    nv_fix_department_order();

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_DELETE_DEPARTMENT', $id . ': ' . $array['title'], $admin_info['admin_id']);
    $nv_Cache->delMod($module_name);

    nv_htmlOutput("OK");
}

$array = [];
$error = '';

$id = $nv_Request->get_int('id', 'get', 0);
$parentid = $nv_Request->get_int('parentid', 'get', 0);
if (!empty($parentid) and !isset($department_array[$parentid])) {
    nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
}

if (!empty($id)) {
    $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id = ' . $id;
    $result = $db->query($sql);
    $array = $result->fetch();

    if (empty($array)) {
        nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content']);
    }

    $array['parentid_old'] = $array['parentid'];

    $caption = $lang_module['department_edit'];
    $array_in_cat = GetDepartmentidInParent($id);
    $form_action = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;parentid=' . $parentid . '&amp;id=' . $id;
} else {
    $array = [
        'id' => 0,
        'parentid' => $parentid,
        'parentid_old' => $parentid,
        'title' => '',
        'description' => '',
        'assign_work' => 0,
        'checkin_out_session' => 0,
        'view_all_task' => 0,
        'view_all_absence' =>0,
        'check_work' => 0,
    ];

    $caption = $lang_module['department_add'];
    $array_in_cat = [];
    $form_action = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;parentid=' . $parentid;
}

if ($nv_Request->isset_request('submit', 'post')) {
    $array['title'] = $nv_Request->get_title('title', 'post', '');
    $array['parentid'] = $nv_Request->get_int('sel_parentid', 'post', 0);
    $array['description'] = $nv_Request->get_string('description', 'post', '');
    $array['assign_work'] = (int) $nv_Request->get_bool('assign_work', 'post', false);
    $array['checkin_out_session'] = (int) $nv_Request->get_bool('checkin_out_session', 'post', false);
    $array['view_all_task'] = (int) $nv_Request->get_bool('view_all_task', 'post', false);
    $array['view_all_absence'] = (int) $nv_Request->get_bool('view_all_absence', 'post', false);
    $array['check_work'] = (int) $nv_Request->get_bool('check_work', 'post', false);

    // Xử lý dữ liệu
    $array['description'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['description'])), '<br />');
    if ($array['parentid'] and !isset($department_array[$array['parentid']])) {
        $array['parentid'] = 0;
    }

    // Kiểm tra trùng tiêu đề
    $is_exists = false;
    $sql = 'SELECT id FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE title=:title' . ($id ? ' AND id != ' . $id : '');
    $sth = $db->prepare($sql);
    $sth->bindParam(':title', $array['title'], PDO::PARAM_STR);
    $sth->execute();
    if ($sth->fetchColumn()) {
        $is_exists = true;
    }

    if (empty($array['title'])) {
        $error = $lang_module['department_error_title'];
    } elseif ($is_exists) {
        $error = $lang_module['department_error_exists'];
    } else {
        if (!$id) {
            $sql = 'SELECT MAX(weight) weight FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE parentid=' . $array['parentid'];
            $weight = intval($db->query($sql)->fetchColumn()) + 1;

            $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_department (
                parentid, title, alias, description, weight, sort, lev, numsubcat, subcatid, checkin_out_session,
                view_all_task, view_all_absence, check_work, assign_work
            ) VALUES (
                " . $array['parentid'] . ", :title, :alias, :description, " . $weight . ", 0, 0, 0, '',
                " . $array['checkin_out_session'] . ", " . $array['view_all_task'] . ", " . $array['view_all_absence'] . ",
                " . $array['check_work'] . ", " . $array['assign_work'] . "
            )";

            $data_insert = [];
            $data_insert['title'] = $array['title'];
            $data_insert['alias'] = $array['title'];
            $data_insert['description'] = $array['description'];;

            $new_id = $db->insert_id($sql, 'id', $data_insert);
            if (!empty($new_id)) {
                nv_fix_department_order();
                $nv_Cache->delMod($module_name);
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_ADD_DEPARTMENT', $array['title'], $admin_info['admin_id']);
                nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $array['parentid']);
            } else {
                $error = $lang_module['errorsave'];
            }
        } else {
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_department SET
                parentid=" . $array['parentid'] . ",
                title=:title,
                alias=:alias,
                description=:description,
                assign_work=" . $array['assign_work'] . ",
                checkin_out_session=" . $array['checkin_out_session'] . ",
                view_all_task=" . $array['view_all_task'] . ",
                view_all_absence=" . $array['view_all_absence'] . ",
                check_work=" . $array['check_work'] . "
            WHERE id = " . $id;
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $stmt->bindParam(':alias', $array['title'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $array['description'], PDO::PARAM_STR);
            $stmt->execute();

            if ($array['parentid'] != $array['parentid_old']) {
                $weight = $db->query('SELECT MAX(weight) FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE parentid=' . $array['parentid'])->fetchColumn();
                $weight = intval($weight) + 1;

                $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_department SET weight=' . $weight . ' WHERE id=' . $id;
                $db->query($sql);

                nv_fix_department_order();
            }

            $nv_Cache->delMod($module_name);
            nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_EDIT_DEPARTMENT', $id . ': ' . $array['title'], $admin_info['admin_id']);
            nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op . '&parentid=' . $array['parentid']);
        }
    }
}

$array['assign_work'] = empty($array['assign_work']) ? '' : ' checked="checked"';
$array['checkin_out_session'] = empty($array['checkin_out_session']) ? '' : ' checked="checked"';
$array['check_work'] = empty($array['check_work']) ? '' : ' checked="checked"';
$array['view_all_task'] = empty($array['view_all_task']) ? '' : ' checked="checked"';
$array['view_all_absence'] = empty($array['view_all_absence']) ? '' : ' checked="checked"';
$array['description'] = nv_br2nl($array['description']);

$xtpl = new XTemplate('departments.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('CAPTION', $caption);
$xtpl->assign('FORM_ACTION', $form_action);
$xtpl->assign('DATA', $array);

// Xác định danh sách thể loại để build ra
$array_department_list = [];
$array_department_list[0] = $lang_module['department_sub_sl'];

foreach ($department_array as $id_i => $array_value) {
    $lev_i = $array_value['lev'];
    $xtitle_i = '';
    if ($lev_i > 0) {
        $xtitle_i .= '&nbsp;&nbsp;&nbsp;|';
        for ($i = 1; $i <= $lev_i; ++$i) {
            $xtitle_i .= '---';
        }
        $xtitle_i .= '>&nbsp;';
    }
    $xtitle_i .= $array_value['title'];
    $array_department_list[$id_i] = $xtitle_i;
}

// Xác định danh sách các thể loại cha để build ra
$department_listsub = [];
foreach ($array_department_list as $id_i => $title_i) {
    if (!in_array($id_i, $array_in_cat)) {
        $department_listsub[] = [
            'key' => $id_i,
            'title' => $title_i,
            'selected' => ($id_i == $array['parentid']) ? ' selected="selected"' : ''
        ];
    }
}

// Xuất parentid
foreach ($department_listsub as $_row) {
    $xtpl->assign('PARENTCAT', $_row);
    $xtpl->parse('main.parentcat');
}

// Xuất thanh NAV
if ($parentid > 0) {
    $parentid_i = $parentid;
    $array_department_title = [];
    $stt = 0;
    while ($parentid_i > 0) {
        $array_department_title[] = [
            'active' => ($stt++ == 0) ? true : false,
            'link' => NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=departments&amp;parentid=" . $parentid_i,
            'title' => $department_array[$parentid_i]['title']
        ];
        $parentid_i = $department_array[$parentid_i]['parentid'];
    }
    $array_department_title[] = [
        'active' => false,
        'link' => NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=departments",
        'title' => $lang_module['department_parent']
    ];
    krsort($array_department_title, SORT_NUMERIC);

    foreach ($array_department_title as $cat) {
        $xtpl->assign('CAT', $cat);
        if ($cat['active']) {
            $xtpl->parse('main.department_title.active');
        } else {
            $xtpl->parse('main.department_title.loop');
        }
    }
    $xtpl->parse('main.department_title');
}

// Xuất các thể loại theo parentid
$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE parentid = ' . $parentid . ' ORDER BY weight ASC';
$array_cats = $db->query($sql)->fetchAll();
$num = sizeof($array_cats);

foreach ($array_cats as $row) {
    $row['url_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=departments&amp;parentid=' . $row['parentid'] . '&amp;id=' . $row['id'];
    $row['url_view'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=departments&amp;parentid=' . $row['id'];
    $row['assign_work'] = $lang_module['yesno' . $row['assign_work']];
    $row['checkin_out_session'] = $lang_module['yesno' . $row['checkin_out_session']];
    $row['view_all_task'] = $lang_module['yesno' . $row['view_all_task']];
    $row['view_all_absence'] = $lang_module['yesno' . $row['view_all_absence']];
    $row['check_work'] = $lang_module['yesno' . $row['check_work']];

    for ($i = 1; $i <= $num; ++$i) {
        $xtpl->assign('WEIGHT', [
            'w' => $i,
            'selected' => ($i == $row['weight']) ? ' selected="selected"' : ''
        ]);

        $xtpl->parse('main.loop.weight');
    }

    $xtpl->assign('ROW', $row);

    // Số thể loại con
    if (!empty($row['numsubcat'])) {
        $xtpl->parse('main.loop.numsubcat');
    }

    $xtpl->parse('main.loop');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', $error);
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
