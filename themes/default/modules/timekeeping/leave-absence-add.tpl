<!-- BEGIN: main -->
<h1 class="mb-2">{LANG.apply_leave}</h1>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<form id="leave-absence-add-form" method="post" action="{ACTION_URL}" class="form-horizontal leave-absence-add-form">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group">
                <label class="control-label col-sm-6">{LANG.type_leave} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="radio">
                        <label>
                            <input type="radio" name="type_leave" id="optionsRadios1" value="1" {LEAVE_ABSENCE.checked_radio_1}>
                            {LANG.type_full}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="type_leave" id="optionsRadios1" value="0.5" {LEAVE_ABSENCE.checked_radio_2}>
                            {LANG.type_half}
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group date-leave-absence" {LEAVE_ABSENCE.style_date}>
                <label class="control-label col-sm-6" for="day">{LANG.day} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="form-pickdate">
                        <input name="day" id="day" title="{LANG.day}" class="form-control" value="{LEAVE_ABSENCE.date}" maxlength="10" type="text" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="form-group date-range-leave-absence" {LEAVE_ABSENCE.style_range_date}>
                <label class="control-label col-sm-6" for="start_time">{LANG.start_day} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="form-pickdate">
                        <input name="start_time" id="start_time" title="{LANG.start_day}" class="form-control" value="{LEAVE_ABSENCE.start_time}" maxlength="10" type="text" autocomplete="off">
                    </div>
                    <div class="cls-half-day-checkbox">
                        <div class="half_day_start_checkbox" {LEAVE_ABSENCE.style_half_day_start_checkbox}>
                            <input type="checkbox" id="half_day_start_morning" value="1" class="form-control" name="half_day_start_morning" {LEAVE_ABSENCE.checked_half_day_start_morning}>
                            <label for="half_day_start_morning">{LANG.morning}</label><br>
                        </div>
                        <div class="half_day_start_checkbox" {LEAVE_ABSENCE.style_half_day_start_checkbox}>
                            <input type="checkbox" id="half_day_start_afternoon" value="1" class="form-control" name="half_day_start_afternoon" {LEAVE_ABSENCE.checked_half_day_start_afternoon}>
                            <label for="half_day_start_afternoon">{LANG.afternoon}</label><br>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group date-range-leave-absence" {LEAVE_ABSENCE.style_range_date}>
                <label class="control-label col-sm-6" for="end_time">{LANG.end_day} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="form-pickdate">
                        <input name="end_time" id="end_time" title="{LANG.end_day}" class="form-control" value="{LEAVE_ABSENCE.end_time}" maxlength="10" type="text" autocomplete="off">
                    </div>
                    <div class="cls-half-day-checkbox">
                        <div class="half_day_end_checkbox" {LEAVE_ABSENCE.style_half_day_end_checkbox}>
                            <input type="checkbox" id="half_day_end_morning" value="1" class="form-control" name="half_day_end_morning" {LEAVE_ABSENCE.checked_half_day_end_morning}>
                            <label for="half_day_end_morning">{LANG.morning}</label><br>
                        </div>
                        <div class="half_day_end_checkbox" {LEAVE_ABSENCE.style_half_day_end_checkbox}>
                            <input type="checkbox" id="half_day_end_afternoon" value="1" class="form-control" name="half_day_end_afternoon" {LEAVE_ABSENCE.checked_half_day_end_afternoon}>
                            <label for="half_day_end_afternoon">{LANG.afternoon}</label><br>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-6" for="use_absence_year"> {LANG.use_absence_year}: </label>
                <div class="col-sm-18 col-lg-12">
                    <input type="checkbox" id="use_absence_year" value="1" class="form-control" name="use_absence_year" {LEAVE_ABSENCE.checked_use_absence_year}/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-6">{LANG.reason} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <textarea  class="form-control" name="reason" >{LEAVE_ABSENCE.reason_leave}</textarea>
                </div>
            </div>
            <input type="hidden" name="id" value="{LEAVE_ABSENCE.id}" />
            <input type="hidden" name="save_leave_absence" value="1" />
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <input class="btn btn-primary" name="submit" type="submit" value="{LANG.leave_absence_submit}" {disabled_save}/>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    const _current_time = new Date();
    var _minDate = 1;
    var _working_time = new Date();
    _working_time.setHours(8, 0, 0, 0);
    if (_current_time < _working_time) {
        _minDate = _current_time;
    }

    $('input[name="type_leave"]').change(function(){
        if ($(this).val() == 1) {
            $('.date-range-leave-absence').show();
            $('.date-leave-absence').hide();
        } else {
            $('.date-range-leave-absence').hide();
            $('.date-leave-absence').show();
        }
    });

    $("#day").datepicker({
        showOn : "both",
        dateFormat : "dd/mm/yy",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        buttonImage : nv_base_siteurl + "assets/images/calendar.gif",
        buttonImageOnly : true,
        yearRange: "-99:+0",
        minDate: new Date(),
        beforeShow: function() {
            setTimeout(function(){
                $('.ui-datepicker').css('z-index', 999999999);
            }, 0);
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
        minDate: _minDate,
        onSelect: function () {
            start = $("#start_time").datepicker( 'getDate' );
            end = $("#end_time").datepicker( 'getDate' );
            if($("#end_time").datepicker( 'getDate' ) == null || start > end){
                $("#end_time").datepicker( "setDate", start );
            }
            startDate = $("input[name=start_time]").val();
            endDate = $("input[name=end_time]").val();
            if (startDate == endDate) {
                $(".half_day_end_checkbox").hide();
                $(".half_day_start_checkbox").hide();
            } else {
                $(".half_day_end_checkbox").show();
                $(".half_day_start_checkbox").show();
            }
        },
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
        minDate: _minDate,
        onSelect: function () {
            startDate = $("input[name=start_time]").val();
            endDate = $("input[name=end_time]").val();
            if (startDate == endDate) {
                $(".half_day_end_checkbox").hide();
                $(".half_day_start_checkbox").hide();
            } else {
                $(".half_day_end_checkbox").show();
                $(".half_day_start_checkbox").show();
            }
        },
        beforeShow: function() {
            setTimeout(function(){
                $('.ui-datepicker').css('z-index', 999999999);
            }, 0);
        }
    });
    $("#leave-absence-add-form").submit(function() {
        var url = $(this).attr("action");
        url +='?nocache=' + new Date().getTime();
        var patt = /[0-9]{2}\/[0-9]{2}/g;
        var type_leave = $("input[name=type_leave]:checked").val();
        if (type_leave == '1') {
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
        } else {
            var a = $("input[name=day]").val();
            if (!patt.test(a)) {
                alert("{LANG.error_format_date}: " + $("input[name=day]").attr("title"));
                $("input[name=day]").val('');
                $("input[name=day]").select();
                return !1;
            }
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
            dataType: 'json',
            cache: false,
            success : function(b) {
                $("input[name=submit]").removeAttr("disabled");
                if (!b.success) {
                    alert(b.message);
                    return;
                }
                window.location.href = b.link;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Request Error!!!');
                $("input[name=submit]").removeAttr("disabled");
            }
        });
        return !1;
    });
});
</script>
<!-- END: main -->
