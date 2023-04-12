<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/31/2009 0:51
 */

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

// Trạng thái các công việc
$global_array_status = [];
$global_array_status[1] = $lang_module['working'];
$global_array_status[2] = $lang_module['finish'];
$global_array_status[3] = $lang_module['stop'];

// Danh sách phòng ban
$_sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department ORDER BY sort ASC';
$department_array = $nv_Cache->db($_sql, 'id', $module_name);

// Cache id office Leader list
$_sql = 'SELECT t1.id, t1.userid, t1.office_id, t1.department_id
FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources as t1
INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_office as t2 ON t1.office_id = t2.id
WHERE t2.leader=1 AND t1.still_working=1';
$leader_array = $nv_Cache->db($_sql, 'userid', $module_name);

// Cache id office Manager list
$_sql = 'SELECT t1.id, t1.userid, t1.office_id, t1.department_id
FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources as t1
INNER JOIN ' . $db_config['prefix'] . '_' . $module_data . '_office as t2 ON t1.office_id = t2.id
WHERE t2.manager=1 AND t1.still_working=1';
$manager_array = $nv_Cache->db($_sql, 'userid', $module_name);

// Bậc nhân viên
$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_ranks ORDER BY weight ASC';
$global_array_ranks = $nv_Cache->db($sql, 'id', $module_name);

// Các dự án đang làm
$global_array_project = [];
$sql = "SELECT * FROM " . $db_config['prefix'] . '_' . $module_data . "_taskcat WHERE status=1 ORDER BY sort ASC";
$global_array_project = $nv_Cache->db($sql, 'id', $module_name);

// Chức vụ
$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_office ORDER BY weight ASC';
$global_array_offices = $nv_Cache->db($sql, 'id', $module_name);

// Các chức vụ là trưởng phòng
$global_array_office_leader = [];
foreach ($global_array_offices as $office) {
    if (!empty($office['leader'])) {
        $global_array_office_leader[] = $office['id'];
    }
}

$page_title = isset($OPCLASS) ? $OPCLASS->lang_title : '';

if ($nv_Request->isset_request('get_alias_title', 'post')) {
    $id = $nv_Request->get_int('id', 'post, get', 0);
    $title = $nv_Request->get_title('get_alias_title', 'post', '');
    $alias = $OPCLASS->getAlias($title, $id);
    nv_htmlOutput($alias);
}
if ($nv_Request->get_int('delete', 'post', 0)) {
    $id = $nv_Request->get_int('delete', 'post', 0);
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $statement = $OPCLASS->deleteById($id);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}
if ($nv_Request->get_int('save', 'post', 0)) {
    $id = $nv_Request->get_int('id', 'post,get', 0);
    $OPCLASS->checkssAdmin($nv_Request->get_string('checkss', 'post'));
    $data_post = $OPCLASS->dataPost();
    if ($id > 0) {
        $statement = $OPCLASS->editById($id, $data_post);
    } else {
        $statement = $OPCLASS->save($data_post);
    }
    $nv_Cache->delMod($module_name);
    nv_htmlOutput($statement ? "OK" : 'ERROR');
}

/**
 * Lấy danh sách phòng ban con tính cả phòng ban được lấy
 *
 * @param integer $id
 * @return array
 */
function GetDepartmentidInParent($id)
{
    global $department_array;

    $array_cat = [];
    $array_cat[] = $id;
    $subcatid = explode(',', $department_array[$id]['subcatid']);

    if (!empty($subcatid)) {
        foreach ($subcatid as $id) {
            if ($id > 0) {
                if ($department_array[$id]['numsubcat'] == 0) {
                    $array_cat[] = $id;
                } else {
                    $array_cat_temp = GetDepartmentidInParent($id);
                    foreach ($array_cat_temp as $id_i) {
                        $array_cat[] = $id_i;
                    }
                }
            }
        }
    }

    return array_unique($array_cat);
}

/**
 * Lấy danh sách phòng ban cha, tính cả phòng ban này
 *
 * @param integer $id
 * @return array
 */
function GetlistDepartmentIdParent($id)
{
    global $department_array;

    $array_cat = [];
    $array_cat[] = $id;
    $parentid = $department_array[$id]['parentid'];
    if (!empty($parentid)) {
        foreach ($department_array as $key => $value) {
            if ($value['id'] == $parentid) {
                $array_cat[] = $value['id'];
                $array_cat_temp = GetlistDepartmentIdParent($value['id']);
                foreach ($array_cat_temp as $id_i) {
                    $array_cat[] = $id_i;
                }
            }
        }
    }
    return array_unique($array_cat);
}

