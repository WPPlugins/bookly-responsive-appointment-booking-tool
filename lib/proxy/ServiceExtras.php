<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class ServiceExtras
 * Invoke local methods from Service Extras add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static string getStepHtml( \BooklyLite\Lib\UserBookingData $userData, bool $show_cart_btn, string $info_text, string $progress_tracker ) Render step Repeat
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::getStepHtml()
 *
 * @method static void renderAppearance( string $progress_tracker ) Render extras in appearance.
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::renderAppearance()
 *
 * @method static \BooklyServiceExtras\Lib\Entities\ServiceExtra[] findByIds( array $extras_ids ) Return extras entities.
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::findByIds()
 *
 * @method static \BooklyServiceExtras\Lib\Entities\ServiceExtra[] findByServiceId( int $service_id ) Return extras entities.
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::findByServiceId()
 *
 * @method static \BooklyServiceExtras\Lib\Entities\ServiceExtra[] findAll() Return all extras entities.
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::findAll()
 *
 * @method static array getInfo( array $extras, bool $translate )
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::getInfo()
 *
 * @method static int getTotalDuration( array $extras )
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::getTotalDuration()
 *
 * @method static int reorder( array $order )
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::reorder()
 *
 * @method static void renderCustomerDetails() Render extras in customer details dialog
 * @see \BooklyServiceExtras\Lib\ProxyProviders\Local::renderCustomerDetails()
 */
abstract class ServiceExtras extends Base\ProxyInvoker
{

}