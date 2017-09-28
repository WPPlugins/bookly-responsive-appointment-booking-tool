<?php
namespace BooklyLite\Backend\Modules\Support;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Support
 */
class Controller extends Lib\Base\Controller
{
    /**
     * Send support request.
     */
    public function executeSendSupportRequest()
    {
        $name  = trim( $this->getParameter( 'name' ) );
        $email = trim( $this->getParameter( 'email' ) );
        $msg   = trim( $this->getParameter( 'msg' ) );

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

        $message = $this->render( '_email_to_support', compact( 'name', 'email', 'msg' ), false );
        $headers = array(
            'Content-Type: text/html; charset=utf-8',
            'From: ' . get_option( 'bookly_email_sender_name' ) . ' <' . get_option( 'bookly_email_sender' ) . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        );

        if ( wp_mail( 'support@ladela.com', 'Support Request ' . site_url(), $message, $headers ) ) {
            wp_send_json_success( array( 'message' => __( 'Sent successfully.', 'bookly' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error sending support request.', 'bookly' ) ) );
        }
    }

    /**
     * Dismiss notice for 'Contact Us' button.
     */
    public function executeDismissContactUsNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_contact_us_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Record click on 'Contact Us' button.
     */
    public function executeContactUsBtnClicked()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_contact_us_notice', 1 );
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'contact_us_btn_clicked', 1 );

        wp_send_json_success();
    }

    /**
     * Dismiss notice for 'Feedback' button.
     */
    public function executeDismissFeedbackNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_feedback_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Subscribe to monthly emails.
     */
    public function executeSubscribe()
    {
        $email = $this->getParameter( 'email' );
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
    public function executeDismissSubscribeNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_subscribe_notice', 1 );

        wp_send_json_success();
    }

    /**
     * Override parent method to add 'wp_ajax_bookly_' prefix.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}