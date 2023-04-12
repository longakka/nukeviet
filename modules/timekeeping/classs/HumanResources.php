<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace Modules\timekeeping\classs;

use NukeViet\Module\timekeeping\Shared\Money;
use XTemplate;
use NukeViet\Module\timekeeping\Role;

/*
 * Chu y ve du lieu
 * id la userid cua table
 */
class HumanResources extends Main
{

    private $tpl = 'human_resources';

    protected $title = 'human_resources';
    public $num_items = 0;
    protected $table_data = 'human_resources';
    public $search_array = [
        'name' => '',
        'email' => '',
        'department_id' => '',
        'office_id' => '',
        'confirm_workout' => 0,
    ];
    public function __construct()
    {
        parent::__construct();
        $this->search_array['name'] = $this->nv_Request->get_title('name', 'get, post', '');
        $this->search_array['email'] = $this->nv_Request->get_title('email', 'get, post', '');
        $this->search_array['department_id'] = $this->nv_Request->get_int('department_id', 'get, post', 0);
        $this->search_array['office_id'] = $this->nv_Request->get_int('office_id', 'get, post', '');
        $this->search_array['confirm_workout'] = (int) $this->nv_Request->get_bool('cw', 'get, post', false);
    }
    public $user_info = [];

    private $array_roles = [
        Role::ROLE_ACCOUNTING_DEPARTMENT,
        Role::ROLE_HR_DEPARTMENT
    ];

    public function dataPost()
    {
        global $global_array_ranks;

        $post = [];
        $username = $this->nv_Request->get_title('username', 'post', 0);
        if (empty($username)) {
            nv_htmlOutput($this->lang_module['errorYouNeedSelectHumanResource']);
        }
        $md5username = nv_md5safe($username);
        if (preg_match('/^([0-9]+)$/', $username)) {
            $sql = 'SELECT userid FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . intval($username) . ' OR md5username=' . $this->db->quote($md5username);
        } else {
            $sql = 'SELECT userid FROM ' . NV_USERS_GLOBALTABLE . ' WHERE md5username=' . $this->db->quote($md5username);
        }
        list($post['userid']) = $this->db->query($sql)->fetch(3);
        if (empty($post['userid'])) {
            nv_htmlOutput($this->lang_module['errorHumanResourceNotExist']);
        }
        $post['office_id'] = $this->nv_Request->get_int('office_id', 'post', 0);
        $OFFICE = new Office();
        $office_item = $OFFICE->getById($post['office_id']);
        $post['confirm_workout'] = 0;
        $post['view_diary_penalize'] = 0;
        if (!empty($office_item['leader'])) {
            $confirm_workout = $this->nv_Request->get_array('confirm_workout', 'post', 0);
            $post['confirm_workout'] = implode(',', $confirm_workout);
        }
        $view_diary_penalize = $this->nv_Request->get_array('view_diary_penalize', 'post', 0);
        $post['view_diary_penalize'] = implode(',', $view_diary_penalize);
        $view_leave_absence = $this->nv_Request->get_array('view_leave_absence', 'post', 0);
        $post['view_leave_absence'] = implode(',', $view_leave_absence);
        $view_department_report = $this->nv_Request->get_array('view_department_report', 'post', 0);
        $post['view_department_report'] = implode(',', $view_department_report);

        $post['department_id'] = $this->nv_Request->get_int('department_id', 'post', 0);
        $post['phone'] = $this->nv_Request->get_title('phone', 'post', '');
        $post['cmnd'] = $this->nv_Request->get_title('cmnd', 'post', '');
        $post['address'] = $this->nv_Request->get_title('address', 'post', '');
        $weight = $this->nv_Request->get_int('weight', 'post', 0);
        $post['weight'] = $weight > 0 ? $weight : $this->count() + 1;
        $post['still_working'] = (int) $this->nv_Request->get_bool('still_working', 'post', false);
        $post['no_aswork'] = (int) $this->nv_Request->get_bool('no_aswork', 'post', false);

        if (defined('NV_IS_GODADMIN')) {
            // Lấy và xử lý quyền hạn
            $post['role_id'] = $this->nv_Request->get_typed_array('role_id', 'post', 'int', []);
            $post['role_id'] = array_intersect($post['role_id'], $this->array_roles);
            $post['role_id'] = empty($post['role_id']) ? '' : implode(',', $post['role_id']);
        }

        $post['rank_id'] = $this->nv_Request->get_absint('rank_id', 'post', 0);
        if (!isset($global_array_ranks[$post['rank_id']])) {
            $post['rank_id'] = 0;
        }

        return $post;
    }

