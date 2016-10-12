<?php
namespace BooklyLite\Lib;

/**
 * Class NotificationSender
 * @package BooklyLite\Lib
 */
abstract class NotificationSender
{
    /**
     * @var SMS
     */
    static $sms = null;
    static $sms_authorized = null;

    /**
     * Send instant notifications.
     *
     * @param Entities\CustomerAppointment $ca
     */
    public static function send( Entities\CustomerAppointment $ca )
    {
        list ( $codes, $appointment, $customer, $staff ) = self::_prepareData( $ca );

        $status = $ca->get( 'status' );
        $extra  = get_option( 'ab_email_notification_reply_to_customers' )
            ? array( 'reply-to' => array( 'email' => $customer->get( 'email' ), 'name' => $customer->get( 'name' ) ) )
            : array();

        foreach ( array( 'sms' ) as $gateway ) {
            $to_staff = new Entities\Notification();
            $to_staff->loadBy( array( 'type' => "staff_{$status}_appointment", 'gateway' => $gateway ) );
            if ( $to_staff->get( 'active' ) ) {
                // Send notification to staff member (and admins if necessary).
                self::_send( $to_staff, $codes, $staff->get( 'email' ), $staff->get( 'phone' ), $extra );
            }

            $to_client = new Entities\Notification();
            $to_client->loadBy( array( 'type' => "client_{$status}_appointment", 'gateway' => $gateway ) );
            if ( $to_client->get( 'active' ) ) {
                // Client time zone offset.
                if ( $ca->get( 'time_zone_offset' ) !== null ) {
                    $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
                }
                // Send notification to client.
                self::_send( $to_client, $codes, $customer->get( 'email' ), $customer->get( 'phone' ) );
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
            $status    = get_option( 'ab_settings_default_appointment_status' );
            $cart_info = array();
            $payments  = array();
            $customer  = null;
            $codes     = null;
            $total     = 0.0;
            $compound_tokens = array();
            $email_to_staff  = new Entities\Notification();
            $sms_to_staff    = new Entities\Notification();

            $email_to_staff->loadBy( array( 'type' => "staff_{$status}_appointment", 'gateway' => 'email' ) );
            $sms_to_staff->loadBy( array( 'type' => "staff_{$status}_appointment", 'gateway' => 'sms' ) );

            foreach ( $ca_list as $ca ) {
                if ( ! isset( $compound_tokens[ $ca->get( 'compound_token' ) ] ) ) {
                    if ( $ca->get( 'compound_token' ) ) {
                        $compound_tokens[ $ca->get( 'compound_token' ) ] = true;
                    }
                    list ( $codes, $appointment, $customer, $staff ) = self::_prepareData( $ca );
                    $extra = get_option( 'ab_email_notification_reply_to_customers' )
                        ? array( 'reply-to' => array( 'email' => $customer->get( 'email' ), 'name' => $customer->get( 'name' ) ) )
                        : array();

                    if ( $email_to_staff->get( 'active' ) ) {
                        // Send email to staff member (and admins if necessary).
                        self::_send( $email_to_staff, $codes, $staff->get( 'email' ), $staff->get( 'phone' ), $extra );
                    }
                    if ( $sms_to_staff->get( 'active' ) ) {
                        // Send SMS to staff member (and admins if necessary).
                        self::_send( $sms_to_staff, $codes, $staff->get( 'email' ), $staff->get( 'phone' ), $extra );
                    }

                    // Prepare data for {cart_info} || {cart_info_c}.
                    $cart_info[] = array(
                        'appointment_price' => ( $codes->get( 'service_price' ) + $codes->get( 'extras_total_price', 0 ) )  * $codes->get( 'number_of_persons' ),
                        'appointment_start' => $ca->get( 'time_zone_offset' ) !== null
                            ? Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) )
                            : $codes->get( 'appointment_start' ),
                        'cancel_url'   => admin_url( 'admin-ajax.php?action=ab_cancel_appointment&token=' . $codes->get( 'appointment_token' ) ),
                        'service_name' => $codes->get( 'service_name' ),
                        'staff_name'   => $codes->get( 'staff_name' ),
                        'extras'       => $ca->get( 'extras' ),
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
            // Send notification to client.
            foreach ( array( 'email', 'sms' ) as $gateway ) {
                $to_client = new Entities\Notification();
                $to_client->loadBy( array( 'type' => "client_{$status}_appointment_cart", 'gateway' => $gateway ) );
                if ( $to_client->get( 'active' ) ) {
                    self::_send( $to_client, $codes, $customer->get( 'email' ), $customer->get( 'phone' ) );
                }
            }
        } else { // Combined notifications disabled.
            foreach ( $ca_list as $ca ) {
                self::send( $ca );
            }
        }
    }

    /**
     * Send scheduled notification.
     *
     * @param Entities\Notification $notification
     * @param Entities\CustomerAppointment $ca
     * @return bool
     */
    public static function sendFromCron( Entities\Notification $notification, Entities\CustomerAppointment $ca )
    {
        list ( $codes, $appointment, $customer ) = self::_prepareData( $ca );

        // Client time zone offset.
        if ( $ca->get( 'time_zone_offset' ) !== null ) {
            $codes->set( 'appointment_start', Utils\DateTime::applyTimeZoneOffset( $appointment->get( 'start_date' ), $ca->get( 'time_zone_offset' ) ) );
        }
        // Send notification to client.
        $result = self::_send( $notification, $codes, $customer->get( 'email' ), $customer->get( 'phone' ), array(), $ca->get( 'locale' ) );

        return $result;
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
        foreach ( array( 'sms' ) as $gateway ) {
            $to_client = new Entities\Notification();
            $to_client->loadBy( array( 'type' => 'client_new_wp_user', 'gateway' => $gateway ) );

            if ( $to_client->get( 'active' ) ) {
                $codes = new NotificationCodes();
                $codes->set( 'client_email', $customer->get( 'email' ) );
                $codes->set( 'client_name',  $customer->get( 'name' ) );
                $codes->set( 'client_phone', $customer->get( 'phone' ) );
                $codes->set( 'new_password', $password );
                $codes->set( 'new_username', $username );
                $codes->set( 'site_address', site_url() );

                self::_send( $to_client, $codes, $customer->get( 'email' ), $customer->get( 'phone' ) );
            }
        }
    }

    /**
     * Prepare data for email.
     *
     * @param Entities\CustomerAppointment $ca
     * @return array [ NotificationCodes, Entities\Appointment, Entities\Customer, Entities\Staff ]
     */
    private static function _prepareData( Entities\CustomerAppointment $ca )
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
        $codes->set( 'appointment_end',   $appointment_end );
        $codes->set( 'appointment_start', $appointment_start );
        $codes->set( 'appointment_token', $ca->get( 'token' ) );
        $codes->set( 'booking_number' ,   $appointment->get( 'id' ) );
        $codes->set( 'category_name',     $service->getCategoryName() );
        $codes->set( 'client_email',      $customer->get( 'email' ) );
        $codes->set( 'client_name',       $customer->get( 'name' ) );
        $codes->set( 'client_phone',      $customer->get( 'phone' ) );
        $codes->set( 'custom_fields',     $ca->getFormattedCustomFields( 'text' ) );
        $codes->set( 'custom_fields_2c',  $ca->getFormattedCustomFields( 'html' ) );
        $codes->set( 'number_of_persons', $ca->get( 'number_of_persons' ) );
        if ( $ca->get( 'payment_id' ) ) {
            $codes->set( 'payment_type',  Entities\Payment::typeToString( Entities\Payment::find( $ca->get( 'payment_id' ) )->get( 'type' ) ) );
        }
        $codes->set( 'service_info',      $service->getInfo() );
        $codes->set( 'service_name',      $service->getTitle() );
        $codes->set( 'service_price',     $price );
        $codes->set( 'staff_email',       $staff->get( 'email' ) );
        $codes->set( 'staff_info',        $staff->getInfo() );
        $codes->set( 'staff_name',        $staff->getName() );
        $codes->set( 'staff_phone',       $staff->get( 'phone' ) );
        $codes->set( 'staff_photo',       $staff_photo ? $staff_photo[0] : '' );

        $codes->setStaffService( $staff_service );

        $codes = apply_filters( 'bookly_prepare_notification_codes', $codes, $ca );

        if ( $ca->get( 'payment_id' ) ) {
            $payment = Entities\Payment::query()->select( 'total, paid' )->where( 'id', $ca->get( 'payment_id' ) )->fetchRow();
            $codes->set( 'total_price', $payment['total'] );
            $codes->set( 'amount_paid', $payment['paid'] );
            $codes->set( 'amount_due',  $payment['total'] - $payment['paid'] );
        } else {
            // After filter bookly_modify_notification_codes can get extras_total_price
            $codes->set( 'total_price', ( $codes->get( 'service_price' ) + $codes->get( 'extras_total_price', 0 ) ) * $codes->get( 'number_of_persons' ) );
            $codes->set( 'amount_paid', '' );
            $codes->set( 'amount_due',  '' );
        }

        return array( $codes, $appointment, $customer, $staff );
    }

    /**
     * Send test notification emails.
     *
     * @param string $to_email
     * @param array  $sender
     * @param string $reply_to_customers
     * @param array  $notification_types
     * @param string $content_type
     */
    public static function sendTestEmailNotifications( $to_email, array $sender, $reply_to_customers, array $notification_types, $content_type )
    {

    }

    /**
     * Send email to $mail_to.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes     $codes
     * @param                       $mail_to
     * @param string                $phone
     * @param array                 $extra
     * @param null|string           $language_code
     * @param null|string           $content_type
     * @return bool
     */
    private static function _send( Entities\Notification $notification, NotificationCodes $codes, $mail_to, $phone = '', $extra = array(), $language_code = null, $content_type = null )
    {
        $result  = false;
        $message_template = Utils\Common::getTranslatedString( $notification->get( 'gateway' ) . '_' . $notification->get( 'type' ), $notification->get( 'message' ), $language_code );
        $content_type = $content_type ?: get_option( 'ab_email_content_type' );
        $message = $codes->replace( $message_template, $notification->get( 'gateway' ), $content_type );
        if ( $notification->get( 'gateway' ) == 'sms' ) {
            // Send sms.
            if ( self::$sms_authorized === null ) {
                self::$sms = new SMS();
                self::$sms_authorized = self::$sms->loadProfile();
            }
            if ( self::$sms_authorized ) {
                if ( $phone != '' ) {
                    $result = self::$sms->sendSms( $phone, $message, $notification->getTypeId() );
                }
                if ( $notification->get( 'copy' ) ) {
                    if ( ( $administrator_phone = get_option( 'ab_sms_administrator_phone', '' ) != '' ) ) {
                        self::$sms->sendSms( $administrator_phone, $message, $notification->getTypeId() );
                    }
                }
            }
        }

        return $result;
    }

}