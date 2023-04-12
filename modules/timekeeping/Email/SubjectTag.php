<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace NukeViet\Module\timekeeping\Email;

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

/**
 * @author VINADES.,JSC
 *
 */
class SubjectTag
{
    /**
     * @param string $full_name
     * @param int $year
     * @param int $userid
     * @return string
     */
    public static function getLeaveAbsence($full_name, $year, $userid)
    {
        global $lang_module, $global_config;
        $global_config['custom_configs']['custom_mail_references'] = 'leave-absence-u' . $userid . '-y' . $year . '@' . NV_SERVER_NAME;
        return $full_name . ' ' . $lang_module['email_subject_leave'] . ' ' . $year;
    }

    /**
     * @param string $full_name
     * @param int $year
     * @param int $userid
     * @return string
     */
    public static function getDiaryPenalize($full_name, $year, $userid)
    {
        global $lang_module, $global_config;
        $global_config['custom_configs']['custom_mail_references'] = 'diary-penalize-u' . $userid . '-y' . $year . '@' . NV_SERVER_NAME;
        return $full_name . ' ' . $lang_module['email_subject_penalize'] . ' ' . $year;
    }

    /**
     * @param string $full_name
     * @param int $year
     * @param int $userid
     * @return string
     */
    public static function getWorkOut($full_name, $year, $userid)
    {
        global $lang_module, $global_config;
        $global_config['custom_configs']['custom_mail_references'] = 'workout-u' . $userid . '-y' . $year . '@' . NV_SERVER_NAME;
        return $full_name . ' ' . $lang_module['email_subject_workout'] . ' ' . $year;
    }

    /**
     * Email xin duyệt công làm thêm đột xuất
     *
     * @param string $full_name
     * @param int $year
     * @param int $userid
     * @return string
     */
    public static function getWorkOutSurprise($full_name, $year, $userid)
    {
        global $lang_module, $global_config;
        $global_config['custom_configs']['custom_mail_references'] = 'workoutsurprise-u' . $userid . '-y' . $year . '@' . NV_SERVER_NAME;
        return $full_name . ' ' . $lang_module['email_subject_workout_surprise'] . ' ' . $year;
    }
}
