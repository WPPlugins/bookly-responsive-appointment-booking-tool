<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_general' ) ) ?>" enctype="multipart/form-data" class="ab-settings-form">
    <table class="form-horizontal">
        <tr>
            <td>
                <label for="ab_settings_time_slot_length"><?php _e( 'Time slot length', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <select class="form-control" name="ab_settings_time_slot_length" id="ab_settings_time_slot_length">
                    <?php
                    foreach ( array( 5, 10, 12, 15, 20, 30, 60, 90, 120, 180, 240, 360 ) as $duration ) {
                        $duration_output = AB_DateTimeUtils::secondsToInterval( $duration * 60 );
                        ?>
                        <option value="<?php echo $duration ?>" <?php selected( get_option( 'ab_settings_time_slot_length' ), $duration ) ?>>
                            <?php echo $duration_output ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'Select the time interval that will be used in frontend and backend, e.g. in calendar, second step of the booking process, while indicating the working hours, etc.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_minimum_time_prior_booking"><?php _e( 'Minimum time requirement prior to booking', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <select class="form-control" name="ab_settings_minimum_time_prior_booking" id="ab_settings_minimum_time_prior_booking">
                    <option value="0" <?php selected( get_option( 'ab_settings_minimum_time_prior_booking' ), 0 ) ?>><?php _e( 'Disabled', 'bookly' ) ?></option>
                    <?php foreach ( array_merge( range( 1, 12 ), array( 24, 48 ) ) as $hour ): ?>
                        <option value="<?php echo $hour ?>" <?php selected( get_option( 'ab_settings_minimum_time_prior_booking' ), $hour ) ?>><?php echo AB_DateTimeUtils::secondsToInterval( $hour * 3600 ) ?></option>
                    <?php endforeach ?>
                </select>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'Set a minimum amount of time before the chosen appointment (for example, require the customer to book at least 1 hour before the appointment time).', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_maximum_available_days_for_booking"><?php _e( 'Number of days available for booking', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <input class="form-control" type="number" id="ab_settings_maximum_available_days_for_booking" name="ab_settings_maximum_available_days_for_booking" min="1" max="365" value="<?php echo esc_attr( get_option( 'ab_settings_maximum_available_days_for_booking', 365 ) ) ?>" />
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'Specify the number of days that should be available for booking at step 2 starting from the current day.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_use_client_time_zone"><?php _e( 'Display available time slots in client\'s time zone', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::optionToggle( 'ab_settings_use_client_time_zone' ) ?>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'The value is taken from client’s browser.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_cancel_page_url"><?php _e( 'Cancel appointment page URL', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <input class="form-control" type="text" name="ab_settings_cancel_page_url" id="ab_settings_cancel_page_url" value="<?php echo esc_attr( get_option( 'ab_settings_cancel_page_url' ) ) ?>" placeholder="<?php echo esc_attr( __( 'Enter a URL', 'bookly' ) ) ?>" />
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'Insert a URL of a page that is shown to clients after they have cancelled their booking.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_final_step_url_mode"><?php _e( 'Final step URL', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <select class="form-control" id="ab_settings_final_step_url_mode">
                    <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ): ?>
                        <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'ab_settings_final_step_url' ), $mode ) ?> ><?php echo $text ?></option>
                    <?php endforeach ?>
                </select>
                <input class="form-control" style="margin-top: 5px; <?php echo get_option( 'ab_settings_final_step_url' ) == ''? 'display: none':''; ?>" type="text" name="ab_settings_final_step_url" value="<?php echo esc_attr( get_option( 'ab_settings_final_step_url' ) ) ?>" placeholder="<?php echo esc_attr( __( 'Enter a URL', 'bookly' ) ) ?>" />
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'Set a URL of a page that the user will be forwarded to after successful booking. If disabled then the default step 5 is displayed.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_allow_staff_members_edit_profile"><?php _e( 'Allow staff members to edit their profiles', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::optionToggle( 'ab_settings_allow_staff_members_edit_profile' ) ?>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_link_assets_method"><?php _e( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <select class="form-control" name="ab_settings_link_assets_method" id="ab_settings_link_assets_method">
                    <option value="enqueue" <?php selected( get_option( 'ab_settings_link_assets_method' ), 'enqueue' ) ?>>Enqueue</option>
                    <option value="print" <?php selected( get_option( 'ab_settings_link_assets_method' ), 'print' ) ?>>Print</option>
                </select>
            </td>
            <td class="ab-valign-top">
                <?php AB_Utils::popover( __( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton() ?>
            </td>
        </tr>
    </table>
</form>