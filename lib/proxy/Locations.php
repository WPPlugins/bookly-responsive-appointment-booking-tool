<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class Locations
 * Invoke local methods from Locations add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static void addBooklyMenuItem() Add 'Locations' to Bookly menu
 * @see \BooklyLocations\Lib\ProxyProviders\Local::addBooklyMenuItem()
 *
 * @method static \Booklylocations\Lib\Entities\Location|false findById( int $location_id ) Return Location entity.
 * @see \BooklyLocations\Lib\ProxyProviders\Local::findById()
 *
 * @method static \Booklylocations\Lib\Entities\Location[] findByStaffId( int $staff_id ) Return locations associated with given staff.
 * @see \BooklyLocations\Lib\ProxyProviders\Local::findByStaffId()
 *
 * @method static void renderAppearance() Render Locations in Appearance
 * @see \BooklyLocations\Lib\ProxyProviders\Local::renderAppearance()
 */
abstract class Locations extends Base\ProxyInvoker
{

}