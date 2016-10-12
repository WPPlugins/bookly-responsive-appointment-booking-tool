<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class PayPal
 * @package BooklyLite\Lib\Payment
 */
class PayPal
{
    // Array for cleaning PayPal request
    static public $remove_parameters = array( 'action', 'ab_fid', 'error_msg', 'token', 'PayerID',  'type' );

    /**
     * The array of products for checkout
     *
     * @var array
     */
    protected $products = array();

    /**
     * Send the Express Checkout NVP request
     *
     * @param $form_id
     * @throws \Exception
     */
    public function send_EC_Request( $form_id )
    {
        header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array( 'action' => 'ab-paypal-error', 'ab_fid' => $form_id, 'error_msg' => '' ), Lib\Utils\Common::getCurrentPageURL() ) ) );
        exit;
    }

    /**
     * Send the NVP Request to the PayPal
     *
     * @param       $method
     * @param array $data
     * @return array
     */
    public function sendNvpRequest( $method, array $data )
    {
    }

    public static function renderForm( $form_id )
    {
    }

    /**
     * Add the Product for payment
     *
     * @param \stdClass $product
     */
    public function addProduct( \stdClass $product )
    {
        $this->products[] = $product;
    }

}