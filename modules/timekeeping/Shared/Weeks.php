<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace NukeViet\Module\timekeeping\Shared;

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

/**
 * @author VINADES.,JSC
 *
 */
class Weeks
{
    /**
     * @param number $month
     * @param number $year
     * @param number $mode 0 thì xuất tuần số 1 là tuần có chứa ngày 1 của tháng, 1 thì xuất tuần 1 là tuần có chứa ngày thứ 7 đầu tiên
     * @return []
     */
    public static function get7thDayOfMonth($month = 0, $year = 0, $mode = 0)
    {
        if (empty($month)) {
            $month = intval(date('n'));
        }
        if (empty($year)) {
            $year = intval(date('Y'));
        }

        // Xác định ngày 1 của tháng
        $start_month = mktime(0, 0, 0, $month, 1, $year);
        // Số ngày trong tháng
        $number_day = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        // Xác định ngày cuối cùng của tháng
        $end_month = $start_month + (86400 * $number_day) - 1;

        // Xác định ngày đầu tuần 1
        $day_of_week = intval(date('N', $start_month));
        $start_week = $start_month - (($day_of_week - 1) * 86400);
        $this_day = $start_week + (5 * 86400);

        // Tính lại tuần 1 là tuần có chứa thứ 7 đầu tiên
        if ($mode != 0 and $this_day < $start_month) {
            $this_day += (7 * 86400);
        }

        $weeks = [];
        $stt = 1;
        while (1) {
            $weeks[$stt++] = [
                'timestamp' => $this_day,
                'd' => intval(date('j', $this_day)),
                'm' => intval(date('n', $this_day)),
                'y' => intval(date('Y', $this_day)),
                'view_day' => date('d/m/Y', $this_day),
            ];

            $this_day += (7 * 86400);
            if ($this_day > $end_month) {
                break;
            }
        }

        return $weeks;
    }
}