    public function setMemberInfor($userid, $check_still_working = 0)
    {
        if (empty($userid))
            return array();
        $this->db->sqlreset()
            ->select("t1.*, t2.title, t3.title, t3.leader, t3.manager, t3.weight office_weight, t4.email, t4.username, t4.first_name, t4.last_name, t4.gender, t4.photo, t4.birthday")
            ->from($this->table_data . " AS t1")
            ->join(" INNER JOIN " . NV_USERS_GLOBALTABLE . " AS t4 ON t1.userid = t4.userid " . " LEFT JOIN " . $this->table_module . "_department AS t2 ON t1.department_id = t2.id LEFT JOIN " . $this->table_module . "_office AS t3 ON t1.office_id = t3.id")
            ->where("t1.userid = " . $userid . ($check_still_working ? ' AND t1.still_working=1' : ''));
        $row = $this->db->query($this->db->sql())
            ->fetch(2);
        if (empty($row)) {
            return [];
        }

        $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
        $row['confirm_workout'] = !empty($row['confirm_workout']) ? $row['confirm_workout'] : '0';
        $row['view_diary_penalize'] = !empty($row['view_diary_penalize']) ? $row['view_diary_penalize'] : '0';
        $row['view_leave_absence'] = !empty($row['view_leave_absence']) ? $row['view_leave_absence'] : '0';
        $this->user_info = $row;

        return $this->user_info;
    }

    public function editById($id, $data = array())
    {
        if (count($data) == 0 || ($id != $data['userid']))
            return false;
        return $this->updateCSLD($data, 'userid = ' . $id);
    }

    public function getById($id, $select = '*')
    {
        $this->db->sqlreset()
            ->select('t1.username, t1.userid, t1.email, t1.first_name, t1.last_name, t1.gender, t1.photo, t1.birthday,t2.*')
            ->from(NV_USERS_GLOBALTABLE . ' AS t1 ')
            ->join('INNER JOIN ' . $this->table_data . ' AS t2 ON t1.userid = t2.userid')
            ->where('t1.userid=' . $id);
        $row = $this->db->query($this->db->sql())
            ->fetch(2);
        if (empty($row)) {
            throw new \Exception('This staff ' . $id . ' not exists!!!');
        }

        $row['role_id'] = empty($row['role_id']) ? [] : explode(',', $row['role_id']);

        return $row;
    }

    public function htmlDepartments($department_id, $user_id)
    {
        $leader_department = array();
        $str_leader_department = $this->getAll('confirm_workout', 'userid = ' . $user_id);
        if (!empty($str_leader_department)) {
            $leader_department = explode(',', (string) $str_leader_department[0]['confirm_workout']);
        }
        $list_department = array();
        $DEPARTMENT = new Department();
        $catList = $DEPARTMENT->getCatList();

        foreach ($catList as $department) {
            $list_department[$department['id']] = $department;
            $list_department[$department['id']]['checked'] = in_array($department['id'], $leader_department) ? 'checked' : '';
        }
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        foreach ($list_department as $department) {
            $department['name'] = str_replace(">", "", $department['name']);
            $department['name'] = str_replace("|", "", $department['name']);
            $department['name'] = str_replace("-", "&nbsp;", $department['name']);
            $department['name'] = preg_replace('/\s{' . $catList[$department_id]['level'] * 3 . '}/', '', $department['name']);
            $xtpl->assign('DEPARTMENT', $department);
            $xtpl->parse('list_department.loop');
        }
        $xtpl->parse('list_department');
        return $xtpl->text('list_department');
    }

