<!-- BEGIN: main -->
<!-- BEGIN: department_title -->
<ol class="breadcrumb breadcrumb-catnav">
    <!-- BEGIN: loop --><li><a href="{CAT.link}">{CAT.title}</a></li><!-- END: loop -->
    <!-- BEGIN: active --><li class="active">{CAT.title}</li><!-- END: active -->
</ol>
<!-- END: department_title -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <colgroup>
            <col class="w100">
        </colgroup>
        <thead>
            <tr>
                <th style="width: 80px;" class="text-nowrap">{LANG.order}</th>
                <th class="text-nowrap">{LANG.department_title}</th>
                <th style="width: 40%;" class="text-nowrap">{LANG.info}</th>
                <th style="width: 80px;" class="text-center text-nowrap">{LANG.function}</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td class="text-center">
                    <select id="change_weight_{ROW.id}" onchange="nv_change_department_weight('{ROW.id}');" class="form-control input-sm">
                        <!-- BEGIN: weight -->
                        <option value="{WEIGHT.w}"{WEIGHT.selected}>{WEIGHT.w}</option>
                        <!-- END: weight -->
                    </select>
                </td>
                <td>
                    <strong><a href="{ROW.url_view}">{ROW.title}</a></strong>
                    <!-- BEGIN: numsubcat -->(<span class="text-danger">{ROW.numsubcat}</span>)<!-- END: numsubcat -->
                </td>
                <td>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <strong class="text-primary">{ROW.assign_work}</strong> {LANG.assign_work}
                        </li>
                        <li>
                            <strong class="text-primary">{ROW.checkin_out_session}</strong> {LANG.checkin_out_session}
                        </li>
                        <li>
                            <strong class="text-primary">{ROW.view_all_task}</strong> {LANG.view_all_task}
                        </li>
                        <li>
                            <strong class="text-primary">{ROW.view_all_absence}</strong> {LANG.view_all_absence}
                        </li>
                        <li>
                            <strong class="text-primary">{ROW.check_work}</strong> {LANG.der_check_work}
                        </li>
                    </ul>
                </td>
                <td class="text-center text-nowrap">
                    <a class="btn btn-sm btn-default" href="{ROW.url_edit}"><i class="fa fa-edit"></i> {GLANG.edit}</a>
                    <a class="btn btn-sm btn-danger" href="javascript:void(0);" onclick="nv_delele_department('{ROW.id}');"><i class="fa fa-trash"></i> {GLANG.delete}</a>
                </td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<h2><i class="fa fa-th-large" aria-hidden="true"></i> {CAPTION}</h2>
<div class="panel panel-default">
    <div class="panel-body">
        <form method="post" action="{FORM_ACTION}" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-6 control-label">{LANG.department_title} <span class="fa-required text-danger">(<em class="fa fa-asterisk"></em>)</span>:</label>
                <div class="col-sm-18 col-lg-10">
                    <input type="text" name="title" value="{DATA.title}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label">{LANG.department_parentid}:</label>
                <div class="col-sm-18 col-lg-10">
                    <select class="form-control" name="sel_parentid" id="sel_parentid">
                        <!-- BEGIN: parentcat -->
                        <option value="{PARENTCAT.key}"{PARENTCAT.selected}>{PARENTCAT.title}</option>
                        <!-- END: parentcat -->
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-6 control-label">{LANG.description}:</label>
                <div class="col-sm-18 col-lg-10">
                    <textarea class="form-control" rows="3" name="description">{DATA.description}</textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-10 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="assign_work" value="1"{DATA.assign_work}> {LANG.assign_work}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-10 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="checkin_out_session" value="1"{DATA.checkin_out_session}> {LANG.checkin_out_session}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-10 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="view_all_task" value="1"{DATA.view_all_task}> {LANG.view_all_task}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-10 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="view_all_absence" value="1"{DATA.view_all_absence}> {LANG.view_all_absence}</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-18 col-lg-10 col-sm-offset-6">
                    <div class="checkbox">
                        <label><input type="checkbox" name="check_work" value="1"{DATA.check_work}> {LANG.der_check_work}</label>
                    </div>
                    <span class="help-block mb-0"><i>{LANG.der_check_work1}</i></span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-18 col-sm-offset-6">
                    <button type="submit" name="submit" value="submit" class="btn btn-primary">{GLANG.submit}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: main -->
