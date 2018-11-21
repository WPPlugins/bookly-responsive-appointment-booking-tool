<?php
namespace Bookly\Backend\Modules\Calendar\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Modules\Calendar\Proxy
 *
 * @method static array prepareAppointmentCodesData( array $codes, array $appointment_data, string $participants ) Prepare codes data for appointment description displayed in calendar.
 * @method static void  prepareAppointmentsQueryForFC( Lib\Query $query, int $staff_id, \DateTime $start_date, \DateTime $end_date ) Prepare appointments query for full calendar
 * @method static void  renderAddOnsComponents() Render components on calendar page.
 */
abstract class Shared extends Lib\Base\Proxy
{

}