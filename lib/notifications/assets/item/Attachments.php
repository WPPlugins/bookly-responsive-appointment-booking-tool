<?php
namespace Bookly\Lib\Notifications\Assets\Item;

use Bookly\Lib;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Notifications\Assets\Order;

/**
 * Class Attachments
 * @package Bookly\Lib\Notifications\Assets\Item
 *
 * @property Codes $codes
 */
class Attachments extends Order\Attachments
{
    /**
     * @inheritdoc
     */
    public function createFor( Notification $notification )
    {
        $result = array();

        if ( $notification->getAttachIcs() ) {
            if ( ! isset( $this->files['ics'] ) ) {
                // ICS.
                if ( $this->codes->getItem()->isSeries() && Lib\Config::recurringAppointmentsActive() ) {
                    $ics = Proxy\RecurringAppointments::createICS( $this->codes );
                } else {
                    $ics = new ICS( $this->codes );
                }
                $file = $ics->create();
                if ( $file ) {
                    $this->files['ics'] = $file;
                }
            }
            $result = isset( $this->files['ics'] ) ? array( $this->files['ics'] ) : array();
        }

        return array_merge( parent::createFor( $notification ), $result );
    }
}