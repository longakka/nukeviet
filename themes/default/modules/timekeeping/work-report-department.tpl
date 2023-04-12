<!-- BEGIN: main -->
<link type="text/css" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<h1 class="mb-3">{LANG.wrd_title}</h1>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->
<form method="post" action="{FORM_ACTION}">
    <div class="form-search-responsive">
        <div class="col-desktop-stretch col-mobile-1 mb-2">
            <div class="ipt-item">
                <label for="ele_d">{LANG.department_sel}</label>
                <div>
                    <select class="form-control form-control-fixed-h" name="department_id" id="ele_d">
                        <option value=""></option>
                        <!-- BEGIN: department -->
                        <option value="{DEPARTMENT.id}"{DEPARTMENT.selected}>{DEPARTMENT.title}</option>
                        <!-- END: department -->
                    </select>
                </div>
            </div>
        </div>
        <div class="col-200 mb-2">
            <div class="ipt-item">
                <label for="user_filter">{LANG.type_project}</label>
                <div>
                    <select class="form-control form-control-fixed-h" name="type_project">
                        <option value="0" {type_project_working_selected}>{LANG.project_working}</option>
                        <option value="1" {type_project_accounting_selected}>{LANG.project_accounting}</option>
                    </select>
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
    <input type="hidden" name="tokend" value="{TOKEND}">
</form>
<!-- BEGIN: empty -->
<div class="alert alert-info">{LANG.no_data_to_show}</div>
<!-- END: empty -->
<script type="text/javascript">
$(document).ready(function() {
    $('#ele_d').select2({
        width: '100%',
        placeholder: '{LANG.wrd_choose}',
    });
});
</script>
<!-- BEGIN: data_working -->
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="width: 1%;">{LANG.stt}</th>
                <th style="width: 30%;">{LANG.taskcat_id}</th>
                <th class="w100">{LANG.wrd_time}</th>
                <th>{LANG.userid_assigned}</th>
                <!-- BEGIN: hidden_head -->
                <th class="w100 text-center">{LANG.wrd_ntime}</th>
                <!-- END: hidden_head -->
                <th class="w100 text-center">{LANG.wrd_ntime}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop_cat -->
            <!-- BEGIN: loop -->
            <tr>
                <!-- BEGIN: col_title -->
                <td class="text-center text-nowrap"<!-- BEGIN: rowspan1 --> rowspan="{ROWSPAN}"<!-- END: rowspan1 -->>{TT}</td>
                <td<!-- BEGIN: rowspan2 --> rowspan="{ROWSPAN}"<!-- END: rowspan2 -->>{TASKCAT}</td>
                <td class="text-right text-nowrap"<!-- BEGIN: rowspan3 --> rowspan="{ROWSPAN}"<!-- END: rowspan3 -->><strong class="text-danger">{TOTAL}</strong></td>
                <!-- END: col_title -->
                <td> {ROW.name} </td>
                <!-- BEGIN: hidden_body -->
                <td class="text-right"><strong class="text-danger">{ROW.time}</strong></td>
                <!-- END: hidden_body -->
                <td class="text-right"><strong class="text-danger">{ROW.accepted_time}</strong></td>
            </tr>
            <!-- END: loop -->
            <!-- END: loop_cat -->
            <tr>
                <td colspan="4" class="text-right"><strong>{LANG.wrd_sum}</strong></td>
                <!-- BEGIN: hidden_body1 -->
                <td class="text-right"><strong class="text-danger">{ALL_TOTAL}</strong></td>
                <!-- END: hidden_body1 -->
                <td class="text-right"><strong class="text-danger">{ALL_TOTAL_ACCEPTED}</strong></td>
            </tr>
        </tbody>
    </table>
</div>
<!-- END: data_working -->
<!-- END: main -->
