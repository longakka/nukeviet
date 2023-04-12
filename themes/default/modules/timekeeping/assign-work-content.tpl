<!-- BEGIN: main -->
<h1 class="mb-3">{TITLE}</h1>
<p class="text-info"><span class="text-danger">(*)</span> {LANG.is_required}</p>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<div class="panel panel-default">
    <div class="panel-body">
        <form method="post" action="{FORM_ACTION}" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_title">{LANG.title} <span class="text-danger">(*)</span>:</label>
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
                <label class="col-sm-6 control-label" for="element_work_content">{LANG.schedule_work_content} <span class="text-danger">(*)</span>:</label>
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
                <label class="col-sm-6 control-label" for="element_work_time">{LANG.schedule_work_time} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-9 col-lg-7">
                    <input type="number" id="element_work_time" name="work_time" value="{DATA.work_time}" class="form-control" min="0" step="0.1" pattern="^([0-9]*)(\.*)([0-9]+)$" oninvalid="setCustomValidity(nv_number)" oninput="setCustomValidity('')">
                </div>
            </div>
            <!-- BEGIN: change_work_time -->
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_change_work_time">{LANG.asswork_change_work_time}:</label>
                <div class="col-sm-18 col-lg-14">
                    <textarea class="form-control" rows="3" id="element_change_work_time" name="change_work_time" placeholder="{LANG.asswork_change_work_time1}">{DATA.change_work_time}</textarea>
                </div>
            </div>
            <!-- END: change_work_time -->
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_userid">{LANG.asswork_userid} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-14">
                    <select class="form-control" name="userid" id="element_userid"{NO_CHAGE_USER}>
                        <option value="0">---</option>
                        <!-- BEGIN: staff -->
                        <option value="{STAFF.id}"{STAFF.selected}>{STAFF.title}</option>
                        <!-- END: staff -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label" for="element_taskcat_id">{LANG.asswork_taskcat_id} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-14">
                    <select class="form-control" name="taskcat_id" id="element_taskcat_id" data-selected="{DATA.taskcat_id}">
                        <option value="0">---</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-6" for="from_time">{LANG.start_day} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="form-pickdate">
                        <input name="from_time" id="from_time" title="{LANG.start_day}" class="form-control" value="{DATA.from_time}" maxlength="10" type="text" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-6" for="to_time">{LANG.end_day} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <div class="form-pickdate">
                        <input name="to_time" id="to_time" title="{LANG.end_day}" class="form-control" value="{DATA.to_time}" maxlength="10" type="text" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <input type="hidden" name="copy" value="{IS_COPY}">
                    <input type="hidden" name="add_time" value="{DATA.add_time}">
                    <input type="hidden" name="saveform" value="{NV_CHECK_SESSION}">
                    <button type="submit" class="btn btn-primary">{GLANG.submit}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>
<link type="text/css" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#element_userid').select2({
        width: '100%',
        language: '{NV_LANG_INTERFACE}'
    });
    $('#element_taskcat_id').select2({
        width: '100%',
        language: '{NV_LANG_INTERFACE}'
    });

    $("#from_time").datepicker({
        showOn : "both",
        dateFormat : "dd/mm/yy",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        buttonImage : nv_base_siteurl + "assets/images/calendar.gif",
        buttonImageOnly : true,
        yearRange: "-5:+10",
        onSelect: function () {
            start = $("#from_time").datepicker('getDate');
            end = $("#to_time").datepicker('getDate');
            if (end == null || start > end) {
                $("#to_time").datepicker("setDate", start);
            }
        },
        beforeShow: function() {
            setTimeout(function() {
                $('.ui-datepicker').css('z-index', 999999999);
            }, 0);
        }
    });

    $("#to_time").datepicker({
        showOn : "both",
        dateFormat : "dd/mm/yy",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        buttonImage : nv_base_siteurl + "assets/images/calendar.gif",
        buttonImageOnly : true,
        yearRange: "-5:+10",
        onSelect: function () {
        },
        beforeShow: function() {
            setTimeout(function() {
                $('.ui-datepicker').css('z-index', 999999999);
            }, 0);
        }
    });

    // Xử lý khi thay đổi loại công việc
    $('#element_userid').on('change', function() {
        $.ajax({
            type: 'POST',
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '={OP}&nocache=' + new Date().getTime(),
            data: {
                loadtaskcat: '{NV_CHECK_SESSION}',
                userid: $(this).val(),
            },
            dataType: 'json',
            cache: false,
            success: function(respon) {
                var select = $('[name="taskcat_id"]');
                var html = '<option value="0">---</option>';
                for (var i = 0; i < respon.length; i++) {
                    html += '<option value="' + respon[i].id + '"' + (respon[i].id == select.data('selected') ? ' selected="selected"' : '') + '>' + respon[i].title + '</option>';
                }
                select.html(html).select2({
                    width: '100%',
                    language: '{NV_LANG_INTERFACE}'
                }).trigger('change');
            },
            error: function(e, x, t) {
                alert('Error!!!');
            }
        });
    });
    $('#element_userid').trigger('change');
});
</script>
<!-- END: main -->
