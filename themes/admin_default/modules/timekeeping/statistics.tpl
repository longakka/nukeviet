<!-- BEGIN: list_statistics_day -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{BANG_CONG}</h2>
    </div>
    <div class="panel-body">
        <div class="table-responsive table-statistic">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="50" class="text-center"> TT </th>
                        <th class="text-center"> {GLANG.full_name} </th>
				        <th width="150"> CHECK IN </th>
				        <th width="150"> CHECK OUT </th>
                        <th width="100" class="text-center"> {LANG.late} </th>
                        <th width="100" class="text-center"> {LANG.worktime} </th>
                        <th width="100" class="text-center"> {LANG.workout} </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- BEGIN: loop -->
                    <tr>
                        <!-- BEGIN: first_loop -->
                        <td rowspan="{rowspan}">
                            {tt}
                        </td>
                        <td rowspan="{rowspan}">
                            <p>{ROW.full_name}<p>
                            <p class="text-primary">{ROW.info}<p>
                        </td>
                        <!-- END: first_loop -->
                        <td>
                            {ROW1.checkin}
                        </td>
                        <td>
                            {ROW1.checkout}
                        </td>
                        <td class="text-right">
                            {ROW1.late}
                        </td>
                        <td class="text-right">
                            {ROW1.worktime}
                        </td>
                        <td class="text-right">
                            {ROW1.workout}
                        </td>
                    </tr>
                    <!-- END: loop -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <strong>{GLANG.total}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sumLate}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sum_worktime}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sum_workout}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sumWork}</strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<!-- BEGIN: generate_page -->
<div class="text-center">{GENERATE_PAGE}</div>
<!-- END: generate_page -->
<!-- END: list_statistics_day -->
<!-- BEGIN: list_statistics_month -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{BANG_CONG}</h2>
    </div>
    <div class="panel-body">
        <div class="table-responsive table-statistic">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="50" class="text-center"> TT </th>
                        <th width="50"> {LANG.image} </th>
                        <th class="text-center"> {GLANG.full_name} </th>
                        <th width="150"> {LANG.department} </th>
				        <th width="150"> {LANG.office} </th>
                        <th width="100" class="text-center"> {LANG.late} </th>
                        <th width="100" class="text-center"> {LANG.worktime} </th>
                        <th width="100" class="text-center"> {LANG.workout} </th>
                        <th width="100" class="text-center"> {LANG.workday} </th>
                        <th width="100">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- BEGIN: loop -->
                    <tr>
                        <td>
                            {ROW.tt}
                        </td>
                        <td>
                            <img src="{ROW.photo}" alt="{LANG.image}" width="50" height="50"/>
                        </td>
                        <td>
                            {ROW.full_name}
                        </td>
                        <td>
                            {ROW.department_title}
                        </td>
                        <td>
                            {ROW.office_title}
                        </td>
                        <td class="text-right">
                            {ROW.late}
                        </td>
                        <td class="text-right">
                            {ROW.worktime}
                        </td>
                        <td class="text-right">
                            {ROW.workout}
                        </td>
                        <td class="text-right">
                            {ROW.workday}
                        </td>
                        <td>
                            <a href="{ROW.detail}" target="_self">{LANG.detail}</a>
                        </td>
                    </tr>
                    <!-- END: loop -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <strong>{GLANG.total}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sumLate}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sum_worktime}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sum_workout}</strong>
                        </td>
                        <td class="text-right">
                            <strong>{sumWork}</strong>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<!-- BEGIN: generate_page -->
<div class="text-center">{GENERATE_PAGE}</div>
<!-- END: generate_page -->
<!-- END: list_statistics_month -->
<!-- BEGIN: statistics_month -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{LANG.sl_workday}</h2>
    </div>
    <div class="panel-body">
        <form method="post" action="{ACTION_URL}">
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
                <button type="submit" class="btn btn-success" name="xuatbaocao" value="xuatbaocao"><i class="fa fa-download" aria-hidden="true"></i>&nbsp; {LANG.name_button_export}</button>
            </div>
        </form>
    </div>
</div>
{BANG_CONG}
<!-- END: statistics_month -->
<!-- BEGIN: statistics_day -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{LANG.sl_workday}</h2>
    </div>
    <div class="panel-body">
        <form method="post" action="{ACTION_URL}">
            <div class="row">
                <div class="col-xs-24 col-md-4">
                    <div class="input-group">
                        <input class="form-control datepicker"  required="required" value="{search_day}" type="text" name="search_day" placeholder="dd/mm/YYYY" autocomplete="off" /> 
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button">
                                <em class="fa fa-calendar fa-fix">&nbsp;</em>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-xs-24 col-md-5">
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" value="{search_name}" placeholder="{GLANG.your_account}">
                    </div>
                </div>
                <div class="col-xs-24 col-md-5">
                    <div class="form-group">
                        <input type="text" name="email" class="form-control" value="{search_email}" placeholder="Email">
                    </div>
                </div>
                <div class="col-xs-24 col-md-5">
                    <div class="form-group">
                        <select  name="department_id" class="form-control">
                            <option value="0">{LANG.department}</option>
                            <!-- BEGIN: search_department -->
                            <option value="{department_id}" {selected}>{department_title}</option>
                            <!-- END: search_department -->
                        </select>
                    </div>
                </div>
                <div class="col-xs-24 col-md-5">
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
{BANG_CONG}
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
<script type="text/javascript">
$(".datepicker").datepicker({
    dateFormat: "dd/mm/yy",
    changeMonth: !0,
    changeYear: !0,
    showOtherMonths: !0,
    showOn: "focus",
    yearRange: "-90:+0"
});
</script>
<!-- END: statistics_day -->
<!-- BEGIN: main -->
<ul class="nav nav-tabs">
    <li role="presentation" class="{statistics_month_active}">
        <a href="{link_statistics_month}">
            <h2>{LANG.statistics_month}</h2>
        </a>
    </li>
    <li role="presentation" class="{statistics_day_active}">
        <a href="{link_statistics_day}">
            <h2>{LANG.statistics_day}</h2>
        </a>
    </li>
</ul>
<br/>
{STATISTICS_TYPE}
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
    $("select[name=per_page").change(function() {
        var $per_page = $(this).val();
        if ($per_page > 0) {
            window.location.href = script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable+'=statistics&statistics_type={statistics_type}&month={month}&year={year}&search_day={search_day}&per_page=' + $per_page;
        }
    })
    //]]>
</script>
<!-- END: main -->