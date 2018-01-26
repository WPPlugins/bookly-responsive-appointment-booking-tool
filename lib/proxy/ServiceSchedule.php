<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class ServiceSchedule
 * Invoke local methods from Service Schedule add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static array getSchedule( int $service_id ) Get schedule for service
 * @see \BooklyServiceSchedule\Lib\ProxyProviders\Local::getSchedule()
 */
abstract class ServiceSchedule extends Base\ProxyInvoker
{

}