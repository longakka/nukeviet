<!-- BEGIN: taskcat_add -->
<div id="addContent">
    <form id="addtaskcat" method="post" action="{ACTION_URL}">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <colgroup>
                    <col class="w200" />
                </colgroup>
                <tbody>
                    <tr>
                        <td>{LANG.taskcat_name} <span style="color:red">*</span></td>
                        <td>
                            <input title="{LANG.taskcat_name}" class="form-control" style="width:300px;" type="text" name="title" value="{TASKCAT.title}" maxlength="255" />
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.alias}</td>
                        <td>
                            <div class="input-group w300">
                                <input class="form-control" type="text" name="alias" value="{TASKCAT.alias}" id="id_alias" />
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button">
                                        <i class="fa fa-refresh fa-lg" onclick="nv_get_alias('id_alias');">&nbsp;</i>
                                    </button> </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.cat_task}</td>
                        <td>
                            <select class="form-control w300" title="{LANG.cat_task}" name="parentid">
                                <option value="0">{LANG.catParent0}</option>
                                <!-- BEGIN: listcat -->
                                <option value="{ROW.id}" {selected}>{ROW.name}</option>
                                <!-- END: listcat -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{LANG.department}</td>
                        <td>
                            <select class="form-control w300" title="{LANG.department}" name="department">
                                <option value="0">{LANG.department}</option>
                                <!-- BEGIN: departments -->
                                <option value="{DEPARTMENT.id}" {selected}>{DEPARTMENT.title}</option>
                                <!-- END: departments -->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top">{LANG.description}</td>
                        <td><textarea style="width:300px;" class="form-control" name="description" id="description">{TASKCAT.description}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="id" value="{TASKCAT.id}" />
        <input type="hidden" name="checkss" value="{CHECKSS}" />
        <input type="hidden" name="save" value="1" />
        <input class="btn btn-primary" name="submit" type="submit" value="{LANG.save}" />
    </form>
</div>
<script type="text/javascript">
    //<![CDATA[
    $("[name='title']").change(function() {
        nv_get_alias('id_alias');
    });
    //]]>
</script>

<script type="text/javascript">
    //<![CDATA[
    function nv_get_alias(id) {
        var title = strip_tags($("[name='title']").val());
        if (title != '') {
            $.post(script_name + '?' + nv_name_variable + '=' + nv_module_name + '&op={OP}&nocache=' + new Date().getTime(), 'get_alias_title=' + encodeURIComponent(title) + '&id={TASKCAT.id}', function(res) {
                $("#" + id).val(strip_tags(res));
            });
        }
        return false;
    }

    $("form#addtaskcat").submit(function() {
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
            success : function(b) {
                if (b == "OK") {
                    window.location.href = "{LIST_TASKCAT_URL}";
                } else {
                    alert(b);
                    $("input[name=submit]").removeAttr("disabled");
                }
            }
        });
        return !1;
    });
    //]]>
</script>
<!-- END: taskcat_add -->
<!-- BEGIN: main -->
<div class="myh3">
    <span><a class="mycat" href="0">{LANG.catParent0}</a></span>
</div>

<div id="pageContent">
    <div style="text-align: center"><em class="fa fa-spinner fa-spin fa-4x">&nbsp;</em><br />{LANG.wait}</div>
</div>

<input name="addCat" class="btn btn-default" type="button" value="{LANG.taskcat_add}" />

<script type="text/javascript">
    //<![CDATA[
    $(function() {
        $("div#pageContent").load("{MODULE_URL}=taskcat&list=1&random=" + nv_randomPassword(10));
    });
    $("input[name=addCat]").click(function() {
        window.location.href = "{MODULE_URL}=taskcat_add";
        return false;
    });
    //]]>
