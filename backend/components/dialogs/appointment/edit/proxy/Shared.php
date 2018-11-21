<?php
namespace Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy
 *
 * @method static array prepareDataForPackage( array $result )
 * @method static void  renderAppointmentDialogCustomersList() Render content in AppointmentForm for customers.
 * @method static void  renderAppointmentDialogFooter() Render buttons in appointments dialog footer.
 * @method static void  renderComponents()
 * @method static void  enqueueAssets()
 */
abstract class Shared extends Lib\Base\Proxy
{

}