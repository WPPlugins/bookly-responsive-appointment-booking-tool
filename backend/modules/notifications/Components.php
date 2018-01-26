<?php
namespace BooklyLite\Backend\Modules\Notifications;

use BooklyLite\Lib;
use BooklyLite\Lib\Entities\Notification;

/**
 * Class Components
 * @package BooklyLite\Backend\Modules\Notifications
 */
class Components extends Lib\Base\Components
{
    protected $css_prefix = 'bookly-js-codes-';

    /**
     * Codes for all notifications.
     *
     * @return array
     */
    private function getCommonCodes()
    {
        return array(
            array( 'code' => 'company_name',    'description' => __( 'name of company', 'bookly' ) ),
            array( 'code' => 'company_logo',    'description' => __( 'company logo', 'bookly' ) ),
            array( 'code' => 'company_address', 'description' => __( 'address of company', 'bookly' ) ),
            array( 'code' => 'company_phone',   'description' => __( 'company phone', 'bookly' ) ),
            array( 'code' => 'company_website', 'description' => __( 'company web-site address', 'bookly' ) ),
        );
    }

    /**
     * Render codes for notifications
     *
     * @param string $notification_type
     */
    public function renderCodes( $notification_type )
    {
        switch ( $notification_type ) {
            case Notification::TYPE_APPOINTMENT_START_TIME:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED:
            case Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
                $this->renderCustomerAppointmentCodesForCN( $notification_type );
                break;
            case 'staff_agenda':
            case Notification::TYPE_STAFF_DAY_AGENDA:
                $this->renderStaffDayAgendaCodes();
                break;
            case 'client_birthday_greeting':
            case Notification::TYPE_CUSTOMER_BIRTHDAY:
                $this->renderBirthdayGreetingCodes();
                break;
            case 'client_new_wp_user':
                $this->renderNewWpUserCodes();
                break;
            case 'client_pending_appointment_cart':
            case 'client_approved_appointment_cart':
                $this->renderCompoundCodes();
                break;
            case 'staff_waiting_list':
                $this->renderWaitingListCodes();
                break;
            case 'client_pending_appointment':
            case 'staff_pending_appointment':
            case 'client_approved_appointment':
            case 'staff_approved_appointment':
            case 'client_cancelled_appointment':
            case 'staff_cancelled_appointment':
            case 'client_rejected_appointment':
            case 'staff_rejected_appointment':
            case 'client_waitlisted_appointment':
            case 'staff_waitlisted_appointment':
            case 'client_reminder':
            case 'client_reminder_1st':
            case 'client_reminder_2nd':
            case 'client_reminder_3rd':
            case 'client_follow_up':
                $this->renderBaseCodes();
                break;
            case 'staff_package_purchased':
            case 'client_package_purchased':
            case 'staff_package_deleted':
            case 'client_package_deleted':
                $this->renderPackageCodes( $notification_type );
                break;
            case 'client_pending_recurring_appointment':
            case 'staff_pending_recurring_appointment':
            case 'client_approved_recurring_appointment':
            case 'staff_approved_recurring_appointment':
            case 'client_cancelled_recurring_appointment':
            case 'staff_cancelled_recurring_appointment':
            case 'client_rejected_recurring_appointment':
            case 'staff_rejected_recurring_appointment':
            case 'client_waitlisted_recurring_appointment':
            case 'staff_waitlisted_recurring_appointment':
                $this->renderRecurringCodes();
                break;
        }
    }

