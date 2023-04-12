<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 10/03/2010 10:51
 */

namespace Modules\timekeeping\classs;

use PDO;
use Exception;

abstract class Main
{

    protected $db;

    protected $db_config;

    protected $nv_Request;

    protected $nv_Cache;

    protected $global_config;

    protected $lang_global;

    protected $lang_module;

    protected $site_mods;

    protected $admin_info;

    protected $user_info;

    protected $module_name;

    protected $module_file;

    protected $module_data;

    protected $module_config;
    
    protected $client_info;

    protected $op;

    public $lang_title;

    private $tpl = 'main';

    protected $title = 'main';

    protected $table_data;

    protected $table_module;

    protected $module_upload;

    protected $dir_theme;

    public $checkss;

    protected $base_url;

    public $cr_time;

    protected $month = 0;

    protected $year = 0;

    protected $listMonth;

    protected $listYear;
    /**
     *  @var integer Tổng giờ làm việc của một công (8 giờ)
     */
    public $hourwork = 8;

    public $HR = null;

    public $base_url_user = '';

    public function __construct()
    {
        global $db, $nv_Request, $nv_Cache, $db_config, $global_config, $module_config, $lang_global, $lang_module, $site_mods, $admin_info, $module_name, $op, $user_info, $client_info;
        $this->db = $db;
        $this->nv_Request = $nv_Request;
        $this->nv_Cache = $nv_Cache;
        $this->db_config = $db_config;
        $this->global_config = $global_config;
        $this->module_config = $module_config;
        $this->lang_global = $lang_global;
        $this->site_mods = $site_mods;
        $this->admin_info = $admin_info;
        $this->user_info = $user_info;
        $this->client_info = $client_info;
        $this->op = $op;
        $this->cr_time = NV_CURRENTTIME;
        // $this->viewTime($this->cr_time);

        $this->setModule($module_name);

        // Lấy ngôn ngữ ngoài site cho admin
        $lang_file = NV_ROOTDIR . '/modules/' . $this->module_file . '/language/' . NV_LANG_INTERFACE . '.php';
        if (defined('NV_ADMIN') and file_exists($lang_file)) {
            include $lang_file;
        }

        $this->lang_module = $lang_module;
        $this->lang_title = $this->lang_module[$this->title];
        $this->month = date('n', $this->cr_time);
        $this->year = date('Y', $this->cr_time);
        $this->listMonth = array(
            1 => $this->lang_global['january'],
            2 => $this->lang_global['february'],
            3 => $this->lang_global['march'],
            4 => $this->lang_global['april'],
            5 => $this->lang_global['may'],
            6 => $this->lang_global['june'],
            7 => $this->lang_global['july'],
            8 => $this->lang_global['august'],
            9 => $this->lang_global['september'],
            10 => $this->lang_global['october'],
            11 => $this->lang_global['november'],
            12 => $this->lang_global['december']
        );
        $this->listYear = array(
            2021,
            2022,
            2023,
            2024,
            2025,
            2026,
            2027,
            2028,
            2029,
            2030
        );
    }

    public abstract function dataPost();
    public function viewTime($timestamp)
    {
        echo date('G:i  j/n/Y', $timestamp);
        exit();
    }
    public function setModule($module_name)
    {
        $module_info = $this->site_mods[$module_name];
        $this->module_name = $module_name;
        $this->module_file = $module_info['module_file'];
        $this->module_data = $module_info['module_data'];
        $this->module_upload = $module_info['module_upload'];
        $this->table_module = $this->db_config['prefix'] . "_" . $this->module_data;
        $this->table_data = $this->db_config['prefix'] . "_" . $this->module_data . "_" . $this->table_data;

        if (defined('NV_ADMIN')) {
            $this->dir_theme = NV_ROOTDIR . '/themes/' . $this->global_config['module_theme'] . '/modules/' . $this->module_file;
        } else {
            global $module_info;
            $this->dir_theme = NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $this->module_file;
        }

        if (defined('NV_ADMIN')) {
            $this->checkss = md5(NV_CHECK_SESSION . '_' . $this->module_name . '_' . $this->admin_info['userid']);
        } elseif (defined('NV_IS_USER')) {
            $this->checkss = md5(NV_CHECK_SESSION . '_' . $this->module_name . '_' . $this->user_info['userid']);
        } else {
            $this->checkss = md5(NV_CHECK_SESSION . '_' . $this->module_name . '_0');
        }

        $this->base_url = NV_BASE_ADMINURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $this->module_name;
        $this->base_url_user = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name;
    }

    public function setDirTheme($dir_theme)
    {
        $this->dir_theme = $dir_theme;
    }

