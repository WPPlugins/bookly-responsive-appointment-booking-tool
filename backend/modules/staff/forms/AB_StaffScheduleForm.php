<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffScheduleForm
 */
class AB_StaffScheduleForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_StaffScheduleItem';
        parent::__construct();
    }

    /**
     * @var wpdb $wpdb
     */
    private $wpdb;

    public function configure() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->setFields( array( 'days', 'staff_id', 'start_time', 'end_time' ) );
    }

    public function save() {
        if ( isset($this->data['days']) ) {
            foreach ( $this->data['days'] as $id => $day_index ) {
                $staffScheduleItem = new AB_StaffScheduleItem();
                $staffScheduleItem->load( $id );
                $staffScheduleItem->set( 'day_index', $day_index );
                if ($this->data['start_time'][$day_index]) {
                    $staffScheduleItem->set( 'start_time', $this->data['start_time'][$day_index] );
                    $staffScheduleItem->set( 'end_time', $this->data['end_time'][$day_index] );
                } else {
                    $staffScheduleItem->set( 'start_time', null );
                    $staffScheduleItem->set( 'end_time', null );
                }
                $staffScheduleItem->save();
            }
        }
    }
}
