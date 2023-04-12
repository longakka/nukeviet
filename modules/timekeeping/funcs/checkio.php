<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */

if (!defined('NV_IS_MOD_TIMEKEEPING')) {
    die('Stop!!!');
}

$page_title = $lang_module['check_in_out'];
$page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$canonicalUrl = getCanonicalUrl($page_url);

$array_mod_title[] = [
    'catid' => 1,
    'title' => $lang_module['checkio'],
    'link' => $page_url
];

// Lấy trạng thái làm việc là chính thức hay làm thêm
if ($nv_Request->isset_request('status_work', 'post')) {
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    // Cập nhật lại trạng thái của status_work
    $OPCLASS->checkInAllow();
    nv_jsonOutput([
        'status_work' => $OPCLASS->status_work,
        'workout' => $OPCLASS->workout
    ]);
}

// Submit check in
if ($nv_Request->isset_request('checkin', 'post')) {
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $data_post = $OPCLASS->dataPost();
    $statement = $OPCLASS->save($data_post);
    if ($statement and NV_CLIENT_IP != '127.0.0.1') {
        $status = $OPCLASS->status_work == 'worktime' ? 'check-in' : $lang_module['workout'];
        $OPCLASS->sendAPI($status, $data_post['place'], $data_post['description']);
    }
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

// Không hỏi lại check in vào giờ làm việc nữa
if ($nv_Request->isset_request('checkin_cancel', 'post')) {
    $nv_Request->set_Session('timekeeping_checkin_cancel', 1);
}

// Submit check out
if ($nv_Request->isset_request('checkout', 'post')) {
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $statement = $OPCLASS->checkOut();
    // Nếu là làm thêm đột xuất thì gửi gmail về cho lãnh đạo
    if ($statement) {
        if (($OPCLASS->status_last['status'] == 'workout') && empty($OPCLASS->status_last['workout_id'])) {
            $OPCLASS->sendEmailManageWorkout();
        }
        if (NV_CLIENT_IP != '127.0.0.1') {
            $OPCLASS->sendAPI('check-out');
        }
    }
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

// HTML page
$contents = $OPCLASS->html();
$OPCLASS->sendClientUser($contents);
