<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'general' ) ) ?>">
    <?php
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_lite_uninstall_remove_bookly_data', __( 'Delete all data on uninstall', 'bookly' ), __( 'If you want to replace Bookly Lite with full version of Bookly then disable this setting to prevent data from being deleted when you uninstall Bookly Lite.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_time_slot_length', __( 'Time slot length', 'bookly' ), __( 'Select a time interval which will be used as a step when building all time slots in the system.', 'bookly' ),
            $values['bookly_gen_time_slot_length'] );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_service_duration_as_slot_length', __( 'Service duration as slot length', 'bookly' ), __( 'Enable this option to make slot length equal to service duration at the Time step of booking form.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_default_appointment_status', __( 'Default appointment status', 'bookly' ), __( 'Select status for newly booked appointments.', 'bookly' ),
            array( array( \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_PENDING, __( 'Pending', 'bookly' ) ), array( \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_APPROVED, __( 'Approved', 'bookly' ) ), ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_min_time_prior_booking', __( 'Minimum time requirement prior to booking', 'bookly' ), __( 'Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'bookly' ),
            $values['bookly_gen_min_time_prior_booking'] );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_min_time_prior_cancel', __( 'Minimum time requirement prior to canceling', 'bookly' ), __( 'Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'bookly' ),
            $values['bookly_gen_min_time_prior_cancel'] );
        \BooklyLite\Lib\Utils\Common::optionText( 'bookly_gen_approve_page_url', __( 'Set the URL of a page that is shown to staff after they approve their appointment.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionText( 'bookly_gen_cancel_page_url', __( 'Set the URL of a page that is shown to clients after they successfully cancelled their appointment.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionText( 'bookly_gen_cancel_denied_page_url', __( 'Cancel appointment URL (denied)', 'bookly' ), __( 'Set the URL of a page that is shown to clients when the cancellation of appointment is not available anymore.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionNumeric( 'bookly_gen_max_days_for_booking', __( 'Number of days available for booking', 'bookly' ), __( 'Set how far in the future the clients can book appointments.', 'bookly' ), 1, 1 );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_use_client_time_zone', __( 'Display available time slots in client\'s time zone', 'bookly' ), __( 'The value is taken from client’s browser.', 'bookly' ) )
    ?>
    <div class="form-group">
        <label for="bookly_settings_final_step_url_mode"><?php _e( 'Final step URL', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that the user will be forwarded to after successful booking. If disabled then the default Done step is displayed.', 'bookly' ) ?></p>
        <select class="form-control" id="bookly_settings_final_step_url_mode">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ) : ?>
                <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'bookly_gen_final_step_url' ), $mode ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
        <input class="form-control"
               style="margin-top: 5px; <?php echo get_option( 'bookly_gen_final_step_url' ) == '' ? 'display: none' : '' ?>"
               type="text" name="bookly_gen_final_step_url"
               value="<?php form_option( 'bookly_gen_final_step_url' ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_allow_staff_edit_profile', __( 'Allow staff members to edit their profiles', 'bookly' ), __( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) ) ?>
    <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_gen_link_assets_method', __( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ), __( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ),
        array( array( 'enqueue', 'Enqueue' ), array( 'print', 'Print' ) ) )
    ?>
    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>