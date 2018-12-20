<?php
namespace Bookly\Lib\Notifications\Base;

use Bookly\Lib\Config;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Proxy;
use Bookly\Lib\SMS;
use Bookly\Lib\Utils;

/**
 * Class Sender
 * @package Bookly\Lib\Notifications\Base
 */
abstract class Sender
{
    /** @var SMS */
    protected static $sms;

    /**
     * Protected constructor.
     */
    protected function __construct()
    {
        // Use static methods for creating new objects.
    }

    /**
     * Send email to administrators.
     *
     * @param Notification $notification
     * @param Codes $codes
     * @param array $attachments
     * @param array $extra_headers
     * @return bool
     */
    protected function sendEmailToAdmins( Notification $notification, Codes $codes, $attachments = array(), $extra_headers = array() )
    {
        $admin_emails = Utils\Common::getAdminEmails();
        if ( empty ( $admin_emails ) ) {
            return false;
        }

        return $this->_sendEmail(
            $admin_emails,
            $notification->getSubject(),
            Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'admin', 'email' ),
            $codes,
            $attachments,
            $extra_headers
        );
    }

    /**
     * Send email to client.
     *
     * @param string $email
     * @param Notification $notification
     * @param Codes $codes
     * @param array $attachments
     * @param array $extra_headers
     * @return bool
     */
    protected function sendEmailToClient( $email, Notification $notification, Codes $codes, $attachments = array(), $extra_headers = array() )
    {
        if ( $email == '' ) {
            return false;
        }

        return $this->_sendEmail(
            $email,
            $notification->getTranslatedSubject(),
            $notification->getTranslatedMessage(),
            $codes,
            $attachments,
            $extra_headers
        );
    }

    /**
     * Send email to staff.
     *
     * @param string $email
     * @param Notification $notification
     * @param Codes $codes
     * @param array $attachments
     * @param array $extra_headers
     * @return bool
     */
    protected function sendEmailToStaff( $email, Notification $notification, Codes $codes, $attachments = array(), $extra_headers = array() )
    {
        if ( $email == '' ) {
            return false;
        }

        return $this->_sendEmail(
            $email,
            $notification->getSubject(),
            Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'staff', 'email' ),
            $codes,
            $attachments,
            $extra_headers
        );
    }

    /**
     * Send SMS to admin.
     *
     * @param Notification $notification
     * @param Codes $codes
     * @return bool
     */
    protected function sendSmsToAdmin( Notification $notification, Codes $codes )
    {
        $phone = get_option( 'bookly_sms_administrator_phone', '' );
        if ( $phone == '' ) {
            return false;
        }

        return $this->_sendSms(
            $phone,
            Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'admin', 'sms' ),
            $codes,
            $notification->getTypeId()
        );
    }

    /**
     * Send SMS to client.
     *
     * @param string $phone
     * @param Notification $notification
     * @param Codes $codes
     * @return bool
     */
    protected function sendSmsToClient( $phone, Notification $notification, Codes $codes )
    {
        if ( $phone == '' ) {
            return false;
        }

        return $this->_sendSms(
            $phone,
            $notification->getTranslatedMessage(),
            $codes,
            $notification->getTypeId()
        );
    }

    /**
     * Send SMS to staff.
     *
     * @param string $phone
     * @param Notification $notification
     * @param Codes $codes
     * @return bool
     */
    protected function sendSmsToStaff( $phone, Notification $notification, Codes $codes )
    {
        if ( $phone == '' ) {
            return false;
        }

        return $this->_sendSms(
            $phone,
            Proxy\Pro::prepareNotificationMessage( $notification->getMessage(), 'staff', 'sms' ),
            $codes,
            $notification->getTypeId()
        );
    }

    /**
     * Send email.
     *
     * @param string|array $email
     * @param string $subject
     * @param string $message
     * @param Codes $codes
     * @param array $attachments
     * @param array $extra_headers
     * @return bool
     */
    private function _sendEmail( $email, $subject, $message, Codes $codes, $attachments = array(), $extra_headers = array() )
    {
        // Subject.
        $subject = $codes->replace( $subject, 'text' );

        // Message.
        if ( Config::sendEmailAsHtml() ) {
            $message = wpautop( $codes->replace( $message, 'html' ) );
        } else {
            $message = $codes->replace( $message, 'text' );
        }

        // Headers.
        $headers = Utils\Common::getEmailHeaders( $extra_headers );

        // Do send.
        return wp_mail( $email, $subject, $message, $headers, $attachments );
    }

    /**
     * Send SMS.
     *
     * @param string $phone
     * @param string $message
     * @param Codes $codes
     * @param int $type_id
     * @return bool
     */
    private function _sendSms( $phone, $message, Codes $codes, $type_id )
    {
        if ( self::$sms === null ) {
            self::$sms = new SMS();
        }

        // Message.
        $message = $codes->replaceForSms( $message );

        // Do send.
        return self::$sms->sendSms( $phone, $message['personal'], $message['impersonal'], $type_id );
    }
}