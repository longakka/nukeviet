<!-- BEGIN: config_member_add -->
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2-bootstrap.min.css">
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{LANG.view_config}</h2>
    </div>
    <div class="panel-body">
        <form class="form-inline"  method="post" action="{ACTION_URL}&userid={userid}">
            <div class="form-group">
                <label for="month"><strong>{LANG.month}</strong> </label>
                <select class="form-control" id="month" name="month">
                    <!-- BEGIN: month -->
                    <option value="{MONTH.value}" {selected}>{MONTH.title}</option>
                    <!-- END: month -->
                </select>
            </div>
            <div class="form-group">
                <label for="year"><strong>{LANG.year}</strong> </label>
                <select class="form-control" id="year" name="year">
                    <!-- BEGIN: year -->
                    <option value="{YEAR.value}" {selected}>{YEAR.title}</option>
                    <!-- END: year -->
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">{LANG.select}</button>
            </div>
        </form>
    </div>
</div>
<form id="config" class="form-inline" role="form" action="{ACTION_URL}" method="post" autocomplete="off">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <colgroup>
                <col style="width: 50%" />
                <col style="width: 50%" />
            </colgroup>
            <tbody>
                <tr>
                    <th>{LANG.staff}</th>
                    <td>
                        <select class="form-control w400" name="userid" id="userid" title="{LANG.staff}">
                            <!--BEGIN: has_staff -->
                            <option selected="selected" value="{userid}">{full_name}</option>
                            <!--END: has_staff -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>{LANG.timePreAllow} ({GLANG.min}): </th>
                    <td><input class= "form-control w400" type="text" value="{timePreAllow}" name="timePreAllow" /></td>
                </tr>
                <tr>
                    <th>{LANG.timeAfterAllow} ({GLANG.min}):</th>
                    <td><input class= "form-control w400" type="text" value="{timeAfterAllow}" name="timeAfterAllow" /></td>
                </tr>
                <tr>
                    <th>{LANG.timeLateAllow} ({GLANG.min}):</th>
                    <td><input class= "form-control w400" type="text" value="{timeLateAllow}" name="timeLateAllow" /></td>
                </tr>
                <tr>
                    <th>{LANG.dayAllowOfWeek}:</th>
                    <td>
                        <!-- BEGIN: dayAllowOfWeek -->
                        <input type="checkbox" id="weekday_{WEEKDAY.value}" value="{WEEKDAY.value}" class="form-control" name="dayAllowOfWeek[]" {WEEKDAY.checked} value="{WEEKDAY.value}">
                        <label for="weekday_{WEEKDAY.value}"> {WEEKDAY.title}</label><br>
                        <!-- END: dayAllowOfWeek -->
                    </td>
                </tr>
                <tr>
                <th>{LANG.config_saturday}:</th>
                    <td>
                        <input type="checkbox" id="work_week_1" value="1" class="form-control" name="work_week[]" {checked_work_week_1}>
                        <label for="work_week_1"> {LANG.work_week_1}</label><br>
                        <input type="checkbox" id="work_week_2" value="2" class="form-control" name="work_week[]" {checked_work_week_2}>
                        <label for="work_week_2"> {LANG.work_week_2}</label><br>
                        <input type="checkbox" id="work_week_3" value="3" class="form-control" name="work_week[]" {checked_work_week_3}>
                        <label for="work_week_3"> {LANG.work_week_3}</label><br>
                        <input type="checkbox" id="work_week_4" value="4" class="form-control" name="work_week[]" {checked_work_week_4}>
                        <label for="work_week_4"> {LANG.work_week_4}</label><br>
                        <input type="checkbox" id="work_week_5" value="5" class="form-control" name="work_week[]" {checked_work_week_5}>
                        <label for="work_week_5"> {LANG.work_week_5}</label><br>
                    </td>
                </tr>
                <tr>
                    <th>{LANG.work_time}:</th>
                    <td>
                        <!-- BEGIN: session -->
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h3>{session_title}</h3>
                                <!-- BEGIN: time -->
                                <div>
                                    <strong>{time_title}:</strong>
                                    <select class="form-control" name="hour_{time}[]">
                                        <!-- BEGIN: hour -->
                                        <option value="{hour}" {selected}>{hour_title}</option>
                                        <!-- END: hour -->
                                    </select>
                                    <select class="form-control" name="minute_{time}[]">
                                        <!-- BEGIN: minute -->
                                        <option value="{minute}"  {selected}>{minute_title}</option>
                                        <!-- END: minute -->
                                    </select>
                                </div><br/>
                                <!-- END: time -->
                            </div>
                        </div>
                        <!-- END: session -->
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center" colspan="2">
                         <!-- BEGIN: submit -->
                        <input class="btn btn-primary" type="submit" value="{LANG.save}" name="submit">
                        <input type="hidden" name="checkss" value="{CHECKSS}" />
                        <input type="hidden" value="1" name="savesetting">
                         <!-- END: submit -->
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</form>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
    //<![CDATA[
    $(document).ready(function() {
        $('#userid').select2({
            theme: 'bootstrap',
            language : '{NV_LANG_INTERFACE}', 
            ajax: {
                type : 'POST',
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=config_member_add&list_userid=1',
                dataType: "json",
                delay: 200,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
        $('#userid').on('select2:select', function (e) {
            window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=config_member_add&userid=' + e.target.value;
        });
        $("form#config").submit(function() {
            var url = $(this).attr("action");
            url +='&nocache=' + new Date().getTime();
            a = $(this).serialize();
            $("input[name=submit]").attr("disabled", "disabled");
            $.ajax({
                type : "POST",
                url : url,
                data : a,
                success : function(b) {
                    if (b == "OK") {
                        window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=config_member';
                    } else {
                        alert(b);
                        $("input[name=submit]").removeAttr("disabled");
                    }
                }
            });
            return !1;
        });
    })
    //]]>
</script>
<!-- END: config_member_add -->
<!-- BEGIN: main -->
<div class="well hidden-print">
    <div class="panel-body">
        <form method="post" action="{NV_BASE_ADMINURL}index.php">
            <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}" /> 
            <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}" /> 
            <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}" />
            <input type="hidden" name="per_page"  value="{search_of_per_page}" />
            <div class="row">
                <div class="col-xs-24 col-md-12">
                    <div class="form-group">
                        <select class="form-control" id="month" name="month">
                            <option value="0">{LANG.sl_month}</option>
                            <!-- BEGIN: month -->
                            <option value="{MONTH.value}" {selected}>{MONTH.title}</option>
                            <!-- END: month -->
                        </select>
                    </div>
                </div>
                <div class="col-xs-24 col-md-12">
                    <div class="form-group">
                        <select class="form-control" id="year" name="year">
                            <option value="0">{LANG.sl_year}</option>
                            <!-- BEGIN: year -->
                            <option value="{YEAR.value}" {selected}>{YEAR.title}</option>
                            <!-- END: year -->
                        </select>
                    </div>
                </div>
            </div>
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
                        <select  name="department_id" class="form-control">
                            <option value="0">{LANG.department}</option>
                            <!-- BEGIN: search_department -->
                            <option value="{department_id}" {selected}>{department_title}</option>
                            <!-- END: search_department -->
                        </select>
                    </div>
                </div>
                <div class="col-xs-24 col-md-6">
                    <div class="form-group">
                        <select  name="office_id" class="form-control">
                            <option value="0">{LANG.office}</option>
                            <!-- BEGIN: search_office -->
                            <option value="{office_id}" {selected}>{office_title}</option>
                            <!-- END: search_office -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <input type="hidden" name="per_page"  value="{search_of_per_page}" />
                <button type="submit" class="btn btn-primary">{LANG.select}</button>
            </div>
        </form>
    </div>
