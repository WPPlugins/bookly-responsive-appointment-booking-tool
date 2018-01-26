<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Plugin;
use BooklyLite\Lib\Utils\Common;
use BooklyLite\Lib\Entities\CustomerAppointment;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'general' ) ) ?>">
    <?php
        Common::optionToggle( 'bookly_gen_lite_uninstall_remove_bookly_data', __( 'Delete all data on uninstall', 'bookly' ), __( 'If you want to replace Bookly Lite with full version of Bookly then disable this setting to prevent data from being deleted when you uninstall Bookly Lite.', 'bookly' ) );
        Common::optionToggle( 'bookly_gen_time_slot_length', __( 'Time slot length', 'bookly' ), __( 'Select a time interval which will be used as a step when building all time slots in the system.', 'bookly' ),
            $values['bookly_gen_time_slot_length'] );
        Common::optionToggle( 'bookly_gen_service_duration_as_slot_length', __( 'Service duration as slot length', 'bookly' ), __( 'Enable this option to make slot length equal to service duration at the Time step of booking form.', 'bookly' ) );
        Common::optionToggle( 'bookly_gen_default_appointment_status', __( 'Default appointment status', 'bookly' ), __( 'Select status for newly booked appointments.', 'bookly' ), array( array( CustomerAppointment::STATUS_PENDING, __( 'Pending', 'bookly' ) ), array( CustomerAppointment::STATUS_APPROVED, __( 'Approved', 'bookly' ) ), ) );
        Common::optionToggle( 'bookly_gen_min_time_prior_booking', __( 'Minimum time requirement prior to booking', 'bookly' ), __( 'Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'bookly' ),
            $values['bookly_gen_min_time_prior_booking'] );
        Common::optionToggle( 'bookly_gen_min_time_prior_cancel', __( 'Minimum time requirement prior to canceling', 'bookly' ), __( 'Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'bookly' ),
            $values['bookly_gen_min_time_prior_cancel'] );
        Common::optionNumeric( 'bookly_gen_max_days_for_booking', __( 'Number of days available for booking', 'bookly' ), __( 'Set how far in the future the clients can book appointments.', 'bookly' ), 1, 1 );
        Common::optionToggle( 'bookly_gen_use_client_time_zone', __( 'Display available time slots in client\'s time zone', 'bookly' ), __( 'The value is taken from client’s browser.', 'bookly' ) );
        Common::optionToggle( 'bookly_gen_allow_staff_edit_profile', __( 'Allow staff members to edit their profiles', 'bookly' ), __( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) );
        Common::optionToggle( 'bookly_gen_link_assets_method', __( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ), __( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ),
            array( array( 'enqueue', 'Enqueue' ), array( 'print', 'Print' ) ) )
    ?>
    <div class="form-group">
        <label for="bookly_gen_collect_stats"><?php _e( 'Help us improve Bookly by sending anonymous usage stats', 'bookly' ); ?></label>
        <select class="form-control" name="bookly_gen_collect_stats" id="bookly_gen_collect_stats">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ) : ?>
                <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'bookly_gen_collect_stats' ), $mode ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="panel-footer">
        <?php Common::csrf() ?>
        <?php Common::submitButton() ?>
        <?php Common::resetButton() ?>
    </div>
</form>