<!-- BEGIN: main -->
<h1 class="mb-3">{LANG.work_manager}</h1>
<form action="{FORM_ACTION}" method="get" class="pb-2">
    <!-- BEGIN: no_rewrite -->
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
    <!-- END: no_rewrite -->
    <div class="form-search-responsive wrap">
        <div class="col-desktop-3 col-mobile-1 mb-2">
            <div class="ipt-item">
                <label for="ele_q">{LANG.keywork}</label>
                <input class="form-control" id="ele_q" type="text" value="{SEARCH.q}" name="q" maxlength="255" placeholder="{LANG.search_title}">
            </div>
        </div>
        <div class="col-desktop-3 col-mobile-2 mb-2">
            <div class="ipt-item">
                <label for="ele_project">{LANG.project_alias}</label>
                <select class="form-control" name="p" id="ele_project">
                    <option value="0">{LANG.all}</option>
                    <!-- BEGIN: search_project -->
                    <option value="{PROJECT.key}" {PROJECT.selected}>{PROJECT.title}</option>
                    <!-- END: search_project -->
                </select>
            </div>
        </div>
        <div class="col-desktop-3 col-mobile-2 mb-2">
            <div class="ipt-item">
                <label for="ele_status">{LANG.status}</label>
                <select class="form-control" name="s" id="ele_status">
                    <option value="0">{LANG.any}</option>
                    <!-- BEGIN: search_status -->
                    <option value="{STATUS.key}" {STATUS.selected}>{STATUS.title}</option>
                    <!-- END: search_status -->
                </select>
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
            <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> {LANG.search_submit}</button>
        </div>
        <div class="mb-2">
            <button type="button" class="btn btn-success" onclick="scrollToAnchor('add_new');"><i class="fa fa-plus" aria-hidden="true"></i> {LANG.add}</button>
        </div>
    </div>
</form>
<!-- BEGIN: view -->
<!-- BEGIN: total -->
<h2 class="mb-3">{LANG.work_day}</h2>
<table class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th style="width: 50%;"> {LANG.project_name} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_worktime} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_overtime} </th>
            <th style="width: 16,66%;" class="text-center"> {LANG.total_acceptedtime} </th>
        </tr>
    </thead>
    <tbody>
        <!-- BEGIN: loop -->
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
        <!-- END: loop -->
        <tr>
            <th class="text-center">{LANG.total_time}</th>
            <th class="text-center">{TOTAL_WORKTIME}d</th>
            <th class="text-center">{TOTAL_OVERTIME}d</th>
            <th class="text-center">{TOTAL_ACCEPTEDTIME}d</th>
        </tr>
    </tbody>
