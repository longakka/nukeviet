<!-- BEGIN: main -->
<h1 class="mb-3">{LANG.pstat_title} {PROJECT.title}</h1>
<form action="{FORM_ACTION}" method="get" class="pb-2">
    <!-- BEGIN: no_rewrite -->
    <input type="hidden" name="{NV_LANG_VARIABLE}" value="{NV_LANG_DATA}">
    <input type="hidden" name="{NV_NAME_VARIABLE}" value="{MODULE_NAME}">
    <input type="hidden" name="{NV_OP_VARIABLE}" value="{OP}">
    <!-- END: no_rewrite -->
    <input type="hidden" name="id" value="{PROJECT.id}">
    <div class="form-search-responsive wrap">
        <div class="col-200 mb-2">
            <div class="ipt-item">
                <label for="filter_range">{LANG.search_from} - {LANG.search_to}</label>
                <div>
                    <input type="text" class="form-control ipt-daterangepicker" id="filter_range" name="r" value="{SEARCH.range}" autocomplete="off"/>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter" aria-hidden="true"></i> {LANG.view}</button>
        </div>
    </div>
</form>
<ul class="list-unstyled">
    <li><i class="fa fa-dot-circle-o" aria-hidden="true"></i> {LANG.worktime}: <strong class="text-danger">{TOTAL.work_time}</strong><!-- BEGIN: work_time --> ({TOTAL.work_time_day}d)<!-- END: work_time --></li>
    <li><i class="fa fa-dot-circle-o" aria-hidden="true"></i> {LANG.workout}: <strong class="text-danger">{TOTAL.over_time}</strong><!-- BEGIN: over_time --> ({TOTAL.over_time_day}d)<!-- END: over_time --></li>
    <li><i class="fa fa-dot-circle-o" aria-hidden="true"></i> {LANG.wrd_sum}: <strong class="text-danger">{TOTAL.all}</strong><!-- BEGIN: add --> ({TOTAL.add_day}d)<!-- END: add --></li>
</ul>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw70">{LANG.stt}</div>
        <div class="name">{LANG.userid_assigned}</div>
        <div class="cw170">{LANG.worktime}</div>
        <div class="cw170">{LANG.workout}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="item">
            <div class="cw70 center-desktop">
                <span class="label-name">{LANG.stt}: </span>{STT}
            </div>
            <div class="name">
                <span class="label-name">{LANG.userid_assigned}: </span>{ROW.staff}
            </div>
            <div class="cw170">
                <span class="label-name">{LANG.worktime}: </span><strong class="text-danger">{ROW.work_time}</strong><!-- BEGIN: work_time --> ({ROW.work_time_day}d)<!-- END: work_time -->
            </div>
            <div class="cw170">
                <span class="label-name">{LANG.workout}: </span><strong class="text-danger">{ROW.over_time}</strong><!-- BEGIN: over_time --> ({ROW.over_time_day}d)<!-- END: over_time -->
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<!-- END: main -->
