<?php

use NukeViet\Module\timekeeping\Shared\Times;

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

if (!nv_function_exists('nv_block_online_staff')) {
    /**
     * @param string $module
     * @param array  $data_block
     * @param array  $lang_block
     * @return string
     */
    function nv_block_config_online_staff($module, $data_block, $lang_block)
    {
        global $nv_Cache, $site_mods, $db_config;

        $html = '';
        $html .= '<div class="form-group">';
        $html .= '<label class="control-label col-sm-6">' . $lang_block['hide_department_ids'] . ':</label>';
        $html .= '<div class="col-sm-18">';

        $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $site_mods[$module]['module_data'] . '_department ORDER BY sort ASC';
        $department_array = $nv_Cache->db($sql, 'id', $module);

        foreach ($department_array as $department) {
            $space = '';
            if ($department['lev'] > 0) {
                for ($i = 1; $i <= $department['lev']; ++$i) {
                    $space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            }

            $html .= '
            <div class="checkbox">
                <label>' . $space . '<input type="checkbox" name="config_hide_department_ids" value="' . $department['id'] . '"/> ' . $department['title'] . '</label>
            </div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param string $module
     * @param array  $lang_block
     * @return array
     */
    function nv_block_config_online_staff_submit($module, $lang_block)
    {
        global $nv_Request;
        $return = [];
        $return['error'] = [];
        $return['config'] = [];
        $return['config']['hide_department_ids'] = $nv_Request->get_typed_array('config_hide_department_ids', 'post', 'int', []);

        return $return;
    }

    /**
     *
     * @param array $block_config
     * @return string|void
     */
    function nv_block_online_staff($block_config)
    {
        global $site_mods, $global_config, $nv_Cache, $db, $db_config, $user_info;

        if (!defined('NV_IS_USER')) {
            return '';
        }

        $module = $block_config['module'];
        $module_data = $site_mods[$module]['module_data'];
        $module_file = $site_mods[$module]['module_file'];

        // Phòng ban
        $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department ORDER BY sort ASC';
        $department_array = $nv_Cache->db($sql, 'id', $module);

        /*
         * Xác định mình phải nhân viên còn làm việc hay không?
         * Nếu không là nhân viên thì không được xem danh sách
         */
        $sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources
        WHERE userid=' . $user_info['userid'] . ' AND still_working=1';
        $my_staff = $db->query($sql)->fetch();
        if (empty($my_staff)) {
            return '';
        }

        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file . '/block.online_staff.tpl')) {
            $block_theme = $global_config['module_theme'];
        } else {
            $block_theme = 'default';
        }
        if (file_exists(NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/css/' . $module_file . '.css')) {
            $block_css = $global_config['module_theme'];
        } else {
            $block_css = 'default';
        }

        $lang_module = [];
        include NV_ROOTDIR . '/modules/' . $module_file . '/language/' . NV_LANG_INTERFACE . '.php';

        $xtpl = new XTemplate('block.online_staff.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/' . $module_file);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('LANG', $lang_module);
        $xtpl->assign('TEMPLATE', $block_theme);
        $xtpl->assign('TEMPLATE_CSS', $block_css);
        $xtpl->assign('MODULE_FILE', $module_file);

        $sql = "SELECT tb1.userid, tb1.department_id, tb2.username, tb2.first_name, tb2.last_name FROM
        " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb1
        INNER JOIN " . NV_USERS_GLOBALTABLE . " tb2 ON tb1.userid=tb2.userid
        WHERE tb2.active=1 AND still_working=1 ORDER BY tb1.weight ASC";
        $result = $db->query($sql);

        $array_staffs = $array_useris = [];
        while ($row = $result->fetch()) {
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name']);
            $row['show_name'] = $row['full_name'] . ' (' . $row['username'] . ')';
            $array_staffs[$row['department_id']][$row['userid']] = $row;
            $array_useris[$row['userid']] = $row['userid'];
        }

        // Lấy thông tin checkin mà chưa checkout
        $checkin = [];
        if (!empty($array_useris)) {
            // Lấy cấu hình bắt đầu ngày làm việc
            $sql = "SELECT config_value FROM " . $db_config['prefix'] . "_" . $module_data . "_config
            WHERE userid=0 AND config_name='timeCheckio' ORDER BY year DESC, month DESC LIMIT 1";
            $timeCheckio = unserialize($db->query($sql)->fetchColumn());
            $day_start = Times::getDayStartTime() + (($timeCheckio[0]['checkin'] - 30) * 60);

            if (NV_CURRENTTIME >= $day_start) {
                $days = Times::getCurrentDay();
            } else {
                $days = Times::getBeforeDay();
            }

            $sql = "SELECT userid, GROUP_CONCAT(checkin_real ORDER BY checkin_real DESC) checkin_real,
            GROUP_CONCAT(checkout_real ORDER BY checkout_real DESC) checkout_real
            FROM " . $db_config['prefix'] . "_" . $module_data . "_checkio
            WHERE day=" . $days['d'] . " AND month=" . $days['m'] . " AND year=" . $days['y'] . "
            GROUP BY userid";
            $result = $db->query($sql);

            while ($row = $result->fetch()) {
                $row['checkin_real'] = explode(',', $row['checkin_real']);
                $row['checkout_real'] = explode(',', $row['checkout_real']);

                $checkin_real = array_pop($row['checkin_real']);
                $checkout_real = array_pop($row['checkout_real']);

                if (empty($checkout_real)) {
                    $checkin[$row['userid']] = $row;
                }
            }
        }

        // Lặp xuất theo thứ tự phòng ban
        $data = 0;
        foreach ($department_array as $department) {
            if (isset($array_staffs[$department['id']]) and !in_array($department['id'], $block_config['hide_department_ids'])) {
                $xtpl->assign('DEPARTMENT', $department);

                $num = 0;
                foreach ($array_staffs[$department['id']] as $staff) {
                    if (isset($checkin[$staff['userid']])) {
                        $num++;
                        $xtpl->assign('STAFF', $staff);
                        $xtpl->parse('main.department.loop');
                    }
                }

                if ($num > 0) {
                    $data++;
                    $xtpl->parse('main.department');
                }
            }
        }

        if (empty($data)) {
            $xtpl->parse('main.empty');
        }

        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}

if (defined('NV_SYSTEM')) {
    global $site_mods;
    $module = $block_config['module'];
    if (isset($site_mods[$module])) {
        $content = nv_block_online_staff($block_config);
    }
}