    public function htmlDepartmentsPenalize($department_id, $user_id)
    {
        $leader_department = array();
        $str_leader_department = $this->getAll('view_diary_penalize', 'userid = ' . $user_id);
        if (!empty($str_leader_department)) {
            $leader_department = explode(',', $str_leader_department[0]['view_diary_penalize']);
        }
        $list_department = array();
        $DEPARTMENT = new Department();
        $catList = $DEPARTMENT->getCatList();
        foreach ($catList as $department) {
            $list_department[$department['id']] = $department;
            $list_department[$department['id']]['checked'] = in_array($department['id'], $leader_department) ? 'checked' : '';
        }
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        foreach ($list_department as $department) {
            $department['name'] = str_replace(">", "", $department['name']);
            $department['name'] = str_replace("|", "", $department['name']);
            $department['name'] = str_replace("-", "&nbsp;", $department['name']);
            $xtpl->assign('DEPARTMENT', $department);
            $xtpl->parse('list_department_penalize.loop');
        }
        $xtpl->parse('list_department_penalize');
        return $xtpl->text('list_department_penalize');
    }

    public function htmlDepartmentsAbsence($department_id, $user_id)
    {
        $leader_department = array();
        $str_leader_department = $this->getAll('view_leave_absence', 'userid = ' . $user_id);
        if (!empty($str_leader_department)) {
            $leader_department = explode(',', $str_leader_department[0]['view_leave_absence']);
        }
        $list_department = array();
        $DEPARTMENT = new Department();
        $catList = $DEPARTMENT->getCatList();
        foreach ($catList as $department) {
            $list_department[$department['id']] = $department;
            $list_department[$department['id']]['checked'] = in_array($department['id'], $leader_department) ? 'checked' : '';
        }
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        foreach ($list_department as $department) {
            $department['name'] = str_replace(">", "", $department['name']);
            $department['name'] = str_replace("|", "", $department['name']);
            $department['name'] = str_replace("-", "&nbsp;", $department['name']);
            $xtpl->assign('DEPARTMENT', $department);
            $xtpl->parse('list_department_absence.loop');
        }
        $xtpl->parse('list_department_absence');
        return $xtpl->text('list_department_absence');
    }

    public function htmlDepartmentReport($department_id, $user_id)
    {
        $leader_department = array();
        $str_leader_department = $this->getAll('view_department_report', 'userid = ' . $user_id);
        if (!empty($str_leader_department)) {
            $leader_department = explode(',', $str_leader_department[0]['view_department_report']);
        }
        $list_department = array();
        $DEPARTMENT = new Department();
        $catList = $DEPARTMENT->getCatList();
        foreach ($catList as $department) {
            $list_department[$department['id']] = $department;
            $list_department[$department['id']]['checked'] = in_array($department['id'], $leader_department) ? 'checked' : '';
        }
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        foreach ($list_department as $department) {
            $department['name'] = str_replace(">", "", $department['name']);
            $department['name'] = str_replace("|", "", $department['name']);
            $department['name'] = str_replace("-", "&nbsp;", $department['name']);
            $xtpl->assign('DEPARTMENT', $department);
            $xtpl->parse('list_department_report.loop');
        }
        $xtpl->parse('list_department_report');
        return $xtpl->text('list_department_report');
    }

