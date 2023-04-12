<!-- BEGIN: main -->
<!-- BEGIN: error -->
<div class="alert alert-danger">{ERROR}</div>
<!-- END: error -->

<div class="panel panel-default panel-bds-post">
    <div class="panel-body">
        <form method="post" action="{FORM_ACTION}" class="form-horizontal">
            <div class="form-group">
                <label class="control-label col-sm-6">{LANG.email_vp} <span class="text-danger">(*)</span>:</label>
                <div class="col-sm-18 col-lg-12">
                    <input name="email_vp" type="text" value="{DATA}" class="form-control" placeholder="Email"/>
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