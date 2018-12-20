<?php
namespace Bookly\Lib\Notifications\Booking;

use Bookly\Lib\DataHolders\Booking as DataHolders;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Notifications;
use Bookly\Lib\Notifications\NewBooking\Codes;
use Bookly\Lib\Notifications\NewBooking\ICS;
use Bookly\Lib\Proxy;

/**
 * Class Sender
 * @package Bookly\Lib\Notifications\Booking
 */
class Sender extends Notifications\Base\Sender
{
    /** @var DataHolders\Order */
    protected $order;
    /** @var Codes */
    protected $codes;

    /**
     * Create new instance.
     *
     * @param DataHolders\Order $order
     * @param Codes $codes
     * @return static
     */
    public static function create( DataHolders\Order $order, Codes $codes )
    {
        $sender = new static();
        $sender->order = $order;
        $sender->codes = $codes;

        return $sender;
    }

    /**
     * Send notifications to client.
     *
     * @param DataHolders\Item $item
     * @param string $lang
     */
    public function sendToClient( DataHolders\Item $item, $lang )
    {
        foreach ( $this->getNotificationsForClient() as $notification ) {
            switch ( $notification->getGateway() ) {
                case 'email':
                    $this->notifyClientByEmail( $notification, $item, $lang );
                    break;
                case 'sms':
                    $this->notifyClientBySms( $notification, $item, $lang );
                    break;
            }
        }
    }

    /**
     * Send notifications to staff and/or admins.
     *
     * @param DataHolders\Item $item
     * @param string $lang
     */
    public function sendToStaffAndAdmins( DataHolders\Item $item, $lang )
    {
        // Notify staff and admins.
        foreach ( $this->getNotificationsForStaffAndAdmins() as $notification ) {
            if ( ! $notification->getToAdmin() && $item->getStaff()->isArchived() ) {
                // No recipient.
                continue;
            }
            switch ( $notification->getGateway() ) {
                case 'email':
                    $this->notifyStaffAndAdminsByEmail( $notification, $item, $lang );
                    break;
                case 'sms':
                    $this->notifyStaffAndAdminBySms( $notification, $item, $lang );
                    break;
            }
        }
    }

    /**
     * Notify client by email.
     *
     * @param Notification $notification
     * @param DataHolders\Item $item
     * @param string $lang
     */
    protected function notifyClientByEmail( Notification $notification, DataHolders\Item $item, $lang )
    {
        if ( $this->order->getCustomer()->getEmail() == '' ) {
            // No recipient.
            return;
        }

        // Prepare codes for this item.
        $this->codes->prepareForItem( $item, $lang, true );

        // Attachments.
        $attachments = $this->createAttachments( $notification );

        // Send email to client.
        $this->sendEmailToClient( $this->order->getCustomer()->getEmail(), $notification, $this->codes, $attachments );

        // Clean up attachments.
        $this->clearAttachments( $attachments );
    }

    /**
     * Notify client by SMS.
     *
     * @param Notification $notification
     * @param DataHolders\Item $item
     * @param string $lang
     */
    protected function notifyClientBySms( Notification $notification, DataHolders\Item $item, $lang )
    {
        if ( $this->order->getCustomer()->getPhone() == '' ) {
            // No recipient.
            return;
        }

        // Prepare codes for this item.
        $this->codes->prepareForItem( $item, $lang, true );

        // Send SMS to client.
        $this->sendSmsToClient( $this->order->getCustomer()->getPhone(), $notification, $this->codes );
    }

    /**
     * Notify staff and/or administrators by email.
     *
     * @param Notification $notification
     * @param DataHolders\Item $item
     * @param string $lang
     */
    protected function notifyStaffAndAdminsByEmail( Notification $notification, DataHolders\Item $item, $lang )
    {
        if ( ! $notification->getToAdmin() && $item->getStaff()->getEmail() == '' ) {
            // No recipient.
            return;
        }

        // Prepare codes for this item.
        $this->codes->prepareForItem( $item, $lang, false );

        // Attachments.
        $attachments = $this->createAttachments( $notification );

        // Extra headers.
        $extra_headers = array();
        if ( get_option( 'bookly_email_reply_to_customers' ) ) {
            $customer      = $this->order->getCustomer();
            $extra_headers = array( 'reply-to' => array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() ) );
        }

        // Send email to staff.
        if ( $notification->getToStaff() ) {
            $this->sendEmailToStaff( $item->getStaff()->getEmail(), $notification, $this->codes, $attachments, $extra_headers );
        }

        // Send email to administrators.
        if ( $notification->getToAdmin() ) {
            $this->sendEmailToAdmins( $notification, $this->codes, $attachments, $extra_headers );
        }

        // Clean up attachments.
        $this->clearAttachments( $attachments );
    }

    /**
     * Notify staff and/or administrator by SMS.
     *
     * @param Notification $notification
     * @param DataHolders\Item $item
     * @param string $lang
     */
    protected function notifyStaffAndAdminBySms( Notification $notification, DataHolders\Item $item, $lang )
    {
        if ( ! $notification->getToAdmin() && $item->getStaff()->getPhone() == '' ) {
            // No recipients for this item.
            return;
        }

        // Prepare codes for this item.
        $this->codes->prepareForItem( $item, $lang, false );

        // Send SMS to staff.
        if ( $notification->getToStaff() ) {
            $this->sendSmsToStaff( $item->getStaff()->getPhone(), $notification, $this->codes );
        }

        // Send SMS to administrator.
        if ( $notification->getToAdmin() ) {
            $this->sendSmsToAdmin( $notification, $this->codes );
        }
    }

    /**
     * @inheritdoc
     */
    protected function createAttachments( Notification $notification )
    {
        $attachments = array();

        // ICS.
        if ( $notification->getAttachIcs() ) {
            $file = $this->createIcs( $this->codes );
            if ( $file ) {
                $attachments[] = $file;
            }
        }

        // Invoices.
        if ( $notification->getAttachInvoice() && $this->order->hasPayment() ) {
            $file = Proxy\Invoices::getInvoice( $this->order->getPayment() );
            if ( $file ) {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

    /**
     * Create ICS attachment.
     *
     * @param Codes $codes
     * @return bool|string
     */
    protected function createIcs( Codes $codes )
    {
        $ics = new ICS( $codes );

        return $ics->create();
    }

    /**
     * Remove attachment files.
     *
     * @param array $attachments
     */
    protected function clearAttachments( array $attachments )
    {
        foreach ( $attachments as $file ) {
            unlink( $file );
        }
    }
}