<?php
namespace Bookly\Lib\Notifications\Cart\Proxy;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking\Order;

/**
 * Class Pro
 * @package Bookly\Lib\Notifications\Cart\Proxy
 *
 * @method static void sendCombinedToClient( Order $order ) Send combined notifications to client.
 */
abstract class Pro extends Lib\Base\Proxy
{

}