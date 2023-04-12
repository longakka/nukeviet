<!-- BEGIN: main -->
<link type="text/css" href="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
<link type="text/css" href="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/locales/bootstrap-datepicker.{NV_LANG_INTERFACE}.min.js"></script>

<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />

<form method="get" action="{FORM_ACTION}">
    <!-- BEGIN: no_rewrite -->
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{MODULE_NAME}">
    <!-- END: no_rewrite -->
    <div class="row">
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <label class="control-label" for="element_q">{LANG.keywork}:</label>
                <input type="text" class="form-control" id="element_q" name="q" value="{SEARCH.q}" maxlength="255" placeholder="{LANG.search_title}">
            </div>
        </div>
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <label class="control-label" for="element_userid">{LANG.asswork_userid}:</label>
                <div>
                    <select class="form-control" name="userid" id="element_userid" {NO_CHAGE_USER}>
                        <option value="0">---</option>
                        <!-- BEGIN: staff -->
                        <option value="{STAFF.userid}" selected>{STAFF.full_name}</option>
                        <!-- END: staff -->
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <label class="control-label" for="progress_work">{LANG.progress_work}:</label>
                <div>
                    <select class="form-control" name="progress_work" id="progress_work" {NO_CHAGE_USER}>
                        <option value="">---</option>
                        <!-- BEGIN: progress_work -->
                        <option value="{PROGRESS_WORK.alias}" {selected}>{PROGRESS_WORK.title}</option>
                        <!-- END: progress_work -->
                    </select>
                </div>
            </div>
        </div>
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <label class="form-label" for="user_filter">{LANG.search_from} - {LANG.search_to}</label>
                <div class="ipt-item">
                    <input type="text" class="form-control ipt-daterangepicker" id="user_filter" name="r" value="{SEARCH.range}" autocomplete="off"/>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <label class="form-label" for="worked_finsh">{LANG.worked_finsh}:</label>
                <input type="checkbox" class="form-control" name="worked_finsh" value="1" {checked_worked_finsh}>
            </div>
        </div>
        <div class="col-xs-24 col-sm-12 col-md-12">
            <div class="form-group">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search" aria-hidden="true"></i> {GLANG.search}</button>
                <a href="{LINK_ADD_NEW}" class="btn btn-success"><i class="fa fa-plus-circle" aria-hidden="true"></i> {LANG.asswork_add}</a>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
