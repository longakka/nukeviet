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
$userid = $nv_Request->get_int('userid', 'get, post', 0);
if ($nv_Request->isset_request('savesetting', 'post')) {
    if (empty($userid)) {
        nv_htmlOutput($lang_module['error_you_need_choice_staff']);
    }
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $data_post = $OPCLASS->dataPost();
    $statement = $OPCLASS->saveConfig($data_post);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
if ($nv_Request->isset_request('list_userid', 'post, get')) {
    $search = $nv_Request->get_title("search", "post,get", "");
    $data= $OPCLASS->getStaff($search);
    nv_jsonOutput($data);
}
$contents = $OPCLASS->htmlAdd($userid);
$OPCLASS->sendClientAdmin($contents);
