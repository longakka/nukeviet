<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 2-9-2010 14:43
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$error = '';
$page_title = $lang_module['config_email'];

if ($nv_Request->isset_request('submit', 'post')) {
    $email_vp = $nv_Request->get_title('email_vp', 'post', '');
    $error = nv_check_valid_email ( $email_vp );
    if (empty($error)) {
        $sth_1 = $db->prepare("SELECT * FROM " . NV_CONFIG_GLOBALTABLE . " WHERE  lang = '" . NV_LANG_DATA . "' AND module = :module_name AND config_name = 'email_vp'");
        $sth_1->bindParam(':module_name', $module_name, PDO::PARAM_STR);
        $sth_1->execute();
        if ($sth_1->fetchColumn()) {
            $sql1 = "UPDATE " . NV_CONFIG_GLOBALTABLE . " SET config_value = :config_value WHERE lang = '" . NV_LANG_DATA . "' AND module = :module_name AND config_name = 'email_vp'";
        } else {
            $sql1 = "INSERT INTO " . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . NV_LANG_DATA . "', :module_name, 'email_vp', :config_value)";
        }

        try {
            $sth = $db->prepare($sql1);
            $sth->bindParam(':module_name', $module_name, PDO::PARAM_STR);
            $sth->bindParam(':config_value', $email_vp, PDO::PARAM_STR);
            $result = $sth->execute();
            $nv_Cache->delMod('settings');
            nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
        } catch (PDOException $e) {
            nv_htmlOutput($lang_module['errorsave']);
        }

    }
}

$email_vp = '';
if (isset($module_config[$module_name]['email_vp'])) {
    $email_vp = $module_config[$module_name]['email_vp'];
}
$xtpl = new XTemplate('config_email.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('GLANG', $lang_global);
$xtpl->assign('FORM_ACTION', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
$xtpl->assign('DATA', $email_vp);

if (!empty($error)) {
    $xtpl->assign('ERROR', $error);
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
