<?php
namespace BooklyLite\Lib\DataHolders\Booking;

use BooklyLite\Lib;

/**
 * Class Series
 * @package BooklyLite\Lib\DataHolders\Booking
 */
class Series extends Item
{
    /** @var Lib\Entities\Series */
    protected $series;
    /** @var Item[] */
    protected $items = array();

    /**
     * Constructor.
     *
     * @param Lib\Entities\Series $series
     */
    public function __construct( Lib\Entities\Series $series )
    {
        $this->type   = Item::TYPE_SERIES;
        $this->series = $series;
    }

    /**
     * Get series.
     *
     * @return Lib\Entities\Series
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * Add item.
     *
     * @param Item $item
     * @return $this
     */
    public function addItem( Item $item )
    {
        $this->items[] = $item;

        return $this;
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
     * Get service.
     *
     * @return Lib\Entities\Service
     */
    public function getService()
    {
        return $this->items[0]->getService();
    }

    /**
     * Get staff.
     *
     * @return Lib\Entities\Staff
     */
    public function getStaff()
    {
        return $this->items[0]->getStaff();
    }

    /**
     * Get appointment.
     *
     * @return Lib\Entities\Appointment
     */
    public function getAppointment()
    {
        return $this->items[0]->getAppointment();
    }

    /**
     * Get customer appointment.
     *
     * @return Lib\Entities\CustomerAppointment
     */
    public function getCA()
    {
        return $this->items[0]->getCA();
    }

    /**
     * Get service price.
     *
     * @return float
     */
    public function getServicePrice()
    {
        return $this->items[0]->getServicePrice();
    }

    /**
     * Get total price.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        $price = 0.0;
        $break_on_first = get_option( 'bookly_recurring_appointments_payment' ) == 'first';
        foreach ( $this->items as $item ) {
            $price += $item->getTotalPrice();
            if ( $break_on_first ) {
                break;
            }
        }

        return $price;
    }

    /**
     * Get deposit.
     *
     * @return string
     */
    public function getDeposit()
    {
        return $this->items[0]->getDeposit();
    }

    /**
     * Create new item.
     *
     * @param Lib\Entities\Series $series
     * @return static
     */
    public static function create( Lib\Entities\Series $series )
    {
        return new static( $series );
    }
}