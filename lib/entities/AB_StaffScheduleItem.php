<?php

/**
* Class AB_StaffService
*/
class AB_StaffScheduleItem extends AB_Entity {

    const WORKING_START_TIME = '00:00:00';
    const WORKING_END_TIME   = '23:45:00';

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_staff_schedule_item';
        $this->schema = array(
            'id'               => array(),
            'staff_id'         => array( 'format' => '%d' ),
            'day_index'        => array( 'format' => '%d' ),
            'start_time'       => array( 'format' => '%s' ),
            'end_time'         => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    /**
     * Checks if
     *
     * @param $start_time
     * @param $end_time
     * @param $break_id
     *
     * @return bool
     */
    public function isBreakIntervalAvailable( $start_time, $end_time, $break_id = 0 ) {
        if ( ! $this->loaded ) {
            return false;
        }

        return !is_object($this->wpdb->get_row($this->wpdb->prepare(
            'SELECT * FROM `ab_schedule_item_break`
             WHERE staff_schedule_item_id = %d
             AND (
                 start_time > %s AND start_time < %s
                 OR (end_time > %s AND end_time < %s)
                 OR (start_time < %s AND end_time > %s)
                 OR (start_time = %s AND end_time = %s)
             )
             AND id <> %d',
            $this->get( 'id' ),
            $start_time,
            $end_time,
            $start_time,
            $end_time,
            $start_time,
            $end_time,
            $start_time,
            $end_time,
            $break_id
        )));
    }

    /**
     * Get list of breaks
     *
     * @return array|mixed
     */
    public function getBreaksList() {
        if ( ! $this->loaded ) {
            return array();
        }

        return $this->wpdb->get_results( $this->wpdb->prepare(
            'SELECT *
             FROM `ab_schedule_item_break`
             WHERE staff_schedule_item_id = %d
             ORDER BY start_time, end_time',
            $this->get( 'id' )
        ) );
    }

    public function save() {
        foreach ( $this->getBreaksList() as $row ) {
            $break = new AB_ScheduleItemBreak();
            $break->setData( $row );
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
