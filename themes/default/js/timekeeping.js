var geolat = '',
    geolng = '';

function locate_checkin(position) {
    if (position.coords == 'undefined') {
        return false;
    }
    geolat = position.coords.latitude;
    geolng = position.coords.longitude;
    $.ajax({
        type: "POST",
        url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=checkio&nocache=' + new Date().getTime(),
        data: 'status_work=1&checkss=' + CHECKSS,
        success: function(res) {
            // Reset hết các giá trị về mặc định
            $('[data-toggle="checkinWorkoutReasonInput"]').val('');
            $('[data-toggle="checkinWorkOutSurpriseArea"]').addClass('hidden');
            $('[data-toggle="checkinWorkOutRegArea"]').addClass('hidden').data('workoutid', 0);
            $('[data-toggle="checkinWorkOutRegArea"] .h1').html('');

            // Trường hợp làm thêm
            if (res.status_work == 'workout') {
                if (res.workout && res.workout.id) {
                    // Làm thêm đã đăng ký
                    $('[data-toggle="checkinWorkOutRegArea"]').removeClass('hidden').data('workoutid', res.workout.id);
                    $('[data-toggle="checkinWorkOutRegArea"] .h1').html(res.workout.reason);
                } else {
                    // Làm thêm đột xuất
                    $('[data-toggle="checkinWorkOutSurpriseArea"]').removeClass('hidden');
                }
                // Mở modal lên
                $('#modalCheckin').modal('show');
            } else {
                $('[data-toggle="checkinbtn"]').trigger('click');
            }

        }
    });
}
$(document).ready(function() {
    // Nút checkout
    $('[data-toggle="checkoutbtn"]').on('click', function(e) {
        e.preventDefault();
        var mess = $('[data-toggle="checkoutMessageArea"]');
        var btns = $('[data-toggle="checkoutBtns"]');
        if (btns.data('busy')) {
            return 1;
        }
        btns.data('busy', true);
        mess.html(mess.data('wait'));
        $('.btn', btns).prop('disabled', true);

        $.ajax({
            type: "POST",
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=checkio&nocache=' + new Date().getTime(),
            data: 'checkout=1&checkss=' + CHECKSS,
            success: function(b) {
                if (b == "OK") {
                    location.reload();
                } else {
                    btns.data('busy', false);
                    mess.html(b);
                    $('.btn', btns).prop('disabled', false);
                }
            }
        });
    });

    // Nút checkin submit
    $('[data-toggle="checkinbtn"]').on('click', function(e) {
        e.preventDefault();
        var mess = $('[data-toggle="checkinMessageArea"]');
        var control = $('[data-toggle="checkinControlArea"]');
        var btns = $('[data-toggle="checkinBtns"]');
        var workoutreg = $('[data-toggle="checkinWorkOutRegArea"]');
        var workoutsurprise = $('[data-toggle="checkinWorkOutSurpriseArea"]');
        var iptReason = $('[data-toggle="checkinWorkoutReasonInput"]');

        if (btns.data('busy')) {
            return 1;
        }
        // Làm thêm
        var description = ''; // Lý do làm thêm đột xuất hoặc lý do làm thêm đã đăng ký
        var workout_id = workoutreg.data('workoutid');
        if (workoutreg.is(':visible')) {
            description = $('.h1', workoutreg).text();
        } else if (workoutsurprise.is(':visible')) {
            description = trim(iptReason.val());
            if (description == '') {
                workoutsurprise.addClass('has-error');
                iptReason.focus();
                return 1;
            } else {
                workoutsurprise.removeClass('has-error');
            }
        }

        btns.data('busy', true);
        $('.btn', btns).prop('disabled', true);
        mess.html(mess.data('wait')).removeClass('hidden');
        control.addClass('hidden');

        $.ajax({
            type: "POST",
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=checkio&nocache=' + new Date().getTime(),
            data: 'geolat=' + geolat + '&geolng=' + geolng + '&workout_id=' + workout_id + '&description=' + encodeURIComponent(description) + '&checkin=1&checkss=' + CHECKSS,
            success: function(b) {
                if (b == "OK") {
                    if (description == '') {
                        $('#modalwelcome').modal('show');
                        $('#modalCheckin').modal('hide');
                    } else {
                        location.reload();
                    }
                } else {
                    btns.data('busy', false);
                    $('.btn', btns).prop('disabled', false);
                    mess.html(b);
                    control.removeClass('hidden');
                }
            }
        });
    });
    $('[data-toggle="checkinbtn_cancel"]').on('click', function(e) {
        $('[data-toggle="precheckinbtn"]').data('busy', false).prop('disabled', false);
    });

    // Nút vào checkin
    $('[data-toggle="precheckinbtn"]').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.data('busy')) {
            return 1;
        }
        var btns = $('[data-toggle="checkinBtns"]');
        if (btns.data('busy')) {
            // Đang trong tiến trình check in thì chỉ mở modal lên
            $('#modalCheckin').modal('show');
            return 1;
        }
        if (!navigator.geolocation) {
            alert(window.lang_module.Geolocation_is_not_supported_by_this_browser);
            return -1;
        }
        $('[data-toggle="precheckinbtn"]').data('busy', true).prop('disabled', true);
        navigator.geolocation.getCurrentPosition(locate_checkin, (error) => {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorStr = window.lang_module.You_have_to_brower_identify_your_address;
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorStr = window.lang_module.Location_information_is_unavailable;
                    break;
                case error.TIMEOUT:
                    errorStr = window.lang_module.The_request_to_get_user_location_timed_out;
                    break;
                case error.UNKNOWN_ERROR:
                    errorStr = window.lang_module.error_identify_location;
                    break;
                default:
                    errorStr = window.lang_module.error_identify_location;
            }
            $('[data-toggle="precheckinbtn"]').data('busy', true).prop('disabled', false);
            alert(errorStr);
        });
        return -1;
    });

    // Chọn ngày theo tiếng Việt
    var ranges = {};

    if (typeof moment != 'undefined') {
        moment.locale(nv_lang_interface);

        ranges[window.lang_module.cal_range_today] = [moment().startOf('day'), moment().endOf('day')];
        ranges[window.lang_module.cal_range_thisweek] = [moment().startOf('week'), moment().endOf('week')];
        ranges[window.lang_module.cal_range_lastweek] = [moment().startOf('week').subtract(7, 'days'), moment().endOf('week').subtract(7, 'days')];
        ranges[window.lang_module.cal_range_thismonth] = [moment().startOf('month'), moment().endOf('month')];
        ranges[window.lang_module.cal_range_lastmonth] = [moment().startOf('month').subtract(1, 'months').startOf('month'), moment().endOf('month').subtract(1, 'months').endOf('month')];
        ranges[window.lang_module.cal_range_thisyear] = [moment().startOf('year'), moment().endOf('year')];
        ranges[window.lang_module.cal_range_lastyear] = [moment().startOf('year').subtract(1, 'years'), moment().endOf('year').subtract(1, 'years')];
        ranges[window.lang_module.cal_range_all] = [moment('01/01/2010', "DD/MM/YYYY"), moment().add(10, 'years').endOf('year')];
    }

    if ($('.ipt-daterangepicker').length) {
        $('.ipt-daterangepicker').daterangepicker({
            showDropdowns: true,
            locale: {
                cancelLabel: window.lang_module.cancel,
                applyLabel: window.lang_module.apply,
                customRangeLabel: window.lang_module.other,
                format: 'DD-MM-YYYY'
            },
            ranges: ranges,
            opens: 'right',
            drops: "auto",
            linkedCalendars: false
        });
    }
});

