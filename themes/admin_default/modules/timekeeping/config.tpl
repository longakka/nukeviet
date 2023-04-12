<!-- BEGIN: main -->
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2-bootstrap.min.css">
<div class="panel panel-default">
    <div class="panel-heading">
        <h2>{LANG.view_config}</h2>
    </div>
    <div class="panel-body">
        <form class="form-inline"  method="post" action="{ACTION_URL}">
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
            <tbody>
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
                        <input type="checkbox" id="weekday_{WEEKDAY.value}" value="{WEEKDAY.value}" class="form-control" name="dayAllowOfWeek[]" {WEEKDAY.checked} >
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
                <tr>
                    <th>{LANG.day_off}</th>
                    <td>
                        <select class="form-control w500" multiple="multiple" name="holidays[]" id="holidays" >
                            <!-- BEGIN: holiday -->
                            <option selected="selected" value="{holiday}">{holiday}</option>
                            <!-- END: holiday -->
                        </select>
                        <p>{LANG.format_day_off}</p>
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
        $('#holidays').select2({
            theme: 'bootstrap',
            language : '{NV_LANG_INTERFACE}', 
            minimumInputLength:5,
            maximumInputLength:5,
            tags: true,
            tokenSeparators: [',', ' '],
            createTag: function (params) {
                var patt = /[0-9]{2}\/[0-9]{2}/g;
                if (!patt.test(params.term)) {
                    return null;

                }
                return {
                    id: params.term,
                    text: params.term
                }
            }
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
                    console.log('b=', b);
                    if (b == "OK") {
                        window.location.href = window.location.href;
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
<!-- END: main -->
