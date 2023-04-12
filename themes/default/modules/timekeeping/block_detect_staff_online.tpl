<!-- BEGIN: main -->
<div class="modal modal-center" tabindex="-1" role="dialog" id="block_modalwelcome" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body p-8 text-center">
                <p style="text-align: justify">{LANG.good_morning}</p>
            </div>
            <div class="modal-footer text-center">
                <a href="javascript.void(0)" class="btn btn-success" data-dismiss="modal">{LANG.i_am_ready}</a>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-center" tabindex="-1" role="dialog" id="modal_detect_staff_online" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="text-center m-bottom">
                    <p>{LANG.do_you_checkin}</p>
                </div>
                <div class="text-center" data-toggle="checkinBtns">
                    <button type="button" class="btn btn-success" data-toggle="block_checkinbtn">{LANG.checkin}</button>
                    <button type="button" class="btn btn-danger" data-toggle="block_checkinbtn_cancel" data-dismiss="modal">{LANG.cancel}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
//<![CDATA[
    function block_locate_checkin(position) {
        if (position.coords == 'undefined') {
            return false;
        }
        let geolat = position.coords.latitude;
        let geolng = position.coords.longitude;
        $.ajax({
            type: "POST",
            url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '={module_name}' + '&' + nv_fc_variable + '=checkio&nocache=' + new Date().getTime(),
            data: 'geolat=' + geolat + '&geolng=' + geolng + '&checkin=1&checkss={CHECKSS}',
            success: function(b) {
                if (b == "OK") {
                    $('#block_modalwelcome').modal('show');
                    $('#modal_detect_staff_online').modal('hide');
                } else {
                    alert('{LANG.error_identify_location}');
                }
            }
        });
    }
    $(document).ready(function() {
        $('#modal_detect_staff_online').modal('show');
        $('[data-toggle="block_checkinbtn"]').on('click', function(e) {
            e.preventDefault();
            if (!navigator.geolocation) {
                alert('{LANG.Geolocation_is_not_supported_by_this_browser}');
                return -1;
            }
            $('[data-toggle="block_checkinbtn"]').prop('disabled', true);
            navigator.geolocation.getCurrentPosition(block_locate_checkin, (error) => {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorStr = '{LANG.You_have_to_brower_identify_your_address}';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorStr = '{LANG.Location_information_is_unavailable}';
                        break;
                    case error.TIMEOUT:
                        errorStr = '{LANG.The_request_to_get_user_location_timed_out}';
                        break;
                    case error.UNKNOWN_ERROR:
                        errorStr ='{LANG.error_identify_location}';
                        break;
                    default:
                        errorStr = '{LANG.error_identify_location}';
                }
                $('[data-toggle="block_checkinbtn"]').prop('disabled', false);
                alert(errorStr);
            });

        })
        $('[data-toggle="block_checkinbtn_cancel"]').on('click', function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '={module_name}' + '&' + nv_fc_variable + '=checkio&nocache=' + new Date().getTime(),
                data: 'checkin_cancel=1',
                success: function(b) {
                    
                }
            });
        })
    })
//]]>
</script>
<!-- END: main -->
