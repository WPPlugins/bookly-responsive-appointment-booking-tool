<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class Mollie
 */
class Mollie
{
    // Array for cleaning Mollie request
    public static $remove_parameters = array( 'action', 'ab_fid', 'error_msg' );

    public static function renderForm( $form_id )
    {
        $userData = new Lib\UserBookingData( $form_id );
        if ( $userData->load() ) {
            $replacement = array(
                '%form_id%' => $form_id,
                '%gateway%' => Lib\Entities\Payment::TYPE_MOLLIE,
                '%back%'    => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_back' ),
                '%next%'    => Lib\Utils\Common::getTranslatedOption( 'ab_appearance_text_button_next' ),
            );
            $form        = '<form method="post" class="ab-%gateway%-form">
                <input type="hidden" name="ab_fid" value="%form_id%"/>
                <input type="hidden" name="action" value="ab-mollie-checkout"/>
                <input type="hidden" name="response_url"/>
                <button class="ab-left ab-back-step ab-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">%back%</span></button>
                <button class="ab-right ab-next-step ab-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">%next%</span></button>
             </form>';
            echo strtr( $form, $replacement );
        }
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
    }

    /**
     * Check gateway data and if ok save payment info
     *
     * @param \Mollie_API_Object_Payment $details
     */
    public static function handlePayment( $details )
    {
        wp_send_json_success();
    }

    /**
     * Redirect to Mollie Payment page, or step payment.
     *
     * @param                     $form_id
     * @param Lib\UserBookingData $userData
     * @param                     $response_url
     */
    public static function paymentPage( $form_id, Lib\UserBookingData $userData, $response_url )
    {
        self::_redirectTo( $userData, 'error', __( 'Mollie error.', 'bookly' ) );
    }

    /**
     * Notification for customer
     *
     * @param Lib\UserBookingData $userData
     * @param string              $status success || error || processing
     * @param string              $message
     */
    private static function _redirectTo( Lib\UserBookingData $userData, $status = 'success', $message = '' )
    {
        $userData->load();
        $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_MOLLIE, $status, $message );
        @wp_redirect( remove_query_arg( Lib\Payment\Mollie::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }

    public static function getCancelledAppointments( $tr_id )
    {
        return array();
    }

}