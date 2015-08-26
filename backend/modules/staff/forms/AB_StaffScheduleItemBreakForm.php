<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffScheduleItemBreakForm
 */
class AB_StaffScheduleItemBreakForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::$entity_class = 'AB_ScheduleItemBreak';
        parent::__construct();
    }

    public function configure()
    {
        $this->setFields( array(
            'staff_schedule_item_id',
            'start_time',
            'end_time'
        ) );
    }

}