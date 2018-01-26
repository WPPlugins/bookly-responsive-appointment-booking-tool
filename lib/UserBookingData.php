<?php
namespace BooklyLite\Lib;

/**
 * Class UserBookingData
 * @package BooklyLite\Frontend\Modules\Booking\Lib
 */
class UserBookingData
{
    // Protected properties

    // Step 0
    /** @var  string */
    protected $time_zone;
    /** @var  int */
    protected $time_zone_offset;

    // Step service
    /** @var  string Y-m-d */
    protected $date_from;
    /** @var  array */
    protected $days;
    /** @var  string H:i*/
    protected $time_from;
    /** @var  string H:i*/
    protected $time_to;

    // Step time
    protected $slots = array();

    // Step details
    /** @var  string */
    protected $full_name;
    /** @var  string */
    protected $first_name;
    /** @var  string */
    protected $last_name;
    /** @var  string */
    protected $email;
    /** @var  string */
    protected $phone;
    /** @var  string */
    protected $notes;

    // Step payment
    /** @var  string */
    protected $coupon_code;

    // Cart item keys being edited
    /** @var  array */
    protected $edit_cart_keys = array();
    /** @var  bool */
    protected $repeated = 0;
    /** @var  array */
    protected $repeat_data = array();

    // Private

    /** @var  string */
    private $form_id;

    // Frontend expect variables
    private $properties = array(
        // Step 0
        'time_zone',
        'time_zone_offset',
        // Step service
        'date_from',
        'days',
        'time_from',
        'time_to',
        // Step time
        'slots',
        // Step details
        'full_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'notes',
        // Step payment
        'coupon_code',
        // Cart item keys being edited
        'edit_cart_keys',
        'repeated',
        'repeat_data',
    );

    /** @var \BooklyCoupons\Lib\Entities\Coupon|null */
    private $coupon;
    /** @var array */
    private $booking_numbers = array();
    /** @var integer|null */
    private $payment_id;

    // Public

