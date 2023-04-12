<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/31/2009 0:51
 */

if (!defined('NV_SYSTEM')) {
    die('Stop!!!');
}

date_default_timezone_set($global_config['statistics_timezone']);

define('NV_IS_MOD_TIMEKEEPING', true);
$cron_funcs = array('check-not-checkout, checkout-member');
$is_cron_funcs = in_array($op, $cron_funcs);
// Chỉ thành viên mới vào đây được
if (!defined('NV_IS_USER') && !$is_cron_funcs) {
    $page_title = $lang_module['info'];
    $url = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=users&amp;' . NV_OP_VARIABLE . '=login&amp;nv_redirect=' . nv_redirect_encrypt($client_info['selfurl']), true);
    $contents = nv_theme_alert($lang_module['info'], $lang_module['info_login'], 'info', $url, $lang_module['info_redirect_click'], 5);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

/**
 * Cấu hình một số ngôn ngữ dùng cho javascript
 */
$lang_timekeeping = [
    'where_are_you' => $lang_module['where_are_you'],
    'reason_workout' => $lang_module['reason_workout'],
    'please_waiting' => $lang_module['please_waiting'],
    'checkout_confirm' => $lang_module['checkout_confirm'],
    'cal_range_today' => $lang_module['cal_range_today'],
    'cal_range_thisweek' => $lang_module['cal_range_thisweek'],
    'cal_range_lastweek' => $lang_module['cal_range_lastweek'],
    'cal_range_thismonth' => $lang_module['cal_range_thismonth'],
    'cal_range_lastmonth' => $lang_module['cal_range_lastmonth'],
    'cal_range_thisyear' => $lang_module['cal_range_thisyear'],
    'cal_range_lastyear' => $lang_module['cal_range_lastyear'],
    'cal_range_all' => $lang_module['cal_range_all'],
    'cancel' => $lang_module['cancel'],
    'apply' => $lang_module['apply'],
    'other' => $lang_module['other'],
    'Geolocation_is_not_supported_by_this_browser' => $lang_module['Geolocation_is_not_supported_by_this_browser'],
    'You_have_to_brower_identify_your_address' => $lang_module['You_have_to_brower_identify_your_address'],
    'Location_information_is_unavailable' => $lang_module['Location_information_is_unavailable'],
    'The_request_to_get_user_location_timed_out' => $lang_module['The_request_to_get_user_location_timed_out'],
    'error_identify_location' => $lang_module['error_identify_location'],
];
$my_head .= '<script type="text/javascript">window.lang_module = ' . json_encode($lang_timekeeping) . ';</script>';
$my_head .= '<script src="' . NV_BASE_SITEURL . NV_ASSETS_DIR . '/js/jquery/jquery.cookie.js" type="text/javascript"></script>';
$my_head .= '<link rel="stylesheet" href="' . NV_BASE_SITEURL . 'themes/default/images/' . $module_file . '/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="' . NV_BASE_SITEURL . 'themes/default/images/' . $module_file . '/daterangepicker/daterangepicker-luna.css">';

$my_footer .= '<script type="text/javascript" src="' . NV_BASE_SITEURL . 'themes/default/images/' . $module_file . '/daterangepicker/moment-with-locales.min.js"></script>
<script type="text/javascript" src="' . NV_BASE_SITEURL . 'themes/default/images/' . $module_file . '/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="' . NV_BASE_SITEURL . 'themes/default/images/' . $module_file . '/daterangepicker/daterangepicker-luna.js"></script>';

$HR = new \Modules\timekeeping\classs\HumanResources();

if (empty($HR->setMemberInfor($user_info['userid'], true)) &&  !$is_cron_funcs) {
    $page_title = $lang_module['info'];
    $contents = nv_theme_alert($lang_module['info'], $lang_module['info_not_member']);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}
$other_op = [
    'work',
    'project',
    'work-report',
    'work-report-list',
    'work-report-department',
    'project-statistics',
    'diary-penalize',
    'penalize-summary',
    'diary-penalize-add',
    'work-report-compare',
    'leave-absence-add',
    'leave-absence',
    'leave-absence-department',
    'fixed-work-schedule',
    'fixed-work-schedule-content',
    'assign-work',
    'assign-work-content',
    'online-work',
];
if (!in_array($op, $other_op)) {
    switch ($op) {
        case 'checkio':
            $OPCLASS = new \Modules\timekeeping\classs\Checkio($HR);
            break;
        case 'statistics':
            $OPCLASS = new \Modules\timekeeping\classs\StatisticsUser($HR);
            break;
        case 'workout':
            $OPCLASS = new \Modules\timekeeping\classs\WorkOut();
            break;
        case 'workout-add':
            $OPCLASS = new \Modules\timekeeping\classs\WorkOut();
            break;
        case 'workout-confirm':
            $OPCLASS = new \Modules\timekeeping\classs\WorkOut();
            break;
        case 'business-trip':
            $OPCLASS = new \Modules\timekeeping\classs\BusinessTrip();
            break;
        case 'business-trip-add':
            $OPCLASS = new \Modules\timekeeping\classs\BusinessTrip();
            break;
        case 'task':
            $OPCLASS = new \Modules\timekeeping\classs\Task();
            break;
        case 'check-not-checkout':
            $OPCLASS = new \Modules\timekeeping\classs\Cron();
            break;
        default:
            header('Location: ' . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=checkio', true));
            die();
            break;
    }
    $OPCLASS->setHr($HR);
}
require_once NV_ROOTDIR . '/modules/' . $module_file . '/global.functions.php';
