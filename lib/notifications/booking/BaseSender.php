<?php
namespace Bookly\Lib\Notifications\Booking;

use Bookly\Lib\Proxy;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\DataHolders\Booking\Item;
use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Notifications\Base;
use Bookly\Lib\Notifications\Assets\Item\Attachments;
use Bookly\Lib\Notifications\Assets\Item\Codes;
use Bookly\Lib\Notifications\WPML;

/**
 * Class BaseSender
 * @package Bookly\Lib\Notifications\Base
 */
abstract class BaseSender extends Base\Sender
{
    /**
     * Notify client.
     *
     * @param Notification[] $notifications
     * @param Item $item
     * @param Order $order
     * @param \Bookly\Lib\Notifications\Assets\Item\Codes $codes
     */
    protected static function notifyClient( array $notifications, Item $item, Order $order, Codes $codes )
    {
        if ( $item->getCA()->getLocale() ) {
            WPML::switchLang( $item->getCA()->getLocale() );
        } else {
            WPML::switchToDefaultLang();
        }

        $codes->prepareForItem( $item, 'client' );
        $attachments = new Attachments( $codes );

        foreach ( $notifications as $notification ) {
            if ( $notification->matchesItemForClient( $item ) ) {
                static::sendToClient( $order->getCustomer(), $notification, $codes, $attachments );
            }
        }

        $attachments->clear();

        WPML::restoreLang();
    }

    /**
     * Notify staff and/or administrators.
     *
     * @param Notification[] $notifications
     * @param Item $item
     * @param Order $order
     * @param \Bookly\Lib\Notifications\Assets\Item\Codes $codes
     */
    protected static function notifyStaffAndAdmins( array $notifications, Item $item, Order $order, Codes $codes )
    {
        WPML::switchToDefaultLang();

        if ( $item->isSeries() ) {
            Proxy\RecurringAppointments::sendSeries( $notifications, $item, $order, $codes );
        } else {
            // Reply to customer.
            $reply_to = null;
            if ( get_option( 'bookly_email_reply_to_customers' ) ) {
                $customer = $order->getCustomer();
                $reply_to = array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() );
            }

            // Handle collaborative and compound services.
            $sub_items = array();
            if ( $item->isCollaborative() || $item->isCompound() ) {
                $sub_items = $item->getItems();
            } else {
                $sub_items[] = $item;
            }

            foreach ( $sub_items as $sub_item ) {
                $codes->prepareForItem( $sub_item, 'staff' );
                $attachments = new Attachments( $codes );
                foreach ( $notifications as $notification ) {
                    if ( $notification->matchesItemForStaff( $sub_item, $item->getService() ) ) {
                        static::sendToStaff( $sub_item->getStaff(), $notification, $codes, $attachments, $reply_to );
                        static::sendToAdmins( $notification, $codes, $attachments, $reply_to );
                    }
                }
                $attachments->clear();
            }
        }

        WPML::restoreLang();
    }
}