    public function setHr($HR)
    {
        $this->HR = $HR;
    }

    public function checkssAdmin($checkss)
    {
        if ($this->checkss != $checkss) {
            nv_htmlOutput('Error Session, Please close the browser and try again');
        }
        return $this->checkss == $checkss;
    }

    public function getAlias($title, $id = 0)
    {
        $alias = change_alias($title);
        $i = 0;
        $aliasCopy = $alias;
        while ($this->count('alias="' . $aliasCopy . '" AND id != ' . $id) > 0) {
            $i++;
            $aliasCopy = $alias . "-" . $i;
        }
        return strtolower($aliasCopy);
    }

    public function checkExistUserid($userid)
    {
        $db = $this->db;
        $md5username = nv_md5safe($userid);
        if (preg_match('/^([0-9]+)$/', $userid)) {
            $sql = 'SELECT userid, username, active, group_id, in_groups FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . intval($userid) . ' OR md5username=' . $db->quote($md5username);
        } else {
            $sql = 'SELECT userid, username, active, group_id, in_groups FROM ' . NV_USERS_GLOBALTABLE . ' WHERE md5username=' . $db->quote($md5username);
        }
        return $db->query($sql)->fetch(3);
    }

    public function checkExistId($id)
    {
        $this->db->sqlreset()
            ->select('COUNT(*)')
            ->from($this->table_data)
            ->where('id=' . $id);
        $_sql = $this->db->sql();
        $count = $this->db->query($_sql)->fetchColumn();
        return $count > 0;
    }

    public function count($where = null, $select = 'COUNT(*)')
    {
        $this->db->sqlreset()
            ->select($select)
            ->from($this->table_data);
        if ($where !== null) {
            $this->db->where($where);
        }
        $_sql = $this->db->sql();
        $num_items = $this->db->query($_sql)->fetchColumn();
        return $num_items;
    }

    public function getAll($select = "*", $where = null, $order = null, $key = null)
    {
        $data_return = array();
        $this->db->sqlreset()
            ->select($select)
            ->from($this->table_data);
        if ($where !== null) {
            $this->db->where($where);
        }
        if (!empty($order)) {
            $this->db->order($order);
        }
        $_sql = $this->db->sql();
        $result = $this->db->query($_sql);
        while ($row = $result->fetch(2)) {
            if ($key != null) {
                $data_return[$row[$key]] = $row;
            } else {
                $data_return[] = $row;
            }
        };
        return $data_return;
    }

    public function getById($id, $select = '*')
    {
        $id = !empty($id) ? $id : '0';
        $this->db->sqlreset()
            ->select($select)
            ->from($this->table_data)
            ->where('id=' . $id);
        $_sql = $this->db->sql();
        $row = $this->db->query($_sql)->fetch(2);
        return $row;
    }

    public function listPagination($per_page, $page, $select = '*', $where = null, $order = null)
    {
        $data_return = array();
        $this->db->sqlreset()
            ->select($select)
            ->from($this->table_data)
            ->limit($per_page)
            ->offset(($page - 1) * $per_page);
        if (!empty($where)) {
            $this->db->where($where);
        }
        if (!empty($order)) {
            $this->db->order($order);
        }
        $result = $this->db->query($this->db->sql());
        while ($row = $result->fetch(2)) {
            $data_return[] = $row;
        }
        return $data_return;
    }

    public function deleteById($id)
    {
        $sql = 'DELETE FROM ' . $this->table_data . ' WHERE id = ' . $id;
        return $this->db->query($sql);
    }

    public function deleteAll($where)
    {
        $sql = 'DELETE FROM ' . $this->table_data . ' WHERE ' . $where;
        return $this->db->query($sql);
    }

    public function editById($id, $data = array())
    {
        if (count($data) == 0)
            return false;
        return $this->updateCSLD($data, 'id = ' . $id);
    }

    public function updateCSLD($data, $where, $array_type = null, $keys = null)
    {
        try {
            $keys = empty($keys) ? array_keys($data) : $keys;
            $set = array_map(function ($k) {
                return $k . ' = :' . $k;
            }, $keys);
            $stmt = $this->db->prepare('UPDATE ' . $this->table_data . ' SET ' . implode(", ", $set) . ' WHERE ' . $where);
            foreach ($keys as $k) {
                $stmt->bindValue(':' . $k, $data[$k], !empty($array_type[$k]) ? $array_type[$k] : PDO::PARAM_STR);
            }
            return $stmt->execute();
        } catch (Exception $e) {
            trigger_error(print_r($e, true));
            nv_htmlOutput('Save error!!!');
        }
        return false;
    }

