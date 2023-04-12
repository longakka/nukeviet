<!-- BEGIN: html_month_year -->
<form method="post" action="{ACTION_URL}">
    <div class="form-search-inline">
        <div class="ipt-items">
            <div class="ipt-groups">
                <div class="ipt-item">
                    <label for="month">{LANG.month}</label>
                    <select class="form-control" id="month" name="month">
                        <!-- BEGIN: month -->
                        <option value="{MONTH.value}" {selected}>{MONTH.title}</option>
                        <!-- END: month -->
                    </select>
                </div>
                <div class="ipt-item">
                    <label for="year">{LANG.year}</label>
                    <select class="form-control" id="year" name="year">
                        <!-- BEGIN: year -->
                        <option value="{YEAR.value}" {selected}>{YEAR.title}</option>
                        <!-- END: year -->
                    </select>
                </div>
            </div>
        </div>
        <div class="ipt-btns">
            <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {LANG.select}</button>
        </div>
    </div>
</form>
<!-- END: html_month_year -->

<!-- BEGIN: workout_add -->
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<div id="addContent">
    <h1 class="mb-2">{LANG.workout_add}</h1>
    <div class="panel panel-default">
        <div class="panel-body">
            <form id="addWorkout" method="post" action="{ACTION_URL}" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-6" for="day">{LANG.sl_day} <span class="text-danger">(*)</span>:</label>
                    <div class="col-sm-18 col-lg-12">
                        <div class="form-pickdate">
                            <input name="day" id="day" title="{LANG.sl_day}" class="form-control" value="{WORKOUT.day}" maxlength="10" type="text" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-6">{LANG.work_start} <span class="text-danger">(*)</span>:</label>
                    <div class="col-sm-18 col-lg-12">
                        <div class="form-picktime">
                            <select class="form-control pick-hour" name="hour_start">
                                <!-- BEGIN: hour_start -->
                                <option value="{hour}" {selected}>{hour_title}</option>
                                <!-- END: hour_start -->
                            </select>
                            <select class="form-control pick-minute" name="minute_start">
                                <!-- BEGIN: minute_start -->
                                <option value="{minute}"  {selected}>{minute_title}</option>
                                <!-- END: minute_start -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-6">{LANG.to} <span class="text-danger">(*)</span>:</label>
                    <div class="col-sm-18 col-lg-12">
                        <div class="form-picktime">
                            <select class="form-control pick-hour" name="hour_end">
                                <!-- BEGIN: hour_end -->
                                <option value="{hour}" {selected}>{hour_title}</option>
                                <!-- END: hour_end -->
                            </select>
                            <select class="form-control pick-minute" name="minute_end">
                                <!-- BEGIN: minute_end -->
                                <option value="{minute}" {selected}>{minute_title}</option>
                                <!-- END: minute_end -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-6">{LANG.reason} <span class="text-danger">(*)</span>:</label>
                    <div class="col-sm-18 col-lg-12">
                        <textarea  class="form-control" name="reason" >{WORKOUT.reason}</textarea>
                    </div>
                </div>
                <input type="hidden" name="id" value="{WORKOUT.id}" />
                <input type="hidden" name="checkss" value="{CHECKSS}" />
                <input type="hidden" name="save" value="1" />
                <div class="row">
                    <div class="col-sm-18 col-sm-offset-6">
                        <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" {disabled_save}/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[
        $("#day").datepicker({
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
        $("form#addWorkout").submit(function() {
            var url = $(this).attr("action");
            url +='?&nocache=' + new Date().getTime();
            var patt = /[0-9]{2}\/[0-9]{2}/g;
            var a = $("input[name=day]").val();
            if (!patt.test(a)) {
                alert("{LANG.error_format_date}: " + $("input[name=day]").attr("title"));
                $("input[name=day]").val('');
                $("input[name=day]").select();
                return !1;
            }
            var rs = $('textarea[name=reason]').val();
            rs = rs.trim();
            if (rs.length == 0) {
                alert("{LANG.error_empty_reason}");
                $("textarea[name=reason]").select();
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
                        window.location.href = "{LIST_WORKOUT_URL}";
                    } else {
                        alert(b);
                        $("input[name=submit]").removeAttr("disabled");
                    }
                }
            });
            return !1;
        });
    //]]>
