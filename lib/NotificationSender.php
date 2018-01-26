<?php
namespace BooklyLite\Lib;

use BooklyLite\Lib\DataHolders\Booking as DataHolders;
use BooklyLite\Lib\DataHolders\Notification\Settings;
use BooklyLite\Lib\Entities\CustomerAppointment;
use BooklyLite\Lib\Entities\Notification;

/**
 * Class NotificationSender
 * @package BooklyLite\Lib
 */
abstract class NotificationSender
{
    /** @var SMS */
    private static $sms = null;

    /**
     * Send notifications from cart.
     *
     * @param DataHolders\Order $order
     */
    public static function sendFromCart( DataHolders\Order $order )
    {
        if ( Config::combinedNotificationsEnabled() ) {
            self::_sendCombined( $order );
        } else {
            foreach ( $order->getItems() as $item ) {
                switch ( $item->getType() ) {
                    case DataHolders\Item::TYPE_SIMPLE:
                    case DataHolders\Item::TYPE_COMPOUND:
                        self::sendSingle( $item, $order );
                        break;
                    case DataHolders\Item::TYPE_SERIES:
                        Proxy\RecurringAppointments::sendRecurring( $item, $order );
                        break;
                }
            }
        }
    }

    /**
     * Send notifications for single appointment.
     *
     * @param DataHolders\Item $item
     * @param DataHolders\Order $order
     * @param array $codes_data
     * @param bool $to_staff
     * @param bool $to_customer
     */
    public static function sendSingle(
        DataHolders\Item $item,
        DataHolders\Order $order = null,
        array $codes_data = array(),
        $to_staff = true,
        $to_customer = true
    )
    {
        global $sitepress;

        $wp_locale                 = $sitepress instanceof \SitePress ? $sitepress->get_default_language() : null;
        $order                     = $order ?: DataHolders\Order::createFromItem( $item );
        $status                    = $item->getCA()->getStatus();
        $staff_sms_notification    = $to_staff ? self::_getSmsNotification( 'staff', $status ) : false;
        $client_sms_notification   = $to_customer ? self::_getSmsNotification( 'client', $status ) : false;

        if ( $staff_sms_notification || $client_sms_notification ) {
            // Prepare codes.
            $codes = NotificationCodes::createForOrder( $order, $item );
            if ( isset ( $codes_data['cancellation_reason'] ) ) {
                $codes->cancellation_reason = $codes_data['cancellation_reason'];
            }

            // Notify staff by SMS.
            if ( $staff_sms_notification ) {
                self::_sendSmsToStaff( $staff_sms_notification, $codes, $item->getStaff()->getPhone() );
            }

            // Customer locale.
            $customer_locale = $item->getCA()->getLocale() ?: $wp_locale;
            if ( $customer_locale != $wp_locale ) {
                self::_switchLocale( $customer_locale );
                $codes->refresh();
            }

            // Client time zone offset.
            if ( $item->getCA()->getTimeZoneOffset() !== null ) {
                $codes->appointment_start = self::_applyTimeZone( $codes->appointment_start, $item->getCA() );
                $codes->appointment_end   = self::_applyTimeZone( $codes->appointment_end, $item->getCA() );
            }
            // Notify client by SMS.
            if ( $client_sms_notification ) {
                self::_sendSmsToClient( $client_sms_notification, $codes, $order->getCustomer()->getPhone() );
            }

            if ( $customer_locale != $wp_locale ) {
                self::_switchLocale( $wp_locale );
            }
        }
    }

