<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */
/**
 * chú ý các phiên làm việc trong timeCheckio phải được sắp xếp theo thứ tự
 * Cái này sau này phải thêm một hàm ở Config để sắp xếp phiên làm việc.
 */
namespace Modules\timekeeping\classs;

use PDO;
use XTemplate;
use Exception;
use NukeViet\Module\timekeeping\Shared\Weeks;
use NukeViet\Module\timekeeping\Email\SubjectTag;

class Checkio extends Main
{

    // Những ngày (thứ) làm việc cho phép trong tuần
    public $dayAllowOfWeek;

    // Làm việc vào tuần thứ mấy trong tháng
    public $work_week = array();

    public $day = 0;

    public $month = 0;

    public $year = 0;

    public $hour = 0;

    public $minute = 0;

    public $weekday = 0;

    /**
     * Timestamp bắt đầu ngày 00:00:00 dd/mm/yyyy
     */
    public $firstTimestamp;

    public $timeLateAllow;

    public $timePreAllow;

    public $timeAfterAllow;

    /**
     * Ví dụ
     * [0][checkin] => 480
     *
     * [0][checkout] => 720
     *
     * [1][checkin] => 810
     *
     * [1][checkout] => 1050
     *
     * Đem chia 60 ra số giờ
     *
     * @var array
     */
    public $timeCheckio;

    /**
     * @var array lấy nguyên row cuối cùng mà chưa checkout trong bảng checkio
     */
    public $status_last;

    public $holidays;

    /**
     *
     * @var integer thời điểm bắt đầu làm việc buổi chiều, tính theo phút (8 giờ)
     */
    public $startAfternoonTimeWork = 0;

    /**
     *
     * @var integer thời điểm bắt đầu cho phép check in buổi chiều, tính theo giây timestamp
     */
    public $checkInStartTimeAfternoon;

    /**
     *
     * @var integer thời điểm bắt đầu ngày làm việc, tính theo phút (8 giờ)
     */
    public $startTimeWork = 0;

    /**
     *
     * @var integer thời điểm kết thúc ngày làm việc, tính theo phút (17 giờ 30 phút)
     */
    public $endTimeWork = 0;

    /**
     *
     * @var integer thời điểm bắt đầu cho phép check in, tính theo giây timestamp
     */
    public $checkInStartTime;
    /**
     * Danh sách địa chỉ theo ip, tạm thời đang code cứng ở đây
     */
    public $list_address_for_ip =[
        // '127.0.0.1' => 'localhost',
        '123.25.21.13' => 'Văn phòng công ty'
    ];
    /**
     * Api key của dịch vụ xác dịnh vị trí từ kinh độ, vĩ độ https://opencagedata.com/
     */
    public $opencagedata_key = 'b5a4b9a97add4b178d3563cebb02460f';
    /**
     *
     * @var integer thời điểm kết thúc cho phép check in, tính theo giây timestamp
     */
    public $checkInEndTime;

    /**
     * Trạng thái của lần checkin/out là làm hành chính (worktime) hay làm thêm (workout).
     */
    public $status_work = 'worktime';

    /**
     * Cập nhật thông tin làm thêm nếu checkin vào lúc làm thêm
     */
    public $workout = array();

    private $tpl = 'checkio';

    protected $title = 'checkio';

    protected $table_data = 'checkio';

