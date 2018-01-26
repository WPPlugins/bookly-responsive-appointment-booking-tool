<?php
namespace BooklyLite\Lib\Slots;

use \BooklyLite\Lib\Entities;

/**
 * Class Staff
 * @package BooklyLite\Lib\Slots
 */
class Staff
{
    /** @var Schedule */
    protected $schedule;
    /** @var Booking[] */
    protected $bookings;
    /** @var Service[] */
    protected $services;
    /** @var array */
    protected $workload;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->schedule = new Schedule();
        $this->bookings = array();
        $this->services = array();
    }

    /**
     * Get schedule.
     *
     * @return Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Add booking.
     *
     * @param Booking $booking
     * @return $this
     */
    public function addBooking( Booking $booking )
    {
        $date = $booking->getRange()->start()->value()->format( 'Y-m-d' );
        if ( ! isset( $this->workload[ $date ] ) ) {
            $this->workload[ $date ] = 0;
        }
        $this->bookings[] = $booking;
        $this->workload[ $date ] += $booking->getRangeWithPadding()->length();

        return $this;
    }

    /**
     * Get bookings.
     *
     * @return Booking[]
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * Add service.
     *
     * @param int    $service_id
     * @param double $price
     * @param int    $capacity_min
     * @param int    $capacity_max
     * @param string $staff_preference_rule
     * @param int    $staff_preference_order
     * @return $this
     */
    public function addService( $service_id, $price, $capacity_min, $capacity_max, $staff_preference_rule, $staff_preference_order )
    {
        $this->services[ $service_id ] = new Service( $price, $capacity_min, $capacity_max, $staff_preference_rule, $staff_preference_order );

        return $this;
    }

    /**
     * Tells whether staff provides given service.
     *
     * @param int $service_id
     * @return bool
     */
    public function providesService( $service_id )
    {
        return isset ( $this->services[ $service_id ] );
    }

    /**
     * Get service by ID.
     *
     * @param int $service_id
     * @return Service
     */
    public function getService( $service_id )
    {
        return $this->services[ $service_id ];
    }

    /**
     * @param $date
     * @return int
     */
    public function getWorkload( $date )
    {
        if ( isset( $this->workload[ $date ] ) ) {
            return $this->workload[ $date ];
        }

        return 0;
    }

    /**
     * @param Staff $staff
     * @param Range $slot
     * @return bool
     */
    public function morePreferableThan( Staff $staff, Range $slot )
    {
        $service_id = $slot->serviceId();
        $service    = $this->getService( $service_id );

        switch ( $service->getStaffPreferenceRule() ) {
            case Entities\Service::PREFERRED_ORDER:
                return $service->getStaffPreferenceOrder() < $staff->getService( $service_id )->getStaffPreferenceOrder();
            case Entities\Service::PREFERRED_LEAST_OCCUPIED:
                $date  = $slot->start()->value()->format( 'Y-m-d' );
                return $this->getWorkload( $date ) < $staff->getWorkload( $date );
            case Entities\Service::PREFERRED_MOST_OCCUPIED:
                $date  = $slot->start()->value()->format( 'Y-m-d' );
                return $this->getWorkload( $date ) > $staff->getWorkload( $date );
            case Entities\Service::PREFERRED_LEAST_EXPENSIVE:
                return $service->price() < $staff->getService( $service_id )->price();
            case Entities\Service::PREFERRED_MOST_EXPENSIVE:
            default:
                return $service->price() > $staff->getService( $service_id )->price();
        }
    }
}