/* JS phần lịch làm việc cố định */
function nv_change_wschedule_status(id, checksess) {
    $('#change_status' + id).prop('disabled', true);
    $.post(
        script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=fixed-work-schedule&nocache=' + new Date().getTime(),
        'changestatus=' + checksess + '&id=' + id,
        function(res) {
            $('#change_status' + id).prop('disabled', false);
            if (res != 'OK') {
                alert(nv_is_change_act_confirm[2]);
                location.reload();
            }
        });
}

function nv_cancel_wschedule(id, checksess) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=fixed-work-schedule&nocache=' + new Date().getTime(),
            'cancel=' + checksess + '&id=' + id,
            function(res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function nv_delele_wschedule(id, checksess) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=fixed-work-schedule&nocache=' + new Date().getTime(),
            'deleterow=' + checksess + '&id=' + id,
            function(res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function nv_wschedule_action(oForm, checkss, msgnocheck) {
    var fa = oForm['idcheck[]'];
    var listid = '';
    if (fa.length) {
        for (var i = 0; i < fa.length; i++) {
            if (fa[i].checked) {
                listid = listid + fa[i].value + ',';
            }
        }
    } else {
        if (fa.checked) {
            listid = listid + fa.value + ',';
        }
    }

    if (listid != '') {
        var action = document.getElementById('action-of-content').value;
        if (action == 'cancel') {
            if (confirm(nv_is_del_confirm[0])) {
                $.post(
                    script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=fixed-work-schedule&nocache=' + new Date().getTime(),
                    'cancel=' + checkss + '&listid=' + listid,
                    function(res) {
                        var r_split = res.split("_");
                        if (r_split[0] == 'OK') {
                            location.reload();
                        } else {
                            alert(nv_is_del_confirm[2]);
                        }
                    });
            }
        } else if (action == 'delete') {
            if (confirm(nv_is_del_confirm[0])) {
                $.post(
                    script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=fixed-work-schedule&nocache=' + new Date().getTime(),
                    'deleterow=' + checkss + '&listid=' + listid,
                    function(res) {
                        var r_split = res.split("_");
                        if (r_split[0] == 'OK') {
                            location.reload();
                        } else {
                            alert(nv_is_del_confirm[2]);
                        }
                    });
            }
        }
    } else {
        alert(msgnocheck);
    }
}

/* JS phần giao việc */
function nv_cancel_aswork(id, checksess) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
            'cancel=' + checksess + '&id=' + id,
            function(res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function nv_delele_aswork(id, checksess) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
            'deleterow=' + checksess + '&id=' + id,
            function(res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function nv_aswork_action(oForm, checkss, msgnocheck) {
    var fa = oForm['idcheck[]'];
    var listid = '';
    if (fa.length) {
        for (var i = 0; i < fa.length; i++) {
            if (fa[i].checked) {
                listid = listid + fa[i].value + ',';
            }
        }
    } else {
        if (fa.checked) {
            listid = listid + fa.value + ',';
        }
    }

    if (listid != '') {
        var action = document.getElementById('action-of-content').value;
        if (action == 'cancel') {
            if (confirm(nv_is_del_confirm[0])) {
                $.post(
                    script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
                    'cancel=' + checkss + '&listid=' + listid,
                    function(res) {
                        var r_split = res.split("_");
                        if (r_split[0] == 'OK') {
                            location.reload();
                        } else {
                            alert(nv_is_del_confirm[2]);
                        }
                    });
            }
        } else if (action == 'delete') {
            if (confirm(nv_is_del_confirm[0])) {
                $.post(
                    script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
                    'deleterow=' + checkss + '&listid=' + listid,
                    function(res) {
                        var r_split = res.split("_");
                        if (r_split[0] == 'OK') {
                            location.reload();
                        } else {
                            alert(nv_is_del_confirm[2]);
                        }
                    });
            }
        } else if ((action == 'had_finish') || (action == 'cancel_had_finish')) {
            if (confirm(nv_is_change_act_confirm[0])) {
                let completed = action == 'had_finish' ? 1 : 0;
                $.post(
                    script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
                    'checksess=' + checkss + '&is_completed=1&listid=' + listid + '&completed=' + completed,
                    function(res) {
                        var r_split = res.split("_");
                        if (r_split[0] == 'OK') {
                            location.reload();
                        } else {
                            alert(nv_is_change_act_confirm[2]);
                        }
                    });
            }
        }
    } else {
        alert(msgnocheck);
    }
}

function nv_complete_aswork(id, completed, checksess) {
    $.post(
        script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nocache=' + new Date().getTime(),
        'checksess=' + checksess + '&is_completed=1&id=' + id + '&completed=' + completed,
        function(res) {
            console.log(res);
            var r_split = res.split("_");
            if (r_split[0] == 'OK') {
                location.reload();
            } else {
                alert(nv_is_change_act_confirm[2]);
            }
        }
    );
}
