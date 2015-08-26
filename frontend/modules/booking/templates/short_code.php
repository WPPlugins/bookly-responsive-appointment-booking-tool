<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<!--
Plugin Name: Bookly – Responsive WordPress Appointment Booking and Scheduling Plugin
Plugin URI: http://booking-wp-plugin.com
Version: 7.6.1
-->
<?php if ( $print_assets ) include '_css.php' ?>
<div id="ab-booking-form-<?php echo $form_id ?>" class="ab-booking-form" data-form_id="<?php echo $form_id ?>">
    <div style="text-align: center"><img src="<?php echo includes_url( 'js/tinymce/skins/lightgray/img/loader.gif' ) ?>" alt="<?php echo esc_attr( __( 'Loading...', 'bookly' ) ) ?>" /></div>
</div>
<script type="text/javascript">
    (function (win, fn) {
        var done = false, top = true,
            doc = win.document,
            root = doc.documentElement,
            modern = doc.addEventListener,
            add = modern ? 'addEventListener' : 'attachEvent',
            rem = modern ? 'removeEventListener' : 'detachEvent',
            pre = modern ? '' : 'on',
            init = function(e) {
                if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                if (!done) { done = true; fn.call(win, e.type || e); }
            },
            poll = function() {
                try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (!modern) if (root.doScroll) {
                try { top = !win.frameElement; } catch(e) { }
                if (top) poll();
            }
            doc[add](pre + 'DOMContentLoaded', init, false);
            doc[add](pre + 'readystatechange', init, false);
            win[add](pre + 'load', init, false);
        }
    })(window, function() {
        window.bookly({
            is_finished    : <?php echo (int)$booking_finished ?>,
            is_cancelled   : <?php echo (int)$booking_cancelled ?>,
            ajaxurl        : <?php echo json_encode( $ajax_url ) ?>,
            attributes     : <?php echo $attributes ?>,
            form_id        : <?php echo json_encode( $form_id ) ?>,
            start_of_week  : <?php echo (int) get_option( 'start_of_week' ) ?>,
            date_format    : <?php echo json_encode( AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_PICKADATE ) ) ?>,
            custom_fields  : <?php echo get_option( 'ab_custom_fields' ) ?>,
            show_calendar  : <?php echo intval( get_option( 'ab_appearance_show_calendar' ) ) ?>,
            intlTelInput_utils   : <?php echo json_encode( plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ) ) ?>,
            country        : <?php echo json_encode( get_option( 'ab_settings_phone_default_country' ) ) ?>
        });
    });
</script>