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

use Modules\timekeeping\classs\ConfigMember;
use Modules\timekeeping\classs\Checkio;
use NukeViet\Module\timekeeping\Email\SubjectTag;

$page_title = $lang_module['apply_leave'];
$page_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 1,
    'title' => $page_title,
    'link' => $page_url
];
$base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

// Lưu đơn xin nghỉ phép
if ($nv_Request->isset_request('save_leave_absence', 'post')) {
    $respon = [
        'message' => '',
        'success' => false
    ];

    $id = $nv_Request->get_int('id', 'post', 0);
    $reason = $nv_Request->get_title('reason', 'post', 0);
    $date_leave = $nv_Request->get_title('day', 'post', 0);
    $date_start_leave = $nv_Request->get_title('start_time', 'post', 0);
    $date_end_leave = $nv_Request->get_title('end_time', 'post', 0);
    $type_leave = $nv_Request->get_title('type_leave', 'post', 0);
    $half_day_start_morning = $nv_Request->get_title('half_day_start_morning', 'post', 0);
    $half_day_end_morning = $nv_Request->get_title('half_day_end_morning', 'post', 0);
    $half_day_start_afternoon = $nv_Request->get_title('half_day_start_afternoon', 'post', 0);
    $half_day_end_afternoon = $nv_Request->get_title('half_day_end_afternoon', 'post', 0);
    $use_absence_year = $nv_Request->get_title('use_absence_year', 'post', 0);
    $confirm = 0;
    $confirm_userid = 0;
    $department_id = $HR->user_info['department_id'];
    if (!empty($HR->user_info['leader'])) {
        $department_item = $db->query('SELECT parentid FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE id  = '. $department_id)->fetch(2);
        $department_id = $department_item['parentid'];
        if ($department_id == 0) {
            $confirm = 1;
            $confirm_userid = $HR->user_info['userid'];
        }
    }
    $leader_id_arr = [];
    foreach ($leader_array as $key => $ld) {
        if ($ld['department_id'] == $department_id) {
            $leader_id_arr[] = $ld['userid'];
        }
    }
    $is_date_valid = false;
    $where = '';

    // Lấy cấu hình ngày làm việc, ngày nghỉ lễ, tết của nhân viên
    $config_member = new ConfigMember($HR->user_info['userid']);
    $checkio = new Checkio($HR);
    $day_allow_of_week = $config_member->dayAllowOfWeek;
    $work_week = $config_member->work_week;
    $holidays = $config_member->holidays;
    $total_day = 0;
    $half_day_start = $half_day_end = 0;
    $title_date_str = '';
    $title_date_end = '';
    if ($half_day_start_morning == 1 and $half_day_start_afternoon == 0) {
        $half_day_start = 1;
        $title_date_str = $lang_module['morning'] . ' ';
    } else if ($half_day_start_morning == 0 and $half_day_start_afternoon == 1) {
        $half_day_start = 2;
        $title_date_str = $lang_module['afternoon'] . ' ';
    }
    if ($half_day_end_morning == 1 and $half_day_end_afternoon == 0) {
        $half_day_end = 1;
        $title_date_end = $lang_module['morning'] . ' ';
    } else if ($half_day_end_morning == 0 and $half_day_end_afternoon == 1) {
        $half_day_end = 2;
        $title_date_end = $lang_module['afternoon'] . ' ';
    }

    $time_range = NV_CURRENTTIME;

    if ($type_leave == 1) {
        // Trường hợp nghỉ cả ngày
        $time = $title_date_str . $date_start_leave . ' - ' . $title_date_end . $date_end_leave;
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $date_start_leave, $m)) {
            $start_leave = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
        }
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $date_end_leave, $m)) {
            $end_leave = mktime(23, 59, 59, $m[2], $m[1], $m[3]);
        }
        if ($start_leave > $end_leave) {
            $respon['message'] = $lang_module['error_endtime_less_startime'];
            nv_jsonOutput($respon);
        }
        $is_date_valid = true;
        $where_date = $start_leave;
        $date_leave = 0;
        $year = date('Y', $start_leave);
        $month = date('m', $start_leave);
        $day = date('d', $start_leave);
        // Lấy list các ngày xin nghỉ phép (từ startdate đến enddate)
        $period = new DatePeriod(
            new DateTime(date('Y-m-d H:i:s', $start_leave)),
            new DateInterval('P1D'),
            new DateTime(date('Y-m-d H:i:s', $end_leave))
        );
        foreach ($period as $key => $value) {
            $date = $value->format('d/m');
            $date_1 = $value->format('d/m/Y');
            $timestamp = strtotime($value->format('Y-m-d'));
            $weekday = date('w', $timestamp);
            $wd = $checkio->getNumberWeekOfMonth($timestamp);
            if (in_array($weekday, $day_allow_of_week) || ($weekday == 6 && in_array($wd, $work_week))) {
                // Nếu ngày nghỉ phép là các ngày làm việc trong tuần, hoặc thứ 7 làm việc
                if (!in_array($date, $holidays)) {
                    // Nếu không rơi vào ngày lễ, tết
                    $total_day++;
                    if ($date_start_leave != $date_end_leave) {
                        if ($half_day_start != 0 && $date_start_leave == $date_1) {
                            $total_day = $total_day - 0.5;
                        }
                        if ($half_day_end != 0 && $date_end_leave == $date_1) {
                            $total_day = $total_day - 0.5;
                        }
                    }
                }
            }
        }
        $time_range = $start_leave ?? NV_CURRENTTIME;
    } else {
        // Trường hợp nghỉ nửa ngày
        $time = $date_leave;
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $date_leave, $m)) {
            $date_leave = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
            $time_range = $date_leave;
            $year = date('Y', $date_leave);
            $date = date('d/m', $date_leave);
            $is_date_valid = true;
            $where_date = $date_leave;
            $start_leave = $end_leave = 0;
            $weekday = date('w', $date_leave);
            $wd = $checkio->getNumberWeekOfMonth($date_leave);
            if (in_array($weekday, $day_allow_of_week) || ($weekday == 6 && in_array($wd, $work_week))) {
                // Nếu ngày nghỉ phép là các ngày làm việc trong tuần, hoặc thứ 7 làm việc
                if (!in_array($date, $holidays)) {
                    // Nếu không rơi vào ngày lễ, tết
                    $total_day = 0.5;
                }
            }
        }
    }

    if ($total_day == 0) {
        $respon['message'] = $lang_module['error_absence_not_workday'];
        nv_jsonOutput($respon);
    }

    if (!empty($reason) and $is_date_valid) {
        // Kiểm tra tồn tại
        $sql = 'SELECT id FROM ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence
                WHERE userid = ' . $HR->user_info['userid'] . '
                AND (start_leave = ' . $where_date . ' OR date_leave = ' . $where_date . ')';
        $sth = $db->prepare($sql);
        $sth->execute();
        if ($sth->fetchColumn() and !$id) {
            $respon['message'] = $lang_module['error_absence'];
            nv_jsonOutput($respon);
        } else {
            // Xử lý dữ liệu
            $reason = nv_nl2br(nv_htmlspecialchars(strip_tags($reason)), '<br />');
            if (!$id) {
                $sql = 'INSERT INTO ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence (
                    userid, reason_leave, type_leave, date_leave, start_leave, end_leave,
                    userid_approved, num_day, half_day_start, half_day_end, use_absence_year, confirm, deny_reason, approved_time, created_time
                ) VALUES (
                    ' . $HR->user_info['userid'] . ', :reason_leave, ' . $type_leave . ', ' . $date_leave . ', ' . $start_leave . ',
                    ' . $end_leave . ', ' . $confirm_userid . ', ' . $total_day . ' , ' . $half_day_start . ' , ' . $half_day_end . ' ,  ' . $use_absence_year . ', ' . $confirm . ', "", 0, ' . NV_CURRENTTIME . '
                )';
            } else {
                $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence SET
                    reason_leave = :reason_leave, type_leave = ' . $type_leave . ', date_leave = ' . $date_leave . ',
                    start_leave = ' . $start_leave . ', end_leave = ' . $end_leave . ', userid_approved = ' . $confirm_userid . ',
                    num_day = ' . $total_day . ', half_day_start = ' . $half_day_start . ', half_day_end = ' . $half_day_end . ', use_absence_year = ' . $use_absence_year . ', confirm = ' . $confirm . '
                        WHERE id = ' . $id;
            }

            try {

                $sth = $db->prepare($sql);
                $sth->bindParam(':reason_leave', $reason, PDO::PARAM_STR);
                $result = $sth->execute();

                // Gửi email cho quản lý
                if ($result) {
                    $list_manager = $HR->getManager();
                    if (!empty($list_manager)) {
                        // Lấy quản lý cấp gần nhất
                        $managers = array_shift($list_manager);
                        $name_manager = $to = [];
                        foreach ($managers as $manager) {
                            $name_manager[] = $manager['full_name'] . ' (' . $manager['office_title'] . ')';
                            $to[] = $manager['email'];
                        }
                        $name_manager = implode(', ', $name_manager);
                        $to = array_values(array_unique($to));

                        $time_stamp_ref = preg_replace('/[^0-9+-]+/', '', str_replace( ' ', '', str_replace( '/', '-', str_replace('-', '+-+', $time))));
                        $subject = SubjectTag::getLeaveAbsence($HR->user_info['full_name'], $year, $HR->user_info['userid']);
                        $message = "<h1>$subject</h1>";
                        $message .= "<p><strong>" . $lang_module['email_workout_ct1'] . ": </strong> " . $name_manager . "</p>";
                        $message .= "<p><strong>" . $lang_module['reason'] . ": </strong> $reason</p>";
                        $message .= "<p><strong>" . $lang_module['time_leave'] . ": </strong> $time</p>";
                        $message .= "<p><strong>" . $lang_module['total_day'] . ": </strong> $total_day</p>";
                        $message .= "<p>" . $lang_module['mess_email_1'] . " <a target=\"_blank\" href=\"" . NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $module_name . "&amp;" . NV_OP_VARIABLE . "=leave-absence&amp;d=" . $HR->user_info['department_id'] . "&u=" . $HR->user_info['userid'] . "&amp;r=" . $time_stamp_ref, true) . "\">" . strtolower($lang_module['link_1']) . "</a> " . $lang_module['mess_email_2'] . ".</p>";

                        $from = [
                            $global_config['site_name'],
                            $global_config['site_email']
                        ];
                        nv_sendmail($from, $to, $subject, $message, null, null, null);
                    }
                }

                $respon['success'] = true;
                $range = '01-' . date('m', $time_range) . '-' . date('Y', $time_range);
                $range .= '+-+';
                $range .= cal_days_in_month(CAL_GREGORIAN, date('n', $time_range), date('Y', $time_range)) . '-' . date('m', $time_range) . '-' . date('Y', $time_range);
                $link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . "=" . $module_name . "&" . NV_OP_VARIABLE . '=leave-absence&r=' . $range;
                $respon['link'] = nv_url_rewrite($link, true);
                nv_jsonOutput($respon);
            } catch (PDOException $e) {
                $respon['message'] = $lang_module['error_saveabsence'] . ': ' . $e->getMessage();
                nv_jsonOutput($respon);
            }
        }
    }
}

