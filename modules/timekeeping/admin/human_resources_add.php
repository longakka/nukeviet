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

$userid = $nv_Request->get_int('userid', 'post,get', 0);
$page_title = $userid > 0 ? $lang_module['edit_human_resources'] : $lang_module['human_resources_add'];

if ($nv_Request->get_int('info_basic', 'post,get', 0)) {
    $username = $nv_Request->get_title('username', 'post', '');
    $htmlInfoHr = $OPCLASS->htmlInfoHr($username);
    nv_htmlOutput($htmlInfoHr);
}
if ($nv_Request->isset_request('list_department', 'post,get')) {

    $department_id = $nv_Request->get_int('department_id', 'post, get', 0);
    $userid = $nv_Request->get_int('userid', 'post, get', 0);
    $htmlDepartments = $OPCLASS->htmlDepartments($department_id, $userid);
    nv_htmlOutput($htmlDepartments);
}

$contents = $OPCLASS->htmlAdd($userid);
$OPCLASS->sendClientAdmin($contents);
