<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */
define('NV_SYSTEM', true);

use Modules\timekeeping\classs\Cron;
$_SERVER['HTTPS'] = 'on';
$_SERVER['HTTP_HOST'] = 'vinades.vn';
$_SERVER['SERVER_NAME'] = 'vinades.vn';
$_SERVER['SERVER_PROTOCOL'] = 'http';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '';
$_SERVER['PHP_SELF'] = '';
$_GET['language'] = 'vi';

// Xac dinh thu muc goc cua site
define('NV_CONSOLE_DIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME))));
if (NV_CONSOLE_DIR == '/home/vinades/private') {
    define('NV_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME) . '/../public_html')));
} else {
    define('NV_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', __file__), PATHINFO_DIRNAME) . '/..')));
    if(NV_ROOTDIR == 'D:/VINADES/module-timekeeping') {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'timekeeping.my';
    }
}

require NV_ROOTDIR . '/includes/mainfile.php';
date_default_timezone_set($global_config['statistics_timezone']);
$module_name = 'timekeeping';
$module_file = 'timekeeping';
$site_mods[$module_name] = array(
    'module_data' => 'timekeeping',
    'module_file' => 'timekeeping',
    'module_upload' => 'timekeeping',
    'template' => 'timekeeping',
);
require NV_ROOTDIR . '/modules/' . $module_file . '/language/' . NV_LANG_INTERFACE . '.php';
function checkout_member()
{
    $cron = new Cron();
    $cron->checkout_member();

}
echo "Begin: checkout-member<br />\n";
checkout_member();//
echo "END: checkout-member<br />\n";
exit();
/*
Cách chạy:
cd vào thư mục php: cd C:\xampp\php74
chạy file php: php  D:\VINADES\module-timekeeping\private\check-not-checkout.php
*/
