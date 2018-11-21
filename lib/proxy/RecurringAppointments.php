<?php
namespace Bookly\Lib\Proxy;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking as DataHolders;

/**
 * Class RecurringAppointments
 * @package Bookly\Lib\Proxy
 *
 * @method static void cancelPayment( int $payment_id ) Cancel payment for whole series.
 * @method static bool hideChildAppointments( bool $default, Lib\CartItem $cart_item ) If only first appointment in series needs to be paid hide next appointments.
 * @method static void sendRecurring( DataHolders\Series $series, DataHolders\Order $order, $codes_data = array(), $to_staff = true, $to_customer = true ) Send notifications for recurring appointment.
 */
abstract class RecurringAppointments extends Lib\Base\Proxy
{

}