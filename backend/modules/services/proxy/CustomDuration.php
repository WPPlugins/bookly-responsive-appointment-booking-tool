<?php
namespace Bookly\Backend\Modules\Services\Proxy;

use Bookly\Lib;

/**
 * Class CustomerGroups
 * @package Bookly\Backend\Modules\Services\Proxy
 *
 * @method static array prepareServiceDurationOptions( array $options, array $service ) Add "Custom" option to service duration select.
 * @method static void  renderServiceDurationFields( array $service ) Render services duration(units) fields.
 * @method static void  renderServiceDurationHelp() Render services duration help tip.
 * @method static void  renderServicePriceLabel( $service_id ) Render service price label.
 */
abstract class CustomDuration extends Lib\Base\Proxy
{

}