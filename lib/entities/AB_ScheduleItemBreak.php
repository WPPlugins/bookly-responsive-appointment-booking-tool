<?php

/**
* Class AB_ScheduleItemBreak
*/
class AB_ScheduleItemBreak extends AB_Entity {

  /**
   * Constructor.
   */
  public function __construct() {
      $this->table_name = 'ab_schedule_item_break';
      $this->schema = array(
          'id'                     => array(),
          'staff_schedule_item_id' => array( 'format' => '%d' ),
          'start_time'             => array( 'format' => '%s' ),
          'end_time'               => array( 'format' => '%s' ),
      );
      parent::__construct();
  }

}
