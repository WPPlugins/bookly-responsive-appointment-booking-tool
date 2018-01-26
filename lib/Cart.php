<?php
namespace BooklyLite\Lib;

use BooklyLite\Lib\DataHolders\Booking as DataHolders;
use BooklyLite\Lib\Utils\Common;

/**
 * Class Cart
 * @package BooklyLite\Lib
 */
class Cart
{
    /**
     * @var CartItem[]
     */
    private $items = array();

    /**
     * @var UserBookingData
     */
    private $userData = null;

    /**
     * Constructor.
     *
     * @param UserBookingData $userData
     */
    public function __construct( UserBookingData $userData )
    {
        $this->userData = $userData;
    }

    /**
     * Get cart item.
     *
     * @param integer $key
     * @return CartItem|false
     */
    public function get( $key )
    {
        if ( isset ( $this->items[ $key ] ) ) {
            return $this->items[ $key ];
        }

        return false;
    }

    /**
     * Add cart item.
     *
     * @param CartItem $item
     * @return integer
     */
    public function add( CartItem $item )
    {
        $this->items = array( $item );
        end( $this->items );

        return key( $this->items );
    }

    /**
     * Replace given item with other items.
     *
     * @param integer $key
     * @param CartItem[] $items
     * @return array
     */
    public function replace( $key, array $items )
    {
        $new_items = array();
        $new_keys  = array();
        $new_key   = 0;
        foreach ( $this->items as $cart_key => $cart_item ) {
            if ( $cart_key == $key ) {
                foreach ( $items as $item ) {
                    $new_items[ $new_key ] = $item;
                    $new_keys[] = $new_key;
                    ++ $new_key;
                }
            } else {
                $new_items[ $new_key ++ ] = $cart_item;
            }
        }
        $this->items = $new_items;

        return $new_keys;
    }

    /**
     * Drop cart item.
     *
     * @param integer $key
     */
    public function drop( $key )
    {
        $this->items = array();
    }

    /**
     * Get cart items.
     *
     * @return CartItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get items data as array.
     *
     * @return array
     */
    public function getItemsData()
    {
        foreach ( $this->items as $key => $item ) {
            $data = array();
            $data[ $key ] = $item->getData();
            return $data;
        }

        return array();
    }

    /**
     * Set items data from array.
     *
     * @param array $data
     */
    public function setItemsData( array $data )
    {
        foreach ( $data as $key => $item_data ) {
            $item = new CartItem();
            $item->setData( $item_data );
            $this->items[ $key ] = $item;
            break;
        }
    }

