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
 * @method static array          prepareLocationsForCombinedServices( array $locations, array $services ) Prepare Location Ids for combined services.
 * @method static int            prepareStaffLocationId( int $location_id, int $staff_id ) Prepare StaffService Location Id.
 * @method static int            prepareStaffScheduleLocationId( int $location_id, int $staff_id ) Prepare StaffService Location Id.
 * @method static Lib\Query      prepareStaffScheduleQuery( Lib\Query $query, int $location_id, int $staff_id ) Prepare Get StaffSchedule Query.
 * @method static array          prepareWorkingSchedule( array $working_schedule, array $staff_ids ) Prepare working schedule for Finder
 * @method static bool           servicesPerLocationAllowed() Get allow-services-per-location option.
 */
abstract class Locations extends Lib\Base\Proxy
{

}