</script>
<!-- END: workout_add -->

<!-- BEGIN: list -->
<ul class="nav nav-tabs mb-2">
    <li role="presentation" class="{active_workout_registry}">
        <a href="{ACTION_URL}?workout_type=registry&month={month}&year={year}">{LANG.list_workout_registry}</a>
    </li>
    <li role="presentation" class="{active_workout_surprise}">
        <a href="{ACTION_URL}?workout_type=surprise&month={month}&year={year}">{LANG.list_workout_surprise}</a>
    </li>
</ul>
{HTML_MONTH_YEAR}
<!-- BEGIN: workout_surprise -->
<h1 class="mb-2">{workout_title}</h1>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw70">{LANG.stt}</div>
        <div class="cw170">{LANG.time}</div>
        <div class="name">{LANG.reason}</div>
        <div class="cw140">{LANG.sum_work}</div>
        <div class="cw140">{LANG.confirm}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="dropdown">
            <div class="item">
                <div class="cw70 center-desktop">
                    <div class="mb-1"><span class="label-name mr-0">#</span><span class="label square-label label-info">{LOOP.tt}</span></div>
                    <span class="label-name">{LANG.day}: </span>{LOOP.day_text}
                </div>
                <div class="cw170">
                    <div class="list">
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-success mr-1" title="{LANG.checkin}" data-toggle="tktooltip">{LANG.checkin_s}</span>
                                <span class="value">{LOOP.time_in}</span>
                            </div>
                        </div>
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-danger mr-1" title="{LANG.checkout}" data-toggle="tktooltip">{LANG.checkout_s}</span>
                                <span class="value">{LOOP.time_out}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="name">
                    <span class="label-name">{LANG.reason}: </span>{LOOP.description}
                </div>
                <div class="cw140">
                    <span class="label-name">{LANG.sum_work}: </span>{LOOP.sum_work_str}
                </div>
                <div class="cw140">
                    <span class="label-name">{LANG.confirm}: </span>{LOOP.sum_confirm_str}
                </div>
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<!-- END: workout_surprise -->
<!-- BEGIN: workout_registry -->
<h1 class="mb-2">{workout_title}</h1>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw70">{LANG.stt}</div>
        <div class="cw120">{LANG.work_start}</div>
        <div class="cw120">{LANG.to}</div>
        <div class="name">{LANG.reason}</div>
        <div class="cw220">{LANG.confirm}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="dropdown">
            <div class="item">
                <div class="cw70 center-desktop">
                    <div class="mb-1"><span class="label-name mr-0">#</span><span class="label square-label label-info">{LOOP.tt}</span></div>
                    <span class="label-name">{LANG.day}: </span>{LOOP.day_text}
                </div>
                <div class="cw120">
                    <span class="label-name">{LANG.work_start}: </span>{LOOP.work_start}
                </div>
                <div class="cw120">
                    <span class="label-name">{LANG.to}: </span>{LOOP.to}
                </div>
                <div class="name">
                    <span class="label-name">{LANG.reason}: </span>{LOOP.reason}
                </div>
                <div class="cw220">
                    <span class="label-name mb-1">{LANG.confirm}: </span>
                    <div class="center-desktop">
                        <!-- BEGIN: not_confirm -->
                        <em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}workout-add/?&id={LOOP.id}">{GLANG.edit}</a>
                        - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="javascript:void(0)" onclick="deleteWorkout({LOOP.id})">{GLANG.delete}</a>
                        <!-- END: not_confirm -->
                        <!-- BEGIN: had_confirm -->
                            {had_confirmed}
                        <!-- END: had_confirm -->
                        <div id="checkio" class="text-center">
                        <!-- BEGIN: view_checkin -->
                        <button type="button" class="btn btn-success btn-sm" data-toggle="precheckinbtn" data-busy="false">{LANG.checkin}</button>
                        <!-- END: view_checkin -->
                        <!-- BEGIN: view_checkout -->
                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modalCheckout">{LANG.checkout}</button>
                        <!-- END: view_checkout -->
                        </div>
                        <!-- BEGIN: end_checkin -->
                            <!-- BEGIN: workday_list -->
                            {in_out}
                            <!-- END: workday_list -->
                            <p class="text-center"><strong>{sum_time_work}</strong></p>
                        <!-- END: end_checkin -->
                    </div>
                </div>
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<script src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery/jquery.cookie.js" type="text/javascript"></script>
<!-- START FORFOOTER -->
<div class="modal modal-center" tabindex="-1" role="dialog" id="modalCheckout" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body p-8 text-center">
                <div class="h1" data-confirm="{LANG.checkout_confirm}" data-wait="<i class='fa fa-spinner fa-spin fa-2x text-danger'></i><div class='text-danger mt-2'>{LANG.please_waiting}</div>" data-toggle="checkoutMessageArea">{LANG.checkout_confirm}</div>
            </div>
            <div class="modal-footer text-center" data-toggle="checkoutBtns" data-busy="false">
                <button type="button" class="btn btn-success" data-toggle="checkoutbtn">{GLANG.yes}</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">{GLANG.no}</button>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-center" tabindex="-1" role="dialog" id="modalCheckin" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body p-8 text-center">
                <div class="h1 hidden" data-toggle="checkinMessageArea" data-wait="<i class='fa fa-spinner fa-spin fa-2x text-danger'></i><div class='text-danger mt-2'>{LANG.please_waiting}</div>">
                    <i class="fa fa-spinner fa-spin fa-2x text-danger"></i><div class="text-danger mt-2">{LANG.please_waiting}</div>
                </div>
                <div data-toggle="checkinControlArea">
                    <div data-toggle="checkinFormArea">
                        <label>{LANG.where_are_you}</label>
                        <input type="text" class="form-control form-control-checkin text-center" value="" data-toggle="checkinPlaceInput" placeholder="{LANG.where_are_you}">
                    </div>
                    <div class="mt-8" data-toggle="checkinWorkOutSurpriseArea">
                        <label>{LANG.workout_with_surprise}</label>
                        <input type="text" class="form-control form-control-checkin text-center" value="" data-toggle="checkinWorkoutReasonInput" placeholder="{LANG.reason}">
                    </div>
                    <div class="mt-8" data-toggle="checkinWorkOutRegArea" data-workoutid="0">
                        <label>{LANG.workout_with_reg}</label>
                        <div class="h1" data-toggle="val"></div>
                    </div>
                </div>
                <div class="text-center mt-4" data-toggle="checkinBtns" data-busy="false">
                    <button type="button" class="btn btn-success" data-toggle="checkinbtn">{LANG.checkin}</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">{GLANG.cancel}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END FORFOOTER -->
