<?php
namespace BooklyLite\Lib\Proxy;

use BooklyLite\Lib\Base;

/**
 * Class Packages
 * Invoke local methods from Packages add-on.
 *
 * @package BooklyLite\Lib\Proxy
 *
 * @method static void addBooklyMenuItem() Add 'Packages' to Bookly menu
 * @see \BooklyPackages\Lib\ProxyProviders\Local::addBooklyMenuItem()
 *
 * @method static void renderServicePackage( array $service, array $service_collection ) Render sub services for packages
 * @see \BooklyPackages\Lib\ProxyProviders\Local::renderServicePackage()
 *
 * @method static void renderPackageScheduleDialog()
 * @see \BooklyPackages\Lib\ProxyProviders\Local::renderPackageScheduleDialog()
 *
 * @method static array prepareNotificationCodesList( array $codes, string $set = '', string $notification_type = 'staff_package_deleted' ) Alter array of codes to be displayed in Bookly Notifications.
 * @see \BooklyPackages\Lib\ProxyProviders\Local::prepareNotificationCodesList()
 */
abstract class Packages extends Base\ProxyInvoker
{

}