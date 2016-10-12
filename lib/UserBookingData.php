<?php
namespace BooklyLite\Lib;

/**
 * Class UserBookingData
 * @package BooklyLite\Frontend\Modules\Booking\Lib
 */
class UserBookingData
{
    private $form_id = null;

    /**
     * @var array
     */
    private $booking_numbers = array();

    /**
     * Data provided by user at booking steps
     * and stored in PHP session.
     * @var array
     */
    private $data = array(
        // Step 0
        'time_zone_offset' => null,
        // Step service
        'date_from'        => null,
        'days'             => null,
        'time_from'        => null,
        'time_to'          => null,
        // Step time
        'slots'            => array(),
        // Step details
        'name'             => null,
        'email'            => null,
        'phone'            => null,
        // Step payment
        'coupon'           => null,
        // Cart item keys being edited
        'edit_cart_keys'   => array(),
    );

    /**
     * @var Cart
     */
    public $cart = null;

    /**
     * @var Chain
     */
    public $chain = null;

    /**
     * @var Entities\Coupon|null
     */
    private $coupon = null;

    /**
     * Constructor.
     *
     * @param $form_id
     */
    public function __construct( $form_id )
    {
        $this->form_id = $form_id;
        $this->cart    = new Cart( $this );
        $this->chain   = new Chain();

        // If logged in then set name, email and if existing customer then also phone.
        $current_user = wp_get_current_user();
        if ( $current_user && $current_user->ID ) {
            $customer = new Entities\Customer();
            if ( $customer->loadBy( array( 'wp_user_id' => $current_user->ID ) ) ) {
                $this->set( 'name',  $customer->get( 'name' ) );
                $this->set( 'email', $customer->get( 'email' ) );
                $this->set( 'phone', $customer->get( 'phone' ) );
            } else {
                $this->set( 'name',  $current_user->display_name );
                $this->set( 'email', $current_user->user_email );
            }
        }
    }

    public function resetChain()
    {
        $this->chain->clear();
        $this->chain->add( new ChainItem() );

        // Set up default parameters.
        $prior_time = Config::getMinimumTimePriorBooking();
        $this->set( 'date_from', date( 'Y-m-d', current_time( 'timestamp' ) + $prior_time ) );
        $times = Entities\StaffScheduleItem::query()
            ->select( 'SUBSTRING_INDEX(MIN(start_time), ":", 2) AS min_end_time,' .
                'SUBSTRING_INDEX(MAX(end_time), ":", 2) AS max_end_time' )
            ->whereNot( 'start_time', null )
            ->fetchRow();
        $this->set( 'time_from',      $times['min_end_time'] );
        $this->set( 'time_to',        $times['max_end_time'] );
        $this->set( 'slots',          array() );
        $this->set( 'edit_cart_keys', array() );
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        Session::setFormVar( $this->form_id, 'data',  $this->data );
        Session::setFormVar( $this->form_id, 'cart',  $this->cart->getItemsData() );
        Session::setFormVar( $this->form_id, 'chain', $this->chain->getItemsData() );
        Session::setFormVar( $this->form_id, 'booking_numbers', $this->booking_numbers );
        Session::setFormVar( $this->form_id, 'last_touched', time() );
    }

    /**
     * Set data parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set( $name, $value )
    {
        $this->data[ $name ] = $value;
    }

    /**
     * Get data parameter.
     *
     * @param string $name
     * @return mixed
     */
    public function get( $name )
    {
        if ( array_key_exists( $name, $this->data ) ) {
            return $this->data[ $name ];
        }

        return false;
    }

    /**
     * Load data from session.
     *
     * @return bool
     */
    public function load()
    {
        $data = Session::getFormVar( $this->form_id, 'data' );
        if ( $data !== null ) {
            // Restore data.
            $this->data = $data;
            $this->chain->setItemsData( Session::getFormVar( $this->form_id, 'chain' ) );
            $this->cart->setItemsData( Session::getFormVar( $this->form_id, 'cart' ) );
            $this->booking_numbers = Session::getFormVar( $this->form_id, 'booking_numbers' );

            return true;
        }

        return false;
    }

