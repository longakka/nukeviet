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

$page_title = $lang_module['add_penalize'];
$page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 1,
    'title' => $page_title,
    'link' => $page_url
];

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

$ids = $nv_Request->get_int('id', 'post,get', 0);
$diary_penalize = [];
if (!empty($ids)) {
    $sql = 'SELECT * FROM  ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize WHERE id = ' . $ids;
    $result = $db->query($sql);
    $diary_penalize = $result->fetch(2);
}
$base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;

// Lấy danh sách nhân viên thuộc phòng ban
$sql1 = "SELECT tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id
FROM " . NV_USERS_GLOBALTABLE . " tb1
INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb2 ON tb1.userid=tb2.userid
WHERE tb1.active=1 AND tb2.still_working=1
ORDER BY " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC";
$result1 = $db->query($sql1);
$array_staffs = [];
$array_department_staffs = [];
while ($row = $result1->fetch()) {
    $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name']);
    if (empty($array_staffs[$row['userid']])) {
        $array_staffs[$row['userid']] = $row['username'];
    } else {
        $array_staffs[$row['userid']] .= ' (' . $row['username'] . ')';
    }
    $array_department_staffs[0][$row['userid']] = $array_staffs[$row['userid']];
    $array_department_staffs[$row['department_id']][$row['userid']] = $array_staffs[$row['userid']];
}

