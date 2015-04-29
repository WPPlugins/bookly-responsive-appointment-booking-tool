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

  /**
   * Remove all breaks for certain staff member
   *
   * @param $staff_id
   *
   * @return bool
   */
  public function removeBreaksByStaffId($staff_id)
  {
      $this->wpdb->get_results( $this->wpdb->prepare(
        'DELETE break FROM ab_schedule_item_break AS break
         LEFT JOIN ab_staff_schedule_item   AS item ON item.id = break.staff_schedule_item_id
         WHERE item.staff_id = %d',
         $staff_id
      ));
  }
}
