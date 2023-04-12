<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2010 - 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sun, 08 Apr 2012 00:00:00 GMT
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

// Kiểm tra và chuyển hướng nhân sự đến khu vực được phép quản lý đầu tiên
$allow_func_check = array_values(array_diff($allow_func, ['main']));
if (!empty($allow_func_check)) {
    nv_redirect_location(NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $allow_func_check[0]);
}

$contents = 'Nếu bạn đang xem được dòng này tức là bạn chưa có quyền quản lý các chức năng của module này. Mời liên hệ với kỹ thuật';

include NV_ROOTDIR . '/includes/header.php';
echo nv_admin_theme($contents);
include NV_ROOTDIR . '/includes/footer.php';
