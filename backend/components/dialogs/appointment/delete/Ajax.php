<?php
namespace Bookly\Backend\Components\Dialogs\Appointment\Delete;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking as DataHolders;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Appointment\Delete
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'user' );
    }

    /**
     * Delete single appointment.
     */
    public static function deleteAppointment()
    {
        $appointment_id = self::parameter( 'appointment_id' );
        $reason         = self::parameter( 'reason' );

        if ( self::parameter( 'notify' ) ) {
            $ca_list = Lib\Entities\CustomerAppointment::query()
                ->where( 'appointment_id', $appointment_id )
                ->find();
            /** @var Lib\Entities\CustomerAppointment $ca */
            foreach ( $ca_list as $ca ) {
                switch ( $ca->getStatus() ) {
                    case Lib\Entities\CustomerAppointment::STATUS_PENDING:
                    case Lib\Entities\CustomerAppointment::STATUS_WAITLISTED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                        break;
                    case Lib\Entities\CustomerAppointment::STATUS_APPROVED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                        break;
                }
                Lib\Notifications\Sender::sendSingle(
                    DataHolders\Simple::create( $ca ),
                    null,
                    array( 'cancellation_reason' => $reason )
                );
            }
        }

        Lib\Entities\Appointment::find( $appointment_id )->delete();

        wp_send_json_success();
    }
}