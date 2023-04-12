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

use NukeViet\Module\timekeeping\Shared\TaskAssign;

$page_title = $lang_module['work_list'];
$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$array_mod_title[] = [
    'catid' => 0,
    'title' => $page_title,
    'link' => $base_url
];

// Lấy các dự án đang hoạt động mà mình đang join
$array_taskcat_id_timekeeping = [];
$_sql = 'SELECT id, title FROM ' . $db_config['prefix'] . '_' . $module_data . '_taskcat WHERE status = 1 AND (
    project_public=1 OR FIND_IN_SET(' . $user_info['userid'] . ', users_assigned)
)';
$_query = $db->query($_sql);
while ($_row = $_query->fetch()) {
    $array_taskcat_id_timekeeping[$_row['id']] = $_row;
}

// Xóa báo cáo công việc
if ($nv_Request->isset_request('delete_id', 'get') and $nv_Request->isset_request('delete_checkss', 'get')) {
    $id = $nv_Request->get_int('delete_id', 'get');
    $delete_checkss = $nv_Request->get_string('delete_checkss', 'get');
    if ($id > 0 and $delete_checkss == md5($id . NV_CACHE_PREFIX . $client_info['session_id'])) {
        $db->query('DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_task WHERE id = ' . $db->quote($id));
        $db->query('DELETE FROM ' . $db_config['prefix'] . '_' . $module_data . '_task_change WHERE task_id = ' . $db->quote($id));
        $nv_Cache->delMod($module_name);
        nv_insert_logs(NV_LANG_DATA, $module_name, 'Delete Work', 'ID: ' . $id, $user_info['userid']);
        nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    }
}

// Các phòng ban được quản lý
$departments_parent = GetlistDepartmentIdParent($HR->user_info['department_id']);

// Load lịch làm việc cố định
if (isset($_POST['loadschedule']) and $nv_Request->get_title('loadschedule', 'post', '') == NV_CHECK_SESSION) {
    $respon = [
        'message' => '',
        'rows' => [],
    ];

    $time = $nv_Request->get_string('time', 'post', '');
    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $time, $m)) {
        $time = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    } else {
        $time = 0;
    }
    if (empty($time)) {
        $respon['message'] = 'Wrong time!!!';
        nv_jsonOutput($respon);
    }

    // Lấy lịch làm việc
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE
    deleted=0 AND status=1 AND department_id IN(" . implode(',', $departments_parent) . ") AND (
        apply_allsubs=1 OR FIND_IN_SET(" . $HR->user_info['department_id'] . ", apply_departments) OR
        department_id=" . $HR->user_info['department_id'] . "
    ) AND FIND_IN_SET(" . date('N', $time) . ", apply_days) ORDER BY title ASC";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $row['work_content'] = nv_unhtmlspecialchars(nv_br2nl($row['work_content']));
        $row['work_note'] = nv_unhtmlspecialchars(nv_br2nl($row['work_note']));
        $respon['rows'][] = $row;
    }

    if (empty($respon['rows'])) {
        $respon['message'] = sprintf($lang_module['work_type2_empty'], date('d/m/Y', $time));
    }

    nv_jsonOutput($respon);
}

