<?php
namespace Bookly\Backend\Modules\Staff\Forms;

use Bookly\Lib;

/**
 * Class StaffSchedule
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffSchedule extends Lib\Base\Form
{
    protected static $entity_class = 'StaffScheduleItem';

    public function configure()
    {
        $this->setFields( array( 'days', 'staff_id', 'start_time', 'end_time', 'location_id' ) );
    }

    public function save()
    {
        if ( isset( $this->data['days'] ) ) {
            foreach ( $this->data['days'] as $id => $day_index ) {
                $res_schedule = new Lib\Entities\StaffScheduleItem();
                $res_schedule->load( $id );
                $res_schedule->setDayIndex( $day_index );
                if ( $this->data['start_time'][ $day_index ] ) {
                    $res_schedule
                        ->setStartTime( $this->data['start_time'][ $day_index ] )
                        ->setEndTime( $this->data['end_time'][ $day_index ] );
                } else {
                    $res_schedule
                        ->setStartTime( null )
                        ->setEndTime( null );
                }
                $res_schedule->save();
            }
        }
    }

}
