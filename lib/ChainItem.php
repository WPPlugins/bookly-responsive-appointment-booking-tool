<?php
namespace BooklyLite\Lib;

/**
 * Class ChainItem
 * @package BooklyLite\Lib
 */
class ChainItem
{
    /** @var  int */
    protected $location_id;
    /** @var  int */
    protected $service_id;
    /** @var  array */
    protected $staff_ids = array();
    /** @var  int */
    protected $number_of_persons;
    /** @var  array */
    protected $extras = array();
    /** @var  array */
    protected $custom_fields = array();
    /** @var  int */
    protected $series_unique_id = 0;
    /** @var  bool */
    protected $first_in_series = false;
    /** @var  int */
    protected $quantity;

    // Add here the properties that don't need to be returned in getData

    /** @var  Entities\Service[] */
    private $sub_services;
    /** @var  array */
    private $sub_services_staff_ids = array();

    /**
     * Constructor.
     */
    public function __construct() { }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return array(
            'service_id'        => $this->service_id,
            'staff_ids'         => $this->staff_ids,
            'number_of_persons' => $this->number_of_persons,
            'quantity'          => $this->quantity,
            'extras'            => $this->extras,
            'custom_fields'     => $this->custom_fields,
            'location_id'       => $this->location_id,
            'series_unique_id'  => $this->series_unique_id,
            'first_in_series'   => $this->first_in_series,
        );
    }

    /**
     * Set data.
     *
     * @param array $data
     */
    public function setData( array $data )
    {
        foreach ( $data as $name => $value ) {
            $this->{$name} = $value;
        }
    }

    /**
     * Get service.
     *
     * @return Entities\Service
     */
    public function getService()
    {
        return Entities\Service::find( $this->service_id );
    }

    /**
     * Get sub services.
     *
     * @return Entities\Service[]
     */
    public function getSubServices()
    {
        if ( $this->sub_services === null ) {
            $service = $this->getService();
            if ( $service->isCompound() ) {
                $this->sub_services = $service->getSubServices();
            } else {
                $this->sub_services = array( $service );
            }
        }

        return $this->sub_services;
    }

    /**
     * Get sub services with spare time.
     *
     * @return array
     */
    public function getSubServicesWithSpareTime()
    {
        $result  = array();
        $service = $this->getService();
        if ( $service->getType() == Entities\Service::TYPE_COMPOUND ) {
            $items  = Entities\SubService::query( 'ss' )
                ->where( 'ss.service_id', $service->getId() )
                ->sortBy( 'ss.position' )
                ->find();
            /** @var Entities\SubService $sub_service */
            foreach ( $items as $sub_service ) {
                if ( $sub_service->getType() == Entities\SubService::TYPE_SERVICE ) {
                    $result[] = Entities\Service::find( $sub_service->getSubServiceId() );
                } else {
                    // Spare time.
                    $result[] = $sub_service;
                }
            }
        } else {
            $result[] = $service;
        }

        return $result;
    }

    /**
     * Get staff ids for sub service.
     *
     * @param Entities\Service $sub_service
     * @return array
     */
    public function getStaffIdsForSubService( Entities\Service $sub_service )
    {
        return array( 1 );
    }

    /**
     * Check if exist payable extras.
     *
     * @return bool
     */
    public function hasPayableExtras()
    {
        $extras = (array) Proxy\ServiceExtras::findByIds( array_keys( $this->extras ) );
        foreach ( $extras as $extra ) {
            if ( $extra->getPrice() > 0 ) {
                return true;
            }
        }

        return false;
    }

    /**************************************************************************
     * Getters & Setters                                                      *
     **************************************************************************/

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Gets staff_ids
     *
     * @return array
     */
    public function getStaffIds()
    {
        return $this->staff_ids;
    }

    /**
     * Sets staff_ids
     *
     * @param array $staff_ids
     * @return $this
     */
    public function setStaffIds( $staff_ids )
    {
        $this->staff_ids = $staff_ids;

        return $this;
    }

    /**
     * Gets number_of_persons
     *
     * @return int
     */
    public function getNumberOfPersons()
    {
        return $this->number_of_persons;
    }

    /**
     * Sets number_of_persons
     *
     * @param int $number_of_persons
     * @return $this
     */
    public function setNumberOfPersons( $number_of_persons )
    {
        $this->number_of_persons = $number_of_persons;

        return $this;
    }

    /**
     * Gets quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets quantity
     *
     * @param int $quantity
     * @return $this
     */
    public function setQuantity( $quantity )
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Gets extras
     *
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Sets extras
     *
     * @param array $extras
     * @return $this
     */
    public function setExtras( $extras )
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * Gets custom_fields
     *
     * @return array
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * Sets custom_fields
     *
     * @param array $custom_fields
     * @return $this
     */
    public function setCustomFields( $custom_fields )
    {
        $this->custom_fields = $custom_fields;

        return $this;
    }

    /**
     * Gets location_id
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Sets location_id
     *
     * @param int $location_id
     * @return $this
     */
    public function setLocationId( $location_id )
    {
        $this->location_id = $location_id;

        return $this;
    }

    /**
     * Gets series_unique_id
     *
     * @return int
     */
    public function getSeriesUniqueId()
    {
        return (int) $this->series_unique_id;
    }

    /**
     * Sets series_unique_id
     *
     * @param int $series_unique_id
     * @return $this
     */
    public function setSeriesUniqueId( $series_unique_id )
    {
        $this->series_unique_id = $series_unique_id;

        return $this;
    }


    /**
     * Gets first_in_series
     *
     * @return bool
     */
    public function getFirstInSeries()
    {
        return $this->first_in_series;
    }

    /**
     * Sets first_in_series
     *
     * @param bool $first_in_series
     * @return $this
     */
    public function setFirstInSeries( $first_in_series )
    {
        $this->first_in_series = $first_in_series;

        return $this;
    }
}