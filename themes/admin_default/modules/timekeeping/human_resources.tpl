<!-- BEGIN: human_resources_info -->
<img style="float:left; margin-right: 10px" src="{HUMAN_RESOURCES.photo}" alt="{LANG.image}" width="100" height="100" />
<p><strong>{GLANG.full_name}: </strong>{HUMAN_RESOURCES.full_name}</p>
<p><strong>email: </strong>{HUMAN_RESOURCES.email}</p>
<p><strong>{LANG.birthday}: </strong>{HUMAN_RESOURCES.birthday}</p>
<p><strong>{LANG.gender}: </strong>{HUMAN_RESOURCES.gender}</p>
<!-- END: human_resources_info -->
<!-- BEGIN: list_department -->
<ul class="list-unstyled">
    <!-- BEGIN: loop -->
    <li>
        <input type="checkbox" name="confirm_workout[]" value="{DEPARTMENT.id}" {DEPARTMENT.checked}> {DEPARTMENT.name}
    </li>
    <!-- END: loop -->
</ul>
<!-- END: list_department -->
<!-- BEGIN: list_department_penalize -->
<ul class="list-unstyled">
    <!-- BEGIN: loop -->
    <li>
        <input type="checkbox" name="view_diary_penalize[]" value="{DEPARTMENT.id}" {DEPARTMENT.checked}> {DEPARTMENT.name}
    </li>
    <!-- END: loop -->
</ul>
<!-- END: list_department_penalize -->
<!-- BEGIN: list_department_absence -->
<ul class="list-unstyled">
    <!-- BEGIN: loop -->
    <li>
        <input type="checkbox" name="view_leave_absence[]" value="{DEPARTMENT.id}" {DEPARTMENT.checked}> {DEPARTMENT.name}
    </li>
    <!-- END: loop -->
</ul>
<!-- END: list_department_absence -->
<!-- BEGIN: list_department_report -->
<ul class="list-unstyled">
    <!-- BEGIN: loop -->
    <li>
        <input type="checkbox" name="view_department_report[]" value="{DEPARTMENT.id}" {DEPARTMENT.checked}> {DEPARTMENT.name}
    </li>
    <!-- END: loop -->
</ul>
<!-- END: list_department_report -->
<!-- BEGIN: human_resources_add -->
<div id="addContent">
    <form id="addHuman_resources" method="post" action="{ACTION_URL}">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <colgroup>
                    <col class="w200" />
                </colgroup>
                <tbody>
                    <tr>
                        <td>{LANG.choice_human_resources}: <sup class="required">(*)</sup></td>
                        <td>
                            <input class="form-control pull-left" name="username" id="username" type="text" readonly value="{HUMAN_RESOURCES.username}" maxlength="20" style="width: 180px" />&nbsp;
                            <input type="button" value="{LANG.add_select}" {disabled_select} onclick="open_browse_us()" class="btn btn-default" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>{LANG.info_basic}</strong>
                            <span>
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-refresh fa-lg" onclick="nv_get_info_basic()">&nbsp;</i>
                                </button>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="info_basic">
                                {HUMAN_RESOURCES_BASIC}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>{LANG.info_extends}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.department}</td>
                        <td>
                            <select class="form-control w300" title="{LANG.department}" name="department_id">
                                <option value="0">{LANG.department}</option>
                                <!-- BEGIN: listcat -->
                                <option value="{ROW.id}" {selected}>{ROW.name}</option>
                                <!-- END: listcat -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.office}</td>
                        <td>
                            <select class="form-control w300" title="{LANG.catParent}" name="office_id">
                                <option value="0">{LANG.office_select}</option>
                                <!-- BEGIN: list_office -->
                                <option value="{ROW.id}" {selected} data-leader="{ROW.leader}">{ROW.title}</option>
                                <!-- END: list_office -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.hr_still_working}</td>
                        <td>
                            <label class="mb-0"><input type="checkbox" name="still_working" value="1" {HUMAN_RESOURCES.still_working}> {LANG.hr_still_working1}</label>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.hr_no_aswork}</td>
                        <td>
                            <label class="mb-0"><input type="checkbox" name="no_aswork" value="1" {HUMAN_RESOURCES.no_aswork}> {LANG.hr_no_aswork1}</label>
                        </td>
                    </tr>
                    <tr id="container-list-department" {department_d_none}>
                        <td>{LANG.confirm_workout}</td>
                        <td>
                            <div id="list-department">
                                {LIST_DEPARTMENT}
                            </div>
                        </td>
                    </tr>
                    <tr id="container-list-department-penalize">
                        <td>{LANG.view_diary_penalize}</td>
                        <td>
                            <div id="list-department-penalize">
                                {LIST_DEPARTMENT_PENALIZE}
                            </div>
                        </td>
                    </tr>
                    <tr id="container-list-department-absence">
                        <td>{LANG.view_leave_absence}</td>
                        <td>
                            <div id="list-department-absence">
                                {LIST_DEPARTMENT_ABSENCE}
                            </div>
                        </td>
                    </tr>
                    <tr id="container-list-department-absence">
                        <td>{LANG.view_work_report}</td>
                        <td>
                            <div id="list-department-absence">
                                {LIST_DEPARTMENT_REPORT}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.phone}</td>
                        <td>
                            <input class="form-control  w300" type="text" name="phone" value="{HUMAN_RESOURCES.phone}" />
                        </td>
                    </tr>
                    <tr>
                        <td>CMND</td>
                        <td>
                            <input class="form-control  w300" type="text" name="cmnd" value="{HUMAN_RESOURCES.cmnd}" />
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.address}</td>
                        <td>
                            <input class="form-control w300" type="text" name="address" value="{HUMAN_RESOURCES.address}" />
                        </td>
                    </tr>
                    <!-- BEGIN: role -->
                    <tr>
                        <td>{LANG.hr_role}</td>
                        <td>
                            <!-- BEGIN: loop -->
                            <div class="checkbox">
                                <label><input type="checkbox" name="role_id[]" value="{ROLE.id}" {ROLE.checked}> {ROLE.title}</label>
                            </div>
                            <!-- END: loop -->
                        </td>
                    </tr>
                    <!-- END: role -->
                    <tr>
                        <td>{LANG.rank}</td>
                        <td class="form-inline">
                            <select class="form-control" name="rank_id">
                                <option value="0">----</option>
                                <!-- BEGIN: rank -->
                                <option value="{RANK.id}" {RANK.selected}>{RANK.title}</option>
                                <!-- END: rank -->
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="id" value="{HUMAN_RESOURCES.userid}" />
        <input type="hidden" name="weight" value="{HUMAN_RESOURCES.weight}" />
        <input type="hidden" name="checkss" value="{CHECKSS}" />
        <input type="hidden" name="save" value="1" />
        <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" />
    </form>
