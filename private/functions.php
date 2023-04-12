<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

if (!defined('NV_IS_CONSOLE')) {
    die('Stop!!!');
}

// Phòng ban
$sql = 'SELECT * FROM ' . $db_config['prefix'] . '_' . $module_data . '_department ORDER BY parentid ASC';
$global_array_departments = $nv_Cache->db($sql, 'id', $module_name);
