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

class Office extends Main
{
    private $tpl = 'office';
    protected $title  = 'office';
    protected $table_data = 'office';
    public function dataPost()
    {
        $post = array();
        $post['title'] = $this->nv_Request->get_title('title', 'post', '', 1);
        $post['description'] = $this->nv_Request->get_title('description', 'post', '', 1);
        $post['description'] = nv_nl2br($post['description'], "<br />");
        $post['leader'] = $this->nv_Request->get_absint('leader', 'post', 0);
        $post['manager'] = $this->nv_Request->get_absint('manager', 'post', 0);
        return $post;
    }
    public function save($data, $array_type = null, $keys = null) {
        $data['weight'] = $this->count() + 1;
        $statement = parent::save($data);
        return $statement;
    }
    public function htmlList()
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name );
        $xtpl->assign('CHECKSS', $this->checkss);
        $a = 0;
        $listOffice = $this->getAll( "*", null, $order = "weight ASC");
        $count = count($listOffice);
        foreach ($listOffice as $id => $values) {
            $values['count'] = $count;
            $values['leader_checked'] = (!empty($values['leader'])) ? 'checked="checked"' : '';
            $values['manager_checked'] = (!empty($values['manager'])) ? 'checked="checked"' : '';
            $xtpl->assign('LOOP', $values);
            for ($i = 1; $i <= $count; $i++) {
                $opt = array(
                    'value' => $i,
                    'selected' => $i == $values['weight'] ? " selected=\"selected\"" : ""
                );
                $xtpl->assign('NEWWEIGHT', $opt);
                $xtpl->parse('list.loop.option');
            }
            $xtpl->parse('list.loop');
        }
        $xtpl->parse('list');
        return $xtpl->text('list');
    }
    public function html($id=0)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_ADMINURL', NV_BASE_ADMINURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op);
        $xtpl->assign('MODULE_URL', NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $this->module_name );
        $office = array(
            'title' => '',
            'description' => '',
            'manager_checked' => '',
            'leader_checked' => '',
        );
        if (!empty($id)) {
            $office = $this->getById($id);
            $office['manager_checked'] = (!empty($office['manager'])) ? 'checked="checked"' : '';
            $office['leader_checked'] = (!empty($office['leader'])) ? 'checked="checked"' : '';
        }
        $xtpl->assign('action_title', !empty($id) ? $this->lang_module['edit_office'] . ": " : $this->lang_module['add_office'] );
        $xtpl->assign('name_office', $office['title'] );
        $xtpl->assign('OFFICE', $office);
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
