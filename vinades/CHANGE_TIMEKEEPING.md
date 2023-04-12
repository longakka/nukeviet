# Ghi chú cập nhật CSDL
## 14/02/2023
```sql
ALTER TABLE `nv4_timekeeping_taskcat` ADD `sort` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thứ tự toàn bộ' AFTER `parentid`, ADD `lev` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Cấp mấy, cấp cha thì=0' AFTER `sort`, ADD `numsubcat` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số item con trong nó' AFTER `lev`, ADD `subcatid` VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'ID item con của nó phân cách bởi dấu phảy' AFTER `numsubcat`;
```
## 11/02/2023
```sql
ALTER TABLE `nv4_timekeeping_taskcat` ADD `project_accounting` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Dự án hoạch toán' AFTER `project_public`;
```
## 06/02/2023
```sql
ALTER TABLE `nv4_timekeeping_human_resources` ADD `view_department_report` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `view_leave_absence`;
ALTER TABLE `nv4_timekeeping_human_resources` ADD `rank_id` TINYINT(2) UNSIGNED NULL AFTER `department_id`;
```

## 08/01/2023
```sql
CREATE TABLE nv4_timekeeping_ranks (
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
) ENGINE=InnoDB COMMENT 'Bậc nhân viên';
ALTER TABLE `nv4_timekeeping_human_resources` ADD rank_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID bậc' AFTER `role_id`;
```

## 30/12/2022
```sql
ALTER TABLE `nv4_timekeeping_checkio` ADD `geolat` DOUBLE NULL AFTER `late`, ADD `geolng` DOUBLE NULL AFTER `geolat`;
```
## 23/12/2022
```sql
ALTER TABLE `nv4_timekeeping_task_assign` ADD `completed` TINYINT(2) NOT NULL DEFAULT '0' AFTER `accepted_time`;
```
## 25/11/2022 Dũng thêm trường trưởng phòng

ALTER TABLE `nv4_timekeeping_office` ADD manager tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Trưởng phòng hay không' AFTER `leader`;

DROP TABLE IF EXISTS nv4_timekeeping_work_schedule;
CREATE TABLE nv4_timekeeping_work_schedule (
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
) ENGINE=InnoDB COMMENT 'Lịch làm việc cố định';

DROP TABLE IF EXISTS nv4_timekeeping_task_assign;
CREATE TABLE nv4_timekeeping_task_assign (
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
) ENGINE=InnoDB COMMENT 'Giao việc';

DROP TABLE IF EXISTS nv4_timekeeping_task_assign_change;
CREATE TABLE nv4_timekeeping_task_assign_change (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  assign_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID giao việc',
  userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người thay đổi',
  add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
  time_new double NOT NULL DEFAULT '0' COMMENT 'Giờ cũ',
  time_old double NOT NULL DEFAULT '0' COMMENT 'Giờ mới',
  change_reason text NULL DEFAULT NULL COMMENT 'Nguyên nhân sửa',
  PRIMARY KEY (id),
  KEY assign_id (assign_id)
) ENGINE=InnoDB COMMENT 'Lịch sử sửa giờ giao việc';

ALTER TABLE nv4_timekeeping_task
ADD work_type tinyint(1) NOT NULL DEFAULT '0' COMMENT '0,1,2: Việc tự nhập, việc được giao, việc lịch cố định',
ADD assigned_task int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID việc được giao',
ADD schedule_task int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID việc cố định',
ADD INDEX work_type (work_type),
ADD INDEX assigned_task (assigned_task),
ADD INDEX schedule_task (schedule_task);

ALTER TABLE `nv4_timekeeping_task` ADD `accepted_time` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Số giờ tính công' AFTER `over_time`;
UPDATE `nv4_timekeeping_task` SET `accepted_time` = work_time + over_time;

ALTER TABLE `nv4_timekeeping_department` ADD `assign_work` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Chấm công dạng giao việc hay không' AFTER `checkin_out_session`;
ALTER TABLE `nv4_timekeeping_task` ADD `set_time` DOUBLE UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Chỉ định số công được chấm' AFTER `accepted_time`, ADD `set_userid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Người chỉ định số công' AFTER `set_time`, ADD `set_date` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thời gian chỉ định số công' AFTER `set_userid`;
ALTER TABLE `nv4_timekeeping_task` ADD INDEX( `set_time`);
ALTER TABLE `nv4_timekeeping_human_resources` ADD `no_aswork` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Không làm dạng giao việc dẫu cho phòng ban có giao việc' AFTER `view_leave_absence`;