/**
 * @param integer $id
 * @return array
 */
function GetUserInfoById($userid)
{
    global $db, $module_data, $db_config;
    return $db->query('SELECT first_name,last_name,username FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid = ' . $userid)->fetch(2);
}

/**
 * @param integer $id
 * @return string
 */
function nv_get_full_department_title($id)
{
    global $department_array;
    if (!isset($department_array[$id])) {
        return 'N/A';
    }
    $title = [];
    $parentid = $id;
    while ($parentid > 0) {
        $array_cat_i = $department_array[$parentid];
        $title[] = $array_cat_i['title'];
        $parentid = $array_cat_i['parentid'];
    }
    if (sizeof($title) > 1) {
        // Bỏ ban giám đốc ở prefix
        array_pop($title);
    }
    krsort($title, SORT_NUMERIC);
    return implode('&gt;', $title);
}

/**
 * @param number $parentid
 * @param number $order
 * @param number $lev
 * @return number
 */
function nv_fix_department_order($parentid = 0, $order = 0, $lev = 0)
{
    global $db, $module_data, $db_config;

    $sql = 'SELECT id, parentid FROM ' . $db_config['prefix'] . '_' . $module_data . '_department WHERE parentid=' . $parentid . ' ORDER BY weight ASC';
    $result = $db->query($sql);
    $array_cat_order = [];
    while ($row = $result->fetch()) {
        $array_cat_order[] = $row['id'];
    }
    $result->closeCursor();
    $weight = 0;
    if ($parentid > 0) {
        ++$lev;
    } else {
        $lev = 0;
    }
    foreach ($array_cat_order as $id_i) {
        ++$order;
        ++$weight;
        $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_department SET weight=' . $weight . ', sort=' . $order . ', lev=' . $lev . ' WHERE id=' . intval($id_i);
        $db->query($sql);
        $order = nv_fix_department_order($id_i, $order, $lev);
    }
    $numsubcat = $weight;
    if ($parentid > 0) {
        $sql = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_department SET numsubcat=' . $numsubcat;
        if ($numsubcat == 0) {
            $sql .= ", subcatid=''";
        } else {
            $sql .= ", subcatid='" . implode(',', $array_cat_order) . "'";
        }
        $sql .= ' WHERE id=' . intval($parentid);
        $db->query($sql);
    }
    return $order;
}
/**
 * Lấy toàn bộ danh sách nhân viên mình có thể giao việc
 * Tất cả nếu phòng ban của mình được phép xem tất cả
 * Nhân viên phòng ban mình hoặc phòng ban con của mình
 * @param array $departments_allowed
 * @return array
 */
function nv_get_staffs_in_departments($departments_allowed, $search = null)
{
    global $db, $module_data, $db_config, $global_config;
    $where = [];
    $where[] = 'tb1.active=1';
    $where[] = 'tb2.still_working=1';
    $where[] = 'tb2.department_id IN(' . implode(',', $departments_allowed) . ')';
    if (!empty($search)) {
        $where[] = '((tb1.first_name LIKE "%' . $db->dblikeescape($search) . '%")' . ' OR ' . '(tb1.last_name LIKE "%' . $db->dblikeescape($search) . '%")' . ' OR ' . '(tb1.username LIKE "%' . $db->dblikeescape($search) . '%"))';
    }

    $db->sqlreset()
        ->select('tb1.userid, tb1.username, tb1.first_name, tb1.last_name, tb1.email, tb2.office_id, tb2.department_id')
        ->from(NV_USERS_GLOBALTABLE . ' tb1')
        ->join('INNER JOIN ' . $db_config['prefix'] . "_" . $module_data . '_human_resources tb2 ON tb1.userid=tb2.userid')
        ->where(implode(' AND ', $where))
        ->order("tb2.weight ASC, " . ($global_config['name_show'] == 0 ? "CONCAT(tb1.first_name,' ',tb1.last_name)" : "CONCAT(tb1.last_name,' ',tb1.first_name)") . " ASC");

    $result = $db->query($db->sql());

    $array_staffs = [];
    while ($row = $result->fetch()) {
        $array_staffs[$row['userid']] = nv_show_name_user($row['first_name'], $row['last_name']);
        if (empty($array_staffs[$row['userid']])) {
            $array_staffs[$row['userid']] = $row['username'];
        } else {
            $array_staffs[$row['userid']] .= ' (' . $row['username'] . ')';
        }
    }
    return $array_staffs;
}