</div>
<script type="text/javascript">
    //<![CDATA[
    function open_browse_us() {
        nv_open_browse('{NV_BASE_ADMINURL}index.php?' + nv_name_variable + '=users&' + nv_fc_variable + '=getuserid&area=username&return=username&filtersql={FILTERSQL}', 'NVImg', 850, 500, 'resizable=no,scrollbars=no,toolbar=no,location=no,status=no');
    }

    function nv_get_info_basic() {
        var username = $("input[name=username]").val();
        username = trim(username);
        if (username == '') return !1;
        $.ajax({
            type: "POST",
            url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&op={OP}&info_basic=1&nocache=' + new Date().getTime(),
            data: "username=" + username,
            dataType: "html",
            success: function(res) {
                $("#info_basic").html(res);
            }
        });

    }
    $("form#addHuman_resources").submit(function() {
        var url = $(this).attr("action");
        url += '&nocache=' + new Date().getTime();
        var username = $("input[name=username]").val();
        username = trim(username);
        if (username == '') {
            alert('{LANG.errorYouNeedSelectHumanResource}');
            $("input[name=username]").select();
            return !1;
        }
        a = $(this).serialize();
        $("input[name=submit]").attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: url,
            data: a,
            success: function(b) {
                if (b == "OK") {
                    window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources';
                } else {
                    alert(b);
                    $("input[name=submit]").removeAttr("disabled");
                }
            }
        });
        return !1;
    });
    $("select[name=office_id]").change(function(e) {
        var leader = $(this).find(':selected').data('leader');
        if (leader == 1) {
            $('#container-list-department').fadeIn();
        } else {
            $('#container-list-department').fadeOut();
        }
    });
    $("select[name=department_id]").change(function(e) {
        var department_id = $(this).val();
        var userid = $("input[name=id]").val();
        $("#list-department").load(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources_add&list_department=1&department_id=' + department_id + '&userid=' + userid);
    });
    //]]>
</script>
<!-- END: human_resources_add -->
<!-- BEGIN: main -->
<form action="{NV_BASE_ADMINURL}index.php" method="get">
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}" />
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}" />
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}" />
    <input type="hidden" name="per_page" value="{search_of_per_page}" />
    <div class="row">
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <input type="text" name="name" class="form-control" value="{search_name}" placeholder="{GLANG.your_account}">
            </div>
        </div>
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <input type="text" name="email" class="form-control" value="{search_email}" placeholder="Email">
            </div>
        </div>
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <select name="department_id" class="form-control">
                    <option value="0">{LANG.department}</option>
                    <!-- BEGIN: search_department -->
                    <option value="{department_id}" {selected}>{department_title}</option>
                    <!-- END: search_department -->
                </select>
            </div>
        </div>
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <select name="office_id" class="form-control">
                    <option value="0">{LANG.office}</option>
                    <!-- BEGIN: search_office -->
                    <option value="{office_id}" {selected}>{office_title}</option>
                    <!-- END: search_office -->
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <div class="checkbox">
                    <label><input type="checkbox" value="1" name="cw"{CONFIRM_WORKOUT}> {LANG.hr_approval_other}</label>
                </div>
            </div>
        </div>
        <div class="col-xs-24 col-md-6">
            <div class="form-group">
                <input class="btn btn-primary loading" type="submit" value="{LANG.search_submit}" />
            </div>
        </div>
    </div>
