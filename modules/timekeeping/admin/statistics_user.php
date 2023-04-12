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
$month = $nv_Request->get_int("month", "get,post", 0);
$year = $nv_Request->get_int("year", "get,post", 0);

if (!empty($month)) {
    $OPCLASS->setMonth($month);
}
if (!empty($year)) {
    $OPCLASS->setYear($year);
}
$OPCLASS->setDirTheme(NV_ROOTDIR . '/themes/default/modules/' . $module_file);
$contents = $OPCLASS->htmlAdmin();
$OPCLASS->sendClientAdmin($contents);

