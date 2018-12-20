<?php
namespace Bookly\Lib\Notifications\Senders;

use Bookly\Lib\Config;
use Bookly\Lib\DataHolders\Booking as DataHolders;
use Bookly\Lib\DataHolders\Notification\Settings;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Notifications\Codes;

/**
 * Class StatusChanged
 * @package Bookly\Lib\Notifications\Senders
 */
class StatusChanged extends BaseSender
{
    /**
     * Create new instance.
     *
     * @param DataHolders\Item $item
     * @return static
     */
    public static function create( DataHolders\Item $item )
    {
        $sender = new static();
        $sender->order = DataHolders\Order::createFromItem( $item );
        $sender->codes = Codes\Codes::createForOrder( $sender->order );

        return $sender;
    }

    /**
     * @inheritdoc
     */
    protected function fetchNotifications()
    {
        $this->client_notifications = array();
        $this->staff_notifications  = array();
        /** @var Notification[] $notifications */
        $notifications = Notification::query()
            ->where( '`type`', Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED )
            ->where( 'active', '1' )
            ->find()
        ;
        foreach ( $notifications as $notification ) {
            if ( Config::proActive() || $notification->getGateway() == 'sms' ) {
                $settings = new Settings( $notification );
                if ( $settings->getInstant() && ! $settings->getRepeated() ) {
                    if ( $notification->getToCustomer() ) {
                        $this->client_notifications[] = $notification;
                    }
                    if ( $notification->getToStaff() || $notification->getToAdmin() ) {
                        $this->staff_notifications[] = $notification;
                    }
                }
            }
        }
    }
}