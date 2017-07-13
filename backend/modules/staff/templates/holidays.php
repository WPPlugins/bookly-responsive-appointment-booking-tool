<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="bookly-holidays-nav">
    <div class="input-group input-group-lg">
        <div class="input-group-btn">
            <button class="btn btn-default bookly-js-jCalBtn" data-trigger=".jCal .left" type="button">
                <i class="dashicons dashicons-arrow-left-alt2"></i>
            </button>
        </div>
        <input class="form-control text-center jcal_year" id="appendedPrependedInput"
               readonly type="text" value="">
        <div class="input-group-btn">
            <button class="btn btn-default bookly-js-jCalBtn" data-trigger=".jCal .right" type="button">
                <i class="dashicons dashicons-arrow-right-alt2"></i>
            </button>
        </div>
    </div>
</div>

<div class="bookly-js-holidays jCal-wrap bookly-margin-top-lg"></div>

<script>
    jQuery(function ($) {
        var d = new Date();
        $('.bookly-js-holidays').jCal({
            day         : new Date(d.getFullYear(), 0, 1),
            days        : 1,
            showMonths  : 12,
            scrollSpeed : 350,
            events      : <?php echo $holidays ?>,
            action      : 'bookly_staff_holidays_update',
            staff_id    : <?php echo $staff_id ?>,
            dayOffset   : <?php echo (int)get_option('start_of_week') ?>,
            loadingImg  : <?php echo json_encode( plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ) ) ?>
        });

        $('.bookly-js-jCalBtn').on('click', function(e) {
            e.preventDefault();
            var trigger = $(this).data('trigger');
            $('.bookly-js-holidays').find($(trigger)).trigger('click');
        })
    });
</script>