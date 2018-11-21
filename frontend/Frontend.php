<?php
namespace Bookly\Frontend;

use Bookly\Lib;

/**
 * Class Frontend
 * @package Bookly\Frontend
 */
abstract class Frontend
{
    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        add_action( 'wp_loaded', array( __CLASS__, 'handleRequest' ) );
    }

    /**
     * Handle request.
     */
    public static function handleRequest()
    {
        // Payments ( PayPal Express Checkout and etc. )
        if ( isset ( $_REQUEST['bookly_action'] ) ) {
            // Disable caching.
            Lib\Utils\Common::noCache();

            Lib\Proxy\Shared::handleRequestAction( $_REQUEST['bookly_action'] );
        }
    }
}