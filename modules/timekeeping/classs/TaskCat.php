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

class TaskCat extends ParentModel
{
    private $tpl = 'taskcat';
    protected $title  = 'taskcat';
    protected $table_data = 'taskcat';
    public function __construct()
    {
        parent::__construct('status=1');
    }
    public function dataPost()
    {
        $post = array();
        $post['parentid'] = $this->nv_Request->get_int('parentid', 'post', 0);
        $post['title'] = $this->nv_Request->get_title('title', 'post', '', 1);
        $post['department'] = $this->nv_Request->get_int('department', 'post', '', 0);
        $post['description'] = $this->nv_Request->get_title('description', 'post', '', 1);
        $post['description'] = nv_nl2br($post['description'], "<br />");
        $post['alias'] = $this->nv_Request->get_title('alias', 'post', '', 1);
        return $post;
    }

    public function htmlAdd($id = 0)
    {
        global $db, $db_config, $module_data;
        $taskcat = array(
            'id' => 0,
            'parentid' => 0,
            'department' => 0,
            'alias' => '',
            'name' => '',
            'description' => '',
            'weight' => 1,
            'level' => 0
        );
        $taskcat = !empty($id) ? $this->getById($id) : $taskcat;
        $xtpl = new XTemplate($this->tpl . '.tpl', $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('LIST_TASKCAT_URL',   NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE . '=taskcat');
        $xtpl->assign('OP', $this->op);
        $xtpl->assign('TASKCAT', $taskcat);
        $taskcatShrink = $this->catList;
        if ($id > 0 && !empty($taskcat)) {
            $taskcat['level'] = $this->catList[$id]['level'];
            $taskcatShrink = array();
            $g = false;
            foreach ($this->catList as $row) {
                if ($row['id'] == $taskcat['id']) {
                    $g = true;
                    continue;
                }
                if ($g && ($row['level'] > $taskcat['level'])) continue;
                $taskcatShrink[] = $row;
            }
        }
        foreach ($taskcatShrink as $row) {
            $xtpl->assign('ROW', $row);
            $xtpl->assign('selected', $row['id'] == $taskcat['parentid'] ? 'selected' : '');
            $xtpl->parse('taskcat_add.listcat');
        }

        $_sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_department ORDER BY parentid ASC';
        $_query = $db->query($_sql);
        while ($_row = $_query->fetch()) {
            $xtpl->assign('DEPARTMENT', $_row);
            $xtpl->parse('taskcat_add.departments');
        }
        $xtpl->parse('taskcat_add');
        return $xtpl->text('taskcat_add');
    }
    public function htmlList($parentid = 0)
    {
        global $db, $db_config, $module_data;
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('PARENTID', $parentid);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name . '&' . NV_OP_VARIABLE);
        $a = 0;

        $_sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_department ORDER BY parentid ASC';
        $_query = $db->query($_sql);
        while ($_row = $_query->fetch()) {
            $department_array[$_row['id']] = $_row['title'];
        }

        $catList = $this->getAll('*', null, 'parentid,weight,department ASC');
        $catList = $this->getListLevel($catList);
        foreach ($catList as $id => $values) {
            if ($values['parentid'] == $parentid) {
                $loop = array(
                    'id' => $id,
                    'title' => $values['title'],
                    'count' => $values['count'],
                    'department' =>  $department_array[$values['department']],
                    'status_checked' => !empty($values['status']) ?  'checked="checked"' : ''
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
}
