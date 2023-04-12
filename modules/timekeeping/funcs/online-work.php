<?php

use NukeViet\Module\timekeeping\Shared\Times;

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
$page_title = $lang_module['online_work'];
$base_url = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $op;
$base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
$array_mod_title[] = [
    'catid' => 0,
    'title' => $page_title,
    'link' => $base_url
];

// Lỗi nếu không có phòng ban
if (!isset($department_array[$HR->user_info['department_id']])) {
    trigger_error('No department!', 256);
}

// $array_staffs : danh sách nhân viên nhóm trong phòng ban
// $array_userid : danh sách ID các nhân viên
// $array_view_allowed : danh sách phòng được phép xem work-report
$sql = "SELECT tb1.userid, tb1.department_id, tb1.view_department_report, tb2.username, tb2.first_name, tb2.last_name
        FROM " . $db_config['prefix'] . "_" . $module_data . "_human_resources tb1 INNER JOIN " . NV_USERS_GLOBALTABLE . " tb2 ON tb1.userid=tb2.userid
        WHERE tb2.active=1 AND still_working=1
        ORDER BY tb1.weight ASC"; // them stillworking, vao ca block
        
$result = $db->query($sql);
$array_staffs = $array_userid = $array_view_allowed = [];
$list_sub_dep = [];

while ($row = $result->fetch()) {
    $row['full_name'] = nv_show_name_user($row['first_name'], $row['last_name']);
    $row['show_name'] = $row['full_name'] . ' (' . $row['username'] . ')';
    $array_staffs[$row['department_id']][$row['userid']] = $row;
    $array_userid[$row['userid']] = $row['userid'];

    if (!empty($department_array[$HR->user_info['department_id']]['view_all_task'])) {
        // phòng này được phép xem tất cả
        $array_view_allowed[] = $row['department_id'];
    } else if (isset($leader_array[$user_info['userid']])) {
        // nếu là leader thì được xem phòng mình và các phòng con
        if ($leader_array[$user_info['userid']]['department_id'] == $row['department_id']) {
            $array_view_allowed[] = $row['department_id'];
            if (empty($list_sub_dep)) {
                $list_sub_dep = GetDepartmentidInParent($row['department_id']);
            }
        }
    } else if (($user_info['userid'] == $row['userid']) && ($row['view_department_report'] != 0)) {                   // nếu là chính mình
        // lấy dữ liệu trong view_department_report ra (nếu có)
        $array_view_allowed = array_map('intval', explode(',', $row['view_department_report']));
    }
}

if (!empty($list_sub_dep)) {
    $array_view_allowed = array_merge($array_view_allowed, $list_sub_dep);
}
$array_view_allowed = array_unique($array_view_allowed);

// Lấy thông tin checkin mà chưa checkout
$checkin = [];
$works = [];
$beginning_of_day = strtotime('today');
$end_of_day = strtotime('tomorrow')-1;

if (!empty($array_userid)) {
    // Lấy cấu hình bắt đầu ngày làm việc
    $sql = "SELECT config_value 
            FROM " . $db_config['prefix'] . "_" . $module_data . "_config
            WHERE userid=0 AND config_name='timeCheckio' ORDER BY year DESC, month DESC LIMIT 1";
    
    $timeCheckio = unserialize($db->query($sql)->fetchColumn());
    $day_start = Times::getDayStartTime() + (($scheckin_time - 30) * 60);

    if (NV_CURRENTTIME >= $day_start) {
        $days = Times::getCurrentDay();
    } else {
        $days = Times::getBeforeDay();
    }

    $sql = "SELECT userid, GROUP_CONCAT(checkin_real ORDER BY checkin_real DESC) checkin_real,
                           GROUP_CONCAT(checkout_real ORDER BY checkout_real DESC) checkout_real
            FROM " . $db_config['prefix'] . "_" . $module_data . "_checkio
            WHERE day=" . $days['d'] . " AND month=" . $days['m'] . " AND year=" . $days['y'] . "
            GROUP BY userid";
    $result = $db->query($sql);

    while ($row = $result->fetch()) {
        $row['checkin_real'] = explode(',', $row['checkin_real']);
        $row['checkout_real'] = explode(',', $row['checkout_real']);
        $checkin_real = array_pop($row['checkin_real']);
        $checkout_real = array_pop($row['checkout_real']);

        if (empty($checkout_real)) {
            $checkin[$row['userid']] = $row;
            // Lấy công việc
            $sql = "SELECT t1.title, t1.taskcat_id, t2.title as taskcat_title
                    FROM " . $db_config['prefix'] . "_" . $module_data . "_task as t1 INNER JOIN " . $db_config['prefix'] . "_" . $module_data . "_taskcat as t2 ON t1.taskcat_id=t2.id
                    WHERE t1.userid_assigned=" . $row['userid'] . " AND t1.status=1 
                          AND (t1.time_create BETWEEN " . $beginning_of_day . " AND " . $end_of_day . ")
                    ORDER BY t1.time_create DESC
                    LIMIT 3";                                                           // Giới hạn xem 3 việc
            $res = $db->query($sql);
            $userid = $row['userid'];

            while ($_row = $res->fetch()) {
                $work['title'] = $_row['title'];
                $work['taskcat_id'] = $_row['taskcat_id'];
                $work['taskcat_title'] = $_row['taskcat_title'];
                $works[$userid][] = $work;
            }
        }
    }
}

$xtpl = new XTemplate('online_work.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
$xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
$xtpl->assign('LANG', $lang_module);

// Lặp xuất theo thứ tự phòng ban
$displayed_deps = 0;
foreach ($department_array as $department) {
    if (isset($array_staffs[$department['id']])) {      // phòng này không trống - có nhân viên đang làm
        $xtpl->assign('DEPARTMENT', $department);

        $num_staffs_displayed = 0;
        foreach ($array_staffs[$department['id']] as $staff) {
            if (isset($checkin[$staff['userid']])) {
                $num_staffs_displayed++;
                $xtpl->assign('STAFF', $staff);

                // Nếu là chính mình
                if ($staff['userid'] == $user_info['userid']) {
                    // thêm link tới trang báo cáo của mình
                    $xtpl->assign('LINK', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name  . '&amp;' . NV_OP_VARIABLE . '=work');
                    $xtpl->parse('main.department.staff.link_openning');
                    $xtpl->parse('main.department.staff.link_closing');
                } elseif (!empty($array_view_allowed)) {
                    // có quyền xem work-report một phòng nào đó
                    if (in_array($department['id'], $array_view_allowed)) {
                        // phòng đang duyệt nằm trong danh sách được xem
                        // thêm link tới trang báo cáo của họ
                        $xtpl->assign('LINK', NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=work-report&amp;d=' . $department['id'] . '&amp;u=' . $staff['userid']);
                        $xtpl->parse('main.department.staff.link_openning');
                        $xtpl->parse('main.department.staff.link_closing');
                    }
                }

                if (!empty($works[$staff['userid']])) {
                    $work_list = $works[$staff['userid']];
                    while ($work = array_pop($work_list)) {
                        $xtpl->assign('task_title', $work['title']);
                        $xtpl->assign('taskcat_title', $work['taskcat_title']);
                        $xtpl->parse('main.department.staff.work');
                    }
                }
                $xtpl->parse('main.department.staff');

            }
        }

        if ($num_staffs_displayed > 0) {
            $displayed_deps++;
            $xtpl->parse('main.department');
        }
    }
}

if (empty($displayed_deps)) {
    $xtpl->parse('main.empty');
}

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
