<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

define('NV_SYSTEM', true);

// Xac dinh thu muc goc cua site
define('NV_ROOTDIR', pathinfo(str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__) . '/..'), PATHINFO_DIRNAME));
require NV_ROOTDIR . '/includes/mainfile.php';
$module_data = 'timekeeping';

function calculateWorkHour($checkinTimestamp, $checkoutTimestamp)
{
    $timeCheckio = array(
        array(
            'checkin' => 480, // tuong ung luc 8 gio (8*60=480)
            'checkout' => 720
        ),
        array(
            'checkin' => 810,
            'checkout' => 1050
        )
    );
    $work_time_allow = array_fill(0, 24 * 60, false);
    foreach ($timeCheckio as $time_range) {
        for ($i = $time_range['checkin']; $i < $time_range['checkout']; $i++) {
            $work_time_allow[$i] = true;
        }
    }
    $timeCheckIn = intval(date('G', $checkinTimestamp)) * 60 + intval(date('i', $checkinTimestamp));
    $timeCheckOut = intval(date('G', $checkoutTimestamp)) * 60 + intval(date('i', $checkoutTimestamp));
    $workday = 0;
    for ($i = $timeCheckIn; $i <= $timeCheckOut; $i++) {
        $workday += $work_time_allow[$i];
    }
    return $workday;
}

function update_workday()
{
    global $db, $module_data, $db_config;
    $table = 'checkio';
    // cho phép làm trễ 5 phút
    $late_allow = 5;
    // giờ bắt đầu làm việc 8h
    $time_checkin_common = 8 * 60 * 60;

    // giờ làm việc buổi chiều dành cho những người được cho phép checkin sau 9h30'
    $time_checkin_common2 = (13 * 60 + 30) * 60;
    // giờ nghĩ làm việc 17h30'
    $time_checkout_common = (17 * 60 + 30) * 60;
    // giờ checkin cho phép trước 9h30'
    $time_checkin_allow = (9 * 60 + 30) * 60;
    $db->sqlreset()
        ->select('*')
        ->from($db_config['prefix'] . '_' . $module_data . '_' . $table)
        ->order('day ASC');
    $result = $db->query($db->sql());
    $array_data = array();
    while ($row = $result->fetch(2)) {
        $first_time = mktime(0, 0, 0, date('n', $row['checkin_real']), date('j', $row['checkin_real']), date('Y', $row['checkin_real']));
        $late = 0;
        if (($row['checkin_real'] > ($first_time + $time_checkin_common + $late_allow * 60)) && ($row['checkin_real'] <= ($first_time + $time_checkin_allow))) {
            // chỉ tính thời gian làm trễ đối với nhân viên checkin trước 9h30'
            // những trường hợp checkin sau 9h30 là được cho phép nên sẽ không tinh làm trễ
            $late = $row['checkin_real'] - $first_time - $time_checkin_common;
            $late = ($late < 0) ? 0 : floor($late / 60);
        }

        $workday = 0;
        $auto = 0;
        // những nhân viên checkin sau 9h30' thì sẽ tính checkin vào buổi 2 lúc 13h30'
        // nhưng nếu nhân viên đó checkin sau 13h30' thì sẽ tính tại thời điểm checkin
        $checkin = ($row['checkin_real'] <= ($first_time + $time_checkin_allow)) ? $time_checkin_common : (($row['checkin_real'] <= ($first_time + $time_checkin_common2)) ? $time_checkin_common2 : $row['checkin_real'] - $first_time);
        $checkin += $first_time;
        $checkout = ($row['checkout_real'] < $first_time + $time_checkout_common) ? $row['checkout_real'] : $first_time + $time_checkout_common;
        // nếu quên checkout thì sẽ tính checkout đúng giờ nhưng sẽ đánh dấu $auto = 1;
        if (empty($row['checkout_real'])) {
            $auto = 1;
            $checkout = $first_time + $time_checkout_common;
        }

        $workday = calculateWorkHour($checkin, $checkout);
        $workday = ($workday > 0) ? round($workday / 60 / 8 * 100, 2) : 0;
        $array_data[$row['id']] = array(
            'workday' => $workday,
            'auto' => $auto,
            'late' => $late
        );
        /*
         * $row['late'] = $late;
         * $row['auto'] = $auto;
         * $row['workday'] = $workday;
         * $row['hour_in_real'] = date('G',$row['checkin_real']);
         * $row['m_in_real'] = date('i',$row['checkin_real']);
         * $row['s_in_real'] = date('s',$row['checkin_real']);
         * $row['hour_out_real'] =!empty($row['checkout_real']) ? date('G',$row['checkout_real']) : 0;
         * $row['m_out_real'] = !empty($row['checkout_real']) ? date('i',$row['checkout_real']) : 0;
         * $row['hour_in'] = date('G',$checkin);
         * $row['m_in'] = date('i',$checkin);
         * $row['hour_out'] = date('G',$checkout);
         * $row['m_out'] = date('i',$checkout);
         */
    }
    $stmt = $db->prepare('UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_' . $table . ' SET workday= :workday, auto= :auto, late=:late WHERE id = :id');
    foreach ($array_data as $id => $row) {
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':workday', $row['workday'], PDO::PARAM_INT);
        $stmt->bindParam(':auto', $row['auto'], PDO::PARAM_INT);
        $stmt->bindParam(':late', $row['late'], PDO::PARAM_INT);
        $stmt->execute();
    }
}
update_workday();
echo 'het';
exit();
