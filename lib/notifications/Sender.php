<?php
namespace Bookly\Lib\Notifications;

use Bookly\Lib\Config;
use Bookly\Lib\DataHolders\Booking as DataHolders;
use Bookly\Lib\DataHolders\Notification\Settings;
use Bookly\Lib\Entities;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Proxy;
use Bookly\Lib\SMS;
use Bookly\Lib\Utils;

/**
 * Class Sender
 * @package Bookly\Lib\Notifications
 */
abstract class Sender
{
    /** @var SMS */
    protected static $sms;

    /**
     * Send notifications from cart.
     *
     * @param DataHolders\Order $order
     */
    public static function sendFromCart( DataHolders\Order $order )
    {
        if ( Config::combinedNotificationsEnabled() && Config::proActive() ) {
            Proxy\Pro::sendCombined( $order );
        } else {
            foreach ( $order->getItems() as $item ) {
                switch ( $item->getType() ) {
                    case DataHolders\Item::TYPE_SIMPLE:
                    case DataHolders\Item::TYPE_COLLABORATIVE:
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
        $order                     = $order ?: DataHolders\Order::createFromItem( $item );
        $status                    = $item->getCA()->getStatus();
        $staff_email_notification  = $to_staff ? self::_getEmailNotification( 'staff', $status ) : false;
        $staff_sms_notification    = $to_staff ? self::_getSmsNotification( 'staff', $status ) : false;
        $client_email_notification = $to_customer ? self::_getEmailNotification( 'client', $status ) : false;
        $client_sms_notification   = $to_customer ? self::_getSmsNotification( 'client', $status ) : false;

        if ( $staff_email_notification || $staff_sms_notification || $client_email_notification || $client_sms_notification ) {
            $wp_locale = self::_getWpLocale();
            // Set wp locale for staff,
            // reason - it was changed on front-end.
            self::_switchLocale( $wp_locale );

            // Prepare codes.
            $codes = Codes::createForOrder( $order, $item );
            if ( isset ( $codes_data['cancellation_reason'] ) ) {
                $codes->cancellation_reason = $codes_data['cancellation_reason'];
            }

            // Send notifications to staff.
            if ( $item->isSimple() ) {
                if ( ! $item->getStaff()->isArchived() ) {
                    // Notify staff by email.
                    if ( $staff_email_notification ) {
                        self::_sendEmailToStaff( $staff_email_notification, $codes, $item->getStaff()->getEmail() );
                    }
                    // Notify staff by SMS.
                    if ( $staff_sms_notification ) {
                        self::_sendSmsToStaff( $staff_sms_notification, $codes, $item->getStaff()->getPhone() );
                    }
                }
            } else {
                // Compound and collaborative items.
                foreach ( $item->getItems() as $sub_item ) {
                    self::sendSingle( $sub_item, null, $codes_data, true, false );
                }
            }

            // Send notifications to client.
            if ( $client_email_notification || $client_sms_notification ) {
                // Client locale.
                $client_locale = $item->getCA()->getLocale() ?: $wp_locale;
                self::_switchLocale( $client_locale );
                $codes->refresh();

                // Client time zone offset.
                if ( $item->getCA()->getTimeZoneOffset() !== null ) {
                    $codes->appointment_start = $codes->appointment_start === null ? null : self::_applyTimeZone( $codes->appointment_start, $item->getCA() );
                    $codes->appointment_end   = $codes->appointment_start === null ? null : self::_applyTimeZone( $codes->appointment_end, $item->getCA() );
                }
                // Notify client by email.
                if ( $client_email_notification ) {
                    self::_sendEmailToClient( $client_email_notification, $codes, $order->getCustomer()->getEmail() );
                }
                // Notify client by SMS.
                if ( $client_sms_notification ) {
                    self::_sendSmsToClient( $client_sms_notification, $codes, $order->getCustomer()->getPhone() );
                }

                // Restore locale.
                self::_switchLocale( $wp_locale );
            }
        }

        $ca = $item->getCA();
        if ( $ca->isJustCreated() ) {
            self::sendOnCACreated( $ca );
        } elseif ( $ca->isStatusChanged() ) {
            self::sendOnCAStatusChanged( $ca );
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
        $wp_locale = self::_getWpLocale();

        $order = DataHolders\Order::createFromItem( $item );

        $client_locale = $item->getCA()->getLocale() ?: $wp_locale;
        self::_switchLocale( $client_locale );

        $codes = Codes::createForOrder( $order, $item );

        // Client time zone offset.
        if ( $item->getCA()->getTimeZoneOffset() !== null ) {
            $codes->appointment_start = $codes->appointment_start === null ? null : self::_applyTimeZone( $codes->appointment_start, $item->getCA() );
            $codes->appointment_end   = $codes->appointment_start === null ? null : self::_applyTimeZone( $codes->appointment_end, $item->getCA() );
        }

        // Send notification to client.
        $result = $notification->getGateway() == 'email'
            ? self::_sendEmailToClient( $notification, $codes, $order->getCustomer()->getEmail() )
            : self::_sendSmsToClient( $notification, $codes, $order->getCustomer()->getPhone() );

        // Restore locale.
        self::_switchLocale( $wp_locale );

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

        $codes = Codes::createForOrder( $order, $item );

        // Send notification to client.
        $result = $notification->getGateway() == 'email'
            ? self::_sendEmailToStaff( $notification, $codes, $item->getStaff()->getEmail() )
            : self::_sendSmsToStaff( $notification, $codes, $item->getStaff()->getPhone() );

        return $result;
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

        $codes = Codes::createForOrder( $order, $item );

        // Send notification to admin.
        $result = $notification->getGateway() == 'email'
            ? self::_sendEmailToAdmins( $notification, $codes )
            : self::_sendSmsToAdmin( $notification, $codes );

        return $result;
    }

    /**
     * Send reminder (email or SMS) to staff.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param string $phone
     * @return bool
     */
    public static function sendStaffAgendaFromCron( Entities\Notification $notification, Codes $codes, $email, $phone )
    {
        $result = false;
        if ( $notification->getToAdmin() ) {
            $result = $notification->getGateway() == 'email'
                ? self::_sendEmailToAdmins( $notification, $codes )
                : self::_sendSmsToAdmin( $notification, $codes );
        }

        $notification->setToAdmin( false );
        if ( $notification->getToStaff() ) {
            $result =  $notification->getGateway() == 'email'
                ? self::_sendEmailToStaff( $notification, $codes, $email, false )
                : self::_sendSmsToStaff( $notification, $codes, $phone );
        }

        return $result;
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
        $codes = new Codes();
        $codes->client_address    = $customer->getAddress();
        $codes->client_email      = $customer->getEmail();
        $codes->client_first_name = $customer->getFirstName();
        $codes->client_last_name  = $customer->getLastName();
        $codes->client_name       = $customer->getFullName();
        $codes->client_phone      = $customer->getPhone();

        $result = $notification->getGateway() == 'email'
            ? self::_sendEmailToClient( $notification, $codes, $customer->getEmail() )
            : self::_sendSmsToClient( $notification, $codes, $customer->getPhone() );

        return $result;
    }

    /**
     * Send test notification emails.
     *
     * @param string $to_mail
     * @param array  $notification_ids
     * @param string $send_as
     */
    public static function sendTestEmailNotifications( $to_mail, array $notification_ids, $send_as )
    {
        $codes = Codes::createForTest();
        $notification = new Entities\Notification();

        /**
         * @see \Bookly\Backend\Modules\Notifications\Ajax::executeTestEmailNotifications
         * overwrite this setting and headers
         * in filter bookly_email_headers
         */
        $reply_to_customer = false;

        foreach ( $notification_ids as $id ) {
            $notification->loadBy( array( 'id' => $id, 'gateway' => 'email' ) );

            switch ( $notification->getType() ) {
                case 'client_pending_appointment':
                case 'client_approved_appointment':
                case 'client_cancelled_appointment':
                case 'client_rejected_appointment':
                case 'client_waitlisted_appointment':
                case 'client_pending_appointment_cart':
                case 'client_approved_appointment_cart':
                case 'client_birthday_greeting':
                case 'client_follow_up':
                case 'client_new_wp_user':
                case 'client_reminder':
                case 'client_reminder_1st':
                case 'client_reminder_2nd':
                case 'client_reminder_3rd':
                case Entities\Notification::TYPE_CUSTOMER_BIRTHDAY:
                    self::_sendEmailToClient( $notification, $codes, $to_mail, $send_as );
                    break;
                case 'staff_pending_appointment':
                case 'staff_approved_appointment':
                case 'staff_cancelled_appointment':
                case 'staff_rejected_appointment':
                case 'staff_waitlisted_appointment':
                case 'staff_waiting_list':
                case 'staff_agenda':
                case Entities\Notification::TYPE_STAFF_DAY_AGENDA:
                    self::_sendEmailToStaff( $notification, $codes, $to_mail, $reply_to_customer, $send_as );
                    break;
                // Recurring Appointments email notifications.
                case 'client_pending_recurring_appointment':
                case 'client_approved_recurring_appointment':
                case 'client_cancelled_recurring_appointment':
                case 'client_rejected_recurring_appointment':
                case 'client_waitlisted_recurring_appointment':
                    self::_sendEmailToClient( $notification, $codes, $to_mail, $send_as );
                    break;
                case 'staff_pending_recurring_appointment':
                case 'staff_approved_recurring_appointment':
                case 'staff_cancelled_recurring_appointment':
                case 'staff_rejected_recurring_appointment':
                case 'staff_waitlisted_recurring_appointment':
                    self::_sendEmailToStaff( $notification, $codes, $to_mail, $reply_to_customer, $send_as );
                    break;
                // Packages email notifications.
                case 'client_package_purchased':
                case 'client_package_deleted':
                    self::_sendEmailToClient( $notification, $codes, $to_mail, $send_as );
                    break;
                case 'staff_package_purchased':
                case 'staff_package_deleted':
                    self::_sendEmailToStaff( $notification, $codes, $to_mail, $reply_to_customer, $send_as );
                    break;
                // Custom email notifications.
                case Entities\Notification::TYPE_APPOINTMENT_START_TIME:
                case Entities\Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
                case Entities\Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED:
                case Entities\Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
                    if ( $notification->getToStaff() ) {
                        self::_sendEmailToStaff( $notification, $codes, $to_mail, $reply_to_customer, $send_as );
                    }
                    if ( $notification->getToCustomer() ) {
                        self::_sendEmailToClient( $notification, $codes, $to_mail, $send_as );
                    }
                    if ( ! $notification->getToStaff() && $notification->getToAdmin() ) {
                        self::_sendEmailToAdmins( $notification, $codes );
                    }
                    break;
            }
        }
    }

    /**
     * Send notification on customer appointment created.
     *
     * @param CustomerAppointment $ca
     */
    public static function sendOnCACreated( CustomerAppointment $ca )
    {
        /** @var Notification[] $notifications */
        $notifications = Notification::query()->where( '`type`', Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED )->where( 'active', '1' )->find();
        foreach ( $notifications as $notification ) {
            if ( Config::proActive() || $notification->getGateway() == 'sms' ) {
                $settings = new Settings( $notification );
                if ( $settings->getInstant() && in_array( $settings->getStatus(), array( 'any', $ca->getStatus() ) ) ) {
                    $services = $settings->forServices();
                    if( $services == 'any'
                        || in_array( Entities\Appointment::find( $ca->getAppointmentId() )->getServiceId(), $services )
                    ) {
                        self::_send( $notification, array( $ca ) );
                    }
                }
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
        $notifications = Notification::query()->where( '`type`', Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED )->where( 'active', '1' )->find();
        foreach ( $notifications as $notification ) {
            if ( Config::proActive() || $notification->getGateway() == 'sms' ) {
                $settings = new Settings( $notification );
                if ( $settings->getInstant() && in_array( $settings->getStatus(), array( 'any', $ca->getStatus() ) ) ) {
                    $services = $settings->forServices();
                    if( $services == 'any'
                        || in_array( Entities\Appointment::find( $ca->getAppointmentId() )->getServiceId(), $services )
                    ) {
                        self::_send( $notification, array( $ca ) );
                    }
                }
            }
        }
    }

    /**
     * @param Notification          $notification
     * @param CustomerAppointment[] $ca_list
     */
    public static function sendCustomNotification( Notification $notification, array $ca_list )
    {
        self::_send( $notification, $ca_list );
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
     * Send email notification to client.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param string|null $send_as
     * @return bool
     */
    protected static function _sendEmailToClient( Entities\Notification $notification, Codes $codes, $email, $send_as = null )
    {
        if ( $email == '' ) {
            return false;
        }
        $subject = $codes->replace( $notification->getTranslatedSubject(), 'text' );

        $message = $notification->getTranslatedMessage();

        $send_as_html = $send_as === null ? Config::sendEmailAsHtml() : $send_as == 'html';
        if ( $send_as_html ) {
            $message = wpautop( $codes->replace( $message, 'html' ) );
        } else {
            $message = $codes->replace( $message, 'text' );
        }

        $attachments = array();

        // ICS.
        if ( $notification->getAttachIcs() ) {
            $file = static::_createIcs( $codes );
            if ( $file ) {
                $attachments[] = $file;
            }
        }
        // Invoices.
        if ( $notification->getAttachInvoice() && $codes->getOrder()->hasPayment() ) {
            $file = Proxy\Invoices::getInvoice( $codes->getOrder()->getPayment() );
            if ( $file ) {
                $attachments[] = $file;
            }
        }

        $result = wp_mail( $email, $subject, $message, Utils\Common::getEmailHeaders(), $attachments );

        // Clean up attachments.
        foreach ( $attachments as $file ) {
            unlink( $file );
        }

        return $result;
    }

    /**
     * Send email notification to staff.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @param string $email
     * @param bool $reply_to_customer
     * @param string|null $send_as
     * @return bool
     */
    protected static function _sendEmailToStaff(
        Entities\Notification $notification,
        Codes $codes,
        $email,
        $reply_to_customer = null,
        $send_as = null
    )
    {
        // Subject.
        $subject = $codes->replace( $notification->getSubject(), 'text' );

        // Message.
        $message = Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'staff', $notification->getGateway() );
        $send_as_html = $send_as === null ? Config::sendEmailAsHtml() : $send_as == 'html';
        if ( $send_as_html ) {
            $message = wpautop( $codes->replace( $message, 'html' ) );
        } else {
            $message = $codes->replace( $message, 'text' );
        }

        // Headers.
        $extra_headers = array();
        if ( $reply_to_customer === null ? get_option( 'bookly_email_reply_to_customers' ) : $reply_to_customer ) {
            // Codes can be without order.
            if ( $codes->getOrder() !== null ) {
                $customer      = $codes->getOrder()->getCustomer();
                $extra_headers = array( 'reply-to' => array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() ) );
            }
        }

        $headers = Utils\Common::getEmailHeaders( $extra_headers );

        $attachments = array();

        // ICS.
        if ( $notification->getAttachIcs() ) {
            $file = static::_createIcs( $codes );
            if ( $file ) {
                $attachments[] = $file;
            }
        }
        // Invoices.
        if ( $notification->getAttachInvoice() && $codes->getOrder()->hasPayment() ) {
            $file = Proxy\Invoices::getInvoice( $codes->getOrder()->getPayment() );
            if ( $file ) {
                $attachments[] = $file;
            }
        }

        // Send email to staff.
        $result = wp_mail( $email, $subject, $message, $headers, $attachments );

        // Clean up attachments.
        foreach ( $attachments as $file ) {
            unlink( $file );
        }

        // Send to administrators.
        if ( $notification->getToAdmin() ) {
            self::_sendEmailToAdmins( $notification, $codes );
        }

        return $result;
    }

    /**
     * Send email notification to admin.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     *
     * @return bool
     */
    protected static function _sendEmailToAdmins(
        Entities\Notification $notification,
        Codes $codes
    )
    {
        $admin_emails = Utils\Common::getAdminEmails();
        if ( ! empty( $admin_emails ) ) {
            // Subject.
            $subject = $codes->replace( $notification->getSubject(), 'text' );

            // Message.
            $message = Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'admin', $notification->getGateway() );
            if ( Config::sendEmailAsHtml() ) {
                $message = wpautop( $codes->replace( $message, 'html' ) );
            } else {
                $message = $codes->replace( $message, 'text' );
            }

            $attachments = array();

            // ICS.
            if ( $notification->getAttachIcs() ) {
                $file = static::_createIcs( $codes );
                if ( $file ) {
                    $attachments[] = $file;
                }
            }
            // Invoices.
            if ( $notification->getAttachInvoice() && $codes->getOrder()->hasPayment() ) {
                $file = Proxy\Invoices::getInvoice( $codes->getOrder()->getPayment() );
                if ( $file ) {
                    $attachments[] = $file;
                }
            }

            $result = wp_mail( $admin_emails, $subject, $message, Utils\Common::getEmailHeaders(), $attachments );

            // Clean up attachments.
            foreach ( $attachments as $file ) {
                unlink( $file );
            }

            return $result;
        }

        return true;
    }

    /**
     * Create ICS attachment.
     *
     * @param Codes $codes
     * @return bool|string
     */
    protected static function _createIcs( Codes $codes )
    {
        $ics = new ICS( $codes );

        return $ics->create();
    }

    /**
     * Send SMS notification to client.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @param string $phone
     * @return bool
     */
    protected static function _sendSmsToClient( Entities\Notification $notification, Codes $codes, $phone )
    {
        $message = $codes->replace( $notification->getTranslatedMessage(), 'text' );

        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        return self::$sms->sendSms( $phone, $message, $codes->getImpersonalMessage(), $notification->getTypeId() );
    }

    /**
     * Send SMS notification to staff.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @param string $phone
     * @return bool
     */
    protected static function _sendSmsToStaff( Entities\Notification $notification, Codes $codes, $phone )
    {
        // Message.
        $message = $codes->replace( Proxy\Pro::prepareNotificationMessage(
            $notification->getMessage(),
            'staff',
            $notification->getGateway()
        ), 'text' );

        // Send SMS to staff.
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        $result = self::$sms->sendSms( $phone, $message, $codes->getImpersonalMessage(), $notification->getTypeId() );

        // Send to administrators.
        if ( $notification->getToAdmin() ) {
            $message = $codes->replace( Proxy\Pro::prepareNotificationMessage(
                $notification->getMessage(),
                'admin',
                $notification->getGateway()
            ), 'text' );

            self::$sms->sendSms(
                get_option( 'bookly_sms_administrator_phone', '' ),
                $message,
                $codes->getImpersonalMessage(),
                $notification->getTypeId()
            );
        }

        return $result;
    }

    /**
     * Send SMS notification to admin.
     *
     * @param Entities\Notification $notification
     * @param Codes $codes
     * @return bool
     */
    protected static function _sendSmsToAdmin( Entities\Notification $notification, Codes $codes )
    {
        // Message.
        $message = $codes->replace( Proxy\Pro::prepareNotificationMessage(
            $notification->getMessage(),
            'admin',
            $notification->getGateway()
        ), 'text' );

        // Send to administrators.
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        return self::$sms->sendSms(
            get_option( 'bookly_sms_administrator_phone', '' ),
            $message,
            $codes->getImpersonalMessage(),
            $notification->getTypeId()
        );
    }

    /**
     * Get email notification for given recipient and status.
     *
     * @param string $recipient
     * @param string $status
     * @param bool $is_recurring
     * @return Entities\Notification|bool
     */
    protected static function _getEmailNotification( $recipient, $status, $is_recurring = false )
    {
        $postfix = $is_recurring ? '_recurring' : '';
        return self::_getNotification( "{$recipient}_{$status}{$postfix}_appointment", 'email' );
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
        if ( ( Config::proActive() || in_array( $type, Notification::$bookly_notifications[ $gateway ] ) ) && $notification->loadBy( array( 'type' => $type, 'gateway' => $gateway, 'active' => 1 ) ) ) {
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
            if ( $locale != $sitepress->get_current_language() ) {
                $sitepress->switch_lang( $locale );
                // WPML Multilingual CMS 3.9.2 // 2018-02
                // Does not overload the date translation
                $GLOBALS['wp_locale'] = new \WP_Locale();
            }
        }
    }

    /**
     * Get default locale.
     *
     * @return string
     */
    public static function _getWpLocale()
    {
        global $sitepress;

        return $sitepress instanceof \SitePress ? $sitepress->get_default_language() : null;
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
                        Entities\Service::find( $ca->getCompoundServiceId() )
                    );
                }
                $compounds[ $ca->getCompoundToken() ]->addItem( DataHolders\Simple::create( $ca ) );
            } else {
                $marked_as_sent = false;

                $simple = DataHolders\Simple::create( $ca );
                if ( $notification->getToCustomer() && Sender::sendFromCronToClient( $notification, $simple ) ) {
                    Sender::wasSent( $notification, $ca->getId() );
                    $marked_as_sent = true;
                }

                if ( $notification->getToStaff() &&
                    $simple->getStaff()->getVisibility() != 'archive' &&
                    ( $notification->getGateway() == 'email' && $simple->getStaff()->getEmail() != ''
                        || $notification->getGateway() == 'sms' && $simple->getStaff()->getPhone() != '' )
                ) {
                    if ( Sender::sendFromCronToStaff( $notification, $simple ) && ! $marked_as_sent ) {
                        Sender::wasSent( $notification, $ca->getId() );
                        $marked_as_sent = true;
                    }
                }
                if ( $notification->getToStaff() != 1 && $notification->getToAdmin() ) {
                    if ( Sender::sendFromCronToAdmin( $notification, $simple ) && ! $marked_as_sent ) {
                        Sender::wasSent( $notification, $ca->getId() );
                    }
                }
            }
        }

        foreach ( $compounds as $compound ) {
            if ( Sender::sendFromCronToClient( $notification, $compound ) ) {
                /** @var DataHolders\Simple $item */
                foreach ( $compound->getItems() as $item ) {
                    Sender::wasSent( $notification, $item->getCA()->getId() );
                }
            }
        }
    }
}