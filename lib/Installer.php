<?php
namespace BooklyLite\Lib;

/**
 * Class Installer
 * @package Bookly
 */
class Installer extends Base\Installer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Load l10n for fixtures creating.
        load_plugin_textdomain( 'bookly', false, Plugin::getSlug() . '/languages' );

        /*
         * Notifications email & sms.
         */
        $this->notifications = array(
            array(
                'gateway' => 'email',
                'type'    => 'client_pending_appointment',
                'subject' => __( 'Your appointment information', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked {service_name}.\n\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_pending_appointment_cart',
                'subject' => __( 'Your appointment information', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_pending_appointment',
                'subject' => __( 'New booking information', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nYou have new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_approved_appointment',
                'subject' => __( 'Your appointment information', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked {service_name}.\n\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_approved_appointment_cart',
                'subject' => __( 'Your appointment information', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_approved_appointment',
                'subject' => __( 'New booking information', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nYou have new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_cancelled_appointment',
                'subject' => __( 'Booking cancellation', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_cancelled_appointment',
                'subject' => __( 'Booking cancellation', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nThe following booking has been cancelled.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_new_wp_user',
                'subject' => __( 'New customer', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_reminder',
                'subject' => __( 'Your appointment at {company_name}', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} tomorrow on {appointment_time}. We are waiting you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_follow_up',
                'subject' => __( 'Your visit to {company_name}', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\n\nThank you and we look forward to seeing you again soon.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_agenda',
                'subject' => __( 'Your agenda for {tomorrow_date}', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nYour agenda for tomorrow is:\n\n{next_day_agenda}", 'bookly' ) ),
                'active'  => 0,
            ),

            array(
                'gateway' => 'sms',
                'type'    => 'client_pending_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_pending_appointment_cart',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_pending_appointment',
                'subject' => '',
                'message' => __( "Hello.\nYou have new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_approved_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_approved_appointment_cart',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_approved_appointment',
                'subject' => '',
                'message' => __( "Hello.\nYou have new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_cancelled_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_cancelled_appointment',
                'subject' => '',
                'message' => __( "Hello.\nThe following booking has been cancelled.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_new_wp_user',
                'subject' => '',
                'message' => __( "Hello.\nAn account was created for you at {site_address}\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_reminder',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} tomorrow on {appointment_time}. We are waiting you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    =>'client_follow_up',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\nThank you and we look forward to seeing you again soon.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_agenda',
                'subject' => '',
                'message' => __( "Hello.\nYour agenda for tomorrow is:\n{next_day_agenda}", 'bookly' ),
                'active'  => 0,
            ),
        );
        /*
         * Options.
         */
        $this->options = array(
            'ab_lite_uninstall_remove_bookly_data'   => '1',
            'ab_data_loaded'                         => '0',
            // DB version.
            'ab_db_version'                          => Plugin::getVersion(),
            // Timestamp when the plugin was installed.
            'ab_installation_time'                   => time(),
            // Settings.
            'ab_settings_company_name'               => '',
            'ab_settings_company_logo_attachment_id' => '',
            'ab_settings_company_address'            => '',
            'ab_settings_company_phone'              => '',
            'ab_settings_company_website'            => '',
            'ab_settings_pay_locally'                => '1',
            'ab_settings_sender_name'                => get_option( 'blogname' ),
            'ab_settings_sender_email'               => get_option( 'admin_email' ),
            'ab_settings_time_slot_length'           => '15',
            'ab_settings_minimum_time_prior_booking' => '0',
            'ab_settings_maximum_available_days_for_booking' => '365',
            'ab_settings_minimum_time_prior_cancel'  => '0',
            'ab_settings_approve_page_url'           => home_url(),
            'ab_settings_cancel_page_url'            => home_url(),
            'ab_settings_cancel_denied_page_url'     => home_url(),
            'ab_settings_use_client_time_zone'       => '0',
            'ab_settings_step_cart_enabled'          => '0',
            'ab_settings_create_account'             => '0',
            'ab_settings_coupons'                    => '0',
            'ab_settings_google_client_id'           => '',
            'ab_settings_google_client_secret'       => '',
            'ab_settings_google_two_way_sync'        => '1',
            'ab_settings_google_limit_events'        => '50',
            'ab_settings_google_event_title'         => '{service_name}',
            'ab_settings_final_step_url'             => '',
            'ab_settings_allow_staff_members_edit_profile' => '1',
            'ab_settings_link_assets_method'         => 'enqueue',
            'ab_settings_phone_default_country'      => 'auto',
            'ab_settings_default_appointment_status' => Entities\CustomerAppointment::STATUS_APPROVED,
            'ab_settings_client_cancel_appointment_action' => 'cancel',
            'ab_settings_cron_reminder'              => array( 'client_follow_up' => 21, 'client_reminder' => 18, 'staff_agenda' => 18 ),
            'ab_settings_cart_notifications_combined' => '0',
            'ab_cart_show_columns'                   => array( 'service' => array( 'show' => 1 ), 'date' => array( 'show' => 1 ), 'time' => array( 'show' => 1 ), 'employee' => array( 'show' => 1 ), 'price' => array( 'show' => 1 ), 'deposit' => array( 'show' => 1 ) ),
            // Business hours.
            'ab_settings_monday_start'               => '08:00',
            'ab_settings_monday_end'                 => '18:00',
            'ab_settings_tuesday_start'              => '08:00',
            'ab_settings_tuesday_end'                => '18:00',
            'ab_settings_wednesday_start'            => '08:00',
            'ab_settings_wednesday_end'              => '18:00',
            'ab_settings_thursday_start'             => '08:00',
            'ab_settings_thursday_end'               => '18:00',
            'ab_settings_friday_start'               => '08:00',
            'ab_settings_friday_end'                 => '18:00',
            'ab_settings_saturday_start'             => '',
            'ab_settings_saturday_end'               => '',
            'ab_settings_sunday_start'               => '',
            'ab_settings_sunday_end'                 => '',
            // Appearance.
            'ab_appearance_text_info_service_step'   => __( 'Please select service: ', 'bookly' ),
            'ab_appearance_text_info_time_step'      => __( "Below you can find a list of available time slots for {service_name} by {staff_name}.\nClick on a time slot to proceed with booking.", 'bookly' ),
            'ab_appearance_text_info_cart_step'      => __( "Below you can find a list of services selected for booking.\nClick BOOK MORE if you want to add more services.", 'bookly' ),
            'ab_appearance_text_info_details_step'   => __( "You selected a booking for {service_name} by {staff_name} at {service_time} on {service_date}. The price for the service is {service_price}.\nPlease provide your details in the form below to proceed with booking.", 'bookly' ),
            'ab_appearance_text_info_details_step_guest' => '',
            'ab_appearance_text_info_payment_step'   => __( 'Please tell us how you would like to pay: ', 'bookly' ),
            'ab_appearance_text_info_complete_step'  => __( 'Thank you! Your booking is complete. An email with details of your booking has been sent to you.', 'bookly' ),
            'ab_appearance_text_info_coupon'         => __( 'The total price for the booking is {total_price}.', 'bookly' ),
            'ab_appearance_color'                    => '#f4662f',  // booking form color
            'ab_appearance_required_employee'        => '0',
            'ab_appearance_text_step_service'        => __( 'Service',  'bookly' ),
            'ab_appearance_text_step_time'           => __( 'Time',     'bookly' ),
            'ab_appearance_text_step_cart'           => __( 'Cart',     'bookly' ),
            'ab_appearance_text_step_details'        => __( 'Details',  'bookly' ),
            'ab_appearance_text_step_payment'        => __( 'Payment',  'bookly' ),
            'ab_appearance_text_step_done'           => __( 'Done',     'bookly' ),
            'ab_appearance_text_button_next'         => __( 'Next',     'bookly' ),
            'ab_appearance_text_button_back'         => __( 'Back',     'bookly' ),
            'ab_appearance_text_button_apply'        => __( 'Apply',    'bookly' ),
            'ab_appearance_text_button_book_more'    => __( 'Book More','bookly' ),
            'ab_appearance_text_label_category'      => __( 'Category', 'bookly' ),
            'ab_appearance_text_label_service'       => __( 'Service',  'bookly' ),
            'ab_appearance_text_label_employee'      => __( 'Employee', 'bookly' ),
            'ab_appearance_text_label_select_date'   => __( 'I\'m available on or after', 'bookly' ),
            'ab_appearance_text_label_start_from'    => __( 'Start from', 'bookly' ),
            'ab_appearance_text_label_finish_by'     => __( 'Finish by',  'bookly' ),
            'ab_appearance_text_label_name'          => __( 'Name',     'bookly' ),
            'ab_appearance_text_label_phone'         => __( 'Phone',    'bookly' ),
            'ab_appearance_text_label_email'         => __( 'Email',    'bookly' ),
            'ab_appearance_text_label_coupon'        => __( 'Coupon',   'bookly' ),
            'ab_appearance_text_label_pay_locally'   => __( 'I will pay locally', 'bookly' ),
            'ab_appearance_text_label_pay_mollie'    => __( 'I will pay now with Mollie', 'bookly' ),
            'ab_appearance_text_label_pay_paypal'    => __( 'I will pay now with PayPal', 'bookly' ),
            'ab_appearance_text_label_pay_ccard'     => __( 'I will pay now with Credit Card', 'bookly' ),
            'ab_appearance_text_label_ccard_number'  => __( 'Credit Card Number', 'bookly' ),
            'ab_appearance_text_label_ccard_expire'  => __( 'Expiration Date', 'bookly' ),
            'ab_appearance_text_label_ccard_code'    => __( 'Card Security Code', 'bookly' ),
            'ab_appearance_text_label_number_of_persons' => __( 'Number of persons', 'bookly' ),
            'ab_appearance_text_option_service'      => __( 'Select service', 'bookly' ),
            'ab_appearance_text_option_category'     => __( 'Select category', 'bookly' ),
            'ab_appearance_text_option_employee'     => __( 'Any', 'bookly' ),
            'ab_appearance_text_required_service'    => __( 'Please select a service',   'bookly' ),
            'ab_appearance_text_required_employee'   => __( 'Please select an employee', 'bookly' ),
            'ab_appearance_text_required_name'       => __( 'Please tell us your name',  'bookly' ),
            'ab_appearance_text_required_phone'      => __( 'Please tell us your phone', 'bookly' ),
            'ab_appearance_text_required_email'      => __( 'Please tell us your email', 'bookly' ),
            // Progress tracker.
            'ab_appearance_show_progress_tracker'    => '1',
            'ab_appearance_staff_name_with_price'    => '1',
            // Time slots setting.
            'ab_appearance_show_blocked_timeslots'   => '0',
            'ab_appearance_show_day_one_column'      => '0',
            'ab_appearance_show_calendar'            => '0',
            // Envato Marketplace Purchase Code.
            Plugin::getPurchaseCode()                => '',
            'ab_currency'                            => 'USD',
            // PayPal.
            'ab_paypal_type'                         => 'disabled',
            'ab_paypal_api_username'                 => '',
            'ab_paypal_api_password'                 => '',
            'ab_paypal_api_signature'                => '',
            'ab_paypal_ec_mode'                      => '',  // '.sandbox' or ''
            'ab_paypal_id'                           => '',
            // Authorize.net
            'ab_authorizenet_type'                   => 'disabled',
            'ab_authorizenet_sandbox'                => '0',
            'ab_authorizenet_api_login_id'           => '',
            'ab_authorizenet_transaction_key'        => '',
            // Stripe.
            'ab_stripe'                              => 'disabled',
            'ab_stripe_publishable_key'              => '',
            'ab_stripe_secret_key'                   => '',
            // 2Checkout.
            'ab_2checkout'                           => 'disabled',
            'ab_2checkout_sandbox'                   => '0',
            'ab_2checkout_api_seller_id'             => '',
            'ab_2checkout_api_secret_word'           => '',
            // PayU Latam.
            'ab_payulatam'                           => 'disabled',
            'ab_payulatam_sandbox'                   => '0',
            'ab_payulatam_api_account_id'            => '',
            'ab_payulatam_api_key'                   => '',
            'ab_payulatam_api_merchant_id'           => '',
            // Payson.
            'ab_payson'                              => 'disabled',
            'ab_payson_sandbox'                      => '0',
            'ab_payson_fees_payer'                   => 'PRIMARYRECEIVER',
            'ab_payson_funding'                      => array( 'CREDITCARD' ),
            'ab_payson_api_agent_id'                 => '',
            'ab_payson_api_key'                      => '',
            'ab_payson_api_receiver_email'           => '',
            // Mollie.
            'ab_mollie'                              => 'disabled',
            'ab_mollie_api_key'                      => '',
            // Custom Fields.
            'ab_custom_fields'                       => '[{"type":"textarea","label":' . json_encode( __( 'Notes', 'bookly' ) ) . ',"required":false,"id":1,"services":[]}]',
            'ab_custom_fields_per_service'           => '0',
            // WooCommerce.
            'ab_woocommerce_enabled'                 => '0',
            'ab_woocommerce_product'                 => '',
            'ab_woocommerce_cart_info_name'          => __( 'Appointment', 'bookly' ),
            'ab_woocommerce_cart_info_value'         => __( 'Date', 'bookly' ) . ": {appointment_date}\n" . __( 'Time', 'bookly' ) . ": {appointment_time}\n" . __( 'Service', 'bookly' ) . ": {service_name}",
            // SMS.
            'ab_sms_token'                           => '',
            'ab_sms_administrator_phone'             => '',
            'ab_sms_default_country_code'            => '',
            'ab_sms_notify_low_balance'              => '1',
            'ab_sms_notify_week_summary'             => '1',
            'ab_sms_notify_week_summary_sent'        => date( 'W' ),
            // Email notification.
            'ab_email_content_type'                  => 'html',
            'ab_email_notification_reply_to_customers' => '1',

        );

        $this->tables = array(
            Entities\Appointment::getTableName(),
            Entities\Category::getTableName(),
            Entities\Coupon::getTableName(),
            Entities\CouponService::getTableName(),
            Entities\Customer::getTableName(),
            Entities\CustomerAppointment::getTableName(),
            Entities\Holiday::getTableName(),
            Entities\Notification::getTableName(),
            Entities\Payment::getTableName(),
            Entities\ScheduleItemBreak::getTableName(),
            Entities\SentNotification::getTableName(),
            Entities\Service::getTableName(),
            Entities\Staff::getTableName(),
            Entities\StaffScheduleItem::getTableName(),
            Entities\StaffService::getTableName(),
        );
    }

    public function install()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        parent::install();

        if( false === Entities\Staff::find( 1 ) ){
            $wpdb->insert( Entities\Staff::getTableName(), array( 'full_name' => 'Employee', 'id' => 1, 'visibility' => 'public' ) );
            Entities\StaffScheduleItem::query( 'ss' )
                ->delete()->where( 'ss.staff_id', 1 )
                ->execute();
            $fields = array(
                'staff_id'   => 1,
                'day_index'  => 1,
                'start_time' => '08:00:00',
                'end_time'   => '18:00:00',
            );
            for ( $i = 1; $i <= 7; $i ++ ) {
                $fields['day_index'] = $i;
                $schedule = new Entities\StaffScheduleItem();
                $schedule->setFields( $fields );
                $schedule->save();
            }
        }
    }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        parent::uninstall();
        $this->_remove_l10n_data();
    }

    /**
     * Load data.
     */
    protected function _load_data()
    {
        parent::_load_data();

        // Insert notifications.
        foreach ( $this->notifications as $data ) {
            $notification = new Entities\Notification( $data );
            $notification->save();
        }

        foreach ( array( 'ab_woocommerce_cart_info_name', 'ab_woocommerce_cart_info_value' ) as $option_name ) {
            do_action( 'wpml_register_single_string', 'bookly', $option_name, get_option( $option_name ) );
        }

        // Register custom fields for translate in WPML
        foreach ( json_decode( $this->options['ab_custom_fields'] ) as $custom_field ) {
            switch ( $custom_field->type ) {
                case 'textarea':
                case 'text-field':
                case 'captcha':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label );
                    break;
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label );
                    foreach ( $custom_field->items as $label ) {
                        do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ) . '=' . sanitize_title( $label ), $label );
                    }
                    break;
            }
        }
    }

    /**
     * Remove l10n data
     */
    protected function _remove_l10n_data()
    {
        global $wpdb;
        $wpml_strings_table = $wpdb->prefix . 'icl_strings';
        $result = $wpdb->query( "SELECT table_name FROM information_schema.tables WHERE table_name = '$wpml_strings_table' AND TABLE_SCHEMA=SCHEMA()" );
        if ( $result == 1 ) {
            @$wpdb->query( "DELETE FROM {$wpdb->prefix}icl_string_translations WHERE string_id IN (SELECT id FROM $wpml_strings_table WHERE context='bookly')" );
            @$wpdb->query( "DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id IN (SELECT id FROM $wpml_strings_table WHERE context='bookly')" );
            @$wpdb->query( "DELETE FROM {$wpml_strings_table} WHERE context='bookly'" );
        }
    }

    /**
     * Create tables in database.
     */
    protected function _create_tables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Staff::getTableName() . '` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wp_user_id`         BIGINT(20) UNSIGNED,
                `attachment_id`      INT UNSIGNED DEFAULT NULL,
                `full_name`          VARCHAR(255),
                `email`              VARCHAR(255),
                `phone`              VARCHAR(255),
                `info`               TEXT,
                `google_data`        VARCHAR(255),
                `google_calendar_id` VARCHAR(255),
                `position`           INT NOT NULL DEFAULT 9999,
                `visibility`         ENUM("public","private") NOT NULL DEFAULT "public"
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Category::getTableName() . '` (
                `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`     VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 9999
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Service::getTableName() . '` (
                `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `title`         VARCHAR(255) DEFAULT "",
                `type`          ENUM("simple","compound") NOT NULL DEFAULT "simple",
                `duration`      INT NOT NULL DEFAULT 900,
                `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `color`         VARCHAR(255) NOT NULL DEFAULT "#FFFFFF",
                `category_id`   INT UNSIGNED,
                `capacity`      INT NOT NULL DEFAULT 1,
                `position`      INT NOT NULL DEFAULT 9999,
                `padding_left`  INT NOT NULL DEFAULT 0,
                `padding_right` INT NOT NULL DEFAULT 0,
                `info`          TEXT,
                `sub_services`  TEXT NOT NULL,
                `visibility`    ENUM("public","private") NOT NULL DEFAULT "public",
                CONSTRAINT
                    FOREIGN KEY (category_id)
                    REFERENCES ' . Entities\Category::getTableName() . '(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffService::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `service_id` INT UNSIGNED NOT NULL,
                `price`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `deposit`    VARCHAR(100) NOT NULL DEFAULT "100%",
                `capacity`   INT NOT NULL DEFAULT 1,
                UNIQUE KEY unique_ids_idx (staff_id, service_id),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffScheduleItem::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `day_index`  INT UNSIGNED NOT NULL,
                `start_time` TIME,
                `end_time`   TIME,
                UNIQUE KEY unique_ids_idx (staff_id, day_index),
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\ScheduleItemBreak::getTableName() . '` (
                `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_schedule_item_id` INT UNSIGNED NOT NULL,
                `start_time`             TIME,
                `end_time`               TIME,
                CONSTRAINT
                    FOREIGN KEY (staff_schedule_item_id)
                    REFERENCES ' . Entities\StaffScheduleItem::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Notification::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `gateway`     ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`        VARCHAR(255) NOT NULL DEFAULT "",
                `active`      TINYINT(1) NOT NULL DEFAULT 0,
                `copy`        TINYINT(1) NOT NULL DEFAULT 0,
                `subject`     VARCHAR(255) NOT NULL DEFAULT "",
                `message`     TEXT
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Customer::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wp_user_id` BIGINT(20) UNSIGNED,
                `name`       VARCHAR(255) NOT NULL DEFAULT "",
                `phone`      VARCHAR(255) NOT NULL DEFAULT "",
                `email`      VARCHAR(255) NOT NULL DEFAULT "",
                `notes`      TEXT NOT NULL DEFAULT ""
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Appointment::getTableName() . '` (
                `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`        INT UNSIGNED NOT NULL,
                `service_id`      INT UNSIGNED,
                `start_date`      DATETIME NOT NULL,
                `end_date`        DATETIME NOT NULL,
                `google_event_id` VARCHAR(255) DEFAULT NULL,
                `extras_duration` INT NOT NULL DEFAULT 0,
                `internal_note`   TEXT,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Holiday::getTableName() . '` (
                  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `staff_id`     INT UNSIGNED NULL DEFAULT NULL,
                  `parent_id`    INT UNSIGNED NULL DEFAULT NULL,
                  `date`         DATE NOT NULL,
                  `repeat_event` TINYINT(1) NOT NULL DEFAULT 0,
                  CONSTRAINT
                      FOREIGN KEY (staff_id)
                      REFERENCES ' . Entities\Staff::getTableName() . '(id)
                      ON DELETE CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Payment::getTableName() . '` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `created`           DATETIME NOT NULL,
                `type`              ENUM("local","coupon","paypal","authorizeNet","stripe","2checkout","payulatam","payson","mollie") NOT NULL DEFAULT "local",
                `token`             VARCHAR(255) NOT NULL,
                `transaction_id`    VARCHAR(255) NOT NULL,
                `total`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `status`            ENUM("pending","completed") NOT NULL DEFAULT "completed",
                `details`           TEXT
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CustomerAppointment::getTableName() . '` (
                `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id`         INT UNSIGNED NOT NULL,
                `appointment_id`      INT UNSIGNED NOT NULL,
                `location_id`         INT UNSIGNED NULL DEFAULT NULL,
                `payment_id`          INT UNSIGNED DEFAULT NULL,
                `number_of_persons`   INT UNSIGNED NOT NULL DEFAULT 1,
                `extras`              TEXT,
                `custom_fields`       TEXT,
                `status`              ENUM("pending","approved","cancelled") NOT NULL DEFAULT "approved",
                `token`               VARCHAR(255),
                `time_zone_offset`    INT,
                `locale`              VARCHAR(8) NULL,
                `compound_service_id` INT UNSIGNED DEFAULT NULL,
                `compound_token`      VARCHAR(255) DEFAULT NULL,
                CONSTRAINT
                    FOREIGN KEY (customer_id)
                    REFERENCES  ' . Entities\Customer::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (appointment_id)
                    REFERENCES  ' . Entities\Appointment::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT 
                    FOREIGN KEY (payment_id)
                    REFERENCES ' . Entities\Payment::getTableName() . '(id)
                    ON DELETE   SET NULL
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Coupon::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `code`        VARCHAR(255) NOT NULL DEFAULT "",
                `discount`    DECIMAL(3,0) NOT NULL DEFAULT 0,
                `deduction`   DECIMAL(10,2) NOT NULL DEFAULT 0,
                `usage_limit` INT UNSIGNED NOT NULL DEFAULT 1,
                `used`        INT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CouponService::getTableName() . '` (        
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `coupon_id`   INT UNSIGNED NOT NULL,
                `service_id`  INT UNSIGNED NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (coupon_id)
                    REFERENCES  ' . Entities\Coupon::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES  ' . Entities\Service::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\SentNotification::getTableName() . '` (
                `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_appointment_id` INT UNSIGNED,
                `staff_id`                INT UNSIGNED,
                `gateway`                 ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`                    VARCHAR(60) NOT NULL,
                `created`                 DATETIME NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (customer_appointment_id)
                    REFERENCES  ' . Entities\CustomerAppointment::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES  ' . Entities\Staff::getTableName() . '(id)
                    ON DELETE   CASCADE
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );
    }

}