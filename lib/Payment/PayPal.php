<?php

/**
 *
 */
class PayPal {

	/**
	 * The array of products for checkout
	 *
	 * @var array
	 */
	protected $products = array();

    public static function getCurrencyCodes() {
        return array( 'AUD', 'BRL', 'CAD', 'RMB', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'IDR', 'INR', 'ILS', 'JPY', 'KRW', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RON', 'RUB', 'SGD', 'ZAR', 'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD' );
    }

    /**
     * Add the Product for payment
     *
     * @param stdClass $product
     */
    public function addProduct( stdClass $product ) {
        $this->products[] = $product;
    }
}