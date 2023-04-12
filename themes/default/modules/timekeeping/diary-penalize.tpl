<!-- BEGIN: main -->
<h1 class="mb-2">{LANG.view_diary_penalize}</h1>
<form method="get" action="{FORM_ACTION}" id="diary-penalize-form">
    <!-- BEGIN: no_rewrite -->
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
    <!-- END: no_rewrite -->
    <div class="form-search-responsive">
        <div class="col-desktop-stretch col-mobile-1">
            <div class="form-search-responsive">
                <div class="col-2 mb-2">
                    <div class="ipt-item">
                        <label for="ele_d">{LANG.department_sel}</label>
                        <div>
                            <select class="form-control" name="d" id="ele_d">
                                <!-- BEGIN: department -->
                                <option value="{DEPARTMENT.id}"{DEPARTMENT.selected}>{DEPARTMENT.title}</option>
                                <!-- END: department -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-2 mb-2">
                    <div class="ipt-item">
                        <label for="ele_u">{LANG.search_user}</label>
                        <div>
                            <select class="form-control" name="u" id="ele_u" data-selected="{SEARCH.userid}"></select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-200 mb-2">
            <div class="ipt-item">
                <label for="user_filter">{LANG.search_from} - {LANG.search_to}</label>
                <div>
                    <input type="text" class="form-control ipt-daterangepicker" id="user_filter" name="r" value="{SEARCH.range}" autocomplete="off"/>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter" aria-hidden="true"></i> {LANG.view}</button>
        </div>
    </div>
</form>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var data = {JSON_DATA};
    var form = $('#diary-penalize-form');
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

    $("#add-link-diary-penalize-form").submit(function() {
        var url = $(this).attr("action");
        var id = $('input[name=""]').attr("action");
        url +='?&nocache=' + new Date().getTime();
        var link = $('input[name=link]').val();
        if (link == '') {
            alert("{LANG.errorlink}");
            $('input[name=link]').select();
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
                    $('#add-link'+id).hide();
                    window.location.href = window.location.href;
                } else {
                    alert(b);
                    $("input[name=submit]").removeAttr("disabled");
                }
            }
        });
        return !1;
    });
});

function deletePenalize(id) {
    if (!confirm('{LANG.confirm_delete_diary_penalize}')) {
        return false;
    }
    $.ajax({
        type : "POST",
        url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=diary-penalize&nocache=' + new Date().getTime(),
        data : '&delete_penalize=' + id,
        success : function(b) {
            if (b == "OK") {
                window.location.href = window.location.href;
            } else {
                alert(b);
            }
        }
    });
}

