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
$month = $nv_Request->get_int("month", "get,post", 0);
$year = $nv_Request->get_int("year", "get,post", 0);
if (!empty($month)) {
    $OPCLASS->setMonth($month);
}
if (!empty($year)) {
    $OPCLASS->setYear($year);
}
$contents = $OPCLASS->html();
$OPCLASS->sendClientUser($contents);
