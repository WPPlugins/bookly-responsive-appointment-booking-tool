<?php
namespace Bookly\Lib\DataHolders\Booking;

use Bookly\Lib;

/**
 * Class Collaborative
 * @package Bookly\Lib\DataHolders\Booking
 */
class Collaborative extends Item
{
    /** @var Lib\Entities\Service */
    protected $collaborative_service;
    /** @var string */
    protected $collaborative_token;
    /** @var Simple[] */
    protected $items = array();
    /** @var array */
    protected $extras;
    /** @var int */
    protected $service_duration;
    /** @var Lib\Slots\DatePoint */
    protected $total_end;

    /**
     * Constructor.
     *
     * @param Lib\Entities\Service $collaborative_service
     */
    public function __construct( Lib\Entities\Service $collaborative_service )
    {
        $this->type = Item::TYPE_COLLABORATIVE;
        $this->collaborative_service = $collaborative_service;
    }

    /**
     * @inheritdoc
     */
    public function getAppointment()
    {
        return $this->items[0]->getAppointment();
    }

    /**
     * @inheritdoc
     */
    public function getCA()
    {
        return $this->items[0]->getCA();
    }

    /**
     * @inheritdoc
     */
    public function getDeposit()
    {
        return $this->collaborative_service->getDeposit();
    }

    /**
     * @inheritdoc
     */
    public function getExtras()
    {
        if ( $this->extras === null ) {
            $this->extras = array();
            foreach ( $this->items as $item ) {
                $this->extras += $item->getExtras();
            }
        }

        return $this->extras;
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
     * @inheritdoc
     */
    public function getService()
    {
        return $this->collaborative_service;
    }

    /**
     * @inheritdoc
     */
    public function getServiceDuration()
    {
        if ( $this->service_duration === null ) {
            $result = Lib\Entities\SubService::query( 'ss' )
                ->select( 'MAX(s.duration) AS duration' )
                ->leftJoin( 'Service', 's', 's.id = ss.sub_service_id' )
                ->where( 'ss.service_id', $this->collaborative_service->getId() )
                ->fetchRow()
            ;
            $this->service_duration = $result['duration'];
        }

        return $this->service_duration;
    }

    /**
     * @inheritdoc
     */
    public function getServicePrice()
    {
        return $this->collaborative_service->getPrice();
    }

    /**
     * @inheritdoc
     */
    public function getStaff()
    {
        return $this->items[0]->getStaff();
    }

    /**
     * @inheritdoc
     */
    public function getTax()
    {
        if ( ! $this->tax ) {
            $rates = Lib\Proxy\Taxes::getServiceTaxRates();
            if ( $rates ) {
                $this->tax = Lib\Proxy\Taxes::calculateTax( $this->getTotalPrice(), $rates[ $this->getService()->getId() ] );
            }
        }

        return $this->tax;
    }

    /**
     * Set tax.
     *
     * @param float $tax
     * @return $this
     */
    public function setTax( $tax )
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get collaborative token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->collaborative_token;
    }

    /**
     * Set collaborative token.
     *
     * @param string $token
     * @return $this
     */
    public function setToken( $token )
    {
        $this->collaborative_token = $token;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotalEnd()
    {
        if ( $this->total_end === null ) {
            foreach ( $this->items as $item ) {
                $item_end = $item->getTotalEnd();
                if ( $this->total_end === null ) {
                    $this->total_end = $item_end;
                } else if ( $item_end->gt( $this->total_end ) ) {
                    $this->total_end = $item_end;
                }
            }
        }

        return $this->total_end;
    }

    /**
     * @inheritdoc
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

        return $service_price * $this->getCA()->getNumberOfPersons() +
            $extras_total_price * ( $this->getCA()->getExtrasMultiplyNop() ? $this->getCA()->getNumberOfPersons() : 1 );
    }

    /**
     * Create new item.
     *
     * @param Lib\Entities\Service $collaborative_service
     * @return static
     */
    public static function create( Lib\Entities\Service $collaborative_service )
    {
        return new static( $collaborative_service );
    }

    /**
     * Create new item.
     *
     * @param string $token
     * @param array  $statuses
     * @return Collaborative
     */
    public static function createByToken( $token, $statuses = array() )
    {
        $query = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->where( 'ca.collaborative_token', $token );
        if ( $statuses ) {
            $query->whereIn( 'ca.status', $statuses );
        }

        $ca_list = $query->find();

        $self = new static( Lib\Entities\Service::find( $ca_list[0]->getCollaborativeServiceId() ) );

        foreach ( $ca_list as $ca ) {
            $self->addItem( Simple::create( $ca ) );
        }

        return $self;
    }

    /**
     * Create from simple item.
     *
     * @param Simple $item
     * @return static
     */
    public static function createFromSimple( Simple $item )
    {
        return static::create( Lib\Entities\Service::find( $item->getCA()->getCollaborativeServiceId() ) )->addItem( $item );
    }

    /**
     * @inheritdoc
     */
    public function setStatus( $status )
    {
        foreach ( $this->items as $item ) {
            $item->setStatus( $status );
        }
    }
}