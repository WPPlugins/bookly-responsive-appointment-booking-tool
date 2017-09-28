<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class ScheduleItemBreak
 * @package BooklyLite\Lib\Entities
 */
class ScheduleItemBreak extends Lib\Base\Entity
{
    protected static $table = 'ab_schedule_item_breaks';

    protected static $schema = array(
        'id'                     => array( 'format' => '%d' ),
        'staff_schedule_item_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'StaffScheduleItem' ) ),
        'start_time'             => array( 'format' => '%s' ),
        'end_time'               => array( 'format' => '%s' ),
    );

    protected static $cache = array();

    /**
     * Remove all breaks for certain staff member
     *
     * @param $staff_id
     */
    public function removeBreaksByStaffId( $staff_id )
    {
        $this->wpdb->get_results( $this->wpdb->prepare(
            'DELETE `break` FROM `' . self::getTableName() . '` AS `break`
            LEFT JOIN `' . StaffScheduleItem::getTableName() . '` AS `item` ON `item`.`id` = `break`.`staff_schedule_item_id`
            WHERE `item`.`staff_id` = %d',
            $staff_id
        ) );
    }

}
