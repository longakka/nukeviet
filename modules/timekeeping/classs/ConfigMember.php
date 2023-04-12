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

class ConfigMember extends Config
{

    private $tpl = 'config_member';

    protected $title = 'config_member';

    protected $table_data = 'config';
    public $search_array = array(
        'name' => '',
        'email' => '',
        'department_id' => '',
        'office_id' => '',
    );
    public $num_items = 0;

    public function __construct($userid, $month = 0, $year = 0)
    {
        parent::__construct($userid, $month, $year);
        $this->HR = new HumanResources();
        $this->HR->setMemberInfor($userid);
        $this->search_array['name'] = $this->nv_Request->get_title('name', 'get, post', '');
        $this->search_array['email'] = $this->nv_Request->get_title('email', 'get, post', '');
        $this->search_array['department_id'] = $this->nv_Request->get_int('department_id', 'get, post', 0);
        $this->search_array['office_id'] = $this->nv_Request->get_int('office_id', 'get, post', '');
    }
    public function getData($per_page = 30, $page = 1)
    {
        $where = array();
        $where[] = '(year*100 + month) <= ' . ($this->year*100 + $this->month);
        $this->search_array['name'] = trim($this->search_array['name']);
        if (!empty($this->search_array['name'])) {
            $sql = $this->global_config['name_show'] == 0 ? "concat(last_name,' ',first_name)" : "concat(first_name,' ',last_name)";
            $where[] = $sql . 'LIKE "%' . $this->search_array['name'] . '%"';
        }
        if (!empty($this->search_array['email'])) {
            $where[] = 'user.email LIKE "%' . $this->db->dblikeescape($this->search_array['email'])  . '%"';
        }
        if (!empty($this->search_array['department_id'])) {
            $where[] = 'human_resources.department_id = ' . $this->search_array['department_id'];
        }
        if (!empty($this->search_array['office_id'])) {
            $where[] = 'human_resources.office_id = ' . $this->search_array['office_id'];
        }

        $this->db->sqlreset()
        ->select('COUNT(DISTINCT t1.userid)')
        ->from($this->table_data . ' AS t1')
        ->join(
            'INNER JOIN ' . $this->table_module . '_human_resources AS human_resources ON t1.userid = human_resources.userid ' .
            'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS user ON t1.userid = user.userid '
        )
        
        ->order('human_resources.weight ASC');
        if (!empty($where)) {
            $this->db->where(implode(' AND ', $where));
        }

        $this->num_items = $this->db->query($this->db->sql())->fetchColumn();
        $this->db->select('t1.userid, user.username, user.last_name, user.first_name')->group('t1.userid')->limit($per_page)
        ->offset(($page - 1) * $per_page);
        $result = $this->db->query($this->db->sql());
        $userids = array();
        $data_return = array();
        while ($row = $result->fetch(2)) {
            $userids[] = $row['userid'];
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            if (empty($row['photo'])) {
                $row['photo'] = NV_BASE_SITEURL . "themes/default/images/users/no_avatar.png";
            } else {
                $row['photo'] = NV_BASE_SITEURL . $row['photo'];
                if (defined('SSO_REGISTER_DOMAIN')) {
                    $row['photo'] = SSO_REGISTER_DOMAIN . $row['photo'];
                }
            }
            $data_return[$row['userid']] = $row;
        }
        if (empty($userids)) {
            return array();
        }
        foreach ($userids as $userid) {
            $configMember = new Config($userid, $this->month, $this->year);
            $configMember->setUserid($userid);
            $configMember->setConfigData();
            $configData = $configMember->getConfigData();
            if (!empty($configData['dayAllowOfWeek'])) {
                $dayAllowOfWeek = array_map(function ($item) {
                    return $this->weekday[$item];
                }, $configData['dayAllowOfWeek']);
                $configData['work_day'] = implode(", ", $dayAllowOfWeek);
                // kiểm tra xem có làm việc ngày thứ 7 không
                if (!in_array(6, array_values($configData['dayAllowOfWeek']))) {
                    unset($configData['work_week']);
                }
            }
            $data_return[$userid] = array_merge($data_return[$userid], $configData);
        }
        return $data_return;
    }

