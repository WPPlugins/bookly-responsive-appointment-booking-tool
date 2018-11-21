<?php
namespace Bookly\Backend\Modules\Appearance\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Modules\Appearance\Proxy
 *
 * @method static array prepareOptions( array $options_to_save, array $options ) Alter array of options to be saved in Bookly Appearance.
 * @method static void  renderPaymentGatewaySelector() Render gateway selector.
 * @method static int   renderServiceStepSettings() Render checkbox settings.
 * @method static int   renderTimeStepSettings() Render checkbox settings.
 * @method static bool  showCreditCard() In case there are payment systems that request credit card information in the Details step, it will return true.
 */
abstract class Shared extends Lib\Base\Proxy
{

}