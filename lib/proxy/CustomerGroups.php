<?php
namespace Bookly\Lib\Proxy;

use Bookly\Lib;

/**
 * Class CustomerGroups
 * @package Bookly\Lib\Proxy
 *
 * @method static void addBooklyMenuItem() Add 'Customer Groups' to Bookly menu.
 * @method static float prepareCartTotalPrice( float $total, Lib\UserBookingData $user_data ) Prepare total price depending on group discount.
 * @method static string prepareDefaultAppointmentStatus( string $status, int $group_id ) Get Default Appointment Status depending on group_id.
 */
abstract class CustomerGroups extends Lib\Base\Proxy
{

}