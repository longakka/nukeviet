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

$per_page = $nv_Request->get_int('per_page', 'post, get', 30);
$page = $nv_Request->get_int('page', 'post, get', 1);
$page_title = $lang_module['list_config_member'];

if ($nv_Request->get_int('delete_config_member', 'post', 0)) {
    $userid = $nv_Request->get_int('delete_config_member', 'post', 0);
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $statement = $OPCLASS->deleteAll('userid=' . $userid);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
$contents = $OPCLASS->html($per_page, $page);
$OPCLASS->sendClientAdmin($contents);
