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

class Department extends ParentModel
{

    private $tpl = 'department';

    protected $title = 'department';

    protected $table_data = 'department';

    public function dataPost()
    {
        $post = [];

        $post['parentid'] = $this->nv_Request->get_int('parentid', 'post', 0);
        $post['title'] = $this->nv_Request->get_title('title', 'post', '', 1);
        $post['description'] = $this->nv_Request->get_title('description', 'post', '', 1);
        $post['description'] = nv_nl2br($post['description'], "<br />");
        $post['alias'] = $this->nv_Request->get_title('alias', 'post', '', 1);
        $post['view_all_task'] = $this->nv_Request->get_int('view_all_task', 'post', 0);
        $post['view_all_absence'] = $this->nv_Request->get_int('view_all_absence', 'post', 0);
        $post['checkin_out_session'] = $this->nv_Request->get_int('checkin_out_session', 'post', 0);
        $post['check_work'] = (int) $this->nv_Request->get_bool('check_work', 'post', false);

        return $post;
    }

    /**
     * @param number $id
     * @return mixed
     */
    public function htmlAdd($id = 0)
    {
        $department = [
            'id' => 0,
            'parentid' => 0,
            'alias' => '',
            'name' => '',
            'description' => '',
            'weight' => 1,
            'view_all_task' => 0,
            'view_all_absence' => 0,
            'checkin_out_session' => 0,
            'level' => 0,
            'check_work' => 0,
        ];

        $department = !empty($id) ? $this->getById($id) : $department;

        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('LIST_DEPARTMENT_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE . '=department');
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('DEPARTMENT', $department);

        $departmentShrink = $this->catList;

        if ($id > 0 && !empty($department)) {
            $department['level'] = $this->catList[$id]['level'];
            $departmentShrink = array();
            $g = false;
            foreach ($this->catList as $row) {
                if ($row['id'] == $department['id']) {
                    $g = true;
                    continue;
                }
                if ($g && ($row['level'] > $department['level']))
                    continue;
                $departmentShrink[] = $row;
            }
        }

        foreach ($departmentShrink as $row) {
            $xtpl->assign('ROW', $row);
            $xtpl->assign('selected', $row['id'] == $department['parentid'] ? 'selected' : '');
            $xtpl->parse('department_add.listcat');
        }

        $xtpl->assign('checked_view_all_task', !empty($department['view_all_task']) ? 'checked' : '');
        $xtpl->assign('checked_view_all_absence', !empty($department['view_all_absence']) ? 'checked' : '');
        $xtpl->assign('checked_checkin_out_session', !empty($department['checkin_out_session']) ? 'checked' : '');
        $xtpl->assign('CHECK_WORK', !empty($department['check_work']) ? 'checked="checked"' : '');

        $xtpl->parse('department_add');
        return $xtpl->text('department_add');
    }

    public function htmlList($parentid = 0)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('PARENTID', $parentid);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE);

        $a = 0;

        foreach ($this->catList as $id => $values) {
            if ($values['parentid'] == $parentid) {
                $loop = array(
                    'id' => $id,
                    'title' => $values['title'],
                    'count' => $values['count']
                );
                $xtpl->assign('LOOP', $loop);
                for ($i = 1; $i <= $values['pcount']; $i++) {
                    $opt = array(
                        'value' => $i,
                        'selected' => $i == $values['weight'] ? " selected=\"selected\"" : ""
                    );
                    $xtpl->assign('NEWWEIGHT', $opt);
                    $xtpl->parse('list.loop.option');
                }

                if ($loop['count'] != 0)
                    $xtpl->parse('list.loop.count');
                else
                    $xtpl->parse('list.loop.countEmpty');

                $xtpl->parse('list.loop');
                $a++;
            }
        }

        $xtpl->parse('list');
        return $xtpl->text('list');
    }

    public function html()
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE);
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
    /**
     * Lấy danh sách các phòng ban con mà có/chưa có lãnh đạo
     * @param int $department_id
     * @param int $leader = 1 -> có lãnh đạo
     * @param array $department_exist có tác dụng để phòng đề quy chạy mãi
     * @return array
     */
    public function get_department_children($department_id, $leader = 1, $department_exist = [])
    {
        $data = [];
        $list_department_id = [];
        $this->db->sqlreset()
        ->select('t1.id, t1.title, t4.leader, count(*) sum')
        ->from($this->table_data . ' t1')
        ->join('INNER JOIN ' . $this->table_module . '_human_resources t2 ON t1.id = t2.department_id ' . 
        'INNER JOIN ' . NV_USERS_GLOBALTABLE . ' t3 ON t2.userid = t3.userid ' . 
        'INNER JOIN ' . $this->table_module . '_office t4 ON t2.office_id = t4.id')
        ->where('t1.parentid = ' . $department_id . ' AND t1.id NOT IN (' . implode(',', $department_exist) . ')')
        ->group('t2.department_id, t4.leader');
        
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $list_department_id[$row['id']] = false;
            $data[] = $row;
        }
        foreach ($data as $row) {
            if ($row['leader'] == 1 && $row['sum'] > 0) {
                $list_department_id[$row['id']] = true;
            }
        }
        $data_return = $leader == 1 ? array_filter( $list_department_id, function ($item){
            return !empty($item);
        }) : array_filter( $list_department_id, function ($item){
            return empty($item);
        });
        $data_return = array_keys($data_return);
        $data_new = [];
        foreach ($data_return as $department_id) {
            $data_new =  array_merge($this->get_department_children($department_id, $leader, $data_return), $data_new);
        }
        return array_merge($data_return, $data_new);
    }
}