    public function htmlInfoHr($username, $human_resources = null)
    {
        if (empty($username) && $human_resources == null)
            return '';
        if (empty($human_resources)) {
            $md5username = nv_md5safe($username);
            if (preg_match('/^([0-9]+)$/', $username)) {
                $sql = 'SELECT userid, username, email, first_name, last_name, gender, photo, birthday FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . intval($username) . ' OR md5username=' . $this->db->quote($md5username);
            } else {
                $sql = 'SELECT userid, username, email, first_name, last_name, gender, photo, birthday FROM ' . NV_USERS_GLOBALTABLE . ' WHERE md5username=' . $this->db->quote($md5username);
            }
            $human_resources = $this->db->query($sql)->fetch(2);
        }
        $human_resources['full_name'] = isset($human_resources['first_name']) ? nv_show_name_user($human_resources['first_name'], $human_resources['last_name'], $human_resources['username']) : '';
        $human_resources['gender'] = ($human_resources['gender'] === 'M') ? $this->lang_module['male'] : (($human_resources['gender'] === 'F') ? $this->lang_module['female'] : '');

        if (empty($human_resources['photo'])) {
            $human_resources['photo'] = NV_BASE_SITEURL . "themes/default/images/users/no_avatar.png";
        } else {
            $human_resources['photo'] = NV_BASE_SITEURL . $human_resources['photo'];
            if (defined('SSO_REGISTER_DOMAIN')) {
                $human_resources['photo'] = SSO_REGISTER_DOMAIN . $human_resources['photo'];
            }
        }

        $human_resources['birthday'] = !empty($human_resources['birthday']) ? date("d/m/Y", $human_resources['birthday']) : $human_resources['birthday'];
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('HUMAN_RESOURCES', $human_resources);
        $xtpl->parse('human_resources_info');
        return $xtpl->text('human_resources_info');
    }

