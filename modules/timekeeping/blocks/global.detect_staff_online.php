<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_MAINFILE')) {
    exit('Stop!!!');
}
if (!nv_function_exists('detect_staff_online')) {
    /**
     * detect_staff_online()
     *
     * @param array $block_config
     * @return string|void
     */
    function detect_staff_online($block_config)
    {
        global $global_config, $user_info, $site_mods, $nv_Request;
        
        $checkin_cancel = $nv_Request->get_int('timekeeping_checkin_cancel', 'session', 0);
        if (!defined('NV_IS_USER') || ($checkin_cancel == 1)) {
            return null;
        }

        $module_file = $site_mods[$block_config['module']]['module_file'];
        if (file_exists(NV_ROOTDIR . '/modules/' . $module_file . '/language/' . NV_LANG_INTERFACE . '.php')) {
            require NV_ROOTDIR . '/modules/' . $module_file . '/language/' . NV_LANG_INTERFACE . '.php';
        } 
       
        $HR = new \Modules\timekeeping\classs\HumanResources();
        $CHECKIO = new \Modules\timekeeping\classs\Checkio($HR);
        if ($CHECKIO->checkOutAllow()) {
            return null;
        }
        $CHECKIO->checkInAllow();
        if ($CHECKIO->status_work !== 'worktime') {
            return null;
        }

        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/timekeeping/block_detect_staff_online.tpl')) {
            $block_theme = $global_config['module_theme'];
        } else {
            $block_theme = 'default';
        }

        $xtpl = new XTemplate('block_detect_staff_online.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/timekeeping');
        $xtpl->assign('LANG', $lang_module);
        $xtpl->assign('module_name', $block_config['module']);
        $xtpl->assign('CHECKSS', $CHECKIO->checkss);
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
if (defined('NV_SYSTEM')) {
    global $site_mods, $module_name, $global_array_cat, $module_array_cat, $nv_Cache, $module_data;
    $module = $block_config['module'];

    $url_current = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
    $url_current = nv_url_rewrite($url_current, true);

    $url_timekeeping_checkio = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=checkio';
    $url_timekeeping_checkio = nv_url_rewrite($url_timekeeping_checkio, true);

    if (isset($site_mods[$module]) && ($url_current != $url_timekeeping_checkio)) {
        $module_name_cr = $module_name;
        if ($module !== $module_name) {
            $module_name = $module;
        }
        $content = detect_staff_online($block_config);
        $module_name = $module_name_cr;
    }
}
