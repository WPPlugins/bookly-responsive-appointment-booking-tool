<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class Subscribe
 * @package Bookly\Backend\Components\Notices
 */
class Subscribe extends Lib\Base\Component
{
    /**
     * Render subscribe notice.
     */
    public static function render()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_subscribe_notice', true ) ) {

            // Show notice 1 day after installation time.
            if ( time() - Lib\Plugin::getInstallationTime() >= DAY_IN_SECONDS ) {
                self::enqueueStyles( array(
                    'frontend' => array( 'css/ladda.min.css', ),
                ) );
                self::enqueueScripts( array(
                    'backend' => array( 'js/alert.js' => array( 'jquery' ), ),
                    'frontend' => array(
                        'js/spin.min.js'  => array( 'jquery' ),
                        'js/ladda.min.js' => array( 'jquery' ),
                    ),
                    'module' => array( 'js/subscribe.js' => array( 'bookly-alert.js', 'bookly-ladda.min.js', ), ),
                ) );

                self::renderTemplate( 'subscribe' );
            }
        }
    }
}