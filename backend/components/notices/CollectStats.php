<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class CollectStats
 * @package Bookly\Backend\Components\Notices
 */
class CollectStats extends Lib\Base\Component
{
    /**
     * Render collect stats notice.
     */
    public static function render()
    {
        if ( self::needShowCollectStatNotice() ) {
            self::enqueueStyles( array(
                'frontend' => array( 'css/ladda.min.css', ),
            ) );
            self::enqueueScripts( array(
                'module' => array( 'js/collect-stats.js' => array( 'jquery' ), ),
            ) );

            self::renderTemplate( 'collect_stats', array( 'enabled' => get_option( 'bookly_gen_collect_stats' ) == '1' ) );
        }
    }

    /**
     * @return bool
     */
    public static function needShowCollectStatNotice()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $enabled = get_option( 'bookly_gen_collect_stats' ) == '1';
            $user_id = get_current_user_id();
            if (
                $enabled && get_user_meta( $user_id, 'bookly_show_collecting_stats_notice', true ) ||
                ! $enabled && ! get_user_meta( $user_id, 'bookly_dismiss_collect_stats_notice', true )
            ) {
                return true;
            }
        }

        return false;
    }
}