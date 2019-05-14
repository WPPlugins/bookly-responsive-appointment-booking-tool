<?php
namespace Bookly\Backend\Modules\Services\Proxy;

use Bookly\Lib;

/**
 * Class ServiceSchedule
 * @package Bookly\Backend\Modules\Services\Proxy
 *
 * @method static string getTabHtml( int $service_id ) Render service schedule html.
 * @method static void   renderTab() Render service schedule tab.
 */
abstract class ServiceSchedule extends Lib\Base\Proxy
{

}