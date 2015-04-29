<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="ab-booking-form-<?php echo $form_id ?>" class="ab-booking-form" style="overflow: hidden"></div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#ab-booking-form-<?php echo $form_id ?>').appointmentBooking({
            ajaxurl: '<?php echo admin_url('admin-ajax.php'); ?>',
            ab_attributes: <?php echo $attributes ?>,
            last_step: <?php echo (int)$booking_finished  ?>,
            cancelled: <?php echo (int)$booking_cancelled  ?>,
            form_id: '<?php echo $form_id ?>',
            start_of_week: <?php echo get_option( 'start_of_week' ) ?>,
            no_current_day_appointments: <?php echo intval( get_option( 'ab_settings_no_current_day_appointments' ) ) ?>,
            today_text: '<?php echo addslashes( __( 'Today', 'ab' ) ) ?>'
        });
    });
</script>