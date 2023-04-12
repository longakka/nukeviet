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
class Money
{
    /**
     * @param float|double|int $value
     * 
     * @return string
     */
    public static function format($value)
    {
        return number_format($value, 0, ',', '.');
    }
}
