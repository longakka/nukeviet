<?php

/**
 * NukeViet Content Management System
 * @version 4.x
 * @author VINADES.,JSC <contact@vinades.vn>
 * @copyright (C) 2009-2021 VINADES.,JSC. All rights reserved
 * @license GNU/GPL version 2 or any later version
 * @see https://github.com/nukeviet The NukeViet CMS GitHub project
 */

if (!defined('NV_IS_MOD_TIMEKEEPING')) {
    die('Stop!!!');
}

// Kiểm tra có phải lãnh đạo hay không
if (!in_array($user_info['userid'], array_keys($leader_array))) {
    $page_title = $lang_module['info'];
    $url = nv_url_rewrite(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=users&amp;' . NV_OP_VARIABLE . '=login&amp;nv_redirect=' . nv_redirect_encrypt($client_info['selfurl']), true);
    $contents = nv_theme_alert($lang_module['info'], $lang_module['no_permission'], 'info', $url, $lang_module['info_redirect_click'], 5);
    include NV_ROOTDIR . '/includes/header.php';
    echo nv_site_theme($contents);
    include NV_ROOTDIR . '/includes/footer.php';
}

// Thay đổi hoạt động
if ($nv_Request->isset_request('change_status', 'post, get')) {
    $id = $nv_Request->get_int('id', 'post, get', 0);
    $content = 'NO_' . $id;

    $query = 'SELECT status FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE id=' . $id;
    $row = $db->query($query)->fetch();
    if (isset($row['status'])) {
        $status = ($row['status']) ? 0 : 1;
        $query = 'UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_taskcat SET status=' . intval($status) . ' WHERE id=' . $id;
        $db->query($query);
        $content = 'OK_' . $id;
    }
    $nv_Cache->delMod($module_name);
    include NV_ROOTDIR . '/includes/header.php';
    echo $content;
    include NV_ROOTDIR . '/includes/footer.php';
}

// Xóa dự án
if ($nv_Request->isset_request('delete_id', 'get') and $nv_Request->isset_request('delete_checkss', 'get')) {
    $id = $nv_Request->get_int('delete_id', 'get');
    $delete_checkss = $nv_Request->get_string('delete_checkss', 'get');
    if ($id > 0 and $delete_checkss == md5($id . NV_CACHE_PREFIX . $client_info['session_id'])) {
        $db->query('DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat  WHERE id = ' . $db->quote($id));
        $nv_Cache->delMod($module_name);
        nv_insert_logs(NV_LANG_DATA, $module_name, 'Delete Project', 'ID: ' . $id, $user_info['userid']);
        nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    }
}

$row = [];
$error = [];
$row['id'] = $nv_Request->get_int('id', 'post,get', 0);
$parentid =  $nv_Request->get_int('parentid', 'get', 0);

$in_department = $department_array[$leader_array[$user_info['userid']]['department_id']];

// Lấy hết nhân sự trong phòng ban này và phòng ban con
$_sql = 'SELECT t1.userid, t2.username, t2.first_name, t2.last_name
FROM ' . $db_config['prefix'] . '_' . $module_data . '_human_resources as t1
INNER JOIN ' . NV_USERS_GLOBALTABLE . ' as t2 ON t1.userid = t2.userid
WHERE department_id IN (' . implode(',', GetDepartmentidInParent($in_department['id'])) . ') AND t1.still_working=1
ORDER BY ' . ($global_config['name_show'] == 0 ? "CONCAT(t2.first_name,' ',t2.last_name)" : "CONCAT(t2.last_name,' ',t2.first_name)") . " ASC";
$_query = $db->query($_sql);
while ($_row = $_query->fetch()) {
    $array_user_department[$_row['userid']] = nv_show_name_user($_row['first_name'], $_row['last_name']) . ' (' . $_row['username'] . ')';
}

if ($nv_Request->isset_request('submit', 'post')) {
    $row['title'] = $nv_Request->get_title('title', 'post', '');
    $row['alias'] = $nv_Request->get_title('alias', 'post', '');
    $row['alias'] = (empty($row['alias'])) ? change_alias($row['title']) : change_alias($row['alias']);
    $row['parentid'] = $nv_Request->get_int('parentid', 'post', 0);
    $row['status'] = $nv_Request->get_int('status', 'post', 1);
    $row['description'] = $nv_Request->get_title('description', 'post', '');
    $row['users_assigned'] = $nv_Request->get_typed_array('users_assigned', 'post', 'int', []);
    $row['users_assigned'] = implode(',', $row['users_assigned']);
    $row['department'] = $in_department['id'];
    $row['project_public'] = $nv_Request->get_int('project_public', 'post', 0);
    $row['project_accounting'] = $nv_Request->get_int('project_accounting', 'post', 0);

    if (empty($row['title'])) {
        $error[] = $lang_module['error_required_title'];
    }

    if (empty($error)) {
        try {
            if (empty($row['id'])) {
                $stmt = $db->prepare('INSERT INTO ' . $db_config['prefix'] . '_' . $module_data . '_taskcat (
                    title, alias, parentid, status, description, department, users_assigned, project_public, project_accounting
                ) VALUES (
                    :title, :alias, :parentid, :status, :description,
                    :department,:users_assigned, :project_public, :project_accounting
                )');
            } else {
                $stmt = $db->prepare('UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_taskcat SET
                    title = :title, alias = :alias, parentid = :parentid,
                    status = :status, description = :description,
                    department = :department, users_assigned = :users_assigned,
                    project_public = :project_public, project_accounting = :project_accounting
                WHERE id=' . $row['id']);
            }

            // Nếu dự án là dự án con của một dự án khác thì dự án đó bắt buộc là dự án hạch toán
            $row['project_accounting'] = !empty($row['project_accounting']) || !empty($row['parentid']) ? 1 : 0;

            $stmt->bindParam(':title', $row['title'], PDO::PARAM_STR);
            $stmt->bindParam(':alias', $row['alias'], PDO::PARAM_STR);
            $stmt->bindParam(':parentid', $row['parentid'], PDO::PARAM_INT);
            $stmt->bindParam(':status', $row['status'], PDO::PARAM_INT);
            $stmt->bindParam(':description', $row['description'], PDO::PARAM_STR);
            $stmt->bindParam(':department', $row['department'], PDO::PARAM_INT);
            $stmt->bindParam(':users_assigned', $row['users_assigned'], PDO::PARAM_STR);
            $stmt->bindParam(':project_public', $row['project_public'], PDO::PARAM_INT);
            $stmt->bindParam(':project_accounting', $row['project_accounting'], PDO::PARAM_INT);

            $exc = $stmt->execute();
            if ($exc) {
                nv_fix_project_order();
                $nv_Cache->delMod($module_name);
                if (empty($row['id'])) {
                    nv_insert_logs(NV_LANG_DATA, $module_name, 'Add Project', ' ', $user_info['userid']);
                } else {
                    nv_insert_logs(NV_LANG_DATA, $module_name, 'Edit Project', 'ID: ' . $row['id'], $user_info['userid']);
                }
                nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            die($e->getMessage()); // Remove this line after checks finished
        }
    }
} elseif ($row['id'] > 0) {
    $row = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE id=' . $row['id'])->fetch();
    $array_in_cat = GetProjectidInParent($row['id']);
    if (empty($row)) {
        nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    }
} else {
    $row['id'] = 0;
    $row['title'] = '';
    $row['alias'] = '';
    $row['parentid'] = 0;
    $row['status'] = 1;
    $row['description'] = '';
    $row['department'] = 0;
    $row['project_public'] = 0;
    $row['project_accounting'] = 0;
    $array_in_cat = [];
}


$q = $nv_Request->get_title('q', 'post,get');
$search_status = $nv_Request->get_absint('s', 'post,get', 1);
if ($search_status < 0 or $search_status > 2) {
    $search_status = 1;
}

// Fetch Limit
$show_view = false;
if (!$nv_Request->isset_request('id', 'post,get')) {
    $show_view = true;
    $per_page = 20;
    $page = $nv_Request->get_int('page', 'post,get', 1);
    $db->sqlreset()
        ->select('COUNT(*)')
        ->from('' . $db_config['prefix'] . '_' . $module_data . '_taskcat');

    $where = 'department = ' . $in_department['id'] . ' AND parentid = ' . $db->quote($parentid);

    if (!empty($q)) {
        $where .= ' AND title LIKE :q_title';
    }
    if ($search_status == 1) {
        // Dự án đang thực hiện
        $where .= ' AND status=1';
    } elseif ($search_status == 2) {
        // Dự án đã lưu trữ
        $where .= ' AND status=0';
    }
    $db->where($where);

    $sth = $db->prepare($db->sql());

    if (!empty($q)) {
        $sth->bindValue(':q_title', '%' . $q . '%');
    }
    $sth->execute();
    $num_items = $sth->fetchColumn();

    $db->select('*')
        ->order('id DESC')
        ->limit($per_page)
        ->offset(($page - 1) * $per_page);
    $sth = $db->prepare($db->sql());

    if (!empty($q)) {
        $sth->bindValue(':q_title', '%' . $q . '%');
    }
    $sth->execute();
}

$base_url_origin = $base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;

$xtpl = new XTemplate('project.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
$xtpl->assign('NV_LANG_DATA', NV_LANG_DATA);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
$xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('MODULE_UPLOAD', $module_upload);
$xtpl->assign('NV_ASSETS_DIR', NV_ASSETS_DIR);
$xtpl->assign('OP', $op);
$xtpl->assign('ROW', $row);
$xtpl->assign('base_url_origin', $base_url_origin);
$xtpl->assign('DEPARTMENT_NAME', $in_department['title']);
$xtpl->assign('DEPARTMENT_ID', $in_department['id']);

// Xuất thanh NAV
if ($parentid > 0) {
    $parentid_i = $parentid;
    $array_cat_title = [];
    $stt = 0;
    while ($parentid_i > 0) {
        $array_cat_title[] = [
            'active' => ($stt++ == 0) ? true : false,
            'link' => $base_url_origin . "&amp;parentid=" . $parentid_i,
            'title' => $global_array_project[$parentid_i]['title']
        ];
        $parentid_i = $global_array_project[$parentid_i]['parentid'];
    }
    $array_cat_title[] = [
        'active' => false,
        'link' => $base_url_origin,
        'title' => $lang_module['project_main']
    ];
    krsort($array_cat_title, SORT_NUMERIC);

    foreach ($array_cat_title as $cat) {
        $xtpl->assign('CAT', $cat);
        if ($cat['active']) {
            $xtpl->parse('main.view.cat_title.active');
        } else {
            $xtpl->parse('main.view.cat_title.loop');
        }
    }
    $xtpl->parse('main.view.cat_title');
}
// Form tìm kiếm
if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
}

$xtpl->assign('Q', $q);
$xtpl->assign('project_public_checked', empty($row['project_public']) ? '' : 'checked');
$xtpl->assign('project_accounting_checked', empty($row['project_accounting']) ? '' : 'checked');

if (!empty($array_user_department)) {
    $users_assigned = !empty($row['users_assigned']) ? $row['users_assigned'] : '';
    foreach ($array_user_department as $_id => $_name) {
        $xtpl->assign('users_assigned', [
            'userid' => $_id,
            'fullname' => $_name
        ]);
        $xtpl->parse('main.users_assigned');
    }
}

if (!empty($users_assigned)) {
    foreach (explode(',', $users_assigned) as $_id) {
        $xtpl->assign('user_assigned', $_id);
        $xtpl->parse('main.users_assigned_list');
    }
}
// Xác định danh sách thể loại để build ra

$array_cat_list = [];
$array_cat_list[0] = $lang_module['project_main'];

foreach ($global_array_project as $id_i => $array_value) {
    $lev_i = $array_value['lev'];
    $xtitle_i = '';
    if ($lev_i > 0) {
        $xtitle_i .= '&nbsp;&nbsp;&nbsp;|';
        for ($i = 1; $i <= $lev_i; ++$i) {
            $xtitle_i .= '---';
        }
        $xtitle_i .= '>&nbsp;';
    }
    $xtitle_i .= $array_value['title'];
    $array_cat_list[$id_i] = $xtitle_i;
}


// Xác định danh sách các thể loại cha để build ra
$cat_listsub = [];
foreach ($array_cat_list as $id_i => $title_i) {
    if (!in_array($id_i, $array_in_cat)) {
        $cat_listsub[] = [
            'key' => $id_i,
            'title' => $title_i,
            'selected' => ($id_i == $row['parentid'] || $id_i == $parentid) ? ' selected="selected"' : ''
        ];
    }
}
// Xuất parentid
foreach ($cat_listsub as $_row) {
    $xtpl->assign('PROJECT_PARENT', $_row);
    $xtpl->parse('main.project_parent');
}

if ($show_view) {
    if (!empty($q)) {
        $base_url .= '&amp;q=' . urlencode($q);
    }
    $base_url .= '&amp;s=' . $search_status;

    $generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
    if (!empty($generate_page)) {
        $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
        $xtpl->parse('main.view.generate_page');
    }

    $number = $page > 1 ? ($per_page * ($page - 1)) + 1 : 1;
    while ($view = $sth->fetch()) {
        $xtpl->assign('CHECK', $view['status'] == 1 ? 'checked' : '');
        $view['project_accounting_check'] = $view['project_accounting'] == 1 ? 'checked' : '';

        if (!empty($view['project_public'])) {
            $view['list_users_assigned'] = $lang_module['project_public'];
        } elseif (!empty($view['users_assigned']) and (!empty($array_user_department))) {
            $view['users_assigned'] = explode(',', $view['users_assigned']);
            $view['list_users_assigned'] = [];
            foreach ($view['users_assigned'] as $_id) {
                if (isset($array_user_department[$_id])) {
                    $view['list_users_assigned'][] = $array_user_department[$_id];
                }
            }
            $view['list_users_assigned'] = implode(', ', $view['list_users_assigned']);
        }

        $view['department'] = $department_array[$view['department']]['title'];
        $view['link_edit'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $view['id'];
        $view['link_statistics'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=project-statistics&amp;id=' . $view['id'];
        $view['link_delete'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);

        $view['link_childrent'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;parentid=' . $view['id'];

        $xtpl->assign('VIEW', $view);
        $xtpl->parse('main.view.loop');
    }

    for ($i = 0; $i < 3; $i++) {
        $xtpl->assign('SEARCH_STATUS', [
            'key' => $i,
            'title' => $lang_module['project_status_' . $i],
            'selected' => $i == $search_status ? ' selected="selected"' : '',
        ]);
        $xtpl->parse('main.view.search_status');
    }

    $xtpl->parse('main.view');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['project'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