$(document).ready(function() {
    $('.datepicker').datepicker({
        language: '{NV_LANG_INTERFACE}',
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayBtn: 'linked',
        autoclose: true,
        todayHighlight: true
    });
});
</script>
<form>
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width: 1%" class="text-center">
                        <input name="check_all[]" type="checkbox" value="yes" onclick="nv_checkAll(this.form, 'idcheck[]', 'check_all[]',this.checked);">
                    </th>
                    <th style="width: 40%" class="text-nowrap">
                        <a href="{URL_ORDER_TITLE}">{ICON_ORDER_TITLE} {LANG.task_name}</a>
                    </th>
                    <th style="width: 15%" class="text-nowrap">
                        <a href="{URL_ORDER_ADD_TIME}">{ICON_ORDER_ADD_TIME} {LANG.schedule_addtime}</a>
                    </th>
                    <th style="width: 14%" class="text-nowrap text-center">{LANG.function}</th>
                </tr>
            </thead>
            <tbody>
                <!-- BEGIN: loop -->
                <tr>
                    <td class="text-center">
                        <input type="checkbox" onclick="nv_UncheckAll(this.form, 'idcheck[]', 'check_all[]', this.checked);" value="{ROW.id}" name="idcheck[]">
                    </td>
                    <td>
                        <div>
                            <!-- BEGIN: isedit -->
                            <a href="#" title="{EDIT_MESSAGE}" data-toggle="worktip" data-click="workchange" data-id="{ROW.id}"><i class="fa fa-history text-danger" aria-hidden="true"></i></a>
                            <!-- END: isedit -->
                            <strong data-rel="tooltip" data-content="{ROW.work_content}">{ROW.title}</strong>
                        </div>
                        <div class="text-muted"><small>{ROW.work_note}</small></div>
                        <div>
                            <small class="text-primary">
                                <i class="fa fa-user-circle" aria-hidden="true"></i> {ROW.assignee_name}
                                <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                <i class="fa fa-user-circle-o" aria-hidden="true"></i> {ROW.user_name}
                            </small>
                        </div>
                        <div><small>{ROW.workmess}</small></div>
                    </td>
                    <td class="text-nowrap">{ROW.add_time}</td>
                    <td class="text-center text-nowrap">
                        <a href="{ROW.url_copy}" title="{LANG.asswork_copy}" class="btn btn-info btn-xs" data-toggle="worktip" data-container="body" data-animation="false"><i class="fa fa-copy"></i></a>
                        <a href="{ROW.url_edit}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {GLANG.edit}</a>
                        <a href="javascript:void(0);" onclick="nv_cancel_aswork('{ROW.id}', '{NV_CHECK_SESSION}');" class="btn btn-xs btn-warning"><i class="fa fa-trash"></i> {LANG.cancel}</a>
                        <!-- BEGIN: delete -->
                        <a href="javascript:void(0);" onclick="nv_delele_aswork('{ROW.id}', '{NV_CHECK_SESSION}');" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> {GLANG.delete}</a>
                        <!-- END: delete -->
                        <!-- BEGIN: completed -->
                        <a href="javascript:void(0);" onclick="nv_complete_aswork('{ROW.id}', 1, '{NV_CHECK_SESSION}');" class="btn btn-xs btn-default"><i class="fa fa-check"></i> {LANG.had_finish}</a>
                        <!-- END: completed -->
                        <!-- BEGIN: complete_cancel-->
                        <a href="javascript:void(0);" onclick="nv_complete_aswork('{ROW.id}', 0, '{NV_CHECK_SESSION}');" class="btn btn-xs btn-default"><i class="fa fa-check"></i> {LANG.cancel_had_finish}</a>
                        <!-- END: complete_cancel -->
                    </td>
                </tr>
                <!-- END: loop -->
            </tbody>
            <!-- BEGIN: generate_page -->
            <tfoot>
                <tr>
                    <td colspan="4">
                        {GENERATE_PAGE}
                    </td>
                </tr>
            </tfoot>
            <!-- END: generate_page -->
        </table>
    </div>
    <div class="form-group form-inline">
        <div class="form-group">
            <select class="form-control" id="action-of-content">
                <option value="cancel">{LANG.cancel}</option>
                <option value="had_finish">{LANG.had_finish}</option>
                <option value="cancel_had_finish">{LANG.cancel_had_finish}</option>
                <!-- BEGIN: delete -->
                <option value="delete">{GLANG.delete}</option>
                <!-- END: delete -->
            </select>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="nv_aswork_action(this.form, '{NV_CHECK_SESSION}', '{LANG.msgnocheck}')">{GLANG.submit}</button>
        </div>
    </div>
</form>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="worktip"]').tooltip();

    var md = $('#mdWorkChange');
    $('[data-click="workchange"]').on('click', function(e) {
        e.preventDefault();
        md.data('id', $(this).data('id')).modal('show');
    });
    md.on('show.bs.modal', function(e) {
        $('.ct', md).html('');
        $('.load', md).removeClass('hidden');

        $.ajax({
            type: 'POST',
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '={OP}&nocache=' + new Date().getTime(),
            data: {
                loadworkchange: '{NV_CHECK_SESSION}',
                id: md.data('id'),
            },
            cache: false,
            success: function(respon) {
                $('.load', md).addClass('hidden');
                $('.ct', md).html(respon);
            },
            error: function(e, x, t) {
                alert('Error!!!');
            }
        });
    });
    $('#element_userid').select2({
        width: '100%',
        language: '{NV_LANG_INTERFACE}',
        ajax: {
            type : 'POST',
            url : script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=assign-work&nv_staffs=1&nocache=' + new Date().getTime(),
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
});
</script>
<!-- START FORFOOTER -->
<div class="modal" tabindex="-1" role="dialog" id="mdWorkChange">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{LANG.close}"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{LANG.asswork_change}</h4>
            </div>
            <div class="load text-center p-6">
                <i class="fa fa-3x fa-spin fa-spinner"></i>
            </div>
            <div class="ct"></div>
        </div>
    </div>
</div>
<!-- END FORFOOTER -->
<!-- END: main -->

<!-- BEGIN: change -->
<table class="table mb-0">
    <thead>
        <tr>
            <th>{LANG.asswork_change_u}</th>
            <th>{LANG.asswork_change_t}</th>
            <th>{LANG.asswork_change_o}</th>
            <th>{LANG.asswork_change_n}</th>
            <th>{LANG.asswork_change_r}</th>
        </tr>
    </thead>
    <tbody>
        <!-- BEGIN: loop -->
        <tr>
            <td>{ROW.user}</td>
            <td>{ROW.add_time}</td>
            <td>{ROW.time_old}h</td>
            <td>{ROW.time_new}h</td>
            <td>{ROW.change_reason}</td>
        </tr>
        <!-- END: loop -->
    </tbody>
</table>
<!-- END: change -->
