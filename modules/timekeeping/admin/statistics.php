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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$statistics_type = $nv_Request->get_title('statistics_type', 'get', 'statistics_month');
$per_page = $nv_Request->get_int('per_page', 'post, get', 50);
$page = $nv_Request->get_int('page', 'post, get', 1);
$month = $nv_Request->get_int("month", "get,post", 0);
$year = $nv_Request->get_int("year", "get,post", 0);
$value_export = $nv_Request->get_title("xuatbaocao", "post", "");
$data = [];

if (!empty($month)) {
    $OPCLASS->setMonth($month);
}
if (!empty($year)) {
    $OPCLASS->setYear($year);
}

/*
 * $data['month'] = 5;
 * $data['year'] = 2021;
 * $spreadsheet = new Spreadsheet();
 * $writer = new Xlsx($spreadsheet);
 * $OPCLASS->export_ChamCong($data, $spreadsheet, $writer, $admin_info['username'], $admin_info['position']);
 */
if ($value_export == 'xuatbaocao') {
    $data['month'] = $month;
    $data['year'] = $year;
    $spreadsheet = new Spreadsheet();
    $writer = new Xlsx($spreadsheet);
    $OPCLASS->export_ChamCong($data, $spreadsheet, $writer, $admin_info['username'], $admin_info['position']);
}

$contents = $OPCLASS->html($statistics_type, $per_page, $page);
$OPCLASS->sendClientAdmin($contents);
