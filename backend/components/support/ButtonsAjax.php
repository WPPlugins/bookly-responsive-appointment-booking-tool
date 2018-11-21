<?php
namespace Bookly\Backend\Components\Support;

use Bookly\Lib;
use Bookly\Backend\Modules;
use Bookly\Backend\Components\Support\Lib\Urls;

/**
 * Class ButtonsAjax
 * @package Bookly\Backend\Components\Support
 */
class ButtonsAjax extends Lib\Base\Ajax
{
    /**
     * Send support request.
     */
    public static function sendSupportRequest()
    {
        $name  = trim( self::parameter( 'name' ) );
        $email = trim( self::parameter( 'email' ) );
        $msg   = trim( self::parameter( 'msg' ) );

        // Validation.
        if ( $email == '' || $msg == '' ) {
            wp_send_json_error( array( 'message' => __( 'All fields marked with an asterisk (*) are required.', 'bookly' ) ) );
        }
        if ( ! is_email( $email ) ) {
            wp_send_json_error( array(
                'invalid_email' => true,
                'message'       => __( 'Invalid email.', 'bookly' ),
            ) );
        }

        $plugins = apply_filters( 'bookly_plugins', array() );
        $message = self::renderTemplate( '_email_to_support', compact( 'name', 'email', 'msg', 'plugins' ), false );
        $headers = array(
            'Content-Type: text/html; charset=utf-8',
            'From: ' . get_option( 'bookly_email_sender_name' ) . ' <' . get_option( 'bookly_email_sender' ) . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        );

        if ( wp_mail( 'support@bookly.info', 'Support Request ' . site_url(), $message, $headers ) ) {
            wp_send_json_success( array( 'message' => __( 'Sent successfully.', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error sending support request.', 'bookly' ) ) );
        }
    }

    /**
     * Dismiss notice for 'Contact Us' button.
     */
    public static function dismissContactUsNotice()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_contact_us_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Record click on 'Contact Us' button.
     */
    public static function contactUsBtnClicked()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_contact_us_notice', 1 );
        update_user_meta( get_current_user_id(), 'bookly_contact_us_btn_clicked', 1 );

        wp_send_json_success();
    }

    /**
     * Dismiss notice for 'Feedback' button.
     */
    public static function dismissFeedbackNotice()
    {
        update_user_meta( get_current_user_id(), 'bookly_dismiss_feedback_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Proceed to feature requests.
     */
    public static function proceedToFeatureRequests()
    {
        update_user_meta( get_current_user_id(), 'bookly_feature_requests_rules_hide', self::parameter( 'hide', 0 ) );
        wp_send_json_success( array( 'target' => Lib\Utils\Common::prepareUrlReferrers( Urls::FEATURES_REQUEST_PAGE, 'notification_bar' ) ) );
    }
}