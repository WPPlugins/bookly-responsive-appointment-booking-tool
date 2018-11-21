<?php
namespace Bookly\Frontend\Modules\Booking\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Frontend\Modules\Booking\Proxy
 *
 * @method static array  booklyFormOptions( array $bookly_options ) modify bookly options.
 * @method static array  enqueueBookingScripts( array $depends ) Enqueue scripts for booking form. @params $depends as array of registered script handles this script depends on.
 * @method static array  enqueueBookingStyles( array $depends ) Enqueue styles for booking form. @params $depends as array of registered style handles this sheet depends on.
 * @method static array  prepareCartItemInfoText( array $data, Lib\CartItem $cart_item ) Prepare array for replacing in Cart items.
 * @method static array  prepareChainItemInfoText( array $data, Lib\ChainItem $chain_item ) Prepare array for replacing in Chain items.
 * @method static array  prepareInfoTextCodes( array $info_text_codes, array $data ) Prepare array for replacing on booking steps.
 * @method static array  preparePaymentOptions( array $options, $form_id, bool $show_price, Lib\CartInfo $cart_info, $payment_status ) Prepare payment options for Payment step.
 * @method static void   renderCartItemInfo( Lib\UserBookingData $userData, $cart_key, $positions, $desktop ) Render extra info for cart item at Cart step.
 * @method static void   renderChainItemHead() Render head for chain in step service.
 * @method static void   renderChainItemTail() Render tail for chain in step service.
 * @method static void   renderChainItemTailTip() Render tail tip for chain in step service.
 * @method static string renderCustomFieldsOnDetailsStep( Lib\UserBookingData $userData ) Get Custom Fields HTML for details step.
 * @method static void   renderPaymentForms( $form_id, $page_url ) Render payment form for each gateway at Payment step.
 * @method static void   renderWaitingListInfoText() Render WL info text in Time step.
 */
abstract class Shared extends Lib\Base\Proxy
{

}