/**
 * @param integer $id
 * @return array
 * @desc Hàm lấy mảng chứa danh sách id danh mục con của 1 danh mục nào đó, tính cả danh mục đó
 */
function GetProjectidInParent($id)
{
    global $global_array_project;

    $array_cat = [];
    $array_cat[] = $id;
    $subcatid = explode(',', $global_array_project[$id]['subcatid']);

    if (! empty($subcatid)) {
        foreach ($subcatid as $id) {
            if ($id > 0) {
                if ($global_array_project[$id]['numsubcat'] == 0) {
                    $array_cat[] = $id;
                } else {
                    $array_cat_temp = GetProjectidInParent($id);
                    foreach ($array_cat_temp as $id_i) {
                        $array_cat[] = $id_i;
                    }
                }
            }
        }
    }

    return array_unique($array_cat);
}

/**
 * @param number $parentid
 * @param number $order
 * @param number $lev
 * @return number
 */
function nv_fix_project_order($parentid = 0, $order = 0, $lev = 0)
{
    global $db, $module_data, $db_config;

    $sql = "SELECT id, parentid FROM " . $db_config['prefix'] . '_' . $module_data . "_taskcat WHERE parentid=" . $parentid . " ORDER BY weight ASC";
    $result = $db->query($sql);
    $array_cat_order = [];
    while ($row = $result->fetch()) {
        $array_cat_order[] = $row['id'];
    }
    $result->closeCursor();
    $weight = 0;
    if ($parentid > 0) {
        ++$lev;
    } else {
        $lev = 0;
    }
    foreach ($array_cat_order as $id_i) {
        ++$order;
        ++$weight;
        $sql = "UPDATE " . $db_config['prefix'] . '_' . $module_data . "_taskcat SET weight=" . $weight . ", sort=" . $order . ", lev=" . $lev . " WHERE id=" . intval($id_i);
        $db->query($sql);
        $order = nv_fix_project_order($id_i, $order, $lev);
    }
    $numsubcat = $weight;
    if ($parentid > 0) {
        $sql = "UPDATE " . $db_config['prefix'] . '_' . $module_data . "_taskcat SET numsubcat=" . $numsubcat;
        if ($numsubcat == 0) {
            $sql .= ", subcatid=''";
        } else {
            $sql .= ", subcatid='" . implode(',', $array_cat_order) . "'";
        }
        $sql .= " WHERE id=" . intval($parentid);
        $db->query($sql);
    }
    return $order;
}

/**
 * Hàm kiểm tra quyền duyệt làm thêm của $leader_info đối với $staff_info
 *
 * @param array $leader_info
 * @param array $staff_info
 * @return boolean
 */
function nvCheckAllowLeaveApproval($leader_info, $staff_info)
{
    global $global_array_offices;

    /**
     * Quyền duyệt
     * - Là lãnh đạo
     * - Duyệt cho nhân viên thuộc phòng ban mình hoặc lãnh đạo mà thứ tự thấp hơn trong bảng office so với mình
     * - Duyệt bất kỳ phòng ban con nào
     */
    if (empty($leader_info['leader'])) {
        return false;
    }
    // Phòng ban con của người duyệt
    $mysub_departments = array_diff(GetDepartmentidInParent($leader_info['department_id']), [$leader_info['department_id']]);

    // Nếu thuộc phòng ban con thì duyệt tất
    if (in_array($staff_info['department_id'], $mysub_departments)) {
        return true;
    }

    // Nhân viên của phòng ban mình
    if ($leader_info['department_id'] == $leader_info['department_id'] and empty($global_array_offices[$staff_info['office_id']]['leader'])) {
        return true;
    }

    // Lãnh đạo phòng ban mình mà thấp chức vụ hơn
    if ($leader_info['department_id'] == $leader_info['department_id'] and $global_array_offices[$leader_info['office_id']]['weight'] < $global_array_offices[$staff_info['office_id']]['weight']) {
        return true;
    }

    return false;
}