    /**
     * Send combined notifications.
     *
     * @param DataHolders\Order $order
     */
    protected static function _sendCombined( DataHolders\Order $order )
    {
        $status    = get_option( 'bookly_gen_default_appointment_status' );
        $cart_info = array();
        $total     = 0.0;

        foreach ( $order->getItems() as $item ) {
            $sub_items = array();

            // Send notification to staff.
            switch ( $item->getType() ) {
                case DataHolders\Item::TYPE_SIMPLE:
                case DataHolders\Item::TYPE_COMPOUND:
                    self::sendSingle( $item, $order, array(),true, false );
                    $sub_items[] = $item;
                    break;
                case DataHolders\Item::TYPE_SERIES:
                    /** @var DataHolders\Series $item */
                    Proxy\RecurringAppointments::sendRecurring( $item, $order, array(), true, false );
                    $sub_items = $item->getItems();
                    if ( get_option( 'bookly_recurring_appointments_payment' ) == 'first' ) {
                        array_splice( $sub_items, 1 );
                    }
                    break;
            }

            foreach ( $sub_items as $sub_item ) {
                // Sub-item price.
                $price = $sub_item->getTotalPrice();

                // Prepare data for {cart_info} || {cart_info_c}.
                $cart_info[] = array(
                    'appointment_price' => $price,
                    'appointment_start' => self::_applyTimeZone( $sub_item->getAppointment()->getStartDate(), $sub_item->getCA() ),
                    'cancel_url'        => admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $sub_item->getCA()->getToken() ),
                    'service_name'      => $sub_item->getService()->getTranslatedTitle(),
                    'staff_name'        => $sub_item->getStaff()->getTranslatedName(),
                    'extras'            => (array) Proxy\ServiceExtras::getInfo( json_decode( $sub_item->getCA()->getExtras(), true ), true ),
                    'appointment_start_info' => $sub_item->getService()->getDuration() < DAY_IN_SECONDS ? null : $sub_item->getService()->getStartTimeInfo(),
                );

                // Total price.
                $total += $price;
            }
        }

        // Prepare codes.
        $items = $order->getItems();
        $codes = NotificationCodes::createForOrder( $order, $items[0] );
        $codes->cart_info = $cart_info;
        if ( ! $order->hasPayment() ) {
            $codes->total_price = $total;
        }

