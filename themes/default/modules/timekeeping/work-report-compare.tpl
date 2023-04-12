<!-- BEGIN: main -->
<link type="text/css" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<h1 class="mb-3">{LANG.work_report_compare}</h1>
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
<!-- BEGIN: data -->
<div class="table-responsive">
    <table class="table table-bordered">
        <tbody>
            <tr class="info">
                <th style="width: 1%;">{LANG.stt}</th>
                <th style="width: 39%;">{LANG.userid_assigned}</th>
                <th class="text-center" colspan="2">{LANG.work_by_checkin_out}</th>
                <th class="text-center" colspan="2">{LANG.work_by_report}</th>
            </tr>
            <!-- BEGIN: loop -->
            <tr>
                <td class="text-center text-nowrap">{TT}</td>
                <td>{ROW.fullname}</td>
                <td style="width: 14%;" class="text-center{ROW.bg1}"><strong>{ROW.time_checkin_out}</strong></td>
                <td style="width: 14%;" class="text-center{ROW.bg1}"><strong>{ROW.time_report}</strong></td>
                <td style="width: 14%;" class="text-center{ROW.bg2}"><strong>{ROW.time_checkin_out}</strong></td>
                <td style="width: 14%;" class="text-center{ROW.bg2}"><strong>{ROW.time_accepted}</strong></td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- END: data -->
<!-- END: main -->