</table>
<!-- END: total -->
<h2 class="mb-3">{LANG.work_list}</h2>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <!-- BEGIN: col_number --><th style="width: 1%;" class="text-nowrap">{LANG.number}</th><!-- END: col_number -->
                <th style="width: 1%;" class="text-nowrap">{LANG.time_create}</th>
                <th>{LANG.title}</th>
                <th class="w50 text-center">{LANG.work_time}</th>
                <th class="w50 text-center">{LANG.over_time}</th>
                <th class="w50 text-center">{LANG.work_accepted_time}</th>
                <th class="w100 text-center">{LANG.status}</th>
                <th class="w100">{LANG.action}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop_day -->
            <!-- BEGIN: loop -->
            <tr>
                <!-- BEGIN: col_number-->
                <td class="text-center" <!-- BEGIN: rowspan--> rowspan="{ROWSPAN}"<!-- END: rowspan-->>{TT}</td>
                <!-- END: col_number-->
                <!-- BEGIN: col_day-->
                <td class="text-center text-nowrap"<!-- BEGIN: rowspan--> rowspan="{ROWSPAN}"<!-- END: rowspan-->>{DAY}</td>
                <!-- END: col_day-->
                <td>
                    <!-- BEGIN: status1 -->
                    <i class="fa fa-play-circle text-info" aria-hidden="true" title="{VIEW.status_text}" data-toggle="worktip"></i>
                    <!-- END: status1 -->
                    <!-- BEGIN: status2 -->
                    <i class="fa fa-check-circle text-success" aria-hidden="true" title="{VIEW.status_text}" data-toggle="worktip"></i>
                    <!-- END: status2 -->
                    <!-- BEGIN: status3 -->
                    <i class="fa fa-pause-circle-o text-danger" aria-hidden="true" title="{VIEW.status_text}" data-toggle="worktip"></i>
                    <!-- END: status3 -->
                    <a href="#" data-toggle="modal" data-target="#work-detail{VIEW.id}"> {VIEW.title} </a>
                    <div>
                        <small class="text-muted">{VIEW.taskcat_id}</small>
                    </div>
                    <div class="modal fade" id="work-detail{VIEW.id}" tabindex="-1" role="dialog" aria-labelledby="mdTitle{VIEW.id}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h5 class="modal-title" id="mdTitle{VIEW.id}">{VIEW.title}</h5>
                            </div>
                            <div class="modal-body">
                                <label> {LANG.link}: </label></br><a class="cls_link_detail" href="{VIEW.link}" target="_blank">{VIEW.link}</a></br>
                                <label>{LANG.content}: </label></br> {VIEW.content}</br>
                                <label>{LANG.description}: </label></br> {VIEW.description}
                            </div>
                        </div>
                    </div>
                </div>
                </td>
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
                    <strong class="text-success">{VIEW.accepted_time}h</strong>
                    <!-- BEGIN: accepted_time -->
                    ({VIEW.accepted_time_day}d)
                    <!-- END: accepted_time -->
                </td>
                <td> {VIEW.status_text} </td>
                <td class="text-center">
                    <a href="{VIEW.link_copy}" title="{LANG.work_copy}" class="btn btn-info btn-xs" data-toggle="worktip" data-container="body" data-animation="false"><i class="fa fa-copy"></i></a>
                    <a href="{VIEW.link_edit}" title="{LANG.edit}" class="btn btn-default btn-xs" data-toggle="worktip" data-container="body" data-animation="false"><i class="fa fa-pencil"></i></a>
                    <a href="{VIEW.link_delete}" onclick="return confirm(nv_is_del_confirm[0]);" class="btn btn-danger btn-xs" title="{LANG.delete}" data-toggle="worktip" data-container="body" data-animation="false"><i class="fa fa-trash"></i></a>
                </td>
            </tr>
            <!-- END: loop -->
            <!-- END: loop_day -->
        </tbody>
    </table>
</div>
<!-- BEGIN: generate_page -->
<div class="text-center">
    {NV_GENERATE_PAGE}
</div>
<!-- END: generate_page -->
<!-- END: view -->
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->
<h2 class="mb-3">{LANG.add}</h2>
<div class="panel panel-default">
    <div class="panel-body" id="add_new" >
        <form id="form-work-content" class="form-horizontal" action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
            <input type="hidden" name="id" value="{ROW.id}" />
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.time_create}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-8 col-md-6">
                    <div class="input-group">
                        <input class="form-control" type="text" name="time_create" value="{ROW.time_create}" id="time_create" pattern="^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{1,4}$" required="required" oninvalid="setCustomValidity(nv_required)" oninput="setCustomValidity('')" autocomplete="off" readonly/>
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="time_create-btn">
                                <em class="fa fa-calendar fa-fix"> </em>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-sm-8 col-md-6 mt-sm-3">
                    <select class="form-control" name="work_type">
                        <!-- BEGIN: work_type -->
                        <option value="{WORK_TYPE.key}"{WORK_TYPE.selected}>{WORK_TYPE.title}</option>
                        <!-- END: work_type -->
                    </select>
                </div>
            </div>
            <div class="form-group{CSS_ASSIGNED}" id="area-assigned-work">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.work_pick}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control" name="assigned_task" data-selected="{ROW.assigned_task}">
                    </select>
                </div>
            </div>
            <div class="form-group{CSS_SCHEDULE}" id="area-schedule-work">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.work_pick}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control" name="schedule_task" data-selected="{ROW.schedule_task}">
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.taskcat_id}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control" name="taskcat_id">
                        <option value=""> --- </option>
                        <!-- BEGIN: select_taskcat_id -->
                        <option value="{OPTION.key}" {OPTION.selected}>{OPTION.title}</option>
                        <!-- END: select_taskcat_id -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.title}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <input class="form-control" type="text" name="title" value="{ROW.title}" required="required" oninvalid="setCustomValidity(nv_required)" oninput="setCustomValidity('')" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.status}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control" name="status">
                        <!-- BEGIN: select_status -->
                        <option value="{OPTION.key}" {OPTION.selected}>{OPTION.title}</option>
                        <!-- END: select_status -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.link}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <input class="form-control" type="text" name="link" value="{ROW.link}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.content}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <textarea class="form-control" rows="5" name="content">{ROW.content}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.work_time}</strong> <small class="text-danger">(**)</small></label>
                <div class="col-sm-19 col-md-4">
                    <input class="form-control" type="number" name="work_time" value="{ROW.work_time}" step="0.1" pattern="^([0-9]*)(\.*)([0-9]+)$" oninvalid="setCustomValidity(nv_number)" oninput="setCustomValidity('')" />
                </div>
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.over_time}</strong> <small class="text-danger">(**)</small></label>
                <div class="col-sm-19 col-md-4">
                    <input class="form-control" type="number" name="over_time" value="{ROW.over_time}" step="0.1" pattern="^([0-9]*)(\.*)([0-9]+)$" oninvalid="setCustomValidity(nv_number)" oninput="setCustomValidity('')" />
                </div>
                <div class="mt-1 col-sm-19 col-sm-offset-5 col-md-offset-4 hidden" id="note-assign-work-process">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.description}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <textarea class="form-control" rows="5" name="description">{ROW.description}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-19 col-md-20 col-sm-offset-5 col-md-offset-4">
                    <small class="text-danger">(**)</small> {LANG.work_require_oot}
                </div>
            </div>
            <div class="row text-center">
                <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" />
            </div>
        </form>
    </div>
