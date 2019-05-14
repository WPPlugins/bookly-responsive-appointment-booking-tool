<?php
namespace Bookly\Lib;

use Bookly\Lib\Entities\Notification;
use Bookly\Lib\DataHolders\Notification\Settings;

/**
 * Class Installer
 * @package Bookly
 */
class Installer extends Base\Installer
{
    /** @var array */
    protected $notifications = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // l10n.
        load_plugin_textdomain( 'bookly', false, Plugin::getSlug() . '/languages' );

        // Notifications email & sms.
        $default_settings = Settings::getDefault();
        $settings = $default_settings;
        $settings['status'] = 'approved';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_NEW_BOOKING,
            'name'        => __( 'Notification to customer about approved appointment', 'bookly' ),
            'subject'     => __( 'Your appointment information', 'bookly' ),
            'message'     => wpautop( __( "Dear {client_name}.\n\nThis is a confirmation that you have booked {service_name}.\n\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'approved';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_NEW_BOOKING,
            'name'        => __( 'Notification to staff member about approved appointment', 'bookly' ),
            'subject'     => __( 'New booking information', 'bookly' ),
            'message'     => wpautop( __( "Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'cancelled';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to customer about cancelled appointment', 'bookly' ),
            'subject'     => __( 'Booking cancellation', 'bookly' ),
            'message'     => wpautop( __( "Dear {client_name}.\n\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'cancelled';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to staff member about cancelled appointment', 'bookly' ),
            'subject'     => __( 'Booking cancellation', 'bookly' ),
            'message'     => wpautop( __( "Hello.\n\nThe following booking has been cancelled.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'rejected';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to customer about rejected appointment', 'bookly' ),
            'subject'     => __( 'Booking rejection', 'bookly' ),
            'message'     => wpautop( __( "Dear {client_name}.\n\nYour booking of {service_name} on {appointment_date} at {appointment_time} has been rejected.\n\nReason: {cancellation_reason}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'rejected';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to staff member about rejected appointment', 'bookly' ),
            'subject'     => __( 'Booking rejection', 'bookly' ),
            'message'     => wpautop( __( "Hello.\n\nThe following booking has been rejected.\n\nReason: {cancellation_reason}\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'approved';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_NEW_BOOKING,
            'name'        => __( 'Notification to customer about approved appointment', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nThis is a confirmation that you have booked {service_name}.\nWe are waiting you at {company_address} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'approved';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_NEW_BOOKING,
            'name'        => __( 'Notification to staff member about approved appointment', 'bookly' ),
            'message'     => __( "Hello.\nYou have a new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'cancelled';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to customer about cancelled appointment', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'cancelled';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to staff member about cancelled appointment', 'bookly' ),
            'message'     => __( "Hello.\nThe following booking has been cancelled.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'rejected';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to customer about rejected appointment', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nYour booking of {service_name} on {appointment_date} at {appointment_time} has been rejected.\nReason: {cancellation_reason}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['status'] = 'rejected';
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED,
            'name'        => __( 'Notification to staff member about rejected appointment', 'bookly' ),
            'message'     => __( "Hello.\nThe following booking has been rejected.\nReason: {cancellation_reason}\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['option'] = 2;
        $settings['offset_hours'] = 1;
        $settings['perform'] = 'before';
        $settings['at_hour'] = 18;
        $settings['offset_bidirectional_hours'] = - 24;
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_APPOINTMENT_REMINDER,
            'name'        => __( 'Evening reminder to customer about next day appointment (requires cron setup)', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_time}. We are waiting for you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['option'] = 2;
        $settings['at_hour'] = 21;
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_APPOINTMENT_REMINDER,
            'name'        => __( 'Follow-up message in the same day after appointment (requires cron setup)', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\nThank you and we look forward to seeing you again soon.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings'    => $settings,
        );
        $settings = $default_settings;
        $settings['at_hour'] = 18;
        $settings['offset_bidirectional_hours'] = - 24;
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => Notification::TYPE_STAFF_DAY_AGENDA,
            'name'        => __( 'Evening notification with the next day agenda to staff member (requires cron setup)', 'bookly' ),
            'message'     => __( "Hello.\nYour agenda for tomorrow is:\n{next_day_agenda}", 'bookly' ),
            'to_staff'    => 1,
            'settings'    => $settings,
        );

        /*
         * Options.
         */
        $this->options = array(
            // Appearance.
            'bookly_admin_preferred_language'            => '',
            'bookly_app_color'                           => '#f4662f',
            'bookly_app_custom_styles'                   => '',
            'bookly_app_required_employee'               => '0',
            'bookly_app_service_name_with_duration'      => '0',
            'bookly_app_show_blocked_timeslots'          => '0',
            'bookly_app_show_calendar'                   => '0',
            'bookly_app_show_day_one_column'             => '0',
            'bookly_app_show_login_button'               => '0',
            'bookly_app_show_notes'                      => '1',
            'bookly_app_show_progress_tracker'           => '1',
            'bookly_app_align_buttons_left'              => '0',
            'bookly_app_staff_name_with_price'           => '1',
            'bookly_app_show_email_confirm'              => '0',
            'bookly_l10n_button_apply'                   => __( 'Apply', 'bookly' ),
            'bookly_l10n_button_back'                    => __( 'Back', 'bookly' ),
            'bookly_l10n_info_complete_step'             => __( 'Thank you! Your booking is complete. An email with details of your booking has been sent to you.', 'bookly' ),
            'bookly_l10n_info_complete_step_limit_error' => __( 'You are trying to use the service too often. Please contact us to make a booking.', 'bookly' ),
            'bookly_l10n_info_complete_step_processing'  => __( 'Your payment has been accepted for processing.', 'bookly' ),
            'bookly_l10n_info_details_step'              => __( "You selected a booking for {service_name} by {staff_name} at {service_time} on {service_date}. The price for the service is {service_price}.\nPlease provide your details in the form below to proceed with booking.", 'bookly' ),
            'bookly_l10n_info_details_step_guest'        => '',
            'bookly_l10n_info_payment_step_single_app'   => __( 'Please tell us how you would like to pay: ', 'bookly' ),
            'bookly_l10n_info_payment_step_several_apps' => __( 'Please tell us how you would like to pay: ', 'bookly' ),
            'bookly_l10n_info_service_step'              => __( 'Please select service: ', 'bookly' ),
            'bookly_l10n_info_time_step'                 => __( "Below you can find a list of available time slots for {service_name} by {staff_name}.\nClick on a time slot to proceed with booking.", 'bookly' ),
            'bookly_l10n_label_category'                 => __( 'Category', 'bookly' ),
            'bookly_l10n_label_ccard_code'               => __( 'Card Security Code', 'bookly' ),
            'bookly_l10n_label_ccard_expire'             => __( 'Expiration Date', 'bookly' ),
            'bookly_l10n_label_ccard_number'             => __( 'Credit Card Number', 'bookly' ),
            'bookly_l10n_label_email'                    => __( 'Email', 'bookly' ),
            'bookly_l10n_label_email_confirm'            => __( 'Confirm email', 'bookly' ),
            'bookly_l10n_label_employee'                 => __( 'Employee', 'bookly' ),
            'bookly_l10n_label_finish_by'                => __( 'Finish by', 'bookly' ),
            'bookly_l10n_label_name'                     => __( 'Name', 'bookly' ),
            'bookly_l10n_label_first_name'               => __( 'First name', 'bookly' ),
            'bookly_l10n_label_last_name'                => __( 'Last name', 'bookly' ),
            'bookly_l10n_label_notes'                    => __( 'Notes', 'bookly' ),
            'bookly_l10n_label_pay_ccard'                => __( 'I will pay now with Credit Card', 'bookly' ),
            'bookly_l10n_label_pay_locally'              => __( 'I will pay locally', 'bookly' ),
            'bookly_l10n_label_pay_mollie'               => __( 'I will pay now with Mollie', 'bookly' ),
            'bookly_l10n_label_phone'                    => __( 'Phone', 'bookly' ),
            'bookly_l10n_label_select_date'              => __( 'I\'m available on or after', 'bookly' ),
            'bookly_l10n_label_service'                  => __( 'Service', 'bookly' ),
            'bookly_l10n_label_start_from'               => __( 'Start from', 'bookly' ),
            'bookly_l10n_option_category'                => __( 'Select category', 'bookly' ),
            'bookly_l10n_option_employee'                => __( 'Any', 'bookly' ),
            'bookly_l10n_option_service'                 => __( 'Select service', 'bookly' ),
            'bookly_l10n_option_day'                     => __( 'Select day', 'bookly' ),
            'bookly_l10n_option_month'                   => __( 'Select month', 'bookly' ),
            'bookly_l10n_option_year'                    => __( 'Select year', 'bookly' ),
            'bookly_l10n_required_email'                 => __( 'Please tell us your email', 'bookly' ),
            'bookly_l10n_email_confirm_not_match'        => __( 'Email confirmation doesn\'t match', 'bookly' ),
            'bookly_l10n_required_employee'              => __( 'Please select an employee', 'bookly' ),
            'bookly_l10n_required_name'                  => __( 'Please tell us your name', 'bookly' ),
            'bookly_l10n_required_first_name'            => __( 'Please tell us your first name', 'bookly' ),
            'bookly_l10n_required_last_name'             => __( 'Please tell us your last name', 'bookly' ),
            'bookly_l10n_required_phone'                 => __( 'Please tell us your phone', 'bookly' ),
            'bookly_l10n_required_service'               => __( 'Please select a service', 'bookly' ),
            'bookly_l10n_step_service'                   => __( 'Service', 'bookly' ),
            'bookly_l10n_step_time'                      => __( 'Time', 'bookly' ),
            'bookly_l10n_step_time_slot_not_available'   => __( 'The selected time is not available anymore. Please, choose another time slot.', 'bookly' ),
            'bookly_l10n_step_details'                   => __( 'Details', 'bookly' ),
            'bookly_l10n_step_details_button_login'      => __( 'Login', 'bookly' ),
            'bookly_l10n_step_payment'                   => __( 'Payment', 'bookly' ),
            'bookly_l10n_step_done'                      => __( 'Done', 'bookly' ),
            // Button Next.
            'bookly_l10n_step_service_button_next'       => __( 'Next', 'bookly' ),
            'bookly_l10n_step_service_mobile_button_next' => __( 'Next', 'bookly' ),
            'bookly_l10n_step_details_button_next'       => __( 'Next', 'bookly' ),
            'bookly_l10n_step_payment_button_next'       => __( 'Next', 'bookly' ),
            // Calendar.
            'bookly_cal_one_participant'                 => '{service_name}' . "\n" . '{client_name}' . "\n" . '{client_phone}' . "\n" . '{client_email}' . "\n" . '{total_price} {payment_type} {payment_status}' . "\n" . __( 'Status', 'bookly' ) . ': {status}' . "\n" . __( 'Signed up', 'bookly' ) . ': {signed_up}' . "\n" . __( 'Capacity',  'bookly' ) . ': {service_capacity}',
            'bookly_cal_many_participants'               => '{service_name}' . "\n" . __( 'Signed up', 'bookly' ) . ': {signed_up}' . "\n" . __( 'Capacity',  'bookly' ) . ': {service_capacity}',
            // Company.
            'bookly_co_logo_attachment_id'               => '',
            'bookly_co_name'                             => '',
            'bookly_co_address'                          => '',
            'bookly_co_phone'                            => '',
            'bookly_co_website'                          => '',
            // Customers.
            'bookly_cst_allow_duplicates'                => '0',
            'bookly_cst_cancel_action'                   => 'cancel',
            'bookly_cst_create_account'                  => '0',
            'bookly_cst_default_country_code'            => '',
            'bookly_cst_first_last_name'                 => '0',
            'bookly_cst_phone_default_country'           => 'auto',
            'bookly_cst_remember_in_cookie'              => '0',
            'bookly_cst_required_address'                => '0',
            'bookly_cst_required_birthday'               => '0',
            'bookly_cst_required_details'                => array( 'phone', 'email' ),
            'bookly_cst_show_update_details_dialog'      => '1',
            // Email notifications.
            'bookly_email_sender'                        => get_option( 'admin_email' ),
            'bookly_email_sender_name'                   => get_option( 'blogname' ),
            'bookly_email_send_as'                       => 'html',
            'bookly_email_reply_to_customers'            => '1',
            // General.
            'bookly_gen_time_slot_length'                => '15',
            'bookly_gen_service_duration_as_slot_length' => '0',
            'bookly_gen_default_appointment_status'      => Entities\CustomerAppointment::STATUS_APPROVED,
            'bookly_gen_min_time_prior_booking'          => '0',
            'bookly_gen_min_time_prior_cancel'           => '0',
            'bookly_gen_max_days_for_booking'            => '365',
            'bookly_gen_use_client_time_zone'            => '0',
            'bookly_gen_allow_staff_edit_profile'        => '1',
            'bookly_gen_link_assets_method'              => 'enqueue',
            'bookly_gen_collect_stats'                   => '0',
            // URL.
            'bookly_url_approve_page_url'                => home_url(),
            'bookly_url_approve_denied_page_url'         => home_url(),
            'bookly_url_cancel_page_url'                 => home_url(),
            'bookly_url_cancel_denied_page_url'          => home_url(),
            'bookly_url_reject_page_url'                 => home_url(),
            'bookly_url_reject_denied_page_url'          => home_url(),
            // SMS.
            'bookly_sms_token'                           => '',
            'bookly_sms_administrator_phone'             => '',
            'bookly_sms_notify_low_balance'              => '1',
            'bookly_sms_notify_weekly_summary'           => '1',
            'bookly_sms_notify_weekly_summary_sent'      => date( 'W' ),
            'bookly_sms_undelivered_count'               => '0',
            // Business hours.
            'bookly_bh_monday_start'                     => '08:00',
            'bookly_bh_monday_end'                       => '18:00',
            'bookly_bh_tuesday_start'                    => '08:00',
            'bookly_bh_tuesday_end'                      => '18:00',
            'bookly_bh_wednesday_start'                  => '08:00',
            'bookly_bh_wednesday_end'                    => '18:00',
            'bookly_bh_thursday_end'                     => '18:00',
            'bookly_bh_thursday_start'                   => '08:00',
            'bookly_bh_friday_start'                     => '08:00',
            'bookly_bh_friday_end'                       => '18:00',
            'bookly_bh_saturday_start'                   => '',
            'bookly_bh_saturday_end'                     => '',
            'bookly_bh_sunday_start'                     => '',
            'bookly_bh_sunday_end'                       => '',
            // Payments.
            'bookly_pmt_currency'                        => 'USD',
            'bookly_pmt_price_format'                    => '{symbol}{sign}{price|2}',
            'bookly_pmt_order'                           => '',
            // Pay locally.
            'bookly_pmt_local'                           => '1',
            // Notifications.
            'bookly_ntf_processing_interval'             => '2', // hours
        );
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        /** @var \wpdb */
        global $wpdb;

        $this->removeData();
        $this->dropTables();
        $this->_removeL10nData();

        // Remove user meta.
        $meta_names = array(
            'bookly_appointment_form_send_notifications',
            'bookly_contact_us_btn_clicked',
            'bookly_delete_customers_options',
            'bookly_dismiss_appearance_notice',
            'bookly_dismiss_collect_stats_notice',
            'bookly_dismiss_contact_us_notice',
            'bookly_dismiss_demo_site_description',
            'bookly_dismiss_feedback_notice',
            'bookly_dismiss_nps_notice',
            'bookly_dismiss_subscribe_notice',
            'bookly_dismiss_feature_requests_description',
            'bookly_filter_appointments_list',
            'bookly_filter_services_categories',
            'bookly_filter_staff_categories',
            'bookly_show_collecting_stats_notice',
        );
        $wpdb->query( $wpdb->prepare( sprintf( 'DELETE FROM `' . $wpdb->usermeta . '` WHERE meta_key IN (%s)',
            implode( ', ', array_fill( 0, count( $meta_names ), '%s' ) ) ), $meta_names ) );

        wp_clear_scheduled_hook( 'bookly_daily_routine' );
        wp_clear_scheduled_hook( 'bookly_hourly_routine' );
    }

    /**
     * @inheritdoc
     */
    public function createTables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Staff::getTableName() . '` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `category_id`        INT UNSIGNED DEFAULT NULL,
                `wp_user_id`         BIGINT(20) UNSIGNED DEFAULT NULL,
                `attachment_id`      INT UNSIGNED DEFAULT NULL,
                `full_name`          VARCHAR(255) DEFAULT NULL,
                `email`              VARCHAR(255) DEFAULT NULL,
                `phone`              VARCHAR(255) DEFAULT NULL,
                `info`               TEXT DEFAULT NULL,
                `working_time_limit` INT UNSIGNED DEFAULT NULL,
                `visibility`         ENUM("public","private","archive") NOT NULL DEFAULT "public",
                `position`           INT NOT NULL DEFAULT 9999,
                `google_data`        TEXT DEFAULT NULL,
                `outlook_data`       TEXT DEFAULT NULL
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
                `id`                           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `category_id`                  INT UNSIGNED DEFAULT NULL,
                `type`                         ENUM("simple","collaborative","compound","package") NOT NULL DEFAULT "simple",
                `title`                        VARCHAR(255) DEFAULT "",
                `duration`                     INT NOT NULL DEFAULT 900,
                `slot_length`                  VARCHAR(255) NOT NULL DEFAULT "default",
                `price`                        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `color`                        VARCHAR(255) NOT NULL DEFAULT "#FFFFFF",
                `deposit`                      VARCHAR(100) NOT NULL DEFAULT "100%",
                `capacity_min`                 INT NOT NULL DEFAULT 1,
                `capacity_max`                 INT NOT NULL DEFAULT 1,
                `one_booking_per_slot`         TINYINT(1) NOT NULL DEFAULT 0,
                `padding_left`                 INT NOT NULL DEFAULT 0,
                `padding_right`                INT NOT NULL DEFAULT 0,
                `info`                         TEXT DEFAULT NULL,
                `start_time_info`              VARCHAR(255) DEFAULT "",
                `end_time_info`                VARCHAR(255) DEFAULT "",
                `units_min`                    INT UNSIGNED NOT NULL DEFAULT 1,
                `units_max`                    INT UNSIGNED NOT NULL DEFAULT 1,
                `package_life_time`            INT DEFAULT NULL,
                `package_size`                 INT DEFAULT NULL,
                `package_unassigned`           TINYINT(1) NOT NULL DEFAULT 0,
                `appointments_limit`           INT DEFAULT NULL,
                `limit_period`                 ENUM("off","day","week","month","year","upcoming","calendar_day","calendar_week","calendar_month","calendar_year") NOT NULL DEFAULT "off",
                `staff_preference`             ENUM("order","least_occupied","most_occupied","least_occupied_for_period","most_occupied_for_period","least_expensive","most_expensive") NOT NULL DEFAULT "most_expensive",
                `staff_preference_settings`    TEXT DEFAULT NULL,
                `recurrence_enabled`           TINYINT(1) NOT NULL DEFAULT 1,
                `recurrence_frequencies`       SET("daily","weekly","biweekly","monthly") NOT NULL DEFAULT "daily,weekly,biweekly,monthly",
                `time_requirements`            ENUM("required","optional","off") NOT NULL DEFAULT "required",
                `collaborative_equal_duration` TINYINT(1) NOT NULL DEFAULT 0,
                `visibility`                   ENUM("public","private","group") NOT NULL DEFAULT "public",
                `position`                     INT NOT NULL DEFAULT 9999,
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\SubService::getTableName() . '` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `type`              ENUM("service","spare_time") NOT NULL DEFAULT "service",
                `service_id`        INT UNSIGNED NOT NULL,
                `sub_service_id`    INT UNSIGNED DEFAULT NULL,
                `duration`          INT DEFAULT NULL,
                `position`          INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (sub_service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffScheduleItem::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`    INT UNSIGNED NOT NULL,
                `location_id` INT UNSIGNED DEFAULT NULL,
                `day_index`   INT UNSIGNED NOT NULL,
                `start_time`  TIME DEFAULT NULL,
                `end_time`    TIME DEFAULT NULL,
                UNIQUE KEY unique_ids_idx (staff_id, day_index, location_id),
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffService::getTableName() . '` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`     INT UNSIGNED NOT NULL,
                `service_id`   INT UNSIGNED NOT NULL,
                `location_id`  INT UNSIGNED DEFAULT NULL,
                `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `deposit`      VARCHAR(100) NOT NULL DEFAULT "100%",
                `capacity_min` INT NOT NULL DEFAULT 1,
                `capacity_max` INT NOT NULL DEFAULT 1,
                UNIQUE KEY unique_ids_idx (staff_id, service_id, location_id),
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\ScheduleItemBreak::getTableName() . '` (
                `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_schedule_item_id` INT UNSIGNED NOT NULL,
                `start_time`             TIME DEFAULT NULL,
                `end_time`               TIME DEFAULT NULL,
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
                `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `gateway`        ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`           VARCHAR(255) NOT NULL DEFAULT "",
                `active`         TINYINT(1) NOT NULL DEFAULT 0,
                `name`           VARCHAR(255) NOT NULL DEFAULT "",
                `subject`        VARCHAR(255) NOT NULL DEFAULT "",
                `message`        TEXT DEFAULT NULL,
                `to_staff`       TINYINT(1) NOT NULL DEFAULT 0,
                `to_customer`    TINYINT(1) NOT NULL DEFAULT 0,
                `to_admin`       TINYINT(1) NOT NULL DEFAULT 0,
                `attach_ics`     TINYINT(1) NOT NULL DEFAULT 0,
                `attach_invoice` TINYINT(1) NOT NULL DEFAULT 0,
                `settings`       TEXT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Customer::getTableName() . '` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wp_user_id`         BIGINT(20) UNSIGNED DEFAULT NULL,
                `facebook_id`        BIGINT(20) UNSIGNED DEFAULT NULL,
                `group_id`           INT UNSIGNED DEFAULT NULL,
                `full_name`          VARCHAR(255) NOT NULL DEFAULT "",
                `first_name`         VARCHAR(255) NOT NULL DEFAULT "",
                `last_name`          VARCHAR(255) NOT NULL DEFAULT "",
                `phone`              VARCHAR(255) NOT NULL DEFAULT "",
                `email`              VARCHAR(255) NOT NULL DEFAULT "",
                `birthday`           DATE DEFAULT NULL,
                `country`            VARCHAR(255) DEFAULT NULL,
                `state`              VARCHAR(255) DEFAULT NULL, 
                `postcode`           VARCHAR(255) DEFAULT NULL,  
                `city`               VARCHAR(255) DEFAULT NULL,  
                `street`             VARCHAR(255) DEFAULT NULL,
                `street_number`      VARCHAR(255) DEFAULT NULL,
                `additional_address` VARCHAR(255) DEFAULT NULL,    
                `notes`              TEXT NOT NULL,
                `info_fields`        TEXT DEFAULT NULL,
                `created`            DATETIME NOT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Series::getTableName() . '` (
                `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `repeat` VARCHAR(255) DEFAULT NULL,
                `token`  VARCHAR(255) NOT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Appointment::getTableName() . '` (
                `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `location_id`              INT UNSIGNED DEFAULT NULL,
                `staff_id`                 INT UNSIGNED NOT NULL,
                `staff_any`                TINYINT(1) NOT NULL DEFAULT 0,
                `service_id`               INT UNSIGNED DEFAULT NULL,
                `custom_service_name`      VARCHAR(255) DEFAULT NULL,
                `custom_service_price`     DECIMAL(10,2) DEFAULT NULL,
                `start_date`               DATETIME DEFAULT NULL,
                `end_date`                 DATETIME DEFAULT NULL,
                `extras_duration`          INT NOT NULL DEFAULT 0,
                `internal_note`            TEXT DEFAULT NULL,
                `google_event_id`          VARCHAR(255) DEFAULT NULL,
                `google_event_etag`        VARCHAR(255) DEFAULT NULL,
                `outlook_event_id`         VARCHAR(255) DEFAULT NULL,
                `outlook_event_change_key` VARCHAR(255) DEFAULT NULL,
                `outlook_event_series_id`  VARCHAR(255) DEFAULT NULL,
                `created_from`             ENUM("bookly","google","outlook") NOT NULL DEFAULT "bookly",
                `created`                  DATETIME NOT NULL,
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
                `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `coupon_id` INT UNSIGNED DEFAULT NULL,
                `type`      ENUM("local","coupon","paypal","authorize_net","stripe","2checkout","payu_biz","payu_latam","payson","mollie","woocommerce") NOT NULL DEFAULT "local",
                `total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `tax`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid_type` ENUM("in_full","deposit") NOT NULL DEFAULT "in_full",
                `gateway_price_correction` DECIMAL(10,2) NULL DEFAULT 0.00,
                `status`    ENUM("pending","completed","rejected") NOT NULL DEFAULT "completed",
                `details`   TEXT DEFAULT NULL,
                `created`   DATETIME NOT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CustomerAppointment::getTableName() . '` (
                `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `series_id`                INT UNSIGNED DEFAULT NULL,
                `package_id`               INT UNSIGNED DEFAULT NULL,
                `customer_id`              INT UNSIGNED NOT NULL,
                `appointment_id`           INT UNSIGNED NOT NULL,
                `payment_id`               INT UNSIGNED DEFAULT NULL,
                `number_of_persons`        INT UNSIGNED NOT NULL DEFAULT 1,
                `units`                    INT UNSIGNED NOT NULL DEFAULT 1,
                `notes`                    TEXT DEFAULT NULL,
                `extras`                   TEXT DEFAULT NULL,
                `extras_multiply_nop`      TINYINT(1) NOT NULL DEFAULT 1,
                `extras_consider_duration` TINYINT(1) NOT NULL DEFAULT 1,
                `custom_fields`            TEXT DEFAULT NULL,
                `status`                   VARCHAR(255) NOT NULL DEFAULT "approved",
                `status_changed_at`        DATETIME NULL,
                `token`                    VARCHAR(255) DEFAULT NULL,
                `time_zone`                VARCHAR(255) DEFAULT NULL,
                `time_zone_offset`         INT DEFAULT NULL,
                `rating`                   INT DEFAULT NULL,
                `rating_comment`           TEXT DEFAULT NULL,
                `locale`                   VARCHAR(8) NULL,
                `collaborative_service_id` INT UNSIGNED DEFAULT NULL,
                `collaborative_token`      VARCHAR(255) DEFAULT NULL,
                `compound_service_id`      INT UNSIGNED DEFAULT NULL,
                `compound_token`           VARCHAR(255) DEFAULT NULL,
                `created_from`             ENUM("frontend","backend") NOT NULL DEFAULT "frontend",
                `created`                  DATETIME NOT NULL,
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
                    FOREIGN KEY (series_id)
                    REFERENCES  ' . Entities\Series::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\SentNotification::getTableName() . '` (
                `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ref_id`          INT UNSIGNED NOT NULL,
                `notification_id` INT UNSIGNED NOT NULL,
                `created`         DATETIME NOT NULL,
                INDEX `ref_id_idx` (`ref_id`),
                CONSTRAINT
                    FOREIGN KEY (notification_id) 
                    REFERENCES  ' . Entities\Notification::getTableName() . ' (`id`) 
                    ON DELETE   CASCADE 
                    ON UPDATE   CASCADE
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Stat::getTableName() . '` (
                `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`     VARCHAR(255) NOT NULL,
                `value`    TEXT DEFAULT NULL,
                `created`  DATETIME NOT NULL
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Message::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `message_id` INT UNSIGNED NOT NULL,
                `type`       VARCHAR(255) NOT NULL,
                `subject`    TEXT,
                `body`       TEXT,
                `seen`       TINYINT(1) NOT NULL DEFAULT 0,
                `created`    DATETIME NOT NULL
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Shop::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `plugin_id`   INT UNSIGNED NOT NULL,
                `type`        ENUM("plugin","bundle") NOT NULL DEFAULT "plugin",
                `highlighted` TINYINT(1) NOT NULL DEFAULT 0,
                `priority`    INT UNSIGNED DEFAULT 0,
                `demo_url`    VARCHAR(255) DEFAULT NULL,
                `title`       VARCHAR(255) NOT NULL,
                `slug`        VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `url`         VARCHAR(255) NOT NULL,
                `icon`        VARCHAR(255) NOT NULL,
                `price`       DECIMAL(10,2) NOT NULL,
                `sales`       INT UNSIGNED NOT NULL,
                `rating`      DECIMAL(10,2) NOT NULL,
                `reviews`     INT UNSIGNED NOT NULL,
                `published`   DATETIME NOT NULL,
                `seen`        TINYINT(1) NOT NULL DEFAULT 0,
                `created`     DATETIME NOT NULL
              ) ENGINE = INNODB
              DEFAULT CHARACTER SET = utf8
              COLLATE = utf8_general_ci'
        );
    }

    /**
     * @inheritdoc
     */
    public function loadData()
    {
        parent::loadData();

        // Insert notifications.
        foreach ( $this->notifications as $data ) {
            $notification = new Entities\Notification();
            $notification->setFields( $data )->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function removeData()
    {
        parent::removeData();

        delete_option( 'bookly_updated_from_legacy_version' );
    }

    /**
     * Remove l10n data.
     */
    protected function _removeL10nData()
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
}