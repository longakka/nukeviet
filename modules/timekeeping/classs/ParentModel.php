<?php

/**
* Sử dụng đối với dữ liệu có trường parentid và weight
 */

namespace Modules\timekeeping\classs;

use PDO;

abstract class ParentModel extends Main
{
    protected $catList;
    public function __construct($where = null)
    {
        parent::__construct();
        $catList = $this->getAll('*', $where, 'parentid,weight ASC');
        $this->catList = $this->getListLevel($catList);
    }
    public abstract function dataPost();
    public function getCatList() {
        return $this->catList;
    }
    /*
    Tạo mãng dữ liệu theo cấp bậc cha->con
    kết hợp 2 function la nv_setCats và getListLevel
    */
    public function nv_setCats($list2, $id, $list, $num = 0)
    {
        $num++;
        $defis = "";
        for ($i = 0; $i < $num; $i++) {
            $defis .= "---";
        }
        if (isset($list[$id])) {
            foreach ($list[$id] as $value) {
                $list2[$value['id']] = $value;
                $list2[$value['id']]['level'] = $num;
                $list2[$value['id']]['count'] = isset($list[$value['id']]) ? count($list[$value['id']]) : 0;
                $list2[$value['id']]['pcount'] = count($list[$list2[$value['id']]['parentid']]);
                $list2[$value['id']]['name'] = "&nbsp;|" . $defis . "> " . $list2[$value['id']]['name'];
                if (isset($list[$value['id']])) {
                    $list2 = $this->nv_setCats($list2, $value['id'], $list, $num);
                }
            }
        }
        return $list2;
    }
    public function getListLevel($list_array, $name = 'title') {
        $list = array();
        foreach ($list_array as $row) {
            $row['name'] = $row[$name];
            $list[$row['parentid']][] = $row;
        }
        if (empty($list)) {
            return $list;
        }
        $list2 = array();
        /*
        count: so luong cac 'cat' la con cua no
        pcount: so luong cac 'cat' ngang hang voi no, pcount ->parent count
        */
        foreach ($list[0] as $value) {
            $list2[$value['id']] = $value;
            $list2[$value['id']]['level'] = 0;
            $list2[$value['id']]['count'] = isset($list[$value['id']]) ? count($list[$value['id']]) : 0;
            $list2[$value['id']]['pcount'] = count($list[$list2[$value['id']]['parentid']]);
            if (isset($list[$value['id']])) {
               $list2 = $this->nv_setCats($list2, $value['id'], $list);
            }
        }
        return $list2;
    }
    public function fix_Weight($parentid)
    {
        $listCat = $this->getAll('*', "parentid=" . intval($parentid), "weight ASC");
        $weight = 0;
        foreach ($listCat as $row) {
            $weight++;
            $this->updateCSLD(array('weight' => $weight), 'id = ' . $row['id'], array('weight' => PDO::PARAM_INT));
        }
        return true;
    }
    public function updateWeight($id, $cWeight)
    {
        if (!isset($this->catList[$id])) die("ERROR");

        if ($cWeight > $this->catList[$id]['pcount']) $cWeight = $this->catList[$id]['pcount'];

        $result = $this->getAll('id', "parentid=" . intval($this->catList[$id]['parentid']) . " AND id!=" . $id, "weight ASC");
        $weight = 0;
        foreach ($result as $row) {
            $weight++;
            if ($weight == $cWeight) $weight++;
            $this->updateCSLD(array('weight' => $weight), 'id = ' . $row['id'], array('weight' => PDO::PARAM_INT));
        }
        $this->updateCSLD(array('weight' => $cWeight), 'id = ' . $id, array('weight' => PDO::PARAM_INT));
        $this->nv_Cache->delMod($this->module_name);
        nv_insert_logs(NV_LANG_DATA, $this->module_name, $this->lang_module['logChangeWeightDepartment'], "Id: " . $id, $this->admin_info['userid']);
        return true;
    }
    public function deleteById($id)
    {
        $this->checkssAdmin($this->nv_Request->get_string('checkss', 'post,get'));
        if (!isset($this->catList[$id])) nv_htmlOutput($this->lang_module['errorNotExists']);

        if ($this->catList[$id]['count'] > 0) nv_htmlOutput($this->lang_module['errorNeedDelSub']);

        parent::deleteById($id);
        $this->fix_Weight($this->catList[$id]['parentid']);
        $this->nv_Cache->delMod($this->module_name);
        nv_insert_logs(NV_LANG_DATA, $this->module_name, $this->lang_module['logDelDepartment'], "Id: " . $id, $this->admin_info['userid']);
        return true;
    }
    public function editById($id, $data_post = array())
    {
        $if_fixWeight = false;
        $weight = $this->catList[$id]['weight'];
        if ($data_post['parentid'] != $this->catList[$id]['parentid']) {
            $weight = $this->count("parentid=" . $data_post['parentid'], "MAX(weight) as nweight");
            $weight = !empty($weight) ? $weight+1 : 1;
            $if_fixWeight = $this->catList[$id]['parentid'];
        }
        $data_post['weight'] = $weight;
        parent::editById($id, $data_post);
        if ($if_fixWeight !== false) $this->fix_Weight($if_fixWeight);
        $this->nv_Cache->delMod($this->module_name);
        nv_insert_logs(NV_LANG_DATA, $this->module_name, $this->table_data, "Id: " . $id, $this->admin_info['userid']);
        return true;
    }
    public function save($data, $array_type = null, $keys = null) {
        $data['weight'] = $this->count('parentid='. $data['parentid']) + 1;
        $statement = parent::save($data);
        return $statement;
    }
}
