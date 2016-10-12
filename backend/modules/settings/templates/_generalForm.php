<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'general' ) ) ?>" enctype="multipart/form-data"
      class="ab-settings-form">
    <div class="form-group">
        <label for="ab_lite_uninstall_remove_bookly_data"><?php _e( 'Delete all data on uninstall', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'If you want to replace Bookly Lite with full version of Bookly then disable this setting to prevent data from being deleted when you uninstall Bookly Lite.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_lite_uninstall_remove_bookly_data', array( ) ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_time_slot_length"><?php _e( 'Time slot length', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Select the time interval that will be used in frontend and backend, e.g. in calendar, second step of the booking process, while indicating the working hours, etc.', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_time_slot_length" id="ab_settings_time_slot_length">
            <?php foreach ( array( 5, 10, 12, 15, 20, 30, 45, 60, 90, 120, 180, 240, 360 ) as $duration ) :
                $duration_output = \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $duration * 60 ); ?>
                <option value="<?php echo $duration ?>" <?php selected( get_option( 'ab_settings_time_slot_length' ), $duration ) ?>>
                    <?php echo $duration_output ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_default_appointment_status"><?php _e( 'Default appointment status', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Select status for newly booked appointments.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_settings_default_appointment_status', array( array( \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_PENDING, __( 'Pending', 'bookly' ) ), array( \BooklyLite\Lib\Entities\CustomerAppointment::STATUS_APPROVED, __( 'Approved', 'bookly' ) ) ) ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_minimum_time_prior_booking"><?php _e( 'Minimum time requirement prior to booking', 'bookly' ) ?></label>
        <p class="help-block"><?php _e('Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'bookly') ?></p>
        <select class="form-control" name="ab_settings_minimum_time_prior_booking"
                id="ab_settings_minimum_time_prior_booking">
            <option value="0"><?php _e( 'Disabled', 'bookly' ) ?></option>
            <?php foreach ( array_merge( range( 1, 12 ), range( 24, 144, 24 ), range( 168, 672, 168 ) ) as $hour ) : ?>
                <option value="<?php echo $hour ?>" <?php selected( get_option( 'ab_settings_minimum_time_prior_booking' ), $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_minimum_time_prior_cancel"><?php _e( 'Minimum time requirement prior to canceling', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'bookly' ) ?></p>
        <select class="form-control" name="ab_settings_minimum_time_prior_cancel"
                id="ab_settings_minimum_time_prior_cancel">
            <option value="0"><?php _e( 'Disabled', 'bookly' ) ?></option>
            <?php foreach ( array_merge( array( 1 ), range( 2, 12, 2 ), range( 24, 168, 24 ) ) as $hour ) : ?>
                <option value="<?php echo $hour ?>" <?php selected( get_option( 'ab_settings_minimum_time_prior_cancel' ), $hour ) ?>><?php echo \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group">
        <label for="ab_settings_approve_page_url"><?php _e( 'Approve appointment URL', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that is shown to staff after they approve their appointment.', 'bookly' ) ?></p>
        <input class="form-control" type="text" name="ab_settings_approve_page_url" id="ab_settings_approve_page_url"
               value="<?php echo esc_attr( get_option( 'ab_settings_approve_page_url' ) ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <div class="form-group">
        <label for="ab_settings_cancel_page_url"><?php _e( 'Cancel appointment URL (success)', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that is shown to clients after they successfully cancelled their appointment.', 'bookly' ) ?></p>
        <input class="form-control" type="text" name="ab_settings_cancel_page_url" id="ab_settings_cancel_page_url"
               value="<?php echo esc_attr( get_option( 'ab_settings_cancel_page_url' ) ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <div class="form-group">
        <label for="ab_settings_cancel_denied_page_url"><?php _e( 'Cancel appointment URL (denied)', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that is shown to clients when the cancellation of appointment is not available anymore.', 'bookly' ) ?></p>
        <input class="form-control" type="text" id="ab_settings_cancel_denied_page_url"
               name="ab_settings_cancel_denied_page_url"
               value="<?php echo esc_attr( get_option( 'ab_settings_cancel_denied_page_url' ) ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <div class="form-group">
        <label for="ab_settings_maximum_available_days_for_booking"><?php _e( 'Number of days available for booking', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set how far in the future the clients can book appointments.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionNumeric( 'ab_settings_maximum_available_days_for_booking', 1, 1, 365 ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_use_client_time_zone"><?php _e( 'Display available time slots in client\'s time zone', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'The value is taken from client’s browser.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_settings_use_client_time_zone' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_final_step_url_mode"><?php _e( 'Final step URL', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that the user will be forwarded to after successful booking. If disabled then the default Done step is displayed.', 'bookly' ) ?></p>
        <select class="form-control" id="ab_settings_final_step_url_mode">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ) : ?>
                <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'ab_settings_final_step_url' ), $mode ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
        <input class="form-control"
               style="margin-top: 5px; <?php echo get_option( 'ab_settings_final_step_url' ) == '' ? 'display: none' : '' ?>"
               type="text" name="ab_settings_final_step_url"
               value="<?php echo esc_attr( get_option( 'ab_settings_final_step_url' ) ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <div class="form-group">
        <label for="ab_settings_allow_staff_members_edit_profile"><?php _e( 'Allow staff members to edit their profiles', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_settings_allow_staff_members_edit_profile' ) ?>
    </div>
    <div class="form-group">
        <label for="ab_settings_link_assets_method"><?php _e( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ) ?></p>
        <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'ab_settings_link_assets_method', array( array( 'enqueue', 'Enqueue' ), array( 'print', 'Print' ) ) ) ?>
    </div>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>