    /**
     * Save all cart items (customer appointments).
     *
     * @param DataHolders\Order $order
     * @param string $time_zone
     * @param int    $time_zone_offset
     * @param array  $booking_numbers
     * @return DataHolders\Order
     */
    public function save( DataHolders\Order $order, $time_zone, $time_zone_offset, &$booking_numbers )
    {
        foreach ( $this->getItems() as $i => $cart_item ) {
            // Init.
            $payment_id = $order->hasPayment() ? $order->getPayment()->getId() : null;
            $service    = $cart_item->getService();
            $series     = null;
            $compound   = null;

            // Compound.
            if ( $service->getType() == Entities\Service::TYPE_COMPOUND ) {
                $compound = DataHolders\Compound::create( $service )
                    ->setToken( Utils\Common::generateToken(
                        '\BooklyLite\Lib\Entities\CustomerAppointment',
                        'compound_token'
                    ) )
                ;
            }

            // Series.
            if ( $series_unique_id = $cart_item->getSeriesUniqueId() ) {
                if ( $order->hasItem( $series_unique_id ) ) {
                    $series = $order->getItem( $series_unique_id );
                } else {
                    $series_entity = new Entities\Series();
                    $series_entity
                        ->setRepeat( '{}' )
                        ->setToken( Common::generateToken( get_class( $series_entity ), 'token' ) )
                        ->save()
                    ;

                    $series = DataHolders\Series::create( $series_entity );
                    $order->addItem( $series_unique_id, $series );
                }
                if ( get_option( 'bookly_recurring_appointments_payment' ) == 'first' && ! $cart_item->getFirstInSeries() ) {
                    // Link payment with the first item only.
                    $payment_id = null;
                }
            }

            $extras = json_encode( $cart_item->getExtras() );
            $custom_fields = json_encode( $cart_item->getCustomFields() );

            foreach ( $cart_item->getSlots() as $slot ) {
                list ( $service_id, $staff_id, $datetime ) = $slot;
                $service = Entities\Service::find( $service_id );

                /*
                 * Get appointment with the same params.
                 * If it exists -> create connection to this appointment,
                 * otherwise create appointment and connect customer to new appointment
                 */
                $appointment = new Entities\Appointment();
                $appointment->loadBy( array(
                    'service_id' => $service_id,
                    'staff_id'   => 1,
                    'start_date' => $datetime,
                ) );
                if ( $appointment->isLoaded() == false ) {
                    // Create new appointment.
                    $appointment
                        ->setSeriesId( $series ? $series->getSeries()->getId() : null )
                        ->setLocationId( $cart_item->getLocationId() ?: null )
                        ->setServiceId( $service_id )
                        ->setStaffId( 1 )
                        ->setStaffAny( count( $cart_item->getStaffIds() ) > 1 )
                        ->setStartDate( $datetime )
                        ->setEndDate( date( 'Y-m-d H:i:s', strtotime( $datetime ) + $service->getDuration() ) )
                        ->save();
                } else {
                    $update = false;
                    if ( ! $appointment->getLocationId() && $cart_item->getLocationId() ) {
                        // Set location if it was not set previously.
                        $appointment->setLocationId( $cart_item->getLocationId() );
                        $update = true;
                    }
                    if ( $appointment->getStaffAny() == 1 && count( $cart_item->getStaffIds() ) == 1 ) {
                        // Remove marker Any for staff
                        $appointment->setStaffAny( 0 );
                        $update = true;
                    }
                    if ( $update ) {
                        $appointment->save();
                    }
                }

                // Create CustomerAppointment record.
                $customer_appointment = new Entities\CustomerAppointment();
                $customer_appointment
                    ->setCustomer( $order->getCustomer() )
                    ->setAppointment( $appointment )
                    ->setPaymentId( $payment_id )
                    ->setNumberOfPersons( $cart_item->getNumberOfPersons() )
                    ->setNotes( $this->userData->getNotes() )
                    ->setExtras( $extras )
                    ->setCustomFields( $custom_fields )
                    ->setStatus( get_option( 'bookly_gen_default_appointment_status' ) )
                    ->setTimeZone( $time_zone )
                    ->setTimeZoneOffset( $time_zone_offset )
                    ->setCompoundServiceId( $compound ? $compound->getService()->getId() : null )
                    ->setCompoundToken( $compound ? $compound->getToken() : null )
                    ->setCreatedFrom( 'frontend' )
                    ->setCreated( current_time( 'mysql' ) )
                    ->save();

                // Handle extras duration.
                if ( Config::serviceExtrasEnabled() ) {
                    $appointment
                        ->setExtrasDuration( $appointment->getMaxExtrasDuration() )
                        ->save();
                }

                // Google Calendar.
                $appointment->handleGoogleCalendar();

                // Add booking number.
                $booking_numbers[] = $appointment->getId();

                // Only first appointment should have custom fields, extras (compound).
                $custom_fields = $extras = '[]';

                // Add entities to result.
                $item = DataHolders\Simple::create( $customer_appointment )
                    ->setService( $service )
                    ->setAppointment( $appointment )
                ;
                if ( $compound ) {
                    $item = $compound->addItem( $item );
                }
                if ( $series ) {
                    $series->addItem( $item );
                } else {
                    $order->addItem( $i, $item );
                }
            }
        }

        $booking_numbers = array_unique( $booking_numbers );

        return $order;
    }

