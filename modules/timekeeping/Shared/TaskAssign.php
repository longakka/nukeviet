<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace NukeViet\Module\timekeeping\Shared;

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

/**
 * @author VINADES.,JSC
 *
 */
class TaskAssign
{
    /**
     * Hàm đồng bộ lại số giờ đã làm việc của 1 lệnh giao việc
     *
     * @param int $id
     * @param boolean $assign_work
     */
    public static function syncWorkedTime($id, $assign_work = true)
    {
        global $db_config, $module_data, $db;

        // Quét các công việc được giao này cập nhật lại thời gian làm việc
        if ($assign_work) {
            $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task_assign WHERE id=" . $id;
            $task_assign = $db->query($sql)->fetch();
            if (!empty($task_assign)) {
                $sql = "SELECT * FROM " . $db_config['prefix'] . "_" . $module_data . "_task
                WHERE work_type=1 AND assigned_task=" . $id . "
                ORDER BY set_time DESC, time_create ASC";
                $result = $db->query($sql);

                $remaining_time = $task_assign['work_time'];
                while ($row = $result->fetch()) {
                    if ($row['set_time'] > 0) {
                        // Công quản lý tính cố định
                        $time = $row['set_time'];
                    } else {
                        $time = $row['work_time'] + $row['over_time'];
                        // Công nhập lớn hơn thời gian khả dụng được duyệt
                        if ($time > $remaining_time) {
                            $time = $remaining_time;
                        }
                        if ($time < 0) {
                            $time = 0;
                        }
                    }

                    $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task SET
                        accepted_time=" . $time . "
                    WHERE id=" . $row['id'];
                    $db->query($sql);

                    $remaining_time -= $time;
                }
            }
        }

        // Cập nhật số giờ đã làm việc cho việc được giao này
        $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task_assign SET worked_time=IFNULL((
            SELECT SUM(work_time)+SUM(over_time) FROM " . $db_config['prefix'] . "_" . $module_data . "_task
            WHERE work_type=1 AND assigned_task=" . $id . "
        ), 0) WHERE id=" . $id;
        $db->exec($sql);

        // Cập nhật số giờ được duyệt
        $sql = "UPDATE " . $db_config['prefix'] . "_" . $module_data . "_task_assign SET accepted_time=IFNULL((
            SELECT SUM(accepted_time) FROM " . $db_config['prefix'] . "_" . $module_data . "_task
            WHERE work_type=1 AND assigned_task=" . $id . "
        ), 0) WHERE id=" . $id;
        $db->exec($sql);
    }
}
