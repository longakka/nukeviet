<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

$per_page = $nv_Request->get_int('per_page', 'post, get', 50);
$page = $nv_Request->get_int('page', 'post, get', 1);
$page_title = $lang_module['list_human_resources'];

// Kiểm tra Slack
if ($nv_Request->get_title('hmrsnotislack', 'post', '') === NV_CHECK_SESSION) {
    if (!defined('NV_IS_AJAX')) {
        die('Wrong URL!!!');
    }

    $id = $nv_Request->get_absint('id', 'post', 0);
    $respon = [
        'text' => '',
        'classcolor' => 'danger',
        'success' => false
    ];

    $staff = $OPCLASS->getById($id);
    if (empty($staff)) {
        $respon['text'] = 'Staff not exists!!!';
        nv_jsonOutput($respon);
    }

    $staff['full_name'] = nv_show_name_user($staff['first_name'], $staff['last_name'], $staff['username']);

    $message = $lang_module['hmrs_check_slack1'] . ' ' . $staff['full_name'] . ' (<@' . nv_strtolower($staff['username']) . '>) ';
    $OPCLASS->sendSlack(urlencode($message), '#vinades');

    $respon['success'] = true;
    $respon['classcolor'] = 'success';
    $respon['text'] = $lang_module['success'];

    nv_jsonOutput($respon);
}

if ($nv_Request->isset_request('cWeight, id', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $cWeight = $nv_Request->get_int('cWeight', 'post');
    $statement = $OPCLASS->updateWeight($id, $cWeight);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
$contents = $OPCLASS->html($per_page, $page);
$OPCLASS->sendClientAdmin($contents);