</script>
<!-- END: main -->
<!-- BEGIN: list -->
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover" summary="{PARENTID}">
        <colgroup>
            <col class="w100" />
            <col />
            <col class="w150" span="2" />
        </colgroup>
        <thead>
            <tr>
                <th> {LANG.pos} </th>
                <th> {LANG.taskcat_name} </th>
                <th> {LANG.department} </th>
                <th> {LANG.description} </th>
                <th> {GLANG.status} </th>
                <th>&nbsp;</th>
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
                    <!-- BEGIN: count -->
                    <a class="yessub" href="{LOOP.id}">{LOOP.title}</a><span class="red">({LOOP.count})</span>
                    <!-- END: count -->
                    <!-- BEGIN: countEmpty --> {LOOP.title} <!-- END: countEmpty -->
                </td>
                <td>
                    {LOOP.department}
                </td>
                <td>
                    {LOOP.description}
                </td>
                <td class="text-center">
                    <input  type="checkbox" class="form-control" value="1" name="status" onchange="change_status(event, {LOOP.id})" {LOOP.status_checked}/>
                </td>
                <td><em class="fa fa-edit fa-lg">&nbsp;</em><a href="{MODULE_URL}=taskcat_add&id={LOOP.id}">{GLANG.edit}</a> - <em class="fa fa-trash-o fa-lg">&nbsp;</em><a class="del" href="{LOOP.id}">{GLANG.delete}</a></td>
            </tr>
            <!-- END: loop -->
        </tbody>
    </table>
</div>

<script type="text/javascript">
    //<![CDATA[
    $("a.yessub").click(function() {
        var a = $(this).attr("href");
        $("div.myh3").append('<span> &raquo; <a class="mycat" href="' + a + '">' + $(this).text() + "</a></span>");
        $("div#pageContent").load("{MODULE_URL}=taskcat&list=1&parentid=" + a + "&nocache=" + nv_randomPassword(10));
        return !1;
    });
    $("a.mycat").click(function() {
        if ($(this).parent().next().text() != "") {
            $(this).parent().nextAll().remove();
            var a = $(this).attr("href"), a = a != "0" ? "&parentid=" + a : "";
            $("div#pageContent").load("{MODULE_URL}=taskcat&list" + a + "&nocache=" + nv_randomPassword(10));
        }
        return !1;
    });
    $("a.del").click(function() {
        confirm("{LANG.delConfirm} ?") && $.ajax({
            type : "POST",
            url : "{MODULE_URL}=taskcat&nocache=" + new Date().getTime(),
            data : "delete=" + $(this).attr("href")+ "&checkss={CHECKSS}",
            success : function(a) {
                a == "OK" ? $("div#pageContent").load("{MODULE_URL}=taskcat&list&parentid={PARENTID}&nocache=" + nv_randomPassword(10)) : alert(a);
            }
        });
        return !1;
    });
    $("select.newWeight").change(function() {
        var a = $(this).attr("name").split("_"), b = $(this).val(), c = this, a = a[1];
        $(this).attr("disabled", "disabled");
        $.ajax({
            type : "POST",
            url : "{MODULE_URL}=taskcat&nocache=" + new Date().getTime(),
            data : "cWeight=" + b + "&id=" + a,
            success : function(a) {
                a == "OK" ? ( a = $("table.tab1").attr("summary"), $("div#pageContent").load("{MODULE_URL}=taskcat&list&parentid={PARENTID}&random=" + nv_randomPassword(10))) : alert("{LANG.errorChangeWeight}");
                $(c).removeAttr("disabled");
            }
        });
        return !1;
    });
    function change_status(e, id) {
        var c = this;
        $(this).attr("disabled", "disabled");
        $.ajax({
            type : "POST",
            url : "{MODULE_URL}=taskcat&nocache=" + new Date().getTime(),
            data : "change_status=1&status=" + (e.target.checked ? 1: 0) + "&id=" + id +'&checkss={CHECKSS}',
            success : function(a) {
                console.log('a=', a);
                a == "OK" ?  window.location.href =  window.location.href  : alert("{LANG.errorChangeStatus}");
                $(c).removeAttr("disabled");
            }
        });
    }

    //]]>
</script>
<!-- END: list -->