DROP TABLE IF EXISTS nv4_timekeeping_task_change;
CREATE TABLE nv4_timekeeping_task_change (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  task_id int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'ID báo cáo',
  userid int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Người thay đổi',
  add_time int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời gian sửa',
  time_new double NOT NULL DEFAULT '0' COMMENT 'Giờ cũ',
  time_old double NOT NULL DEFAULT '0' COMMENT 'Giờ mới',
  change_reason text NULL DEFAULT NULL COMMENT 'Nguyên nhân sửa',
  PRIMARY KEY (id),
  KEY task_id (task_id)
) ENGINE=InnoDB COMMENT 'Lịch sử sửa giờ chấm công việc';

## 01/09/2022 Dũng bổ sung trường vào bảng nhân viên

```sql
ALTER TABLE nv4_timekeeping_human_resources
ADD role_id smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'ID quyền' AFTER userid,
ADD still_working tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 là nghỉ làm, 1 là đang làm' AFTER role_id,
ADD INDEX role_id (role_id),
ADD INDEX still_working (still_working);

UPDATE nv4_timekeeping_human_resources SET still_working=0 WHERE userid IN(
    SELECT userid FROM nv4_users WHERE active=0
);

ALTER TABLE `nv4_timekeeping_human_resources` CHANGE `role_id` `role_id` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'IDs quyền';
```

## 26/07/22 ngocngan2803: thêm field use_absence_year vào table nv4_timekeeping_leave_absence
```sql
ALTER TABLE `nv4_timekeeping_leave_absence`
ADD `use_absence_year` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'Sử dụng ngày nghỉ phép năm' AFTER `half_day_end`;
```

## 20/07/22 ngocngan2803: thêm field num_day vào table nv4_timekeeping_leave_absence
```sql
ALTER TABLE `nv4_timekeeping_leave_absence`
ADD `num_day` DOUBLE NOT NULL DEFAULT 0 COMMENT 'Số ngày nghỉ phép' AFTER `end_leave`,
ADD `half_day_start` DOUBLE NOT NULL DEFAULT 0 COMMENT 'Nghỉ một buổi của ngày bắt đầu 1:sáng, 2:chiều' AFTER `num_day`,
ADD `half_day_end` DOUBLE NOT NULL DEFAULT 0 COMMENT 'Nghỉ một buổi của ngày kết thúc 1:sáng, 2:chiều' AFTER `half_day_start`;
```

## 15/07/2022 ngocngan2803: thêm field view_all_absence vào table nv4_timekeeping_department
```sql
ALTER TABLE `nv4_timekeeping_department`
ADD `view_all_absence` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'Cho phép xem nghỉ phép của các phòng'
AFTER `view_all_task`;
```

- Chạy đoạn SQL bên trên
- Vào quản trị của module, cấu hình email nhập email văn phòng và lưu lại để nhận cấu hình mới.
- Bấm vào tên module này ở phần danh sách module để nhận function mới leave-absence-department.php và sau đó cấu hình layout thích hợp cho nó.

