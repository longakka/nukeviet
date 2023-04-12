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

class Task extends Main
{
    private $tpl = 'task';
    protected $title  = 'task';
    protected $table_data = 'task';
    public function dataPost()
    {
        $post = array();

        $post['title'] = $this->nv_Request->get_title('title', 'post', '', 1);
        $post['parentid'] = $this->nv_Request->get_int('parentid', 'post', 0);
        $post['taskcat_id'] = $this->nv_Request->get_int('taskcat_id', 'post', 0);
        $post['description'] = $this->nv_Request->get_title('description', 'post', '', '');
        $post['description'] = nv_nl2br($post['description'], "<br />");
        $post['link'] = $this->nv_Request->get_title('link', 'post', '', '');
        $post['userid_create'] = $this->HR->user_info['userid'];
        return $post;
    }
    public function search($term) {
        $this->db->sqlreset()
        ->select('id, title AS text')
        ->from($this->table_data)
        ->where("title LIKE '%" . $term . "%'")
        ->limit(10)
        ->order('id ASC');
        $data=array();
        $result = $this->db->query($this->db->sql());
        while($row = $result->fetch(2)) {
            $row['text'] .= "(#" . $row['id'] . ")";
            $data[]=$row;
        }
        return $data;
    }
    public function editById($id, $data = array())
    {
        list($userid_create) = $this->db->query("SELECT userid_create FROM " . $this->table_data . " WHERE id = $id")->fetch(3);
        if ($userid_create == $this->HR->user_info['userid']) {
            parent::editById($id, $data);
            return true;
        }
        return false;
    }
    public function htmlAdd($id) {
        $task = array(
            'id' => 0,
            'parentid' => 0,
            'alias' => '',
            'name' => '',
            'description' => '',
            'weight' => 1,
            'level' => 0
        );
        $task = !empty($id) ? $this->getById($id) : $task;
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
        $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
        $xtpl->assign('LIST_TASK_URL', "/");
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        $xtpl->assign('TASK', $task);
        $TASKCAT = new TaskCat();
        $catList=$TASKCAT->getCatList();
        foreach($catList as $value) {
            $xtpl->assign('LOOP', $value);
            $xtpl->assign('selected', $value['id']==$task['taskcat_id'] ? 'selected' : '');
            $xtpl->parse('main.loop');

        }
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