    public function __construct($HR)
    {
        parent::__construct();
        $this->HR = $HR;

        $this->day = date('j', $this->cr_time);
        $this->month = date('n', $this->cr_time);
        $this->year = date('Y', $this->cr_time);
        $this->hour = date('G', $this->cr_time);
        $this->minute = date('i', $this->cr_time);
        $this->weekday = date('w', $this->cr_time);
        $CONFIG = new ConfigMember($this->HR->user_info['userid']);
        $this->timeLateAllow = $CONFIG->timeLateAllow;
        $this->timePreAllow = $CONFIG->timePreAllow;
        $this->timeAfterAllow = $CONFIG->timeAfterAllow;
        $this->dayAllowOfWeek = $CONFIG->dayAllowOfWeek;
        $this->work_week = $CONFIG->work_week;
        $this->timeCheckio = $CONFIG->timeCheckio;
        $this->holidays = $CONFIG->holidays;

        // Giờ bắt đầu của một ngày 0 giờ 0 phút 0 giấy ngày xx/xx/xxxxx
        $this->firstTimestamp = $this->cr_time - (($this->hour * 60 + $this->minute) * 60 + intval(date('s', $this->cr_time)));
        // Bắt đầu giờ làm buổi sáng
        $this->startTimeWork = !empty($this->timeCheckio[0]['checkin']) ? $this->timeCheckio[0]['checkin'] : 0;
        // Bắt đầu giờ làm buổi chiều
        $this->startAfternoonTimeWork = !empty($this->timeCheckio[1]['checkin']) ? $this->timeCheckio[1]['checkin'] : 0;
        // Kết thúc giờ làm của một ngày
        $this->endTimeWork = count($this->timeCheckio) > 0 ? $this->timeCheckio[count($this->timeCheckio) - 1]['checkout'] : 0;

        $this->checkInStartTimeAfternoon = $this->firstTimestamp + (($this->startAfternoonTimeWork - $this->timePreAllow) < 0 ? 0 : ($this->startAfternoonTimeWork - $this->timePreAllow)) * 60;
        $this->checkInStartTime = $this->firstTimestamp + (($this->startTimeWork - $this->timePreAllow) < 0 ? 0 : ($this->startTimeWork - $this->timePreAllow)) * 60;
        $this->checkInEndTime = $this->firstTimestamp + (($this->startTimeWork + $this->timeAfterAllow) > 24 * 60 ? 24 * 60 : ($this->startTimeWork + $this->timeAfterAllow)) * 60;
        $this->status_last = $this->getLastCheckIo();
    }

    public function get_place_from_coords($geolat, $geolng) {
        $place_fm = '';
        try {
            $geocoder = new \OpenCage\Geocoder\Geocoder($this->opencagedata_key);
            $result = $geocoder->geocode($geolat . ',' . $geolng, ['language' => 'vi']);
            $components = !empty($result['results'][0]['components']) ? $result['results'][0]['components'] : '';
            $place = [];
            if (!empty($components['city_district'])) {
                $place[] = $components['city_district'];
            }
            if (!empty($components['city'])) {
                $place[] = $components['city'];
            }
            if (!empty($components['state'])) {
                $place[] = $components['state'];
            }
            if (!empty($place)) {
                $place_fm = implode(', ', $place);
            }


        } catch (Exception $e) {
            trigger_error(print_r($e, true));
        }
        return $place_fm;
    }
    public function dataPost()
    {
        $post = array();
        $post['day'] = $this->day;
        $post['month'] = $this->month;
        $post['year'] = $this->year;
        $post['userid'] = $this->HR->user_info['userid'];
        $post['geolat'] = $this->nv_Request->get_title('geolat', 'post', '0');
        $post['geolng'] = $this->nv_Request->get_title('geolng', 'post', '0');
        $post['place'] = $this->get_place_from_coords($post['geolat'], $post['geolng']);
        $post['workout_id'] = $this->nv_Request->get_int('workout_id', 'post', 0);
        $post['description'] = $this->nv_Request->get_title('description', 'post', '');
        $department_id = $this->HR->user_info['department_id'];
        $DEPARTMENT = new Department();
        $department_item = $DEPARTMENT->getById($this->HR->user_info['department_id'], 'checkin_out_session');
        $checkin_out_session = $department_item['checkin_out_session'];
        if (!$this->checkInAllow()) {
            nv_htmlOutput($this->lang_module['error_not_checkin']);
            exit();
        }
        $late = 0;
        $checkInInsert = 0;
        if ($checkin_out_session == 1) {
            //Trường hợp có checkin/out theo phiên
            // Lấy lần checkin đầu tiên của buổi chiều - nếu empty thì sẽ tính là lần checkin đầu tiên của buổi chiều
            $first_checkin_afternoon = $this->getFirstAfternoonCheckIo();
            if (empty($this->status_last) && $this->status_work == 'worktime') {
                // Lần checkin đầu tiên trong ngày rơi vào giờ làm việc hành chính - buổi sáng
                $session_work = $this->getSessionWork($this->cr_time);
                $checkInInsert = $this->firstTimestamp + $session_work['checkin'] * 60;
                $late = round(($this->cr_time - $checkInInsert) / 60);
                $late = $late > $this->timeLateAllow ? $late : 0;
            } else if (empty($first_checkin_afternoon) && $this->status_work == 'worktime' && ($this->cr_time >= $this->checkInStartTimeAfternoon)){
                // Lần checkin lần đầu tiên vào buổi chiều và rơi vào giờ làm việc hành chính - buổi chiều
                $session_work = $this->getSessionWork($this->cr_time);
                $checkInInsert = $this->firstTimestamp + $session_work['checkin'] * 60;
                $late = round(($this->cr_time - $checkInInsert) / 60);
                $late = $late > $this->timeLateAllow ? $late : 0;
            } else if (!empty($this->status_last['checkout'])) {
                $checkInInsert = $this->cr_time;
            }
        } else {
            //Trường hợp không có checkin/out theo phiên
            if (empty($this->status_last) && ($this->status_work == 'worktime')) {
                // lần checkin đầu tiên trong ngày và rơi vào giờ làm việc hành chính
                $session_work = $this->getSessionWork($this->cr_time);
                $checkInInsert = $this->firstTimestamp + $session_work['checkin'] * 60;
                $late = round(($this->cr_time - $checkInInsert) / 60);
                $late = $late > $this->timeLateAllow ? $late : 0;
            } else if (!empty($this->status_last['checkout'])) {
                $checkInInsert = $this->cr_time;
            }
        }
        $post['checkin'] = $checkInInsert;
        $post['checkin_real'] = $this->cr_time;
        $post['late'] = $late;
        $post['status'] = $this->status_work;
        return $post;
    }

