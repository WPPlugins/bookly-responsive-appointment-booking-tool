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
            foreach ( $this->data['days'] as $id => $schedule_item_id ) {
                $staffScheduleItem = new AB_StaffScheduleItem();
                $staffScheduleItem->load( $id );
                $staffScheduleItem->set( 'schedule_item_id', $schedule_item_id );
                if ($this->data['start_time'][$schedule_item_id]) {
                    $staffScheduleItem->set( 'start_time', $this->data['start_time'][$schedule_item_id] );
                    $staffScheduleItem->set( 'end_time', $this->data['end_time'][$schedule_item_id] );
                } else {
                    $staffScheduleItem->set( 'start_time', null );
                    $staffScheduleItem->set( 'end_time', null );
                }
                $staffScheduleItem->save();
            }
        }
    }
}
