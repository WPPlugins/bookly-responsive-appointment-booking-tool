<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class StaffScheduleItem
 * @package BooklyLite\Lib\Entities
 */
class StaffScheduleItem extends Lib\Base\Entity
{
    protected static $table = 'ab_staff_schedule_items';

    protected static $schema = array(
        'id'         => array( 'format' => '%d' ),
        'staff_id'   => array( 'format' => '%d', 'reference' => array( 'entity' => 'Staff' ) ),
        'day_index'  => array( 'format' => '%d' ),
        'start_time' => array( 'format' => '%s' ),
        'end_time'   => array( 'format' => '%s' ),
    );

    protected static $cache = array();

    const WORKING_START_TIME = 0;     // 00:00:00
    const WORKING_END_TIME   = 86400; // 24:00:00

    /**
     * Checks if
     *
     * @param $start_time
     * @param $end_time
     * @param $break_id
     * @return bool
     */
    public function isBreakIntervalAvailable( $start_time, $end_time, $break_id = 0 )
    {
        return ScheduleItemBreak::query()
            ->where( 'staff_schedule_item_id', $this->get( 'id' ) )
            ->whereNot( 'id', $break_id )
            ->whereRaw(
                'start_time > %s AND start_time < %s OR (end_time > %s AND end_time < %s) OR (start_time < %s AND end_time > %s) OR (start_time = %s AND end_time = %s)',
                array( $start_time, $end_time, $start_time, $end_time, $start_time, $end_time, $start_time, $end_time )
            )
            ->count() == 0;
    }

    /**
     * Get list of breaks
     *
     * @return array
     */
    public function getBreaksList()
    {
        return ScheduleItemBreak::query()
            ->where( 'staff_schedule_item_id', $this->get( 'id' ) )
            ->sortBy( 'start_time, end_time' )
            ->fetchArray();
    }

    public function save()
    {
        $list = $this->getBreaksList();
        foreach ( $list as $row ) {
            $break = new ScheduleItemBreak();
            $break->setFields( $row );
            if (
                $this->get( 'start_time' )     >= $break->get( 'start_time' )
                || $break->get( 'start_time' ) >= $this->get( 'end_time' )
                || $this->get( 'start_time' )  >= $break->get( 'end_time' )
                || $break->get( 'end_time' )   >= $this->get( 'end_time' )
            ) {
                $break->delete();
            }
        }

        parent::save();
    }

}
