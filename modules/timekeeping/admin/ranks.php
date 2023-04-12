<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$page_title = $lang_module['rank_manager'];

// Thay đổi thứ tự
if ($nv_Request->get_title('changeweight', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_absint('id', 'post', 0);
    $new_weight = $nv_Request->get_absint('new_weight', 'post', 0);

    // Kiểm tra tồn tại
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id=" . $id;
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }
    if (empty($new_weight)) {
        nv_htmlOutput('NO_' . $id);
    }

    $sql = "SELECT id FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id!=" . $id . " ORDER BY weight ASC";
    $result = $db->query($sql);

    $weight = 0;
    while ($row = $result->fetch()) {
        ++$weight;
        if ($weight == $new_weight) {
            ++$weight;
        }
        $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_ranks SET weight=" . $weight . " WHERE id=" . $row['id'];
        $db->query($sql);
    }

    $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_ranks SET weight=" . $new_weight . " WHERE id=" . $id;
    $db->query($sql);

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CHANGE_WEIGHT_RANK', json_encode($array), $admin_info['admin_id']);
    $nv_Cache->delMod($module_name);
    nv_htmlOutput('OK_' . $id);
}

// Thay đổi hoạt động
if ($nv_Request->get_title('changestatus', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_absint('id', 'post', 0);

    // Kiểm tra tồn tại
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id=" . $id;
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }

    $status = empty($array['status']) ? 1 : 0;

    $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_ranks SET status = " . $status . " WHERE id = " . $id;
    $db->query($sql);

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_CHANGE_STATUS_RANK', json_encode($array), $admin_info['admin_id']);
    $nv_Cache->delMod($module_name);

    nv_htmlOutput("OK");
}

// Xóa
if ($nv_Request->get_title('delete', 'post', '') === NV_CHECK_SESSION) {
    $id = $nv_Request->get_absint('id', 'post', 0);

    // Kiểm tra tồn tại
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id=" . $id;
    $array = $db->query($sql)->fetch();
    if (empty($array)) {
        nv_htmlOutput('NO_' . $id);
    }

    // Xóa
    $sql = "DELETE FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id=" . $id;
    $db->query($sql);

    // Cập nhật thứ tự
    $sql = "SELECT id FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks ORDER BY weight ASC";
    $result = $db->query($sql);
    $weight = 0;

    while ($row = $result->fetch()) {
        ++$weight;
        $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_ranks SET weight=" . $weight . " WHERE id=" . $row['id'];
        $db->query($sql);
    }

    nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_DELETE_RANK', json_encode($array), $admin_info['admin_id']);
    $nv_Cache->delMod($module_name);

    nv_htmlOutput("OK");
}

$array = $error = [];
$is_submit_form = $is_edit = false;
$id = $nv_Request->get_absint('id', 'get', 0);

if (!empty($id)) {
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE id = " . $id;
    $result = $db->query($sql);
    $array = $result->fetch();

    if (empty($array)) {
        nv_info_die($lang_global['error_404_title'], $lang_global['error_404_title'], $lang_global['error_404_content']);
    }

    $is_edit = true;
    $caption = $lang_module['rank_edit'];
    $form_action = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $id;
} else {
    $array = [
        'id' => 0,
        'monthly_salary' => 0,
        'title' => '',
        'description' => '',
    ];

    $caption = $lang_module['rank_add'];
    $form_action = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
}

if ($nv_Request->get_title('save', 'post', '') === NV_CHECK_SESSION) {
    $is_submit_form = true;
    $array['title'] = nv_substr($nv_Request->get_title('title', 'post', ''), 0, 190);
    $array['description'] = $nv_Request->get_string('description', 'post', '');
    $array['monthly_salary'] = $nv_Request->get_title('monthly_salary', 'post', '');
    $array['monthly_salary'] = intval(str_replace([',', '.'], '', $array['monthly_salary']));

    // Xử lý dữ liệu
    $array['description'] = nv_nl2br(nv_htmlspecialchars(strip_tags($array['description'])), '<br />');

    // Kiểm tra trùng
    $is_exists = false;
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks WHERE title = :title" . ($id ? ' AND id != ' . $id : '');
    $sth = $db->prepare($sql);
    $sth->bindParam(':title', $array['title'], PDO::PARAM_STR);
    $sth->execute();
    if ($sth->fetchColumn()) {
        $is_exists = true;
    }

    if (empty($array['title'])) {
        $error[] = $lang_module['rank_error_title'];
    } elseif ($is_exists) {
        $error[] = $lang_module['rank_error_exists'];
    }
    if ($array['monthly_salary'] <= 0) {
        $error[] = $lang_module['rank_error_monthly_salary'];
    }

    if (empty($error)) {
        if (!$id) {
            $sql = "SELECT MAX(weight) weight FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks";
            $weight = intval($db->query($sql)->fetchColumn()) + 1;

            $sql = "INSERT INTO " . $db_config['prefix'] . "_" . $module_data . "_ranks (
                title, description, monthly_salary, weight, add_time, edit_time
            ) VALUES (
                :title, :description, " . $array['monthly_salary'] . ", " . $weight . ", " . NV_CURRENTTIME . ", 0
            )";
        } else {
            $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_ranks SET
                title = :title, monthly_salary=" . $array['monthly_salary'] . ", description = :description, 
                edit_time = " . NV_CURRENTTIME . "
            WHERE id = " . $id;
        }

        try {
            $sth = $db->prepare($sql);
            $sth->bindParam(':title', $array['title'], PDO::PARAM_STR);
            $sth->bindParam(':description', $array['description'], PDO::PARAM_STR, strlen($array['description']));
            $sth->execute();

            if ($id) {
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_EDIT_rank', json_encode($array), $admin_info['userid']);
            } else {
                nv_insert_logs(NV_LANG_DATA, $module_name, 'LOG_ADD_rank', json_encode($array), $admin_info['userid']);
            }

            $nv_Cache->delMod($module_name);
            nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        } catch (PDOException $e) {
            trigger_error(print_r($e, true));
            $error[] = $lang_module['errorsave'];
        }
    }
}

$array['description'] = nv_br2nl($array['description']);
$array['monthly_salary'] = $array['monthly_salary'] ? number_format($array['monthly_salary'], 0, ',', '.') : '';

$xtpl = new XTemplate('ranks.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('CAPTION', $caption);
$xtpl->assign('FORM_ACTION', $form_action);
$xtpl->assign('DATA', $array);

// Xuất danh sách
$sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_ranks ORDER BY weight ASC";
$array_ranks = $db->query($sql)->fetchAll();
$num = sizeof($array_ranks);

foreach ($array_ranks as $row) {
    $row['url_edit'] = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $row['id'];
    $row['status_render'] = empty($row['status']) ? '' : ' checked="checked"';
    $row['monthly_salary'] = number_format($row['monthly_salary'], 0, ',', '.');

    for ($i = 1; $i <= $num; ++$i) {
        $xtpl->assign('WEIGHT', [
            'w' => $i,
            'selected' => ($i == $row['weight']) ? ' selected="selected"' : ''
        ]);

        $xtpl->parse('main.loop.weight');
    }

    $xtpl->assign('ROW', $row);
    $xtpl->parse('main.loop');
}

// Hiển thị lỗi
if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

// Hiển thị nút thêm
if (!$is_edit) {
    $xtpl->parse('main.add_btn');
}

// Cuộn đến form
if ($is_submit_form or $is_edit) {
    $xtpl->parse('main.scroll');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
