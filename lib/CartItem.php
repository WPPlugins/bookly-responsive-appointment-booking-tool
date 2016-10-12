<?php
namespace BooklyLite\Lib;

/**
 * Class CartItem
 * @package BooklyLite\Lib
 */
class CartItem
{
    private $data = array(
        // Step service
        'location_id'       => null,
        'service_id'        => null,
        'staff_ids'         => null,
        'number_of_persons' => null,
        'date_from'         => null,
        'days'              => null,
        'time_from'         => null,
        'time_to'           => null,
        // Step extras
        'extras'            => array(),
        // Step time
        'slots'             => null,
        // Step details
        'custom_fields'     => array(),
    );

    public static $service_prices = array();

    /**
     * Constructor.
     */
    public function __construct() { }

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
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param array $data
     */
    public function setData( array $data )
    {
        $this->data = $data;
    }

    /**
     * Get service.
     *
     * @return Entities\Service
     */
    public function getService()
    {
        return Entities\Service::find( $this->data['service_id'] );
    }

    /**
     * Get service price.
     *
     * @return float
     */
    public function getServicePrice()
    {
        $service = $this->getService();
        $price   = 0.0;

        $slots  = $this->get( 'slots' );
        list ( $service_id, $staff_id ) = $slots[0];

        $service_start = date( 'H:i:s', $slots[0][2] );
        if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
            $service_start = Utils\DateTime::applyTimeZoneOffset( $service_start,  $this->get( 'time_zone_offset' ) );
        }

        if ( $service->get( 'type' ) == Entities\Service::TYPE_COMPOUND ) {
            $service_price = apply_filters( 'bookly_special_hours_apply_special_period_price', $service->get( 'price' ), $service_start, $service_id, $staff_id );
            $price += $service_price;
        } else {
            if ( ! isset ( self::$service_prices[ $staff_id ][ $service_id ] ) ) {
                $staff_service = new Entities\StaffService();
                $staff_service->loadBy( array(
                    'staff_id'   => $staff_id,
                    'service_id' => $service_id,
                ) );
                $service_price = apply_filters( 'bookly_special_hours_apply_special_period_price', $staff_service->get( 'price' ), $service_start, $service_id, $staff_id );
                self::$service_prices[ $staff_id ][ $service_id ] = $service_price;
            }
            $price += self::$service_prices[ $staff_id ][ $service_id ];
        }

        return $price + $this->getExtrasAmount();
    }

    /**
     * Get service deposit.
     *
     * @return mixed
     */
    public function getDeposit()
    {
        $slots = $this->get( 'slots' );
        list ( $service_id, $staff_id ) = $slots[0];
        $staff_service = new Entities\StaffService();
        $staff_service->loadBy( array(
            'staff_id'   => $staff_id,
            'service_id' => $service_id,
        ) );
        return $staff_service->get( 'deposit' );
    }

    /**
     * Get service deposit price.
     *
     * @return mixed
     */
    public function getDepositPrice()
    {
        $nop = $this->get( 'number_of_persons' );

        return apply_filters( 'bookly_deposit_get_deposit_amount',
            $nop * $this->getServicePrice(),
            $this->getDeposit(),
            $nop );
    }

    /**
     * Get service deposit price formatted.
     *
     * @return mixed
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
        $slots = $this->get( 'slots' );
        $staff_id = $slots[0][1];

        return Entities\Staff::find( $staff_id );
    }

    /**
     * Get summary price of service's extras.
     *
     * @return int
     */
    public function getExtrasAmount()
    {
        $amount = 0.0;
        $_extras = $this->get( 'extras' );
        /** @var \BooklyServiceExtras\Lib\Entities\ServiceExtra[] $extras */
        $extras = apply_filters( 'bookly_extras_find_by_ids', array(), array_keys( $_extras ) );
        foreach ( $extras as $extra ) {
            $amount += $extra->get( 'price' ) * $_extras[ $extra->get( 'id' ) ];
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
        return apply_filters( 'bookly_extras_get_total_duration', 0, $this->get( 'extras' ) );
    }

    public function isFirstSubService( $service_id )
    {
        return $this->data['slots'][0][0] == $service_id;
    }

    /**
     * @return \BooklyLocations\Lib\Entities\Location|false
     */
    public function getLocation()
    {
        if ( Config::locationsEnabled() ) {
            return \BooklyLocations\Lib\Entities\Location::find( $this->get( 'location_id' ) );
        }

        return false;
    }

}