<?php
namespace Bookly\Lib\DataHolders\Booking;

use Bookly\Lib;

/**
 * Class Order
 * @package Bookly\Lib\DataHolders\Booking
 */
class Order
{
    /** @var Lib\Entities\Customer */
    protected $customer;
    /** @var Lib\Entities\Payment */
    protected $payment;
    /** @var Item[] */
    protected $items = array();

    /**
     * Constructor.
     *
     * @param Lib\Entities\Customer $customer
     */
    public function __construct( Lib\Entities\Customer $customer )
    {
        $this->customer = $customer;
    }

    /**
     * Get customer.
     *
     * @return Lib\Entities\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set payment.
     *
     * @param Lib\Entities\Payment $payment
     * @return $this
     */
    public function setPayment( Lib\Entities\Payment $payment )
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Check if payment exists.
     *
     * @return bool
     */
    public function hasPayment()
    {
        return (bool) $this->payment;
    }

    /**
     * Get payment.
     *
     * @return Lib\Entities\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Add item.
     *
     * @param string $id
     * @param Item $item
     * @return $this
     */
    public function addItem( $id, Item $item )
    {
        $this->items[ $id ] = $item;

        return $this;
    }

    /**
     * Check if item exists.
     *
     * @param string $id
     * @return bool
     */
    public function hasItem( $id )
    {
        return isset ( $this->items[ $id ] );
    }

    /**
     * Get item.
     *
     * @param string $id
     * @return Item
     */
    public function getItem( $id )
    {
        return $this->items[ $id ];
    }

    /**
     * Get items.
     *
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get flat array of items.
     *
     * @return Item[]
     */
    public function getFlatItems()
    {
        $result = array();
        foreach ( $this->items as $item ) {
            if ( $item->isSeries() ) {
                /** @var Series $item */
                $result = array_merge( $result, $item->getItems() );
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Create new order.
     *
     * @param Lib\Entities\Customer $customer
     * @return static
     */
    public static function create( Lib\Entities\Customer $customer )
    {
        return new static( $customer );
    }

    /**
     * Create new order from item.
     *
     * @param Item $item
     * @return static
     */
    public static function createFromItem( Item $item )
    {
        $order = static::create( Lib\Entities\Customer::find( $item->getCA()->getCustomerId() ) )->addItem( 0, $item );

        if ( $item->getCA()->getPaymentId() ) {
            $order->setPayment( Lib\Entities\Payment::find( $item->getCA()->getPaymentId() ) );
        }

        return $order;
    }

    /**
     * Create Order from payment.
     *
     * @param Lib\Entities\Payment $payment
     * @return Order|null
     */
    public static function createFromPayment( Lib\Entities\Payment $payment )
    {
        /** @var Lib\Entities\CustomerAppointment[] $ca_list */
        $ca_list = Lib\Entities\CustomerAppointment::query()->where( 'payment_id', $payment->getId() )->find();
        if ( $ca_list ) {
            $customer = Lib\Entities\Customer::find( $ca_list[0]->getCustomerId() );
            $order    = static::create( $customer );
            $order->setPayment( $payment );
            foreach ( $ca_list as $i => $customer_appointment ) {
                $series      = null;
                $compound    = null;
                $appointment = Lib\Entities\Appointment::find( $customer_appointment->getAppointmentId() );

                // Compound.
                if ( $customer_appointment->getCompoundServiceId() !== null ) {
                    $service  = Lib\Entities\Service::find( $customer_appointment->getCompoundServiceId() );
                    $compound = Lib\DataHolders\Booking\Compound::create( $service )
                        ->setToken( $customer_appointment->getCompoundToken() );
                } else {
                    $service  = Lib\Entities\Service::find( $appointment->getServiceId() );
                }

                // Series.
                if ( $series_unique_id = $ca_list[0]->getSeriesId() ) {
                    if ( ! $order->hasItem( $series_unique_id ) ) {
                        $series = Lib\DataHolders\Booking\Series::create( Lib\Entities\Series::find( $series_unique_id ) );
                        $order->addItem( $series_unique_id, $series );
                    }
                }

                $item = Lib\DataHolders\Booking\Simple::create( $customer_appointment )
                    ->setService( $service )
                    ->setAppointment( $appointment );

                if ( $compound ) {
                    $item = $compound->addItem( $item );
                }
                if ( $series ) {
                    $series->addItem( $i, $item );
                } else {
                    $order->addItem( $i, $item );
                }
            }

            return $order;
        }

        return null;
    }

}