// Load công việc được giao
if (isset($_POST['loadaswork']) and $nv_Request->get_title('loadaswork', 'post', '') == NV_CHECK_SESSION) {
    $respon = [
        'message' => '',
        'rows' => [],
    ];

    $time = $nv_Request->get_string('time', 'post', '');
    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $time, $m)) {
        $time = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    } else {
        $time = 0;
    }
    if (empty($time)) {
        $respon['message'] = 'Wrong time!!!';
        nv_jsonOutput($respon);
    }

    // Lấy việc được giao
    $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign WHERE
    deleted=0 AND userid=" . $user_info['userid'] . " AND to_time>" . ($time - (30 * 86400)) . " ORDER BY title ASC";
    $result = $db->query($sql);

    $array = [];
    while ($row = $result->fetch()) {
        $row['work_content'] = nv_unhtmlspecialchars(nv_br2nl($row['work_content']));
        $row['work_note'] = nv_unhtmlspecialchars(nv_br2nl($row['work_note']));

        if (empty($row['worked_time'])) {
            // Chưa làm
            $row['worked_level'] = 1;
            $row['worked_note'] = sprintf($lang_module['work_ass_note1'], $row['work_time']);
        } elseif ($row['worked_time'] < $row['work_time']) {
            // Còn giờ tính công
            $row['worked_level'] = 2;
            $row['worked_note'] = sprintf($lang_module['work_ass_note2'], $row['worked_time'], $row['work_time'] - $row['worked_time']);
        } elseif ($row['worked_time'] == $row['work_time']) {
            // Làm vừa đủ
            $row['worked_level'] = 3;
            $row['worked_note'] = sprintf($lang_module['work_ass_note3'], $row['work_time']);
        } else {
            // Quá giờ
            $row['worked_level'] = 4;
            $row['worked_note'] = sprintf($lang_module['work_ass_note4'], $row['worked_time'], $row['work_time'], $row['worked_time'] - $row['work_time']);
        }
        /*
         * Nhân viên không làm việc dạng giao việc thì bỏ cảnh báo
         * Trưởng phòng, trưởng nhóm, trưởng bộ phận, lãnh đạo cũng bỏ qua
         */
        if (!empty($HR->user_info['no_aswork']) or !empty($HR->user_info['leader']) or !empty($HR->user_info['manager'])) {
            $row['worked_note'] = '';
        }

        // Thêm vào tiêu đề làm bao nhiêu giờ/bao nhiêu giờ
        if (!empty($row['worked_time'])) {
            $row['title'] .= '  (' . $row['worked_time'] . '/' . $row['work_time'] . 'h)';
        }

        $array[$row['id']] = $row;
    }

    foreach ($array as $row) {
        $respon['rows'][] = $row;
    }

    if (empty($respon['rows'])) {
        $respon['message'] = sprintf($lang_module['work_type1_empty'], date('d/m/Y', $time));
    }

    nv_jsonOutput($respon);
}

$row = [];
$error = [];

$row['id'] = $nv_Request->get_int('id', 'post,get', 0);
$is_copy = $nv_Request->get_int('copy', 'post,get', 0);

if ($row['id'] > 0) {
    $row = $db->query('SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_task WHERE id=' . $row['id'] . " AND userid_assigned=" . $user_info['userid'])->fetch();
    if (empty($row)) {
        nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
    }
    $row_old = $row;
    if ($is_copy) {
        $row['id'] = 0;
        $row['time_create'] = 0;
        $row_old = [];
    }
} else {
    $row['id'] = 0;
    $row['taskcat_id'] = 0;
    $row['title'] = '';
    $row['status'] = 0;
    $row['link'] = '';
    $row['content'] = '';
    $row['description'] = '';
    $row['time_create'] = 0;
    $row['work_time'] = 0;
    $row['over_time'] = 0;
    $row['work_type'] = 0;
    $row['assigned_task'] = 0;
    $row['schedule_task'] = 0;
    $row_old = [];
}

