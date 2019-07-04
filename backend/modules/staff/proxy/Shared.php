<?php
namespace Bookly\Backend\Modules\Staff\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Modules\Staff\Proxy
 *
 * @method static array  editStaff( array $data, Lib\Entities\Staff $staff ) Prepare edit staff form.
 * @method static void   enqueueStaffProfileScripts() Enqueue scripts for page Staff.
 * @method static void   enqueueStaffProfileStyles() Enqueue styles for page Staff.
 * @method static string getAffectedAppointmentsFilter( string $filter_url, int $staff_id ) Get link with filter for appointments page.
 * @method static void   preUpdateStaff( Lib\Entities\Staff $staff, array $params ) Do stuff before staff update.
 * @method static void   renderStaffForm( Lib\Entities\Staff $staff ) Render Staff form tab details.
 * @method static int    renderStaffPage( array $params ) Do stuff on staff page render.
 * @method static void   renderStaffService( int $staff_id, Lib\Entities\Service $service, array $services_data, array $attributes = array() ) Render controls for staff on Services tab.
 * @method static void   renderStaffServiceLabels() Render column header for controls on Services tab.
 * @method static void   renderStaffServiceTail( int $staff_id, Lib\Entities\Service $service, int $location_id, $attributes = array() ) Render controls for Staff on tab services.
 * @method static void   renderStaffTab( Lib\Entities\Staff $staff ) Render staff tab.
 * @method static void   updateStaff( Lib\Entities\Staff $staff, array $params ) Update staff settings in add-ons.
 * @method static void   updateStaffSchedule( array $_post ) Update staff schedule settings in add-ons.
 * @method static void   updateStaffServices( array $_post ) Update staff services settings in add-ons.
 */
abstract class Shared extends Lib\Base\Proxy
{

}