    public function getStaff($term)
    {
        $this->db->sqlreset()
            ->select('t2.userid AS id, t2.first_name, t2.last_name')
            ->from($this->table_module . '_human_resources AS t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid')
            ->where('t2.first_name LIKE "%' . $term . '%" OR t2.last_name LIKE "%' . $term . '%"')
            ->limit(10)
            ->order('t1.weight ASC');
        ;
        $data = array();
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $row['text'] = $row['first_name'] . " " . $row['last_name'] . " (#" . $row['id'] . ")";
            $data[] = $row;
        }
        return $data;
    }

    public function htmlAdd()
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
        $xtpl->assign('userid', 0);
        if (!empty($this->HR) && !empty($this->HR->user_info['userid'])) {
            $xtpl->assign('userid', $this->HR->user_info['userid']);
            $xtpl->assign('full_name', $this->HR->user_info['full_name'] . " (#" . $this->userid . ")");
            $xtpl->parse('config_member_add.has_staff');
        }
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', array(
                'value' => $k,
                'title' => $title
            ));
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('config_member_add.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', array(
                'value' => $value,
                'title' => $value
            ));
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('config_member_add.year');
        }
        $xtpl->assign('checked_work_week_1', in_array(1, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_2', in_array(2, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_3', in_array(3, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_4', in_array(4, $this->work_week) ? 'checked' : '');
        $xtpl->assign('checked_work_week_5', in_array(5, $this->work_week) ? 'checked' : '');
        for ($i = 0; $i < count($this->weekday); $i++) {
            $xtpl->assign('WEEKDAY', array(
                'value' => $i,
                'title' => $this->weekday[$i],
                'checked' => in_array($i, $this->dayAllowOfWeek) ? 'checked' : ''
            ));
            $xtpl->parse('config_member_add.dayAllowOfWeek');
        }
        foreach ($this->timeCheckio as $k => $session) {
            $ss = ($k == 0) ? 'morning' : 'afternoon';
            $xtpl->assign('session', $ss);
            $xtpl->assign('session_title', $this->lang_module[$ss]);
            foreach ($session as $ck => $value) {
                $xtpl->assign('time', $ck);
                $xtpl->assign('time_title', $this->lang_module[$ck == 'checkin' ? 'start' : 'end']);
                $hour = floor($value / 60);
                $minute = $value % 60;
                for ($h = 0; $h <= 23; $h++) {
                    $xtpl->assign('hour', $h);
                    $xtpl->assign('hour_title', $h . " " . $this->lang_global['hour']);
                    $xtpl->assign('selected', $hour == $h ? 'selected' : '');
                    $xtpl->parse('config_member_add.session.time.hour');
                }
                for ($m = 0; $m <= 59; $m++) {
                    $xtpl->assign('minute', $m);
                    $xtpl->assign('minute_title', $m . " " . $this->lang_global['min']);
                    $xtpl->assign('selected', $minute == $m ? 'selected' : '');
                    $xtpl->parse('config_member_add.session.time.minute');
                }
                $xtpl->parse('config_member_add.session.time');
            }
            $xtpl->parse('config_member_add.session');
        }
        $monthcr = date('n', $this->cr_time);
        $yearcr = date('Y', $this->cr_time);
        if ($yearcr * 100 + $monthcr <= $this->year * 100 + $this->month) {
            $xtpl->parse('config_member_add.submit');
        }
        $xtpl->parse('config_member_add');
        return $xtpl->text('config_member_add');
    }

    public function html($per_page = 30, $page = 1)
    {
        $base_url = $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;per_page=' . $per_page;
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
        $listPagination = $this->getData($per_page, $page);
                
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
        $xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
        $xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
        $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
        $xtpl->assign('MODULE_NAME', $this->module_name);
        $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('config_member_time', sprintf($this->lang_module['config_member_time'], $this->month, $this->year));
        $xtpl->assign('search_of_per_page', $per_page);
        
        $tt = ($page - 1) * $per_page + 1;
        foreach ($listPagination as $row) {
            $row['tt'] = $tt++;
            $xtpl->assign('LOOP', $row);

            foreach ($row['timeCheckio'] as $k => $session) {
                $ss = ($k == 0) ? 'morning' : 'afternoon';
                $xtpl->assign('session_title', $this->lang_module[$ss]);
                foreach ($session as $ck => $value) {
                    $xtpl->assign('time_title', $this->lang_module[$ck == 'checkin' ? 'start' : 'end']);
                    $hour = floor($value / 60);
                    $minute = $value % 60;
                    $xtpl->assign('hour', $hour);
                    $xtpl->assign('minute', $minute);
                    $xtpl->parse('main.loop.session.time');
                }
                $xtpl->parse('main.loop.session');
            }
            if ($row['work_week']) {
                foreach ($row['work_week'] as $val) {
                    $xtpl->assign('work_week', $this->lang_module['work_week_' . $val]);
                    $xtpl->parse('main.loop.saturday_work_week.work_week');
                }
                $xtpl->parse('main.loop.saturday_work_week');
            }
            $xtpl->parse('main.loop');
        }
        
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', [
                'value' => $k,
                'title' => $title
            ]);
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('main.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', [
                'value' => $value,
                'title' => $value
            ]);
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('main.year');
        }
        $DEPARTMENT = new Department();
        $list_department = $DEPARTMENT->getAll();
        foreach ($list_department as $row) {
            $xtpl->assign('department_id', $row['id']);
            $xtpl->assign('department_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['department_id'] ? 'selected' : '');
            $xtpl->parse('main.search_department'); 
        }

        $OFFICE = new Office();
        $list_office = $OFFICE->getAll();
        foreach ($list_office as $row) {
            $xtpl->assign('office_id', $row['id']);
            $xtpl->assign('office_title', $row['title']);
            $xtpl->assign('selected', $row['id'] == $this->search_array['office_id'] ? 'selected' : '');
            $xtpl->parse('main.search_office'); 
        }
        foreach ([10, 30, 50] as $per_page_number) {
            $xtpl->assign('per_page_number', $per_page_number);
            $xtpl->assign('selected', $per_page_number == $per_page ? 'selected' : '' );
            $xtpl->parse('main.per_page_loop');
        }
        $generate_page = nv_generate_page($base_url, $this->num_items, $per_page, $page);
        if (!empty($generate_page)) {
            $xtpl->assign('GENERATE_PAGE', $generate_page);
            $xtpl->parse('main.generate_page');
        }
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
