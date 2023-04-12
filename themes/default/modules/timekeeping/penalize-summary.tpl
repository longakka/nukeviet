<!-- BEGIN: main -->
<h1 class="mb-2">{LANG.view_penalize_summary}</h1>
<form method="get" action="{FORM_ACTION}" id="penalize-summary-form">
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
    var form = $('#penalize-summary-form');

    // thay đổi phòng ban thì load lại danh sách nhân viên
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
<div class="alert alert-info">{LANG.penalize_summary_empty}</div>
<!-- END: empty -->
<!-- BEGIN: please_choose -->
<div class="alert alert-info">{LANG.penalize_summary_guidance}</div>
<!-- END: please_choose -->
<!-- BEGIN: view -->
<h1 class="mb-2">{LANG.penalize_summary_detail} {DEPARTMENT_NAME} {STAFF} {LANG.view_report_from} {SEARCH_FROM} {LANG.view_report_to} {SEARCH_TO}</h1>
<div class="table-responsive list-penalize-summary">
<!-- <div class="table-responsive list-diary-penalize"> -->
    <table class="table table-striped table-bordered table-hover">
        <tbody>
            <tr>
                <!-- các thẻ trên tittle, số thứ tự, nhân sự, lý do, ngày tháng, trạng thái xử lý, xác nhận -->
                <th>{LANG.stt}</th>
                <th>{LANG.userid_assigned}</th>
                <th>{LANG.penalize_count}</th>
                <th>{LANG.penalize_handled_count}</th>
                <th>{LANG.penalize_not_handled_count}</th>
            </tr>

            <!-- Nội dung table, thêm thông tin của function vào đây-->
            <!-- Hiển thị tên nhân sự, số lần bị phạt, số lần đã xử lý, số lần chưa xử lý.-->
            <!-- BEGIN: loop -->
            <tr>
                <td> {TT} </td>
                <td>
                    <p><strong>{VIEW.name}</p></strong>
                    <p>{VIEW.department}</p>
                </td>
                <td class="text-center">{VIEW.penalize_count}</td>
                <td class="text-center">{VIEW.penalize_handled_count}</td>
                <td class="text-center">{VIEW.penalize_not_handled_count}</td>
            </tr>
            <!-- END: loop -->

        </tbody>
    </table>


</div>

<!-- END: view -->
<!-- BEGIN: generate_page -->
<div class="text-center">
    {NV_GENERATE_PAGE}
</div>
<!-- END: generate_page -->
<!-- END: main -->