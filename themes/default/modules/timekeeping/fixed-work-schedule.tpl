<!-- BEGIN: main -->
<link type="text/css" href="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" />
<link type="text/css" href="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}themes/default/images/{MODULE_FILE}/bootstrap-datepicker/locales/bootstrap-datepicker.{NV_LANG_INTERFACE}.min.js"></script>
<div class="row">
    <div class="col-lg-18">
        <form method="get" action="{FORM_ACTION}">
            <!-- BEGIN: no_rewrite -->
            <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
            <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
            <input type="hidden" name="{NV_OP_VARIABLE}" value="{MODULE_NAME}">
            <!-- END: no_rewrite -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="element_q">{LANG.keywork}:</label>
                        <input type="text" class="form-control" id="element_q" name="q" value="{SEARCH.q}" placeholder="{LANG.enter_search_key}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="element_from">{LANG.start_day}:</label>
                        <input type="text" class="form-control datepicker" id="element_from" name="f" value="{SEARCH.from}" placeholder="dd/mm/yyyy" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="element_to">{LANG.end_day}:</label>
                        <input type="text" class="form-control datepicker" id="element_to" name="t" value="{SEARCH.to}" placeholder="dd/mm/yyyy" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="visible-sm-block visible-md-block visible-lg-block">&nbsp;</label>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-search" aria-hidden="true"></i> {GLANG.search}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-6">
        <div class="form-group text-right">
            <label class="visible-sm-block visible-md-block visible-lg-block">&nbsp;</label>
            <a href="{LINK_ADD_NEW}" class="btn btn-success"><i class="fa fa-plus-circle" aria-hidden="true"></i> {LANG.schedule_add}</a>
        </div>
    </div>
</div>
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
                    <th style="width: 15%" class="text-nowrap text-center">{LANG.schedule_status}</th>
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
                        <div><strong>{ROW.title}</strong></div>
                        <div class="text-muted"><small>{ROW.work_note}</small></div>
                    </td>
                    <td class="text-nowrap">{ROW.add_time}</td>
                    <td class="text-center">
                        <input name="status" id="change_status{ROW.id}" value="1" type="checkbox"{ROW.status_render} onclick="nv_change_wschedule_status('{ROW.id}', '{NV_CHECK_SESSION}');">
                    </td>
                    <td class="text-center text-nowrap">
                        <a href="{ROW.url_edit}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {GLANG.edit}</a>
                        <a href="javascript:void(0);" onclick="nv_cancel_wschedule('{ROW.id}', '{NV_CHECK_SESSION}');" class="btn btn-xs btn-warning"><i class="fa fa-trash"></i> {LANG.cancel}</a>
                        <!-- BEGIN: delete -->
                        <a href="javascript:void(0);" onclick="nv_delele_wschedule('{ROW.id}', '{NV_CHECK_SESSION}');" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> {GLANG.delete}</a>
                        <!-- END: delete -->
                    </td>
                </tr>
                <!-- END: loop -->
            </tbody>
            <!-- BEGIN: generate_page -->
            <tfoot>
                <tr>
                    <td colspan="6">
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
                <!-- BEGIN: delete -->
                <option value="delete">{GLANG.delete}</option>
                <!-- END: delete -->
            </select>
        </div>
        <button type="button" class="btn btn-primary" onclick="nv_wschedule_action(this.form, '{NV_CHECK_SESSION}', '{LANG.msgnocheck}')">{GLANG.submit}</button>
    </div>
</form>
<!-- END: main -->
