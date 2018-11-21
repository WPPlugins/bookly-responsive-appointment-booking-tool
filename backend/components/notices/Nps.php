<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;
use Bookly\Backend\Modules;

/**
 * Class Nps
 * @package Bookly\Backend\Components\Notices
 */
class Nps extends Lib\Base\Component
{
    /**
     * Render Net Promoter Score notice.
     */
    public static function render()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $dismiss_value = get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_nps_notice', true );
            // Show notice 1 month after it was closed the last time.
            if ( ! $dismiss_value || $dismiss_value > 1 && time() - $dismiss_value >= 30 * DAY_IN_SECONDS ) {
                // Show notice 1 month after installation time.
                if ( time() - Lib\Plugin::getInstallationTime() >= 30 * DAY_IN_SECONDS ) {
                    self::enqueueStyles( array(
                        'frontend' => array( 'css/ladda.min.css', ),
                        'module'   => array( 'css/bootstrap-stars.css', ),
                    ) );

                    self::enqueueScripts( array(
                        'backend' => array(
                            'js/alert.js' => array( 'jquery' ),
                        ),
                        'frontend' => array(
                            'js/spin.min.js'  => array( 'jquery' ),
                            'js/ladda.min.js' => array( 'jquery' ),
                        ),
                        'module' => array(
                            'js/jquery.barrating.min.js' => array( 'jquery' ),
                            'js/nps.js' => array( 'bookly-jquery.barrating.min.js', 'bookly-alert.js', 'bookly-ladda.min.js', ),
                        ),
                    ) );

                    self::renderTemplate( 'nps', array( 'current_user' => wp_get_current_user() ) );
                }
            }
        }
    }
}