    /**
     * Partially update data in session.
     *
     * @param array $data
     */
    public function fillData( array $data )
    {
        foreach ( $data as $name => $value ) {
            if ( array_key_exists( $name, $this->data ) ) {
                /** Fill array @see UserBookingData::$data */
                $this->set( $name, $value );
            } elseif ( $name == 'chain' ) {
                $chain_items = $this->chain->getItems();
                $this->chain->clear();
                foreach ( $value as $key => $_data ) {
                    $item = isset ( $chain_items[ $key ] ) ? $chain_items[ $key ] : new ChainItem();
                    $this->chain->add( $item );
                    foreach ( $_data as $_name => $_value ) {
                        $item->set( $_name, $_value );
                    }
                }
            } elseif ( $name == 'cart' ) {
                foreach ( $value as $key => $_data ) {
                    $item = $this->cart->get( $key );
                    foreach ( $_data as $_name => $_value ) {
                        $item->set( $_name, $_value );
                    }
                }
            }
        }
    }

    /**
     * Set chain from given cart item.
     *
     * @param integer $cart_key
     */
    public function setChainFromCartItem( $cart_key )
    {
        $cart_item = $this->cart->get( $cart_key );
        $this->set( 'date_from', $cart_item->get( 'date_from' ) );
        $this->set( 'days',      $cart_item->get( 'days' ) );
        $this->set( 'time_from', $cart_item->get( 'time_from' ) );
        $this->set( 'time_to',   $cart_item->get( 'time_to' ) );
        $this->set( 'slots',     $cart_item->get( 'slots' ) );

        $chain_item = new ChainItem();
        $chain_item->set( 'service_id',        $cart_item->get( 'service_id' ) );
        $chain_item->set( 'staff_ids',         $cart_item->get( 'staff_ids' ) );
        $chain_item->set( 'number_of_persons', $cart_item->get( 'number_of_persons' ) );
        $chain_item->set( 'extras',            $cart_item->get( 'extras' ) );
        $chain_item->set( 'quantity',          1 );

        $this->chain->clear();
        $this->chain->add( $chain_item );
    }

    /**
     * Add chain items to cart.
     */
    public function addChainToCart()
    {
        $cart_items     = array();
        $edit_cart_keys = $this->get( 'edit_cart_keys' );
        $eck_idx        = 0;
        $slots          = $this->get( 'slots' );
        $slots_idx      = 0;
        foreach ( $this->chain->getItems() as $chain_item ) {
            for ( $q = 0; $q < $chain_item->get( 'quantity' ); ++ $q ) {
                $cart_item_slots = array();

                if ( $chain_item->getService()->get( 'type' ) == Entities\Service::TYPE_COMPOUND ) {
                    foreach ( $chain_item->getSubServices() as $sub_service ) {
                        $cart_item_slots[] = $slots[ $slots_idx ++ ];
                    }
                } else {
                    $cart_item_slots[] = $slots[ $slots_idx ++ ];
                }
                $cart_item = new CartItem();

                $cart_item->set( 'date_from', $this->get( 'date_from' ) );
                $cart_item->set( 'days',      $this->get( 'days' ) );
                $cart_item->set( 'time_from', $this->get( 'time_from' ) );
                $cart_item->set( 'time_to',   $this->get( 'time_to' ) );

                $cart_item->set( 'extras',            $chain_item->get( 'extras' ) );
                $cart_item->set( 'location_id',       $chain_item->get( 'location_id' ) );
                $cart_item->set( 'number_of_persons', $chain_item->get( 'number_of_persons' ) );
                $cart_item->set( 'service_id',        $chain_item->get( 'service_id' ) );
                $cart_item->set( 'slots',             $cart_item_slots );
                $cart_item->set( 'staff_ids',         $chain_item->get( 'staff_ids' ) );
                if ( isset ( $edit_cart_keys[ $eck_idx ] ) ) {
                    $cart_item->set( 'custom_fields', $this->cart->get( $edit_cart_keys[ $eck_idx ] )->get( 'custom_fields' ) );
                    ++ $eck_idx;
                }

                $cart_items[] = $cart_item;
            }
        }

        $count = count( $edit_cart_keys );
        $inserted_keys = array();

        if ( $count ) {
            for ( $i = $count - 1; $i > 0; -- $i ) {
                $this->cart->drop( $edit_cart_keys[ $i ] );
            }
            $inserted_keys = $this->cart->replace( $edit_cart_keys[0], $cart_items );
        } else {
            foreach ( $cart_items as $cart_item ) {
                $inserted_keys[] = $this->cart->add( $cart_item );
            }
        }

        $this->set( 'edit_cart_keys', $inserted_keys );
    }

