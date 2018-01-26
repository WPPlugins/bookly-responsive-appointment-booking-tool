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
    public function checkAvailableTimeForCart()
    {
        $recalculate_totals = false;
        foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
            if ( array_key_exists( 'bookly', $wc_item ) ) {
                if ( ! isset( $wc_item['bookly']['version'] ) ) {
                    if ( $this->_migration( $wc_key, $wc_item ) === false ) {
                        // Removed item from cart.
                        continue;
                    }
                }
                $userData = new Lib\UserBookingData( null );
                $userData->fillData( $wc_item['bookly'] );
                $userData->cart->setItemsData( $wc_item['bookly']['items'] );
                if ( $wc_item['quantity'] > 1 ) {
                    foreach ( $userData->cart->getItems() as $cart_item ) {
                        // Equal appointments increase quantity
                        $cart_item->setNumberOfPersons( $cart_item->getNumberOfPersons() * $wc_item['quantity'] );
                    }
                }
                // Check if appointment's time is still available
                $failed_cart_key = $userData->cart->getFailedKey();
                if ( $failed_cart_key !== null ) {
                    $cart_item = $userData->cart->get( $failed_cart_key );
                    $slot = $cart_item->getSlots();
                    $notice = strtr( __( 'Sorry, the time slot %date_time% for %service% has been already occupied.', 'bookly' ),
                        array(
                            '%service%'   => '<strong>' . $cart_item->getService()->getTranslatedTitle() . '</strong>',
                            '%date_time%' => Lib\Utils\DateTime::formatDateTime( $slot[0][2] )
                    ) );
                    wc_print_notice( $notice, 'notice' );
                    WC()->cart->set_quantity( $wc_key, 0, false );
                    $recalculate_totals = true;
                }
            }
        }
        if ( $recalculate_totals ) {
            WC()->cart->calculate_totals();
        }
    }

    /**
     * Assign checkout value from appointment.
     *
     * @param $null
     * @param $field_name
     * @return string|null
     */
    public function checkoutValue( $null, $field_name )
    {
        if ( empty( $this->checkout_info ) ) {
            foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
                if ( array_key_exists( 'bookly', $wc_item ) ) {
                    if ( ! isset( $wc_item['bookly']['version'] ) || $wc_item['bookly']['version'] < self::VERSION ) {
                        if ( $this->_migration( $wc_key, $wc_item ) === false ) {
                            // Removed item from cart.
                            continue;
                        }
                    }
                    $this->checkout_info = array(
                        'billing_first_name' => $wc_item['bookly']['first_name'],
                        'billing_last_name'  => $wc_item['bookly']['last_name'],
                        'billing_email'      => $wc_item['bookly']['email'],
                        'billing_phone'      => $wc_item['bookly']['phone']
                    );
                    break;
                }
            }
        }
        if ( array_key_exists( $field_name, $this->checkout_info ) ) {
            return $this->checkout_info[ $field_name ];
        }

        return null;
    }

    /**
     * Do bookings after checkout.
     *
     * @param $order_id
     */
    public function paymentComplete( $order_id )
    {
        $order = new \WC_Order( $order_id );
        foreach ( $order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'bookly' );
            if ( $data && ! isset ( $data['processed'] ) ) {
                $userData = new Lib\UserBookingData( null );
                $userData->fillData( $data );
                $userData->cart->setItemsData( $data['items'] );
                if ( $order_item['qty'] > 1 ) {
                    foreach ( $userData->cart->getItems() as $cart_item ) {
                        $cart_item->setNumberOfPersons( $cart_item->getNumberOfPersons() * $order_item['qty'] );
                    }
                }
                list( $total, $deposit ) = $userData->cart->getInfo();
                $payment = new Lib\Entities\Payment();
                $payment
                    ->setType( Lib\Entities\Payment::TYPE_WOOCOMMERCE )
                    ->setStatus( Lib\Entities\Payment::STATUS_COMPLETED )
                    ->setTotal( $total )
                    ->setPaid( $deposit )
                    ->setCreated( current_time( 'mysql' ) )
                    ->save();
                $order = $userData->save( $payment );
                $payment->setDetails( $order )->save();
                if ( get_option( 'bookly_cst_create_account' ) && $order->getCustomer()->getWpUserId() ) {
                    update_post_meta( $order_id, '_customer_user', $order->getCustomer()->getWpUserId() );
                }
                // Mark item as processed.
                $data['processed'] = true;
                $data['ca_list']   = array();
                foreach ( $order->getFlatItems() as $item ) {
                    $data['ca_list'][] = $item->getCA()->getId();
                }
                wc_update_order_item_meta( $item_id, 'bookly', $data );
                Lib\NotificationSender::sendFromCart( $order );
            }
        }
    }

    /**
     * Cancel appointments on WC order cancelled.
     *
     * @param $order_id
     */
    public function cancelOrder( $order_id )
    {
        $order = new \WC_Order( $order_id );
        foreach ( $order->get_items() as $item_id => $order_item ) {
            $data = wc_get_order_item_meta( $item_id, 'bookly' );
            if ( isset ( $data['processed'], $data['ca_ids'] ) && $data['processed'] ) {
                /** @var Lib\Entities\CustomerAppointment[] $ca_list */
                $ca_list = Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $data['ca_ids'] )->find();
                foreach ( $ca_list as $ca ) {
                    $ca->cancel();
                }
                $data['ca_ids'] = array();
                wc_update_order_item_meta( $item_id, 'bookly', $data );
            }
        }
    }

    /**
     * Change attr for WC quantity input
     *
     * @param array       $args
     * @param \WC_Product $product
     * @return mixed
     */
    public function quantityArgs( $args, $product )
    {
        if ( $product->get_id() == $this->product_id ) {
            $args['max_value'] = $args['input_value'];
            $args['min_value'] = $args['input_value'];
        }

        return $args;
    }

    /**
     * Change item price in cart.
     *
     * @param \WC_Cart $cart_object
     */
    public function beforeCalculateTotals( $cart_object )
    {
        foreach ( $cart_object->cart_contents as $wc_key => $wc_item ) {
            if ( isset ( $wc_item['bookly'] ) ) {
                if ( ! isset( $wc_item['bookly']['version'] ) || $wc_item['bookly']['version'] < self::VERSION ) {
                    if ( $this->_migration( $wc_key, $wc_item ) === false ) {
                        // Removed item from cart.
                        continue;
                    }
                }
                $userData = new Lib\UserBookingData( null );
                $userData->fillData( $wc_item['bookly'] );
                $userData->cart->setItemsData( $wc_item['bookly']['items'] );
                list( , $deposit ) = $userData->cart->getInfo();
                /** @var \WC_Product $wc_item['data'] */
                $wc_item['data']->set_price( $deposit );
            }
        }
    }

    public function addOrderItemMeta( $item_id, $values, $wc_key )
    {
        if ( isset ( $values['bookly'] ) ) {
            wc_update_order_item_meta( $item_id, 'bookly', $values['bookly'] );
        }
    }

    /**
     * Get item data for cart.
     *
     * @param $other_data
     * @param $wc_item
     * @return array
     */
    public function getItemData( $other_data, $wc_item )
    {
        if ( isset ( $wc_item['bookly'] ) ) {
            $userData = new Lib\UserBookingData( null );
            $info = array();
            if ( isset ( $wc_item['bookly']['version'] ) && $wc_item['bookly']['version'] == self::VERSION ) {
                $userData->fillData( $wc_item['bookly'] );
                if ( Lib\Config::useClientTimeZone() ) {
                    $userData->applyTimeZone();
                }
                $userData->cart->setItemsData( $wc_item['bookly']['items'] );
                list ( , $deposit, $due ) = $userData->cart->getInfo();
                foreach ( $userData->cart->getItems() as $cart_item ) {
                    $slots     = $cart_item->getSlots();
                    $client_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();
                    $service   = $cart_item->getService();
                    $staff     = $cart_item->getStaff();
                    $codes = array(
                        '{amount_due}'        => Lib\Utils\Price::format( $due ),
                        '{amount_to_pay}'     => Lib\Utils\Price::format( $deposit ),
                        '{appointment_date}'  => $client_dp->formatI18nDate(),
                        '{appointment_time}'  => $client_dp->formatI18nTime(),
                        '{category_name}'     => $service ? $service->getTranslatedCategoryName() : '',
                        '{number_of_persons}' => $cart_item->getNumberOfPersons(),
                        '{service_info}'      => $service ? $service->getTranslatedInfo() : '',
                        '{service_name}'      => $service ? $service->getTranslatedTitle() : __( 'Service was not found', 'bookly' ),
                        '{service_price}'     => $service ? Lib\Utils\Price::format( $cart_item->getServicePrice() ) : '',
                        '{staff_info}'        => $staff ? $staff->getTranslatedInfo() : '',
                        '{staff_name}'        => $staff ? $staff->getTranslatedName() : '',
                    );
                    $data  = Lib\Proxy\Shared::prepareCartItemInfoText( array(), $cart_item );
                    $codes = Lib\Proxy\Shared::prepareInfoTextCodes( $codes, $data );
                    // Support deprecated codes [[CODE]]
                    foreach ( array_keys( $codes ) as $code_key ) {
                        if ( $code_key{1} == '[' ) {
                            $codes[ '{' . strtolower( substr( $code_key, 2, -2 ) ) . '}' ] = $codes[ $code_key ];
                        } else {
                            $codes[ '[[' . strtoupper( substr( $code_key, 1, -1 ) ) . ']]' ] = $codes[ $code_key ];
                        }
                    }
                    $info[]  = strtr( Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_wc_cart_info_value' ), $codes );
                }
            }
            $other_data[] = array( 'name' => Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_wc_cart_info_name' ), 'value' => implode( PHP_EOL . PHP_EOL, $info ) );
        }

        return $other_data;
    }

    /**
     * Print appointment details inside order items in the backend.
     *
     * @param int $item_id
     */
    public function orderItemMeta( $item_id )
    {
        $data = wc_get_order_item_meta( $item_id, 'bookly' );
        if ( $data ) {
            $other_data = $this->getItemData( array(), array( 'bookly' => $data ) );
            echo '<br/>' . $other_data[0]['name'] . '<br/>' . nl2br( $other_data[0]['value'] );
        }
    }

    /**
     * Add product to cart
     *
     * return string JSON
     */
    public function executeAddToWoocommerceCart()
    {
        if ( ! get_option( 'bookly_wc_enabled' ) ) {
            exit;
        }
        $response = null;
        $userData = new Lib\UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $session = WC()->session;
            /** @var \WC_Session_Handler $session */
            if ( $session instanceof \WC_Session_Handler && $session->get_session_cookie() === false ) {
                $session->set_customer_session_cookie( true );
            }
            if ( $userData->cart->getFailedKey() === null ) {
                $cart_item  = $this->getIntersectedItem( $userData->cart->getItems() );
                if ( $cart_item === null ) {
                    $first_name = $userData->getFirstName();
                    $last_name  = $userData->getLastName();
                    $full_name  = $userData->getFullName();
                    // Check if defined First name
                    if ( ! $first_name ) {
                        $first_name = strtok( $full_name, ' ' );
                        $last_name  = strtok( '' );
                    }
                    $bookly = array(
                        'version'           => self::VERSION,
                        'email'             => $userData->getEmail(),
                        'items'             => $userData->cart->getItemsData(),
                        'name'              => $full_name,
                        'first_name'        => $first_name,
                        'last_name'         => $last_name,
                        'phone'             => $userData->getPhone(),
                        'time_zone_offset'  => $userData->getTimeZoneOffset(),
                    );

                    // Qnt 1 product in $userData exists value with number_of_persons
                    WC()->cart->add_to_cart( $this->product_id, 1, '', array(), array( 'bookly' => $bookly ) );

                    $response = array( 'success' => true );
                } else {
                    $response = array( 'success' => false, 'error' => Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_time_slot_not_available' ) );
                }
            } else {
                $response = array( 'success' => false, 'error' => Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_step_time_slot_not_available' ) );
            }
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }
        wp_send_json( $response );
    }



}