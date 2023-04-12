<!-- BEGIN: main -->
<h1 class="mb-3">{TITLE}</h1>
<p class="text-info"><span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span> {LANG.is_required}</p>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<div class="panel panel-default">
    <div class="panel-body">
        <form method="post" action="{FORM_ACTION}" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_title">{LANG.title} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18 col-lg-14">
                    <input type="text" id="element_title" name="title" value="{DATA.title}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_link">{LANG.link}:</label>
                <div class="col-sm-18 col-lg-14">
                    <input type="text" id="element_link" name="link" value="{DATA.link}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_work_content">{LANG.schedule_work_content} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18 col-lg-14">
                    <textarea class="form-control" rows="3" id="element_work_content" name="work_content">{DATA.work_content}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_work_note">{LANG.schedule_work_note}:</label>
                <div class="col-sm-18 col-lg-14">
                    <textarea class="form-control" rows="3" id="element_work_note" name="work_note">{DATA.work_note}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-14 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="status" value="1"{DATA.status}> {LANG.active1}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_work_time">{LANG.schedule_work_time} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="number" id="element_work_time" name="work_time" value="{DATA.work_time}" class="form-control" min="0" step="0.1" pattern="^([0-9]*)(\.*)([0-9]+)$" oninvalid="setCustomValidity(nv_number)" oninput="setCustomValidity('')">
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-14 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="apply_allsubs" value="1"{DATA.apply_allsubs}> {LANG.schedule_apply_allsubs}</label>
                    </div>
                </div>
            </div>
            <div class="form-group{CSS_APPLY_DEPARTMENTS}" id="ctn-apply-departments">
                <label class="col-sm-6 control-label">{LANG.schedule_apply_departments}:</label>
                <div class="col-sm-18">
                    <!-- BEGIN: department -->
                    <div class="checkbox">
                        <label><input type="checkbox" name="apply_departments[]" value="{DEPARTMENT.id}"{DEPARTMENT.checked}> {DEPARTMENT.title}</label>
                    </div>
                    <!-- END: department -->
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label">{LANG.schedule_apply_days} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18">
                    <!-- BEGIN: day -->
                    <div class="checkbox">
                        <label><input type="checkbox" name="apply_days[]" value="{DAY.key}"{DAY.checked}> {DAY.title}</label>
                    </div>
                    <!-- END: day -->
                </div>
            </div>
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <input type="hidden" name="add_time" value="{DATA.add_time}">
                    <input type="hidden" name="saveform" value="{NV_CHECK_SESSION}">
                    <button type="submit" class="btn btn-primary">{GLANG.submit}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    // Ẩn hiện phần chọn phòng ban con
    $('[name="apply_allsubs"]').on('change', function(e) {
        e.preventDefault();
        if ($(this).is(':checked')) {
            $('#ctn-apply-departments').addClass('hidden');
        } else {
            $('#ctn-apply-departments').removeClass('hidden');
        }
    });
});
</script>
<!-- END: main -->