    /**
     * Get total and deposit for cart.
     *
     * @param bool $apply_coupon
     * @return array
     */
    public function getInfo( $apply_coupon = true )
    {
        $total  = $deposit = $item_price = 0;
        $coupon = false;
        $before_coupon   = 0;
        $coupon_services = array();
        if ( $apply_coupon && $coupon = $this->userData->getCoupon() ) {
            $coupon_services = (array) Proxy\Coupons::getServiceIds( $coupon );
        }

        foreach ( $this->items as $key => $item ) {
            if (
                $item->getSeriesUniqueId()
                && get_option( 'bookly_recurring_appointments_payment' ) === 'first'
                && ( ! $item->getFirstInSeries() )
            ) {
                continue;
            }

            // Cart contains a service that was already removed/deleted from Bookly (WooCommerce BP-224)
            if ( $item->getService() ) {
                $discount   = in_array( $item->getService()->getId(), $coupon_services );
                $item_price = $item->getServicePrice() * $item->getNumberOfPersons();
                if ( $discount ) {
                    $before_coupon += $item_price;
                }
            }

            $total   += $item_price;
            $deposit += Proxy\DepositPayments::prepareAmount( $item_price, $item->getDeposit(), $item->getNumberOfPersons() );
        }

        if ( $coupon ) {
            $total -= ( $before_coupon - $coupon->apply( $before_coupon ) );
            if ( $deposit > $total ) {
                $deposit = $total;
            }
        }

        if ( ! Config::depositPaymentsEnabled() ) {
            $due = 0;
        } else {
            $due = max( $total - $deposit, 0 );
        }

        // coupon discount=10%, deduction=10
        // cart_item price=70, staff_deposit=50, coupon=on
        // cart_item price=30, staff_deposit=20, coupon=off
        //
        //            total deposit due
        // Array like [ 83,   70,   13 ]

        return array( $total, $deposit, $due );
    }

    /**
     * Generate title of cart items (used in payments).
     *
     * @param int  $max_length
     * @param bool $multi_byte
     * @return string
     */
    public function getItemsTitle( $max_length = 255, $multi_byte = true )
    {
        reset( $this->items );
        $title = $this->get( key( $this->items ) )->getService()->getTranslatedTitle();
        $tail  = '';
        $more  = count( $this->items ) - 1;
        if ( $more > 0 ) {
            $tail = sprintf( _n( ' and %d more item', ' and %d more items', $more, 'bookly' ), $more );
        }

        if ( $multi_byte ) {
            if ( preg_match_all( '/./su', $title . $tail, $matches ) > $max_length ) {
                $length_tail = preg_match_all( '/./su', $tail, $matches );
                $title       = preg_replace( '/^(.{' . ( $max_length - $length_tail - 3 ) . '}).*/su', '$1', $title ) . '...';
            }
        } else {
            if ( strlen( $title . $tail ) > $max_length ) {
                while ( strlen( $title . $tail ) + 3 > $max_length ) {
                    $title = preg_replace( '/.$/su', '', $title );
                }
                $title .= '...';
            }
        }

        return $title . $tail;
    }

    /**
     * Return cart_key for not available appointment or NULL.
     *
     * @return int|null
     */
    public function getFailedKey()
    {
        $max_date  = date_create( '@' . ( current_time( 'timestamp' ) + Config::getMaximumAvailableDaysForBooking() * DAY_IN_SECONDS ) )->setTime( 0, 0 );

        foreach ( $this->items as $cart_key => $cart_item ) {
            if( $cart_item->getService() ) {
                $service     = $cart_item->getService();
                $is_compound = $service->getType() == Entities\Service::TYPE_COMPOUND;
                foreach ( $cart_item->getSlots() as $slot ) {
                    list ( $service_id, $null, $datetime ) = $slot;
                    if ( $is_compound ) {
                        $service = Entities\Service::find( $service_id );
                    }
                    $bound_start = date_create( $datetime )->modify( '-' . (int) $service->getPaddingLeft() . ' sec' );
                    $bound_end   = date_create( $datetime )->modify( ( (int) $service->getDuration() + (int) $service->getPaddingRight() + $cart_item->getExtrasDuration() ) . ' sec' );

                    if ( $bound_end < $max_date ) {
                        $query = Entities\CustomerAppointment::query( 'ca' )
                            ->select( 'ss.capacity_max, SUM(ca.number_of_persons) AS total_number_of_persons,
                                a.start_date,
                                a.end_date' )
                            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
                            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
                            ->where( 'a.staff_id', 1 )
                            ->whereIn( 'ca.status', array( Entities\CustomerAppointment::STATUS_PENDING, Entities\CustomerAppointment::STATUS_APPROVED ) )
                            ->groupBy( 'a.service_id, a.start_date' )
                            ->havingRaw( '%s > bound_left AND bound_right > %s AND ( total_number_of_persons + %d ) > ss.capacity_max',
                                array( $bound_end->format( 'Y-m-d H:i:s' ), $bound_start->format( 'Y-m-d H:i:s' ), $cart_item->getNumberOfPersons() ) )
                            ->limit( 1 );
                        $rows  = $query->execute( Query::HYDRATE_NONE );

                        if ( $rows != 0 ) {
                            // Exist intersect appointment, time not available.
                            return $cart_key;
                        }
                    }
                }
            }
        }

        return null;
    }

}