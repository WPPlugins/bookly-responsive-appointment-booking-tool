<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class CompoundServices
 * Invoke local methods from Compound Services add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static void cancelAppointment( \BooklyLite\Lib\Entities\CustomerAppointment $customer_appointment ) Cancel compound appointment
 * @see \BooklyCompoundServices\Lib\ProxyProviders\Local::cancelAppointment()
 *
 * @method static void renderSubServices( array $service, array $service_collection ) Render sub services for compound
 * @see \BooklyCompoundServices\Lib\ProxyProviders\Local::renderSubServices()
 */
abstract class CompoundServices extends Base\ProxyInvoker
{

}