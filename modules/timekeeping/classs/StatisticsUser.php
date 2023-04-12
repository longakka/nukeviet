<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */
namespace Modules\timekeeping\classs;

use XTemplate;

class StatisticsUser extends Main
{

    protected $tpl = 'statistics';

    protected $title = 'statistics';

    protected $table_data = 'checkio';

    public function __construct($HR)
    {
        parent::__construct();
        $this->HR = $HR;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function dataPost()
    {
        $post = array();
        return $post;
    }

    /**
     * @param integer $month
     * @param integer $year
     * @return array|number
     */
    public function getData($month, $year)
    {
        $data = $this->getAll('*', 'userid=' . $this->HR->user_info['userid'] . ' AND month=' . $month . ' AND year=' . $year, 'day ASC');
        $dataReturn = [];

        foreach ($data as $checkio) {
            $checkio['time'] = '<p>Checkin: ' . date('G:i  j/n/Y', $checkio['checkin_real']) . '</p>' . '<p>Checkout: ' . (!empty($checkio['checkout_real']) ? date('G:i  j/n/Y', $checkio['checkout_real']) : '') . '</p>';
            $checkio['checkin'] = date('H:i', $checkio['checkin_real']);
            $checkio['checkout'] = !empty($checkio['checkout_real']) ? date('H:i', $checkio['checkout_real']) : '--';
            $checkio['checkin_full'] = date('H:i d/m/Y', $checkio['checkin_real']);
            $checkio['checkout_full'] = !empty($checkio['checkout_real']) ? date('H:i d/m/Y', $checkio['checkout_real']) : '--';
            $checkio['workday'] = round($checkio['workday'] / 100, 2);
            $checkio['day_text'] = str_pad($checkio['day'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $checkio[$checkio['status']] = $checkio['workday'];

            $dataReturn[$checkio['day']][] = $checkio;
        }

        return $dataReturn;
    }

    /**
     * @param number $month
     * @param number $year
     * @return string
     */
    public function htmlList($month = 0, $year = 0)
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('CHECKSS', $this->checkss);
        $xtpl->assign('BANG_CONG', sprintf($this->lang_module['timesheets'], $month, $year) . " (" . $this->HR->user_info['title'] . ": " . $this->HR->user_info['full_name'] . ")");

        $data = $this->getData($month, $year);
        $tt = 0;
        $sumLate = 0;
        $sumWork = [
            'worktime' => 0,
            'workout' => 0
        ];

        foreach ($data as $session) {
            $tt++;
            foreach ($session as $checkio) {
                $sumLate += $checkio['late'];
                $sumWork[$checkio['status']] += $checkio['workday'];

                $checkio['tt'] = $tt;
                if (empty($checkio['workout'])) {
                    $checkio['workout'] = 0;
                }
                if (empty($checkio['worktime'])) {
                    $checkio['worktime'] = 0;
                }
                $checkio['daysection'] = $this->getDaySection($checkio['checkin_real'], $checkio['checkout_real']);

                $xtpl->assign('LOOP', $checkio);
                $xtpl->parse('list.loop');
            }
        }

        $sumWork['worktime'] = round($sumWork['worktime'], 1);
        $sumWork['workout'] = round($sumWork['workout'], 1);

        $xtpl->assign('sumLate', $sumLate);
        $xtpl->assign('sumWork_time', $sumWork['worktime']);
        $xtpl->assign('sumWork_out', $sumWork['workout']);
        $xtpl->assign('sumWork', $sumWork['worktime'] + $sumWork['workout']);

        $xtpl->parse('list');
        return $xtpl->text('list');
    }

    /**
     * @param integer $timestart
     * @param integer $timeend
     */
    private function getDaySection($timestart, $timeend)
    {
        $hs = intval(date('Gi', $timestart));
        $he = empty($timeend) ? null : intval(date('Gi', $timeend));

        if (is_null($he)) {
            // Trường hợp chưa checkout => Căn cứ vào thời gian checkin
            if ($hs < 1230) {
                return $this->lang_module['daysection0'];
            }
            if ($hs < 1730) {
                return $this->lang_module['daysection1'];
            }
            return $this->lang_module['daysection2'];
        } else {
            // Trường hợp đã checkout
            if ($he <= 730) {
                // Ngày hôm trước
                if ($hs < 1230) {
                    // Cả ngày
                    return $this->lang_module['daysection3'];
                }
                if ($hs < 1730) {
                    // Buổi chiều
                    return $this->lang_module['daysection1'];
                }
                // Buổi tối
                return $this->lang_module['daysection2'];
            }
            if ($he <= 1230) {
                // Buổi sáng
                return $this->lang_module['daysection0'];
            }
            if ($he <= 1730) {
                // Buổi chiều
                if ($hs < 1230) {
                    // Cả ngày
                    return $this->lang_module['daysection3'];
                }
                return $this->lang_module['daysection1'];
            }
            // Buổi tối
            if ($hs < 1230) {
                // Cả ngày
                return $this->lang_module['daysection3'];
            }
            if ($hs < 1730) {
                // Buổi chiều
                return $this->lang_module['daysection1'];
            }
            return $this->lang_module['daysection2'];
        }
    }

    public function html()
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', nv_url_rewrite($this->base_url_user . '&amp;' . NV_OP_VARIABLE . '=' . $this->op, true));
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', array(
                'value' => $k,
                'title' => $title
            ));
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('main.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', array(
                'value' => $value,
                'title' => $value
            ));
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('main.year');
        }

        $xtpl->assign('BANG_CONG', $this->htmlList($this->month, $this->year));
        $xtpl->parse('main');
        return $xtpl->text('main');
    }

    public function htmlAdmin()
    {
        $xtpl = new XTemplate($this->tpl . ".tpl", $this->dir_theme);
        $xtpl->assign('LANG', $this->lang_module);
        $xtpl->assign('GLANG', $this->lang_global);
        $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
        $xtpl->assign('ACTION_URL', $this->base_url . '&amp;' . NV_OP_VARIABLE . '=' . $this->op . '&amp;userid=' . $this->HR->user_info['userid']);
        foreach ($this->listMonth as $k => $title) {
            $xtpl->assign('MONTH', array(
                'value' => $k,
                'title' => $title
            ));
            $xtpl->assign('selected', ($k == $this->month) ? 'selected' : '');
            $xtpl->parse('main.month');
        }
        foreach ($this->listYear as $value) {
            $xtpl->assign('YEAR', array(
                'value' => $value,
                'title' => $value
            ));
            $xtpl->assign('selected', ($value == $this->year) ? 'selected' : '');
            $xtpl->parse('main.year');
        }
        $xtpl->assign('BANG_CONG', $this->htmlList($this->month, $this->year));
        $xtpl->parse('main');
        return $xtpl->text('main');
    }
}
