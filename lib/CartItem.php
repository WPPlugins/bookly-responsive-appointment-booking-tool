<?php
namespace BooklyLite\Lib;

/**
 * Class CartItem
 * @package BooklyLite\Lib
 */
class CartItem
{
    // Step service
    /** @var  int */
    protected $location_id;
    /** @var  int */
    protected $service_id;
    /** @var  array */
    protected $staff_ids;
    /** @var  int */
    protected $number_of_persons;
    /** @var  string Y-m-d */
    protected $date_from;
    /** @var  array */
    protected $days;
    /** @var  string H:i */
    protected $time_from;
    /** @var  string H:i */
    protected $time_to;

    // Step extras
    /** @var  array */
    protected $extras = array();

    // Step time
    /** @var  array */
    protected $slots;

    // Step details
    /** @var  array */
    protected $custom_fields = array();
    /** @var  int */
    protected $series_unique_id = 0;
    /** @var  bool */
    protected $first_in_series = false;

    // Add here the properties that don't need to be returned in getData

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
        return get_object_vars( $this );
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
     * Get service price.
     *
     * @return double
     */
    public function getServicePrice()
    {
        static $service_prices_cache = array();

        $service = $this->getService();
        list ( $service_id ) = $this->slots[0];
        $staff_id = 1;
        if ( Config::specialHoursEnabled() ) {
            $service_start = date( 'H:i:s', strtotime( $this->slots[0][2] ) );
        } else {
            $service_start = 'unused'; //the price is the same for all services in day
        }

        if ( isset ( $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ] ) ) {
            $service_price = $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ];
        } else {
            if ( $service->getType() == Entities\Service::TYPE_COMPOUND ) {
                $service_price = $service->getPrice();
            } else {
                $staff_service = new Entities\StaffService();
                $staff_service->loadBy( compact( 'staff_id', 'service_id' ) );
                $service_price = $staff_service->getPrice();
            }
            $service_price = Proxy\SpecialHours::preparePrice( $service_price, $staff_id, $service_id, $service_start );
            $service_prices_cache[ $staff_id ][ $service_id ][ $service_start ] = $service_price;
        }

        return $service_price + $this->getExtrasAmount();
    }

    /**
     * Get service deposit.
     *
     * @return string
     */
    public function getDeposit()
    {
        list ( $service_id ) = $this->slots[0];
        $staff_service = new Entities\StaffService();
        $staff_service->loadBy( array(
            'staff_id'   => 1,
            'service_id' => $service_id,
        ) );

        return $staff_service->getDeposit();
    }

    /**
     * Get service deposit price.
     *
     * @return double
     */
    public function getDepositPrice()
    {
        $nop = $this->number_of_persons;

        return Proxy\DepositPayments::prepareAmount( $nop * $this->getServicePrice(), $this->getDeposit(), $nop );
    }

    /**
     * Get service deposit price formatted.
     *
     * @return double
     */
    public function getAmountDue()
    {
        $price   = $this->getServicePrice();
        $deposit = $this->getDepositPrice();

        return $price - $deposit;
    }

    /**
     * Get staff.
     *
     * @return Entities\Staff
     */
    public function getStaff()
    {
        return Entities\Staff::find( 1 );
    }

    /**
     * Get summary price of service's extras.
     *
     * @return double
     */
    public function getExtrasAmount()
    {
        $amount  = 0.0;
        $extras  = (array) Proxy\ServiceExtras::findByIds( array_keys( $this->extras ) );
        foreach ( $extras as $extra ) {
            $amount += $extra->getPrice() * $this->extras[ $extra->getId() ];
        }

        return $amount;
    }

    /**
     * Get duration of service's extras.
     *
     * @return int
     */
    public function getExtrasDuration()
    {
        return (int) Proxy\ServiceExtras::getTotalDuration( $this->extras );
    }

    /**
     * @param int $service_id
     * @return bool
     */
    public function isFirstSubService( $service_id )
    {
        return $this->slots[0][0] == $service_id;
    }

    /**************************************************************************
     * Getters & Setters                                                      *
     **************************************************************************/

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