    /**
     * Lấy thời gian check in sau cùng.
     * I. Checkin >= giờ làm việc đầu tiên trong ngày
     * 1. Trả về dữ liệu là lần checkin/out sau cùng với điều kiện checkin_real >= giờ làm việc đầu tiên.
     * Nếu chưa checkout thì cho phép checkout, nếu đã checkout thì cho phép checkin
     * 2. Trả về dữ liệu là lần checkin/out sau cùng với điều kiện là làm thêm (status = "workout") và chưa checkout
     * 3. Empty không thỏa mãn hai điều kiện trê
     * (sẽ tính đây là lần đăng nhập đầu tiên)
     * II. Checkin <= giờ làm việc đầu tiên trong ngày
     * Luôn trả về lần checkin/out sau cùng
     * Nếu chưa checkout thì cho phép checkout, nếu đã checkout thì cho phép checkin
     * Những nhân viên nào hôm qua quên checkout thì đến trước giờ làm việc có thể vào và checkout
     *
     * @return
     */
    public function getLastCheckIo()
    {
        $this->db->sqlreset()
            ->select('*')
            ->from($this->table_data)
            ->order('id DESC')
            ->limit(1);

        $where = 'userid = ' . $this->HR->user_info['userid'];
        $where .= ' AND (( status = "worktime" AND checkin_real >= ' . ($this->cr_time - 24 * 60 * 60);
        if ($this->cr_time >= $this->checkInStartTime) {
            $where .= ' AND checkin_real >= ' . $this->checkInStartTime;
        }
        $where .= ') OR (status = "workout" AND checkout IS NULL))';
        $this->db->where($where);
        return $this->db->query($this->db->sql())
            ->fetch(PDO::FETCH_ASSOC);
    }

