<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class CollectStatsAjax
 * @package Bookly\Backend\Components\Notices
 */
class CollectStatsAjax extends Lib\Base\Ajax
{
    /**
     * Dismiss 'Collecting stats' notice.
     */
    public static function dismissCollectingStatsNotice()
    {
        delete_user_meta( get_current_user_id(), 'bookly_show_collecting_stats_notice' );

        wp_send_json_success();
    }

    /**
     * Dismiss 'Collect stats' notice.
     */
    public static function dismissCollectStatsNotice()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_collect_stats_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Enable collecting stats.
     */
    public static function enableCollectingStats()
    {
        update_option( 'bookly_gen_collect_stats', '1' );

        wp_send_json_success();
    }
}