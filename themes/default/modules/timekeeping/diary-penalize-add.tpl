<!-- BEGIN: main -->
<h1 class="mb-2">{page_title}</h1>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->
<form method="post" action="{ACTION_URL}" id="add-diary-penalize-form" class="form-horizontal">
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-6 control-label" for="day">{LANG.department_sel} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <select class="form-control" name="d" id="ele_d">
                        <!-- BEGIN: department -->
                        <option value="{DEPARTMENT.id}"{DEPARTMENT.selected}>{DEPARTMENT.title}</option>
                        <!-- END: department -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="day">{LANG.search_user} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <select class="form-control" name="u" id="ele_u" data-selected="{userid_select}"></select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-6">{LANG.reason} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <textarea  class="form-control" name="reason" >{diary_penalize.reason}</textarea>
                </div>
            </div>
            <input type="hidden" name="id" value="{diary_penalize.id}" />
            <input type="hidden" name="save_penalize" value="1" />
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" {disabled_save}/>
                </div>
            </div>
        </div>
    </div>
</form>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var data = {JSON_DATA};
    var form = $('#add-diary-penalize-form');
    $('[name="d"]').on('change', function() {
        var uSel = $('[name="u"]', form);
        var department_id = $('[name="d"]', form).val();
        var html = '<option value="0">--</option>';
        if (typeof data[department_id] != 'undefined') {
            $.each(data[department_id].staffs, function(userid, username) {
                html += '<option value="' + userid + '"' + (userid == uSel.data('selected') ? ' selected="selected"' : '') + '>' + username + '</option>';
            });
        }
        uSel.html(html);
        uSel.select2({
            placeholder: '{LANG.search_user}',
            width: '100%'
        });
    }).trigger('change');

    $("#add-diary-penalize-form").submit(function() {
        var url = $(this).attr("action");
        url +='?&nocache=' + new Date().getTime();
        var rs = $('textarea[name=reason]').val();
        var d = $('#ele_d').val();
        var u = $('#ele_u').val();
        rs = rs.trim();
        if (d == 0) {
            alert("{LANG.errordepartment}");
            $('#ele_d').select();
            return !1;
        }
        if (u == 0) {
            alert("{LANG.erroruserid}");
            $('#ele_u').select();
            return !1;
        }
        if (rs.length == 0) {
            alert("{LANG.error_empty_reason}");
            $("textarea[name=reason]").select();
            return !1;
        }

        data_form = $(this).serialize();
        $("input[name=submit]").attr("disabled", "disabled");
        $.ajax({
            type : "POST",
            url : url,
            data : data_form,
            success : function(b) {
                if (b == "OK") {
                    window.location.href = "{LIST_DIARY_PENALIZE_URL}?d=" + d + "&u=" + u;
                } else {
                    alert(b);
                    $("input[name=submit]").removeAttr("disabled");
                }
            }
        });
        return !1;
    });
});
</script>
<!-- END: main -->