## 12/07/2022 ngocngan2803: tạo table nv4_timekeeping_leave_absence, thêm field view_leave_absence vào table nv4_timekeeping_human_resources
```sql
CREATE TABLE `nv4_timekeeping_leave_absence` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT 0,
  `reason_leave` text NOT NULL COMMENT 'Lý do nghỉ phép',
  `type_leave` VARCHAR(10) NOT NULL DEFAULT '',
  `date_leave` int(11) unsigned NOT NULL DEFAULT 0,
  `start_leave` int(11) unsigned NOT NULL DEFAULT 0,
  `end_leave` int(11) unsigned NOT NULL DEFAULT 0,
  `userid_approved` int(11) unsigned NOT NULL DEFAULT 0,
  `confirm` TINYINT(4) unsigned NOT NULL DEFAULT 0,
  `deny_reason` VARCHAR(250) NOT NULL DEFAULT '',
  `approved_time` int(11) unsigned NOT NULL DEFAULT 0,
  `created_time` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY userid (userid)
) ENGINE=MyISAM;

ALTER TABLE `nv4_timekeeping_human_resources` ADD `view_leave_absence` VARCHAR(250) NOT NULL DEFAULT '' AFTER `view_diary_penalize`;
```
## 09/07/2022 ngocngan2803: add field confirm_time, add_link_time table nv4_timekeeping_diary_penalize
```sql
ALTER TABLE `nv4_timekeeping_diary_penalize`
ADD `confirm_time` int(11) unsigned NOT NULL DEFAULT 0 AFTER `confirm_reason`,
ADD `add_link_time` int(11) unsigned NOT NULL DEFAULT 0 AFTER `link`;
```

## 05/07/2022 ngocngan2803: Xóa field office_id table nv4_timekeeping_diary_penalize
```sql
ALTER TABLE `nv4_timekeeping_diary_penalize` DROP `office_id`
```

## 30/06/2022 ngocngan2803: Bổ sung field userid_add, userid_confirm, confirm table nv4_timekeeping_diary_penalize
```sql
ALTER TABLE `nv4_timekeeping_diary_penalize`
ADD `userid_add` INT(11) unsigned NOT NULL DEFAULT 0 AFTER `reason`,
ADD `userid_confirm` INT(11) unsigned NOT NULL DEFAULT 0 AFTER `userid_add`,
ADD `confirm` TINYINT(4) unsigned NOT NULL DEFAULT 0 AFTER `userid_confirm`,
ADD `confirm_reason` VARCHAR(250) NOT NULL DEFAULT '' AFTER `confirm`,
ADD `link` text NOT NULL COMMENT 'Liên kết thực hiện hình phạt' AFTER `confirm`;
```

## 22/06/2022 ngocngan2803: Tạo bảng nv4_timekeeping_diary_penalize
```sql
CREATE TABLE `nv4_timekeeping_diary_penalize` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT 0,
  `department_id` int(11) unsigned NOT NULL  DEFAULT 0 COMMENT 'Thuộc phòng ban',
  `office_id` int(11) unsigned NOT NULL DEFAULT 0,
  `reason` text NOT NULL COMMENT 'Nguyên nhân',
  `penalize_time` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
) ENGINE=MyISAM;
```

## 20/06/2022 ngocngan2803: Bổ sung xem nhật ký phạt
```sql
ALTER TABLE `nv4_timekeeping_human_resources` ADD `view_diary_penalize` VARCHAR(250) NOT NULL DEFAULT '' AFTER `confirm_workout`;
```

## 10/06/2022 ngocngan2803: Bổ sung thời điểm xác nhận/ từ chối, lý do từ chối, userid xác nhận/từ chối
```sql
ALTER TABLE `nv4_timekeeping_workout`
ADD `confirm_userid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'userid của leader xác nhận hoặc từ chối làm thêm' AFTER `confirm`,
ADD `confirm_time` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thời điểm xác nhận hoặc từ chối' AFTER `confirm_userid`,
ADD `deny_reason` VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'Lý do từ chối' AFTER `confirm_time`;
```

## 10/06/2022 ngocngan2803: Bổ sung thời điểm xác nhận/ từ chối, lý do từ chối, trạng thái (0: default, 1: xác nhận, 2: từ chối), userid xác nhận/từ chối
```sql
ALTER TABLE `nv4_timekeeping_checkio`
ADD `confirm_userid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'userid của leader xác nhận hoặc từ chối làm thêm đột xuất' AFTER `confirm`,
ADD `confirm_status` TINYINT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `confirm_userid`,
ADD `confirm_time` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thời điểm xác nhận hoặc từ chối' AFTER `confirm_status`,
ADD `confirm_reason` VARCHAR(250) NOT NULL DEFAULT '' AFTER `confirm_time`;
```

## Dũng 12/03/2022: Đánh index

```sql
ALTER TABLE `nv4_timekeeping_task` ADD INDEX taskcat_id (`taskcat_id`);
ALTER TABLE `nv4_timekeeping_taskcat` ADD INDEX department (`department`);
```

