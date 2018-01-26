<?php
namespace BooklyLite\Lib\DataHolders\Booking;

use BooklyLite\Lib;

/**
 * Class Compound
 * @package BooklyLite\Lib\DataHolders\Booking
 */
class Compound extends Item
{
    /** @var Lib\Entities\Service */
    protected $compound_service;
    /** @var string */
    protected $compound_token;
    /** @var Simple[] */
    protected $items = array();

    /**
     * Constructor.
     *
     * @param Lib\Entities\Service $compound_service
     */
    public function __construct( Lib\Entities\Service $compound_service )
    {
        $this->type = Item::TYPE_COMPOUND;
        $this->compound_service = $compound_service;
    }

    /**
     * Get compound service.
     *
     * @return Lib\Entities\Service
     */
    public function getService()
    {
        return $this->compound_service;
    }

    /**
     * Set compound token.
     *
     * @param string $token
     * @return $this
     */
    public function setToken( $token )
    {
        $this->compound_token = $token;

        return $this;
    }

    /**
     * Get compound token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->compound_token;
    }

    /**
     * Add item.
     *
     * @param Simple $item
     * @return $this
     */
    public function addItem( Simple $item )
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Get items.
     *
     * @return Simple[]
     */
    public function getItems()
    {
        return $this->items;
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
        return $this->compound_service->getPrice();
    }

    /**
     * Get total price.
     *
     * @return float
     */
    public function getTotalPrice()
    {
        // Service price.
        $service_price = $this->getServicePrice();

        // Extras.
        $extras = (array) Lib\Proxy\ServiceExtras::getInfo( json_decode( $this->getCA()->getExtras(), true ), true );
        $extras_total_price = 0.0;
        foreach ( $extras as $extra ) {
            $extras_total_price += $extra['price'];
        }

        return ( $service_price + $extras_total_price ) * $this->getCA()->getNumberOfPersons();
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
     * @param Lib\Entities\Service $compound_service
     * @return static
     */
    public static function create( Lib\Entities\Service $compound_service )
    {
        return new static( $compound_service );
    }

    /**
     * Create from simple item.
     *
     * @param Simple $item
     * @return static
     */
    public static function createFromSimple( Simple $item )
    {
        return static::create( Lib\Entities\Service::find( $item->getCA()->getCompoundServiceId() ) )->addItem( $item );
    }
}