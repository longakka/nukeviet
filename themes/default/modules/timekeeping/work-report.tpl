<!-- BEGIN: main -->
<h1 class="mb-2">{LANG.view_report_department}</h1>
<form method="get" action="{FORM_ACTION}" id="work-report-form">
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
    var form = $('#work-report-form');

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
});
</script>
<!-- BEGIN: empty -->
<div class="alert alert-info">{LANG.work_report_empty}</div>
<!-- END: empty -->
<!-- BEGIN: please_choose -->
<div class="alert alert-info">{LANG.work_report_please_choose}</div>
<!-- END: please_choose -->
<!-- BEGIN: view -->
<h1 class="mb-2">{LANG.view_report_detail} {STAFF} {LANG.view_report_from} {SEARCH_FROM} {LANG.view_report_to} {SEARCH_TO}</h1>
<div class="pull-right">
    <button id="work-copy-link" type="button" class="btn btn-sm btn-success mb-2" data-container="body" data-trigger="manual" data-animation="false" data-title="{LANG.value_copied}" data-clipboard-text="{PAGE_LINK}"> {LANG.work_report_copy}</button>
</div>
<h3 class="mb-2">{LANG.work_day}</h3>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/clipboard/clipboard.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var clipboard1 = new ClipboardJS('#work-copy-link');
    clipboard1.on('success', function(e) {
        $(e.trigger).tooltip('show');
    });
    $('#work-copy-link').mouseleave(function() {
        $(this).tooltip('destroy');
    });
});
</script>
<table class="table table-striped table-bordered table-hover">
    <tbody>
        <tr>
            <th style="width: 50%;"> {LANG.project_name} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_worktime} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_overtime} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_acceptedtime} </th>
        </tr>
        <!-- BEGIN: total_loop -->
        <tr>
            <td>{PROJECT.name}</td>
            <td class="text-center">
                <strong class="text-danger">{PROJECT.work_time}</strong>
                <!-- BEGIN: work_time -->
                ({PROJECT.work_time_day}d)
                <!-- END: work_time -->
            </td>
            <td class="text-center">
                <strong class="text-danger">{PROJECT.over_time}</strong>
                <!-- BEGIN: over_time -->
                ({PROJECT.over_time_day}d)
                <!-- END: over_time -->
            </td>
            <td class="text-center">
                <strong class="text-danger">{PROJECT.accepted_time}</strong>
                <!-- BEGIN: accepted_time -->
                ({PROJECT.accepted_time_day}d)
                <!-- END: accepted_time -->
            </td>
        </tr>
        <!-- END: total_loop -->
        <tr>
            <th class="text-center">{LANG.total_time}</th>
            <th class="text-center">{TOTAL_WORKTIME}d</th>
            <th class="text-center">{TOTAL_OVERTIME}d</th>
            <th class="text-center">{TOTAL_ACCEPTEDTIME}d</th>
        </tr>
    </tbody>
