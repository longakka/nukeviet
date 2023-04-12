<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 31/05/2010, 00:36
 */

define('NV_SYSTEM', true);

define('NV_ROOTDIR', str_replace(DIRECTORY_SEPARATOR, '/', realpath(pathinfo(__file__, PATHINFO_DIRNAME) . '/..')));
require NV_ROOTDIR . '/includes/mainfile.php';
require NV_ROOTDIR . '/includes/core/user_functions.php';

echo '<pre><code>';

$textmessage = "
Phan Tấn Dũng (dungpt) check-in lúc 06:50:03 03/05/2021 ở ở nhà.
Nguyễn Chí Tôn (nguyenchiton) check-in lúc 07:30:29 03/05/2021 ở nhà Hà Nội.
Trần Thu Hà (thuha) check-in lúc 07:30:51 03/05/2021 ở Hải Phòng.
Hồ Thị Hoa Phượng (hoaphuonght99) check-in lúc 07:32:16 03/05/2021 ở Ở nhà Quảng Trị.
Đậu Quốc Việt (quocviet01) check-in lúc 07:32:21 03/05/2021 ở Hà Nội.
Nguyễn Thành Hưng (thanhhung) check-in lúc 07:32:55 03/05/2021 ở nhà.
Tô Thị Thanh Thảo (Thanhthao95) check-in lúc 07:33:59 03/05/2021 ở ở nhà.
Hoàng Tuyên (tuyenhv) check-in lúc 07:34:24 03/05/2021 ở Nhà.
Nguyễn Văn Bảo (baonv-dev) check-in lúc 07:34:27 03/05/2021 ở ở Bắc Giang.
Trần Thị Như Yến (nhuyen) check-in lúc 07:34:28 03/05/2021 ở Văn phòng QTri.
Nguyễn Hữu Thọ (huutho_tdfoss) check-in lúc 07:35:26 03/05/2021 ở Làm việc tại nhà.
Mè Văn Tuân (Tuanme123) check-in lúc 07:35:28 03/05/2021 ở Ở nhà Phú Thọ.
Nguyễn Văn Lâm (lambnck99) check-in lúc 07:35:37 03/05/2021 ở Bắc Ninh.
Phạm Bá Tuấn (tuanpb1988) check-in lúc 07:36:46 03/05/2021 ở Tp. Vinh.
Hoàng Nguyễn (viethoang) check-in lúc 07:36:52 03/05/2021 ở Văn phòng CTy.
Vũ Hữu Nhất (huunhat) check-in lúc 07:39:56 03/05/2021 ở Hà Nội.
Vũ Bích Ngọc (bichngoc) check-in lúc 07:40:45 03/05/2021 ở LN.
Trịnh Ngọc Tuyết (Ngoctuyet98) check-in lúc 07:42:36 03/05/2021 ở Hà Nội.
Nguyễn Lương Nam (nguyennam) check-in lúc 07:43:56 03/05/2021 ở Hải Dương.
Bùi Văn Hùng (vanhung14.2.2017) check-in lúc 07:44:17 03/05/2021 ở Hòa Bình.
Trần Thu (tranthu) check-in lúc 07:46:04 03/05/2021 ở Hoà Bình.
Hà Quang Cường (hqc210185) check-in lúc 07:46:08 03/05/2021 ở Đồng Hới, Quảng Bình.
Quang Hoàng Văn (hoangquang) check-in lúc 07:46:10 03/05/2021 ở Văn phòng tdfoss.
Tiến Phạm (phamductien) check-in lúc 07:46:11 03/05/2021 ở Tại nhà.
Trần Thị Khuyến (trankhuyen) check-in lúc 07:48:26 03/05/2021 ở Hà Nội.
Nguyễn Tống Ngọc Hà (hatong) check-in lúc 07:52:07 03/05/2021 ở Hà Nội.
Đại Cao Trần (caotrandai) check-in lúc 07:52:20 03/05/2021 ở Hà Nội.
Trần Thanh Tùng (tungvinades) check-in lúc 07:54:31 03/05/2021 ở Hà Nội.
Nguyễn Thị Hải Xuân (HaiXuan) check-in lúc 07:56:31 03/05/2021 ở VP Quảng Trị.
Đào Thu Hằng (thuhang) check-in lúc 07:56:43 03/05/2021 ở Hồ Chí Minh.
Ngọc Ánh (anhngoc28) check-in lúc 07:57:22 03/05/2021 ở Hải dương.
Vũ Văn Thảo (thao) check-in lúc 07:59:49 03/05/2021 ở La Phù, Hoài Đức.
Thuy Nguyen (bichthuy) check-in lúc 08:07:40 03/05/2021 ở Phú Thọ.
Trịnh Thị Hương Quỳnh (quynhtth) check-in lúc 08:09:58 03/05/2021 ở Quảng Trị.
Hồ Thị Linh (linhtdfoss) check-in lúc 08:12:58 03/05/2021 ở Văn phòng ở Quảng Trị.
Ngô Thị Giang (giangnt97) check-in lúc 08:36:23 03/05/2021 ở Hà Nội.
Nguyễn Xuân Hải (hainx) check-in lúc 14:16:07 03/05/2021 ở Bình Thạnh, Hồ Chí Minh.
";

$array = array_filter(array_map('trim', explode("\n", $textmessage)));

foreach ($array as $row) {
    if (preg_match('/\((.*?)\).*?lúc ([0-9]{2})\:([0-9]{2})\:([0-9]{2}) ([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ở/iu', $row, $m)) {
        $username = trim($m[1]);
        $checkin = mktime(intval($m[2]), intval($m[3]), intval($m[4]), intval($m[6]), intval($m[5]), intval($m[7]));

        // Lấy thành viên
        $sql = 'SELECT userid FROM ' . NV_USERS_GLOBALTABLE . ' WHERE username=' . $db->quote($username);
        $userid = $db->query($sql)->fetchColumn();

        if (empty($userid)) {
            die("Nhân viên không tồn tại: " . $username);
        }

        $sql = "UPDATE nv4_timekeeping_checkio SET
            checkin_real=" . $checkin . "
        WHERE userid=" . $userid . " AND day=" . intval($m[5]) . " AND month=" . intval($m[6]) . " AND year=" . intval($m[7]);
        $db->query($sql);

        echo $username . ": " . $checkin . "\n";
    } else {
        die($row . " ----- không hợp lệ");
    }
}

die("Xong!\n");
