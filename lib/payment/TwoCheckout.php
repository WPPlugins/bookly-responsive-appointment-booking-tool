<?php
namespace BooklyLite\Lib\Payment;

use BooklyLite\Lib;

/**
 * Class TwoCheckout
 */
class TwoCheckout
{
    // Array for cleaning 2Checkout request
    public static $remove_parameters = array( 'action', 'ab_fid', 'error_msg', 'sid', 'middle_initial', 'li_0_name', 'key', 'email', 'li_0_type', 'lang', 'currency_code', 'invoice_id', 'li_0_price', 'total', 'credit_card_processed', 'zip', 'li_0_quantity', 'cart_weight', 'fixed', 'last_name', 'li_0_product_id', 'street_address', 'city', 'li_0_tangible', 'li_0_description', 'ip_country', 'country', 'merchant_order_id', 'pay_method', 'cart_tangible', 'phone', 'street_address2', 'x_receipt_link_url', 'first_name', 'card_holder_name', 'state', 'order_number', 'type', );

    public static function renderForm( $form_id )
    {
    }

}