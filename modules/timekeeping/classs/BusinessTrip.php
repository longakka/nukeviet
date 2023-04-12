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

class BusinessTrip extends Main
{

    private $tpl = 'business_trip';

    protected $title = 'business_trip_vacation';

    protected $table_data = 'business_trip';

    public function dataPost()
    {
        $post = array();
        $post['userid_leader'] = $this->HR->user_info['userid'];
        $post['userid'] = $this->nv_Request->get_int('userid', 'post', 0);
        $post['salary'] = $this->nv_Request->get_int('salary', 'post', 0);
        $post['status'] = $this->nv_Request->get_title('status', 'post', '');
        $post['start_time'] = strtotime(date_format(date_create_from_format("d/m/Y", $this->nv_Request->get_title('start_time', 'post', '')), "Y-m-d"));
        $end_time = strtotime(date_format(date_create_from_format("d/m/Y", $this->nv_Request->get_title('end_time', 'post', '')), "Y-m-d"));
        // cộng thêm gần 24 giờ nữa để phần end_time tính vào cuối ngày
        $post['end_time'] = $end_time + 24 * 60 * 60 - 60;

        $post['reason'] = $this->nv_Request->get_title('reason', 'post', '', 1);
        $post['reason'] = nv_nl2br($post['reason'], "<br />");

        return $post;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getData($month = 0, $year = 0)
    {
        $first_timestamp = strtotime($year . "-" . $month . "-1");
        $last_timestamp = $month < 12 ? strtotime($year . "-" . ($month + 1) . "-1") : strtotime(($year + 1) . "-1-1");
        $this->db->sqlreset()
            ->select('t1.*, t2.username, t2.first_name, t2.last_name')
            ->from($this->table_data . ' AS t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid')
            ->where("start_time>= $first_timestamp AND start_time<$last_timestamp AND userid_leader=" . $this->HR->user_info['userid']);
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']) . ' (#' . $row['userid'] . ')';
            $data[] = $row;
        }
        return $data;
    }

    public function getById($id, $select = '*')
    {
        $this->db->sqlreset("t1.*, t3.username, t3.first_name, t2.last_name")
            ->select($select)
            ->from($this->table_data . ' AS t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid = t2.userid')
            ->where('t1.id=' . $id);
        $_sql = $this->db->sql();
        $row = $this->db->query($_sql)->fetch(2);
        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']) . ' (#' . $row['userid'] . ')';
        return $row;
    }

    public function getStaff($term)
    {
        $this->db->sqlreset()
            ->select('t2.userid AS id, t2.first_name, t2.last_name')
            ->from($this->table_module . '_human_resources AS t1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS t2 ON t1.userid=t2.userid')
            ->where('(t1.department_id = ' . $this->HR->user_info['department_id'] . ' OR
            t1.department_id IN (
                SELECT id FROM ' . $this->table_module . '_department
                WHERE parentid=' . $this->HR->user_info['department_id'] . '
            )) AND (
                t2.first_name LIKE "%' . $term . '%" OR t2.last_name LIKE "%' . $term . '%"
            ) AND t2.userid !=' . $this->HR->user_info['userid'] . ' AND t1.still_working=1')
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

    public function htmlAdd($id = 0)
    {
        global $global_config;
        $business_trip = array(
            'id' => 0,
            'userid' => 0,
            'reason' => '',
            'start_time' => '',
            'end_time' => '',
            'status' => 'business_trip'
        );
        $business_trip = !empty($id) ? $this->getById($id) : $business_trip;
        $business_trip['start_time'] = date("d/m/Y", $business_trip['start_time']);
        $business_trip['end_time'] = date("d/m/Y", $business_trip['end_time']);

        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
        $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        $xtpl->assign('LIST_BUSINESS_TRIP_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=business-trip', true));
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('BUSINESS_TRIP', $business_trip);
        $xtpl->assign('salary_checked', !empty($business_trip['salary']) ? 'checked' : '');
        $xtpl->assign('selected_business_trip', ($business_trip['status'] == 'business_trip') ? 'selected' : '');
        $xtpl->assign('selected_vacation', ($business_trip['status'] == 'vacation') ? 'selected' : '');
        if (!empty($id)) {
            $xtpl->parse('business_trip_add.has_staff');
        }
        $xtpl->parse('business_trip_add');
        return $xtpl->text('business_trip_add');
    }

    public function htmlList($month = 0, $year = 0)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('MODULE_URL', nv_url_rewrite($this->base_url_user, true));
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('list_business_trip_vacation', sprintf($this->lang_module['list_business_trip_vacation'], $month, $year));
        $data = $this->getData($month, $year);
        $tt = 0;
        foreach ($data as $business_trip) {
            $tt++;
            $business_trip['tt'] = $tt;
            $business_trip['start_day'] = date('j', $business_trip['start_time']);
            $business_trip['start_month'] = date('n', $business_trip['start_time']);
            $business_trip['start_year'] = date('Y', $business_trip['start_time']);
            $business_trip['end_day'] = date('j', $business_trip['end_time']);
            $business_trip['end_month'] = date('n', $business_trip['end_time']);
            $business_trip['end_year'] = date('Y', $business_trip['end_time']);
            $business_trip['salary_checked'] = !empty($business_trip['salary']) ? 'checked' : '';
            $business_trip['type_off'] = $this->lang_module[$business_trip['status']];
            $xtpl->assign('LOOP', $business_trip);
            $xtpl->parse('list.loop');
        }
        $xtpl->parse('list');
        return $xtpl->text('list');
    }

    /*
     * $confirm = false lay theme cua danh sach dang ky lam them
     * $confirm = true lay theme cua danh sach duyet dang ky lam them
     */
    public function html($confirm = false)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));

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
        $BUSINESS_TRIP = $this->htmlList($this->month, $this->year);
        $xtpl->assign('BUSINESS_TRIP', $BUSINESS_TRIP);
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
