<?php
namespace BooklyLite\Lib;

/**
 * Class NotificationSender
 * @package BooklyLite\Lib
 */
abstract class NotificationSender
{
    /** @var SMS */
    private static $sms = null;

    /**
     * Send instant notifications.
     *
     * @param Entities\CustomerAppointment $ca
     * @param mixed[] extra data for templates
     */
    public static function send( Entities\CustomerAppointment $ca, array $data = array() )
    {
        list ( $codes, $appointment, $customer, $staff ) = self::_prepareData( $ca );

        if ( isset( $data['cancellation_reason'] ) ) {
            $codes->set( 'cancellation_reason', $data['cancellation_reason'] );
        }

        $status = $ca->get( 'status' );

        // Notify staff by SMS.
        if ( $notification = self::_getSmsNotification( 'staff', $status ) ) {
            self::_sendSmsToStaff( $notification, $codes, $staff->get( 'phone' ) );
        }
        // Client time zone offset.
        if ( $ca->get( 'time_zone_offset' ) !== null ) {
            $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
            $codes->set( 'appointment_end', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'end_date' ), $ca->get( 'time_zone_offset' ) ) );
        }
        // Notify client by SMS.
        if ( $notification = self::_getSmsNotification( 'client', $status ) ) {
            self::_sendSmsToClient( $notification, $codes, $customer->get( 'phone' ) );
        }
    }

    /**
     * Send notification for recurring appointment list
     * @todo support cart
     *
     * @param array $recurring_list [appointments[], customers[]]
     * @param mixed[] extra data for templates
     */
    public static function sendRecurring( array $recurring_list, array $data = array() )
    {
        if ( $recurring_list['appointments'] ) {
            $first_ca = null;
            $staff    = null;
            $schedule_data = array( 'appointments' => array() );
            /** @var Entities\Appointment $appointment */
            foreach ( $recurring_list['appointments'] as $appointment ) {
                if ( $first_ca === null ) {
                    $first_ca = current( $appointment->getCustomerAppointments( true ) );
                    $staff    = Entities\Staff::find( $appointment->get( 'staff_id' ) );
                }
                $schedule_data['appointments'][ $appointment->get( 'id' ) ] = array(
                    'start' => $appointment->get( 'start_date' ),
                );
            }
            $customers_id = array();
            foreach ( $recurring_list['customers'] as $customer_data ) {
                $customers_id[] = $customer_data['id'];
            }
            list ( $codes ) = self::_prepareData( $first_ca );
            $codes->set( 'cancellation_reason', $data['cancellation_reason'] );
            $cas_token = Entities\CustomerAppointment::query( 'ca' )
                ->select( 'ca.appointment_id,ca.token,ca.customer_id' )
                ->whereIn( 'ca.appointment_id', array_keys( $schedule_data['appointments'] ) )
                ->whereIn( 'ca.customer_id', $customers_id )
                ->fetchArray();

            $time_zone_offset = $first_ca->get( 'time_zone_offset' );
            $original_start = $codes->get( 'appointment_start' );
            $original_end   = $codes->get( 'appointment_end' );
            foreach ( $recurring_list['customers'] as $customer_data ) {
                $customer_id = $customer_data['id'];
                $customer    = Entities\Customer::find( $customer_id );
                // Codes for first ca, set codes for current customer
                $codes->set( 'client_email', $customer->get( 'email' ) );
                $codes->set( 'client_name',  $customer->get( 'name' ) );
                $codes->set( 'client_phone', $customer->get( 'phone' ) );
                $status         = $customer_data['status'];

                $schedule_codes = $schedule_data;
                foreach ( $cas_token as $appointment ) {
                    if ( $appointment['customer_id'] == $customer_id ) {
                        // Set token for customer appointment
                        $schedule_codes['appointments'][ $appointment['appointment_id'] ]['token'] = $appointment['token'];
                    }
                }
                /* schedule_codes = [
                 *      appointments = [
                 *          appointment_id = [
                 *              start => Y-m-d H:i:s
                 *              token   => ca.token
                 *          ]
                 *          ...
                 *      ]
                 * ]
                 */
                $codes->set( 'schedule_codes', $schedule_codes );

                // Notify staff by SMS.
                if ( $notification = self::_getSmsNotification( 'staff', $status, true ) ) {
                    self::_sendSmsToStaff( $notification, $codes, $staff->get( 'phone' ) );
                }
                if ( $time_zone_offset !== null ) {
                    // For client need apply client time zone offset.
                    foreach ( $schedule_codes['appointments'] as $appointment ) {
                        $appointment['start'] = Utils\DateTime::applyTimeZoneOffset( $appointment['start'], $time_zone_offset );
                    }
                    $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $codes->get( 'appointment_start' ), $time_zone_offset ) );
                    $codes->set( 'appointment_end', Utils\DateTime::applyTimeZoneOffset( $codes->get( 'appointment_end' ), $time_zone_offset ) );
                }
                // Notify client by SMS.
                if ( $notification = self::_getSmsNotification( 'client', $status, true ) ) {
                    self::_sendSmsToClient( $notification, $codes, $customer->get( 'phone' ) );
                }
                if ( $time_zone_offset !== null && count( $recurring_list['customers'] ) > 1 ) {
                    // Restore appointment_start & appointment_end for staff notifications
                    // When sending notifications for customers values was changed
                    $codes->set( 'appointment_start', $original_start );
                    $codes->set( 'appointment_end', $original_end );
                }
            }
        }
    }

    /**
     * Send notification from cart.
     *
     * @param Entities\CustomerAppointment[] $ca_list
     */
    public static function sendFromCart( array $ca_list )
    {
        if ( Config::areCombinedNotificationsEnabled() && ! empty( $ca_list ) ) {
            $status    = get_option( 'bookly_gen_default_appointment_status' );
            $cart_info = array();
            $payments  = array();
            $customer  = null;
            $codes     = null;
            $total     = 0.0;
            $compound_tokens = array();
            $sms_to_staff    = self::_getSmsNotification( 'staff', $status );

            foreach ( $ca_list as $ca ) {
                if ( ! isset( $compound_tokens[ $ca->get( 'compound_token' ) ] ) ) {
                    if ( $ca->get( 'compound_token' ) ) {
                        $compound_tokens[ $ca->get( 'compound_token' ) ] = true;
                    }
                    list ( $codes, $appointment, $customer, $staff ) = self::_prepareData( $ca );

                    if ( $sms_to_staff ) {
                        // Send SMS to staff member (and admins if necessary).
                        self::_sendSmsToStaff( $sms_to_staff, $codes, $staff->get( 'phone' ) );
                    }

                    // Prepare data for {cart_info} || {cart_info_c}.
                    $cart_info[] = array(
                        'appointment_price' => ( $codes->get( 'service_price' ) + $codes->get( 'extras_total_price', 0 ) )  * $codes->get( 'number_of_persons' ),
                        'appointment_start' => $ca->get( 'time_zone_offset' ) !== null
                            ? Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) )
                            : $codes->get( 'appointment_start' ),
                        'cancel_url'   => admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $codes->get( 'appointment_token' ) ),
                        'service_name' => $codes->get( 'service_name' ),
                        'staff_name'   => $codes->get( 'staff_name' ),
                        'extras'       => apply_filters( 'bookly_service_extras_get_data_for_appointment', array(), $ca->get( 'extras' ), true ),
                    );
                    if ( ! isset( $payments[ $ca->get( 'payment_id' ) ] ) ) {
                        if ( $ca->get( 'payment_id' ) ) {
                            $payments[ $ca->get( 'payment_id' ) ] = true;
                        }
                        $total += $codes->get( 'total_price' );
                    }
                }
            }
            $codes->set( 'total_price', $total );
            $codes->set( 'cart_info',   $cart_info );

            if ( $to_client = self::_getCombinedSmsNotification( $status ) ) {
                self::_sendSmsToClient( $to_client, $codes, $customer->get( 'phone' ) );
            }
        } else { // Combined notifications disabled.
            $recurrings_lists = array();
            foreach ( $ca_list as $ca ) {
                $appointment = new Entities\Appointment();
                $appointment->load( $ca->get( 'appointment_id' ) );
                if ( $appointment->get( 'series_id' ) ) {
                    $recurrings_lists[ $appointment->get( 'series_id' ) ][ 'appointments' ][] = $appointment;
                    $recurrings_lists[ $appointment->get( 'series_id' ) ][ 'customers' ][ $ca->get( 'customer_id' ) ] = array(
                        'id'     => $ca->get( 'customer_id' ),
                        'status' => $ca->get( 'status' ),
                    );
                } else {
                    self::send( $ca );
                }
            }
            foreach ( $recurrings_lists as $recurrings_list ) {
                self::sendRecurring( $recurrings_list );
            }
        }
    }

    /**
     * Send reminder (email or SMS) to client.
     *
     * @param Entities\Notification $notification
     * @param Entities\CustomerAppointment $ca
     * @return bool
     */
    public static function sendFromCronToClient( Entities\Notification $notification, Entities\CustomerAppointment $ca )
    {
        list ( $codes, $appointment, $customer ) = self::_prepareData( $ca );

        // Client time zone offset.
        if ( $ca->get( 'time_zone_offset' ) !== null ) {
            $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
            $codes->set( 'appointment_end', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'end_date' ), $ca->get( 'time_zone_offset' ) ) );
        }

        // Send notification to client.
        return $notification->get( 'gateway' ) == 'email'
            ? null
            : self::_sendSmsToClient( $notification, $codes, $customer->get( 'phone' ), $ca->get( 'locale' ) );
    }

    /**
     * Send reminder (email or SMS) to staff.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @param string $email
     * @param string $phone
     * @return bool
     */
    public static function sendFromCronToStaff( Entities\Notification $notification, NotificationCodes $codes, $email, $phone )
    {
        return $notification->get( 'gateway' ) == 'email'
            ? null
            : self::_sendSmsToStaff( $notification, $codes, $phone );
    }

    /**
     * Send email with username and password for newly created WP user.
     *
     * @param Entities\Customer $customer
     * @param $username
     * @param $password
     */
    public static function sendEmailForNewUser( Entities\Customer $customer, $username, $password )
    {
        $codes = new NotificationCodes();
        $codes->set( 'client_email', $customer->get( 'email' ) );
        $codes->set( 'client_name',  $customer->get( 'name' ) );
        $codes->set( 'client_phone', $customer->get( 'phone' ) );
        $codes->set( 'new_password', $password );
        $codes->set( 'new_username', $username );
        $codes->set( 'site_address', site_url() );

        $to_client = new Entities\Notification();
        if ( $to_client->loadBy( array( 'type' => 'client_new_wp_user', 'gateway' => 'sms', 'active' => 1 ) ) ) {
            self::_sendSmsToClient( $to_client, $codes, $customer->get( 'phone' ) );
        }
    }

    /**
     * Send test notification emails.
     *
     * @param string $to_mail
     * @param array  $notification_types
     * @param string $send_as
     */
    public static function sendTestEmailNotifications( $to_mail, array $notification_types, $send_as )
    {
    }

    /******************************************************************************************************************
     * Private methods                                                                                                *
     ******************************************************************************************************************/

    /**
     * Prepare data for email.
     *
     * @param Entities\CustomerAppointment $ca
     * @param mixed[] extra data for templates
     * @return array [ NotificationCodes, Entities\Appointment, Entities\Customer, Entities\Staff ]
     */
    private static function _prepareData( Entities\CustomerAppointment $ca, array $data = array() )
    {
        global $sitepress;

        if ( $sitepress instanceof \SitePress ) {
            $sitepress->switch_lang( $ca->get( 'locale' ), true );
        }
        $appointment = new Entities\Appointment();
        $appointment->load( $ca->get( 'appointment_id' ) );

        $customer = new Entities\Customer();
        $customer->load( $ca->get( 'customer_id' ) );

        $staff = new Entities\Staff();
        $staff->load( 1 );

        $service = new Entities\Service();
        $staff_service = new Entities\StaffService();
        if ( $ca->get( 'compound_service_id' ) ) {
            $service->load( $ca->get( 'compound_service_id' ) );
            $staff_service->loadBy( array( 'staff_id' => $staff->get( 'id' ), 'service_id' => $service->get( 'id' ) ) );
            $price = $service->get( 'price' );
            // The appointment ends when the last service ends in the compound service.
            $bounding = Entities\Appointment::query( 'a' )
                ->select( 'MIN(a.start_date) AS start, MAX(DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND)) AS end' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->where( 'ca.compound_token', $ca->get( 'compound_token' ) )
                ->groupBy( 'ca.compound_token' )
                ->fetchRow();
            $appointment_start = $bounding['start'];
            $appointment_end   = $bounding['end'];
        } else {
            $service->load( $appointment->get( 'service_id' ) );
            $staff_service->loadBy( array( 'staff_id' => $staff->get( 'id' ), 'service_id' => $service->get( 'id' ) ) );
            $price = $staff_service->get( 'price' );
            $appointment_end   = date_create( $appointment->get( 'end_date' ) )->modify( '+' . $appointment->get( 'extras_duration' ) . ' sec' )->format( 'Y-m-d H:i:s' );
            $appointment_start = $appointment->get( 'start_date' );
        }

        $staff_photo = wp_get_attachment_image_src( $staff->get( 'attachment_id' ), 'full' );

        $codes = new NotificationCodes();
        $codes->set( 'appointment_end',     $appointment_end );
        $codes->set( 'appointment_start',   $appointment_start );
        $codes->set( 'appointment_token',   $ca->get( 'token' ) );
        $codes->set( 'booking_number' ,     $appointment->get( 'id' ) );
        $codes->set( 'category_name',       $service->getCategoryName() );
        $codes->set( 'client_email',        $customer->get( 'email' ) );
        $codes->set( 'client_name',         $customer->get( 'name' ) );
        $codes->set( 'client_phone',        $customer->get( 'phone' ) );
        $codes->set( 'custom_fields',       $ca->getFormattedCustomFields( 'text' ) );
        $codes->set( 'custom_fields_2c',    $ca->getFormattedCustomFields( 'html' ) );
        $codes->set( 'number_of_persons',   $ca->get( 'number_of_persons' ) );
        $codes->set( 'service_info',        $service->getInfo() );
        $codes->set( 'service_name',        $service->getTitle() );
        $codes->set( 'service_price',       $price );
        $codes->set( 'staff_email',         $staff->get( 'email' ) );
        $codes->set( 'staff_info',          $staff->getInfo() );
        $codes->set( 'staff_name',          $staff->getName() );
        $codes->set( 'staff_phone',         $staff->get( 'phone' ) );
        $codes->set( 'staff_photo',         $staff_photo ? $staff_photo[0] : '' );

        $codes = apply_filters( 'bookly_prepare_notification_codes', $codes, $ca );

        if ( $ca->get( 'payment_id' ) ) {
            $payment = Entities\Payment::find( $ca->get( 'payment_id' ) );
            $codes->set( 'amount_paid',  $payment->get( 'paid' ) );
            $codes->set( 'amount_due',   $payment->get( 'total' ) - $payment->get( 'paid' ) );
            $codes->set( 'payment_type', Entities\Payment::typeToString( $payment->get( 'type' ) ) );
            $codes->set( 'total_price',  $payment->get( 'total' ) );
        } else {
            $codes->set( 'amount_paid', '' );
            $codes->set( 'amount_due',  '' );
            $codes->set( 'total_price', ( $codes->get( 'service_price' ) + $codes->get( 'extras_total_price', 0 ) ) * $codes->get( 'number_of_persons' ) );
        }

        return array( $codes, $appointment, $customer, $staff );
    }

    /**
     * Send SMS notification to client.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @param string $phone
     * @param string|null $language_code
     * @return bool
     */
    private static function _sendSmsToClient( Entities\Notification $notification, NotificationCodes $codes, $phone, $language_code = null )
    {
        $message = $codes->replace( Utils\Common::getTranslatedString(
            'sms_' . $notification->get( 'type' ),
            $notification->get( 'message' ),
            $language_code
        ), 'text' );

        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        return self::$sms->sendSms( $phone, $message, $notification->getTypeId() );
    }

    /**
     * Send SMS notification to staff.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @param string $phone
     * @return bool
     */
    private static function _sendSmsToStaff( Entities\Notification $notification, NotificationCodes $codes, $phone )
    {
        // Message.
        $message = $codes->replace( $notification->get( 'message' ), 'text' );

        // Send SMS to staff.
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        $result = self::$sms->sendSms( $phone, $message, $notification->getTypeId() );

        // Send copy to administrators.
        if ( $notification->get( 'copy' ) ) {

            self::$sms->sendSms( get_option( 'bookly_sms_administrator_phone', '' ), $message, $notification->getTypeId() );
        }

        return $result;
    }

    /**
     * Get SMS notification for given recipient and appointment status.
     *
     * @param string $recipient
     * @param string $status
     * @param bool $is_recurring
     * @return Entities\Notification|bool
     */
    private static function _getSmsNotification( $recipient, $status, $is_recurring = false )
    {
        $postfix = $is_recurring ? '_recurring' : '';
        return self::_getNotification( "{$recipient}_{$status}{$postfix}_appointment", 'sms' );
    }

    /**
     * Get combined email notification for given appointment status.
     *
     * @param string $status
     * @return Entities\Notification|bool
     */
    private static function _getCombinedEmailNotification( $status )
    {
        return self::_getNotification( "client_{$status}_appointment_cart", 'email' );
    }

    /**
     * Get combined SMS notification for given appointment status.
     *
     * @param string $status
     * @return Entities\Notification|bool
     */
    private static function _getCombinedSmsNotification( $status )
    {
        return self::_getNotification( "client_{$status}_appointment_cart", 'sms' );
    }

    /**
     * Get notification object.
     *
     * @param string $type
     * @param string $gateway
     * @return Entities\Notification|bool
     */
    private static function _getNotification( $type, $gateway )
    {
        $notification = new Entities\Notification();
        if ( $notification->loadBy( array(
            'type'    => $type,
            'gateway' => $gateway,
            'active'  => 1
        ) ) ) {
            return $notification;
        }

        return false;
    }

}