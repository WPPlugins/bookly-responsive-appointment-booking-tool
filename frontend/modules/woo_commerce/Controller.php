<?php
namespace BooklyLite\Frontend\Modules\WooCommerce;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Frontend\Modules\WooCommerce
 */
class Controller extends Lib\Base\Controller
{
    const VERSION = '1.0';

    private $product_id = 0;
    private $checkout_info = array();

    protected function getPermissions()
    {
        return array( '_this' => 'anonymous', );
    }

    /**
     * Verifies the availability of all appointments that are in the cart
     */
    public function checkAvailableTimeForCart() { }

    /**
     * Assign checkout value from appointment.
     *
     * @param $null
     * @param $field_name
     * @return string|null
     */
    public function checkoutValue( $null, $field_name )
    {
        return $null;
    }

    /**
     * Do bookings after checkout.
     *
     * @param $order_id
     */
    public function paymentComplete( $order_id ) { }

    /**
     * Cancel appointments on WC order cancelled.
     *
     * @param $order_id
     */
    public function cancelOrder( $order_id ) { }

    /**
     * Change attr for WC quantity input
     *
     * @param $args
     * @param $product
     * @return mixed
     */
    function quantityArgs( $args, $product )
    {
        return $args;
    }

    /**
     * Change item price in cart.
     *
     * @param $cart_object
     */
    public function beforeCalculateTotals( $cart_object ) { }

    public function addOrderItemMeta( $item_id, $values, $wc_key ) { }

    /**
     * Get item data for cart.
     *
     * @param $other_data
     * @param $wc_item
     * @return array
     */
    function getItemData( $other_data, $wc_item )
    {
        return $other_data;
    }

    /**
     * Print appointment details inside order items in the backend.
     *
     * @param $item_id
     */
    public function orderItemMeta( $item_id ) { }

    /**
     * Add product to cart
     *
     * return string JSON
     */
    public function executeAddToWoocommerceCart()
    {
        wp_send_json( array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) ) );
    }

    /**
     * Migration deprecated cart items.
     *
     * @param $wc_key
     * @param $data
     * @return bool
     */
    private function _migration( $wc_key, $data )
    {
        // The current implementation only remove cart items with deprecated format.
        WC()->cart->set_quantity( $wc_key, 0, false );
        WC()->cart->calculate_totals();

        return false;
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }

}