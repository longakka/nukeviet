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

$page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
//$canonicalUrl = getCanonicalUrl($page_url, true, true);
$array_mod_title[] = [
    'catid' => 1,
    'title' => $lang_module['workout_confirm'],
    'link' => $page_url
];

$month = $nv_Request->get_int("month", "get,post", 0);
$year = $nv_Request->get_int("year", "get,post", 0);

if ($nv_Request->isset_request('change_confirm_surprise', 'get')) {
    $id = $nv_Request->get_int("id", "post", 0);
    $confirm_status = $nv_Request->get_int("confirm_status", "post", 0);
    $confirm_reason = $nv_Request->get_string("confirm_reason", "post", 0);
    $workout = $nv_Request->get_int("workout", "post", 0);
    if (empty($id)) nv_htmlOutput('ERROR');
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $statement = false;
    if ($OPCLASS->check_allow_confirm_workout($month, $year)) {
        $statement = $OPCLASS->update_confirm_workout($id, array('workout' => $workout, 'confirm_status' => $confirm_status, 'confirm_reason' => $confirm_reason));
        if ($statement) {
            $OPCLASS->send_email_from_leader_for_surprise($id);
        }
    }
    nv_htmlOutput($statement ? $lang_module['success'] : 'ERROR');
}

if ($nv_Request->isset_request('change_confirm_registry', 'get')) {
    $id = $nv_Request->get_int("id", "post", 0);
    $confirm = $nv_Request->get_int("confirm", "post", 0);
    $deny_reason = $nv_Request->get_string("deny_reason", "post", 0);
    if (empty($id)) nv_htmlOutput('ERROR');
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));

    $statement =$OPCLASS->editById($id, array(
        'confirm' => $confirm,
        'confirm_time'   => time(),
        'deny_reason' => $deny_reason
    ));
    if ($statement) {
        $OPCLASS->send_email_from_leader_for_registry($id, $confirm);
    }
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

if (!empty($month)) {
    $OPCLASS->setMonth($month);
}
if (!empty($year)) {
    $OPCLASS->setYear($year);
}
$contents = empty($OPCLASS->HR->user_info['leader']) ? "<div class='panel panel-default'><div class='panel-body'><h2>". $lang_module['for_leader'] ."</h2></div></div>"
 : $OPCLASS->html_workout_confirm();
$OPCLASS->sendClientUser($contents);