        if ( $to_client = self::_getCombinedSmsNotification( $status ) ) {
            self::_sendSmsToClient( $to_client, $codes, $order->getCustomer()->getPhone() );
        }
    }

    /**
     * Send reminder (email or SMS) to client.
     *
     * @param Entities\Notification $notification
     * @param DataHolders\Item $item
     * @return bool
     */
    public static function sendFromCronToClient( Entities\Notification $notification, DataHolders\Item $item )
    {
        global $sitepress;

        $wp_locale = $sitepress instanceof \SitePress ? $sitepress->get_default_language() : null;

        $order = DataHolders\Order::createFromItem( $item );

        $customer_locale = $item->getCA()->getLocale() ?: $wp_locale;
        if ( $customer_locale != $wp_locale ) {
            self::_switchLocale( $customer_locale );
        }

        $codes = NotificationCodes::createForOrder( $order, $item );

        // Client time zone offset.
        if ( $item->getCA()->getTimeZoneOffset() !== null ) {
            $codes->appointment_start = self::_applyTimeZone( $codes->appointment_start, $item->getCA() );
            $codes->appointment_end   = self::_applyTimeZone( $codes->appointment_end, $item->getCA() );
        }

        // Send notification to client.
        $result = self::_sendSmsToClient( $notification, $codes, $order->getCustomer()->getPhone() );

        if ( $customer_locale != $wp_locale ) {
            self::_switchLocale( $wp_locale );
        }

        return $result;
    }

    /**
     * Send notification to Staff.
     *
     * @param Entities\Notification $notification
     * @param DataHolders\Item $item
     * @return bool
     */
    public static function sendFromCronToStaff( Entities\Notification $notification, DataHolders\Item $item )
    {
        $order = DataHolders\Order::createFromItem( $item );

        $codes = NotificationCodes::createForOrder( $order, $item );

        // Send notification to client.
        return self::_sendSmsToStaff( $notification, $codes, $item->getStaff()->getPhone() );
    }

    /**
     * Send notification to administrators.
     *
     * @param Entities\Notification $notification
     * @param DataHolders\Item $item
     * @return bool
     */
    public static function sendFromCronToAdmin( Entities\Notification $notification, DataHolders\Item $item )
    {
        $order = DataHolders\Order::createFromItem( $item );

        $codes = NotificationCodes::createForOrder( $order, $item );

        // Send notification to client.
        return self::_sendSmsToAdmin( $notification, $codes );;
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
    public static function sendFromCronToStaffAgenda( Entities\Notification $notification, NotificationCodes $codes, $email, $phone )
    {
        return self::_sendSmsToStaff( $notification, $codes, $phone );
    }

    /**
     * Send birthday greeting to client.
     *
     * @param Entities\Notification $notification
     * @param Entities\Customer $customer
     * @return bool
     */
    public static function sendFromCronBirthdayGreeting( Entities\Notification $notification, Entities\Customer $customer )
    {
        $codes = new NotificationCodes();
        $codes->client_email      = $customer->getEmail();
        $codes->client_name       = $customer->getFullName();
        $codes->client_first_name = $customer->getFirstName();
        $codes->client_last_name  = $customer->getLastName();
        $codes->client_phone      = $customer->getPhone();

        $result = self::_sendSmsToClient( $notification, $codes, $customer->getPhone() );

        if ( $notification->getToAdmin() ) {
            self::_sendSmsToAdmin( $notification, $codes );
        }

        return $result;
    }

    /**
     * Send email/sms with username and password for newly created WP user.
     *
     * @param Entities\Customer $customer
     * @param $username
     * @param $password
     */
    public static function sendNewUserCredentials( Entities\Customer $customer, $username, $password )
    {
        $codes = new NotificationCodes();
        $codes->client_email       = $customer->getEmail();
        $codes->client_name        = $customer->getFullName();
        $codes->client_first_name  = $customer->getFirstName();
        $codes->client_last_name   = $customer->getLastName();
        $codes->client_phone       = $customer->getPhone();
        $codes->new_password       = $password;
        $codes->new_username       = $username;
        $codes->site_address       = site_url();

        $to_client = new Entities\Notification();
        if ( $to_client->loadBy( array( 'type' => 'client_new_wp_user', 'gateway' => 'sms', 'active' => 1 ) ) ) {
            self::_sendSmsToClient( $to_client, $codes, $customer->getPhone() );
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

    /**
     * Send notification on customer appointment created.
     *
     * @param CustomerAppointment $ca
     */
    public static function sendOnCACreated( CustomerAppointment $ca )
    {
        /** @var Notification[] $notifications */
        $notifications = Notification::query()->where( '`type`', Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED )->find();
        foreach ( $notifications as $notification ) {
            $settings = new Settings( $notification );
            if ( $settings->getInstant() &&
                in_array( $settings->getStatus(), array( 'any', $ca->getStatus() ) )
            ) {
                NotificationSender::_send( $notification, array( $ca ) );
            }
        }
    }

    /**
     * Send notification on customer appointment status changed.
     *
     * @param CustomerAppointment $ca
     */
    public static function sendOnCAStatusChanged( CustomerAppointment $ca )
    {
        /** @var Notification[] $notifications */
        $notifications = Notification::query()->where( '`type`', Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED )->find();
        foreach ( $notifications as $notification ) {
            $settings = new Settings( $notification );
            if ( $settings->getInstant() &&
                in_array( $settings->getStatus(), array( 'any', $ca->getStatus() ) )
            ) {
                NotificationSender::_send( $notification, array( $ca ) );
            }
        }
    }

    /**
     * @param Notification          $notification
     * @param CustomerAppointment[] $ca_list
     */
    public static function sendCustomNotification( Notification $notification, array $ca_list )
    {
        NotificationSender::_send( $notification, $ca_list );
    }

    /**
     * Mark sent notification.
     *
     * @param Entities\Notification $notification
     * @param int                   $ref_id
     */
    public static function wasSent( Entities\Notification $notification, $ref_id )
    {
        $sent_notification = new Entities\SentNotification();
        $sent_notification
            ->setRefId( $ref_id )
            ->setNotificationId( $notification->getId() )
            ->setCreated( current_time( 'mysql' ) )
            ->save();
    }

    /******************************************************************************************************************
     * Protected methods                                                                                                *
     ******************************************************************************************************************/

    /**
     * Send SMS notification to client.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @param string $phone
     * @return bool
     */
    protected static function _sendSmsToClient( Entities\Notification $notification, NotificationCodes $codes, $phone )
    {
        $message = $codes->replace( Utils\Common::getTranslatedString(
            'sms_' . $notification->getType(),
            $notification->getMessage()
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
    protected static function _sendSmsToStaff( Entities\Notification $notification, NotificationCodes $codes, $phone )
    {
        // Message.
        $message = $codes->replace( $notification->getMessage(), 'text' );

        // Send SMS to staff.
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        $result = self::$sms->sendSms( $phone, $message, $notification->getTypeId() );

        // Send to administrators.
        if ( $notification->getToAdmin() ) {
            self::$sms->sendSms( get_option( 'bookly_sms_administrator_phone', '' ), $message, $notification->getTypeId() );
        }

        return $result;
    }

    /**
     * Send SMS notification to admin.
     *
     * @param Entities\Notification $notification
     * @param NotificationCodes $codes
     * @return bool
     */
    protected static function _sendSmsToAdmin( Entities\Notification $notification, NotificationCodes $codes )
    {
        // Message.
        $message = $codes->replace( $notification->getMessage(), 'text' );

        // Send SMS to staff.
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        return self::$sms->sendSms( get_option( 'bookly_sms_administrator_phone', '' ), $message, $notification->getTypeId() );
    }

    /**
     * Get SMS notification for given recipient and appointment status.
     *
     * @param string $recipient
     * @param string $status
     * @param bool $is_recurring
     * @return Entities\Notification|bool
     */
    protected static function _getSmsNotification( $recipient, $status, $is_recurring = false )
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
    protected static function _getCombinedEmailNotification( $status )
    {
        return self::_getNotification( "client_{$status}_appointment_cart", 'email' );
    }

    /**
     * Get combined SMS notification for given appointment status.
     *
     * @param string $status
     * @return Entities\Notification|bool
     */
    protected static function _getCombinedSmsNotification( $status )
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
    protected static function _getNotification( $type, $gateway )
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

    /**
     * Switch WordPress and WPML locale
     *
     * @param $locale
     */
    protected static function _switchLocale( $locale )
    {
        global $sitepress;

        if ( $sitepress instanceof \SitePress ) {
            $languages   = apply_filters( 'wpml_active_languages', 'skip_missing=0' );
            $locale_code = isset( $languages[ $locale ]['default_locale'] ) ? $languages[ $locale ]['default_locale'] : $locale;
            switch_to_locale( $locale_code );

            $sitepress->switch_lang( $locale );
        }
    }

    /**
     * Apply client time zone to given datetime string in WP time zone.
     *
     * @param string $datetime
     * @param Entities\CustomerAppointment $ca
     * @return false|string
     */
    protected static function _applyTimeZone( $datetime, Entities\CustomerAppointment $ca )
    {
        $time_zone        = $ca->getTimeZone();
        $time_zone_offset = $ca->getTimeZoneOffset();

        if ( $time_zone !== null ) {
            $datetime = date_create( $datetime . ' ' . Config::getWPTimeZone() );
            return date_format( date_timestamp_set( date_create( $time_zone ), $datetime->getTimestamp() ), 'Y-m-d H:i:s' );
        } elseif ( $time_zone_offset !== null ) {
            return Utils\DateTime::applyTimeZoneOffset( $datetime, $time_zone_offset );
        }

        return $datetime;
    }

    /**
     * @param Notification          $notification
     * @param CustomerAppointment[] $ca_list
     */
    private static function _send( Notification $notification, array $ca_list )
    {
        $compounds = array();
        foreach ( $ca_list as $ca ) {
            if ( $ca->getCompoundToken() ) {
                if ( ! isset ( $compounds[ $ca->getCompoundToken() ] ) ) {
                    $compounds[ $ca->getCompoundToken() ] = DataHolders\Compound::create(
                        Entities\Service::find( $ca->getCompoundToken() )
                    );
                }
                $compounds[ $ca->getCompoundToken() ]->addItem( DataHolders\Simple::create( $ca ) );
            } else {
                $marked_as_sent = false;

                $simple = DataHolders\Simple::create( $ca );
                if ( $notification->getToCustomer() && NotificationSender::sendFromCronToClient( $notification, $simple ) ) {
                    NotificationSender::wasSent( $notification, $ca->getId() );
                    $marked_as_sent = true;
                }

                if ( $notification->getToStaff() &&
                    ( $notification->getGateway() == 'sms' && $simple->getStaff()->getPhone() != '' )
                ) {
                    if ( NotificationSender::sendFromCronToStaff( $notification, $simple ) && ! $marked_as_sent ) {
                        NotificationSender::wasSent( $notification, $ca->getId() );
                        $marked_as_sent = true;
                    }
                }
                if ( $notification->getToStaff() != 1 && $notification->getToAdmin() ) {
                    if ( NotificationSender::sendFromCronToAdmin( $notification, $simple ) && ! $marked_as_sent ) {
                        NotificationSender::wasSent( $notification, $ca->getId() );
                    }
                }
            }
        }

        foreach ( $compounds as $compound ) {
            if ( NotificationSender::sendFromCronToClient( $notification, $compound ) ) {
                /** @var DataHolders\Simple $item */
                foreach ( $compound->getItems() as $item ) {
                    NotificationSender::wasSent( $notification, $item->getCA()->getId() );
                }
            }
        }
    }
}