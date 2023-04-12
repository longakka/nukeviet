<?php

/**
 * @Project Đấu Thầu Marketing Vệ Tinh
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2021 VINADES.,JSC. All rights reserved
 * @License not free, contact for price
 * @Createdate Tuesday, June 22, 2021 4:49:56 PM GMT+07:00
 */

namespace NukeViet\Module\timekeeping;

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

/**
 * @author VINADES.,JSC
 *
 */
class Role
{
    /**
     * @var integer bộ phận nhân sự
     */
    const ROLE_HR_DEPARTMENT = 1;

    /**
     * @var integer bộ phận kế toán văn phòng
     */
    const ROLE_ACCOUNTING_DEPARTMENT = 2;
}
