<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */
/*
 * Đăng ký làm thêm
 */
namespace Modules\timekeeping\classs;

use XTemplate;
use NukeViet\Module\timekeeping\Email\SubjectTag;

class WorkOut extends Main
{

    // Thời gian tối đa tháng sau cho phép duyệt làm thêm đối với tháng trước
    private $inc_day_allow_confirm = 5;

    private $tpl = 'workout';

    protected $title = 'workout';

    protected $table_data = 'workout';

    protected $workout_type = 'registry';

    protected $email_vanphong = '';

    public function __construct()
    {
        parent::__construct();
        $month = $this->nv_Request->get_int('month', 'post', 0);
        $year = $this->nv_Request->get_int('year', 'post', 0);
        $this->month = $month > 0 ? $month : $this->month;
        $this->year = $year > 0 ? $year : $this->year;
        $this->workout_type = $this->nv_Request->get_title('workout_type', 'get, post', 'registry');
        if (isset($this->module_config[$this->module_name]['email_vp'])) {
            $this->email_vanphong = $this->module_config[$this->module_name]['email_vp'];
        }
    }

    public function dataPost()
    {
        $day = $this->nv_Request->get_title('day', 'post', 0);
        $hour_start = $this->nv_Request->get_int('hour_start', 'post', 0);
        $minute_start = $this->nv_Request->get_int('minute_start', 'post', 0);
        $hour_end = $this->nv_Request->get_int('hour_end', 'post', 0);
        $minute_end = $this->nv_Request->get_int('minute_end', 'post', 0);
        $reason = $this->nv_Request->get_title('reason', 'post', '', 1);
        $timestamp = strtotime(date_format(date_create_from_format("j/n/Y", $day), "Y-m-d"));
        $month_start = date('m', $timestamp);
        $year_start = date('Y', $timestamp);
        if (!$this->checkWorkOutTime($timestamp, $hour_start, $minute_start, $hour_end, $minute_end)) {
            nv_htmlOutput($this->lang_module['error_registry_workout']);
        }
        if (empty($reason)) {
            nv_htmlOutput($this->lang_module['error_empty_reason']);
        }
        $post = [];
        $post['userid'] = $this->HR->user_info['userid'];
        $post['reason'] = nv_nl2br($reason, "<br />");
        $post['start_time'] = $timestamp + ($hour_start * 60 + $minute_start) * 60;
        $post['end_time'] = $timestamp + ($hour_end * 60 + $minute_end) * 60;

        /**
         * Gửi email cho quản lý
         */
        $list_manager = $this->HR->getManager(1);
        if (!empty($list_manager)) {
            $managers = array_shift($list_manager);
            $name_manager = $to = [];
            foreach ($managers as $manager) {
                $name_manager[] = $manager['full_name'] . ' (' . $manager['office_title'] . ')';
                $to[] = $manager['email'];
            }
            $name_manager = implode(', ', $name_manager);
            $to = array_values(array_unique($to));

            $subject = $this->HR->user_info['full_name'] . ' ' . $this->lang_module['work_out1'];
            $subject_email = SubjectTag::getWorkOut($this->HR->user_info['full_name'], $year_start, $this->HR->user_info['userid']);
            $message = "<h1>$subject</h1>";
            $message .= "<p><strong>" . $this->lang_module['email_workout_ct1'] . ": </strong>" . $name_manager . "</p>";
            $message .= "<p><strong>" . $this->lang_module['day'] . ": </strong>$day</p>";
            $message .= "<p><strong>" . $this->lang_module['workout_start'] . ": </strong> $hour_start ". nv_strtolower($this->lang_module['hour']) . " $minute_start ". nv_strtolower($this->lang_module['minute']) . "</p>";
            $message .= "<p><strong>" . $this->lang_module['workout_finish'] . ": </strong> $hour_end ". nv_strtolower($this->lang_module['hour']) . " $minute_end ". nv_strtolower($this->lang_module['minute']) . "</p>";
            $message .= "<p><strong>" . $this->lang_module['reason'] . ": </strong> $reason</p>";
            $message .= "<p>" . $this->lang_module['mess_email_1'] . " <a target=\"_blank\" href=\"" . NV_MY_DOMAIN . nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . "=" . $this->module_name . "&amp;" . NV_OP_VARIABLE . "=workout-confirm&amp;workout_type=registry&month=" . $month_start . "&year=" . $year_start, true) . "\">" . strtolower($this->lang_module['link_1']) . "</a> " . $this->lang_module['mess_email_2'] . "</p>";

            $from = [
                $this->global_config['site_name'],
                $this->global_config['site_email']
            ];
            nv_sendmail($from, $to, $subject_email, $message);
        }