    public function updateWeight($id, $cWeight)
    {
        $this->db
            ->sqlreset()
            ->select('*')
            ->from($this->table_data)
            ->where('id = ' . $id);
        $item = $this->db->query($this->db->sql())->fetch(2);
        if (!isset($item['weight'])) {
            return false;
        }
        $result = $this->getAll('id', "id!=" . $id, "weight ASC");
        $weight = 0;
        foreach ($result as $row) {
            $weight++;
            if ($weight == $cWeight)
                $weight++;
            $this->updateCSLD(array(
                'weight' => $weight
            ), 'id = ' . $row['id'], array(
                'weight' => PDO::PARAM_INT
            ));
        }
        $this->updateCSLD(array(
            'weight' => $cWeight
        ), 'id = ' . $id, array(
            'weight' => PDO::PARAM_INT
        ));
        $this->nv_Cache->delMod($this->module_name);
        nv_insert_logs(NV_LANG_DATA, $this->module_name, $this->lang_module['logChangeWeightDepartment'], "Id: " . $id, $this->admin_info['userid']);
        return true;
    }

    /*
     * $keys: danh sach cac field trong $data se duoc luu vao CSDL
     */
    public function save($data, $array_type = null, $keys = null)
    {
        try {
            $keys = empty($keys) ? array_keys($data) : $keys;
            $values = array_map(function ($v) {
                return ":" . $v;
            }, $keys);
            $sql = 'INSERT INTO ' . $this->table_data . ' (' . implode(",", $keys) . ') VALUES ( ' . implode(", ", $values) . ' )';
            $stmt = $this->db->prepare($sql);
            foreach ($keys as $k) {
                $stmt->bindValue(':' . $k, $data[$k], !empty($array_type[$k]) ? $array_type[$k] : PDO::PARAM_STR);
            }
            $stmt->execute();
        } catch (Exception $e) {
            trigger_error(print_r($e, true));
            nv_htmlOutput('Save error!!!');
        }
        return $this->db->lastInsertId();
    }

    public function updateLinkEditDelete($data_row)
    {
        return array_map(function ($row) {
            $row['edit'] = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $this->module_name . "&amp;" . NV_OP_VARIABLE . "=" . $this->op . "&amp;id=" . $row['id'];
            $row['delete'] = NV_BASE_ADMINURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $this->module_name . "&amp;" . NV_OP_VARIABLE . "=" . $this->op . "&amp;delete=1&amp;id=" . $row['id'] . "&amp;checkss=" . $this->checkss;
            return $row;
        }, $data_row);
    }

    /**
     * @param string $contents
     */
    public function sendClientAdmin($contents)
    {
        global $my_head;

        $my_head .= '<link rel="stylesheet" href="' . NV_BASE_SITEURL . 'themes/default/css/' . $this->module_file . '.css">';

        include NV_ROOTDIR . '/includes/header.php';
        echo nv_admin_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
    }

    /**
     * @param string $contents
     */
    public function sendClientUser($contents)
    {
        include NV_ROOTDIR . '/includes/header.php';
        echo nv_site_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
    }
    /**
     * @param string $status
     * @param string $place
     * @return mixed
     */
    public function sendSlack($message, $channel)
    {
        $api_remote_url = 'https://id.dauthau.net/api.php';
        $agent = 'NukeViet Remote API Lib';

        $site_timezone = 'Asia/Ho_Chi_Minh';
        date_default_timezone_set($site_timezone);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api_remote_url);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $safe_mode = (ini_get('safe_mode') == '1' || strtolower(ini_get('safe_mode')) == 'on') ? 1 : 0;
        $open_basedir = ini_get('open_basedir') ? true : false;
        if (!$safe_mode and !$open_basedir) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        }

        $apisecret = 'i57N0joiJ4xlJ27X8s62oULGQWA4XwI7';
        $timestamp = time();
        $request = [
            // Tham số bắt buộc
            'apikey' => 'e1eAAdCgJ890Nb5RcJ14bJ5BKt6Y7SjZ',
            'timestamp' => $timestamp,
            'hashsecret' => password_hash($apisecret . '_' . $timestamp, PASSWORD_DEFAULT),
            'action' => 'Slack',
            'language' => 'vi',
            'module' => '',
            // Tham số gửi Slack
            'message' => $message,
            'channel' => $channel
        ];

        // [message] => Lang Data is required for multi-language website!!! cái này thiêt lập sao e
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);

        curl_setopt($ch, CURLOPT_POST, sizeof($request));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == 200) {
            return json_decode($res, true);
        } else {
            return false;
        }
    }
}
