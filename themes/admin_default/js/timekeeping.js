function nv_change_department_weight(id) {
    var new_weight = $('#change_weight_' + id).val();
    $('#change_weight_' + id).prop('disabled', true);
    $.post(
        script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=departments&nocache=' + new Date().getTime(),
        'changeweight=1&id=' + id + '&new_weight=' + new_weight, function (res) {
            $('#change_weight_' + id).prop('disabled', false);
            var r_split = res.split("_");
            if (r_split[0] != 'OK') {
                alert(nv_is_change_act_confirm[2]);
            }
            location.reload();
        });
}

function nv_delele_department(id) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=departments&nocache=' + new Date().getTime(),
            'deleterow=1&id=' + id, function (res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function nv_change_rank_weight(id, checksess) {
    var new_weight = $('#change_weight_' + id).val();
    $('#change_weight_' + id).prop('disabled', true);
    $.post(
        script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=ranks&nocache=' + new Date().getTime(),
        'changeweight=' + checksess + '&id=' + id + '&new_weight=' + new_weight, function (res) {
            $('#change_weight_' + id).prop('disabled', false);
            var r_split = res.split("_");
            if (r_split[0] != 'OK') {
                alert(nv_is_change_act_confirm[2]);
            }
            location.reload();
        });
}

function nv_change_rank_status(id, checksess) {
    $('#change_status' + id).prop('disabled', true);
    $.post(
        script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=ranks&nocache=' + new Date().getTime(),
        'changestatus=' + checksess + '&id=' + id, function (res) {
            $('#change_status' + id).prop('disabled', false);
            if (res != 'OK') {
                alert(nv_is_change_act_confirm[2]);
                location.reload();
            }
        });
}

function nv_delele_rank(id, checksess) {
    if (confirm(nv_is_del_confirm[0])) {
        $.post(
            script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=ranks&nocache=' + new Date().getTime(),
            'delete=' + checksess + '&id=' + id, function (res) {
                var r_split = res.split("_");
                if (r_split[0] == 'OK') {
                    location.reload();
                } else {
                    alert(nv_is_del_confirm[2]);
                }
            });
    }
}

function FormatNumber(str) {
    var strTemp = GetNumber(str);
    if (strTemp.length <= 3) {
        return strTemp;
    }
    strResult = "";
    for (var i = 0; i < strTemp.length; i++) {
        strTemp = strTemp.replace(".", "");
    }
    var m = strTemp.lastIndexOf(",");
    if (m == -1) {
        for (var i = strTemp.length; i >= 0; i--) {
            if (strResult.length > 0 && (strTemp.length - i - 1) % 3 == 0) {
                strResult = "." + strResult;
            }
            strResult = strTemp.substring(i, i + 1) + strResult;
        }
    } else {
        var strphannguyen = strTemp.substring(0, strTemp.lastIndexOf(","));
        var strphanthapphan = strTemp.substring(strTemp.lastIndexOf(","), strTemp.length);
        var tam = 0;
        for (var i = strphannguyen.length; i >= 0; i--) {
            if (strResult.length > 0 && tam == 4) {
                strResult = "." + strResult;
                tam = 1;
            }
            strResult = strphannguyen.substring(i, i + 1) + strResult;
            tam = tam + 1;
        }
        strResult = strResult + strphanthapphan;
    }
    return strResult;
}

function GetNumber(str) {
    //var count = 0;
    for (var i = 0; i < str.length; i++) {
        var temp = str.substring(i, i + 1);
        if (!(temp == "," || temp == "." || (temp >= 0 && temp <= 9))) {
            alert("Mời nhập vào số");
            return str.substring(0, i);
        }
        if (temp == " ") {
            return str.substring(0, i);
        }
        if (temp == ",") { // Ngược dấu ,
            //if (count > 0) {
            //    return str.substring(0, ipubl_date);
            //}
            //count++;
        }
    }
    return str;
}

$(document).ready(function () {
    // Thử test Slack
    $('[data-toggle="hmrsnotislack"]').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=human_resources&nocache=' + new Date().getTime(),
            data: {
                hmrsnotislack: $(this).data('tokend'),
                id: $(this).data('id'),
            },
            dataType: 'json',
            cache: false,
            success: function (respon) {
                alert(respon.text);
            },
            error: function (e, x, t) {
                alert('Error!!!');
                console.log(e, x, t);
            }
        });
    });

    // Định dạng tiền
    $('.ipt-money-d').on('keyup', function () {
        $(this).val(FormatNumber($(this).val()));
    });
});