        return $post;
    }
    /**
     * Lấy danh sách email của các lãnh đạo có quyền xác nhận làm thêm của bộ phận
     */
    public function email_leader_confirm_department($department_id) {
        $email_leader = array();
        $this->db->sqlreset()
        ->select('t1.userid, t2.email')
        ->from($this->table_module . '_human_resources t1')
        ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid')
        ->where('FIND_IN_SET(' . $department_id . ', t1.confirm_workout)');
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch()) {
            $email_leader[$row['userid']] = $row['email'];
        }
        return $email_leader;
    }
    /**
     * Lãnh đạo gửi email cho văn phòng và nhân viên sau khi đã phê duyệt đăng ký làm thêm.
     */
    public function send_email_from_leader_for_registry($workout_id, $confirm) {
        // Lấy thông tin làm thêm
        $this->db->sqlreset()
        ->select('t1.*, t2.email, t2.first_name, t2.last_name, t2.username')
        ->from($this->table_data . ' t1')
        ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid')
        ->where('t1.id = ' . $workout_id);
        $employee = $this->db->query($this->db->sql())->fetch(2);
        $employee_fullname = nv_show_name_user($employee['first_name'], $employee['last_name'], $employee['username']);
        $day = date("j/n/Y", $employee['start_time']);
        $hour_start = date("H", $employee['start_time']);
        $minute_start = date("i", $employee['start_time']);
        $hour_end = date("H", $employee['end_time']);
        $minute_end = date("i", $employee['end_time']);
        $reason = $employee['reason'];
        $email = $employee['email'];
        $leader_fullname = nv_show_name_user($this->HR->user_info['first_name'], $this->HR->user_info['last_name'], $this->HR->user_info['username']);
        $subject = $employee_fullname . ' ' . $this->lang_module['work_out1'];
        $subject_email = SubjectTag::getWorkOut($employee_fullname, date('Y', $employee['start_time']), $employee['userid']);
        $message = "<h1>$subject</h1>";
        $message .= "<p><strong>Ngày: </strong>$day</p>";
        $message .= "<p><strong>Bắt đầu: </strong> $hour_start giờ  $minute_start phút</p>";
        $message .= "<p><strong>Kết thúc: </strong> $hour_end giờ $minute_end phút</p>";
        $message .= "<p><strong>Lý do: </strong> $reason</p>";
        if ($confirm == 3) {
            $deny_reason = $employee['deny_reason'];
            $message .= "<p><strong>Bị từ chối bởi:</strong> $leader_fullname </p>";
            $message .= "<p><strong>Lý do từ chối:</strong> $deny_reason </p>";
            nv_sendmail($this->global_config['smtp_username'], $email, $subject_email, $message, null, null, null);
        } else {
            $message .= "<p><strong>" . ($confirm ? "Đã được xác nhận bởi" : "Đã hủy bởi") . ":</strong> $leader_fullname </p>";
            nv_sendmail($this->global_config['smtp_username'], $email, $subject_email, $message, null, null, null, $this->email_vanphong);
        }
    }
    /**
     * Lãnh đạo gửi email cho văn phòng và nhân viên sau khi đã phê duyệt làm thêm đột xuất
     */
    public function send_email_from_leader_for_surprise($checkio_id) {
        // Lấy thông tin làm thêm
        $this->db->sqlreset()
        ->select('t1.userid, t1.checkin_real, t1.checkout_real, t1.workday, t1.description, t1.confirm_status, t1.confirm_reason, t2.email, t2.first_name, t2.last_name, t2.username')
        ->from($this->table_module . '_checkio t1')
        ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid')
        ->where('t1.id = ' . $checkio_id);
        $employee = $this->db->query($this->db->sql())->fetch(2);
        $employee_fullname = nv_show_name_user($employee['first_name'], $employee['last_name'], $employee['username']);
        $day = date("j/n/Y", $employee['checkin_real']);
        $hour_start = date("H", $employee['checkin_real']);
        $minute_start = date("i", $employee['checkin_real']);
        $hour_end = date("H", $employee['checkout_real']);
        $minute_end = date("i", $employee['checkout_real']);
        $reason = $employee['description'];
        $email = $employee['email'];
        $workday = floor(($employee['workday'] * $this->hourwork * 60) / 100);
        $h = floor($workday / 60);
        $m = $workday % 60;

        $leader_fullname = nv_show_name_user($this->HR->user_info['first_name'], $this->HR->user_info['last_name'], $this->HR->user_info['username']);
        $subject = $employee_fullname . ' ' . $this->lang_module['workout_surprise1'];
        $subject_email = SubjectTag::getWorkOut($employee_fullname, date('Y', $employee['checkin_real']), $employee['userid']);
        $message = "<h1>$subject</h1>";
        $message .= "<p><strong>Ngày: </strong>$day</p>";
        $message .= "<p><strong>Bắt đầu: </strong> $hour_start giờ  $minute_start phút</p>";
        $message .= "<p><strong>Kết thúc: </strong> $hour_end giờ $minute_end phút</p>";
        $message .= "<p><strong>Lý do: </strong> $reason</p>";
        $message .= "<p><strong>Thời gian được duyệt: </strong> $h giờ $m phút</p>";
        if ($employee['confirm_status'] == 1) {
            $message .= "<p><strong>Đã được xác nhận bởi:</strong> $leader_fullname </p>";
            nv_sendmail($this->global_config['smtp_username'], $email, $subject_email, $message, null, null, null, $this->email_vanphong);
        } else if ($employee['confirm_status'] == 2) {
            $confirm_reason = $employee['confirm_reason'];
            $message .= "<p><strong>Bị từ chối bởi:</strong> $leader_fullname </p>";
            $message .= "<p><strong>Lý do từ chối:</strong> $confirm_reason </p>";
            nv_sendmail($this->global_config['smtp_username'], $email, $subject_email, $message, null, null, null);
        }

    }
    /**
     * kiểm tra cho phép cấp trên xác nhận thời gian làm thêm
     * Cụ thể chỉ cho phép xác nhận trong cùng tháng hoặc trước ngày $inc_day_allow_confirm của tháng sau
     * và làm thêm đó mình đã xác nhận hoặc chưa ai xác nhận
     */
    public function check_allow_confirm_workout($month, $year)
    {
        $check_same_month = (date('Y', $this->cr_time) * 100 + date('n', $this->cr_time)) == ($year * 100 + $month);

        $pre_second = $this->inc_day_allow_confirm * 24 * 60 * 60;
        $check_next_month = (date('Y', $this->cr_time - $pre_second) * 100 + date('n', $this->cr_time - $pre_second)) == ($year * 100 + $month);
        return $check_same_month || $check_next_month;
    }

    /*
     * Kiem tra xem thoi gian dang ky lam them co trung voi thoi gian lam viec chinh khong
     * Chu y: nguoi co gio lam rieng thi hoan toan co the checkin vao thoi gian lam viec chinh khong can phai khai bao
     * Trả về true nghĩa là cho phép đăng ký
     */
    public function checkWorkOutTime($timestamp, $hour_start, $minute_start, $hour_end, $minute_end)
    {
        $checkAllow = true;
        $CHECKIO = new Checkio($this->HR);
        $timePreAllow = $CHECKIO->timePreAllow;
        $time_start = $hour_start * 60 + $minute_start;
        $time_end = $hour_end * 60 + $minute_end;
        $checkAllow = $checkAllow && !$CHECKIO->checkDayAllow($timestamp + $time_start * 60);
        $checkAllow = $checkAllow || $CHECKIO->checkHoliday($timestamp);
        $checkHourAllow = true;
        foreach ($CHECKIO->timeCheckio as $session) {
            $checkin = $session['checkin'];
            $checkout = $session['checkout'];
            $checkHourAllow = $checkHourAllow && !(($time_start >= $checkin) && ($time_start <= $checkout));
            $checkHourAllow = $checkHourAllow && !(($time_end >= $checkin) && ($time_end <= $checkout));
        }
        $checkAllow = $checkAllow || $checkHourAllow;
        return $checkAllow;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * Cập nhật lại thời gian làm thêm.
     * Quản lý trực tiếp xác nhận lại thời gian làm thêm của nhân viên và cập nhật lại
     */
    public function update_confirm_workout($id, $data = array())
    {
        $CHECKIO = new Checkio($this->HR);
        $workout = intval($data['workout']);
        $workout = round($workout / 60 / $this->hourwork * 100, 2);
        $statement = $CHECKIO->editById($id, array(
            'workday' => $workout,
            'confirm_userid' => $this->HR->user_info['userid'],
            'confirm_status' => $workout = intval($data['confirm_status']),
            'confirm_time'   => time(),
            'confirm_reason' => $data['confirm_reason']
        ));
        return $statement;
    }

    public function editById($id, $data = array())
    {
        $statement = false;
        $data['confirm_userid'] = $this->HR->user_info['userid'];
        $workout = $this->getById($id);
        if (!empty($workout['confirm']) && ($this->HR->user_info['userid'] == $workout['userid'])) {
            nv_htmlOutput($this->lang_module['error_confirmed']);
        } else {
            $statement = parent::editById($id, $data);
        }
        return $statement;
    }

    public function changeData($data)
    {
        $data_change = [];
        $tt = 0;
        $HR = new HumanResources();
        foreach ($data as $value) {
            $tt++;
            $sum_work = floor(($value['checkout_real'] - $value['checkin_real']) / 60);
            $sum_work = $sum_work < 0 ? 0 : $sum_work;

            $value['tt'] = $tt;
            $value['time'] = '<p>Checkin: ' . date('G:i  j/n/Y', $value['checkin_real']) . '</p>' . '<p>Checkout: ' . date('G:i  j/n/Y', $value['checkout_real']) . '</p>';
            $value['sum_work'] = $sum_work;
            $value['sum_work_str'] = sprintf($this->lang_module['hour_minute'], floor($sum_work / 60), $sum_work % 60);
            $value['day_text'] = str_pad($value['day'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($value['month'], 2, '0', STR_PAD_LEFT);
            $value['time_in'] = date('H:i d/m/Y', $value['checkin_real']);
            $value['time_out'] = date('H:i d/m/Y', $value['checkout_real']);
            if (empty($value['workday']) && $value['workday'] != '0') {
                $value['sum_confirm'] = '';
                $value['hour_confirm'] = '';
                $value['min_confirm'] = '';
                $value['sum_confirm_str'] = $this->lang_module['have_not_confirm_yet'];
            } else {
                $leader = $HR->getById($value['confirm_userid'], 'first_name, last_name, username');
                $sum_work = floor(($value['workday'] * $this->hourwork * 60) / 100);
                $value['hour_confirm'] = floor($sum_work / 60);
                $value['min_confirm'] = $sum_work % 60;

                $sum_confirm = floor(($value['workday'] * $this->hourwork) / 100 * 60);
                $value['sum_confirm'] = $sum_confirm;
                if ($value['confirm_status'] == 2) {
                    $comfirm_deny_by = $this->lang_module['deny_by'];
                    $value['sum_confirm_str'] = '<p>' . $comfirm_deny_by . '</p>' .
                        '<p><strong>'. nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']). '</strong></p>' .
                        '<p>' . $value['confirm_reason'].  ' </p>';
                } else {
                    $comfirm_deny_by = $this->lang_module['confirmed_by'];
                    $value['sum_confirm_str'] = '<p>' . $comfirm_deny_by . '</p>' .
                        '<p><strong>'. nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']). '</strong></p>' .
                        '<p>' . sprintf($this->lang_module['hour_minute'], floor($sum_confirm / 60), $sum_confirm % 60) .  ' </p>';
                }
            }
            $data_change[] = $value;
        }
        return $data_change;
    }

    /**
     * Lấy danh sách làm thêm có đăng ký để xác nhận
     */
    public function get_data_confirm_registry($month = 0, $year = 0)
    {
        $data = array();
        $DEPARTMENT = new Department();
        /**
         * 1. Lấy danh sách mặc định (danh sách được phê duyệt mà không cần cấu hình thêm), đó là phòng mà nhân sự đang là quản lý
         * Hoặc các phòng trực thuộc nhưng chưa có lãnh đạo
         */
        $list_department = $DEPARTMENT->get_department_children($this->HR->user_info['department_id'], 0, [$this->HR->user_info['department_id']]);
        array_push($list_department, $this->HR->user_info['department_id']);

        // 2. Lấy danh sách phê duyệt do được cấu hình riêng
        $this->db->sqlreset()
        ->select('confirm_workout')
        ->from($this->table_module . '_human_resources')
        ->where('userid=' . $this->HR->user_info['userid']);
        list($list_department_config) = $this->db->query($this->db->sql())->fetch(3);
        $list_department_config = explode(',', $list_department_config);
        $list_department = array_merge($list_department, $list_department_config);
        $list_department = array_unique($list_department);

        // Lấy danh sách các phòng trực thuộc của $list_department (giúp cho việc phê duyệt cho các trưởng phòng của phòng đó)
        $this->db->sqlreset()
        ->select('id')
        ->from($this->table_module . '_department')
        ->where('parentid IN (' . implode(',', $list_department) . ')');
        $list_department_childrent = [];
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $list_department_childrent[] = $row['id'];
        }


        $first_timestamp = strtotime($year . '-' . $month . '-1');
        $last_timestamp = $month < 12 ? strtotime($year . '-' . ($month + 1) . '-1') : strtotime(($year + 1) . '-1-1');
        $this->db->sqlreset()
        ->select('t1.*, t2.username, t2.first_name, t2.last_name, t2.photo, t3.phone')
        ->from($this->table_data . ' AS t1')
        ->join(
            'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid ' .
            'INNER JOIN ' . $this->table_module . '_human_resources AS t3 ON t1.userid = t3.userid ' .
            'INNER JOIN ' . $this->table_module . '_office AS t4 ON t4.id = t3.office_id'
        )->order('t1.start_time');

        $or_department = !empty($list_department_childrent) ? ' OR (t3.department_id IN (' . implode(', ', $list_department_childrent) . ') AND t4.leader=1)' : '';
        $arr_where = [];
        //$arr_where[] =  '(t3.still_working=1)';
        $arr_where[] =  '( t1.start_time >= ' . $first_timestamp . ' )';
        $arr_where[] = '( t1.start_time < ' . $last_timestamp . ' )';
        $arr_where[] = '((t3.department_id IN (' . implode(', ', $list_department) . ') AND t4.leader=0)' . $or_department . ')';
        $this->db->where(implode(' AND ', $arr_where));

        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            if (empty($row['photo'])) {
                $row['photo'] = NV_BASE_SITEURL . "themes/default/images/users/no_avatar.png";
            } else {
                $row['photo'] = NV_BASE_SITEURL . $row['photo'];
                if (defined('SSO_REGISTER_DOMAIN')) {
                    $row['photo'] = SSO_REGISTER_DOMAIN . $row['photo'];
                }
            }
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Nhân viên lấy danh sách đăng ký làm thêm
     */
    public function get_data_workout_registry()
    {
        $first_timestamp = strtotime($this->year . "-" . $this->month . "-1");
        $last_timestamp = $this->month < 12 ? strtotime($this->year . "-" . ($this->month + 1) . "-1") : strtotime(($this->year + 1) . "-1-1");
        $data = $this->getAll("*", "start_time>= $first_timestamp AND start_time<$last_timestamp AND userid=" . $this->HR->user_info['userid'], 'start_time');
        return $data;
    }

    /**
     * Nhân viên lấy danh sách làm thêm đột xuất của mình
     */
    public function get_data_workout_surprise()
    {
        $CHECKIO = new Checkio($this->HR);
        $data = $CHECKIO->getAll("*", "month = $this->month AND year = $this->year AND status = 'workout' AND workout_id = 0 AND userid=" . $this->HR->user_info['userid'], 'day');
        return $data;
    }

    /**
     * Quản lý lấy danh sách làm thêm đột xuất để xác nhận
     */
    public function get_data_confirm_surprise($month = 0, $year = 0)
    {
        $data = array();
        $DEPARTMENT = new Department();
        /**
         * 1. Lấy danh sách mặc định (danh sách được phê duyệt mà không cần cấu hình thêm), đó là phòng mà nhân sự đang là quản lý
         * Hoặc các phòng trực thuộc nhưng chưa có lãnh đạo
         */
        $list_department = $DEPARTMENT->get_department_children($this->HR->user_info['department_id'], 0, [$this->HR->user_info['department_id']]);
        array_push($list_department, $this->HR->user_info['department_id']);

        // 2. Lấy danh sách phê duyệt do được cấu hình riêng
        $this->db->sqlreset()
        ->select('confirm_workout')
        ->from($this->table_module . '_human_resources')
        ->where('userid=' . $this->HR->user_info['userid']);
        list($list_department_config) = $this->db->query($this->db->sql())->fetch(3);
        $list_department_config = explode(',', $list_department_config);
        $list_department = array_merge($list_department, $list_department_config);
        $list_department = array_unique($list_department);

        // Lấy danh sách các phòng trực thuộc của $list_department (giúp cho việc phê duyệt cho các trưởng phòng của phòng đó)
        $this->db->sqlreset()
        ->select('id')
        ->from($this->table_module . '_department')
        ->where('parentid IN (' . implode(',', $list_department) . ')');
        $list_department_childrent = [];
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $list_department_childrent[] = $row['id'];
        }

        $this->db->sqlreset()
        ->select('t1.*, t2.username, t2.first_name, t2.last_name, t2.photo, t3.phone')
        ->from($this->table_module . '_checkio AS t1')
        ->join(
            'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid ' .
            'INNER JOIN ' . $this->table_module . '_human_resources AS t3 ON t1.userid = t3.userid ' .
            'INNER JOIN ' . $this->table_module . '_office AS t4 ON t4.id = t3.office_id '
            )
        ->order('t1.day');
        $or_department = !empty($list_department_childrent) ? ' OR (t3.department_id IN (' . implode(', ', $list_department_childrent) . ') AND t4.leader=1)' : '';
        $arr_where = array();
        $arr_where[] =  '( t1.month = ' . $month  . ' )';
        $arr_where[] =  '( t1.year = ' . $year  . ' )';
        $arr_where[] =  '( t1.status = "workout" )';
        $arr_where[] =  '( t1.workout_id = 0 )';
        $arr_where[] = '((t3.department_id IN (' . implode(', ', $list_department) . ') AND t4.leader=0)' . $or_department . ')';
        $this->db->where(implode(' AND ', $arr_where));
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            if (empty($row['photo'])) {
                $row['photo'] = NV_BASE_SITEURL . "themes/default/images/users/no_avatar.png";
            } else {
                $row['photo'] = NV_BASE_SITEURL . $row['photo'];
                if (defined('SSO_REGISTER_DOMAIN')) {
                    $row['photo'] = SSO_REGISTER_DOMAIN . $row['photo'];
                }
            }
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $row['range'] = urlencode(date('d-m-Y', $row['checkin_real']) . ' - ' . date('d-m-Y', $row['checkin_real']));
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Khai báo làm thêm
     */
    public function htmlAdd($id = 0)
    {
        $thisDate = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
        $CONFIG_MEMBER = new ConfigMember($this->user_info['userid']);
        $timeCheckio = $CONFIG_MEMBER->getConfigData()['timeCheckio'];
        $workout = array(
            'id' => 0,
            'userid' => 0,
            'reason' => '',
            'start_time' => $thisDate + 86400 + ($timeCheckio[0]['checkin'] * 60),
            'end_time' => $thisDate + 86400 + ($timeCheckio[1]['checkout'] * 60),
            'confirm' => 0
        );

        $workout = !empty($id) ? $this->getById($id) : $workout;
        $workout['day'] = date('d/m/Y', $workout['start_time']);
        $workout['hour_start'] = date("G", $workout['start_time']);
        $workout['minute_start'] = date("i", $workout['start_time']);
        $workout['hour_end'] = date("G", $workout['end_time']);
        $workout['minute_end'] = date("i", $workout['end_time']);
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
        $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        $xtpl->assign('LIST_WORKOUT_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=workout', true));
        $xtpl->assign('OP', $this->op);

        $xtpl->assign('WORKOUT', $workout);
        for ($h = 0; $h <= 23; $h++) {
            $xtpl->assign('hour', $h);
            $xtpl->assign('hour_title', $h . " " . $this->lang_global['hour']);
            $xtpl->assign('selected', $workout['hour_start'] == $h ? 'selected' : '');
            $xtpl->parse('workout_add.hour_start');
        }
        for ($m = 0; $m <= 59; $m++) {
            $xtpl->assign('minute', $m);
            $xtpl->assign('minute_title', $m . " " . $this->lang_global['min']);
            $xtpl->assign('selected', $workout['minute_start'] == $m ? 'selected' : '');
            $xtpl->parse('workout_add.minute_start');
        }
        for ($h = 0; $h <= 23; $h++) {
            $xtpl->assign('hour', $h);
            $xtpl->assign('hour_title', $h . " " . $this->lang_global['hour']);
            $xtpl->assign('selected', $workout['hour_end'] == $h ? 'selected' : '');
            $xtpl->parse('workout_add.hour_end');
        }
        for ($m = 0; $m <= 59; $m++) {
            $xtpl->assign('minute', $m);
            $xtpl->assign('minute_title', $m . " " . $this->lang_global['min']);
            $xtpl->assign('selected', $workout['minute_end'] == $m ? 'selected' : '');
            $xtpl->parse('workout_add.minute_end');
        }
        $xtpl->assign('disabled_save', !empty($workout['confirm']) ? 'disabled' : '');
        $xtpl->parse('workout_add');
        return $xtpl->text('workout_add');
    }

    /**
     * Hiện thị block tháng và ngày
     */
    public function html_month_year($action_url)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($action_url, true));
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', array(
                'value' => $k,
                'title' => $title
            ));
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('html_month_year.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', array(
                'value' => $value,
                'title' => $value
            ));
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('html_month_year.year');
        }
        $xtpl->parse('html_month_year');
        return $xtpl->text('html_month_year');
    }

    /**
     * Danh sách để lãnh đạo phê duyệt làm thêm đột xuất
     */
    public function html_workout_confirm()
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('HTML_MONTH_YEAR', $this->html_month_year($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;workout_type=' . $this->workout_type));
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        $xtpl->assign('MODULE_URL', nv_url_rewrite($this->base_url_user, true));
        $xtpl->assign('active_workout_registry', $this->workout_type == 'registry' ? 'active' : '');
        $xtpl->assign('active_workout_surprise', $this->workout_type == 'surprise' ? 'active' : '');
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('month', $this->month);
        $xtpl->assign('year', $this->year);
        $xtpl->assign('workout_title', sprintf($this->lang_module['workout_title'], $this->month, $this->year));

        $base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $this->module_name . '&amp;' . NV_OP_VARIABLE . '=work-report&amp;d=0';
        $xtpl->assign('REPORT_LINK', $base_url);
        $HR = new HumanResources();
        if ($this->workout_type == 'surprise') {
            // Danh sách làm thêm đột xuất
            $data = $this->get_data_confirm_surprise($this->month, $this->year);
            $data = $this->changeData($data);
            $change_confirm_allow = $this->check_allow_confirm_workout($this->month, $this->year);
            foreach ($data as $workout) {
                $show_hide_button_confirm = "";
                $show_hide_button_deny = "";
                if ($workout['confirm_status'] == 1) {
                    $show_hide_button_confirm = "style='display:none'";
                } else if ($workout['confirm_status'] == 2) {
                    $show_hide_button_deny = "style='display:none'";
                }
                $xtpl->assign('LOOP', $workout);
                $xtpl->assign('SHOW_HIDE_BUTTON_CONFIRM', $show_hide_button_confirm);
                $xtpl->assign('SHOW_HIDE_BUTTON_DENY', $show_hide_button_deny);
                // Cho phép xác thực khi đủ điều kiện $change_confirm_allow và (chưa được xác thực hoặc người xác thực chính mình)
                if ($change_confirm_allow && (empty($workout['confirm_userid']) || ($workout['confirm_userid'] == $this->HR->user_info['userid']))) {
                    $xtpl->parse('list_confirm.confirm_workout_surprise.loop.change_confirm');
                } else {
                    $leader = $HR->getById($workout['confirm_userid'], 'first_name, last_name, username');
                    $message = !empty($workout['confirm_userid']) ? $workout['sum_confirm_str'] : $this->lang_module['end_time'];
                    $xtpl->assign('message', $message);
                    $xtpl->parse('list_confirm.confirm_workout_surprise.loop.confirmed_by_other');
                }
                $xtpl->parse('list_confirm.confirm_workout_surprise.loop');
            }
            $xtpl->parse('list_confirm.confirm_workout_surprise');
        } else if ($this->workout_type == 'registry') {
            // Danh sách làm thêm đăng ký
            $data = $this->get_data_confirm_registry($this->month, $this->year);
            $tt = 0;
            foreach ($data as $workout) {
                $tt++;
                $workout['tt'] = $tt;
                $workout['day'] = date('j', $workout['start_time']);
                $workout['month'] = date('n', $workout['start_time']);
                $workout['year'] = date('Y', $workout['start_time']);
                $workout['work_start'] = date('G', $workout['start_time']) . " " . $this->lang_global['hour'] . " " . date('i', $workout['start_time']) . " " . $this->lang_global['min'];
                $workout['to'] = date('G', $workout['end_time']) . " " . $this->lang_global['hour'] . " " . date('i', $workout['end_time']) . " " . $this->lang_global['min'];
                $workout['day_text'] = str_pad($workout['day'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($workout['month'], 2, '0', STR_PAD_LEFT);
                $workout['show_hide_button_deny'] = "";
                if ($workout['confirm'] == 3) {
                    $workout['show_hide_button_deny'] = "style='display:none'";
                }
                // Đã được xác thực bởi người khác
                // Kiểm tra xem người đó đã làm chưa.
                $workday_list = array();
                $result = $this->db->query('SELECT checkin_real, checkout_real, workday  FROM ' . $this->table_module . '_checkio WHERE workout_id = ' . $workout['id']);
                while ($row = $result->fetch(2)) {
                    $workday_list[] = $row;
                }
                if (!empty($workday_list)) {
                    $sum_workday = 0;
                    foreach ($workday_list as $workday) {
                        $xtpl->assign('in_out', '<p><strong>In:</strong> ' . date('H:i', $workday['checkin_real']) . ' - <strong>Out:</strong>' . date('H:i', $workday['checkout_real']) . '</p>');
                        $sum_workday += $workday['workday'];
                        $xtpl->parse('list_confirm.confirm_workout_registry.loop.had_finish.workday_list');
                    }
                    $h = ($sum_workday * $this->hourwork) / 100;
                    $xtpl->assign('sum_time_work', $sum_workday == 0 ? $this->lang_module['end_checkin'] : sprintf($this->lang_module['hour_minute'], floor($h), round(($h - floor($h)) * 60)));
                    $xtpl->parse('list_confirm.confirm_workout_registry.loop.had_finish');

                } else if (($workout['confirm'] > 0) && ($workout['confirm_userid'] != $this->HR->user_info['userid']) && ($workout['userid'] == $this->HR->user_info['userid'])) {
                    $leader = $HR->getById($workout['confirm_userid'], 'first_name, last_name, username');
                    if ($workout['confirm'] == 1) {
                        $confirm_or_deny = $this->lang_module['confirmed_by'];
                    } else {
                        $confirm_or_deny = $this->lang_module['deny_by'];
                    }
                    $xtpl->assign('confirm_or_deny', $confirm_or_deny);
                    $xtpl->assign('leader_full_name', nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']));
                    $xtpl->parse('list_confirm.confirm_workout_registry.loop.confirmed_by_other');

                } else {
                    $leader_full_name = '';
                    if ($workout['confirm_userid'] > 0) {
                        $leader = $HR->getById($workout['confirm_userid'], 'first_name, last_name, username');
                        $leader_full_name = nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']);
                    }

                    $confirm_or_deny = '';
                    if ($workout['confirm'] == 1) {
                        $workout['confirm_title'] = $this->lang_module['cancel_confirm'];
                        $confirm_or_deny = $this->lang_module['confirmed_by'];
                    } else if ($workout['confirm'] == 3) {
                        $workout['confirm_title'] = $this->lang_module['confirm'];
                        $confirm_or_deny = $this->lang_module['deny_by'];
                    } else {
                        $leader_full_name = '';
                        $workout['confirm_title'] = $this->lang_module['confirm'];
                    }
                    if ($workout['confirm'] == 1) {
                        $workout['status'] = 0;
                    } else {
                        $workout['status'] = 1;
                    }
                    $workout['confirm_or_deny'] = $confirm_or_deny;
                    $workout['leader_full_name'] = $leader_full_name;
                    $xtpl->assign('CONFIRM', $workout);
                    $xtpl->parse('list_confirm.confirm_workout_registry.loop.confirmed_by_your_self');
                }
                $xtpl->assign('LOOP', $workout);
                $xtpl->parse('list_confirm.confirm_workout_registry.loop');
            }
            $xtpl->parse('list_confirm.confirm_workout_registry');
        }
        $xtpl->parse('list_confirm');
        return $xtpl->text('list_confirm');
    }

    /*
     * Danh sách làm thêm của nhân viên
     */
    public function html()
    {
        $CHECKIO = new Checkio($this->HR);
        $status_last = $CHECKIO->status_last;
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        $xtpl->assign('MODULE_URL', nv_url_rewrite($this->base_url_user, true));
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('active_workout_registry', $this->workout_type == 'registry' ? 'active' : '');
        $xtpl->assign('active_workout_surprise', $this->workout_type == 'surprise' ? 'active' : '');
        $xtpl->assign('month', $this->month);
        $xtpl->assign('year', $this->year);
        $xtpl->assign('HTML_MONTH_YEAR', $this->html_month_year($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;workout_type=' . $this->workout_type));
        $xtpl->assign('workout_title', sprintf($this->lang_module['workout_title'], $this->month, $this->year) . " (" . $this->HR->user_info['title'] . ": " . $this->HR->user_info['full_name'] . ")");
        if ($this->workout_type == 'surprise') {
            // Danh sách làm thêm đột xuất
            $data = $this->get_data_workout_surprise();
            $data = $this->changeData($data);
            foreach ($data as $workout) {
                $xtpl->assign('LOOP', $workout);
                $xtpl->parse('list.workout_surprise.loop');
            }

            $xtpl->parse('list.workout_surprise');
        } else if ($this->workout_type == 'registry') {
            // Danh sách làm thêm được đăng ký
            $data = $this->get_data_workout_registry();
            $woukout_ids = array_map(function ($workout) {
                return $workout['id'];
            }, $data);
            $workday_items = array();
            if (!empty($woukout_ids)) {
                $workday_items = $CHECKIO->getAll('workout_id, workday, checkin_real, checkout_real', 'workout_id IN (' . implode(',', $woukout_ids) . ')');
            }
            $workday_list = array();
            foreach ($workday_items as $value) {
                $workday_list[$value['workout_id']][] = $value;
            }
            $tt = 0;
            foreach ($data as $workout) {
                $tt++;
                $workout['tt'] = $tt;
                $workout['day'] = date('j', $workout['start_time']);
                $workout['month'] = date('n', $workout['start_time']);
                $workout['year'] = date('Y', $workout['start_time']);
                $workout['work_start'] = date('G', $workout['start_time']) . " " . $this->lang_global['hour'] . " " . date('i', $workout['start_time']) . " " . $this->lang_global['min'];
                $workout['to'] = date('G', $workout['end_time']) . " " . $this->lang_global['hour'] . " " . date('i', $workout['end_time']) . " " . $this->lang_global['min'];
                $workout['day_text'] = str_pad($workout['day'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($workout['month'], 2, '0', STR_PAD_LEFT);
                $xtpl->assign('LOOP', $workout);
                /**
                 * Kiểm tra điều kiện để hiện nút Checkin
                 * 1.
                 * Đã xác nhận
                 * 2. Chưa checkin
                 * 3. Trước giờ làm 30 phút
                 * 4. Chưa hết giờ làm
                 */
                $allow_checkin = true;
                $allow_checkin = !empty($workout['confirm']);
                $allow_checkin = $allow_checkin && (empty($status_last) || !empty($status_last['checkout']));
                $allow_checkin = $allow_checkin && ($this->cr_time + $CHECKIO->timePreAllow * 60 > $workout['start_time']);
                $allow_checkin = $allow_checkin && ($this->cr_time < $workout['end_time']);
                if ($allow_checkin) {
                    // Hiện nút Checkin
                    $xtpl->parse('list.workout_registry.loop.view_checkin');
                } else if (!empty($status_last) && empty($status_last['checkout']) && ($status_last['workout_id'] == $workout['id'])) {
                    // Hiện nút Checkout
                    $xtpl->parse('list.workout_registry.loop.view_checkout');
                } else if ($this->cr_time >= $workout['end_time']) {
                    $sum_workday = 0;
                    foreach ($workday_list[$workout['id']] as $workday) {
                        $xtpl->assign('in_out', '<p><strong>In:</strong> ' . date('H:i', $workday['checkin_real']) . ' - <strong>Out:</strong>' . date('H:i', $workday['checkout_real']) . '</p>');
                        $sum_workday += $workday['workday'];
                        $xtpl->parse('list.workout_registry.loop.end_checkin.workday_list');
                    }
                    $h = ($sum_workday * $this->hourwork) / 100;
                    $xtpl->assign('sum_time_work', $sum_workday == 0 ? $this->lang_module['end_checkin'] : sprintf($this->lang_module['hour_minute'], floor($h), round(($h - floor($h)) * 60)));
                    $xtpl->parse('list.workout_registry.loop.end_checkin');
                } else if (!empty($workout['confirm'])) {
                    $leader = $this->HR->getById($workout['confirm_userid'], 'first_name, last_name, username');
                    if ($workout['confirm'] == 1) {
                        $had_confirmed = '<p>' . $this->lang_module['confirmed_by'] . '</p>' .
                        '<p><strong>'. nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']). '</strong></p>';
                    } else {
                        $had_confirmed = '<p>' . $this->lang_module['deny_by'] . '</p>' .
                        '<p><strong>'. nv_show_name_user($leader['first_name'], $leader['last_name'], $leader['username']). '</strong></p>
                        <p>Lý do: ' .$workout['deny_reason'] . '</p>';
                    }
                    $xtpl->assign('had_confirmed', $had_confirmed);
                    $xtpl->parse('list.workout_registry.loop.had_confirm');
                } else {
                    $xtpl->parse('list.workout_registry.loop.not_confirm');
                }
                $xtpl->parse('list.workout_registry.loop');
            }

            $xtpl->parse('list.workout_registry');
        }
        $xtpl->parse('list');
        return $xtpl->text('list');
    }
}
