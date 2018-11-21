<?php
namespace Bookly\Backend\Modules\Staff\Proxy;

use Bookly\Lib;

/**
 * Class Locations
 * @package Bookly\Backend\Modules\Staff\Proxy
 *
 * @method static array getStaffSchedule( int $staff_id, int $location_id ) Prepare staff schedule.
 * @method static void  renderLocationSwitcher( int $staff_id, int $location_id, string $type ) Render location switcher.
 */
abstract class Locations extends Lib\Base\Proxy
{

}