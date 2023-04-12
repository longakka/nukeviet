<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC <contact@vinades.vn>
 * @Copyright (C) 2021 VINADES.,JSC. All rights reserved
 * @License: Not free read more http://nukeviet.vn/vi/store/modules/nvtools/
 * @Createdate Sun, 05 Dec 2021 14:43:38 GMT
 */

if (!defined('NV_IS_FILE_MODULES')) {
  die('Stop!!!');
}

$sql_drop_module = [];
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_business_trip";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_checkio";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_config";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_department";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_human_resources";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_office";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_task";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_task_assign";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_task_change";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_taskcat";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_working";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_workout";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_diary_penalize";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_leave_absence";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_work_schedule";
$sql_drop_module[] = "DROP TABLE IF EXISTS " . $db_config['prefix'] . "_" . $module_data . "_ranks";

$sql_create_module = $sql_drop_module;
$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_business_trip(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL,
  userid_leader int(11) unsigned NOT NULL DEFAULT 0,
  reason varchar(250) NOT NULL DEFAULT '',
  start_time int(11) unsigned NOT NULL,
  end_time int(11) unsigned NOT NULL,
  status enum('vacation','business_trip') NOT NULL DEFAULT 'business_trip',
  salary tinyint(1) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_checkio(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL,
  day tinyint(2) unsigned NOT NULL,
  month tinyint(2) unsigned NOT NULL,
  year smallint(5) unsigned NOT NULL,
  checkin int(11) unsigned NOT NULL,
  checkin_real int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Thời điểm nhấn nút check-in thực tế',
  checkout int(11) unsigned DEFAULT NULL,
  checkout_real int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Thời điểm nhấn nút check-out thực tế',
  workday float DEFAULT NULL,
  auto tinyint(1) unsigned NOT NULL DEFAULT 0,
  late smallint(4) unsigned NOT NULL DEFAULT 0,
  place varchar(250) NOT NULL,
  geolat DOUBLE unsigned NULL DEFAULT NULL,
  geolng DOUBLE unsigned NULL DEFAULT NULL,
  status enum('worktime','workout') NOT NULL DEFAULT 'worktime',
  workout_id int(11) NOT NULL DEFAULT 0,
  confirm int(1) unsigned NOT NULL DEFAULT 0 COMMENT 'userid của leader xác nhận làm thêm đột xuất',
  confirm_userid int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'userid của leader xác nhận hoặc từ chối làm thêm đột xuất',
  confirm_status tinyint(4) unsigned NOT NULL DEFAULT 0,
  confirm_time int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Thời điểm xác nhận hoặc từ chối',
  confirm_reason varchar(250) DEFAULT NULL,
  description varchar(250) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY userid (userid),
  KEY day (day),
  KEY month (month),
  KEY year (year),
  KEY user_date(userid, month, year)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_config(
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL,
  month smallint(2) unsigned DEFAULT NULL,
  year mediumint(5) unsigned DEFAULT NULL,
  config_name varchar(30) NOT NULL,
  config_value text NOT NULL,
  PRIMARY KEY (id),
  KEY config(year DESC, month DESC, id DESC),
  KEY userid (userid),
  KEY month (month),
  KEY year (year),
  KEY config_name (config_name)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_department(
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(250) NOT NULL,
  alias varchar(250) NOT NULL,
  parentid smallint(5) unsigned NOT NULL,
  weight smallint(5) unsigned NOT NULL DEFAULT 0,
  sort smallint(5) unsigned NOT NULL DEFAULT '0',
  lev smallint(5) unsigned NOT NULL DEFAULT '0',
  numsubcat smallint(5) NOT NULL DEFAULT '0',
  subcatid varchar(255) NOT NULL DEFAULT '',
  checkin_out_session tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'checkin/out theo phiên',
  assign_work TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Chấm công dạng giao việc hay không',
  view_all_task tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Cho phép xem báo cáo các phòng',
  view_all_absence tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Cho phép xem nghỉ phép của các phòng',
  check_work tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Kiểm tra nhắc nhở việc',
  description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_human_resources(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL,
  role_id varchar(100) NOT NULL DEFAULT '' COMMENT 'ID quyền',
  rank_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID bậc',
  still_working tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 là nghỉ làm, 1 là đang làm',
  office_id smallint(5) unsigned NOT NULL,
  department_id smallint(5) unsigned NOT NULL,
  confirm_workout varchar(255) DEFAULT NULL,
  view_diary_penalize varchar(255) NOT NULL DEFAULT '',
  view_leave_absence VARCHAR(250) NOT NULL DEFAULT '',
  view_department_report VARCHAR(250) NOT NULL DEFAULT '',
  no_aswork TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Không làm dạng giao việc dẫu cho phòng ban có giao việc',
  phone varchar(10) DEFAULT NULL,
  cmnd varchar(12) DEFAULT NULL,
  address varchar(250) DEFAULT NULL,
  weight smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY id (userid),
  KEY department_id (department_id),
  KEY role_id (role_id),
  KEY still_working (still_working)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_office(
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(250) NOT NULL,
  leader tinyint(1) unsigned NOT NULL DEFAULT 0,
  manager tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Trưởng phòng hay không',
  weight smallint(5) unsigned NOT NULL DEFAULT 0,
  description varchar(250) DEFAULT '',
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_task (
  id INT NOT NULL AUTO_INCREMENT COMMENT 'ID' ,
  taskcat_id INT NOT NULL DEFAULT '0' COMMENT 'cat ID' ,
  title TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Tiêu đề công việc',
  userid_assigned INT NOT NULL DEFAULT '0' COMMENT 'Nhân viên được giao' ,
  status INT NOT NULL DEFAULT '0' COMMENT 'Trạng thái',
  link VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Link',
  content TEXT NOT NULL DEFAULT '' COMMENT 'Nội dung công việc',
  description TEXT NOT NULL DEFAULT '' COMMENT 'Ghi chú',
  time_create INT NOT NULL DEFAULT '0' COMMENT 'Thời gian tạo',
  work_time DOUBLE NOT NULL DEFAULT '0' COMMENT 'Thời gian xử lý',
  work_type tinyint(1) NOT NULL DEFAULT '0' COMMENT '0,1,2: Việc tự nhập, việc được giao, việc lịch cố định',
  over_time DOUBLE NOT NULL DEFAULT '0' COMMENT 'Số giờ làm thêm',
  accepted_time DOUBLE NOT NULL DEFAULT '0' COMMENT 'Số giờ tính công',
  set_time DOUBLE UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Chỉ định số công được chấm',
  set_userid INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Người chỉ định số công',
  set_date INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thời gian chỉ định số công',
  assigned_task int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID việc được giao',
  schedule_task int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID việc cố định',
  PRIMARY KEY (id),
  KEY userid_assigned (userid_assigned),
  KEY time_create (time_create),
  KEY taskcat_id (taskcat_id),
  KEY work_type (work_type),
  KEY assigned_task (assigned_task),
  KEY schedule_task (schedule_task),
  KEY set_time (set_time)
) ENGINE=InnoDB";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_task_assign (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    taskcat_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID dự án',
    userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người được giao, cần INNER JOIN vào bảng human_resources để lấy department_id',
    assignee_userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người giao việc, cần INNER JOIN vào bảng human_resources để lấy department_id',
    title varchar(190) NOT NULL COMMENT 'Tên công việc',
    link varchar(255) NOT NULL DEFAULT '' COMMENT 'Liên kết',
    from_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Làm từ ngày',
    to_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Đến ngày',
    work_content text NULL DEFAULT NULL COMMENT 'Nội dung công việc',
    work_note text NULL DEFAULT NULL COMMENT 'Ghi chú',
    work_time double NOT NULL DEFAULT '0' COMMENT 'Số giờ giao việc',
    worked_time double NOT NULL DEFAULT '0' COMMENT 'Số giờ đã làm theo báo cáo',
    accepted_time double NOT NULL DEFAULT '0' COMMENT 'Số giờ đã được tính công',
    add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian thêm',
    edit_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 đã hủy, chưa hủy',
    PRIMARY KEY (id),
    KEY userid (userid),
    KEY assignee_userid (assignee_userid),
    KEY from_time (from_time),
    KEY to_time (to_time),
    KEY title (title),
    KEY add_time (add_time),
    KEY deleted (deleted)
) ENGINE=InnoDB COMMENT 'Giao việc'";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_task_change (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    task_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID báo cáo',
    userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người thay đổi',
    add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
    time_new double NOT NULL DEFAULT '0' COMMENT 'Giờ mới',
    time_old double NOT NULL DEFAULT '0' COMMENT 'Giờ cũ',
    change_reason text NULL DEFAULT NULL COMMENT 'Nguyên nhân sửa',
    PRIMARY KEY (id),
    KEY task_id (task_id)
) ENGINE=InnoDB COMMENT 'Lịch sử sửa giờ chấm công việc'";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_task_assign_change (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    assign_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID giao việc',
    userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người thay đổi',
    add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
    time_new double NOT NULL DEFAULT '0' COMMENT 'Giờ cũ',
    time_old double NOT NULL DEFAULT '0' COMMENT 'Giờ mới',
    change_reason text NULL DEFAULT NULL COMMENT 'Nguyên nhân sửa',
    PRIMARY KEY (id),
    KEY assign_id (assign_id)
) ENGINE=InnoDB COMMENT 'Lịch sử sửa giờ giao việc'";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_taskcat(
  id smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(250) NOT NULL,
  alias varchar(250) NOT NULL,
  parentid smallint(5) unsigned NOT NULL,
  sort smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Thứ tự toàn bộ',
  lev smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Cấp mấy, cấp cha thì=0',
  numsubcat smallint(5) NOT NULL DEFAULT '0' COMMENT 'Số item con trong nó',
  subcatid varchar(255) NOT NULL DEFAULT '' COMMENT 'ID item con của nó phân cách bởi dấu phảy',
  status tinyint(1) unsigned NOT NULL DEFAULT 1,
  weight int(11) unsigned NOT NULL DEFAULT 0,
  description varchar(250) DEFAULT '',
  department INT NOT NULL DEFAULT '0' COMMENT 'Thuộc phòng ban',
  users_assigned VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Danh sách nhân viên thuộc dự án',
  project_public tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Dự án chung',
  project_accounting tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Dự án hoạch toán',
  PRIMARY KEY (id),
  KEY users_assigned (users_assigned),
  KEY project_public (project_public),
  KEY department (department)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_working(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL,
  taskid int(11) unsigned NOT NULL,
  check_id int(11) unsigned NOT NULL,
  spend int(11) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_workout(
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Người đăng lý',
  reason text NOT NULL COMMENT 'Lý do làm thêm',
  start_time int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Thời gian bắt đầu làm',
  end_time int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Thời gian kết thúc làm',
  confirm int(11) unsigned NOT NULL DEFAULT 0,
  confirm_userid int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'userid của leader xác nhận hoặc từ chối làm thêm',
  confirm_status TINYINT(11) UNSIGNED NOT NULL DEFAULT '0',
  confirm_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời điểm xác nhận hoặc từ chối',
  confirm_reason VARCHAR(250) NOT NULL DEFAULT '',
  deny_reason varchar(250) NOT NULL DEFAULT '' COMMENT 'Lý do từ chối',
  PRIMARY KEY (id),
  KEY userid (userid),
  KEY start_time (start_time),
  KEY end_time (end_time),
  KEY confirm (confirm)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_diary_penalize (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL DEFAULT 0,
  department_id int(11) unsigned NOT NULL  DEFAULT 0 COMMENT 'Thuộc phòng ban',
  reason text NOT NULL COMMENT 'Nguyên nhân',
  userid_add INT(11) unsigned NOT NULL DEFAULT 0,
  userid_confirm INT(11) unsigned NOT NULL DEFAULT 0,
  confirm TINYINT(4) unsigned NOT NULL DEFAULT 0,
  confirm_reason VARCHAR(250) NOT NULL DEFAULT '',
  confirm_time INT(11) unsigned NOT NULL DEFAULT 0,
  link text NOT NULL COMMENT 'Liên kết thực hiện hình phạt',
  add_link_time int(11) unsigned NOT NULL DEFAULT 0,
  penalize_time int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY userid (userid),
  KEY department_id (department_id)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_leave_absence (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  userid int(11) unsigned NOT NULL DEFAULT 0,
  reason_leave text NOT NULL COMMENT 'Lý do nghỉ phép',
  type_leave VARCHAR(10) NOT NULL DEFAULT '',
  date_leave int(11) unsigned NOT NULL DEFAULT 0,
  start_leave int(11) unsigned NOT NULL DEFAULT 0,
  end_leave int(11) unsigned NOT NULL DEFAULT 0,
  num_day DOUBLE NOT NULL DEFAULT 0 COMMENT 'Số ngày nghỉ phép',
  half_day_start DOUBLE NOT NULL DEFAULT 0 COMMENT 'Nghỉ một buổi của ngày bắt đầu 1:sáng, 2:chiều',
  half_day_end DOUBLE NOT NULL DEFAULT 0 COMMENT 'Nghỉ một buổi của ngày kết thúc 1:sáng, 2:chiều',
  use_absence_year TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'Sử dụng ngày nghỉ phép năm',
  userid_approved int(11) unsigned NOT NULL DEFAULT 0,
  confirm TINYINT(4) unsigned NOT NULL DEFAULT 0,
  deny_reason VARCHAR(250) NOT NULL DEFAULT '',
  approved_time int(11) unsigned NOT NULL DEFAULT 0,
  created_time int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY userid (userid)
) ENGINE=MyISAM";

$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_work_schedule (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người tạo lịch',
    title varchar(190) NOT NULL COMMENT 'Tên công việc',
    link varchar(255) NOT NULL DEFAULT '' COMMENT 'Liên kết',
    department_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Phòng ban của lịch này',
    apply_departments varchar(250) NOT NULL DEFAULT '' COMMENT 'Áp dụng cho các phòng ban',
    apply_allsubs tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 cho cả phòng ban con, 0 chỉ các phòng ban được chọn',
    apply_days varchar(100) NOT NULL DEFAULT '' COMMENT 'Thứ áp dụng 1,2,3..7. 1 là thứ 2',
    work_content text NULL DEFAULT NULL COMMENT 'Nội dung công việc',
    work_note text NULL DEFAULT NULL COMMENT 'Ghi chú',
    work_time double NOT NULL DEFAULT '0' COMMENT 'Thời gian làm việc',
    add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian thêm',
    edit_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
    status tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái 1 hoạt động, 0 đã hủy',
    deleted tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 đã hủy, chưa hủy',
    PRIMARY KEY (id),
    KEY userid (userid),
    KEY department_id (department_id),
    KEY apply_departments (apply_departments(191)),
    KEY apply_allsubs (apply_allsubs),
    KEY apply_days (apply_days),
    KEY title (title),
    KEY add_time (add_time),
    KEY status (status),
    KEY deleted (deleted)
) ENGINE=InnoDB COMMENT 'Lịch làm việc cố định'";

// Thang bậc nhân viên
$sql_create_module[] = "CREATE TABLE " . $db_config['prefix'] . "_" . $module_data . "_ranks (
  id smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(190) NOT NULL DEFAULT '' COMMENT 'Tiêu đề',
  description text NOT NULL COMMENT 'Note',
  monthly_salary DOUBLE unsigned NOT NULL DEFAULT '0' COMMENT 'Lương tháng',
  add_time int(11) unsigned NOT NULL DEFAULT '0',
  edit_time int(11) unsigned NOT NULL DEFAULT '0',
  weight smallint(4) unsigned NOT NULL DEFAULT '0',
  status tinyint(4) NOT NULL DEFAULT '1' COMMENT '0: Dừng, 1: Hoạt động',
  PRIMARY KEY (id),
  KEY weight (weight),
  KEY status (status),
  UNIQUE KEY title (title)
) ENGINE=InnoDB COMMENT 'Bậc nhân viên'";

//Bổ sung cấu hình làm việc ngày thứ 7
$sql_create_module[] = "INSERT INTO " . NV_CONFIG_GLOBALTABLE . " (lang, module, config_name, config_value) VALUES ('" . $lang . "', '" . $module_name . "', 'work_week', 'null')";
