<!-- BEGIN: main -->
<!-- BEGIN: add_btn -->
<div class="form-group">
    <a href="#" data-toggle="add" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> {LANG.rank_add}</a>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('[data-toggle="add"]').on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#form-holder').offset().top
            }, 200, function() {
                $('[name="title"]').focus();
            });
        });
    });
</script>
<!-- END: add_btn -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <colgroup>
            <col class="w100">
        </colgroup>
        <thead>
            <tr>
                <th style="width: 10%" class="text-nowrap">{LANG.order}</th>
                <th style="width: 65%" class="text-nowrap">{LANG.rank_title}</th>
                <th style="width: 15%" class="text-center text-nowrap">{LANG.status}</th>
                <th style="width: 10%" class="text-center text-nowrap">{LANG.function}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td class="text-center">
                    <select id="change_weight_{ROW.id}" onchange="nv_change_rank_weight('{ROW.id}', '{NV_CHECK_SESSION}');" class="form-control input-sm">
                        <!-- BEGIN: weight -->
                        <option value="{WEIGHT.w}" {WEIGHT.selected}>{WEIGHT.w}</option>
                        <!-- END: weight -->
                    </select>
                </td>
                <td>
                    <strong>{ROW.title} <span class="text-danger">{ROW.monthly_salary}{LANG.rank_unit}</span></strong>
                    <div><small class="text-muted">{ROW.description}</small></div>
                </td>
                <td class="text-center">
                    <input name="status" id="change_status{ROW.id}" value="1" type="checkbox" {ROW.status_render} onclick="nv_change_rank_status('{ROW.id}', '{NV_CHECK_SESSION}');">
                </td>
                <td class="text-center text-nowrap">
                    <a class="btn btn-sm btn-default" href="{ROW.url_edit}"><i class="fa fa-edit"></i> {GLANG.edit}</a>
                    <a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="nv_delele_rank('{ROW.id}', '{NV_CHECK_SESSION}');"><i class="fa fa-trash"></i> {GLANG.delete}</a>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>

<div id="form-holder"></div>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<h2><i class="fa fa-th-large" aria-hidden="true"></i> {CAPTION}</h2>
<p class="text-info"><span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span> {LANG.is_required}</p>
<div class="panel panel-default">
    <div class="panel-body">
        <form method="post" action="{FORM_ACTION}" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_title">{LANG.rank_title} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18 col-lg-10">
                    <input type="text" id="element_title" name="title" value="{DATA.title}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_monthly_salary">{LANG.rank_monthly_salary} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18 col-lg-10">
                    <input type="text" id="element_monthly_salary" name="monthly_salary" value="{DATA.monthly_salary}" class="form-control ipt-money-d">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_description">{LANG.description}:</label>
                <div class="col-sm-18 col-lg-10">
                    <textarea class="form-control" rows="3" id="element_description" name="description">{DATA.description}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <input type="hidden" name="save" value="{NV_CHECK_SESSION}">
                    <button type="submit" class="btn btn-primary">{GLANG.submit}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- BEGIN: scroll -->
<script type="text/javascript">
    $(window).on('load', function() {
        $('html, body').animate({
            scrollTop: $('#form-holder').offset().top
        }, 200, function() {
            $('[name="title"]').focus();
        });
    });
</script>
<!-- END: scroll -->
<!-- END: main -->
