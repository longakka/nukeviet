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
    'title' => $lang_module['workout_user'],
    'link' => $page_url
];

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
