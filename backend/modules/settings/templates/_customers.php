<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'type', '_customers' ) ) ?>" enctype="multipart/form-data" class="ab-settings-form">
    <table class="form-horizontal">
        <tr>
            <td>
                <label for="ab_settings_create_account"><?php _e( 'Create WordPress user account for customers', 'bookly' ) ?></label>
            </td>
            <td>
                <?php AB_Utils::optionToggle( 'ab_settings_create_account' ) ?>
            </td>
            <td>
                <?php AB_Utils::popover( __( 'If this setting is enabled then Bookly will be creating WordPress user accounts for all new customers. If the user is logged in then the new customer will be associated with the existing user account.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_settings_phone_default_country"><?php _e( 'Phone field default country', 'bookly' ) ?></label>
            </td>
            <td class="ab-valign-top">
                <select class="form-control" name="ab_settings_phone_default_country" id="ab_settings_phone_default_country" data-country="<?php echo get_option( 'ab_settings_phone_default_country' ) ?>">
                    <option value="auto"><?php _e( 'Guess country by user\'s IP address', 'bookly' ) ?></option>
                    <option disabled><?php echo str_repeat( '&#9472;', 30 ) ?></option>
                </select>
            </td>
            <td>
                <?php AB_Utils::popover( __( 'Select default country for the phone field in the \'Details\' step of booking. You can also let Bookly determine the country based on the IP address of the client.', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td>
                <label for="ab_sms_default_country_code"><?php _e( 'Default country code', 'bookly' ) ?></label>
            </td>
            <td>
                <input type="text" name="ab_sms_default_country_code" id="ab_sms_default_country_code" value="<?php echo esc_attr( get_option( 'ab_sms_default_country_code' ) ) ?>" class="form-control" />
            </td>
            <td>
                <?php AB_Utils::popover( __( 'Your clients must have their phone numbers in international format in order to receive text messages. However you can specify a default country code that will be used as a prefix for all phone numbers that do not start with "+" or "00". E.g. if you enter "1" as the default country code and a client enters their phone as "(600) 555-2222" the resulting phone number to send the SMS to will be "+1600555222".', 'bookly' ) ) ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?php AB_Utils::submitButton() ?>
                <?php AB_Utils::resetButton( 'ab-payments-reset' ) ?>
            </td>
        </tr>
    </table>
</form>