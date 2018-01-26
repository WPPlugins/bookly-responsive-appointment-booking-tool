<?php
namespace BooklyLite\Lib;

/**
 * Class Installer
 * @package Bookly
 */
class Installer extends Base\Installer
{
    protected $notifications;

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
                'message' => wpautop( __( "Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
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
                'message' => wpautop( __( "Hello.\n\nYou have a new booking.\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
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
                'type'    => 'client_rejected_appointment',
                'subject' => __( 'Booking rejection', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nYour booking of {service_name} on {appointment_date} at {appointment_time} has been rejected.\n\nReason: {cancellation_reason}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'staff_rejected_appointment',
                'subject' => __( 'Booking rejection', 'bookly' ),
                'message' => wpautop( __( "Hello.\n\nThe following booking has been rejected.\n\nReason: {cancellation_reason}\n\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ) ),
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
                'message' => wpautop( __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_reminder_1st',
                'subject' => __( 'Your appointment at {company_name}', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_reminder_2nd',
                'subject' => __( 'Your appointment at {company_name}', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'email',
                'type'    => 'client_reminder_3rd',
                'subject' => __( 'Your appointment at {company_name}', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
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
                'type'    => 'client_birthday_greeting',
                'subject' => __( 'Happy Birthday!', 'bookly' ),
                'message' => wpautop( __( "Dear {client_name},\n\nHappy birthday!\nWe wish you all the best.\nMay you and your family be happy and healthy.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ) ),
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
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_pending_appointment_cart',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_pending_appointment',
                'subject' => '',
                'message' => __( "Hello.\nYou have a new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 1,
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
                'message' => __( "Hello.\nYou have a new booking.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_cancelled_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nYou have cancelled your booking of {service_name} on {appointment_date} at {appointment_time}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_cancelled_appointment',
                'subject' => '',
                'message' => __( "Hello.\nThe following booking has been cancelled.\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_rejected_appointment',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nYour booking of {service_name} on {appointment_date} at {appointment_time} has been rejected.\nReason: {cancellation_reason}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 1,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'staff_rejected_appointment',
                'subject' => '',
                'message' => __( "Hello.\nThe following booking has been rejected.\nReason: {cancellation_reason}\nService: {service_name}\nDate: {appointment_date}\nTime: {appointment_time}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
                'active'  => 1,
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
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_time}. We are waiting for you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_reminder_1st',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_reminder_2nd',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
                'active'  => 0,
            ),
            array(
                'gateway' => 'sms',
                'type'    => 'client_reminder_3rd',
                'subject' => '',
                'message' => __( "Dear {client_name}.\nWe would like to remind you that you have booked {service_name} on {appointment_date} at {appointment_time}. We are waiting for you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
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
                'type'    => 'client_birthday_greeting',
                'subject' => '',
                'message' => __( "Dear {client_name},\nHappy birthday!\nWe wish you all the best.\nMay you and your family be happy and healthy.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
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
            // Appearance.
            'bookly_admin_preferred_language'            => '',
            'bookly_api_server_error_time'               => '0',
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
            'bookly_app_staff_name_with_price'           => '1',
            'bookly_l10n_button_apply'                   => __( 'Apply', 'bookly' ),
            'bookly_l10n_button_back'                    => __( 'Back', 'bookly' ),
            'bookly_l10n_button_book_more'               => __( 'Book More', 'bookly' ),
            'bookly_l10n_info_cart_step'                 => __( "Below you can find a list of services selected for booking.\nClick BOOK MORE if you want to add more services.", 'bookly' ),
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
            'bookly_l10n_label_employee'                 => __( 'Employee', 'bookly' ),
            'bookly_l10n_label_finish_by'                => __( 'Finish by', 'bookly' ),
            'bookly_l10n_label_name'                     => __( 'Name', 'bookly' ),
            'bookly_l10n_label_first_name'               => __( 'First name', 'bookly' ),
            'bookly_l10n_label_last_name'                => __( 'Last name', 'bookly' ),
            'bookly_l10n_label_notes'                    => __( 'Notes', 'bookly' ),
            'bookly_l10n_label_number_of_persons'        => __( 'Number of persons', 'bookly' ),
            'bookly_l10n_label_pay_ccard'                => __( 'I will pay now with Credit Card', 'bookly' ),
            'bookly_l10n_label_pay_locally'              => __( 'I will pay locally', 'bookly' ),
            'bookly_l10n_label_pay_mollie'               => __( 'I will pay now with Mollie', 'bookly' ),
            'bookly_l10n_label_pay_paypal'               => __( 'I will pay now with PayPal', 'bookly' ),
            'bookly_l10n_label_phone'                    => __( 'Phone', 'bookly' ),
            'bookly_l10n_label_select_date'              => __( 'I\'m available on or after', 'bookly' ),
            'bookly_l10n_label_service'                  => __( 'Service', 'bookly' ),
            'bookly_l10n_label_start_from'               => __( 'Start from', 'bookly' ),
            'bookly_l10n_option_category'                => __( 'Select category', 'bookly' ),
            'bookly_l10n_option_employee'                => __( 'Any', 'bookly' ),
            'bookly_l10n_option_service'                 => __( 'Select service', 'bookly' ),
            'bookly_l10n_required_email'                 => __( 'Please tell us your email', 'bookly' ),
            'bookly_l10n_required_employee'              => __( 'Please select an employee', 'bookly' ),
            'bookly_l10n_required_name'                  => __( 'Please tell us your name', 'bookly' ),
            'bookly_l10n_required_first_name'            => __( 'Please tell us your first name', 'bookly' ),
            'bookly_l10n_required_last_name'             => __( 'Please tell us your last name', 'bookly' ),
            'bookly_l10n_required_phone'                 => __( 'Please tell us your phone', 'bookly' ),
            'bookly_l10n_required_service'               => __( 'Please select a service', 'bookly' ),
            'bookly_l10n_step_service'                   => __( 'Service', 'bookly' ),
            'bookly_l10n_step_time'                      => __( 'Time', 'bookly' ),
            'bookly_l10n_step_time_slot_not_available'   => __( 'The selected time is not available anymore. Please, choose another time slot.', 'bookly' ),
            'bookly_l10n_step_cart'                      => __( 'Cart', 'bookly' ),
            'bookly_l10n_step_cart_slot_not_available'   => __( 'The highlighted time is not available anymore. Please, choose another time slot.', 'bookly' ),
            'bookly_l10n_step_details'                   => __( 'Details', 'bookly' ),
            'bookly_l10n_step_details_button_login'      => __( 'Login', 'bookly' ),
            'bookly_l10n_step_payment'                   => __( 'Payment', 'bookly' ),
            'bookly_l10n_step_done'                      => __( 'Done', 'bookly' ),
            // Button Next.
            'bookly_l10n_step_service_button_next'       => __( 'Next', 'bookly' ),
            'bookly_l10n_step_service_mobile_button_next' => __( 'Next', 'bookly' ),
            'bookly_l10n_step_cart_button_next'          => __( 'Next', 'bookly' ),
            'bookly_l10n_step_details_button_next'       => __( 'Next', 'bookly' ),
            'bookly_l10n_step_payment_button_next'       => __( 'Next', 'bookly' ),
            // Cart.
            'bookly_cart_enabled'                        => '0',
            'bookly_cart_show_columns'                   => array(
                'service' => array( 'show' => 1 ), 'date' => array( 'show' => 1 ), 'time' => array( 'show' => 1 ),
                'employee' => array( 'show' => 1 ), 'price' => array( 'show' => 1 ), 'deposit' => array( 'show' => 1 ),
            ),
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
            'bookly_cst_cancel_action'                   => 'cancel',
            'bookly_cst_combined_notifications'          => '0',
            'bookly_cst_create_account'                  => '0',
            'bookly_cst_default_country_code'            => '',
            'bookly_cst_new_account_role'                => 'subscriber',
            'bookly_cst_phone_default_country'           => 'auto',
            'bookly_cst_remember_in_cookie'              => '0',
            'bookly_cst_show_update_details_dialog'      => '1',
            'bookly_cst_first_last_name'                 => '0',
            'bookly_cst_required_phone'                  => '1',
            // Email notifications.
            'bookly_email_sender'                        => get_option( 'admin_email' ),
            'bookly_email_sender_name'                   => get_option( 'blogname' ),
            'bookly_email_send_as'                       => 'html',
            'bookly_email_reply_to_customers'            => '1',
            // Google Calendar.
            'bookly_gc_client_id'                        => '',
            'bookly_gc_client_secret'                    => '',
            'bookly_gc_event_title'                      => '{service_name}',
            'bookly_gc_limit_events'                     => '50',
            'bookly_gc_two_way_sync'                     => '1',
            // General.
            'bookly_gen_lite_uninstall_remove_bookly_data' => '0',
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
            'bookly_url_cancel_confirm_page_url'         => home_url(),
            'bookly_url_reject_page_url'                 => home_url(),
            'bookly_url_reject_denied_page_url'          => home_url(),
            'bookly_url_final_step_url'                  => '',
            // Cron.
            'bookly_cron_reminder_times'                 => array( 'client_follow_up' => 21, 'client_reminder' => 18, 'client_birthday_greeting' => 9, 'staff_agenda' => 18, 'client_reminder_1st' => 1, 'client_reminder_2nd' => 2, 'client_reminder_3rd' => 3 ),
            'bookly_reminder_data'                       => array( 'SW1wb3J0YW50ISBJdCBsb29rcyBsaWtlIHlvdSBhcmUgdXNpbmcgYW4gaWxsZWdhbCBjb3B5IG9mIEJvb2tseSDigJMgaXQgbWF5IGNvbnRhaW4gYSBtYWxpY2lvdXMgY29kZSwgYSB0cm9qYW4gb3IgYSBiYWNrZG9vci4=', 'VGhlIGxlZ2FsIGNvcHkgb2YgQm9va2x5IGluY2x1ZGVzIGFsbCBmZWF0dXJlcywgbGlmZXRpbWUgZnJlZSB1cGRhdGVzLCBhbmQgMjQvNyBzdXBwb3J0LiBCeSBidXlpbmcgYSBsZWdhbCBjb3B5IG9mIEJvb2tseSBhdCBhIHNwZWNpYWwgZGlzY291bnRlZCBwcmljZSwgeW91IG1heSBiZW5lZml0IGZyb20gb3VyIHBhcnRuZXLigJlzIGV4Y2x1c2l2ZSBkaXNjb3VudHMh', 'PGEgaHJlZj0iaHR0cHM6Ly93d3cuYm9va2luZy13cC1wbHVnaW4uY29tL2JlY29tZS1sZWdhbC8iIHRhcmdldD0iX2JsYW5rIj5DbGljayBoZXJlIHRvIGxlYXJuIG1vcmUgPj4+PC9hPg' ),
            'bookly_lic_repeat_time'                     => time() + 7776000,
            // Grace.
            'bookly_grace_notifications'                 => array( 'bookly' => '0', 'add-ons' => 0, 'sent' => '0' ),
            'bookly_grace_hide_admin_notice_time'        => '0',
            // SMS.
            'bookly_sms_token'                           => '',
            'bookly_sms_administrator_phone'             => '',
            'bookly_sms_notify_low_balance'              => '1',
            'bookly_sms_notify_weekly_summary'           => '1',
            'bookly_sms_notify_weekly_summary_sent'      => date( 'W' ),
            // WooCommerce.
            'bookly_wc_enabled'                          => '0',
            'bookly_wc_product'                          => '',
            'bookly_l10n_wc_cart_info_name'              => __( 'Appointment', 'bookly' ),
            'bookly_l10n_wc_cart_info_value'             => __( 'Date', 'bookly' ) . ": {appointment_date}\n"
                . __( 'Time', 'bookly' ) . ": {appointment_time}\n" . __( 'Service', 'bookly' ) . ': {service_name}',
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
            'bookly_pmt_price_format'                    => '{symbol}{price|2}',
            // Pay locally.
            'bookly_pmt_local'                           => '1',
            // PayPal.
            'bookly_paypal_enabled'                      => '0',
            'bookly_paypal_sandbox'                      => '0',
            'bookly_paypal_api_password'                 => '',
            'bookly_paypal_api_signature'                => '',
            'bookly_paypal_api_username'                 => '',
            'bookly_paypal_id'                           => '',
            // Notifications.
            'bookly_ntf_processing_interval'             => '2', // hours
        );
    }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        /** @var Plugin $plugin */
        if ( get_option( 'bookly_gen_lite_uninstall_remove_bookly_data' ) ) {
            $this->removeData();
            $this->dropPluginTables();
            $this->_removeL10nData();

            // Remove user meta.
            $filter_appointments           = Plugin::getPrefix() . 'filter_appointments_list';
            $appearance_notice             = Plugin::getPrefix() . 'dismiss_appearance_notice';
            $contact_us_notice             = Plugin::getPrefix() . 'dismiss_contact_us_notice';
            $feedback_notice               = Plugin::getPrefix() . 'dismiss_feedback_notice';
            $subscribe_notice              = Plugin::getPrefix() . 'dismiss_subscribe_notice';
            $nps_notice                    = Plugin::getPrefix() . 'dismiss_nps_notice';
            $collect_stats_notice          = Plugin::getPrefix() . 'dismiss_collect_stats_notice';
            $contact_us_btn_clicked        = Plugin::getPrefix() . 'contact_us_btn_clicked';
            $appointment_form_notification = Plugin::getPrefix() . 'appointment_form_send_notifications';
            $lic_repeat_time               = Plugin::getPrefix() . 'lic_repeat_time';
            foreach ( get_users( array( 'role' => 'administrator' ) ) as $admin ) {
                delete_user_meta( $admin->ID, $filter_appointments );
                delete_user_meta( $admin->ID, $appearance_notice );
                delete_user_meta( $admin->ID, $contact_us_notice );
                delete_user_meta( $admin->ID, $feedback_notice );
                delete_user_meta( $admin->ID, $subscribe_notice );
                delete_user_meta( $admin->ID, $nps_notice );
                delete_user_meta( $admin->ID, $collect_stats_notice );
                delete_user_meta( $admin->ID, $contact_us_btn_clicked );
                delete_user_meta( $admin->ID, $appointment_form_notification );
                delete_user_meta( $admin->ID, $lic_repeat_time );
            }

            wp_clear_scheduled_hook( 'bookly_daily_routine' );
        }
    }

    /**
     * Create tables in database.
     */
    public function createTables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Staff::getTableName() . '` (
                `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wp_user_id`         BIGINT(20) UNSIGNED DEFAULT NULL,
                `attachment_id`      INT UNSIGNED DEFAULT NULL,
                `full_name`          VARCHAR(255) DEFAULT NULL,
                `email`              VARCHAR(255) DEFAULT NULL,
                `phone`              VARCHAR(255) DEFAULT NULL,
                `info`               TEXT DEFAULT NULL,
                `google_data`        TEXT DEFAULT NULL,
                `google_calendar_id` VARCHAR(255) DEFAULT NULL,
                `visibility`         ENUM("public","private") NOT NULL DEFAULT "public",
                `position`           INT NOT NULL DEFAULT 9999
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
                `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `category_id`            INT UNSIGNED DEFAULT NULL,
                `title`                  VARCHAR(255) DEFAULT "",
                `duration`               INT NOT NULL DEFAULT 900,
                `price`                  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `color`                  VARCHAR(255) NOT NULL DEFAULT "#FFFFFF",
                `capacity_min`           INT NOT NULL DEFAULT 1,
                `capacity_max`           INT NOT NULL DEFAULT 1,
                `padding_left`           INT NOT NULL DEFAULT 0,
                `padding_right`          INT NOT NULL DEFAULT 0,
                `info`                   TEXT DEFAULT NULL,
                `start_time_info`        VARCHAR(255) DEFAULT "",
                `end_time_info`          VARCHAR(255) DEFAULT "",
                `type`                   ENUM("simple","compound","package") NOT NULL DEFAULT "simple",
                `package_life_time`      INT DEFAULT NULL,
                `package_size`           INT DEFAULT NULL,
                `appointments_limit`     INT DEFAULT NULL,
                `limit_period`           ENUM("off", "day","week","month","year") NOT NULL DEFAULT "off",
                `staff_preference`       ENUM("order", "least_occupied", "most_occupied", "least_expensive", "most_expensive") NOT NULL DEFAULT "most_expensive",
                `recurrence_enabled`     TINYINT(1) NOT NULL DEFAULT 1,
                `recurrence_frequencies` SET("daily","weekly","biweekly","monthly") NOT NULL DEFAULT "daily,weekly,biweekly,monthly",
                `visibility`             ENUM("public","private") NOT NULL DEFAULT "public",
                `position`               INT NOT NULL DEFAULT 9999,
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffPreferenceOrder::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `service_id`  INT UNSIGNED NOT NULL,
                `staff_id`    INT UNSIGNED NOT NULL,
                `position`    INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffScheduleItem::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`   INT UNSIGNED NOT NULL,
                `day_index`  INT UNSIGNED NOT NULL,
                `start_time` TIME DEFAULT NULL,
                `end_time`   TIME DEFAULT NULL,
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
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffService::getTableName() . '` (
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `staff_id`     INT UNSIGNED NOT NULL,
                `service_id`   INT UNSIGNED NOT NULL,
                `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `deposit`      VARCHAR(100) NOT NULL DEFAULT "100%",
                `capacity_min` INT NOT NULL DEFAULT 1,
                `capacity_max` INT NOT NULL DEFAULT 1,
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
                `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `gateway`      ENUM("email","sms") NOT NULL DEFAULT "email",
                `type`         VARCHAR(255) NOT NULL DEFAULT "",
                `active`       TINYINT(1) NOT NULL DEFAULT 0,
                `subject`      VARCHAR(255) NOT NULL DEFAULT "",
                `message`      TEXT DEFAULT NULL,
                `to_staff`     TINYINT(1) NOT NULL DEFAULT 0,
                `to_customer`  TINYINT(1) NOT NULL DEFAULT 0,
                `to_admin`     TINYINT(1) NOT NULL DEFAULT 0,
                `attach_ics`   TINYINT(1) NOT NULL DEFAULT 0,
                `settings`     TEXT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Customer::getTableName() . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `wp_user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                `full_name`  VARCHAR(255) NOT NULL DEFAULT "",
                `first_name` VARCHAR(255) NOT NULL DEFAULT "",
                `last_name`  VARCHAR(255) NOT NULL DEFAULT "",
                `phone`      VARCHAR(255) NOT NULL DEFAULT "",
                `email`      VARCHAR(255) NOT NULL DEFAULT "",
                `notes`      TEXT NOT NULL DEFAULT "",
                `birthday`   DATE DEFAULT NULL
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
                `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `series_id`            INT UNSIGNED DEFAULT NULL,
                `location_id`          INT UNSIGNED DEFAULT NULL,
                `staff_id`             INT UNSIGNED NOT NULL,
                `staff_any`            TINYINT(1) NOT NULL DEFAULT 0,
                `service_id`           INT UNSIGNED DEFAULT NULL,
                `custom_service_name`  VARCHAR(255) DEFAULT NULL,
                `custom_service_price` DECIMAL(10,2) DEFAULT NULL,
                `start_date`           DATETIME NOT NULL,
                `end_date`             DATETIME NOT NULL,
                `google_event_id`      VARCHAR(255) DEFAULT NULL,
                `extras_duration`      INT NOT NULL DEFAULT 0,
                `internal_note`        TEXT DEFAULT NULL,
                CONSTRAINT
                    FOREIGN KEY (series_id)
                    REFERENCES  ' . Entities\Series::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
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
                `type`      ENUM("local","coupon","paypal","authorize_net","stripe","2checkout","payu_latam","payson","mollie","woocommerce") NOT NULL DEFAULT "local",
                `total`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `paid_type` ENUM("in_full","deposit") NOT NULL DEFAULT "in_full",
                `status`    ENUM("pending","completed") NOT NULL DEFAULT "completed",
                `details`   TEXT DEFAULT NULL,
                `created`   DATETIME NOT NULL
            ) ENGINE = INNODB
            DEFAULT CHARACTER SET = utf8
            COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\CustomerAppointment::getTableName() . '` (
                `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `package_id`          INT UNSIGNED DEFAULT NULL,
                `customer_id`         INT UNSIGNED NOT NULL,
                `appointment_id`      INT UNSIGNED NOT NULL,
                `payment_id`          INT UNSIGNED DEFAULT NULL,
                `number_of_persons`   INT UNSIGNED NOT NULL DEFAULT 1,
                `notes`               TEXT DEFAULT NULL,
                `extras`              TEXT DEFAULT NULL,
                `custom_fields`       TEXT DEFAULT NULL,
                `status`              ENUM("pending","approved","cancelled","rejected","waitlisted") NOT NULL DEFAULT "approved",
                `status_changed_at`   DATETIME NULL,
                `token`               VARCHAR(255) DEFAULT NULL,
                `time_zone`           VARCHAR(255) DEFAULT NULL,
                `time_zone_offset`    INT DEFAULT NULL,
                `locale`              VARCHAR(8) NULL,
                `compound_service_id` INT UNSIGNED DEFAULT NULL,
                `compound_token`      VARCHAR(255) DEFAULT NULL,
                `created_from`        ENUM("frontend","backend") NOT NULL DEFAULT "frontend",
                `created`             DATETIME NOT NULL,
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
    }

    /**
     * Load data.
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