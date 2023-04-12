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
$id = $nv_Request->get_int('id', 'post,get', 0);

if ($nv_Request->isset_request('list', 'get')) {
    nv_htmlOutput($OPCLASS->htmlList());
}
if ($nv_Request->isset_request('cWeight, id', 'post')) {
    $id = $nv_Request->get_int('id', 'post', 0);
    $cWeight = $nv_Request->get_int('cWeight', 'post');
    $statement = $OPCLASS->updateWeight($id, $cWeight);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
$contents = $OPCLASS->html($id);
$OPCLASS->sendClientAdmin($contents);