</script>
<!-- BEGIN: empty -->
<div class="alert alert-info">{LANG.diary_penalize_empty}</div>
<!-- END: empty -->
<!-- BEGIN: please_choose -->
<div class="alert alert-info">{LANG.diary_penalize_please_choose}</div>
<!-- END: please_choose -->
<!-- BEGIN: view -->
<h1 class="mb-2">{LANG.diary_penalize_detail} {DEPARTMENT_NAME} {STAFF} {LANG.view_report_from} {SEARCH_FROM} {LANG.view_report_to} {SEARCH_TO}</h1>
<div class="table-responsive list-diary-penalize">
    <table class="table table-striped table-bordered table-hover">
        <tbody>
            <tr>
                <th>{LANG.stt}</th>
                <th>{LANG.userid_assigned}</th>
                <th>{LANG.reason}</th>
                <th>{LANG.time_create}</th>
                <th class="text-center">{LANG.status_handle}</th>
                <th class="text-center">{LANG.confirm}</th>
            </tr>
            <!-- BEGIN: loop -->
            <tr>
                <td> {TT} </td>
                <td>
                    <p><strong>{VIEW.name}</p></strong>
                    <p>{VIEW.department}</p>
                    <!-- BEGIN: edit_deleted -->
                    <div class="cls-edit-delete-penalize">
                        <em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}diary-penalize-add/?&id={VIEW.id}">{LANG.edit}</a>
                        - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="javascript:void(0)" onclick="deletePenalize({VIEW.id})">{LANG.delete}</a>
                    </div>
                    <!-- END: edit_deleted -->
                </td>
                <td class="cls-reason-penalize"> {VIEW.user_add_reason} </td>
                <td class="text-center"> {VIEW.created_date} </td>
                <td class="text-center">
                    <div class="center-desktop">
                        {VIEW.link_href}
                        <!-- BEGIN: edit_deleted_link -->
                        <div class="cls-edit-delete-penalize">
                            <em class="fa fa-edit fa-lg">&nbsp;</em><a data-toggle="modal" data-target="#add-link-popup" id="{LINK_INFO.id}_link_penalize" onclick="showPopAddLink({LINK_INFO.id});" data-link="{LINK_INFO.link}">{LANG.edit}</a>
                        </div>
                        <!-- END: edit_deleted_link -->
                        <!-- BEGIN: add_link -->
                        <div class="confirm-item">
                            <a href="#" data-toggle="modal" data-target="#add-link-popup" onclick="showPopAddLink({VIEW.id});" class="btn btn-success btn-xs">{LANG.handle}</a>
                        </div>
                        <!-- END: add_link -->
                    </div>
                </td>
                <td class="cls-confirm">
                    <div class="center-desktop">
                        <!-- BEGIN: had_confirm -->
                        <div>
                            <div class="cls_tooltip_wrapper" data-id="{VIEW.id}">
                                <span {HAD_CONFIRM.is_show_tooltip} class="cls_show_tooltip label square-label mr-1 {HAD_CONFIRM.tooltip_class}">{HAD_CONFIRM.confirm_status}</span>
                                <div class="tooltip fade bottom in" role="tooltip" id="tooltip_{VIEW.id}" style="display: none;">
                                    <div class="tooltip-arrow" style="left: 50%;"></div>
                                    <div class="tooltip-inner">{HAD_CONFIRM.tooltip}</div>
                                </div>
                            </div>
                            <span class="value">{HAD_CONFIRM.leader_full_name}</span>
                        </div>

                        <div id="overlay_screen_{VIEW.id}" class="overlay_screen" style="width: 100%; opacity: 0.2;"></div>
                        <!-- END: had_confirm -->
                        <!-- BEGIN: change_confirm -->
                        <button {CONFIRM.show_hide_button_deny} data-toggle="modal" data-target="#modalDenied" id="deny-penalize-{CONFIRM.id}" type="button" onclick="showPopDenied({CONFIRM.id})" class="btn btn-danger btn-xs btn-space"><i class="fa fa-times"></i> {LANG.deny}</button>
                        <button id = "confirm-penalize-{CONFIRM.id}"  class="btn btn-primary btn-xs btn-space" data-confirm="{CONFIRM.confirm}" onclick="change_confirm({CONFIRM.id}, {CONFIRM.status})"><i class="fa fa-check"></i> {CONFIRM.confirm_title}</button>
                        <!-- END: change_confirm -->
                    </div>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
    <div class="modal fade" id="add-link-popup" tabindex="-1" role="dialog" aria-labelledby="mdTitle{VIEW.id}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="mdTitle{VIEW.id}">{LANG.add_link}</h5>
                </div>
                <div class="modal-body">
                    <form method="post" action="{ACTION_URL}" id="add-link-diary-penalize-form" class="form-horizontal">
                        <div class="form-group">
                            <label class="control-label col-sm-6">{LANG.link}<span class="text-danger">(*)</span>:</label>
                            <div class="col-sm-18 col-lg-12">
                                <input class="form-control" name="link" value="" type="text" />
                                <input class="form-control" name="add_link" value="{VIEW.id}" type="hidden" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-18 col-sm-offset-6">
                                <input class="btn btn-primary" type="submit" value="{LANG.save}"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="modalDenied" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div>
                        <div>
                            <input type="text" id="confirm_reason" class="form-control form-control-checkin text-center" value="" placeholder="{LANG.reason_deny}">
                            <input type="hidden" value="" name="id_penalize" id="id_penalize"/>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" id="denied_penalize_btn" onclick="change_confirm(0, 2)">{LANG.deny}</button>
                        <button type="button" class="btn btn-danger" id="btn_close_popup_denied" data-dismiss="modal">{LANG.cancel}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    //<![CDATA[
        $(document).ready(function() {
            $('.cls_tooltip_wrapper').hover(function(){
                id = $(this).attr('data-id');
                left = $('#tooltip_'+id).width() / 2 - 22;
                $('#tooltip_'+id).css('left', '-'+left+'px');
                $('#tooltip_'+id).show();
            }, function(){
                $('#tooltip_'+id).hide();
            })
        });
        function showPopAddLink(id){
            var link = $('#'+id+'_link_penalize').attr('data-link');
            $('#add-link-popup input[name = "add_link"]').val(id);
            if (typeof link !== 'undefined') {
                $('#add-link-popup input[name = "link"]').val(link);
            } else {
                $('#add-link-popup input[name = "link"]').val('');
            }
        }
        function showPopDenied(id){
            $('#id_penalize').val(id);
            $('#confirm_reason').val('');
        }
        function change_confirm(id, confirm_status) {
            var confirm_reason = '';
            $("#confirm-penalize-" + id).attr("disabled", true);
            $('#deny-penalize-' + id).attr("disabled", true);
            if (confirm_status == 2) {
                $('#denied_penalize_btn').attr("disabled", true);
                $('#btn_close_popup_denied').attr("disabled", true);
                confirm_reason = $('#confirm_reason').val();
                id = $('#id_penalize').val();
                if (confirm_reason == '') {
                    alert("{LANG.denied_reason}");
                    $('#denied_penalize_btn').attr("disabled", false);
                    return;
                }
            }
            $.ajax({
                type : "POST",
                url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=diary-penalize&change_confirm=1&nocache=' + new Date().getTime(),
                data : 'id=' + id + "&confirm_status=" + confirm_status + "&confirm_reason=" + confirm_reason,
                success : function(b) {
                    $("#confirm-penalize-" + id).attr("disabled", false);
                    $('#denied_penalize_btn').attr("disabled", false);
                    $('#deny-penalize-' + id).attr("disabled", false);
                    if (confirm_status == 1) {
                        $("#confirm-penalize-" + id).hide();
                        $("#deny-penalize-" + id).show();
                    } else {
                        $("#confirm-penalize-" + id).show();
                        $("#deny-penalize-" + id).hide();
                    }
                    $('#' + id + '_confirm_reason').val('');
                    if (b == 'OK') {
                        window.location.href = window.location.href;
                    } else {
                        alert(b);
                    }
                }
            });
        }
    //]]>
</script>
<!-- END: view -->
<!-- BEGIN: generate_page -->
<div class="text-center">
    {NV_GENERATE_PAGE}
</div>
<!-- END: generate_page -->
<!-- END: main -->