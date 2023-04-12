<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace Modules\timekeeping\classs;

class Cron extends Main
{

    protected $table_data = 'checkio';

    public function dataPost()
    {
        return true;
    }

    public function check_not_checkout()
    {
        $data = array();
        // Tìm tất cả các thành viên làm hành chính chưa checkout trong khoảng thời gian trong 16 giờ từ thời điểm chạy chương trình
        $this->db->sqlreset()
            ->select('t1.userid, t1.checkin_real, t2.username, t2.email, t2.first_name, t2.last_name')
            ->from($this->table_data . ' AS t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid = t2.userid')
            ->where('t1.checkout IS NULL AND t1.status = "worktime" AND t1.checkin_real >= ' . ($this->cr_time - 16 * 60 * 60));
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $data[] = $row;
        }
        // Lấy những nhân viên chưa checkout sau 1 giờ nghỉ làm.
        $filter_array = array();
        foreach ($data as $value) {
            $CONFIG_MEMBER = new ConfigMember($value['userid']);
            $timeCheckio = $CONFIG_MEMBER->getConfigData()['timeCheckio'];
            $lastSessionCheckio = $timeCheckio[count($timeCheckio) - 1];
            $time = mktime(0, 0, 1, date('n', $value['checkin_real']), date('j', $value['checkin_real']), date('Y', $value['checkin_real']));
            $timeCheckout = $time + ($lastSessionCheckio['checkout'] + 60) * 60;
            if ($this->cr_time >= $timeCheckout) {
                $value['checkout'] = date("G:i \N\g\à\y j/n/Y", $time + $lastSessionCheckio['checkout'] * 60);
                $filter_array[] = $value;
            }
        }
        // Gửi mail thông báo cho các nhân viên đã được lọc ở trên
        $arr_employees = array();
        foreach ($filter_array as $value) {
            $arr_employees[] = $value['full_name'] . ' (<@' . nv_strtolower($value['username']) . '>) ';
            // nv_sendmail($this->global_config['smtp_username'], $value['email'], $this->lang_module['message_have_not_checkout_yet'], $message);
        }
        if (!empty($arr_employees)) {
            $message = implode(', ', $arr_employees) . " " . sprintf($this->lang_module['take_checkout'], $value['checkout']);
            $message = urlencode($message);
            $this->sendSlack($message, '#vinades');
        }
        return 'have not member forget checkout';
    }

    /**
     * Tiến trình checkout cho những thành viên thuộc phòng ban được cấu hình checkin_out_session = 1
     */
    public function checkout_member()
    {
        $HR = new HumanResources();
        $this->db->sqlreset()
            ->select('t1.userid')
            ->from($this->table_module . '_human_resources t1')
            ->join('INNER JOIN ' . $this->table_module . '_department t2 ON t1.department_id = t2.id ' . 'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t3 ON t1.userid = t3.userid')
            ->where('t2.checkin_out_session	= 1');
        $result = $this->db->query($this->db->sql());
        while (list($userid) = $result->fetch(3)) {
            $HR->setMemberInfor($userid);
            $CHECKIO = new Checkio($HR);
            if ($CHECKIO->status_last['status'] == 'worktime') {
                $CHECKIO->checkOut();
            }
        }
    }
    /**
     * Tiến trình checkout cho tất cả thành viên, nên chạy vào lúc 23h50'
     */
    public function checkout_all()
    {
        $HR = new HumanResources();
        $this->db->sqlreset()
            ->select('t1.userid')
            ->from($this->table_module . '_human_resources t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.userid = t2.userid');
        $result = $this->db->query($this->db->sql());
        while (list($userid) = $result->fetch(3)) {
            $HR->setMemberInfor($userid);
            $CHECKIO = new Checkio($HR);
            $CHECKIO->checkOut();
        }
    }
}
