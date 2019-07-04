<?php
namespace Bookly\Backend\Modules\Staff\Proxy;

use Bookly\Lib;

/**
 * Class Pro
 * @package Bookly\Backend\Modules\Staff\Proxy
 *
 * @method static array getCategoriesList() Get categories list.
 * @method static void  renderGoogleCalendarSettings( array $tpl_data ) Render Google Calendar settings.
 * @method static void  renderStaffDetails( Lib\Entities\Staff $staff ) Render staff details form.
 * @method static void  updateCategoriesPositions( array $categories ) Update categories positions.
 */
abstract class Pro extends Lib\Base\Proxy
{

}