<script type="text/javascript">
    //<![CDATA[
    var CHECKSS = '{CHECKSS}';
    function deleteWorkout(id) {
        if (!confirm('{LANG.confirm_delete_workout}')) {
            return false;
        }
        $.ajax({
            type : "POST",
            url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=workout&nocache=' + new Date().getTime(),
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
<!-- END: workout_registry -->
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="tktooltip"]').tooltip();
});
</script>
<!-- END: list -->
<!-- BEGIN: list_confirm -->
<ul class="nav nav-tabs">
    <li role="presentation" class="{active_workout_registry}">
        <a href="{ACTION_URL}?workout_type=registry&month={month}&year={year}">{LANG.list_workout_registry}</a>
    </li>
    <li role="presentation" class="{active_workout_surprise}">
        <a href="{ACTION_URL}?workout_type=surprise&month={month}&year={year}">{LANG.list_workout_surprise}</a>
    </li>
</ul>
<br/>
{HTML_MONTH_YEAR}
<!-- BEGIN: confirm_workout_surprise -->
<h1 class="mb-2">{workout_title}</h1>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw70">{LANG.stt}</div>
        <div class="cw100">{LANG.staff}</div>
        <div class="cw170">{LANG.time}</div>
        <div class="name">{LANG.reason}</div>
        <div class="cw140">{LANG.sum_work}</div>
        <div class="cw140">{LANG.confirm}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="dropdown">
            <div class="item">
                <div class="cw70 center-desktop">
                    <div class="mb-1"><span class="label-name mr-0">#</span><span class="label square-label label-info">{LOOP.tt}</span></div>
                    <span class="label-name">{LANG.day}: </span>{LOOP.day_text}
                </div>
                <div class="cw100">
                    <span class="label-name">{LANG.staff}: </span>{LOOP.full_name}
                </div>
                <div class="cw170">
                    <div class="list">
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-success mr-1" title="{LANG.checkin}" data-toggle="tktooltip">{LANG.checkin_s}</span>
                                <span class="value">{LOOP.time_in}</span>
                            </div>
                        </div>
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-danger mr-1" title="{LANG.checkout}" data-toggle="tktooltip">{LANG.checkout_s}</span>
                                <span class="value">{LOOP.time_out}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="name">
                    <span class="label-name">{LANG.reason}: </span>{LOOP.description}
                    <br/><a href="{REPORT_LINK}&amp;u={LOOP.userid}&amp;r={LOOP.range}" target="_blank" class="cls-link-workout">{LANG.view_report}</a>
                </div>
                <div class="cw140">
                    <span class="label-name">{LANG.sum_work}: </span>{LOOP.sum_work_str}
                </div>
                <div class="cw170">
                    <span class="label-name mb-1">{LANG.confirm}: </span>
                    <div class="confirm-workout">
                        <!-- BEGIN: confirmed_by_other -->
                        {message}
                        <!-- END: confirmed_by_other -->
                        <!-- BEGIN: change_confirm -->
                        <div class="confirm-item input-group input-group-sm">
                            <input type="number" id="{LOOP.id}_hour" name="hour" value="{LOOP.hour_confirm}" class="form-control" placeholder="{GLANG.hour}">
                            <span class="input-group-addon" style="width: 50px">{GLANG.hour}</span>
                        </div>
                        <div class="confirm-item input-group input-group-sm">
                            <input type="number" id="{LOOP.id}_min" name="min" value="{LOOP.min_confirm}" class="form-control" placeholder="{GLANG.min}">
                            <span class="input-group-addon" style="width: 50px">{GLANG.min}</span>
                        </div>
                        <div class="confirm-item input-group input-group-sm">
                            <input type="text" id="{LOOP.id}_confirm_reason" name="confirm_reason" value="" class="form-control" placeholder="{LANG.reason}">
                        </div>
                        <div class="confirm-item confirm-item-submit">
                            <button {SHOW_HIDE_BUTTON_DENY} id="deny-{LOOP.id}" type="button" class="btn btn-danger btn-sm" onclick="change_confirm({LOOP.id}, 2)">{LANG.deny}</button>
                            <button {SHOW_HIDE_BUTTON_CONFIRM} id="confirm-{LOOP.id}" type="button" class="btn btn-success btn-sm" onclick="change_confirm({LOOP.id}, 1)">{LANG.confirm}</button>
                        </div>
                        <!-- END: change_confirm -->
                    </div>
                </div>
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[
        function change_confirm(id, confirm_status) {
            var hour = parseInt($('#' + id + '_hour').val());
            hour = isNaN(hour) ? 0 : hour;
            var min = parseInt($('#' + id + '_min').val());
            min = isNaN(min) ? 0 : min;
            if (hour != 0 || min != 0 ) {
                var workout = hour * 60 + min;
                var confirm_reason = $('#' + id + '_confirm_reason').val();
                if (confirm_status == 1) {
                    $("#confirm-" + id).attr("disabled", true);
                } else {
                    $("#deny-" + id).attr("disabled", true);
                }
                $.ajax({
                    type : "POST",
                    url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=workout_confirm&change_confirm_surprise=1&nocache=' + new Date().getTime(),
                    data : 'id=' + id + "&confirm_status=" + confirm_status + "&confirm_reason=" + confirm_reason + "&workout=" + workout + '&month={month}&year={year}&checkss={CHECKSS}',
                    success : function(b) {
                        if (confirm_status == 1) {
                            $("#confirm-" + id).attr("disabled", false);
                            $("#confirm-" + id).hide();
                            $("#deny-" + id).show();
                        } else {
                            $("#deny-" + id).attr("disabled", false);
                            $("#confirm-" + id).show();
                            $("#deny-" + id).hide();
                        }
                        $('#' + id + '_confirm_reason').val('');
                        alert(b);
                    }
                });
            } else {
                alert("{LANG.no_period_defined}");
            }
        }
    //]]>
</script>
<!-- END: confirm_workout_surprise -->
<!-- BEGIN: confirm_workout_registry -->
<h1 class="mb-2">{workout_title}</h1>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw70">{LANG.stt}</div>
        <div class="cw100">{LANG.staff}</div>
        <div class="cw120">{LANG.work_start}</div>
        <div class="cw120">{LANG.to}</div>
        <div class="name">{LANG.reason}</div>
        <div class="cw200">{LANG.confirm}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="dropdown">
            <div class="item">
                <div class="cw70 center-desktop">
                    <div class="mb-1"><span class="label-name mr-0">#</span><span class="label square-label label-info">{LOOP.tt}</span></div>
                    <span class="label-name">{LANG.day}: </span>{LOOP.day_text}
                </div>
                <div class="cw100">
                    <span class="label-name">{LANG.staff}: </span>{LOOP.full_name}
                </div>
                <div class="cw120">
                    <span class="label-name">{LANG.work_start}: </span>{LOOP.work_start}
                </div>
                <div class="cw120">
                    <span class="label-name">{LANG.to}: </span>{LOOP.to}
                </div>
                <div class="name">
                    <span class="label-name">{LANG.reason}: </span>{LOOP.reason}
                </div>
                <div class="cw200 center-desktop">
                    <!-- BEGIN: had_finish -->
                        <!-- BEGIN: workday_list -->
                        {in_out}
                        <!-- END: workday_list -->
                        <p class="text-center"><strong>{sum_time_work}</strong></p>
                    <!-- END: had_finish -->
                    <!-- BEGIN: confirmed_by_other -->
                    <p>{confirm_or_deny}</p>
                    <p><strong>{leader_full_name}</strong></p>
                    <!-- END: confirmed_by_other -->
                    <!-- BEGIN: confirmed_by_your_self -->
                    <div>
                        <p>{CONFIRM.confirm_or_deny}</p>
                        <p><strong>{CONFIRM.leader_full_name}</strong></p>
                    </div>
                    <div {CONFIRM.show_hide_button_deny} class="confirm-item mb-1">
                        <input type="text" id="{CONFIRM.id}_deny_reason" name="deny_reason" value="" class="form-control" placeholder="{LANG.reason_deny}">
                    </div>
                    <button {CONFIRM.show_hide_button_deny} id="deny-work-register-{CONFIRM.id}" type="button" class="btn btn-danger btn-sm" onclick="change_confirm({CONFIRM.id}, 3)">{LANG.deny}</button>
                    <button id = "confirm-{CONFIRM.id}" class="btn btn-primary btn-sm" data-confirm="{CONFIRM.confirm}" onclick="change_confirm({CONFIRM.id}, {CONFIRM.status})">{CONFIRM.confirm_title}</button>
                    <!-- END: confirmed_by_your_self -->
                </div>
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<script type="text/javascript">
    //<![CDATA[
        function change_confirm(id, confirm) {
            var deny_reason = $('#' + id + '_deny_reason').val();
            if (confirm == 3) {
                $("#deny-work-register-" + id).attr("disabled", true);
            } else {
                $("#confirm-" + id).attr("disabled", true);
            }
            $.ajax({
                type : "POST",
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=workout_confirm&change_confirm_registry=1&nocache=' + new Date().getTime(),
                data : 'id=' + id + '&confirm=' + confirm + '&deny_reason=' + deny_reason + '&checkss={CHECKSS}',
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
<!-- END: confirm_workout_registry -->
<!-- END: list_confirm -->
