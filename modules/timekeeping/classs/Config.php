<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

/**
 * Để giúp ghi cấu hình theo tháng nhưng thuận tiện cho sử dụng
 * chương trình sẽ lưu cấu hình dạng kiểu tại thời điểm gần nhất
 */

namespace Modules\timekeeping\classs;

use XTemplate;
use PDO;

class Config extends Main
{
    private $tpl = 'config';
    protected $title  = 'config';
    protected $table_data = 'config';
    protected $weekday;
    // thời gian cho phép đăng nhập trước và sau giờ của phiên làm việc
    public $timePreAllow = 30;
    // thời gian trể làm tối đa cho phép
    public $timeAfterAllow = 150;
    // thời gian bắt đầu tính làm trể
    public $timeLateAllow = 5;
    public $dayAllowOfWeek = array(1, 2, 3, 4, 5);
    // cấu hình tuần thứ mấy trong tháng dành cho thứ 7 có thể làm việc
    public $work_week = array(1, 3);
    public $timeCheckio = array(
        array(
            'checkin' => 480, // tuong ung luc 8 gio (8*60=480)
            'checkout' => 720
        ),
        array(
            'checkin' => 810,
            'checkout' => 1050
        )
    );
    public $array_config_name = ['dayAllowOfWeek', 'timeCheckio', 'timePreAllow', 'timeAfterAllow', 'timeLateAllow', 'work_week', 'holidays'];
    public $config = array();
    public $holidays = array("30/04", "01/05", "02/09");
    public $userid = 0;
    public function __construct($userid = 0, $month = 0, $year = 0)
    {
        parent::__construct();
        $this->userid = $userid;
        $this->month = !empty($month) ? $month : $this->month;
        $this->year = !empty($year) ? $year : $this->year;
        $this->weekday = array(
            $this->lang_global['sunday'],
            $this->lang_global['monday'],
            $this->lang_global['tuesday'],
            $this->lang_global['wednesday'],
            $this->lang_global['thursday'],
            $this->lang_global['friday'],
            $this->lang_global['saturday']
        );
        $this->setConfigData();
    }
    public function setConfigData()
    {
        $this->db->sqlreset()
            ->select('*')
            ->from($this->table_data)
            ->where('userid = :userid AND config_name = :config_name AND (year*100 + month) <= ' . ($this->year * 100 + $this->month))
            ->order('year DESC, month DESC, id DESC')
            ->limit(1);
        $sth = $this->db->prepare($this->db->sql());
        foreach ($this->array_config_name as $config_name) {
            $sth->bindValue(':config_name', $config_name, PDO::PARAM_STR);
            $sth->bindValue(':userid',  $this->userid, PDO::PARAM_INT);
            $sth->execute();
            $row = $sth->fetch(2);
            if (empty($row) && !empty($this->userid)) {
                // Trường hợp lấy cấu hình của nhân viên, nếu không có nó sẽ lấy cấu hình chung
                $sth->bindValue(':userid',  0, PDO::PARAM_INT);
                $sth->execute();
                $row = $sth->fetch(2);
            }
            if (!empty($row)) {
                switch ($row['config_name']) {
                    case 'timePreAllow':
                        $this->timePreAllow = intval($row['config_value']);
                        break;
                    case 'timeAfterAllow':
                        $this->timeAfterAllow = intval($row['config_value']);
                        break;
                    case 'timeLateAllow':
                        $this->timeLateAllow = intval($row['config_value']);
                        break;
                    case 'dayAllowOfWeek':
                        $this->dayAllowOfWeek = explode(",", $row['config_value']);
                        break;
                    case 'work_week':
                        $this->work_week = explode(",", $row['config_value']);
                        break;
                    case 'holidays':
                        $this->holidays = explode(",", $row['config_value']);
                        break;
                    case 'timeCheckio':
                        $this->timeCheckio = unserialize($row['config_value']);
                        break;
                    default:
                        break;
                }
                $this->config[$row['config_name']] = $row;
            }
        }
    }
    public function getConfigData()
    {
        return array(
            'dayAllowOfWeek' => $this->dayAllowOfWeek,
            'timeCheckio' => $this->timeCheckio,
            'timePreAllow' => $this->timePreAllow,
            'timeAfterAllow' => $this->timeAfterAllow,
            'timeLateAllow' => $this->timeLateAllow,
            'work_week' => $this->work_week,
            'holidays' => $this->holidays
        );
    }
    public function resetConfig()
    {
        $this->timePreAllow = 30;
        $this->timeAfterAllow = 150;
        $this->timeLateAllow = 5;
        $this->dayAllowOfWeek = array(1, 2, 3, 4, 5);
        $this->work_week = array(1, 3);
        $this->timeCheckio = array(
            array(
                'checkin' => 480, // tuong ung luc 8 gio (8*60=480)
                'checkout' => 720
            ),
            array(
                'checkin' => 810,
                'checkout' => 1050
            )
        );
        $this->holidays = array("30/04", "01/05", "02/09");
    }
    public function setUserid($userid)
    {
        $this->userid = $userid;
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
     * Kiểm tra xem $timeCheckio có phiên làm việc trùng nhau không
     * @param $timeCheckio array(array('checkin'=>'xxxx','checkout'=>'xxxx'))
     * @return boolean;
     */
    public function testTimeCheckIo($timeCheckio)
    {
        $mask = array_fill(0, 24 * 60, false);
        $result = true;
        foreach ($timeCheckio as $time_range) {
            $checkin = intval($time_range['checkin']);
            $checkout = intval($time_range['checkout']);
            if ($checkout <= $checkin) {
                $result = false;
                break;
            }
            for ($i = $checkin; $i <= $checkout; $i++) {
                if ($mask[$i]) {
                    $result = false;
                    break;
                }
                $mask[$i] = true;
            }
        }
        return $result;
    }
    public function dataPost()
    {
        $post = array();
        $post['timePreAllow'] = $this->nv_Request->get_int('timePreAllow', 'post', 0);
        $post['timeAfterAllow'] = $this->nv_Request->get_int('timeAfterAllow', 'post', 0);
        $post['timeLateAllow'] = $this->nv_Request->get_int('timeLateAllow', 'post', 0);
        $dayAllowOfWeek = $this->nv_Request->get_array('dayAllowOfWeek', 'post', array());
        $post['dayAllowOfWeek'] = implode(",", $dayAllowOfWeek);
        $work_week = $this->nv_Request->get_array('work_week', 'post', array());
        $post['work_week'] = implode(",", $work_week);
        $holidays = $this->nv_Request->get_array('holidays', 'post', array());
        $post['holidays'] = implode(",", $holidays);

        $hour_checkin = $this->nv_Request->get_array('hour_checkin', 'post', array());
        $hour_checkout = $this->nv_Request->get_array('hour_checkout', 'post', array());
        $minute_checkin = $this->nv_Request->get_array('minute_checkin', 'post', array());
        $minute_checkout = $this->nv_Request->get_array('minute_checkout', 'post', array());
        $timeCheckio = array();
        for ($i = 0; $i < count($hour_checkin); $i++) {
            $timeCheckio[] = array(
                'checkin' => $hour_checkin[$i] * 60 + $minute_checkin[$i],
                'checkout' => $hour_checkout[$i] * 60 + $minute_checkout[$i]
            );
        }
        if (!$this->testTimeCheckIo($timeCheckio)) {
            nv_htmlOutput($this->lang_module['error_timeCheckIo']);
        }
        usort($timeCheckio, function ($a, $b) {
            if ($a['checkin'] == $b['checkin']) {
                return 0;
            }
            return $a['checkin'] < $b['checkin'] ? -1 : 1;
        });
        $post['userid'] = $this->nv_Request->get_int('userid', 'post', 0);
        $post['timeCheckio'] = serialize($timeCheckio);
        return $post;
    }
    public function saveConfig($data)
    {
        /**
         * tìm kiếm cấu hình trong tương ứng CSDL
         * Nếu cấu hình được lưu cuối cùng giống thì không lưu
         * Nếu cấu hình được lưu cuối cùng mà cùng tháng, cùng năm tại thời điểm lưu thì cập nhật
         * Nếu cấu hình được lưu cuối cùng mà khác tháng thì sẽ thêm mới
         * Sau này nếu lấy cấu hình tại một thời điểm bất kỳ nó sẽ lấy các cấu hình trước và gần thời điểm đó nhất
         */

        $old_value = array(
            'dayAllowOfWeek' => implode(',', $this->dayAllowOfWeek),
            'timeCheckio' => serialize($this->timeCheckio),
            'timePreAllow' => $this->timePreAllow,
            'timeAfterAllow' => $this->timeAfterAllow,
            'timeLateAllow' => $this->timeLateAllow,
            'work_week' => implode(',', $this->work_week),
            'holidays' => implode(',', $this->holidays)
        );
        $tien_trinh = array();
        foreach ($this->array_config_name as $config_name) {
            // không lưu ngày nghỉ lễ cho nhân viên
            if (!empty($data['userid']) && ($config_name == 'holidays')) continue;
            if ($old_value[$config_name] == $data[$config_name]) continue;
            $tien_trinh[] = $config_name . '_1';
            $row = $this->config[$config_name];

            if (($row['month'] != $this->month) || ($row['year'] != $this->year)) {
                $this->save(array(
                    'userid' => $data['userid'],
                    'month' => $this->month,
                    'year' => $this->year,
                    'config_name' => $config_name,
                    'config_value' => $data[$config_name]
                ));
            } else {
                $this->editById($row['id'], array(
                    'userid' => $data['userid'],
                    'month' => $this->month,
                    'year' => $this->year,
                    'config_name' => $config_name,
                    'config_value' => $data[$config_name]
                ));
            }
        }
        nv_insert_logs(NV_LANG_DATA, $this->module_name, 'Change config module', '', $this->admin_info['userid']);
        return true;
    }
    public function html()
    {

        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('timePreAllow', $this->timePreAllow);
        $xtpl->assign('timeAfterAllow', $this->timeAfterAllow);
        $xtpl->assign('timeLateAllow', $this->timeLateAllow);
        $xtpl->assign('checked_work_week_1', in_array(1, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_2', in_array(2, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_3', in_array(3, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_4', in_array(4, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_5', in_array(5, $this->work_week) ? 'checked' : '');
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', array(
                'value' => $k,
                'title' => $title
            ));
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('main.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', array(
                'value' => $value,
                'title' => $value
            ));
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('main.year');
        }

        for ($i = 0; $i < count($this->weekday); $i++) {
            $xtpl->assign('WEEKDAY', array(
                'value' => $i,
                'title' => $this->weekday[$i],
                'checked' => in_array($i, $this->dayAllowOfWeek) ? 'checked' : ''
            ));
            $xtpl->parse('main.dayAllowOfWeek');
        }
        foreach ($this->timeCheckio as $k => $session) {
            $ss = ($k == 0) ? 'morning' : 'afternoon';
            $xtpl->assign('session', $ss);
            $xtpl->assign('session_title', $this->lang_module[$ss]);
            foreach ($session as $ck => $value) {
                $xtpl->assign('time', $ck);
                $xtpl->assign('time_title',  $this->lang_module[$ck == 'checkin' ? 'start' : 'end']);
                $hour = floor($value / 60);
                $minute = $value % 60;
                for ($h = 0; $h <= 23; $h++) {
                    $xtpl->assign('hour', $h);
                    $xtpl->assign('hour_title', $h . " " . $this->lang_global['hour']);
                    $xtpl->assign('selected', $hour == $h ? 'selected' : '');
                    $xtpl->parse('main.session.time.hour');
                }
                for ($m = 0; $m <= 59; $m++) {
                    $xtpl->assign('minute', $m);
                    $xtpl->assign('minute_title', $m . " " . $this->lang_global['min']);
                    $xtpl->assign('selected', $minute == $m ? 'selected' : '');
                    $xtpl->parse('main.session.time.minute');
                }
                $xtpl->parse('main.session.time');
            }
            $xtpl->parse('main.session');
        }
        foreach ($this->holidays as $holiday) {
            $xtpl->assign('holiday', $holiday);
            $xtpl->parse('main.holiday');
        }
        $monthcr = date('n', $this->cr_time);
        $yearcr = date('Y', $this->cr_time);
        if ($yearcr * 100 + $monthcr <= $this->year * 100 + $this->month) {
            $xtpl->parse('main.submit');
        }
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