$ids = $nv_Request->get_int('id', 'post,get', 0);
$st = new DateTime(date('Y-m-d', mktime(0, 0, 0, date('n'), date('j'), date('Y'))));
$et = new DateTime(date('Y-m-d',mktime(23, 59, 59, date('n'), date('j'), date('Y'))));
$workingtime = mktime(8, 0, 0, date('n'), date('j'), date('Y'));

if (time() > $workingtime) {
    $st->modify('+1 day');
    $et->modify('+1 day');
}

$leave_absence = array(
    'id' => 0,
    'userid' => 0,
    'reason' => '',
    'date' => $st->format('d/m/Y'),
    'start_time' =>  $st->format('d/m/Y'),
    'end_time' => $st->format('d/m/Y'),
    'checked_radio_1' => 'checked',
    'checked_radio_2' => '',
    'style_date' => 'style="display:none"',
    'style_range_date' => '',
    'style_half_day_end_checkbox' => 'style="display:none"',
    'style_half_day_start_checkbox' => 'style="display:none"',
    'checked_half_day_start_morning' => '',
    'checked_half_day_end_morning' => '',
    'checked_half_day_start_afternoon' => '',
    'checked_half_day_end_afternoon' => '',
    'checked_use_absence_year' => 'checked'
);
if (!empty($ids)) {
    $sql = 'SELECT * FROM  ' . $db_config['prefix'] . '_' . $module_data . '_leave_absence WHERE id = ' . $ids;
    $result = $db->query($sql);
    $leave_absence = $result->fetch(2);
    $leave_absence['style_date'] = $leave_absence['style_range_date'] = '';
    $leave_absence['checked_half_day_start_morning'] = ($leave_absence['half_day_start'] == 1) ? 'checked' : '';
    $leave_absence['checked_half_day_end_morning'] = ($leave_absence['half_day_end'] == 1) ? 'checked' : '';
    $leave_absence['checked_half_day_start_afternoon'] = ($leave_absence['half_day_start'] == 2) ? 'checked' : '';
    $leave_absence['checked_half_day_end_afternoon'] = ($leave_absence['half_day_end'] == 2) ? 'checked' : '';
    $leave_absence['checked_use_absence_year'] = ($leave_absence['use_absence_year'] == 1) ? 'checked' : '';
    if ($leave_absence['type_leave'] == '1') {
        $leave_absence['checked_radio_1'] = 'checked';
        $leave_absence['checked_radio_2'] = '';
        $leave_absence['start_time'] = date('d/m/Y', $leave_absence['start_leave']);
        $leave_absence['end_time'] = date('d/m/Y', $leave_absence['end_leave']);
        $leave_absence['date'] = $st->format('d/m/Y');
        $leave_absence['style_date'] = 'style="display:none"';
    } else {
        $leave_absence['date'] = date('d/m/Y', $leave_absence['date_leave']);
        $leave_absence['start_time'] = $st->format('d/m/Y');
        $leave_absence['end_time'] = $st->format('d/m/Y');
        $leave_absence['checked_radio_1'] = '';
        $leave_absence['checked_radio_2'] = 'checked';
        $leave_absence['style_range_date'] = 'style="display:none"';
    }
}
$xtpl = new XTemplate('leave-absence-add.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
$xtpl->assign('ACTION_URL', nv_url_rewrite($base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $op, true));
$xtpl->assign('OP', $op);
$xtpl->assign('LEAVE_ABSENCE_URL', nv_url_rewrite($base_url_user . '&amp;' . NV_OP_VARIABLE . '=leave-absence', true));
$xtpl->assign('LEAVE_ABSENCE', $leave_absence);

if (!empty($error)) {
    $xtpl->assign('ERROR', $error);
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');
include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
