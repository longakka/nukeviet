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
class Times
{
    /**
     * @param number $timestamp
     * @return number
     */
    public static function getDayStartTime($timestamp = 0)
    {
        if (empty($timestamp)) {
            $timestamp = NV_CURRENTTIME;
        }
        return mktime(0, 0, 0, intval(date('n', $timestamp)), intval(date('j', $timestamp)), intval(date('Y', $timestamp)));
    }

    /**
     * @param number $timestamp
     * @return number[]
     */
    public static function getCurrentDay($timestamp = 0)
    {
        if (empty($timestamp)) {
            $timestamp = NV_CURRENTTIME;
        }
        return [
            'd' => intval(date('j', $timestamp)),
            'm' => intval(date('n', $timestamp)),
            'y' => intval(date('Y', $timestamp)),
        ];
    }

    /**
     * @param number $timestamp
     * @return number[]
     */
    public static function getBeforeDay($timestamp = 0)
    {
        if (empty($timestamp)) {
            $timestamp = NV_CURRENTTIME;
        }
        $thisday = mktime(0, 0, 0, intval(date('n', $timestamp)), intval(date('j', $timestamp)), intval(date('Y', $timestamp)));
        $thisday--;
        return [
            'd' => intval(date('j', $thisday)),
            'm' => intval(date('n', $thisday)),
            'y' => intval(date('Y', $thisday)),
        ];
    }
}