</table>
<h3 class="mb-2">{LANG.work_report_list}</h3>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>{LANG.time_create}</th>
                <th>{LANG.title}</th>
                <th>{LANG.status}</th>
                <th class="w50 text-center">{LANG.work_time}</th>
                <th class="w50 text-center">{LANG.over_time}</th>
                <th class="w50 text-center">{LANG.work_accepted_time}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr class="{VIEW.progress_color}">
                <td> {VIEW.time_create} </td>
                <td>
                    <a href="#" data-toggle="modal" data-target="#work-detail{VIEW.number}"> {VIEW.title} </a>
                    <div class="modal" id="work-detail{VIEW.number}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h5 class="modal-title" id="exampleModalCenterTitle">{VIEW.title}</h5>
                                </div>
                                <div class="modal-body">
                                    <label> {LANG.link}: </label></br><a class="cls_link_detail" href="{VIEW.link}" target="_blank">{VIEW.link}</a></br>
                                    <label>{LANG.content}: </label></br> {VIEW.content}</br>
                                    <label>{LANG.description}: </label></br> {VIEW.description}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <small class="text-muted">{VIEW.taskcat_id}</small>
                    </div>
                </td>
                <td class="text-center"> {VIEW.status} </td>
                <td class="text-center">
                    <strong class="text-danger">{VIEW.work_time}h</strong>
                    <!-- BEGIN: work_time -->
                    ({VIEW.work_time_day}d)
                    <!-- END: work_time -->
                </td>
                <td class="text-center">
                    <strong class="text-danger">{VIEW.over_time}h</strong>
                    <!-- BEGIN: over_time -->
                    ({VIEW.over_time_day}d)
                    <!-- END: over_time -->
                </td>
                <td class="text-center">
                    <!-- BEGIN: show_accepted_time -->
                    <strong class="text-success">{VIEW.accepted_time}h</strong>
                    <!-- END: show_accepted_time -->
                    <!-- BEGIN: edit_accepted_time -->
                    <a href="#" title="{LANG.work_report_clickedit}" data-id="{VIEW.id}" data-value="{VIEW.accepted_time}" data-toggle="tktooltip" data-click="changeAcceptedTime"><strong class="text-success">{VIEW.accepted_time}h</strong></a>
                    <!-- END: edit_accepted_time -->
                    <!-- BEGIN: accepted_time -->
                    ({VIEW.accepted_time_day}d)
                    <!-- END: accepted_time -->
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="tktooltip"]').tooltip({
        trigger: 'hover',
        container: 'body'
    });

    var md = $('#mdEditAcceptedTime');
    $('[data-click="changeAcceptedTime"]').on('click', function(e) {
        e.preventDefault();
        md.data('view-id', $(this).data('id'));
        md.data('view-value', $(this).data('value'));
        $('#mdEditAcceptedTimeIpt').val($(this).data('value'));
        $('#mdEditAcceptedTimeIpt2').val('');
        md.modal('show');
    });

    $('#mdEditAcceptedTimeBtn').on('click', function(e) {
        e.preventDefault();
        var accepted_time = $('#mdEditAcceptedTimeIpt').val();
        var change_note = trim($('#mdEditAcceptedTimeIpt2').val());
        if (isNaN(accepted_time)) {
            alert('{LANG.work_report_err1}');
            return;
        }
        accepted_time = parseFloat(accepted_time);
        if (accepted_time < 0) {
            alert('{LANG.work_report_err2}');
            return;
        }
        if (accepted_time == parseFloat(md.data('view-value'))) {
            alert('{LANG.work_report_err3}');
            return;
        }
        if (change_note == '') {
            alert('{LANG.work_report_err4}');
            return;
        }
        $('#mdEditAcceptedTimeBtn').prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '={OP}&nocache=' + new Date().getTime(),
            data: {
                setacceptedtime: '{NV_CHECK_SESSION}',
                id: md.data('view-id'),
                accepted_time: accepted_time,
                change_note: change_note
            },
            dataType: 'json',
            cache: false,
            success: function(respon) {
                $('#mdEditAcceptedTimeBtn').prop('disabled', false);
                if (respon.message != '') {
                    alert(respon.message);
                    return;
                }
                location.reload();
            },
            error: function(e, x, t) {
                alert('Error!!!');
                $('#mdEditAcceptedTimeBtn').prop('disabled', false);
            }
        });
    });
});
</script>
<!-- START FORFOOTER -->
<div class="modal" tabindex="-1" role="dialog" id="mdEditAcceptedTime" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{LANG.close}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{LANG.work_report_edittitle}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="mdEditAcceptedTimeIpt">{LANG.work_report_editipt} <span class="text-danger">(*)</span>:</label>
                    <input class="form-control" type="number" min="0" step="0.1" value="" id="mdEditAcceptedTimeIpt">
                </div>
                <label for="mdEditAcceptedTimeIpt2">{LANG.work_report_editipt2} <span class="text-danger">(*)</span>:</label>
                <textarea class="form-control" id="mdEditAcceptedTimeIpt2" rows="4"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="mdEditAcceptedTimeBtn">{GLANG.submit}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{LANG.close}</button>
            </div>
        </div>
    </div>
</div>
<!-- END FORFOOTER -->
<!-- END: view -->
<!-- BEGIN: generate_page -->
<div class="text-center">
    {NV_GENERATE_PAGE}
</div>
<!-- END: generate_page -->
<!-- END: main -->