// Thêm mới một nhật ký phạt
if ($nv_Request->isset_request('save_penalize', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $department = $nv_Request->get_int('d', 'post', 0);
    $userid = $nv_Request->get_int('u', 'post', 0);
    $reason = $nv_Request->get_title('reason', 'post', 0);
    $department_arr = GetlistDepartmentIdParent($department);
    if ((isset($leader_array[$user_info['userid']]) and in_array($leader_array[$user_info['userid']]['department_id'], $department_arr)) or $user_info['userid'] == $userid) {
        // Nếu nhân viên yêu cầu phạt là leader và cùng phòng ban với người bị phạt
        // Nếu tự phạt chính mình
        $confirm = 1;
        $userid_confirm = $user_info['userid'];
        $confirm_time = time();
    } else {
        $confirm = 0;
        $userid_confirm = 0;
        $confirm_time = 0;
    }
    if (!empty($department) and !empty($userid) and !empty($reason)) {
        // Kiểm tra tồn tại
        $sql = 'SELECT id FROM ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize
                WHERE reason=:reason AND department_id = ' . $department . ' AND userid = ' . $userid;
        $sth = $db->prepare($sql);
        $sth->bindParam(':reason', $reason, PDO::PARAM_STR);
        $sth->execute();
        if ($sth->fetchColumn()) {
            nv_htmlOutput($lang_module['errorpenalize']);
        } else {
            // Xử lý dữ liệu
            $reason = nv_nl2br(nv_htmlspecialchars(strip_tags($reason)), '<br />');
            if (!$id) {
                $sql = 'INSERT INTO ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize (
                    userid, department_id, reason, userid_add, userid_confirm, confirm, confirm_reason, confirm_time, link, penalize_time
                ) VALUES (
                    ' . $userid . ', ' . $department . ', :reason, ' . $HR->user_info['userid'] . ', ' . $userid_confirm . ', ' . $confirm . ', "", ' . $confirm_time . ', "", ' . NV_CURRENTTIME . '
                )';
            } else {
                $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_diary_penalize SET
                userid = ' . $userid . ', department_id = ' . $department . ', reason = :reason
                    WHERE id = ' . $id;
            }

            try {
                $sth = $db->prepare($sql);
                $sth->bindParam(':reason', $reason, PDO::PARAM_STR);
                $result = $sth->execute();

                /* Gửi email cho trưởng phòng, trưởng nhóm:
                * - nếu người yêu cầu phạt không phải leader.
                * - nếu người yếu cầu phạt là leader của phòng ban khác
                */

                if ($result and $user_info['userid'] != $userid) {
                    if (!isset($leader_array[$user_info['userid']]) or (isset($leader_array[$user_info['userid']]) and !in_array($leader_array[$user_info['userid']]['department_id'], $department_arr))) {
                        //nếu người yêu cầu phạt không phải là leader hoặc là leader của phòng ban khác thì gửi email cho leader
                        $leader_id_arr = [];
                        foreach ($leader_array as $key => $ld) {
                            if ($ld['department_id'] == $department) {
                                $leader_id_arr[] = $ld['userid'];
                            }
                        }
                        if (!empty($leader_id_arr)) {
                            $user_penalize_inf = $db->query('SELECT first_name,last_name,username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $userid)->fetch(2);
                            $sql = 'SELECT email FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid IN (' . implode(',', $leader_id_arr) . ')';
                            $user_list = $db->query($sql);
                            $full_name_user_add = nv_show_name_user($HR->user_info['first_name'], $HR->user_info['last_name'], $HR->user_info['username']);
                            while ($us = $user_list->fetch()) {
                                $full_name = nv_show_name_user($user_penalize_inf['first_name'], $user_penalize_inf['last_name'], $user_penalize_inf['username']);
                                $subject = $full_name_user_add . ' ' . $lang_module['sbj_email_penalize'] . ' ' . $full_name;
                                $email_subject = SubjectTag::getDiaryPenalize($full_name, date('Y'), $userid);
                                $message = "<h1>$subject</h1>";
                                $message .= "<p><strong>Lý do: </strong> $reason</p>";
                                $message .= "<p>" . $lang_module['mess_email_1'] . " <a target=\"_blank\" href=\"" . NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=diary-penalize&amp;d=" . $department . "&u=" . $userid, true) . "\">" . strtolower($lang_module['link_1']) . "</a> " . $lang_module['mess_email_2'] . ".</p>";
                                nv_sendmail($global_config['smtp_username'], $us['email'], $email_subject, $message, null, null, null);
                            }
                        }
                    } else if (isset($leader_array[$user_info['userid']]) and in_array($leader_array[$user_info['userid']]['department_id'], $department_arr)) {
                        // Nếu nhân viên yêu cầu phạt là leader và cùng phòng ban với người bị phạt thì gửi email cho nv bị phạt
                        $rs = $db->query('SELECT email, userid, first_name, last_name, username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $userid);
                        $results = $rs->fetch(2);
                        $full_name = '';
                        if (!empty($results)) {
                            $full_name = nv_show_name_user($results['first_name'], $results['last_name'], $results['username']);
                            $leader_fullname = nv_show_name_user($HR->user_info['first_name'], $HR->user_info['last_name'], $HR->user_info['username']);
                            $subject = $leader_fullname . ' ' . $lang_module['sbj_email_penalize'] . ' ' . $full_name;
                            $email_subject = SubjectTag::getDiaryPenalize($full_name, date('Y'), $userid);
                            $message = "<h1>$subject</h1>";
                            $message .= "<p><strong>" . $lang_module['reason'] . ": </strong> $reason</p>";
                            $message .= "<p><strong>". $lang_module['had_confirmed'] . ":</strong> $leader_fullname </p>";
                            $message .= "<p>" . $lang_module['mess_email_1'] . " <a target=\"_blank\" href=\"" . NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=diary-penalize&amp;d=" . $department . "&u=" . $userid, true) . "\">" . strtolower($lang_module['link_1']) . "</a> " . $lang_module['mess_email_3'] . ".</p>";
                            nv_sendmail($global_config['smtp_username'], $results['email'], $email_subject, $message, null, null, null);
                        }

                    }
                }
                nv_htmlOutput("OK");

            } catch (PDOException $e) {
                nv_htmlOutput($lang_module['errorsave']);
            }
        }
    }
}

if (count($diary_penalize) > 0) {
    $department_id_sl = $diary_penalize['department_id'];
    $userid_select = $diary_penalize['userid'];
} else {
    $department_id_sl = 0;
    $userid_select = 0;
}

$xtpl = new XTemplate('diary-penalize-add.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
$xtpl->assign('ACTION_URL', nv_url_rewrite($base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $op, true));
$xtpl->assign('OP', $op);
$xtpl->assign('LIST_DIARY_PENALIZE_URL', nv_url_rewrite($base_url_user . '&amp;' . NV_OP_VARIABLE . '=diary-penalize', true));
$xtpl->assign('diary_penalize', $diary_penalize);
$xtpl->assign('userid_select', $userid_select);
$xtpl->assign('page_title', $page_title);

// Xếp lại thứ tự
$json_department_staffs = [];
$json_department_staffs[0] = [
    'title' => $lang_module['choose_department'],
    'staffs' => $array_department_staffs[0]
];
foreach ($department_array as $department) {
    if (isset($array_department_staffs[$department['id']])) {
        $json_department_staffs[$department['id']] = [
            'title' => nv_get_full_department_title($department['id']),
            'staffs' => $array_department_staffs[$department['id']]
        ];
    }
}
$xtpl->assign('JSON_DATA', json_encode($json_department_staffs));
foreach ($json_department_staffs as $department_id => $department_data) {
    $xtpl->assign('DEPARTMENT', [
        'id' => $department_id,
        'title' => $department_data['title'],
        'selected' => $department_id == $department_id_sl ? ' selected="selected"' : '',
    ]);
    $xtpl->parse('main.department');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', $error);
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');
include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
