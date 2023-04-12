<!-- BEGIN: business_trip_add -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2-bootstrap.min.css">
<div id="addContent">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong>{LANG.business_trip}</strong>
        </div> 
        <div class="panel-body">
            <form id="addBusiness_trip" method="post" action="{ACTION_URL}" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-4 col-md-4" for="userid">{LANG.staff}:</label>
                    <div class="col-sm-20 col-md-20">
                        <select class="form-control" name="userid" id="userid" title="{LANG.staff}">
                            <!--BEGIN: has_staff -->
                            <option selected="selected" value="{BUSINESS_TRIP.userid}">{BUSINESS_TRIP.full_name}</option>
                            <!--END: has_staff -->
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4 col-md-4" for="start_time">{LANG.start_day}:</label>
                    <div class="col-sm-20 col-md-20">
                        <div class="form-inline">
                            <input name="start_time" id="start_time" title="{LANG.start_day}" class="form-control" value="{BUSINESS_TRIP.start_time}" maxlength="10" type="text" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4 col-md-4" for="end_time">{LANG.end_day}:</label>
                    <div class="col-sm-20 col-md-20">
                        <div class="form-inline">
                            <input name="end_time" id="end_time" title="{LANG.end_day}" class="form-control" value="{BUSINESS_TRIP.end_time}" maxlength="10" type="text" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4 col-md-4" for="status">{LANG.type_off}:</label>
                    <div class="col-sm-20 col-md-20">
                        <select class="form-control" name="status" id="status">
                            <option value="business_trip" {selected_business_trip}>{LANG.business_trip}</option>
                            <option value="vacation" {selected_vacation}>{LANG.vacation}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4  col-md-4">{LANG.has_work}:</label>
                    <div class="col-sm-20  col-md-20">
                        <input type="checkbox" name="salary" {salary_checked} value="1" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-4  col-md-4">{LANG.reason}:</label>
                    <div class="col-sm-20  col-md-20">
                        <textarea  class="form-control" name="reason" >{BUSINESS_TRIP.reason}</textarea>
                    </div>
                </div>
                <input type="hidden" name="id" value="{BUSINESS_TRIP.id}" />
                <input type="hidden" name="checkss" value="{CHECKSS}" />
                <input type="hidden" name="save" value="1" />
                <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}"/>
            </form>
        </div> 
    </div>
</div>
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
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=business_trip_add&list_userid=1',
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
        $("#start_time").datepicker({
            showOn : "both",
            dateFormat : "dd/mm/yy",
            changeMonth : true,
            changeYear : true,
            showOtherMonths : true,
            buttonImage : nv_base_siteurl + "assets/images/calendar.gif",
            buttonImageOnly : true,
            yearRange: "-99:+0",
            beforeShow: function() {
                setTimeout(function(){
                    $('.ui-datepicker').css('z-index', 999999999);
                }, 0);
            }
        });
        $("#end_time").datepicker({
            showOn : "both",
            dateFormat : "dd/mm/yy",
            changeMonth : true,
            changeYear : true,
            showOtherMonths : true,
            buttonImage : nv_base_siteurl + "assets/images/calendar.gif",
            buttonImageOnly : true,
            yearRange: "-99:+0",
            beforeShow: function() {
                setTimeout(function(){
                    $('.ui-datepicker').css('z-index', 999999999);
                }, 0);
            }
        });
        $("form#addBusiness_trip").submit(function() {
            var url = $(this).attr("action");
            url +='?&nocache=' + new Date().getTime();
            var patt = /[0-9]{2}\/[0-9]{2}/g;
            var a = $("input[name=start_time]").val();
            if (!patt.test(a)) {
                alert("{LANG.error_format_date}: " + $("input[name=start_time]").attr("title"));
                $("input[name=start_time]").val('');
                $("input[name=start_time]").select();
                return !1;
            }
            a = $("input[name=end_time]").val();
            if (!patt.test(a)) {
                alert("{LANG.error_format_date}: " + $("input[name=end_time]").attr("title"));
                $("input[name=end_time]").val('');
                $("input[name=end_time]").select();
                return !1;
            }
            a = $('#userid').val();
            if ((a.length==0) || (a == 0) || (a == null) ){
                alert("{LANG.errorIsEmpty}: " + $('#userid').attr("title"));
                $('#userid').val('');
                $('#userid').select();
                return !1;
            }
            a = $(this).serialize();
            $("input[name=submit]").attr("disabled", "disabled");
            $.ajax({
                type : "POST",
                url : url,
                data : a,
                success : function(b) {
                    if (b == "OK") {
                        window.location.href = "{LIST_BUSINESS_TRIP_URL}";
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
<!-- END: business_trip_add -->
<!-- BEGIN: main -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{LANG.sl_month}</h2>
    </div>
    <div class="panel-body">
        <form class="form-inline" method="post" action="{ACTION_URL}">
            <div class="form-group">
                <label for="month">{LANG.month}</label>
                <select class="form-control" id="month" name="month">
                    <!-- BEGIN: month -->
                    <option value="{MONTH.value}" {selected}>{MONTH.title}</option>
                    <!-- END: month -->
                </select>
            </div>
            <div class="form-group">
                <label for="year">{LANG.year}</label>
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
{BUSINESS_TRIP}
<!-- END: main -->
<!-- BEGIN: list -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{list_business_trip_vacation}</h2>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="50" class="text-center"> TT </th>
                        <th width="220" class="text-center"> {LANG.staff} </th>
                        <th width="120" class="text-center"> {LANG.start_day} </th>
                        <th width="120" class="text-center"> {LANG.end_day} </th>
                        <th width="120" class="text-center"> {LANG.type_off} </th>
                        <th width="120" class="text-center"> {LANG.has_work} </th>
                        <th> {LANG.reason} </th>
                        <th width="150">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- BEGIN: loop -->
                    <tr>
                        <td class="text-right">
                            <strong>{LOOP.tt}</strong>
                        </td>
                        <td>
                            {LOOP.full_name}
                        </td>
                        <td>
                            {LOOP.start_day}/ {LOOP.start_month}/ {LOOP.start_year}
                        </td>
                        <td>
                            {LOOP.end_day}/ {LOOP.end_month}/ {LOOP.end_year}
                        </td>
                        <td>
                            {LOOP.type_off}
                        </td>
                        <td>
                            <div class="text-center">
                                <input type="checkbox" {LOOP.salary_checked} class="form-control" />
                            </div>
                        </td>
                        <td>
                            {LOOP.reason}
                        </td>
                        <td>
                            <em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}business-trip-add/?&id={LOOP.id}">{GLANG.edit}</a> 
                            - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="javascript:void(0)" onclick="deleteBusiness_trip({LOOP.id})">{GLANG.delete}</a>
                        </td>
                    </tr>
                    <!-- END: loop -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[
        function deleteBusiness_trip(id) {
            $.ajax({
                type : "POST",
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=business-trip&nocache=' + new Date().getTime(),
                data : '&delete=' + id + '&checkss={CHECKSS}',
                success : function(b) {
                    if (b == "OK") {
                        window.location.href = window.location.href;
                    } else {
                        alert(b);
                    }
                }
            });
        }
    //]]>
</script>
<!-- END: list -->
