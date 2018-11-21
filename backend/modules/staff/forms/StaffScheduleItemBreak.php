<?php
namespace Bookly\Backend\Modules\Staff\Forms;

use Bookly\Lib;

/**
 * Class StaffScheduleItemBreak
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffScheduleItemBreak extends Lib\Base\Form
{
    protected static $entity_class = 'ScheduleItemBreak';

    public function configure()
    {
        $this->setFields( array(
            'staff_schedule_item_id',
            'start_time',
            'end_time'
        ) );
    }

}