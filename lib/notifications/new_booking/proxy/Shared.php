<?php
namespace Bookly\Lib\Notifications\NewBooking\Proxy;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking\Item;
use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Notifications\NewBooking\Codes;
use Bookly\Lib\Notifications\NewBooking\ItemSenders;

/**
 * Class Shared
 * @package Bookly\Lib\Notifications\NewBooking\Proxy
 *
 * @method static ItemSenders\Base getSenderForItem( $default, Item $item, Order $order, Codes $codes ) Get sender for given order item.
 * @method static void  prepareCodesForItem( Codes $codes ) Prepare codes data for new order item (translatable data should be set here).
 * @method static void  prepareCodesForOrder( Codes $codes ) Prepare codes data for order.
 * @method static array prepareNotificationTitles( array $titles ) Prepare notification titles.
 * @method static array prepareNotificationTypeIds( array $type_ids ) Prepare notification type IDs.
 * @method static array prepareReplaceCodes( array $replace_codes, Codes $codes, $format ) Prepare codes for replacements.
 * @method static Lib\Notifications\Codes prepareTestNotificationCodes( Lib\Notifications\Codes $codes ) Prepare codes for testing email templates
 */
abstract class Shared extends Lib\Base\Proxy
{

}