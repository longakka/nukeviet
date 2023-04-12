# Ghi chú module

Vì module này được viết theo hướng đối tượng. Đã tạo 1 thư mục modules/timekeeping/classs chứa các file Class của module. Để sử dụng được các class của module này thì trước khi cài đặt module cần  

## 1. Sửa file composer.json thêm cái `"Modules\\": "modules"` vào đoạn này

```
"autoload": {
        "psr-4": {
            "NukeViet\\": "vendor/vinades/nukeviet",
            "Modules\\": "modules"
        },
        "classmap": [
            "vendor/vinades/pclzip/pclzip.lib.php"
        ]
    },
```

phần "autoload" thêm "Modules\\": "modules"

## 2. cập nhật lại composer

```
composer dump-autoload
```

Tài liệu hướng dẫn sử dụng:

https://docs.google.com/document/d/1qeWjeRG3WzXVdt9YiAjsgAp5o7qBymOW/edit
## 3. Trong ứng dụng có sử dụng api xác định vị trí dựa trên kinh độ, vĩ độ của https://opencagedata.com/
Để sử dụng bạn cần lấy api_key của nó và cập nhật vào class Checkio.php -> opencagedata_key
Trước khi chạy cần cập nhật lại composer 
```
$ composer require opencage/geocode
```

## 4. Giới thiệu hoạt động của prroject 
Liên quan đến các table: nv4_timekeeping_taskcat, nv4_timekeeping_task, timekeeping_task_assign, nv4_timekeeping_task_assign_change, nv4_timekeeping_task_change
### 4.1. Tạo project
Quản lý vào link https://timekeeping.my/timekeeping/project/ để xem các dự án. 
Bấm vào **"Thêm dự án"** để đến link tạo các dự án. Dự án sẽ được lưu vào table **nv4_timekeeping_taskcat**
### 4.2. Giao việc cho nhân viên
Quản lý vào link https://timekeeping.my/timekeeping/assign-work/ để xem các việc được giao cho nhân viên. Bấm vào **"Giao việc mới"** đến link https://timekeeping.my/timekeeping/assign-work-content/ để giao việc cho nhân viên. 

Việc giao cho nhân viên sẽ được lưu ở table **timekeeping_task_assign**
### 4.3. Sửa thời gian giao việc
Khi quản lý có thay đổi về thời gian giao việc cho nhân viên (https://timekeeping.my/timekeeping/assign-work-content/?id=xxxx) trong quá trình sửa giao việc. 

Thì thời gian sửa này sẽ được lưu vào table **nv4_timekeeping_task_assign_change**
### 4.4. Khai báo công việc làm hằng ngày
Nhân viên sau ngay làm việc thì khai báo công việc làm hằng ngày (https://timekeeping.my/timekeeping/work/). 

Nội dung khai báo sẽ được lưu ở table **nv4_timekeeping_task**
### 4.5. Điều chỉnh thời gian tính công cho nhân sự
Quản lý kiểm tra công làm của nhân sự (https://timekeeping.my/timekeeping/work-report/), nếu muốn thay đổi thời gian tính công cho nhân sự ở một nhiệm vụ nào đó thì bấm vào số giờ tính công để nhập giờ công mới. 


Thông tin thay đổi được lưu vào table **nv4_timekeeping_task_change**
### 4.5. Kiểm tra các thay đổi
1. Những nhiệm vụ được giao mà có sự thay đổi (lưu ở table **nv4_timekeeping_task_assign_change**) thì khi lãnh đạo vào xem danh sách giao việc (https://timekeeping.my/timekeeping/assign-work/) thì nhiệm vụ đó sẽ xuất hiện icon (fa fa-history), html ở file assign-work.tpl (main.loop.isedit). bấm vào đó để thấy lịch sử sửa chữa.
2. Những nhân việc được giao việc (thực hiện ở mục 4.2) thì khi nhân viên đó vào khai báo công việc làm hằng ngày (https://timekeeping.my/timekeeping/work/) và chọn **"Làm việc được giao"** sẽ hiện ra các công việc được giao.
