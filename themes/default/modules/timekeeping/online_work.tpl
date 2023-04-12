<!-- BEGIN: main -->
<ul class="tree-staffs">
    <!-- BEGIN: department -->
    <li>
        <span class="department-name">{DEPARTMENT.title}</span>
        <ul class="tree-departments">
            <!-- BEGIN: staff -->
            <li>
                <i class="fa fa-user-circle-o text-success" aria-hidden="true"></i>
                <span class="tree-names">
                    <!-- BEGIN: link_openning --><a href="{LINK}" target="_blank"><!-- END: link_openning -->
                        {STAFF.show_name}
                    <!-- BEGIN: link_closing --></a><!-- END: link_closing -->
                </span>
                <ul class="tree-works">
                    <!-- BEGIN: work -->
                    <li>
                        <i class="fa fa-caret-right text-success" aria-hidden="true"></i> <span title="{taskcat_title}">{task_title}</span>
                    </li>
                    <!-- END: work -->
                </ul>
            </li>
            <!-- END: staff -->
        </ul>
    </li>
    <!-- END: department -->
</ul>
<!-- BEGIN: empty -->
<strong class="text-info">{LANG.no_staff_online}</strong>
<!-- END: empty -->
<!-- END: main -->
