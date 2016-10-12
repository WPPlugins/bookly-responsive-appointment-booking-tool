<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class PayuLatam
 */
class PayuLatam
{
    // Array for cleaning PayU Latam request
    public static $remove_parameters = array( 'action', 'ab_fid', 'error_msg', 'merchantId', 'merchant_name', 'merchant_address', 'telephone', 'merchant_url', 'transactionState', 'lapTransactionState', 'message', 'referenceCode', 'reference_pol', 'transactionId', 'description', 'trazabilityCode', 'cus', 'orderLanguage', 'extra1', 'extra2', 'extra3', 'polTransactionState', 'signature', 'polResponseCode', 'lapResponseCode', 'risk', 'polPaymentMethod', 'lapPaymentMethod', 'polPaymentMethodType', 'lapPaymentMethodType', 'installmentsNumber', 'TX_VALUE', 'TX_TAX', 'currency', 'lng', 'pseCycle', 'buyerEmail', 'pseBank', 'pseReference1', 'pseReference2', 'pseReference3', 'authorizationCode', 'processingDate', );

    public static function replaceData( $form_id )
    {
        return array();
    }

    public static function renderForm( $form_id )
    {
    }

    /**
     * Payment is Approved when signature correct and amount equal appointment price
     *
     * @param $is_sandbox
     * @param $transaction_status
     * @param $reference_code
     * @param $transaction_id
     * @param $signature
     * @return bool
     */
    public static function paymentIsApproved( $is_sandbox, $transaction_status, $reference_code, $transaction_id, $signature )
    {
        return null;
    }

    /**
     * Handles IPN messages
     */
    public static function ipn()
    {
        wp_send_json_success();
    }

}