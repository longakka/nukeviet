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
$id = $nv_Request->get_int('id', 'post,get', 0);

if ($nv_Request->isset_request('list_userid', 'post, get')) {
    $search = $nv_Request->get_title("search", "post,get", "");
    $data= $OPCLASS->getStaff($search);
    nv_jsonOutput($data);
}
$contents = empty($OPCLASS->HR->user_info['leader']) ? "<div class='panel panel-default'><div class='panel-body'><h2>". $lang_module['for_leader'] ."</h2></div></div>" 
 : $OPCLASS->htmlAdd($id);
$OPCLASS->sendClientUser($contents);