</div>
<h2><strong>{config_member_time}</strong></h2>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th width="50" class="text-center"> TT </th>
                <th width="50"> {LANG.image} </th>
                <th width="220" class="text-center"> {LANG.staff} </th>
                <th class="text-center"> {LANG.work_day} </th>
                <th width="250" class="text-center"> {LANG.work_time} </th>
                <th width="150">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td class="text-right">
                    <strong>{LOOP.tt}</strong>
                </td>
                <td class="text-center">
                    <img src="{LOOP.photo}" alt="{LANG.image}" width="50" height="50"/>
                </td>
                <td>
                    {LOOP.full_name}
                </td>
                <td>
                    <p><strong>{LANG.work_day}: </strong>{LOOP.work_day}</p>
                    <!-- BEGIN: saturday_work_week -->
                    <p><strong>{LANG.saturday_work_week}: </strong></p>
                    <ul>
                    <!-- BEGIN: work_week -->
                    <li>{work_week}</li>
                    <!-- END: work_week -->
                    </ul>
                    <!-- END: saturday_work_week -->
                </td>
                <td>
                    <!-- BEGIN: session -->
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <h3>{session_title}</h3>
                            <!-- BEGIN: time -->
                            <div>
                                <strong>{time_title}:</strong>
                                {hour} {GLANG.hour} {minute} {GLANG.min}
                            </div><br/>
                            <!-- END: time -->
                        </div>
                    </div>
                    <!-- END: session -->
                </td>
                <td class="text-center">
                    <em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}&{NV_OP_VARIABLE}=config_member_add&userid={LOOP.userid}">{GLANG.edit}</a> 
                    - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="javascript:void(0)" onclick="deleteConfigMember({LOOP.userid})">{GLANG.delete}</a>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- BEGIN: generate_page -->
<div class="text-center">{GENERATE_PAGE}</div>
<!-- END: generate_page -->
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
<script type="text/javascript">
    //<![CDATA[
        function deleteConfigMember(id) {
            $.ajax({
                type : "POST",
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=config_member&nocache=' + new Date().getTime(),
                data : '&delete_config_member=' + id + '&checkss={CHECKSS}',
                success : function(b) {
                    if (b == "OK") {
                        window.location.href = window.location.href;
                    } else {
                        alert(b);
                    }
                }
            });
        }
        $("select[name=per_page").change(function() {
            var $per_page = $(this).val();
            if ($per_page > 0) {
                window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable+'=config_member&per_page=' + $per_page;
            }
        })
    //]]>
</script>
<!-- END: main -->
