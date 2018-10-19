<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class SubscribeAjax
 * @package Bookly\Backend\Components\Notices
 */
class SubscribeAjax extends Lib\Base\Ajax
{
    /**
     * Subscribe to monthly emails.
     */
    public static function subscribe()
    {
        $email = self::parameter( 'email' );
        if ( is_email( $email ) ) {
            Lib\API::registerSubscriber( $email );

            wp_send_json_success( array( 'message' => __( 'Sent successfully.', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Invalid email.', 'bookly' ) ) );
        }
    }

    /**
     * Dismiss subscribe notice.
     */
    public static function dismissSubscribeNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_subscribe_notice', 1 );

        wp_send_json_success();
    }
}