    /** @var Cart */
    public $cart;
    /** @var Chain */
    public $chain;

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
                $this
                    ->setFullName( $customer->getFullName() )
                    ->setFirstName( $customer->getFirstName() )
                    ->setLastName( $customer->getLastName() )
                    ->setEmail( $customer->getEmail() )
                    ->setPhone( $customer->getPhone() );
            } else {
                $this
                    ->setFullName( $current_user->display_name )
                    ->setFirstName( $current_user->user_firstname )
                    ->setLastName( $current_user->user_lastname )
                    ->setEmail( $current_user->user_email );
            }
        } elseif ( get_option( 'bookly_cst_remember_in_cookie' ) && isset( $_COOKIE['bookly-cst-full-name'] ) ) {
            $this
                ->setFullName( $_COOKIE['bookly-cst-full-name'] )
                ->setFirstName( $_COOKIE['bookly-cst-first-name'] )
                ->setLastName( $_COOKIE['bookly-cst-last-name'] )
                ->setEmail( $_COOKIE['bookly-cst-email'] )
                ->setPhone( $_COOKIE['bookly-cst-phone'] );
        }

        // Register destructor (should work in cases when regular __destruct() does not work).
        register_shutdown_function( array( $this, 'destruct' ) );
    }

    public function resetChain()
    {
        $this->chain->clear();
        $this->chain->add( new ChainItem() );

        // Set up default parameters.
        $prior_time = Config::getMinimumTimePriorBooking();
        $this->setDateFrom( date( 'Y-m-d', current_time( 'timestamp' ) + $prior_time ) );
        $times = Entities\StaffScheduleItem::query( 'ss' )
            ->select( 'SUBSTRING_INDEX(MIN(ss.start_time), ":", 2) AS min_end_time,
                SUBSTRING_INDEX(MAX(ss.end_time), ":", 2) AS max_end_time' )
            ->leftJoin( 'Staff', 's', 's.id = ss.staff_id' )
            ->whereNot( 'start_time', null )
            // Only for visible staff get working hours.
            ->whereNot( 's.visibility', 'private' )
            ->fetchRow();
        $times = Proxy\Shared::adjustMinAndMaxTimes( $times );
        $this
            ->setTimeFrom( $times['min_end_time'] )
            ->setTimeTo( $times['max_end_time'] )
            ->setSlots( array() )
            ->setEditCartKeys( array() )
            ->setRepeated( 0 )
            ->setRepeatData( array() );
    }

    /**
     * Destructor used in register_shutdown_function.
     */
    public function destruct()
    {
        Session::setFormVar( $this->form_id, 'data',            $this->getFrontendData() );
        Session::setFormVar( $this->form_id, 'cart',            $this->cart->getItemsData() );
        Session::setFormVar( $this->form_id, 'chain',           $this->chain->getItemsData() );
        Session::setFormVar( $this->form_id, 'booking_numbers', $this->booking_numbers );
        Session::setFormVar( $this->form_id, 'payment_id',      $this->payment_id );
        Session::setFormVar( $this->form_id, 'last_touched',    time() );
    }

    /**
     * @return array
     */
    private function getFrontendData()
    {
        $data = array();
        foreach ( $this->properties as $variable_name ) {
            $data[ $variable_name ] = $this->{$variable_name};
        }

        return $data;
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
            $this->fillData( $data );
            $this->chain->setItemsData( Session::getFormVar( $this->form_id, 'chain' ) );
            $this->cart->setItemsData( Session::getFormVar( $this->form_id, 'cart' ) );
            $this->booking_numbers = Session::getFormVar( $this->form_id, 'booking_numbers' );
            $this->payment_id = Session::getFormVar( $this->form_id, 'payment_id' );
            // Client time zone.
            if ( Config::useClientTimeZone() ) {
                $this->applyTimeZone();
            }

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
            if ( in_array( $name, $this->properties ) ) {
                $this->{$name} = $value;
            } elseif ( $name == 'chain' ) {
                $chain_items = $this->chain->getItems();
                $this->chain->clear();
                foreach ( $value as $key => $_data ) {
                    $item = isset ( $chain_items[ $key ] ) ? $chain_items[ $key ] : new ChainItem();
                    $item->setData( $_data );
                    $this->chain->add( $item );
                }
            } elseif ( $name == 'cart' ) {
                foreach ( $value as $key => $_data ) {
                    $this->cart->get( $key )->setData( $_data );
                }
            } elseif ( $name === 'repeat' ) {
                $this->setRepeated( $value );
            } elseif ( $name === 'unrepeat' ) {
                $this
                    ->setRepeated( 0 )
                    ->setRepeatData( array() );
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
        $this
            ->setDateFrom( $cart_item->getDateFrom() )
            ->setDays( $cart_item->getDays() )
            ->setTimeFrom( $cart_item->getTimeFrom() )
            ->setTimeTo( $cart_item->getTimeTo() )
            ->setSlots( $cart_item->getSlots() )
            ->setRepeated( 0 )
            ->setRepeatData( array() );

        $chain_item = new ChainItem();
        $chain_item
            ->setServiceId( $cart_item->getServiceId() )
            ->setStaffIds( $cart_item->getStaffIds() )
            ->setNumberOfPersons( $cart_item->getNumberOfPersons() )
            ->setExtras( $cart_item->getExtras() )
            ->setSeriesUniqueId( $cart_item->getSeriesUniqueId() )
            ->setQuantity( 1 );

        $this->chain->clear();
        $this->chain->add( $chain_item );
    }

    /**
     * Add chain items to cart.
     *
     * @return $this
     */
    public function addChainToCart()
    {
        $cart_items     = array();
        $edit_cart_keys = $this->getEditCartKeys();
        $eck_idx        = 0;
        $slots          = $this->getSlots();
        $slots_idx      = 0;
        $repeated       = $this->getRepeated() ?: 1;
        if ( $this->getRepeated() ) {
            $series_unique_id = mt_rand( 1, PHP_INT_MAX );
        } else {
            $series_unique_id = 0;
        }

        $cart_items_repeats = array();
        for ( $i = 0; $i < $repeated; $i++ ) {
            $items_in_repeat = array();
            foreach ( $this->chain->getItems() as $chain_item ) {
                for ( $q = 0; $q < $chain_item->getQuantity(); ++ $q ) {
                    $cart_item_slots = array();

                    if ( $chain_item->getService()->getType() == Entities\Service::TYPE_COMPOUND ) {
                        foreach ( $chain_item->getSubServices() as $sub_service ) {
                            $cart_item_slots[] = $slots[ $slots_idx ++ ];
                        }
                    } else {
                        $cart_item_slots[] = $slots[ $slots_idx ++ ];
                    }
                    $cart_item = new CartItem();

                    $cart_item
                        ->setDateFrom( $this->getDateFrom() )
                        ->setDays( $this->getDays() )
                        ->setTimeFrom( $this->getTimeFrom() )
                        ->setTimeTo( $this->getTimeTo() );

                    $cart_item
                        ->setSeriesUniqueId( $chain_item->getSeriesUniqueId()?: $series_unique_id )
                        ->setExtras( $chain_item->getExtras() )
                        ->setLocationId( $chain_item->getLocationId() )
                        ->setNumberOfPersons( $chain_item->getNumberOfPersons() )
                        ->setServiceId( $chain_item->getServiceId() )
                        ->setSlots( $cart_item_slots )
                        ->setStaffIds( $chain_item->getStaffIds() )
                        ->setFirstInSeries( false );
                    if ( isset ( $edit_cart_keys[ $eck_idx ] ) ) {
                        $cart_item->setCustomFields( $this->cart->get( $edit_cart_keys[ $eck_idx ] )->getCustomFields() );
                        ++ $eck_idx;
                    }

                    $items_in_repeat[] = $cart_item;
                }
            }
            $cart_items_repeats[] = $items_in_repeat;
        }

        /**
         * Searching for minimum time to find first client visiting
         */
        $first_visit_time = $slots[0][2];
        $first_visit_repeat = 0;
        foreach ( $cart_items_repeats as $repeat_id => $items_in_repeat ) {
            foreach ( $items_in_repeat as $cart_item ) {
                /** @var CartItem $cart_item */
                $slots = $cart_item->getSlots();
                foreach ( $slots as $slot ) {
                    if ( $slot[2] < $first_visit_time ) {
                        $first_visit_time   = $slots[2];
                        $first_visit_repeat = $repeat_id;
                    }
                }
            }

        }
        foreach ( $cart_items_repeats[ $first_visit_repeat ] as $cart_item ) {
            /** @var CartItem $cart_item */
            $cart_item->setFirstInSeries( true );
        }

        foreach ( $cart_items_repeats as $items_in_repeat ) {
            $cart_items = array_merge( $cart_items, $items_in_repeat );
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

        $this->setEditCartKeys( $inserted_keys );

        return $this;
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
                    $validator->validateDate( $field_name, $field_value, true );
                    break;
                case 'time_from':
                case 'time_to':
                    $validator->validateTime( $field_name, $field_value, true );
                    break;
                case 'full_name':
                case 'first_name':
                case 'last_name':
                    $validator->validateName( $field_name, $field_value );
                    break;
                case 'email':
                    $validator->validateEmail( $field_name, $data );
                    break;
                case 'phone':
                    $validator->validatePhone( $field_name, $field_value, Config::phoneRequired() );
                    break;
                case 'cart':
                    $validator->validateCart( $field_value, $data['form_id'] );
                    break;
                default:
            }
        }
        // Post validators.
        if ( isset ( $data['phone'] ) || isset ( $data['email'] ) ) {
            $validator->postValidateCustomer( $data, $this );
        }

        return $validator->getErrors();
    }

    /**
     * Save all data and create appointment.
     *
     * @param Entities\Payment $payment
     * @return DataHolders\Booking\Order
     */
    public function save( $payment = null )
    {
        // Find or create customer.
        $user_id  = get_current_user_id();
        $customer = new Entities\Customer();
        if ( $user_id > 0 ) {
            // Try to find customer by WP user ID.
            $customer->loadBy( array( 'wp_user_id' => $user_id ) );
        }
        if ( ! $customer->isLoaded() ) {
            // Try to find customer by phone or email.
            $customer->loadBy(
                Config::phoneRequired()
                    ? array( 'phone' => $this->getPhone() )
                    : array( 'email' => $this->getEmail() )
            );
            if ( ! $customer->isLoaded() ) {
                // Try to find customer by 'secondary' identifier, otherwise create new customer.
                $customer->loadBy(
                    Config::phoneRequired()
                        ? array( 'email' => $this->getEmail(), 'phone' => '' )
                        : array( 'phone' => $this->getPhone(), 'email' => '' )
                );
            }
        }
        // Overwrite only if value is not empty.
        $this->getFullName()  ? $customer->setFullName( $this->getFullName() ) : null;
        $this->getFirstName() ? $customer->setFirstName( $this->getFirstName() ) : null;
        $this->getLastName()  ? $customer->setLastName( $this->getLastName() ) : null;
        $this->getPhone()     ? $customer->setPhone( $this->getPhone() ) : null;
        $this->getEmail()     ? $customer->setEmail( $this->getEmail() ) : null;

        if ( get_option( 'bookly_cst_create_account', 0 ) && ! $customer->getWpUserId() ) {
            // Create WP user and link it to customer.
            $customer->setWpUserId( $user_id );
        }
        $customer->save();

        // Order.
        $order = DataHolders\Booking\Order::create( $customer );

        // Payment.
        if ( $payment ) {
            $order->setPayment( $payment );
            $this->payment_id = $payment->getId();
        }

        if ( get_option( 'bookly_cst_remember_in_cookie' ) ) {
            setcookie( 'bookly-cst-full-name',  $customer->getFullName(),  time() + YEAR_IN_SECONDS );
            setcookie( 'bookly-cst-first-name', $customer->getFirstName(),  time() + YEAR_IN_SECONDS );
            setcookie( 'bookly-cst-last-name',  $customer->getLastName(),  time() + YEAR_IN_SECONDS );
            setcookie( 'bookly-cst-phone',      $customer->getPhone(), time() + YEAR_IN_SECONDS );
            setcookie( 'bookly-cst-email',      $customer->getEmail(), time() + YEAR_IN_SECONDS );
        }

        return $this->cart->save( $order, $this->getTimeZone(), $this->getTimeZoneOffset(), $this->booking_numbers );
    }

    /**
     * Get form ID.
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->form_id;
    }

    /**
     * Get coupon.
     *
     * @return \BooklyCoupons\Lib\Entities\Coupon|null
     */
    public function getCoupon()
    {
        return null;
    }

    /**
     * Set payment ( PayPal, 2Checkout, PayU Latam, Mollie ) transaction status.
     *
     * @param string $gateway
     * @param string $status
     * @param mixed  $data
     * @todo use $status as const
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

    /**
     * Get payment ID.
     *
     * @return int|null
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * Apply client time zone.
     *
     * @return $this
     */
    public function applyTimeZone()
    {
        if ( $this->getTimeZoneOffset() !== null ) {
            Slots\TimePoint::$client_timezone_offset = - $this->getTimeZoneOffset() * MINUTE_IN_SECONDS;
            Slots\DatePoint::$client_timezone = $this->getTimeZone() ?: Utils\DateTime::guessTimeZone( Slots\TimePoint::$client_timezone_offset );
        }

        return $this;
    }

    /**************************************************************************
     * UserData Getters & Setters                                             *
     **************************************************************************/

    /**
     * Gets time_zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        return $this->time_zone;
    }

    /**
     * Sets time_zone
     *
     * @param string $time_zone
     * @return $this
     */
    public function setTimeZone( $time_zone )
    {
        $this->time_zone = $time_zone;

        return $this;
    }

    /**
     * Gets time_zone_offset
     *
     * @return int
     */
    public function getTimeZoneOffset()
    {
        return $this->time_zone_offset;
    }

    /**
     * Sets time_zone_offset
     *
     * @param int $time_zone_offset
     * @return $this
     */
    public function setTimeZoneOffset( $time_zone_offset )
    {
        $this->time_zone_offset = $time_zone_offset;

        return $this;
    }

    /**
     * Gets date_from
     *
     * @return string
     */
    public function getDateFrom()
    {
        return $this->date_from;
    }

    /**
     * Sets date_from
     *
     * @param string $date_from
     * @return $this
     */
    public function setDateFrom( $date_from )
    {
        $this->date_from = $date_from;

        return $this;
    }

    /**
     * Gets days
     *
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Sets days
     *
     * @param array $days
     * @return $this
     */
    public function setDays( $days )
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Gets time_from
     *
     * @return string
     */
    public function getTimeFrom()
    {
        return $this->time_from;
    }

    /**
     * Sets time_from
     *
     * @param string $time_from
     * @return $this
     */
    public function setTimeFrom( $time_from )
    {
        $this->time_from = $time_from;

        return $this;
    }

    /**
     * Gets time_to
     *
     * @return string
     */
    public function getTimeTo()
    {
        return $this->time_to;
    }

    /**
     * Sets time_to
     *
     * @param string $time_to
     * @return $this
     */
    public function setTimeTo( $time_to )
    {
        $this->time_to = $time_to;

        return $this;
    }

    /**
     * Gets slots
     *
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Sets slots
     *
     * @param array $slots
     * @return $this
     */
    public function setSlots( $slots )
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * Gets full_name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * Sets full_name
     *
     * @param string $full_name
     * @return $this
     */
    public function setFullName( $full_name )
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * Gets first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Sets first_name
     *
     * @param string $first_name
     * @return $this
     */
    public function setFirstName( $first_name )
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * Gets last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Sets last_name
     *
     * @param string $last_name
     * @return $this
     */
    public function setLastName( $last_name )
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * Gets email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail( $email )
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone( $phone )
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Sets notes
     *
     * @param string $notes
     * @return $this
     */
    public function setNotes( $notes )
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Gets coupon_code
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->coupon_code;
    }

    /**
     * Sets coupon_code
     *
     * @param string $coupon_code
     * @return $this
     */
    public function setCouponCode( $coupon_code )
    {
        $this->coupon_code = $coupon_code;

        return $this;
    }

    /**
     * Gets edit_cart_keys
     *
     * @return array
     */
    public function getEditCartKeys()
    {
        return $this->edit_cart_keys;
    }

    /**
     * Sets edit_cart_keys
     *
     * @param array $edit_cart_keys
     * @return $this
     */
    public function setEditCartKeys( $edit_cart_keys )
    {
        $this->edit_cart_keys = $edit_cart_keys;

        return $this;
    }

    /**
     * Gets repeated
     *
     * @return bool
     */
    public function getRepeated()
    {
        return $this->repeated;
    }

    /**
     * Sets repeated
     *
     * @param bool $repeated
     * @return $this
     */
    public function setRepeated( $repeated )
    {
        $this->repeated = $repeated;

        return $this;
    }

    /**
     * Gets repeat_data
     *
     * @return array
     */
    public function getRepeatData()
    {
        return $this->repeat_data;
    }

    /**
     * Sets repeat_data
     *
     * @param array $repeat_data
     * @return $this
     */
    public function setRepeatData( $repeat_data )
    {
        $this->repeat_data = $repeat_data;

        return $this;
    }
}