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

if ($nv_Request->isset_request('savesetting', 'post')) {
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $data_post = $OPCLASS->dataPost();
    $statement = $OPCLASS->saveConfig($data_post);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
$contents = $OPCLASS->html();
$OPCLASS->sendClientAdmin($contents);
