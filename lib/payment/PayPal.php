<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class PayPal
 * @package BooklyLite\Lib\Payment
 */
class PayPal
{
    const TYPE_EXPRESS_CHECKOUT = 'ec';
    const TYPE_PAYMENTS_STANDARD = 'ps';

    const URL_POSTBACK_IPN_LIVE = 'https://www.paypal.com/cgi-bin/webscr';
    const URL_POSTBACK_IPN_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    // Array for cleaning PayPal request
    static public $remove_parameters = array( 'bookly_action', 'bookly_fid', 'error_msg', 'token', 'PayerID',  'type' );

    /** @var  string */
    private $error;

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
    public function sendECRequest( $form_id )
    {
        exit;
    }

    /**
     * Outputs HTML form for PayPal Express Checkout.
     *
     * @param string $form_id
     */
    public static function renderECForm( $form_id )
    {
        echo '';
    }

    /**
     * Verify IPN request
     * @return bool
     */
    public static function verifyIPN()
    {
        return false;
    }

    /**
     * Gets error
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

}