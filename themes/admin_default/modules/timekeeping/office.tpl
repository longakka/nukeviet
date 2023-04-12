<!-- BEGIN: list -->
<div class="alert alert-info">{LANG.office_note}</div>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th style="width: 100px;" class="text-nowrap"> {LANG.pos} </th>
                <th class="text-nowrap"> {LANG.office} </th>
                <th style="width: 150px;" class="text-nowrap text-center"> {LANG.manager} </th>
                <th style="width: 150px;" class="text-nowrap text-center"> {LANG.leader} </th>
                <th style="width: 200px;" class="text-nowrap">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <!-- BEGIN: loop -->
            <tr>
                <td>
                    <select name="p_{LOOP.id}" class="form-control newWeight">
                        <!-- BEGIN: option -->
                        <option value="{NEWWEIGHT.value}"{NEWWEIGHT.selected}>{NEWWEIGHT.value}</option>
                        <!-- END: option -->
                    </select>
                </td>
                <td>
                    {LOOP.title}
                </td>
                <td class="text-center">
                    <input type="checkbox" class="disabled" disabled value="1" {LOOP.manager_checked}/>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="disabled" disabled value="1" {LOOP.leader_checked}/>
                </td>
                <td class="text-center"><em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}&{NV_OP_VARIABLE}=office&id={LOOP.id}">{GLANG.edit}</a> - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="{LOOP.id}">{GLANG.delete}</a></td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>
<!-- END: list -->
<!-- BEGIN: main -->
<div id="pageContent">
    <div style="text-align: center"><em class="fa fa-spinner fa-spin fa-4x">&nbsp;</em><br />{LANG.wait}</div>
</div>
<div id="addContent">
    <h3><strong>{action_title} </strong>{name_office} </h3>
    <form id="addOffice" method="post" action="{ACTION_URL}">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <colgroup>
                    <col class="w200" />
                </colgroup>
                <tbody>
                    <tr>
                        <td>{LANG.office_name} <span style="color:red">*</span></td>
                        <td>
                            <input title="{LANG.office_name}" class="form-control" style="width:300px;" type="text" name="title" value="{OFFICE.title}" maxlength="255" />
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.manager}</td>
                        <td>
                            <input type="checkbox" class="form-control" name="manager" value="1" {OFFICE.manager_checked}/>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.leader}</td>
                        <td>
                            <input type="checkbox" class="form-control" name="leader" value="1" {OFFICE.leader_checked}/>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top">{LANG.description}</td>
                        <td><textarea style="width:300px;" class="form-control" name="description" id="description">{OFFICE.description}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="id" value="{OFFICE.id}" />
        <input type="hidden" name="checkss" value="{CHECKSS}" />
        <input type="hidden" name="save" value="1" />
        <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" />
    </form>
</div>
<script type="text/javascript">
    //<![CDATA[
    $( document ).ready(function() {
        $("div#pageContent").load("{MODULE_URL}&" + nv_fc_variable + "=office&list&nocache=" + nv_randomPassword(10));
    });
    $("form#addOffice").submit(function() {
        var url = $(this).attr("action");
        url +='&nocache=' + new Date().getTime();
        var a = $("input[name=title]").val();
        a = trim(a);
        $("input[name=title]").val(a);
        if (a == "") {
            alert("{LANG.errorIsEmpty}: " + $("input[name=title]").attr("title"));
            $("input[name=title]").select();
            return !1;
        }
        a = $(this).serialize();
        $("input[name=submit]").attr("disabled", "disabled");
        $.ajax({
            type : "POST",
            url : url,
            data : a,
            success : function(a) {
                a == "OK" ? window.location.href = window.location.href  : alert(a);
            }
        });
        return !1;
    });
    $("#pageContent").on("click", "a.del", function(e) {
        e.preventDefault();
        confirm("{LANG.delConfirm} ?") && $.ajax({
            type : "POST",
            url : "{MODULE_URL}&" + nv_fc_variable + "=office&nocache=" + new Date().getTime(),
            data : "delete=" + $(this).attr("href")+ "&checkss={CHECKSS}",
            success : function(a) {
                a == "OK" ? window.location.href = window.location.href : alert(a);
            }
        });
        return !1;
    });
    $("#pageContent").on("change","select.newWeight", function() {
        var a = $(this).attr("name").split("_"), b = $(this).val(), c = this, a = a[1];
        $(this).attr("disabled", "disabled");
        $.ajax({
            type : "POST",
            url : "{MODULE_URL}&" + nv_fc_variable + "=office&nocache=" + new Date().getTime(),
            data : "cWeight=" + b + "&id=" + a,
            success : function(a) {
                console.log('a==', a);
                a == "OK" ? window.location.href = window.location.href : alert("{LANG.errorChangeWeight}");
                $(c).removeAttr("disabled");
            }
        });
        return !1;
    });
    //]]>
</script>
<!-- END: main -->

