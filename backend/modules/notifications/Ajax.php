<?php
namespace Bookly\Backend\Modules\Notifications;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Notifications
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Get notifications data.
     */
    public static function getEmailNotificationsData()
    {
        $form = new Forms\Notifications( 'email' );

        $bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
            get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );

        $bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
            get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );

        $notifications = array();
        foreach ( $form->getData() as $notification ) {
            if ( Lib\Config::proActive() || in_array( $notification['type'], Lib\Entities\Notification::$bookly_notifications['email'] ) ) {
                $name = Lib\Entities\Notification::getNameIfExists( $notification['type'] );
                if ( $name !== false ) {
                    if ( in_array( $notification['type'], Lib\Entities\Notification::getCustomNotificationTypes() ) && $notification['subject'] != '' ) {
                        // In window Test Email Notification
                        // for custom notification, subject is name.
                        $name = $notification['subject'];
                    }
                    $notifications[] = array(
                        'id'     => $notification['id'],
                        'name'   => $name,
                        'active' => $notification['active'],
                    );
                }
            }
        }

        $result = array(
            'notifications' => $notifications,
            'sender_email'  => $bookly_email_sender,
            'sender_name'   => $bookly_email_sender_name,
            'send_as'       => get_option( 'bookly_email_send_as' ),
            'reply_to_customers' => get_option( 'bookly_email_reply_to_customers' ),
        );

        wp_send_json_success( $result );
    }

    /**
     * Test email notifications.
     */
    public static function testEmailNotifications()
    {
        $to_email      = self::parameter( 'to_email' );
        $sender_name   = self::parameter( 'sender_name' );
        $sender_email  = self::parameter( 'sender_email' );
        $send_as       = self::parameter( 'send_as' );
        $notifications = self::parameter( 'notifications' );
        $reply_to_customers = self::parameter( 'reply_to_customers' );

        // Change 'Content-Type' and 'Reply-To' for test email notification.
        add_filter( 'bookly_email_headers', function ( $headers ) use ( $sender_name, $sender_email, $send_as, $reply_to_customers ) {
            $headers = array();
            if ( $send_as == 'html' ) {
                $headers[] = 'Content-Type: text/html; charset=utf-8';
            } else {
                $headers[] = 'Content-Type: text/plain; charset=utf-8';
            }
            $headers[] = 'From: ' . $sender_name . ' <' . $sender_email . '>';
            if ( $reply_to_customers ) {
                $headers[] = 'Reply-To: ' . $sender_name . ' <' . $sender_email . '>';
            }

            return $headers;
        }, 10, 1 );

        Lib\Notifications\Sender::sendTestEmailNotifications( $to_email, $notifications, $send_as );

        wp_send_json_success();
    }

    /**
     * Delete custom notification
     */
    public static function deleteCustomNotification()
    {
        $id = self::parameter( 'id' );
        Lib\Entities\Notification::query()
            ->delete()
            ->where( 'id', $id )
            ->whereIn( 'type', Lib\Entities\Notification::getCustomNotificationTypes() )
            ->execute();

        wp_send_json_success();
    }
}