    public function htmlAdd($userid = 0)
    {
        global $crypt, $global_array_ranks;
        // filtersql
        $filtersql = ' userid NOT IN (SELECT userid FROM ' . $this->table_data . ')';
        $human_resources = array(
            'id' => 0,
            'full_name' => '',
            'gender' => null,
            'photo' => '',
            'birthday' => '',
            'userid' => 0,
            'office_id' => '',
            'department_id' => '',
            'phone' => '',
            'cmnd' => '',
            'address' => '',
            'still_working' => 1,
            'no_aswork' => 0,
            'role_id' => [],
            'rank_id' => 0,
        );
        $human_resources = !empty($userid) ? $this->getById($userid) : $human_resources;
        $human_resources['still_working'] = empty($human_resources['still_working']) ? '' : ' checked="checked"';
        $human_resources['no_aswork'] = empty($human_resources['no_aswork']) ? '' : ' checked="checked"';
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('FILTERSQL', $crypt->encrypt($filtersql, NV_CHECK_SESSION));
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('disabled_select', !empty($userid) ? "disabled" : "");
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('LIST_HUMAN_RESOURCEST_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE . '=human_resources');
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('HUMAN_RESOURCES_BASIC', $this->htmlInfoHr(null, $human_resources));
        $xtpl->assign('HUMAN_RESOURCES', $human_resources);

        $OFFICE = new Office();
        $office_item = $OFFICE->getById($human_resources['office_id']);
        if (empty($office_item['leader'])) {
            $xtpl->assign('department_d_none', 'style="display: none"');
        }
        $xtpl->assign('LIST_DEPARTMENT', $this->htmlDepartments($human_resources['department_id'], $userid));
        $xtpl->assign('LIST_DEPARTMENT_PENALIZE', $this->htmlDepartmentsPenalize($human_resources['department_id'], $userid));
        $xtpl->assign('LIST_DEPARTMENT_ABSENCE', $this->htmlDepartmentsAbsence($human_resources['department_id'], $userid));
        $xtpl->assign('LIST_DEPARTMENT_REPORT', $this->htmlDepartmentReport($human_resources['department_id'], $userid));

        $DEPARTMENT = new Department();
        $catList = $DEPARTMENT->getCatList();
        foreach ($catList as $row) {
            $xtpl->assign('ROW', $row);
            $xtpl->assign('selected', $row['id'] == $human_resources['department_id'] ? 'selected' : '');
            $xtpl->parse('human_resources_add.listcat');
        }
        $OFFICE = new Office();
        $officeList = $OFFICE->getAll('*', null, 'weight ASC');
        foreach ($officeList as $row) {
            $xtpl->assign('ROW', $row);
            $xtpl->assign('selected', $row['id'] == $human_resources['office_id'] ? 'selected' : '');
            $xtpl->parse('human_resources_add.list_office');
        }

        // Xuất các role. Chỉ quản trị tối cao được thiết lập role này
        if (defined('NV_IS_GODADMIN')) {
            foreach ($this->array_roles as $role_id) {
                $xtpl->assign('ROLE', [
                    'id' => $role_id,
                    'title' => $this->lang_module['hr_role' . $role_id],
                    'checked' => in_array($role_id, $human_resources['role_id']) ? ' checked="checked"' : ''
                ]);
                $xtpl->parse('human_resources_add.role.loop');
            }
            $xtpl->parse('human_resources_add.role');
        }

        // Xuất bậc nhân viên
        foreach ($global_array_ranks as $rank) {
            if (empty($rank['status'])) {
                continue;
            }
            $rank['selected'] = $rank['id'] == $human_resources['rank_id'] ? ' selected="selected"' : '';
            $xtpl->assign('RANK', $rank);
            $xtpl->parse('human_resources_add.rank');
        }

        $xtpl->parse('human_resources_add');
        return $xtpl->text('human_resources_add');
    }

    public function getData($per_page = 30, $page = 1)
    {
        $dataReturn = array();
        $where = array();
        $this->search_array['name'] = trim($this->search_array['name']);
        if (!empty($this->search_array['name'])) {
            $sql = $this->global_config['name_show'] == 0 ? "concat(last_name,' ',first_name)" : "concat(first_name,' ',last_name)";
            $where[] = $sql . 'LIKE "%' . $this->search_array['name'] . '%"';
        }
        if (!empty($this->search_array['email'])) {
            $where[] = 'user.email LIKE "%' . $this->db->dblikeescape($this->search_array['email']) . '%"';
        }
        if (!empty($this->search_array['department_id'])) {
            $where[] = 'human_resources.department_id = ' . $this->search_array['department_id'];
        }
        if (!empty($this->search_array['office_id'])) {
            $where[] = 'human_resources.office_id = ' . $this->search_array['office_id'];
        }
        if (!empty($this->search_array['confirm_workout'])) {
            $where[] = '(
                human_resources.confirm_workout IS NOT NULL AND
                human_resources.confirm_workout!=\'0\' AND
                human_resources.confirm_workout!=\'\'
            )';
        }
        $this->db->sqlreset()
            ->select('COUNT(*)')
            ->from($this->table_data . ' AS human_resources')
            ->join(
                'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' AS user ON human_resources.userid = user.userid ' .
                'LEFT JOIN ' . $this->table_module . '_department AS department ON human_resources.department_id = department.id ' .
                'LEFT JOIN ' . $this->table_module . '_office AS office ON human_resources.office_id = office.id '
            )->order('human_resources.weight ASC');
        if (!empty($where)) {
            $this->db->where(implode(' AND ', $where));
        }
        $this->num_items = $this->db->query($this->db->sql())->fetchColumn();
        $this->db->select('user.userid, user.username, user.email, user.first_name, user.last_name, user.photo, human_resources.*, department.title AS department_title, office.title AS office_title');
        $this->db->limit($per_page)->offset(($page - 1) * $per_page);
        $result = $this->db->query($this->db->sql());
        $dataReturn = array();
        while ($row = $result->fetch(2)) {
            if (empty($row['photo'])) {
                $row['photo'] = NV_BASE_SITEURL . "themes/default/images/users/no_avatar.png";
            } else {
                $row['photo'] = NV_BASE_SITEURL . $row['photo'];
                if (defined('SSO_REGISTER_DOMAIN')) {
                    $row['photo'] = SSO_REGISTER_DOMAIN . $row['photo'];
                }
            }
            $row['edit'] = $this->base_url . "&amp;" . NV_OP_VARIABLE . "=human_resources_add&amp;id=" . $row['id'];
            $row['delete'] = $this->base_url . "&amp;" . NV_OP_VARIABLE . "=human_resources&amp;delete=1&amp;id=" . $row['id'] . "&amp;checkss=" . $this->checkss;
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $dataReturn[] = $row;
        }
        return $dataReturn;
    }