     /*
     * Kiem tra lần check in đầu tiên buổi chiều
     */
    public function getFirstAfternoonCheckIo()
    {
        $this->db->sqlreset()
            ->select('*')
            ->from($this->table_data)
            ->where('checkin_real >= ' . $this->checkInStartTimeAfternoon . ' AND status = "worktime" AND userid=' . $this->HR->user_info['userid'])
            ->limit(1)
            ->order('id DESC');
            return $this->db->query($this->db->sql())
            ->fetch(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @param
     *            $timestamp
     * @return integer là thứ tự của thứ trong tháng (ví dụ như là thứ 3 đầu tiên của tháng)
     */
    public function getNumberWeekOfMonth($timestamp)
    {
        $tt = 0;
        $weekday = date('w', $timestamp);
        $j = date('j', $timestamp);
        for ($day = 1; $day <= $j; $day++) {
            $time = mktime(0, 0, 1, date('n', $timestamp), $day, date('Y', $timestamp));
            $wd = date('w', $time);
            $tt += ($wd == $weekday) ? 1 : 0;
        }
        return $tt;
    }

    /*
     * Kiem tra ngay nghi chung nhu 30 /4, 1/5 ngay tet
     */
    public function checkHoliday($timestamp)
    {
        $day = date('d', $timestamp);
        $month = date('m', $timestamp);
        return in_array($day . '/' . $month, $this->holidays);
    }

    /*
     * Kiem tra ngay nghi phep hay di cong tac
     */
    public function checkVacation()
    {
        $this->db->sqlreset()
            ->select('COUNT(*)')
            ->from($this->table_module . "_business_trip")
            ->where($this->cr_time . ">=start_time AND " . $this->cr_time . "<end_time AND userid=" . $this->HR->user_info['userid']);
        $num_items = $this->db->query($this->db->sql())
            ->fetchColumn();
        return $num_items > 0;
    }

    /**
     * Kiểm tra những thứ làm việc cho phép.
     * Mặc định là thứ 2, 3, 4, 5, 6 và thứ 7 thì là thứ 7 lần thứ mấy trong tháng
     */
    public function checkDayAllow($timestamp)
    {
        $weekday = date('w', $timestamp);
        $checkInAllow = true;
        // kiem tra cac thu trong tuan duoc phep checkin duoc khai bao o tren.
        // Rieng thu 7 thi do la thu 7 cua cac tuan le trong nam
        $wd = $this->getNumberWeekOfMonth($timestamp);
        $checkInAllow = $checkInAllow && (in_array($weekday, $this->dayAllowOfWeek) || (($weekday == 6) && (in_array($wd, $this->work_week))));
        return $checkInAllow;
    }

    /**
     * Lấy phiên làm việc tương ứng thời gian đưa vào
     * kiểm tra xem checkin có thuộc một phiên làm việc nào không
     * Phên làm việc sẽ được bắt đầu sớm hơn 30 phút (timePreAllow) so với giờ làm việc chính thức
     */
    public function getSessionWork($timestamp)
    {
        $hour = date('G', $timestamp);
        $minute = date('i', $timestamp);
        $cr_time = $hour * 60 + $minute;
        foreach ($this->timeCheckio as $session_work) {
            $checkin = $session_work['checkin'];
            $checkout = $session_work['checkout'];
            if (($cr_time >= ($checkin - $this->timePreAllow)) && ($cr_time <= $checkout)) {
                return $session_work;
                break;
            }
        }
        return 0;
    }

    /**
     * Chỉ không cho checkin duy nhất trường hợp làm trong giờ hành chính mà checkin quá trễ.
     */
    public function checkInAllow()
    {
        if ($this->checkOutAllow()) {
            return false;
        }
        $result = true;

        // Kiểm tra ngày làm việc
        $result = $result && $this->checkDayAllow($this->cr_time);

        // Kiểm tra ngày nghỉ lễ
        $result = $result && !$this->checkHoliday($this->cr_time);
        // Kiểm tra có thuộc phiên làm việc không
        $session_work = $this->getSessionWork($this->cr_time);
        $result = $result && !empty($session_work);
        $this->status_work = $result ? 'worktime' : 'workout';

        // Kiểm tra trường hợp duy nhật không cho phép checkin
        if ($result && empty($this->status_last) && ($this->cr_time > ($this->firstTimestamp + ($session_work['checkin'] + $this->timeAfterAllow) * 60))) {
            return false;
        }

        // Kiểm tra xem làm thêm thì có thuộc phần làm thêm đã được đăng ký hay làm thêm đột xuất không
        if ($this->status_work == 'workout') {
            $this->db->sqlreset()
                ->select('*')
                ->from($this->table_module . '_workout')
                ->where('start_time < ' . ($this->cr_time + $this->timePreAllow * 60) . '  AND end_time > ' . $this->cr_time . ' AND confirm > 0 AND userid = ' . $this->HR->user_info['userid'])
                ->limit(1)
                ->order('id');
            $this->workout = $this->db->query($this->db->sql())
                ->fetch(2);
        }
        return true;
    }

    /**
     * Hàm checkout
     *
     * @param boolean $auto hệ thống tự checkout
     * @return boolean
     */
    public function checkOut($auto = false)
    {
        // Kiểm tra nếu không đang checkin
        if (empty($this->status_last)) {
            return false;
        }
        // Kiểm tra nếu đã checkout rồi
        if (!empty($this->status_last['checkout'])) {
            return false;
        }

        // Dữ liệu update
        $data = [
            'checkout' => null,
            'auto' => $auto ? 1 : 0,
            'checkout_real' => NV_CURRENTTIME
        ];

        /**
         * Trường hợp làm chính thức
         */
        if ($this->status_last['status'] == 'worktime') {
            // Bắt đầu ngày của phiên checkin cuối cùng
            $timeStartDayCheckin = mktime(0, 0, 0, date('n', $this->status_last['checkin_real']), date('j', $this->status_last['checkin_real']), date('Y', $this->status_last['checkin_real']));

            /*
             * - Xác định phiên checkin này có đúng vào phiên làm việc nào hay không.
             * 1 Nếu đúng thì thời gian tính từ phiên đó đến kết thúc các phiên
             * 2 Nếu không tìm phiên gần nhất và thời gian trễ sau đó tính như bước 1 và trừ bớt thời gian trễ
             */
            $late_offset = 0;
            $this_checkin = 0;
            foreach ($this->timeCheckio as $timeCheckio) {
                $checkin = ($timeCheckio['checkin'] * 60 + $timeStartDayCheckin);
                // Có phiên checkin
                if ($this->status_last['checkin'] == $checkin) {
                    $this_checkin = $checkin;
                    break;
                }
            }
            if (empty($this_checkin)) {
                // Tìm phiên và thời gian trễ.
                // Ở đây chỉ tính trễ phiên, còn sớm phiên thì phần checkin đã xử lý để đưa về phiên gần nhất
                for ($i = sizeof($this->timeCheckio) - 1; $i >= 0; $i--) {
                    $timeCheckio = $this->timeCheckio[$i];
                    $checkin = ($timeCheckio['checkin'] * 60 + $timeStartDayCheckin);
                    $checkout = ($timeCheckio['checkout'] * 60 + $timeStartDayCheckin);
                    if ($checkin < $this->status_last['checkin_real'] and $checkout > $this->status_last['checkin_real']) {
                        $this_checkin = $checkin;
                        $late_offset = $this->status_last['checkin_real'] - $this_checkin;
                        break;
                    }
                }
            }
            // Nếu không tìm thấy phiên nào tức là phần xử lý checkin không đúng.
            // Phần đó cần phải đưa về workout
            if (empty($this_checkin)) {
                return false;
            }

            /*
             * Tính ra thời gian kết thúc phiên làm việc và số công. Nguyên tắc
             * - Lặp phiên làm việc trong ngày từ đầu đến cuối, tính sum các phiên trong khoảng thời gian checkin checkout
             */
            $this_checkout = 0;
            $this_workday = 0;
            foreach ($this->timeCheckio as $timeCheckio) {
                $checkin = ($timeCheckio['checkin'] * 60 + $timeStartDayCheckin);
                $checkout = ($timeCheckio['checkout'] * 60 + $timeStartDayCheckin);

                if ($checkin >= $this_checkin and $checkout <= NV_CURRENTTIME) {
                    // Toàn phiên
                    $this_workday += $checkout - $checkin;
                    $this_checkout = $checkout;
                } elseif ($checkin >= $this_checkin and NV_CURRENTTIME > $checkin and $checkout > NV_CURRENTTIME) {
                    // Nghỉ giữa phiên
                    $this_workday += NV_CURRENTTIME - $checkin;
                    $this_checkout = NV_CURRENTTIME;
                }
            }
            // Trường hợp không tìm thấy số công là phần checkin xử lý chưa đúng
            if (empty($this_workday)) {
                return false;
            }

            $data['checkout'] = $this_checkout;
            // Giây đổi ra giờ tính % của 8h, làm tròn 2 chữ số
            $data['workday'] = round((($this_workday - $late_offset) / 60 / 60) / 8 * 100, 2);
        }

        /**
         * Trường hợp làm thêm đột xuất
         */
        if ($this->status_last['status'] == 'workout' and empty($this->status_last['workout_id'])) {
            // Làm đột xuất không tính công, chờ duyệt sau
            $data['checkout'] = NV_CURRENTTIME;
        }

        /**
         * Trường hợp làm theo lịch đăng ký
         */
        if ($this->status_last['status'] == 'workout' and !empty($this->status_last['workout_id'])) {
            /*
             * Cách tính như sau:
             * - Checkin trước thời gian đăng ký lấy thời gian đăng Ký
             * - Checkin sau thời gian đăng ký lấy thời gian checkin
             * - Checkout trước thời gian đăng ký lấy thời gian checkout
             * - Checkout sau thời gian đăng ký lấy thời gian đăng ký
             */
            $CHECKOUT = new WorkOut();
            $workout = $CHECKOUT->getById($this->status_last['workout_id']);
            $checkin = $this->status_last['checkin_real'] > $workout['start_time'] ? $this->status_last['checkin_real'] : $workout['start_time'];
            $checkout = NV_CURRENTTIME < $workout['end_time'] ? NV_CURRENTTIME : $workout['end_time'];
            $data['workday'] = round(($checkout - $checkin) / 60, 2);
            $data['checkout'] = NV_CURRENTTIME;

            /**
             * Tính số công làm thêm tối đa 1 phiên 8 tiếng.
             * Tránh trường hợp làm từ sáng đến chiều tính cả nghỉ trưa
             */
            if ($data['workday'] > 100) {
                $data['workday'] = 100;
            }
        }

        $check = $this->editById($this->status_last['id'], $data);
        return $check;
    }

    /**
     * Hàm kiểm tra nút checkout có được hiển thị hay không
     * Được checkout khi lần check in trước có và chưa checkout
     *
     * @return boolean
     */
    public function checkOutAllow()
    {
        $checkOutAllow = true;
        $checkOutAllow = $checkOutAllow && !empty($this->status_last) && empty($this->status_last['checkout']);
        return $checkOutAllow;
    }

    public function html()
    {
        $base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $this->module_name . '&amp;' . NV_OP_VARIABLE . '=' . $this->op;
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('BASE_URL', $base_url);

        $your_address_current = in_array($this->client_info['ip'], array_keys($this->list_address_for_ip)) ?  $this->list_address_for_ip[$this->client_info['ip']] : '';
        $xtpl->assign('your_address_current', $your_address_current);

        if (!empty($this->timeCheckio)) {
            $checkCheckIn = $this->checkInAllow();
            $checkOutAllow = $this->checkOutAllow();
        } else {
            $checkCheckIn = 9999;
            $checkOutAllow = false;
        }

        if ($checkCheckIn) {
            $xtpl->parse('main.view_checkin');
        }

        if ($checkOutAllow) {
            $xtpl->parse('main.view_checkout');
        }

        $xtpl->assign('CHECKSS', $this->checkss);

        if (!$checkCheckIn && !$checkOutAllow) {
            $f_checkin = ($this->firstTimestamp + $this->getSessionWork($this->cr_time)['checkin'] * 60 + $this->timeAfterAllow * 60);
            $xtpl->assign('CHECKIN_MESSAGE', sprintf($this->lang_module['nocheckin_reason_4'], nv_date('H:i:s', $f_checkin)));
            $xtpl->parse('main.checkin_message');
        }

        if (!empty($this->status_last) && empty($this->status_last['checkout'])) {
            $work_time_allow = array_fill(0, 24 * 60, 0);

            foreach ($this->timeCheckio as $time_range) {
                for ($i = $time_range['checkin']; $i <= $time_range['checkout']; $i++) {
                    $work_time_allow[$i] = 1;
                }
            }

            $xtpl->assign('CHECKIN_TIME_MESSAGE', $this->lang_module[$this->status_last['status'] == 'workout' ? 'checkin_workout_time' : 'checkin_time']);
            $xtpl->assign('checkin_time', date('G:i  j/n/Y', $this->status_last['checkin_real']));
            $xtpl->assign('sum_time', $this->cr_time - $this->status_last['checkin_real']);
            $xtpl->assign('work_time_allow', implode(',', $work_time_allow));

            if (!empty($this->status_last['late'])) {
                $xtpl->assign('late', sprintf($this->lang_module['late_minute'], $this->status_last['late']));
                $xtpl->parse('main.info.late');
            }

            $xtpl->parse('main.info');

            // Thông báo làm thêm tối đa 8 tiếng
            if ($this->status_last['status'] == 'workout') {
                $xtpl->parse('main.info_workout');
            }
        }

        $weeks = Weeks::get7thDayOfMonth(0, 0, 1);
        foreach ($weeks as $week_stt => $week) {
            $xtpl->assign('WEEK_STT', $week_stt);
            $xtpl->assign('WEEK_TEXT', in_array($week_stt, $this->work_week) ? $this->lang_module['day_worktime'] : $this->lang_module['day_workout']);
            $xtpl->assign('WEEK', $week);
            $xtpl->assign('WEEK_CLASS', in_array($week_stt, $this->work_week) ? ' worktime' : ' workout');
            $xtpl->parse('main.week');
        }

        $worktime = true;
        // Kiểm tra ngày làm việc
        $worktime = $worktime && $this->checkDayAllow($this->cr_time);
        // Kiểm tra ngày nghỉ lễ
        $worktime = $worktime && !$this->checkHoliday($this->cr_time);
        $xtpl->assign('WORK_TIMEOUT_MESSAGE', sprintf($this->lang_module['checkio_mess_timeout'], date('d/m/Y'), ($worktime ? $this->lang_module['day_worktime'] : $this->lang_module['day_workout'])));

        $xtpl->parse('main');
        return $xtpl->text('main');
    }

    /**
     *
     * @param string $status
     * @param string $place
     * @return mixed
     */
    public function sendAPI($status, $place = '', $reason_work_out = '')
    {
        $message = $this->HR->user_info['full_name'] . ' (' . $this->HR->user_info['username'] . ') ' . $status . ' ' . $this->lang_module['at'] . ' *' . date('H:i:s d/m/Y') . '*' . ($place ? (' ' . $this->lang_module['from_place'] . ' *' . $place . '*') : '') . ($reason_work_out ? ' :' . $reason_work_out : '') . '.';
        return $this->sendSlack($message, '#vinades');
    }

    /**
     * Gửi email cho lãnh đạo thông báo làm thêm đột xuất
     */
    public function sendEmailManageWorkout()
    {
        $list_manager = $this->HR->getManager();
        if (empty($list_manager)) {
            return false;
        }
        $managers = array_shift($list_manager);
        try {
            $name_manager = $to = [];
            foreach ($managers as $manager) {
                $name_manager[] = $manager['full_name'] . ' (' . $manager['office_title'] . ')';
                $to[] = $manager['email'];
            }
            $name_manager = implode(', ', $name_manager);
            $to = array_values(array_unique($to));

            $from = [
                $this->global_config['site_name'],
                $this->global_config['site_email']
            ];
            $subject = SubjectTag::getWorkOutSurprise($this->HR->user_info['full_name'], date('Y', $this->cr_time), $this->HR->user_info['userid']);
            $link = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $this->module_name . '&amp;' . NV_OP_VARIABLE . '=workout-confirm&amp;workout_type=surprise&amp;month=' . date('n', $this->cr_time) . '&amp;year=' . date('Y', $this->cr_time);
            $link = NV_MY_DOMAIN . nv_url_rewrite($link, true);
            $message = sprintf(
                $this->lang_module['checkout_incident_message'],
                $name_manager,
                $this->HR->user_info['full_name'],
                date('H:i d/m/Y', $this->status_last['checkin_real']),
                date('H:i d/m/Y', $this->cr_time),
                $this->status_last['description'],
                $link
            );
            nv_sendmail($from, $to, $subject, $message);
        } catch (Exception $e) {
            trigger_error(print_r($e, true));
        }
        return true;
    }
}
