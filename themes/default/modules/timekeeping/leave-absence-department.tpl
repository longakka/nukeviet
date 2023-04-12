<!-- BEGIN: main -->
<link type="text/css" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<h1 class="mb-3">{LANG.report_absence_department}</h1>
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
    <table class="table table-bordered table-hover list-leave-absence-department table-striped">
        <thead>
            <tr>
                <th>{LANG.stt}</th>
                <th>{LANG.userid_assigned}</th>
                <th class="text-center">{LANG.all_day_leave}</th>
                <th class="text-center">{LANG.all_day_not_confirm}</th>
                <th class="text-center">{LANG.all_day_approved}</th>
                <th class="text-center">{LANG.all_day_denied}</th>
                <th class="text-center">{LANG.num_absence_year}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td> {TT} </td>
                <td> <p><strong>{VIEW.fullname}</p></strong>
                    <p>{VIEW.department_name}</p></td>
                <td class="text-center"><a target="_blank" href="{VIEW.link_list_leave_absence}"><strong> {VIEW.total_day_all} </strong></a></td>
                <td class="text-center"><a target="_blank" href="{VIEW.link_list_leave_absence}"><strong> {VIEW.total_day_not_confirm} </strong></a></td>
                <td class="text-center"><a target="_blank" href="{VIEW.link_list_leave_absence}"><strong> {VIEW.total_day_approved}</strong></a></td>
                <td class="text-center"><a target="_blank" href="{VIEW.link_list_leave_absence}"><strong> {VIEW.total_day_denied} </strong></a></td>
                <td class="text-center"><strong> {VIEW.num_absence_year} </strong></td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- END: data -->
<!-- END: main -->
