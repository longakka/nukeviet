<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 05/07/2010 09:47
 */

if (!defined('NV_ADMIN') or !defined('NV_MAINFILE')) {
    die('Stop!!!');
}

$module_version = [
    'name' => 'TimeKeeping', // Tieu de module
    'modfuncs' => 'assign-work,assign-work-content,fixed-work-schedule-content,fixed-work-schedule,leave-absence-department, leave-absence, leave-absence-add, work-report-compare,diary-penalize,diary-penalize-add,main,task,checkio,workout, workout-add, workout-confirm,business-trip,business-trip-add, statistics, work, project, work-report, work-report-list, work-report-department,project-statistics, penalize-summary, online-work', // Cac function co block
    'change_alias' => '',
    'submenu' => 'assign-work,assign-work-content,fixed-work-schedule-content,fixed-work-schedule,work, project, work-report, work-report-list, work-report-department, diary-penalize, work-report-compare, leave-absence',
    'is_sysmod' => 0, // 1:0 => Co phai la module he thong hay khong
    'virtual' => 0, // 1:0 => Co cho phep ao hao module hay khong
    'version' => '1.0.00', // Phien ban cua modle
    'date' => 'Monday, June 22, 2020 16:00:00 GMT+07:00', // Ngay phat hanh phien ban
    'author' => 'VINADES <contact@vinades.vn>', // Tac gia
    'note' => '', // Ghi chu
    'uploads_dir' => [
        $module_upload
    ],
    'files_dir' => []
];