    public function html($per_page = 30, $page = 1)
    {
        global $global_array_ranks;

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
        if (!empty($this->search_array['confirm_workout'])) {
            $base_url .= '&amp;cw=1';
        }
        $listPagination = $this->getData($per_page, $page);

        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
        $xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
        $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
        $xtpl->assign('MODULE_NAME', $this->module_name);
        $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('TOKEND', NV_CHECK_SESSION);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('search_of_per_page', $per_page);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name);
        if ($this->search_array['name']) {
            $xtpl->assign('search_name', $this->search_array['name']);
        }
        if ($this->search_array['email']) {
            $xtpl->assign('search_email', $this->search_array['email']);
        }
        $xtpl->assign('CONFIRM_WORKOUT', $this->search_array['confirm_workout'] ? ' checked="checked"' : '');

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

        $tt = ($page - 1) * $per_page + 1;
        foreach ($listPagination as $row) {
            $row['tt'] = $tt++;
            $row['rank'] = isset($global_array_ranks[$row['rank_id']]) ? $global_array_ranks[$row['rank_id']]['title'] : ('#' . $row['rank_id']);
            $row['monthly_salary'] = isset($global_array_ranks[$row['rank_id']]) ? Money::format($global_array_ranks[$row['rank_id']]['monthly_salary']) : '';

            $xtpl->assign('ROW', $row);

            if (!empty($row['rank_id'])) {
                $xtpl->parse('main.loop.rank');
            }

            $xtpl->parse('main.loop');
        }
        foreach ([10, 30, 50] as $per_page_number) {
            $xtpl->assign('per_page_number', $per_page_number);
            $xtpl->assign('selected', $per_page_number == $per_page ? 'selected' : '');
            $xtpl->parse('main.per_page_loop');
        }

