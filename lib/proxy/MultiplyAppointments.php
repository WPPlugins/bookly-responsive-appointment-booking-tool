<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class MultiplyAppointments
 * Invoke local methods from Multiply Appointments add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static void renderAppearance() Render Multiply Appointments in Appearance
 * @see \BooklyMultiplyAppointments\Lib\ProxyProviders\Local::renderAppearance()
 */
abstract class MultiplyAppointments extends Base\ProxyInvoker
{

}