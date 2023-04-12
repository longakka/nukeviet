<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/31/2009 2:29
 */

if (!defined('NV_ADMIN') or !defined('NV_MAINFILE') or !defined('NV_IS_MODADMIN')) {
    die('Stop!!!');
}

define('NV_IS_FILE_ADMIN', true);

date_default_timezone_set($global_config['statistics_timezone']);

$array_normal_op = [
    'departments',
    'config_email',
    'rank'
];

if (!in_array($op, $array_normal_op)) {
    switch ($op) {
        case 'human_resources':
            $OPCLASS = new \Modules\timekeeping\classs\HumanResources();
            break;
        case 'human_resources_add':
            $OPCLASS = new \Modules\timekeeping\classs\HumanResources();
            break;
        case 'office':
            $OPCLASS = new \Modules\timekeeping\classs\Office();
            break;
        case 'statistics':
            $OPCLASS = new \Modules\timekeeping\classs\StatisticsAll();
            break;
        case 'statistics_user':
            $userid = $nv_Request->get_int('userid', 'get', 0);
            $HR = new \Modules\timekeeping\classs\HumanResources();
            if (empty($HR->setMemberInfor($userid))) {
                header('Location: ' . NV_BASE_SITEURL);
                exit();
            }
            $OPCLASS = new \Modules\timekeeping\classs\StatisticsUser($HR);
            break;
        case 'taskcat':
            $OPCLASS = new \Modules\timekeeping\classs\TaskCat();
            break;
        case 'taskcat_add':
            $OPCLASS = new \Modules\timekeeping\classs\TaskCat();
            break;
        case 'config':
            $month = $nv_Request->get_int("month", "get,post", 0);
            $year = $nv_Request->get_int("year", "get,post", 0);
            $OPCLASS = new \Modules\timekeeping\classs\Config(0, $month, $year);
            break;
        case 'config_member':
            $userid = $nv_Request->get_int('userid', 'get, post', 0);
            $month = $nv_Request->get_int("month", "get,post", 0);
            $year = $nv_Request->get_int("year", "get,post", 0);
            $OPCLASS = new \Modules\timekeeping\classs\ConfigMember($userid, $month, $year);
            break;
        case 'config_member_add':
            $userid = $nv_Request->get_int('userid', 'get, post', 0);
            $month = $nv_Request->get_int("month", "get,post", 0);
            $year = $nv_Request->get_int("year", "get,post", 0);
            $OPCLASS = new \Modules\timekeeping\classs\ConfigMember($userid, $month, $year);
            break;
    }
}

require_once NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';
