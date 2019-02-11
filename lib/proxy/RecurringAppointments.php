<?php
namespace Bookly\Lib\Proxy;

use Bookly\Lib;

/**
 * Class RecurringAppointments
 * @package Bookly\Lib\Proxy
 *
 * @method static void cancelPayment( int $payment_id ) Cancel payment for whole series.
 * @method static bool hideChildAppointments( bool $default, Lib\CartItem $cart_item ) If only first appointment in series needs to be paid hide next appointments.
 * @method static bool sendSeries( Lib\Entities\Notification[] $notifications, Lib\DataHolders\Booking\Item $item, Lib\DataHolders\Booking\Order $order, Lib\Notifications\Assets\Item\Codes $codes )
 */
abstract class RecurringAppointments extends Lib\Base\Proxy
{

}