## Dũng 04/03/2022: Đánh index bảng config

```sql
ALTER TABLE `nv4_timekeeping_config` ADD INDEX `userid` (`userid`);
ALTER TABLE `nv4_timekeeping_config` ADD INDEX `month` (`month`);
ALTER TABLE `nv4_timekeeping_config` ADD INDEX `year` (`year`);
ALTER TABLE `nv4_timekeeping_config` ADD INDEX `config_name` (`config_name`);
```

## Dũng 25/01/2022: Chỉnh lại phòng ban

```sql
ALTER TABLE `nv4_timekeeping_department` CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
```

## Dũng 24/01/2022: Chỉnh lại CSDL dự án

```sql
UPDATE `nv4_timekeeping_taskcat` SET `users_assigned`=REPLACE(users_assigned, '|', ',');
ALTER TABLE `nv4_timekeeping_taskcat` ADD INDEX `users_assigned` (`users_assigned`);
ALTER TABLE `nv4_timekeeping_taskcat` ADD INDEX `project_public` (`project_public`);
```

## Dũng 24/01/2022: Chỉnh lại bảng phòng ban để dễ xử lý

```sql
ALTER TABLE `nv4_timekeeping_department`
ADD sort smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `weight`,
ADD  lev smallint(5) unsigned NOT NULL DEFAULT '0' AFTER sort,
ADD  numsubcat smallint(5) NOT NULL DEFAULT '0' AFTER lev,
ADD  subcatid varchar(255) NOT NULL DEFAULT '' AFTER numsubcat;

UPDATE nv4_timekeeping_department SET sort = weight;
```

Xong vào quản lý phòng ban thay đổi thứ tự bất kỳ phòng ban nào

## Dũng 22/01/2022: Bổ sung cấu hình nhắc việc cho phòng ban

```
ALTER TABLE `nv4_timekeeping_department` ADD check_work tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Kiểm tra nhắc nhở việc' AFTER `view_all_task`;
ALTER TABLE `nv4_timekeeping_checkio` ADD INDEX `day` (`day`);
ALTER TABLE `nv4_timekeeping_checkio` ADD INDEX `month` (`month`);
ALTER TABLE `nv4_timekeeping_checkio` ADD INDEX `year` (`year`);
ALTER TABLE `nv4_timekeeping_checkio` ADD INDEX `userid1` (`userid`);
ALTER TABLE `nv4_timekeeping_human_resources` ADD INDEX `department_id` (`department_id`);

ALTER TABLE `nv4_timekeeping_task` ADD INDEX `userid_assigned` (`userid_assigned`);
ALTER TABLE `nv4_timekeeping_task` ADD INDEX `time_create` (`time_create`);
```

#Bá Tuấn 10/1/2022: Thay đổi kiểu dữ liệu "tên công việc"
ALTER TABLE `nv4_timekeeping_task` CHANGE `title` `title` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Tiêu đề công việc';

#Bá Tuấn 16/12:
ALTER TABLE `nv4_timekeeping_taskcat` ADD `users_assigned` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Danh sách nhân viên thuộc dự án' AFTER `department`;
ALTER TABLE `nv4_timekeeping_taskcat` ADD `project_public` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Dự án chung' AFTER `users_assigned`;

#Bá Tuấn 10/12:
ALTER TABLE `nv4_timekeeping_department` ADD `view_all_task` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Cho phép xem báo cáo các phòng' AFTER `weight`;

#Bá Tuấn 7/12:
ALTER TABLE `nv4_timekeeping_task` ADD `work_time` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Thời gian xử lý';
ALTER TABLE `nv4_timekeeping_task` ADD `over_time` DOUBLE NOT NULL DEFAULT '0' COMMENT 'Số giờ làm thêm';


#Bá Tuấn 6/12:
ALTER TABLE `nv4_timekeeping_taskcat` ADD `department` INT NOT NULL DEFAULT '0' COMMENT 'Thuộc phòng ban' AFTER `description`;
ALTER TABLE `nv4_timekeeping_task` ADD `content` TEXT NOT NULL DEFAULT '' COMMENT 'Nội dung công việc' AFTER `link`;

