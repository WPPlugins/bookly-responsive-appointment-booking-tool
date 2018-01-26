<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class SpecialHours
 * Invoke local methods from Special Hours add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static string preparePrice( string $price, int $staff_id, int $service_id, $start_time )
 * @see \BooklySpecialHours\Lib\ProxyProviders\Local::preparePrice()
 */
abstract class SpecialHours extends Base\ProxyInvoker
{

}