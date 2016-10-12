<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class Payson
 * @package BooklyLite\Lib\Payment
 */
class Payson
{
    // Array for cleaning Payson request
    public static $remove_parameters = array( 'action', 'ab_fid', 'error_msg', 'TOKEN' );

    public static function renderForm( $form_id )
    {

    }

    /**
     * Check gateway data and if ok save payment info
     *
     * @param \PaymentDetails          $details
     * @param bool|false               $ipn      When ipn false, this is request from browser and we use _redirectTo for notification customer
     * @param null|Lib\UserBookingData $userData
     */
    public static function handlePayment( $details, $ipn = false, $userData = null )
    {
        header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                'action'    => 'ab-payson-error',
                'ab_fid'    => stripslashes( @$_REQUEST['ab_fid'] ),
                'error_msg' => urlencode( __( 'Payment status', 'bookly' ) ),
            ), Lib\Utils\Common::getCurrentPageURL()
            ) ) );
        exit;
    }

    /**
     * Notification for customer
     *
     * @param Lib\UserBookingData $userData
     * @param string $status    success || error || processing
     * @param string $message
     */
    private static function _redirectTo( Lib\UserBookingData $userData, $status = 'success', $message = '' )
    {
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYSON, $status, $message );
        @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Redirect to Payson Payment page, or step payment.
     *
     * @param $form_id
     * @param Lib\UserBookingData $userData
     * @param $response_url
     */
    public static function paymentPage( $form_id, Lib\UserBookingData $userData, $response_url )
    {
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_PAYSON, 'error', '' );
        @wp_redirect( remove_query_arg( Lib\Payment\Payson::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
    }

    /**
     * @param Lib\Entities\CustomerAppointment[] $customer_appointments
     */
    private static function _deleteAppointments( $customer_appointments )
    {
        foreach ( $customer_appointments as $customer_appointment ) {
            $customer_appointment->deleteCascade();
        }
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        wp_send_json_success();
    }

    /**
     * Response when payment form completed
     *
     * @return \PaymentDetailsResponse or redirect
     */
    public static function response()
    {
        Lib\Payment\Payson::handlePayment( null, false, new Lib\UserBookingData( stripslashes( @$_GET['ab_fid'] ) ) );

    }

    /**
     * Handle cancel request
     */
    public static function cancel()
    {
    }

    /**
     * Init Api
     *
     * @return \PaysonApi
     */
    private static function _getApi()
    {
        return null;
    }

}