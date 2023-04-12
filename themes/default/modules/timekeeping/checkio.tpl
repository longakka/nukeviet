<!-- BEGIN: main -->
<div class="panel panel-default">
    <div class="panel-body">
        <!-- BEGIN: checkin_message -->
        <div class="alert alert-danger">{CHECKIN_MESSAGE}</div>
        <!-- END: checkin_message -->
        <div id="checkio" class="text-center py-8">
            <!-- BEGIN: info -->
            <p class="mb-1">{CHECKIN_TIME_MESSAGE}: {checkin_time}</p>
            <!-- BEGIN: late -->
            <p class="mb-1">{late}</p>
            <!-- END: late -->
            <p class="mb-1">{LANG.time_real}:
                <strong id="time_real">
                    <span id="time_hour"></span>&nbsp{GLANG.hour}
                    <span id="time_minute"></span>&nbsp{GLANG.min}
                    <span id="time_second"></span>&nbsp{GLANG.sec}
                </strong>
            </p>
            <script type="text/javascript">
            //<![CDATA[
                var sum_time = {sum_time};
                var work_time_allow = [{work_time_allow}];
                function clockCheckInTime() {
                    remain = sum_time++;
                    var hour = Math.floor(remain/3600);
                    remain =  remain % 3600;
                    var minute = Math.floor(remain/60);
                    remain =  remain % 60;
                    $('#time_hour').text(hour);
                    $('#time_minute').text(minute);
                    $('#time_second').text(remain);
                }
                setInterval(function(){
                    clockCheckInTime();
                }, 1000);
                clockCheckInTime();
            //]]>
            </script>
            <!-- END: info -->
            <!-- BEGIN: view_checkin -->
            <button type="button" class="btn btn-lg btn-success" data-toggle="precheckinbtn" data-busy="false"><i class="fa fa-sign-in" aria-hidden="true"></i> {LANG.checkin}</button>
            <!-- START FORFOOTER -->
            <div class="modal modal-center" tabindex="-1" role="dialog" id="modalCheckin" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body p-8 text-center">
                            <div class="h1 hidden" data-toggle="checkinMessageArea" data-wait="<i class='fa fa-spinner fa-spin fa-2x text-danger'></i><div class='text-danger mt-2'>{LANG.please_waiting}</div>">
                                <i class="fa fa-spinner fa-spin fa-2x text-danger"></i><div class="text-danger mt-2">{LANG.please_waiting}</div>
                            </div>
                            <div data-toggle="checkinControlArea">
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
                                <button type="button" class="btn btn-danger" data-dismiss="modal"  data-toggle="checkinbtn_cancel">{GLANG.cancel}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END FORFOOTER -->
            <!-- END: view_checkin -->
            <!-- BEGIN: view_checkout -->
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalCheckout"><i class="fa fa-sign-out" aria-hidden="true"></i> {LANG.checkout}</button>
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
            <!-- END FORFOOTER -->
            <!-- END: view_checkout -->
            <!-- BEGIN: info_workout -->
            <p class="mt-3 text-danger">{LANG.info_workout}</p>
            <!-- END: info_workout -->
        </div>
        <div class="text-center">
            <p class="mb-2">
                {WORK_TIMEOUT_MESSAGE}.<br />
                {LANG.checkio_show_week}:
            </p>
            <div class="tkp-list-week">
                <!-- BEGIN: week -->
                <div class="week{WEEK_CLASS}">
                    <div class="w-order">{LANG.week} {WEEK_STT}</div>
                    <div class="w-time">{LANG.week_7th} {WEEK.view_day}</div>
                    <div class="w-status">{WEEK_TEXT}</div>
                </div>
                <!-- END: week -->
            </div>
        </div>
    </div>
</div>
<div class="modal modal-center" tabindex="-1" role="dialog" id="modalwelcome" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body p-8 text-center">
                <p style="text-align: justify">{LANG.good_morning}</p>
            </div>
            <div class="modal-footer text-center">
                <a href="{BASE_URL}" class="btn btn-success">{LANG.i_am_ready}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
//<![CDATA[
var CHECKSS = '{CHECKSS}';
//]]>
</script>
<!-- END: main -->