        $generate_page = nv_generate_page($base_url, $this->num_items, $per_page, $page);
        if (!empty($generate_page)) {
            $xtpl->assign('GENERATE_PAGE', $generate_page);
            $xtpl->parse('main.generate_page');
        }
        $xtpl->parse('main');
        $contents = $xtpl->text('main');
        return $contents;
    }

    public function getData_Member($data)
    {
        // Lấy dữ liệu ở đây
        $result = $this->db->query('SELECT * FROM  ' . $this->table_module . '_checkio WHERE month = ' . intval($data['month']) . ' AND year = ' . intval($data['year']));
        $array_checkio = [];
        while ($row = $result->fetch()) {
            if (empty($array_checkio[$row['userid']][$row['day']])) {
                $array_checkio[$row['userid']][$row['day']] = array(
                    'workday' => 0,
                    'late' => 0,
                    'checkin_real' => $row['checkin_real'],
                    'checkout_real' => $row['checkout_real'],
                    'workout' => 0,
                    'worktime' => 0
                );
            }
            $array_checkio[$row['userid']][$row['day']]['late'] += $row['late'];
            $array_checkio[$row['userid']][$row['day']]['workday'] += $row['workday'];
            $array_checkio[$row['userid']][$row['day']][$row['status']] += $row['workday'];
        }
        return $array_checkio;
    }

    public function getAllMembers()
    {
        $sql = 'SELECT tb1.userid, tb1.username, tb1.email, tb1.first_name, tb1.last_name
        FROM ' . NV_USERS_GLOBALTABLE . ' tb1
        INNER JOIN ' . $this->table_module . '_human_resources tb2 ON tb1.userid=tb2.userid
        WHERE tb1.active=1 AND tb2.still_working=1 ORDER BY tb2.weight ASC';
        $result = $this->db->query($sql);

        $all_members = [];
        while ($row = $result->fetch()) {
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $all_members[$row['userid']] = $row;
        }

        return $all_members;
    }

    /**
     * Lấy danh sách lãnh đạo của nhân sự:
     * - Toàn bộ lãnh đạo phòng ban cao hơn
     * - Nếu nhân sự không là trưởng phòng lấy hết trưởng phòng của phòng ban hiện tại
     * - Nếu nhân sự là trưởng phòng thì lấy trưởng phòng có cấp bậc xếp trên khi không có lãnh đạo cao hơn nào
     *
     * @param int $additional_type
     * 0 là không lấy thêm
     * 1 là lấy thêm nhân sự xác nhận làm thêm
     */
    public function getManager($additional_type = 0)
    {
        global $global_array_office_leader, $department_array, $global_array_offices;
        if (empty($global_array_office_leader)) {
            return [];
        }
        $department_ids = GetlistDepartmentIdParent($this->user_info['department_id']);

        // Lấy hết quản lý từ phòng của mình trở về trước
        $list_manager = [];
        $this->db->sqlreset()
            ->select('tb2.userid, tb2.username, tb2.email, tb2.first_name, tb2.last_name, tb1.office_id, tb1.department_id')
            ->from($this->table_module . '_human_resources tb1')
            ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' tb2 ON tb1.userid = tb2.userid')
            ->where('tb1.department_id IN ( ' . implode(',', $department_ids) . ') AND
            tb1.userid!=' . $this->user_info['userid'] . ' AND
            tb1.office_id IN(' . implode(',', $global_array_office_leader) . ') AND tb1.still_working=1');
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            if (!isset($department_array[$row['department_id']], $global_array_offices[$row['office_id']])) {
                continue;
            }
            $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
            $row['office_title'] = $global_array_offices[$row['office_id']]['title'];
            $list_manager[$department_array[$row['department_id']]['lev']][$global_array_offices[$row['office_id']]['weight']][] = $row;
        }

        /*
         * Lấy thêm người được duyệt làm thêm theo cấu hình riêng
         * Người được cấu hình phải là trưởng phòng
         */
        $list_others = [];
        if ($additional_type == 1) {
            $this->db->sqlreset()
                ->select('tb2.userid, tb2.username, tb2.email, tb2.first_name, tb2.last_name, tb1.office_id, tb1.department_id')
                ->from($this->table_module . '_human_resources tb1')
                ->join('INNER JOIN ' . NV_USERS_GLOBALTABLE . ' tb2 ON tb1.userid = tb2.userid')
                ->where('FIND_IN_SET("' . $this->user_info['department_id'] . '", tb1.confirm_workout) AND
                tb1.userid!=' . $this->user_info['userid'] . ' AND
                tb1.office_id IN(' . implode(',', $global_array_office_leader) . ') AND tb1.still_working=1');
            $result = $this->db->query($this->db->sql());
            while ($row = $result->fetch(2)) {
                if (!isset($department_array[$row['department_id']], $global_array_offices[$row['office_id']])) {
                    continue;
                }
                $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name'], $row['username']);
                $row['office_title'] = $global_array_offices[$row['office_id']]['title'];
                $list_others[$row['userid']] = $row;
            }
        }

        // Xếp theo lev phòng ban thấp => Cao
        krsort($list_manager);

        // Lặp xuất lãnh đạo
        $leader_same_department = [];
        $leaders = [];

        foreach ($list_manager as $department_lever => $office_managers) {
            // Xếp cấp bậc cao => thấp
            ksort($office_managers);

            foreach ($office_managers as $office_weight => $managers) {
                foreach ($managers as $manager) {
                    if (
                        $this->user_info['department_id'] != $manager['department_id'] or // Phòng ban cao hơn
                        empty($this->user_info['leader'])
                    ) {
                        $leaders[$manager['department_id']][$manager['userid']] = $manager;
                    }
                    if (
                        $this->user_info['department_id'] == $manager['department_id'] and
                        !empty($this->user_info['leader']) and
                        $global_array_offices[$manager['office_id']]['weight'] < $this->user_info['office_weight']
                    ) {
                        $leader_same_department[$manager['userid']] = $manager;
                    }
                }
            }
        }

        // Bổ sung quản lý theo cấu hình riêng
        if (!empty($list_others)) {
            if (empty($leaders)) {
                $leaders[$this->user_info['department_id']] = $list_others;
            } else {
                $key = array_key_first($leaders);
                foreach ($list_others as $other) {
                    if (!isset($leaders[$key][$other['userid']])) {
                        $leaders[$key][$other['userid']] = $other;
                    }
                }
            }
        }

        // Không có ai thì lấy quản lý cùng phòng ban mà chức vụ cao hơn
        if (empty($leaders) and !empty($leader_same_department)) {
            $leaders[$this->user_info['department_id']] = $leader_same_department;
        }

        return $leaders;
    }
}
