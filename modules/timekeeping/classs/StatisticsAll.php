<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */
namespace Modules\timekeeping\classs;

use XTemplate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StatisticsAll extends Main
{

    protected $tpl = 'statistics';

    protected $title = 'statistics';

    protected $table_data = 'checkio';

    protected $table_leave_absence = 'leave_absence';

    protected $num_items = 0;
    public $search_array = array(
        'name' => '',
        'email' => '',
        'department_id' => '',
        'office_id' => '',
    );
    public function __construct()
    {
        parent::__construct();
        $this->search_array['search_day'] = $this->nv_Request->get_title('search_day', 'get, post', date('d/m/Y', $this->cr_time));
        $this->search_array['name'] = $this->nv_Request->get_title('name', 'get, post', '');
        $this->search_array['email'] = $this->nv_Request->get_title('email', 'get, post', '');
        $this->search_array['department_id'] = $this->nv_Request->get_int('department_id', 'get, post', 0);
        $this->search_array['office_id'] = $this->nv_Request->get_int('office_id', 'get, post', '');
    }
    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function dataPost()
    {
        $post = [];
        return $post;
    }

    public function getData($userids, $month, $year)
    {
        $dataReturn = [];
        if (empty($userids)) {
            return $dataReturn;
        }
        $this->db->sqlreset()
            ->select('SUM(late) AS late, SUM(workday) AS workday, userid, status')
            ->from($this->table_data)
            ->where('month=' . $month . ' AND year=' . $year . ' AND userid IN (' . implode(',', $userids) . ')')
            ->group('userid, status');
        $return = $this->db->query($this->db->sql());
        while ($row = $return->fetch(2)) {
            if (empty($dataReturn[$row['userid']])) {
                $dataReturn[$row['userid']] = array(
                    'userid' => 0,
                    'late' => 0,
                    'worktime' => 0,
                    'workday' => 0,
                    'workout' => 0
                );
            }
            $dataReturn[$row['userid']]['userid'] = $row['userid'];
            $dataReturn[$row['userid']]['late'] += $row['late'];
            $dataReturn[$row['userid']][$row['status']] += round($row['workday'] / 100, 1);
            $dataReturn[$row['userid']]['workday'] += round($row['workday'] / 100, 1);
        }
        return $dataReturn;
    }

    public function getStatisticsDay($userids) {
        $dataReturn = [];
        $day = $month = $year = 0;
        if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $this->search_array['search_day'], $m)) {
            list(,$day, $month, $year) = array_map(
                    function($v) { return intval($v); }, $m
            );
        }
        if (empty($userids) || empty($day) || empty($month) || empty($month)) {
            return $dataReturn;
        }

        $this->db->sqlreset()
            ->select('*')
            ->from($this->table_data)
            ->where('day=' . $day . ' AND month=' . $month . ' AND year=' . $year . ' AND userid IN (' . implode(',', $userids) . ')');
        $return = $this->db->query($this->db->sql());
        while ($row = $return->fetch(2)) {
            $dataReturn[$row['userid']][] = $row;
        }
        return $dataReturn;
    }

    public function getLeaveAbsenceList($month, $year)
    {
        $dataReturn = [];
        $num_days_in_month = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));
        $start_of_the_month = mktime(0, 0, 0, intval($month), 1, intval($year));
        $end_of_the_month = mktime(23, 59, 59, intval($month), $num_days_in_month, intval($year));
        $month_range_sql_phrase = $start_of_the_month . ' AND ' . $end_of_the_month;
        $this->db->sqlreset()
            ->select('userid, reason_leave, type_leave, date_leave, start_leave, end_leave, half_day_start, half_day_end, use_absence_year')
            ->from($this->table_module . '_' . $this->table_leave_absence)
            ->
        // lấy cả những đợt nghỉ giữa 2 tháng
        where('((end_leave BETWEEN ' . $month_range_sql_phrase . ')' . ' OR (start_leave BETWEEN ' . $month_range_sql_phrase . ')' . ' OR (date_leave BETWEEN ' . $month_range_sql_phrase . ')' . ') AND confirm = 1'); // đã xác nhận

        $result = $this->db->query($this->db->sql());
        if (!empty($result)) {
            while ($row = $result->fetch(2)) { // 2 - PDO::FETCH_ASSOC
                if ($row['type_leave'] == 1) { // Nghỉ cả ngày
                    $month_of_first_leaving_day = date('n', $row['start_leave']);
                    $first_leaving_day = date('j', $row['start_leave']);

                    // nếu ngày bắt đầu nghỉ từ tháng trước
                    if ($month_of_first_leaving_day < $month) {
                        $first_leaving_day = 1;
                    }

                    $last_leaving_day = date('j', $row['end_leave']);

                    // fail-safe, lỗi DB hoặc từ nguồn vào, bỏ qua trường hợp này
                    if ($first_leaving_day > $last_leaving_day) {
                        continue;
                    }

                    for ($day = $first_leaving_day; $day <= $last_leaving_day; $day++) {
                        $dataReturn[$row['userid']][$day] = [
                            'useyear' => $row['use_absence_year'],
                            'reason' => $row['reason_leave'],
                            'type' => '1'
                        ];
                    }

                    // ngày nghỉ đầu là buổi chiều : 2
                    if ($row['half_day_start'] != 0) {
                        $dataReturn[$row['userid']][$first_leaving_day]['type'] = '0.5';
                        $dataReturn[$row['userid']][$first_leaving_day]['useyear'] = $row['use_absence_year'];
                    }

                    // ngày nghỉ cuối là buổi sáng : 1
                    if ($row['half_day_end'] != 0) {
                        $dataReturn[$row['userid']][$last_leaving_day]['type'] = '0.5';
                        $dataReturn[$row['userid']][$last_leaving_day]['useyear'] = $row['use_absence_year'];
                    }
                } else if ($row['type_leave'] == 0.5) {
                    // Nghỉ nửa ngày, còn lại nếu có thì kệ
                    $leaving_day = date('j', $row['date_leave']);
                    $dataReturn[$row['userid']][$leaving_day]['reason'] = $row['reason_leave'];
                    $dataReturn[$row['userid']][$leaving_day]['type'] = '0.5';
                    $dataReturn[$row['userid']][$leaving_day]['useyear'] = $row['use_absence_year'];
                }
            }
        }
        return $dataReturn;
    }

    public function list_statistics_month($per_page = 30, $page = 1, $month = 0, $year = 0)
    {
        $HR = new HumanResources();
        $base_url = $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;statistics_type=statistics_month&amp;month=' . $month . '&amp;year=' . $year . '&amp;per_page=' . $per_page;
        if (!empty($this->search_array['name'])) {
            $base_url .= '&amp;name=' . $this->search_array['name'];
        }
        if (!empty($this->search_array['email'])) {
            $base_url .= '&amp;email=' . $this->search_array['email'];
        }
        if (!empty($this->search_array['department_id'])) {
            $base_url .= '&amp;department_id=' . $this->search_array['department_id'];
        }
        if (!empty($this->search_array['office_id'])) {
            $base_url .= '&amp;office_id=' . $this->search_array['office_id'];
        }
        $dataMember = $HR->getData($per_page, $page);
        $this->num_items = $HR->num_items;
        $userids = array_map(function ($row) {
            return $row['userid'];
        }, $dataMember);
        $dataWorkday = $this->getData($userids, $month, $year);

        $DATA = [];
        foreach ($dataMember as $row) {
            $row['late'] = isset($dataWorkday[$row['userid']]) ? $dataWorkday[$row['userid']]['late'] : 0;
            $row['workday'] = isset($dataWorkday[$row['userid']]) ? $dataWorkday[$row['userid']]['workday'] : 0;
            $row['worktime'] = isset($dataWorkday[$row['userid']]) ? $dataWorkday[$row['userid']]['worktime'] : 0;
            $row['workout'] = isset($dataWorkday[$row['userid']]) ? $dataWorkday[$row['userid']]['workout'] : 0;
            $row['detail'] = $this->base_url . '&amp;' . NV_OP_VARIABLE . '=statistics_user&amp;userid=' . $row['userid']
            . '&amp;month=' . $month . '&amp;year=' . $year;
            $DATA[] = $row;
        }

        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('BANG_CONG', sprintf($this->lang_module['timesheets'], $month, $year));

        $tt = ($page - 1) * $per_page;
        $sumLate = 0;
        $sumWork = 0;
        $sum_worktime = 0;
        $sum_workout = 0;
        foreach ($DATA as $ROW) {
            $tt++;
            $ROW['tt'] = $tt;
            $xtpl->assign('ROW', $ROW);
            $sumLate += $ROW['late'];
            $sum_worktime += $ROW['worktime'];
            $sum_workout += $ROW['workout'];
            $sumWork += $ROW['workday'];
            $xtpl->parse('list_statistics_month.loop');
        }
        $sumWork = round($sumWork, 1);
        $xtpl->assign('sumLate', $sumLate);
        $xtpl->assign('sumWork', $sumWork);
        $xtpl->assign('sum_worktime', $sum_worktime);
        $xtpl->assign('sum_workout', $sum_workout);
        $generate_page = nv_generate_page($base_url, $this->num_items, $per_page, $page);
        if (!empty($generate_page)) {
            $HR = new HumanResources();
            $xtpl->assign('GENERATE_PAGE', $generate_page);
            $xtpl->parse('list_statistics_month.generate_page');
        }
        $xtpl->parse('list_statistics_month');
        return $xtpl->text('list_statistics_month');
    }

    public function list_statistics_day($per_page = 30, $page = 1) {
        $HR = new HumanResources();
        $base_url = $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;statistics_type=statistics_day&amp;search_day=' . $this->search_array['search_day'] .'&amp;per_page=' . $per_page;
        if (!empty($this->search_array['name'])) {
            $base_url .= '&amp;name=' . $this->search_array['name'];
        }
        if (!empty($this->search_array['email'])) {
            $base_url .= '&amp;email=' . $this->search_array['email'];
        }
        if (!empty($this->search_array['department_id'])) {
            $base_url .= '&amp;department_id=' . $this->search_array['department_id'];
        }
        if (!empty($this->search_array['office_id'])) {
            $base_url .= '&amp;office_id=' . $this->search_array['office_id'];
        }
        $dataMember = $HR->getData($per_page, $page);
        $this->num_items = $HR->num_items;
        $userids = array_map(function ($row) {
            return $row['userid'];
        }, $dataMember);
        $dataWorkday = $this->getStatisticsDay($userids);

        $DATA = [];
        foreach ($dataMember as $row) {
            $DATA[$row['userid']] = $row;
            if (empty($DATA[$row['userid']]['workday'])) {
                $DATA[$row['userid']]['workday'] = array();
            }
            if (!empty($dataWorkday[$row['userid']])) {
                $DATA[$row['userid']]['workday'] = $dataWorkday[$row['userid']];
            }
        }

        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('BANG_CONG', $this->lang_module['timesheets_day'] . ': ' . $this->search_array['search_day']);
        $tt = ($page-1) * $per_page;
        foreach ($DATA as $row) {
            $tt ++;
            $xtpl->assign('tt', $tt);
            $xtpl->assign('rowspan', count($row['workday']));
            $row['info'] = $this->lang_module['department'] . ': ' . $row['department_title'] . ' '
                . $this->lang_module['office'] . ': ' . $row['office_title'];
            $xtpl->assign('ROW', $row);
            $one_row = true;
            foreach ($row['workday'] as $row1) {
                $row1['checkin'] = date('H:i', $row1['checkin_real']);
                $row1['checkout'] = date('H:i', $row1['checkout_real']);
                $row1[$row1['status']] = round($row1['workday'] / 100, 1);
                $xtpl->assign('ROW1', $row1);
                if ($one_row) {
                    $xtpl->parse('list_statistics_day.loop.first_loop');
                }else {
                }
                $xtpl->parse('list_statistics_day.loop');
                $one_row = false;
            }
        }
        $generate_page = nv_generate_page($base_url, $this->num_items, $per_page, $page);
        if (!empty($generate_page)) {
            $HR = new HumanResources();
            $xtpl->assign('GENERATE_PAGE', $generate_page);
            $xtpl->parse('list_statistics_day.generate_page');
        }
        $xtpl->parse('list_statistics_day');
        return $xtpl->text('list_statistics_day');
    }

    public function html_statistics_day($per_page, $page) {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op  . '&amp;statistics_type=statistics_day');
        $xtpl->assign('search_of_per_page', $per_page);
        $search_day = $this->search_array['search_day'] ? $this->search_array['search_day'] :  date('d/m/Y', NV_CURRENTTIME);
        $xtpl->assign('search_day', $search_day);
        if ($this->search_array['name']) {
            $xtpl->assign('search_name', $this->search_array['name']);
        }
        if ($this->search_array['email']) {
            $xtpl->assign('search_email', $this->search_array['email']);
        }
        $DEPARTMENT = new Department();
        $list_department = $DEPARTMENT->getAll();
        foreach ($list_department as $row) {
            $xtpl->assign('department_id', $row['id']);
            $xtpl->assign('department_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['department_id'] ? 'selected' : '');
            $xtpl->parse('statistics_day.search_department');
        }
        $OFFICE = new Office();
        $list_office = $OFFICE->getAll();
        foreach ($list_office as $row) {
            $xtpl->assign('office_id', $row['id']);
            $xtpl->assign('office_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['office_id'] ? 'selected' : '');
            $xtpl->parse('statistics_day.search_office');
        }
        $xtpl->assign('BANG_CONG', $this->list_statistics_day($per_page, $page));
        $xtpl->parse('statistics_day');
        return $xtpl->text('statistics_day');
    }

    public function html_statistics_month($per_page, $page) {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op  . '&amp;statistics_type=statistics_month');
        $xtpl->assign('search_of_per_page', $per_page);
        if ($this->search_array['name']) {
            $xtpl->assign('search_name', $this->search_array['name']);
        }
        if ($this->search_array['email']) {
            $xtpl->assign('search_email', $this->search_array['email']);
        }
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', [
                'value' => $k,
                'title' => $title
            ]);
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('statistics_month.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', [
                'value' => $value,
                'title' => $value
            ]);
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('statistics_month.year');
        }
        $DEPARTMENT = new Department();
        $list_department = $DEPARTMENT->getAll();
        foreach ($list_department as $row) {
            $xtpl->assign('department_id', $row['id']);
            $xtpl->assign('department_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['department_id'] ? 'selected' : '');
            $xtpl->parse('statistics_month.search_department');
        }
        $OFFICE = new Office();
        $list_office = $OFFICE->getAll();
        foreach ($list_office as $row) {
            $xtpl->assign('office_id', $row['id']);
            $xtpl->assign('office_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['office_id'] ? 'selected' : '');
            $xtpl->parse('statistics_month.search_office');
        }

        $xtpl->assign('BANG_CONG', $this->list_statistics_month($per_page, $page, $this->month, $this->year));
        $xtpl->parse('statistics_month');
        return $xtpl->text('statistics_month');

    }

    public function html($statistics_type, $per_page = 30, $page = 1)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('statistics_type', $statistics_type);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('link_statistics_month', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;statistics_type=statistics_month');
        $xtpl->assign('link_statistics_day', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;statistics_type=statistics_day');
        $xtpl->assign('search_of_per_page', $per_page);
        $xtpl->assign('month', $this->month);
        $xtpl->assign('year', $this->year);
        $xtpl->assign('search_day', $this->search_array['search_day']);

        if ($statistics_type == 'statistics_month') {
            $xtpl->assign('statistics_month_active', 'active');
            $xtpl->assign('statistics_day_active', '');
            $xtpl->assign('STATISTICS_TYPE', $this->html_statistics_month($per_page, $page,));
        } else if ($statistics_type == 'statistics_day') {
            $xtpl->assign('statistics_month_active', '');
            $xtpl->assign('statistics_day_active', 'active');
            $xtpl->assign('STATISTICS_TYPE', $this->html_statistics_day($per_page, $page,));
        }
        foreach ([10, 30, 50] as $per_page_number) {
            $xtpl->assign('per_page_number', $per_page_number);
            $xtpl->assign('selected', $per_page_number == $per_page ? 'selected' : '' );
            $xtpl->parse('main.per_page_loop');
        }
        $xtpl->parse('main');
        return $xtpl->text('main');
    }

    /*
     * Export chấm công theo tháng
     * Nguyễn Văn Lâm
     */
    public function export_ChamCong($data, $spreadsheet, $writer, $username, $position)
    {
        $comment_field = [
            'email',
            'leave',
            'workout_worktime',
            'worktime',
            'workout',
            'late',
            'checkout_real'
        ];
        $comment = [];
        $comment_bg = [];
        $day_off = [];
        // Đặt thuộc tính cho file
        $spreadsheet->getProperties()
            ->setCreator($username)
            ->setLastModifiedBy($position)
            ->setTitle($this->lang_module['name_title_export'])
            ->setSubject($this->lang_module['name_title_export'])
            ->setDescription($this->lang_module['name_title_export']);
        // Đưa dữ liệu vào file
        $column_info_timekeeping = [
            'Làm hành chính',
            'Đi công tác+Làm ngoài giờ',
            'Tổng',
            'Tổng tính lương',
            'Đi muộn',
            'Trợ cấp ăn ca ở VP',
            'Ký tên'
        ];
        $array_content = [
            'A1' => 'Công ty cổ phần phát triển nguồn mở Việt Nam',
            'A2' => 'Số nhà A8, tập thể Dệt, Ao Sen, Mộ Lao, Hà Đông, Hà Nội',
            'A3' => 'BẢNG CHẤM CÔNG',
            'A4' => 'Tháng ' . $data['month'] . ' năm ' . $data['year']
        ];

        $HR = new HumanResources();
        $dataMember = $HR->getData_Member($data);
        $sheet = $spreadsheet->getActiveSheet()->setTitle($this->lang_module['name_title_export']);
        $all_members = $HR->getAllMembers();

        $list_day = $this->get_list_day($data);
        $array_content['A5'] = 'STT';
        $array_content['B5'] = 'Họ và tên';

        $cl_name_1 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 1);
        $cl_name_2 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 2);
        $cl_name_3 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 3);
        $cl_name_4 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 4);
        $cl_name_5 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 5);
        $cl_name_6 = Coordinate::stringFromColumnIndex(2 + count($list_day) + 6);

        $cl = 3;
        foreach ($list_day as $d => $wday) {
            $cl_name = Coordinate::stringFromColumnIndex($cl++);
            $array_content[$cl_name . '5'] = $d;
            $array_content[$cl_name . '6'] = $wday['name'];
        }
        foreach ($column_info_timekeeping as $value) {
            $cl_name = Coordinate::stringFromColumnIndex($cl++);
            $array_content[$cl_name . '5'] = $value;
        }
        // +2 là thêm 2 cột STT và Họ và tên
        $column_after = Coordinate::stringFromColumnIndex(2 + count($list_day) + count($column_info_timekeeping));
        $tt = 0;
        $index_row = 6;
        $CONFIG_MEMBER = new ConfigMember(0);
        $CONFIG_MEMBER->setMonth($data['month']);
        $CONFIG_MEMBER->setYear($data['year']);

        $leave_absence_list = $this->getLeaveAbsenceList($data['month'], $data['year']);

        foreach ($all_members as $userid => $value) {
            $CONFIG_MEMBER->setUserid($userid);
            $CONFIG_MEMBER->resetConfig();
            $CONFIG_MEMBER->setConfigData();
            $dayAllowOfWeek = $CONFIG_MEMBER->dayAllowOfWeek;
            $work_week = $CONFIG_MEMBER->work_week;
            $tt++;
            $index_row++;
            $array_content['A' . $index_row] = $tt;
            $array_content['B' . $index_row] = $value['full_name'];
            $comment['B' . $index_row] = [
                'email' => $value['email']
            ];
            $cl = 3;
            $order_saturday = 0;
            $sum_workday = array(
                'worktime' => 0,
                'workout' => 0
            );
            $sum_late = 0;
            foreach ($list_day as $d => $wday) {
                $w = $wday['w'];
                $order_saturday += ($w == 6 ? 1 : 0);
                $cl_name = Coordinate::stringFromColumnIndex($cl);
                if (!(in_array($w, $dayAllowOfWeek) || (($w == 6) && in_array($order_saturday, $work_week)))) {
                    $day_off[] = $cl_name . $index_row;
                }

                // Thêm ghi chú nghỉ phép
                if (!empty($leave_absence_list[$userid][$d])) {
                    $comment[$cl_name . $index_row]['leave'] = $this->lang_module['take_leave'];

                    if ($leave_absence_list[$userid][$d]['type'] == 1) {
                        $comment[$cl_name . $index_row]['leave'] .= $this->lang_module['all_day'];
                    } elseif ($leave_absence_list[$userid][$d]['type'] == 0.5) {
                        $comment[$cl_name . $index_row]['leave'] .= $this->lang_module['half_day'];
                    }

                    $comment[$cl_name . $index_row]['leave'] .= ' : ' . str_replace("<br />", "\r\n", html_entity_decode($leave_absence_list[$userid][$d]['reason']));
                    $comment_bg[$cl_name . $index_row] = empty($leave_absence_list[$userid][$d]['useyear']) ? 'FFF5F242' : 'FFF5B342';
                }

                if (!empty($dataMember[$userid][$d])) {
                    $info_work_day_member = $dataMember[$userid][$d];
                    $sum_workday['worktime'] += $info_work_day_member['worktime'];
                    $sum_workday['workout'] += $info_work_day_member['workout'];
                    $sum_late += $info_work_day_member['late'];
                    $workday = round($info_work_day_member['workday'] / 100, 2);
                    $v = ($workday > 1) ? '+' : ($workday == 1 ? 'c' : ( $workday > 0 ? '-' : ''));
                    $array_content[$cl_name . $index_row] = $v;

                    // comment thời gian trễ
                    if (!empty($info_work_day_member['late'])) {
                        $comment[$cl_name . $index_row]['late'] = sprintf($this->lang_module['checkin_late'], $info_work_day_member['late'], date('H:i', $info_work_day_member['checkin_real']));
                    }
                    // comment tổng công làm thêm và làm chính thức
                    if (($info_work_day_member['workout'] > 0) && ($info_work_day_member['worktime'] > 0)) {
                        $comment[$cl_name . $index_row]['workout_worktime'] =sprintf($this->lang_module['total_work_day'], round($info_work_day_member['workday'] / 100, 2));
                    }
                    // comment làm hành chính thiếu công
                    if ( (round($info_work_day_member['worktime'] / 100, 2) < 1) && (round($info_work_day_member['worktime'] / 100, 2) > 0) ) {
                        $comment[$cl_name . $index_row]['worktime'] = $this->lang_module['total_worktime'] . ':' . round($info_work_day_member['worktime'] / 100, 2) . "\r\n" . '- CheckIn: ' . date('H:i', $info_work_day_member['checkin_real']) . '  CheckOut: ' . date('H:i', $info_work_day_member['checkout_real']);
                    }
                    // comment làm thêm
                    if ($info_work_day_member['workout'] > 0) {
                        $comment[$cl_name . $index_row]['workout'] = $this->lang_module['total_workout'] . ':' . round($info_work_day_member['workout'] / 100, 2);
                    }
                    // comment không checkout
                    if (empty($info_work_day_member['checkout_real'])) {
                        $comment[$cl_name . $index_row]['not_checkout'] = $this->lang_module['not_checkout'];
                    }
                }
                $cl++;
            }
            // vị trí tổng công làm hành chính
            $array_content[$cl_name_1 . $index_row] = round($sum_workday['worktime'] / 100, 1);
            $array_content[$cl_name_2 . $index_row] = round($sum_workday['workout'] / 100, 1);
            // vị trí tổng công
            $array_content[$cl_name_3 . $index_row] = '=' . $cl_name_1 . $index_row . '+' . $cl_name_2 . $index_row;
            // vị trí tổng tính lương
            $array_content[$cl_name_4 . $index_row] = '=' . $cl_name_3 . $index_row;
            // vị trí làm muộn
            $array_content[$cl_name_5 . $index_row] = round($sum_late * 1000, 1);
            // Trợ cấp ăn ca ở VP
            $array_content[$cl_name_6 . $index_row] = '=' . $cl_name_1 . $index_row . '*20000';
        }
        $index_row++;
        $cell_tong = 'B' . ($index_row);
        $array_content[$cell_tong] = 'Tổng';

        // Tính tổng các cột thông tin
        for ($i1 = 1; $i1 < count($column_info_timekeeping); $i1++) {
            $cl_name = Coordinate::stringFromColumnIndex(2 + count($list_day) + $i1);
            $array_content[$cl_name . $index_row] = '=SUM(' . $cl_name . '7:' . $cl_name . (6 + count($all_members)) . ')';
        }

        $array_content['B' . ($index_row + 1)] = 'M30: Muộn 30 phút';
        $array_content['K' . ($index_row + 1)] = 'Không checkOut';
        $array_content['K' . ($index_row + 2)] = 'Ngày nghỉ';
        $array_content['B' . ($index_row + 2)] = 'S30: Sớm 30 phút';
        $array_content['E' . ($index_row + 1)] = 'R: Nghỉ có phép';
        $array_content['E' . ($index_row + 2)] = 'Ro: Nghỉ không phép';
        $array_content['P' . ($index_row + 1)] = 'Nghỉ phép tính số ngày nghỉ';
        $array_content['P' . ($index_row + 2)] = 'Nghỉ phép không tính số ngày nghỉ';
        $array_content['W' . ($index_row + 1)] = 'c*: Nghỉ hưởng nguyên lương';
        $array_content['W' . ($index_row + 2)] = '-: Làm nửa ngày';
        $array_content['AC' . ($index_row + 1)] = 'R*: Làm online';
        $array_content['G' . ($index_row + 4)] = 'Người chấm công';
        $array_content['AA' . ($index_row + 4)] = 'Tổng giám đốc';

        $array_content['A' . ($index_row + 9)] = 'Màu vàng: Không login CRM ngay khi đến VP';
        $array_content['A' . ($index_row + 10)] = 'Màu đỏ: Không đăng nhập CRM';
        $array_content['A' . ($index_row + 11)] = 'Nhân viên được nghỉ do có việc riêng mà vẫn hưởng nguyên lương trong những trường hợp sau:';
        $array_content['B' . ($index_row + 12)] = 'Nghỉ đẻ: 06 tháng';
        $array_content['B' . ($index_row + 13)] = 'Kết hôn: nghỉ 03 ngày';
        $array_content['B' . ($index_row + 14)] = 'Con kết hôn: nghỉ 01 ngày';
        $array_content['B' . ($index_row + 15)] = 'Bố mẹ (bên chồng/vợ); vợ/chồng chết, con chết: nghỉ 03 ngày';
        $array_content['B' . ($index_row + 18)] = 'Link đến file thống kê nghỉ phép:';
        $array_content['G' . ($index_row + 18)] = 'https://docs.google.com/spreadsheets/d/1fNCRBO65Yx2r1BSO_scPoTVMA85JG7tXSjevW-sF2z4/edit#gid=970733645';

        // Viết value cho các ô
        foreach ($array_content as $key => $value) {
            $sheet->setCellValue($key, $value);
        }

        // Lặp và điền comment cho các ô
        foreach ($comment as $key => $value) {
            $have_comment = false;
            $comment_length = 0;
            foreach ($comment_field as $field) {
                if (empty($value[$field])) {
                    continue;
                }

                $have_comment = true;
                $sheet->getComment($key)
                    ->getText()
                    ->createTextRun('- ' . $value[$field] . "\r\n");
                $comment_length += nv_strlen($value[$field]);
            }

            if ($have_comment) {
                $sheet->getComment($key)->setHeight('350px');
                $sheet->getComment($key)->setWidth('350px');
            }
        }

        // Định dạng
        // Set page orientation and size
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
        // Kẻ bảng
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => [
                        'rgb' => '000000'
                    ]
                ]
            ]
        ];
        $sheet->getStyle('A5:' . $column_after . $index_row)->applyFromArray($styleArray);
        // Định dạng chung
        $index_row += 20;
        $styleArray = [
            'font' => [
                'size' => 11,
                'name' => 'Times new Roman'
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            ]
        ];
        $sheet->getStyle('A1:' . $column_after . ($index_row))->applyFromArray($styleArray);
        unset($styleArray);
        // Chỉnh cỡ cột
        $array_column = [
            'A' => 5,
            'B' => 25
        ];
        $cl = 3;
        foreach ($list_day as $value) {
            $cl_name = Coordinate::stringFromColumnIndex($cl++);
            $array_column[$cl_name] = 5;
        }
        foreach ($column_info_timekeeping as $value) {
            $cl_name = Coordinate::stringFromColumnIndex($cl++);
            $array_column[$cl_name] = 7;
        }
        $array_column[Coordinate::stringFromColumnIndex(2 + count($list_day) + 5)] = 9;
        $array_column[Coordinate::stringFromColumnIndex(2 + count($list_day) + 6)] = 9;
        foreach ($array_column as $key => $value) {
            $sheet->getColumnDimension($key)->setAutoSize(false);
            $sheet->getColumnDimension($key)->setWidth($value);
        }
        unset($array_column);
        // Chỉnh chiều cao hàng
        $array_row = [
            '3' => 30,
            '4' => 30,
            '5' => 55
        ];
        foreach ($array_row as $key => $value) {
            $sheet->getRowDimension($key)->setRowHeight($value);
        }
        unset($array_row);
        // Merge cell
        $array_merge = [
            'A1:' . $column_after . '1',
            'A2:' . $column_after . '2',
            'A3:' . $column_after . '3',
            'A4:' . $column_after . '4',
            'A5:A6',
            'B5:B6'
        ];
        // 2 la cột STT và Họ và tên
        $cl = 2 + count($list_day);
        foreach ($column_info_timekeeping as $value) {
            $cl_name = Coordinate::stringFromColumnIndex(++$cl);
            $array_merge[] = $cl_name . '5:' . $cl_name . '6';
        }
        foreach ($array_merge as $cell) {
            $sheet->mergeCells($cell);
        }
        // setWrapText
        $sheet->getStyle('A1:' . $column_after . (6 + count($all_members)))
            ->getAlignment()
            ->setWrapText(true);
        // Căn lề
        // Căn giữa ngang, dọc
        $canngang = [
            'A3:A4',
            'A5:B6',
            'C5:' . $column_after . (6 + count($all_members))
        ];
        foreach ($canngang as $cell) {
            $sheet->getStyle($cell)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }
        $sheet->getStyle('A4')
            ->getAlignment()
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        unset($canngang);
        // căn phải cho cột STT
        $sheet->getStyle('A7:A' . (6 + count($all_members)))
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        // căn phải cho các cột bổ sung thông tin phía sau
        ;
        $sheet->getStyle(Coordinate::stringFromColumnIndex(2 + count($list_day) + 1) . '7:' . Coordinate::stringFromColumnIndex(2 + count($list_day) + 6) . (7 + count($all_members)))
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // In đậm
        $array_bold = [
            'A3',
            'A4',
            'A5:' . $column_after . '6',
            $cell_tong,
            Coordinate::stringFromColumnIndex(count($list_day) + 5) . '7:' . Coordinate::stringFromColumnIndex(count($list_day) + 5) . (count($all_members) + 7), // cột Tổng công làm của nhân viên
            'A' . (count($all_members) + 11) . ':' . $column_after . (count($all_members) + 11) // chữ ký
        ];
        foreach ($array_bold as $cell) {
            $sheet->getStyle($cell)
                ->getFont()
                ->setBold(true);
        }
        unset($array_bold);

        // In nghiêng
        $array_italic = [
            'A1',
            'A2',
            'B' . (count($all_members) + 18) . ':B' . (count($all_members) + 22)
        ];
        foreach ($array_italic as $cell) {
            $sheet->getStyle($cell)
                ->getFont()
                ->setItalic(true);
        }
        unset($array_italic);
        // Chỉnh cỡ font
        $array_font_size = array(
            'A3' => 16
        );
        foreach ($array_font_size as $key => $value) {
            $sheet->getStyle($key)
                ->getFont()
                ->setSize($value);
        }
        unset($array_font_size);
        // Tô màu ngày nghỉ cuối tuần
        foreach ($day_off as $cell) {
            $sheet->getStyle($cell)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('99cc00');
        }

        // Tô màu quên checkout và nghỉ phép
        foreach ($comment as $key => $value) {
            if (empty($value)) {
                continue;
            }

            // Nghỉ phép
            if (!empty($value['leave'])) {
                $sheet->getStyle($key)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB(isset($comment_bg[$key]) ? $comment_bg[$key] : \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_YELLOW);
            }

            if (empty($value['not_checkout'])) {
                continue;
            }

            // Không checkout
            $sheet->getStyle($key)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FD0000');
        }

        // Màu chữ cột "Tổng tính cong
        $sheet->getStyle(Coordinate::stringFromColumnIndex(2 + count($list_day) + 4) . '7:' . Coordinate::stringFromColumnIndex(2 + count($list_day) + 4) . (7 + count($all_members)))
            ->getFont()
            ->getColor()
            ->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        // Tô màu giải thích "Không checkout"
        $sheet->getStyle('J' . (8 + count($all_members)))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->
        // ->setARGB('99cc00');
        setARGB('FD0000');
        // Tô màu giải thích "Ngày nghỉ"
        $sheet->getStyle('J' . (9 + count($all_members)))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('99cc00');
        // Tô màu tính số ngày nghỉ
        $sheet->getStyle('O' . (8 + count($all_members)))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFF5B342');
        // Tô màu không tính số ngày nghỉ
        $sheet->getStyle('O' . (9 + count($all_members)))
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFF5F242');
        // Fomat money
        $sheet->getStyle(Coordinate::stringFromColumnIndex(2 + count($list_day) + 5) . '7:' . Coordinate::stringFromColumnIndex(2 + count($list_day) + 6) . (7 + count($all_members)))
            ->getNumberFormat()
            ->setFormatCode('#,##');
        // Tên file

        $filename = "Bang_Cham_Cong_Thang_" . $data['month'] . '_' . $data['year'] . ".xlsx";
        try {
            $writer->save($filename);
            $content = file_get_contents($filename);
        } catch (\Exception $e) {
            exit($e->getMessage());
        }

        header("Content-Disposition: attachment; filename=" . $filename);
        unlink($filename);
        exit($content);
    }

    public function get_list_day($dulieu)
    {
        $dulieu['month'] = intval($dulieu['month']);
        $dulieu['year'] = intval($dulieu['year']);
        $data = [];
        $time = mktime(0, 0, 0, $dulieu['month'], 1, $dulieu['year']);
        $tongso_ngay = date('t', $time);
        $first_wday = date('w', $time);
        $wday_string = array(
            $lang_global['sun'] = 'CN',
            $lang_global['mon'] = 'T2',
            $lang_global['tue'] = 'T3',
            $lang_global['wed'] = 'T4',
            $lang_global['thu'] = 'T5',
            $lang_global['fri'] = 'T6',
            $lang_global['sat'] = 'T7'
        );
        for ($d = 1; $d <= $tongso_ngay; $d++) {
            $w = $first_wday % count($wday_string);
            $data[$d] = array(
                'w' => $w,
                'name' => $wday_string[$first_wday % count($wday_string)]
            );
            $first_wday++;
        }
        return $data;
    }
}
