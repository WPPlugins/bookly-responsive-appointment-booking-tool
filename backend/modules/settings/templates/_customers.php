<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'customers' ) ) ?>">
    <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cst_create_account', __( 'Create WordPress user account for customers', 'bookly' ), __( 'If this setting is enabled then Bookly will be creating WordPress user accounts for all new customers. If the user is logged in then the new customer will be associated with the existing user account.', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cst_new_account_role', __( 'New user account role', 'bookly' ), __( 'Select what role will be assigned to newly created WordPress user accounts for customers.', 'bookly' ),
            $values['bookly_cst_new_account_role'] );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cst_phone_default_country', __( 'Phone field default country', 'bookly' ), __( 'Select default country for the phone field in the \'Details\' step of booking. You can also let Bookly determine the country based on the IP address of the client.', 'bookly' ),
            array( array( 'disabled', __( 'Disabled', 'bookly' ) ), array( 'auto', __( 'Guess country by user\'s IP address', 'bookly' ) ) ) );
        \BooklyLite\Lib\Utils\Common::optionText( 'bookly_cst_default_country_code', __( 'Default country code', 'bookly' ), __( 'Your clients must have their phone numbers in international format in order to receive text messages. However you can specify a default country code that will be used as a prefix for all phone numbers that do not start with "+" or "00". E.g. if you enter "1" as the default country code and a client enters their phone as "(600) 555-2222" the resulting phone number to send the SMS to will be "+1600555222".', 'bookly' ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cst_cancel_action', __( 'Cancel appointment action', 'bookly' ), __( 'Select what happens when customer clicks cancel appointment link. With "Delete" the appointment will be deleted from the calendar. With "Cancel" only appointment status will be changed to "Cancelled".', 'bookly' ),
            array( array( 'delete', __( 'Delete', 'bookly' ) ), array( 'cancel', __( 'Cancel', 'bookly' ) ) ) );
        \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_cst_combined_notifications', __( 'Combined notifications', 'bookly' ), __( 'If combined notifications are enabled then your clients will receive single notification for entire booking instead of separate notification per each booked appointment (e.g. when cart is enabled). You will need to edit corresponding templates in Email and SMS Notifications.', 'bookly' ) )
    ?>
    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton( 'bookly-customer-reset' ) ?>
    </div>
</form>