// Submit thêm báo cáo
if ($nv_Request->isset_request('submit', 'post')) {
    $row['taskcat_id'] = $nv_Request->get_int('taskcat_id', 'post', 0);
    $row['userid_assigned'] = $user_info['userid'];
    $row['title'] = $nv_Request->get_title('title', 'post', '');
    $row['status'] = $nv_Request->get_int('status', 'post');
    $row['link'] = $nv_Request->get_title('link', 'post', '');
    $row['content'] = nv_nl2br($nv_Request->get_textarea('content', '', NV_ALLOWED_HTML_TAGS));
    $row['description'] = nv_nl2br($nv_Request->get_textarea('description', '', NV_ALLOWED_HTML_TAGS));

    $row['work_type'] = $nv_Request->get_absint('work_type', 'post', 0);
    $row['assigned_task'] = $nv_Request->get_absint('assigned_task', 'post', 0);
    $row['schedule_task'] = $nv_Request->get_absint('schedule_task', 'post', 0);

    if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/', $nv_Request->get_string('time_create', 'post'), $m)) {
        $row['time_create'] = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    } else {
        $row['time_create'] = 0;
    }

    $row['work_time'] = $nv_Request->get_float('work_time', 'post', 0);
    $row['over_time'] = $nv_Request->get_float('over_time', 'post', 0);

    // Kiểm tra loại công việc
    if ($row['work_type'] < 0 or $row['work_type'] > 2) {
        $error[] = 'Error work_type';
    }
    // Kiểm tra việc theo lịch cố định
    if ($row['work_type'] == 2) {
        // Lấy lịch làm việc
        $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_work_schedule WHERE
        deleted=0 AND status=1 AND department_id IN(" . implode(',', $departments_parent) . ") AND (
            apply_allsubs=1 OR FIND_IN_SET(" . $HR->user_info['department_id'] . ", apply_departments) OR
            department_id=" . $HR->user_info['department_id'] . "
        ) AND FIND_IN_SET(" . date('N', $row['time_create']) . ", apply_days) AND id=" . $row['schedule_task'];
        $schedule_task = $db->query($sql)->fetch() ?: [];

        if (empty($schedule_task)) {
            $error[] = $lang_module['work_type2_error'];
        } else {
            //$row['title'] = $schedule_task['title'];
            //$row['link'] = $schedule_task['link'];
            //$row['content'] = $schedule_task['work_content'];
            //$row['work_time'] = $schedule_task['work_time'];
            //$row['over_time'] = 0;
        }

        /*
        // Lịch làm việc cố định thì 1 ngày ghi được 1 lần
        $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task WHERE time_create=" . $row['time_create'] . "
        AND userid_assigned=" . $user_info['userid'] . " AND schedule_task=" . $row['schedule_task'];
        $exists_work = $db->query($sql)->fetch();
        if ($exists_work) {
            $error[] = $lang_module['work_type2_exists'];
        }
        */

        // Tổng số công không được quá lịch được giao
        $sql = "SELECT SUM(accepted_time) FROM " . $db_config['prefix'] . "_" . $module_data . "_task
        WHERE time_create=" . $row['time_create'] . "
        AND userid_assigned=" . $user_info['userid'] . " AND schedule_task=" . $row['schedule_task'];
        if (!empty($row['id'])) {
            $sql .= " AND id!=" . $row['id'];
        }
        $exists_work = $db->query($sql)->fetchColumn();

        if (($exists_work + $row['work_time'] + $row['over_time']) > $schedule_task['work_time']) {
            $error[] = sprintf($lang_module['work_type2_error1'], $schedule_task['work_time']);
        }
    }
    // Kiểm tra việc dạng được giao
    if ($row['work_type'] == 1) {
        $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign WHERE
        deleted=0 AND userid=" . $user_info['userid'] . " AND id=" . $row['assigned_task'];
        $assigned_task = $db->query($sql)->fetch() ?: [];

        if (empty($assigned_task)) {
            $error[] = $lang_module['work_type1_error'];
        } else {
            $row['title'] = $assigned_task['title'];
            $row['link'] = $assigned_task['link'];
            $row['content'] = empty($row['content']) ? $assigned_task['work_content'] : $row['content'];
        }
    }

    // Check các lỗi khác
    if (empty($row['taskcat_id'])) {
        $error[] = $lang_module['error_required_taskcat_id'];
    }
    if (empty($row['title'])) {
        $error[] = $lang_module['error_required_title'];
    }
    if (empty($row['time_create'])) {
        $error[] = $lang_module['error_required_time_create'];
    }
    if (empty($row['content'])) {
        $error[] = $lang_module['work_error_content'];
    }
    if (($row['work_time'] < 0) or ($row['over_time'] < 0)) {
        $error[] = $lang_module['error_time'];
    }
    if ($row['work_time'] + $row['over_time'] == 0) {
        $error[] = $lang_module['error_no_time'];
    }

    if (empty($error)) {
        // Tính toán số công được giao
        $department = $department_array[$HR->user_info['department_id']] ?? [];
        if (empty($department) or !isset($department['assign_work'])) {
            nv_htmlOutput('Department error!!!');
        }
        $no_assign_work = (empty($department['assign_work']) or !empty($HR->user_info['leader']) or !empty($HR->user_info['manager']) or !empty($HR->user_info['no_aswork']));

        if (!empty($row['id']) and $row['set_time'] > 0) {
            // Sửa việc, nếu việc được ấn định thời gian thì không thay đổi thời gian nữa
            $row['accepted_time'] = $row['set_time'];
        } else {
            if ($no_assign_work or $row['work_type'] == 2) {
                /*
                 * Phòng ban không giao việc thì tính công tự nhập
                 * Trưởng nhóm, trưởng bộ phận thì tính công tự nhập
                 * Trưởng phòng, lãnh đạo thì tính công tự nhập
                 * Làm theo lịch cố định thì tính công theo lịch đó
                 * Nhân viên được cấu hình bỏ qua giao việc cũng tính công tự nhập
                 */
                $row['accepted_time'] = $row['work_time'] + $row['over_time'];
            } elseif ($row['work_type'] == 1) {
                // Làm theo việc được giao thì tính công theo số công còn lại của lệnh giao việc đó.
                $remaining_time = $assigned_task['work_time'] - $assigned_task['accepted_time'];
                if (!empty($row_old['accepted_time'])) {
                    // Sửa giao việc thì trả lại số công cũ đã trừ đi
                    $remaining_time += $row_old['accepted_time'];
                }
                if ($remaining_time <= 0) {
                    // Làm quá giờ được giao thì không còn tính công
                    $row['accepted_time'] = 0;
                } elseif ($remaining_time > ($row['work_time'] + $row['over_time'])) {
                    // Còn hơn giờ làm + làm thêm
                    $row['accepted_time'] = $row['work_time'] + $row['over_time'];
                } else {
                    $row['accepted_time'] = $remaining_time;
                }
            } else {
                // Không được tính công
                $row['accepted_time'] = 0;
            }
        }

        try {
            if (empty($row['id'])) {
                $stmt = $db->prepare('INSERT INTO ' . $db_config['prefix'] . '_' . $module_data . '_task (
                    taskcat_id, title, userid_assigned, status,
                    link, content, description, time_create, work_time, over_time,
                    work_type, assigned_task, schedule_task, accepted_time
                ) VALUES (
                    :taskcat_id, :title, :userid_assigned, :status, :link,
                    :content, :description, :time_create, ' . $row['work_time'] . ',' . $row['over_time'] . ',
                    :work_type, :assigned_task, :schedule_task, :accepted_time
                )');
                $stmt->bindParam(':userid_assigned', $row['userid_assigned'], PDO::PARAM_INT);
            } else {
                $stmt = $db->prepare('UPDATE ' . $db_config['prefix'] . '_' . $module_data . '_task SET
                    taskcat_id = :taskcat_id, title = :title, status = :status,
                    link = :link, content = :content, description = :description,
                    time_create = :time_create, work_time = ' . $row['work_time'] . ',
                    over_time = ' . $row['over_time'] . ',
                    work_type=:work_type, assigned_task=:assigned_task, schedule_task=:schedule_task,
                    accepted_time=:accepted_time
                WHERE id=' . $row['id']);
            }
            $stmt->bindParam(':taskcat_id', $row['taskcat_id'], PDO::PARAM_INT);
            $stmt->bindParam(':title', $row['title'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $row['status'], PDO::PARAM_INT);
            $stmt->bindParam(':link', $row['link'], PDO::PARAM_STR);
            $stmt->bindParam(':content', $row['content'], PDO::PARAM_STR, strlen($row['content']));
            $stmt->bindParam(':description', $row['description'], PDO::PARAM_STR, strlen($row['description']));
            $stmt->bindParam(':time_create', $row['time_create'], PDO::PARAM_INT);
            $stmt->bindParam(':work_type', $row['work_type'], PDO::PARAM_INT);
            $stmt->bindParam(':assigned_task', $row['assigned_task'], PDO::PARAM_INT);
            $stmt->bindParam(':schedule_task', $row['schedule_task'], PDO::PARAM_INT);
            $stmt->bindParam(':accepted_time', $row['accepted_time'], PDO::PARAM_INT);

            $exc = $stmt->execute();
            if ($exc) {
                if ($row['work_type'] == 1 and !empty($row['assigned_task'])) {
                    TaskAssign::syncWorkedTime($row['assigned_task'], !$no_assign_work);
                }
                if (!empty($row_old['assigned_task']) and $row_old['assigned_task'] != $row['assigned_task']) {
                    TaskAssign::syncWorkedTime($row_old['assigned_task'], !$no_assign_work);
                }
                $nv_Cache->delMod($module_name);
                if (empty($row['id'])) {
                    nv_insert_logs(NV_LANG_DATA, $module_name, 'Add Work', ' ', $user_info['userid']);
                } else {
                    nv_insert_logs(NV_LANG_DATA, $module_name, 'Edit Work', 'ID: ' . $row['id'], $user_info['userid']);
                }
                nv_redirect_location(NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op);
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            $error[] = $e->getMessage();
        }
    }
}

if (empty($row['time_create'])) {
    $row['time_create'] = date('d/m/Y', NV_CURRENTTIME);
} else {
    $row['time_create'] = date('d/m/Y', $row['time_create']);
}

$row['description'] = nv_htmlspecialchars(nv_br2nl($row['description']));
$row['content'] = nv_htmlspecialchars(nv_br2nl($row['content']));

// Tìm kiếm công việc
$search = [];

$search['q'] = $nv_Request->get_title('q', 'get', '');
$search['taskcat_id'] = $nv_Request->get_absint('p', 'get', 0);
$search['status'] = $nv_Request->get_absint('s', 'get', 0);

$init_from = mktime(0, 0, 0, intval(date('n')), 1, intval(date('Y')));
$init_to = mktime(23, 59, 59, intval(date('n')), cal_days_in_month(CAL_GREGORIAN, intval(date('n')), intval(date('Y'))), intval(date('Y')));
$init_range = date('d-m-Y', $init_from) . ' - ' . date('d-m-Y', $init_to);

$search['range'] = $nv_Request->get_string('r', 'get', $init_range);
$search['from'] = 0;
$search['to'] = 0;

// Kiểm tra dữ liệu tìm kiếm
if (preg_match('/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})[\s]*\-[\s]*([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/i', $search['range'], $m)) {
    $m = array_map('intval', $m);

    $search['from'] = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
    $search['to'] = mktime(23, 59, 59, $m[5], $m[4], $m[6]);
    if ($search['from'] >= $search['to']) {
        $search['from'] = 0;
        $search['to'] = 0;
        $search['range'] = '';
    }
} else {
    $search['range'] = '';
}
if ($search['taskcat_id'] and !isset($array_taskcat_id_timekeeping[$search['taskcat_id']])) {
    $search['taskcat_id'] = 0;
}
if ($search['status'] and !isset($global_array_status[$search['status']])) {
    $search['status'] = 0;
}

$page = $nv_Request->get_absint('page', 'get', 1);
$per_page = 30;
$is_search = false;
$num_items = 0;
$is_genpage = false;

// Lấy các công việc
$show_view = false;
if (!$nv_Request->isset_request('id', 'post,get')) {
    $show_view = true;

    $db->sqlreset()
        ->select('COUNT(id)')
        ->from($db_config['prefix'] . '_' . $module_data . '_task');

    $where = [];
    $where[] = 'userid_assigned=' . $user_info['userid'];

    if ($search['from'] != $init_from or $search['to'] != $init_to) {
        $is_search = true;
    }
    if ($search['from']) {
        $where[] = 'time_create>=' . $search['from'];
    }
    if ($search['to']) {
        $where[] = 'time_create<=' . $search['to'];
    }
    if (!empty($search['taskcat_id'])) {
        $where[] = 'taskcat_id=' . $search['taskcat_id'];
        $is_search = true;
        $is_genpage = true;
    }
    if (!empty($search['status'])) {
        $where[] = 'status=' . $search['status'];
        $is_search = true;
        $is_genpage = true;
    }
    if (!empty($search['q'])) {
        $where[] = "title LIKE '%" . $db->dblikeescape($search['q']) . "%'";
        $is_search = true;
        $is_genpage = true;
    }
    // Chỉ tìm từ ngày đến ngày mà không quá 40 ngày thì list hết
    if (!$is_genpage and ($search['to'] - $search['from']) > (40 * 86400)) {
        $is_genpage = true;
    }

    $db->where(implode(' AND ', $where));

    if ($is_genpage) {
        // Nếu tìm kiếm thì có phân trang
        $num_items = $db->query($db->sql())->fetchColumn();
        $db->select('*')
        ->limit($per_page)
        ->offset(($page - 1) * $per_page);
    } else {
        // Mặc định không phân trang
        $db->select('*');
    }
    $db->order('time_create DESC, id DESC');
    $result = $db->query($db->sql());
}

$xtpl = new XTemplate('work.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
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

if (!$global_config['rewrite_enable']) {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php');
    $xtpl->parse('main.no_rewrite');
} else {
    $xtpl->assign('FORM_ACTION', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op);
}

$xtpl->assign('SEARCH', $search);

// Xuất dự án
foreach ($array_taskcat_id_timekeeping as $value) {
    $xtpl->assign('OPTION', array(
        'key' => $value['id'],
        'title' => $value['title'],
        'selected' => ($value['id'] == $row['taskcat_id']) ? ' selected="selected"' : ''
    ));
    $xtpl->parse('main.select_taskcat_id');

    $xtpl->assign('PROJECT', array(
        'key' => $value['id'],
        'title' => $value['title'],
        'selected' => ($value['id'] == $search['taskcat_id']) ? ' selected="selected"' : ''
    ));
    $xtpl->parse('main.search_project');
}

foreach ($global_array_status as $key => $title) {
    $xtpl->assign('OPTION', array(
        'key' => $key,
        'title' => $title,
        'selected' => ($key == $row['status']) ? ' selected="selected"' : ''
    ));
    $xtpl->parse('main.select_status');
    $xtpl->assign('STATUS', array(
        'key' => $key,
        'title' => $title,
        'selected' => ($key == $search['status']) ? ' selected="selected"' : ''
    ));
    $xtpl->parse('main.search_status');
}

$array = [];

if ($show_view) {
    if ($is_search) {
        $base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
        if (!empty($search['q'])) {
            $base_url .= '&amp;q=' . urlencode($search['q']);
        }
        if (!empty($search['taskcat_id'])) {
            $base_url .= '&amp;p=' . $search['taskcat_id'];
        }
        if (!empty($search['status'])) {
            $base_url .= '&amp;s=' . $search['status'];
        }
        if (!empty($search['range'])) {
            $base_url .= '&amp;r=' . urlencode($search['range']);
        }

        if ($is_genpage) {
            $generate_page = nv_generate_page($base_url, $num_items, $per_page, $page);
            if (!empty($generate_page)) {
                $xtpl->assign('NV_GENERATE_PAGE', $generate_page);
                $xtpl->parse('main.view.generate_page');
            }
        }
    }

    // Lấy công việc nhóm theo ngày
    $array = [];
    while ($_row = $result->fetch()) {
        $key = date('d/m/Y', $_row['time_create']);
        $array[$key][$_row['id']] = $_row;
    }

    $work_time = $over_time = $accepted_time = [];

    // Lặp công việc để xuất ra
    $stt = sizeof($array);
    foreach ($array as $view_day => $view_array) {
        $first = true;
        $rowspan = sizeof($view_array);
        $xtpl->assign('TT', $stt);
        $xtpl->assign('ROWSPAN', $rowspan);
        $xtpl->assign('DAY', $view_day);

        foreach ($view_array as $view) {
            // Xử lý cột đầu tiên
            if ($first) {
                $first = false;
                // Hiển thị cột TT khi không tìm kiếm
                if (!$is_genpage) {
                    if ($rowspan > 1) {
                        $xtpl->parse('main.view.loop_day.loop.col_number.rowspan');
                    }
                    $xtpl->parse('main.view.loop_day.loop.col_number');
                }

                // Hiển thị cột ngày tháng ở dòng đầu
                if ($rowspan > 1) {
                    $xtpl->parse('main.view.loop_day.loop.col_day.rowspan');
                }
                $xtpl->parse('main.view.loop_day.loop.col_day');
            }

            // Xử lý data
            $view['content'] = nv_nl2br($view['content']);
            $view['description'] = nv_nl2br($view['description']);
            $view['time_create'] = (empty($view['time_create'])) ? '' : nv_date('d/m/Y', $view['time_create']);

            $work_time[$view['taskcat_id']] = empty($work_time[$view['taskcat_id']]) ? $view['work_time'] : ($work_time[$view['taskcat_id']] + $view['work_time']);
            $over_time[$view['taskcat_id']] = empty($over_time[$view['taskcat_id']]) ? $view['over_time'] : ($over_time[$view['taskcat_id']] + $view['over_time']);
            $accepted_time[$view['taskcat_id']] = empty($accepted_time[$view['taskcat_id']]) ? $view['accepted_time'] : ($accepted_time[$view['taskcat_id']] + $view['accepted_time']);

            $view['work_time_day'] = number_format(($view['work_time'] / 8), 2, '.', ',');
            $view['work_time_day'] = rtrim($view['work_time_day'], '0');
            $view['work_time_day'] = rtrim($view['work_time_day'], '.');

            $view['over_time_day'] = number_format(($view['over_time'] / 8), 2, '.', ',');
            $view['over_time_day'] = rtrim($view['over_time_day'], '0');
            $view['over_time_day'] = rtrim($view['over_time_day'], '.');

            $view['accepted_time_day'] = number_format(($view['accepted_time'] / 8), 2, '.', ',');
            $view['accepted_time_day'] = rtrim($view['accepted_time_day'], '0');
            $view['accepted_time_day'] = rtrim($view['accepted_time_day'], '.');

            $view['taskcat_id'] = empty($array_taskcat_id_timekeeping[$view['taskcat_id']]['title']) ? 'N/A' : $array_taskcat_id_timekeeping[$view['taskcat_id']]['title'];
            $view['status_text'] = $global_array_status[$view['status']];
            $view['link_copy'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $view['id'] . '&amp;copy=1';
            $view['link_edit'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;id=' . $view['id'];
            $view['link_delete'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op . '&amp;delete_id=' . $view['id'] . '&amp;delete_checkss=' . md5($view['id'] . NV_CACHE_PREFIX . $client_info['session_id']);

            $xtpl->assign('VIEW', $view);

            if ($view['work_time'] > 0) {
                $xtpl->parse('main.view.loop_day.loop.work_time');
            }
            if ($view['over_time'] > 0) {
                $xtpl->parse('main.view.loop_day.loop.over_time');
            }
            if ($view['accepted_time'] > 0) {
                $xtpl->parse('main.view.loop_day.loop.accepted_time');
            }
            $xtpl->parse('main.view.loop_day.loop.status' . $view['status']);
            $xtpl->parse('main.view.loop_day.loop');
        }

        $stt--;
        $xtpl->parse('main.view.loop_day');
    }

    if (!$is_genpage) {
        // Hiển thị cột tiêu đề TT khi không tìm kiếm
        $xtpl->parse('main.view.col_number');

        // Hiển thị thống kê khi không tìm kiếm
        $total_worktime = 0;
        $total_overtime = 0;
        $total_acceptedtime = 0;

        foreach ($work_time as $task_catid => $value) {
            $project = [
                'name' => empty($array_taskcat_id_timekeeping[$task_catid]['title']) ? 'N/A' : $array_taskcat_id_timekeeping[$task_catid]['title'],
                'work_time' => $value,
                'over_time' => empty($over_time) ? 0 : $over_time[$task_catid],
                'accepted_time' => empty($accepted_time) ? 0 : $accepted_time[$task_catid]
            ];

            $project['work_time_day'] = number_format(($project['work_time'] / 8), 2, '.', ',');
            $project['work_time_day'] = rtrim($project['work_time_day'], '0');
            $project['work_time_day'] = rtrim($project['work_time_day'], '.');

            $project['over_time_day'] = number_format(($project['over_time'] / 8), 2, '.', ',');
            $project['over_time_day'] = rtrim($project['over_time_day'], '0');
            $project['over_time_day'] = rtrim($project['over_time_day'], '.');

            $xtpl->assign('PROJECT', $project);

            if ($project['work_time'] > 0) {
                $xtpl->parse('main.view.total.loop.work_time');
            }
            if ($project['over_time'] > 0) {
                $xtpl->parse('main.view.total.loop.over_time');
            }
            if ($project['accepted_time'] > 0) {
                $xtpl->parse('main.view.total.loop.accepted_time');
            }
            $xtpl->parse('main.view.total.loop');

            $total_worktime += $value;
            $total_overtime += $over_time[$task_catid] ?? 0;
            $total_acceptedtime += $accepted_time[$task_catid] ?? 0;
        }

        $xtpl->assign('TOTAL_WORKTIME', round($total_worktime / 8, 1));
        $xtpl->assign('TOTAL_OVERTIME', round($total_overtime / 8, 1));
        $xtpl->assign('TOTAL_ACCEPTEDTIME', round($total_acceptedtime / 8, 1));
        $xtpl->parse('main.view.total');
    }

    $xtpl->parse('main.view');
}

if (!empty($error)) {
    $xtpl->assign('ERROR', implode('<br />', $error));
    $xtpl->parse('main.error');
}

// Xuất loại việc
for ($i = 0; $i < 3; $i++) {
    $work_type = [
        'key' => $i,
        'title' => $lang_module['work_type' . $i],
        'selected' => $row['work_type'] == $i ? ' selected="selected"' : '',
    ];
    $xtpl->assign('WORK_TYPE', $work_type);
    $xtpl->parse('main.work_type');
}

if ($row['work_type'] == 0) {
    $xtpl->assign('CSS_ASSIGNED', ' hidden');
    $xtpl->assign('CSS_SCHEDULE', ' hidden');
} elseif ($row['work_type'] == 1) {
    $xtpl->assign('CSS_ASSIGNED', '');
    $xtpl->assign('CSS_SCHEDULE', ' hidden');
} else {
    $xtpl->assign('CSS_ASSIGNED', ' hidden');
    $xtpl->assign('CSS_SCHEDULE', '');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

$page_title = $lang_module['work'];

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