</form>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <colgroup>
            <col class="w100" />
            <col class="w50" />
            <col />
            <col class="w250" />
            <col class="w150" span="3" />
        </colgroup>
        <thead>
            <tr>
                <th> {LANG.pos} </th>
                <th> {LANG.image} </th>
                <th> {GLANG.full_name} </th>
                <th> email </th>
                <th> {LANG.department} </th>
                <th> {LANG.office} </th>
                <th class="text-center">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td>
                    <input type="text" class="form-control" value="{ROW.weight}" name="weight" data-id="{ROW.id}" />
                </td>
                <td>
                    <img src="{ROW.photo}" alt="{LANG.image}" width="50" height="50" />
                </td>
                <td>
                    <strong>{ROW.full_name}</strong>
                    <!-- BEGIN: rank -->
                    <div>
                        <small>{ROW.rank}: <strong class="text-danger">{ROW.monthly_salary}{LANG.rank_unit}</strong></small>
                    </div>
                    <!-- END: rank -->
                </td>
                <td>
                    {ROW.email}
                </td>
                <td>
                    {ROW.department_title}
                </td>
                <td>
                    {ROW.office_title}
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-xs">
                        <a href="{MODULE_URL}&{NV_OP_VARIABLE}=human_resources_add&userid={ROW.userid}" class="btn btn-sm btn-default"><i class="fa fa-pencil"></i> {GLANG.edit}</a>
                        <a class="del btn btn-sm btn-default" href="{ROW.id}"><i class="fa fa-trash text-danger"></i> <span class="text-danger">{GLANG.delete}</span></a>
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right text-left">
                            <li><a href="#" data-toggle="hmrsnotislack" data-id="{ROW.userid}" data-tokend="{TOKEND}"><i class="fa fa-fw fa-slack" aria-hidden="true"></i> {LANG.hmrs_check_slack}</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<form class="form-inline">
    <div class="form-group">
        <label for="exampleInputEmail1">{LANG.number_member_in_page}: </label>
        <select name="per_page" class="form-control">
            <!-- BEGIN: per_page_loop -->
            <option value="{per_page_number}" {selected}> {per_page_number} </option>
            <!-- END: per_page_loop -->
        </select>
    </div>
</form>
<!-- BEGIN: generate_page -->
<div class="text-center">{GENERATE_PAGE}</div>
<!-- END: generate_page -->
<a class="btn btn-primary" target="_self" href="{MODULE_URL}&op=human_resources_add">{LANG.human_resources_add}</a>
<script type="text/javascript">
    //<![CDATA[
    $("a.del").click(function(event) {
        event.preventDefault();
        confirm("{LANG.delConfirm} ?") && $.ajax({
        type: "POST",
            url: script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources&nocache=' + new Date().getTime(),
            data : "delete=" + $(this).attr("href")+ "&checkss={CHECKSS}",
            success: function(a) {
                a == "OK" ? window.location.href = window.location.href : alert(a);
            }
    });
    return !1;
    });
    $("input[name=weight]").change(function() {
        var id = $(this).data("id"),
            newWeight = $(this).val(),
            c = this;
        $(this).attr("disabled", "disabled");
        $.ajax({
            type: "POST",
            url: script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources&nocache=' + new Date().getTime(),
            data: "cWeight=" + newWeight + "&id=" + id,
            success: function(a) {
                a == "OK" ?  window.location.href =  window.location.href : alert("{LANG.errorChangeWeight}");
                $(c).removeAttr("disabled");
            }
        });
        return !1;
    });
    $("select[name=per_page").change(function() {
        var $per_page = $(this).val();
        if ($per_page > 0) {
            window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources&per_page=' + $per_page;
        }
    })
    //]]>
</script>
<!-- END: main -->
