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

if ($nv_Request->isset_request('list', 'get')) {
    $parentid = $nv_Request->get_int('parentid', 'get', 0);
    nv_htmlOutput($OPCLASS->htmlList($parentid));
}
if ($nv_Request->isset_request('cWeight, id', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $cWeight = $nv_Request->get_int('cWeight', 'post');
    $statement = $OPCLASS->updateWeight($id, $cWeight);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
if ($nv_Request->get_int('change_status', 'post', 0)) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $status = $nv_Request->get_int('status', 'post', 1);
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $statement = $OPCLASS->editById($id, array('status' => $status));
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
$contents = $OPCLASS->html();
$OPCLASS->sendClientAdmin($contents);
