<!-- BEGIN: main -->
<h1 class="mb-3">{LANG.project}</h1>
<!-- BEGIN: view -->
<form action="{FORM_ACTION}" method="get">
    <!-- BEGIN: no_rewrite -->
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
    <!-- END: no_rewrite -->
    <div class="form-search-inline">
        <div class="ipt-items">
            <div class="ipt-groups">
                <div class="ipt-item">
                    <input class="form-control" type="text" value="{Q}" name="q" maxlength="255" placeholder="{LANG.search_title}" />
                </div>
                <div class="ipt-item">
                    <select class="form-control" name="s">
                        <!-- BEGIN: search_status -->
                        <option value="{SEARCH_STATUS.key}"{SEARCH_STATUS.selected}>{SEARCH_STATUS.title}</option>
                        <!-- END: search_status -->
                    </select>
                </div>
            </div>
        </div>
        <div class="ipt-btns">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i> {LANG.search_submit}</button>
            <a class="btn btn-success" href="#" data-toggle="addproject"><i class="fa fa-plus" aria-hidden="true"></i> {LANG.project_add}</a>
        </div>
    </div>
</form>
<h2 class="mb-3">{LANG.cat_department} {DEPARTMENT_NAME}</h2>
<!-- BEGIN: cat_title -->
<ol class="breadcrumb breadcrumb-catnav">
    <!-- BEGIN: loop --><li><a href="{CAT.link}">{CAT.title}</a></li><!-- END: loop -->
    <!-- BEGIN: active --><li class="active">{CAT.title}</li><!-- END: active -->
</ol>
<!-- END: cat_title -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>{LANG.project_name}</th>
                <th>{LANG.users_assigned}</th>
                <th class="w100 text-center">{LANG.active}</th>
                <th class="w200">&nbsp;</th>
            </tr>
        </thead>
        <!-- BEGIN: generate_page -->
        <tfoot>
            <tr>
                <td class="text-center" colspan="5">{NV_GENERATE_PAGE}</td>
            </tr>
        </tfoot>
        <!-- END: generate_page -->
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td> <a href="{VIEW.link_childrent}">{VIEW.title}</a> (<span class="text-danger">{VIEW.numsubcat}</span>)</td>
                <td class="text-center"> {VIEW.list_users_assigned} </td>
                <td class="text-center"><input type="checkbox" name="status" id="change_status_{VIEW.id}" value="{VIEW.id}" {CHECK} onclick="nv_change_status({VIEW.id});" /></td>
                <td class="text-center">
                    <a href="{VIEW.link_edit}" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i> {LANG.edit}</a>
                    <a href="{VIEW.link_delete}" onclick="return confirm(nv_is_del_confirm[0]);" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> {LANG.delete}</a>
                    <a href="{VIEW.link_statistics}" class="btn btn-xs btn-primary"><i class="fa fa-info"></i> {LANG.detail}</a>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="addproject"]').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#form-add-project').offset().top
        }, 200);
    });
});
</script>
<!-- END: view -->

<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<div class="panel panel-default" id="form-add-project">
    <div class="panel-body">
        <form class="form-horizontal" action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
            <input type="hidden" name="id" value="{ROW.id}" />
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.department}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <input class="form-control" type="text" value="{DEPARTMENT_NAME}" disabled/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.project_name}</strong> <span class="text-danger">(*)</span></label>
                <div class="col-sm-19 col-md-20">
                    <input class="form-control" type="text" name="title" value="{ROW.title}" required="required" oninvalid="setCustomValidity(nv_required)" oninput="setCustomValidity('')" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label" for="parentid">{LANG.asswork_taskcat_parent}:</label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control" name="parentid" id="parentid" data-selected="{DATA.taskcat_id}">
                         <!-- BEGIN: project_parent -->
                         <option value="{PROJECT_PARENT.key}"{PROJECT_PARENT.selected}>{PROJECT_PARENT.title}</option>
                         <!-- END: project_parent -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.project_public}</strong> </label>
                <div class="col-sm-19 col-md-20" style="padding-top: 5px">
                    <input class="form-control" type="checkbox" name="project_public" value="1" {project_public_checked}/>
                    <span>&nbsp;{LANG.project_public_info}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.users_assigned}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <select class="form-control users_assigned" name="users_assigned[]" multiple="multiple">
                        <!-- BEGIN: users_assigned -->
                        <option value="{users_assigned.userid}">{users_assigned.fullname}</option>
                        <!-- END: users_assigned -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-5 col-md-4 control-label"><strong>{LANG.description}</strong></label>
                <div class="col-sm-19 col-md-20">
                    <textarea class="form-control" style="height:100px;" cols="75" rows="5" name="description">{ROW.description}</textarea>
                </div>
            </div>

            <div class="form-group" style="text-align: center"><input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" /></div>
        </form>
    </div>
</div>

<link type="text/css" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" />
<link rel="stylesheet" href="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.css">
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/select2/i18n/{NV_LANG_DATA}.js"></script>
<script type="text/javascript" src="{NV_STATIC_URL}{NV_ASSETS_DIR}/js/jquery-ui/jquery-ui.min.js"></script>

<script type="text/javascript">
function check_project_public(a) {
    if($(a).is(":checked")){
        $('.users_assigned').prop('disabled', true);
    }
    else if($(a).is(":not(:checked)")){
        $('.users_assigned').prop('disabled', false);
    }
}
$(document).ready(function(){
    check_project_public('input[name="project_public"]');
    $('.users_assigned').select2({
        width: '100%'
    }).val([<!-- BEGIN: users_assigned_list -->{user_assigned}, <!-- END: users_assigned_list-->]).trigger('change');
});
</script>
<script type="text/javascript">
function nv_get_alias(id) {
    var title = strip_tags($("[name='title']").val());
    if (title != '') {
        $.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=project&nocache=' + new Date().getTime(), 'get_alias_title=' + encodeURIComponent(title), function(res) {
            $("#"+id).val(strip_tags(res));
        });
    }
    return false;
}

function nv_change_status(id) {
    var new_status = $('#change_status_' + id).is(':checked') ? true : false;
    if (confirm(nv_is_change_act_confirm[0])) {
        var nv_timer = nv_settimeout_disable('change_status_' + id, 5000);
        $.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=project&nocache=' + new Date().getTime(), 'change_status=1&id='+id, function(res) {
            var r_split = res.split('_');
            if (r_split[0] != 'OK') {
                alert(nv_is_change_act_confirm[2]);
            }
        });
    } else {
        $('#change_status_' + id).prop('checked', new_status ? false : true);
    }
    return;
}
</script>
<!-- END: main -->
