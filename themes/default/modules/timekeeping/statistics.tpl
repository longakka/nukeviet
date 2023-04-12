<!-- BEGIN: list -->
<h1 class="mb-2">{BANG_CONG}</h1>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-6">
        <div class="summary-statistics bg-success">
            <div class="summary-title">
                {LANG.workday}
            </div>
            <div class="summary-value">
                {sumWork}
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6">
        <div class="summary-statistics bg-info">
            <div class="summary-title">
                {LANG.late1}
            </div>
            <div class="summary-value">
                {sumLate}
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6">
        <div class="summary-statistics bg-warning">
            <div class="summary-title">
                {LANG.worktime}
            </div>
            <div class="summary-value">
                {sumWork_time}
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6">
        <div class="summary-statistics bg-danger">
            <div class="summary-title">
                {LANG.workout}
            </div>
            <div class="summary-value">
                {sumWork_out}
            </div>
        </div>
    </div>
</div>
<div class="flex-table mb-4">
    <div class="flex-table-head head-center">
        <div class="cw90">{LANG.stt}</div>
        <div class="cw170">{LANG.time}</div>
        <div class="cw120">{LANG.statistics_workday}</div>
        <div class="name">{LANG.place}</div>
        <div class="cw220">{LANG.info}</div>
    </div>
    <div class="flex-table-body">
        <!-- BEGIN: loop -->
        <div class="dropdown">
            <div class="item">
                <div class="cw90 center-desktop">
                    <div class="mb-1"><span class="label-name mr-0">#</span><span class="label square-label label-info">{LOOP.tt}</span></div>
                    <span class="label-name">{LANG.day}: </span>{LOOP.daysection} {LOOP.day_text}
                </div>
                <div class="cw170">
                    <div class="list">
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-success mr-1" title="{LANG.checkin}" data-toggle="tktooltip">{LANG.checkin_s}</span>
                                <span class="value">{LOOP.checkin_full}</span>
                            </div>
                        </div>
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-danger mr-1" title="{LANG.checkout}" data-toggle="tktooltip">{LANG.checkout_s}</span>
                                <span class="value">{LOOP.checkout_full}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cw120">
                    <div class="list">
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-success mr-1" title="{LANG.worktime}" data-toggle="tktooltip">{LANG.worktime_s}</span>
                                <span class="value">{LOOP.worktime}</span>
                            </div>
                        </div>
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-info mr-1" title="{LANG.workout}" data-toggle="tktooltip">{LANG.workout_s}</span>
                                <span class="value">{LOOP.workout}</span>
                            </div>
                        </div>
                        <div class="list-item">
                            <div class="list-item-inner">
                                <span class="label square-label label-warning mr-1" title="{LANG.late}" data-toggle="tktooltip">{LANG.late_s}</span>
                                <span class="value">{LOOP.late}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="name">
                    <span class="label-name">{LANG.place}: </span>{LOOP.place}
                </div>
                <div class="cw220">
                    <span class="label-name">{LANG.info}: </span>{LOOP.description}
                </div>
            </div>
        </div>
        <!-- END: loop -->
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('[data-toggle="tktooltip"]').tooltip();
});
</script>
<!-- END: list -->

<!-- BEGIN: main -->
<form method="post" action="{ACTION_URL}">
    <div class="form-search-inline">
        <div class="ipt-items">
            <div class="ipt-groups">
                <div class="ipt-item">
                    <label for="month">{LANG.month}</label>
                    <select class="form-control" id="month" name="month">
                        <!-- BEGIN: month -->
                        <option value="{MONTH.value}" {selected}>{MONTH.title}</option>
                        <!-- END: month -->
                    </select>
                </div>
                <div class="ipt-item">
                    <label for="year">{LANG.year}</label>
                    <select class="form-control" id="year" name="year">
                        <!-- BEGIN: year -->
                        <option value="{YEAR.value}" {selected}>{YEAR.title}</option>
                        <!-- END: year -->
                    </select>
                </div>
            </div>
        </div>
        <div class="ipt-btns">
            <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {LANG.select}</button>
        </div>
    </div>
</form>
{BANG_CONG}
<!-- END: main -->
