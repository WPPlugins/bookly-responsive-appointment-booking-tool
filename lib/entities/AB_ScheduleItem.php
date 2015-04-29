<?php

  /**
   * Class AB_ScheduleItem
   */
  class AB_ScheduleItem extends AB_Entity {

      /**
       * Constructor.
       */
      public function __construct() {
          $this->table_name = 'ab_schedule_item';
          $this->schema = array(
              'id'    => array(),
              'name'  => array( 'format' => '%s' ),
          );
          parent::__construct();
      }

  }