    /**
     * Render codes for custom notifications with appointment(s)
     *
     * @param string $notification_type
     */
    private function renderCustomerAppointmentCodesForCN( $notification_type )
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'appointment_date',                'description' => __( 'date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_date',            'description' => __( 'end date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_time',            'description' => __( 'end time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_notes',               'description' => __( 'customer notes for appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_time',                'description' => __( 'time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'approve_appointment_url',         'description' => esc_html__( 'URL of approve appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'booking_number',                  'description' => __( 'booking number', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment',              'description' => __( 'cancel appointment link', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment_confirm_url',  'description' => esc_html__( 'URL of cancel appointment link with confirmation (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment_url',          'description' => esc_html__( 'URL of cancel appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'category_name',                   'description' => __( 'name of category', 'bookly' ) );
        $codes[] = array( 'code' => 'client_email',                    'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',               'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',                'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',                     'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',                    'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'google_calendar_url',             'description' => esc_html__( 'URL for adding event to client\'s Google Calendar (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'number_of_persons',               'description' => __( 'number of persons', 'bookly' ) );
        $codes[] = array( 'code' => 'payment_type',                    'description' => __( 'payment type', 'bookly' ) );
        $codes[] = array( 'code' => 'reject_appointment_url',          'description' => esc_html__( 'URL of reject appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'service_duration',                'description' => __( 'duration of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_info',                    'description' => __( 'info of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_name',                    'description' => __( 'name of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_price',                   'description' => __( 'price of service', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',                     'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',                      'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',                      'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',                     'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',                     'description' => __( 'photo of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'total_price',                     'description' => __( 'total price of booking (sum of all cart items after applying coupon)', 'bookly' ) );

        $codes = Lib\Proxy\Packages::prepareNotificationCodesList( $codes );
        $codes = Lib\Proxy\RecurringAppointments::prepareNotificationCodesList( $codes );

        Lib\Utils\Common::codes(
            Lib\Proxy\Shared::prepareNotificationCodesList( $codes, 'customer' ),
            array( 'type' => $notification_type )
        );
    }

    /**
     * Render codes for staff agenda.
     */
    private function renderStaffDayAgendaCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'agenda_date',     'description' => __( 'agenda date', 'bookly' ) );
        $codes[] = array( 'code' => 'next_day_agenda', 'description' => __( 'staff agenda for next day', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',     'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',      'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',      'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',     'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',     'description' => __( 'photo of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'tomorrow_date',   'description' => __( 'date of next day', 'bookly' ) );

        Lib\Utils\Common::codes(
            $codes,
            array( 'type' => Notification::TYPE_STAFF_DAY_AGENDA )
        );
    }

    /**
     * Render codes for Greeting notifications
     */
    private function renderBirthdayGreetingCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'client_email',        'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',   'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',    'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',         'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',        'description' => __( 'phone of client', 'bookly' ) );

        Lib\Utils\Common::codes(
            $codes,
            array( 'type' => Notification::TYPE_CUSTOMER_BIRTHDAY )
        );
    }

    /**
     * Render codes for new WordPress users.
     */
    private function renderNewWpUserCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'client_email',        'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',   'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',    'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',         'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',        'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'new_password',        'description' => __( 'customer new password', 'bookly' ) );
        $codes[] = array( 'code' => 'new_username',        'description' => __( 'customer new username', 'bookly' ) );
        $codes[] = array( 'code' => 'site_address',        'description' => __( 'site address', 'bookly' ) );

        Lib\Utils\Common::codes( $codes );
    }

    /**
     * Render codes for compound (cart) notifications.
     */
    private function renderCompoundCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'cart_info',           'description' => __( 'cart information', 'bookly' ) );
        $codes[] = array( 'code' => 'cart_info_c',         'description' => __( 'cart information with cancel', 'bookly' ) );
        $codes[] = array( 'code' => 'client_email',        'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',   'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',    'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',         'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',        'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'payment_type',        'description' => __( 'payment type', 'bookly' ) );
        $codes[] = array( 'code' => 'total_price',         'description' => __( 'total price of booking (sum of all cart items after applying coupon)', 'bookly' ) );

        Lib\Utils\Common::codes( Lib\Proxy\Shared::prepareCartNotificationShortCodes( $codes ) );
    }

    /**
     * Render base codes
     */
    private function renderBaseCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'appointment_date',                'description' => __( 'date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_date',            'description' => __( 'end date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_time',            'description' => __( 'end time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_notes',               'description' => __( 'customer notes for appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_time',                'description' => __( 'time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'approve_appointment_url',         'description' => esc_html__( 'URL of approve appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'booking_number',                  'description' => __( 'booking number', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment',              'description' => __( 'cancel appointment link', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment_confirm_url',  'description' => esc_html__( 'URL of cancel appointment link with confirmation (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'cancel_appointment_url',          'description' => esc_html__( 'URL of cancel appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'cancellation_reason',             'description' => __( 'reason you mentioned while deleting appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'category_name',                   'description' => __( 'name of category', 'bookly' ) );
        $codes[] = array( 'code' => 'client_email',                    'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',               'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',                'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',                     'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',                    'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'google_calendar_url',             'description' => esc_html__( 'URL for adding event to client\'s Google Calendar (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'number_of_persons',               'description' => __( 'number of persons', 'bookly' ) );
        $codes[] = array( 'code' => 'payment_type',                    'description' => __( 'payment type', 'bookly' ) );
        $codes[] = array( 'code' => 'reject_appointment_url',          'description' => esc_html__( 'URL of reject appointment link (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'service_duration',                'description' => __( 'duration of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_info',                    'description' => __( 'info of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_name',                    'description' => __( 'name of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_price',                   'description' => __( 'price of service', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',                     'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',                      'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',                      'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',                     'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',                     'description' => __( 'photo of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'total_price',                     'description' => __( 'total price of booking (sum of all cart items after applying coupon)', 'bookly' ) );

        Lib\Utils\Common::codes(
            Lib\Proxy\Shared::prepareNotificationCodesList( $codes, 'customer' )
        );
    }

    /**
     * Render codes notifications about package appointments
     *
     * @param string $notification_type
     */
    private function renderPackageCodes( $notification_type )
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'category_name',           'description' => __( 'name of category', 'bookly' ) );
        $codes[] = array( 'code' => 'client_email',            'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',       'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',        'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',             'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',            'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'service_duration',        'description' => __( 'duration of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_info',            'description' => __( 'info of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_name',            'description' => __( 'name of service', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',             'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',              'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',              'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',             'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',             'description' => __( 'photo of staff', 'bookly' ) );

        $codes = Lib\Proxy\Packages::prepareNotificationCodesList( $codes, '', $notification_type );

        Lib\Utils\Common::codes( $codes );
    }

    /**
     * Render codes notifications wor appointments in waiting list
     */
    private function renderWaitingListCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'appointment_date',         'description' => __( 'date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_date',     'description' => __( 'end date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_time',     'description' => __( 'end time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_time',         'description' => __( 'time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_waiting_list', 'description' => __( 'waiting list of appointment', 'bookly-waiting-list' ) );
        $codes[] = array( 'code' => 'booking_number',           'description' => __( 'booking number', 'bookly' ) );
        $codes[] = array( 'code' => 'category_name',            'description' => __( 'name of category', 'bookly' ) );
        $codes[] = array( 'code' => 'service_info',             'description' => __( 'info of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_name',             'description' => __( 'name of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_price',            'description' => __( 'price of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_duration',         'description' => __( 'duration of service', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',              'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',               'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',               'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',              'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',              'description' => __( 'photo of staff', 'bookly' ) );

        $codes = Lib\Proxy\WaitingList::prepareNotificationCodesList( $codes );

        Lib\Utils\Common::codes( Lib\Proxy\Shared::prepareNotificationCodesList( $codes, 'appointment' ) );
    }

    private function renderRecurringCodes()
    {
        $codes = $this->getCommonCodes();
        $codes[] = array( 'code' => 'appointment_date',    'description' => __( 'date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_date','description' => __( 'end date of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_end_time','description' => __( 'end time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'appointment_time',    'description' => __( 'time of appointment', 'bookly' ) );
        $codes[] = array( 'code' => 'booking_number',      'description' => __( 'booking number', 'bookly' ) );
        $codes[] = array( 'code' => 'category_name',       'description' => __( 'name of category', 'bookly' ) );
        $codes[] = array( 'code' => 'client_email',        'description' => __( 'email of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_first_name',   'description' => __( 'first name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_last_name',    'description' => __( 'last name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_name',         'description' => __( 'full name of client', 'bookly' ) );
        $codes[] = array( 'code' => 'client_phone',        'description' => __( 'phone of client', 'bookly' ) );
        $codes[] = array( 'code' => 'google_calendar_url', 'description' => esc_html__( 'URL for adding event to client\'s Google Calendar (to use inside <a> tag)', 'bookly' ) );
        $codes[] = array( 'code' => 'number_of_persons',   'description' => __( 'number of persons', 'bookly' ) );
        $codes[] = array( 'code' => 'payment_type',        'description' => __( 'payment type', 'bookly' ) );
        $codes[] = array( 'code' => 'service_duration',    'description' => __( 'duration of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_info',        'description' => __( 'info of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_name',        'description' => __( 'name of service', 'bookly' ) );
        $codes[] = array( 'code' => 'service_price',       'description' => __( 'price of service', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_email',         'description' => __( 'email of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_info',          'description' => __( 'info of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_name',          'description' => __( 'name of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_phone',         'description' => __( 'phone of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'staff_photo',         'description' => __( 'photo of staff', 'bookly' ) );
        $codes[] = array( 'code' => 'total_price',         'description' => __( 'total price of booking (sum of all cart items after applying coupon)', 'bookly' ) );

        $codes = Lib\Proxy\RecurringAppointments::prepareNotificationCodesList( $codes );

        Lib\Utils\Common::codes( Lib\Proxy\Shared::prepareNotificationCodesList( $codes, 'customer' ) );
    }

}