    /**
     * Validate fields.
     *
     * @param $data
     * @return array
     */
    public function validate( $data )
    {
        $validator = new Validator();
        foreach ( $data as $field_name => $field_value ) {
            switch ( $field_name ) {
                case 'service_id':
                    $validator->validateNumber( $field_name, $field_value );
                    break;
                case 'date_from':
                case 'time_from':
                    $validator->validateDateTime( $field_name, $field_value, true );
                    break;
                case 'name':
                    $validator->validateString( $field_name, $field_value, 255, true, true, 3 );
                    break;
                case 'email':
                    $validator->validateEmail( $field_name, $data );
                    break;
                case 'phone':
                    $validator->validatePhone( $field_name, $field_value, true );
                    break;
                case 'cart':
                    $validator->validateCart( $field_value, $data['form_id'] );
                    break;
                default:
            }
        }

        return $validator->getErrors();
    }

    /**
     * Save all data and create appointment.
     *
     * @param int $payment_id
     * @return Entities\CustomerAppointment[]
     */
    public function save( $payment_id = null )
    {
        $user_id  = get_current_user_id();
        $customer = new Entities\Customer();
        if ( $user_id > 0 ) {
            // Try to find customer by WP user ID.
            $customer->loadBy( array( 'wp_user_id' => $user_id ) );
        }
        if ( ! $customer->isLoaded() ) {
            // If customer with such name & e-mail exists, append new booking to him, otherwise - create new customer
            $customer->loadBy( array(
                'name'  => $this->get( 'name' ),
                'email' => $this->get( 'email' ),
            ) );
        }
        $customer->set( 'email', $this->get( 'email' ) );
        $customer->set( 'name',  $this->get( 'name' ) );
        $customer->set( 'phone', $this->get( 'phone' ) );
        if ( get_option( 'ab_settings_create_account', 0 ) && ! $customer->get( 'wp_user_id' ) ) {
            // Create WP user and link it to customer.
            $customer->setWPUser( $user_id );
        }
        $customer->save();

        return $this->cart->save( $customer, $payment_id, $this->get( 'time_zone_offset' ), $this->booking_numbers );
    }

    /**
     * Get coupon.
     *
     * @return Entities\Coupon|false
     */
    public function getCoupon()
    {
        if ( $this->coupon === null ) {
            $coupon = new Entities\Coupon();
            $coupon->loadBy( array(
                'code' => $this->get( 'coupon' ),
            ) );
            if ( $coupon->isLoaded() && $coupon->get( 'used' ) < $coupon->get( 'usage_limit' ) ) {
                $this->coupon = $coupon;
            } else {
                $this->coupon = false;
            }
        }

        return $this->coupon;
    }

    /**
     * Set payment ( PayPal, 2Checkout, PayU Latam, Payson, Mollie ) transaction status.
     *
     * @param string $gateway
     * @param string $status
     * @param mixed  $data
     */
    public function setPaymentStatus( $gateway, $status, $data = null )
    {
        Session::setFormVar( $this->form_id, 'payment', array(
            'gateway' => $gateway,
            'status'  => $status,
            'data'    => $data,
        ) );
    }

    /**
     * Get and clear ( PayPal, 2Checkout, PayU Latam, Payson ) transaction status.
     *
     * @return array|false
     */
    public function extractPaymentStatus()
    {
        if ( $status = Session::getFormVar( $this->form_id, 'payment' ) ) {
            Session::destroyFormVar( $this->form_id, 'payment' );

            return $status;
        }

        return false;
    }

    /**
     * Get booking numbers.
     *
     * @return array
     */
    public function getBookingNumbers()
    {
        return $this->booking_numbers;
    }

}