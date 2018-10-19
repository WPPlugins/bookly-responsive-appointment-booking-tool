<?php
namespace Bookly\Lib\Proxy;

use Bookly\Lib;
use BooklyLocations\Lib\Entities\Location;

/**
 * Class Locations
 * @package Bookly\Lib\Proxy
 *
 * @method static void           addBooklyMenuItem() Add 'Locations' to Bookly menu.
 * @method static Location|false findById( int $location_id ) Find location by id
 * @method static Location[]     findByStaffId( int $staff_id ) Find locations by staff id.
 * @method static int            prepareStaffLocationId( int $location_id, int $staff_id ) Prepare StaffService Location Id.
 * @method static bool           servicesPerLocationAllowed() Get allow-services-per-location option.
 */
abstract class Locations extends Lib\Base\Proxy
{

}