</div>

<link type="text/css" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/language/jquery.ui.datepicker-{NV_LANG_INTERFACE}.js"></script>
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_INTERFACE}.js"></script>

<script type="text/javascript">
function scrollToAnchor(aid) {
    $('html,body').animate({
        scrollTop: $("#"+aid).offset().top
    }, 200);
}

$(document).ready(function() {
    $('[data-toggle="worktip"]').tooltip();

    $("#time_create").datepicker({
        dateFormat : "dd/mm/yy",
        orientation: "bottom",
        changeMonth : true,
        changeYear : true,
        showOtherMonths : true,
        autoclose: true,
        onSelect: function() {
            $('[name="work_type"]').trigger('change')
        }
    });

    $("#time_create-btn").click(function(){
        $("#time_create").datepicker('show');
    });

    var form = $('#form-work-content');
    var note_assigned = $('#note-assign-work-process');

    // Xử lý khi thay đổi loại công việc
    $('[name="work_type"]').on('change', function() {
        var $this = $(this);

        if ($this.attr('readonly')) {
            $this.val($this.data('work-type'));
            return;
        }
        var work_type = $(this).val();

        $this.data('work-type', work_type);
        $this.attr('readonly', 'readonly');

        var area_assigned = $('#area-assigned-work');
        var area_schedule = $('#area-schedule-work');

        if (work_type == 0) {
            // Nhập việc
            area_assigned.addClass('hidden');
            area_schedule.addClass('hidden');
            $this.removeAttr('readonly');
            note_assigned.addClass('hidden');
        } else if (work_type == 1) {
            // Làm việc được giao
            area_assigned.removeClass('hidden');
            area_schedule.addClass('hidden');

            var select = $('select', area_assigned);
            select.html('').prop('disabled', true);

            // Load việc được giao
            $.ajax({
                type: 'POST',
                url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '={OP}&nocache=' + new Date().getTime(),
                data: {
                    loadaswork: '{NV_CHECK_SESSION}',
                    time: $('[name="time_create"]').val(),
                },
                dataType: 'json',
                cache: false,
                success: function(respon) {
                    select.prop('disabled', false);
                    $this.removeAttr('readonly');

                    var html = '<option value="0">---</option>';
                    window.assign_works = {};
                    for (var i = 0; i < respon.rows.length; i++) {
                        html += '<option value="' + respon.rows[i].id + '"' + (respon.rows[i].id == select.data('selected') ? ' selected="selected"' : '') + '>' + respon.rows[i].title + '</option>';
                        window.assign_works[respon.rows[i].id] = respon.rows[i];
                    }
                    select.html(html).select2({
                        width: '100%',
                        language: '{NV_LANG_INTERFACE}'
                    }).trigger('change');
                    if (respon.message != '') {
                        alert(respon.message);
                    }
                },
                error: function(e, x, t) {
                    alert('Error!!!');
                    select.prop('disabled', false);
                    $this.removeAttr('readonly');
                }
            });
        } else {
            // Làm lịch cố định
            area_assigned.addClass('hidden');
            area_schedule.removeClass('hidden');
            note_assigned.addClass('hidden');

            var select = $('select', area_schedule);
            select.html('').prop('disabled', true);

            // Lấy lịch cố định ra
            $.ajax({
                type: 'POST',
                url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '={OP}&nocache=' + new Date().getTime(),
                data: {
                    loadschedule: '{NV_CHECK_SESSION}',
                    time: $('[name="time_create"]').val(),
                },
                dataType: 'json',
                cache: false,
                success: function(respon) {
                    select.prop('disabled', false);
                    $this.removeAttr('readonly');

                    var html = '<option value="0">---</option>';
                    window.schedule_works = {};
                    for (var i = 0; i < respon.rows.length; i++) {
                        html += '<option value="' + respon.rows[i].id + '"' + (respon.rows[i].id == select.data('selected') ? ' selected="selected"' : '') + '>' + respon.rows[i].title + '</option>';
                        window.schedule_works[respon.rows[i].id] = respon.rows[i];
                    }
                    select.html(html).select2({
                        width: '100%',
                        language: '{NV_LANG_INTERFACE}'
                    }).trigger('change');
                    if (respon.message != '') {
                        alert(respon.message);
                    }
                },
                error: function(e, x, t) {
                    alert('Error!!!');
                    select.prop('disabled', false);
                    $this.removeAttr('readonly');
                }
            });
        }
    });

    // Xử lý khi chọn việc cố định
    $('[name="schedule_task"]').on('change', function() {
        var task_id = $(this).val();
        if (task_id == 0 || '{ROW.id}' != '0') {
            return;
        }
        $('[name="title"]', form).val(schedule_works[task_id].title);
        $('[name="link"]', form).val(schedule_works[task_id].link);
        $('[name="content"]', form).val(schedule_works[task_id].work_content);
        $('[name="description"]', form).val(schedule_works[task_id].work_note);
        $('[name="work_time"]', form).val(schedule_works[task_id].work_time);
        $('[name="over_time"]', form).val('0');
    });

    // Xử lý khi chọn việc được giao
    $('[name="assigned_task"]').on('change', function() {
        var task_id = $(this).val();
        if (task_id == 0) {
            note_assigned.addClass('hidden');
            return;
        }
        $('[name="title"]', form).val(assign_works[task_id].title);
        $('[name="link"]', form).val(assign_works[task_id].link);
        
        let content = $('[name="content"]', form).val();
        content = content.trim();
        if (content.length == 0) {
            $('[name="content"]', form).val(assign_works[task_id].work_content);
        }

        let description = $('[name="description"]', form).val();
        description = description.trim();
        if (description.length == 0) {
            $('[name="description"]', form).val(assign_works[task_id].work_note);
        }

        var taskcat = $('[name="taskcat_id"]');
        if ($('option[value="' + assign_works[task_id].taskcat_id + '"]', taskcat).length) {
            taskcat.val(assign_works[task_id].taskcat_id);
            note_assigned.removeClass('text-danger text-success text-info text-warning');
            note_assigned.removeClass('hidden').html(assign_works[task_id].worked_note);
            if (assign_works[task_id].worked_level == 1) {
                note_assigned.addClass('text-success');
            } else if (assign_works[task_id].worked_level == 2) {
                note_assigned.addClass('text-info');
            } else if (assign_works[task_id].worked_level == 3) {
                note_assigned.addClass('text-warning');
            } else {
                note_assigned.addClass('text-danger');
            }
        } else {
            note_assigned.addClass('hidden');
        }
    });

    $('[name="work_type"]').trigger('change');
});
</script>
<!-- END: main -->
