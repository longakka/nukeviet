<!-- BEGIN: main -->
<link rel="stylesheet" href="{NV_BASE_SITEURL}themes/{TEMPLATE_CSS}/css/{MODULE_FILE}.css" type="text/css">
<ul class="tree-staffs">
    <!-- BEGIN: department -->
    <li>
        <span class="department-name">{DEPARTMENT.title}</span>
        <ul>
            <!-- BEGIN: loop -->
            <li>
                <i class="fa fa-user-circle-o text-success" aria-hidden="true"></i> <span>{STAFF.show_name}</span>
            </li>
            <!-- END: loop -->
        </ul>
    </li>
    <!-- END: department -->
</ul>
<!-- BEGIN: empty -->
<strong class="text-info">{LANG.no_staff_online}</strong>
<!-- END: empty -->
<!-- END: main -->