#Bá Tuấn 5/12:
*Xóa bảng 'nv4_timekeeping_task' cũ

DROP TABLE nv4_timekeeping_task;

*Tạo lại bảng nv4_timekeeping_task
CREATE TABLE `nv4_timekeeping_task` ( `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ID' , `taskcat_id` INT NOT NULL DEFAULT '0' COMMENT 'cat ID' , `title` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'Tiêu đề công việc' , `userid_assigned` INT NOT NULL DEFAULT '0' COMMENT 'Nhân viên được giao' , `status` INT NOT NULL DEFAULT '0' COMMENT 'Trạng thái' , `link` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Link' , `description` TEXT NOT NULL DEFAULT '' COMMENT 'Ghi chú' , `time_create` INT NOT NULL DEFAULT '0' COMMENT 'Thời gian tạo' , PRIMARY KEY (`id`)) ENGINE = InnoDB;





## 25/11/2021
```php
ALTER TABLE `nv4_timekeeping_department` ADD `checkin_out_session` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'checkin/out theo phiên' AFTER `weight`;
```
## 17/9/2021
```php
ALTER TABLE `nv4_timekeeping_checkio` ADD `confirm` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'userid của leader xác nhận làm thêm đột xuất' AFTER `workout_id`;
ALTER TABLE `nv4_timekeeping_workout` DROP IF EXISTS `userid_leader`;
ALTER TABLE `nv4_timekeeping_workout` CHANGE `confirm` `confirm` INT(11) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `nv4_timekeeping_human_resources` ADD `confirm_workout` VARCHAR(255) NULL DEFAULT NULL AFTER `department_id`;
```
## 30/7/2021 hqc210185: Bổ sung index cho nv4_timekeeping_config
```
CREATE INDEX config ON nv4_timekeeping_config (`year` DESC, `month` DESC, `id` DESC);
```
## 7/7/2021 hqc210185: Bổ sung workout_id để xác định checkin đó để làm thêm gì
```
ALTER TABLE `nv4_timekeeping_checkio` ADD `workout_id` INT(11) NOT NULL DEFAULT '0' AFTER `status`
```
## 28/6/2021 hqc210185: Bổ sung func check-not-checkout
```
INSERT INTO `nv4_vi_modfuncs` (`func_id`, `func_name`, `alias`, `func_custom_name`, `func_site_title`, `in_module`, `show_func`, `in_submenu`, `subweight`, `setting`) VALUES (NULL, 'check_not_checkout', 'check-not-checkout', 'Kiểm tra chưa checkout', 'Kiểm tra chưa checkout', 'timekeeping', '0', '0', '9', '');
```
## 7/6/2021 Thảo: Thêm index cho danh sách chấm công hàng ngày
```
ALTER TABLE `nv4_timekeeping_checkio` ADD INDEX( `userid`, `month`, `year`);
```

## 7/6/2021 trạng thái làm thêm
```
ALTER TABLE `nv4_timekeeping_checkio` ADD `status` ENUM('worktime', 'workout') NOT NULL DEFAULT 'worktime' AFTER `place`;
ALTER TABLE `nv4_timekeeping_checkio` ADD `description` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;
```
## 28/05/2021 bổ sung thời gian ở phần cấu hình
```sql
ALTER TABLE `nv4_timekeeping_config` ADD `month` SMALLINT(2) NULL AFTER `userid`, ADD `year` MEDIUMINT(5) NULL AFTER `month`;
```
## 05/05/2021 hqc210185: Bổ sung cấu hình làm việc ngày thứ 7
```sql
INSERT INTO `nv4_config` (`lang`, `module`, `config_name`, `config_value`) VALUES ('vi', 'timekeeping', 'work_week', NULL);
```
## 03/05/2021 dungpt: Bổ sung thời điểm click nút checkin, checkout thật

```sql
ALTER TABLE `nv4_timekeeping_checkio`
ADD checkin_real int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời điểm nhấn nút check-in thực tế' AFTER `checkin`,
ADD checkout_real int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Thời điểm nhấn nút check-out thực tế' AFTER `checkout`;
```
