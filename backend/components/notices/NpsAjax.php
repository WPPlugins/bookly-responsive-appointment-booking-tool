<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;
use Bookly\Backend\Modules;

/**
 * Class NpsAjax
 * @package Bookly\Backend\Components\Notices
 */
class NpsAjax extends Lib\Base\Ajax
{
    /**
     * Send Net Promoter Score.
     */
    public static function npsSend()
    {
        $rate  = self::parameter( 'rate' );
        $msg   = self::parameter( 'msg', '' );
        $email = self::parameter( 'email', '' );

        Lib\API::sendNps( $rate, $msg, $email );

        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_nps_notice', 1 );

        wp_send_json_success( array( 'message' => __( 'Sent successfully.', 'bookly' ) ) );
    }

    /**
     * Dismiss NPS notice.
     */
    public static function dismissNpsNotice()
    {
        if ( get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_nps_notice', true ) != 1 ) {
            update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_nps_notice', time() );
        }

        wp_send_json_success();
    }
}