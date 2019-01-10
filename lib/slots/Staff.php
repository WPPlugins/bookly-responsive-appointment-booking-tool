<?php
namespace Bookly\Lib\Slots;

use Bookly\Lib\Entities;
use Bookly\Lib\Proxy;

/**
 * Class Staff
 *
 * @package Bookly\Lib\Slots
 */
class Staff
{
    /** @var Schedule[] */
    protected $schedule;
    /** @var Booking[] */
    protected $bookings;
    /** @var Service[] */
    protected $services;
    /** @var array */
    protected $workload;
    /** @var int */
    protected $working_time_limit;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->schedule = array( new Schedule() );
        $this->bookings = array();
        $this->services = array();
    }

    /**
     * Get schedule.
     *
     * @param int $location_id
     * @return Schedule
     */
    public function getSchedule( $location_id = 0 )
    {
        return isset ( $this->schedule[ $location_id ] )
            ? $this->schedule[ $location_id ]
            : $this->schedule[0];
    }

    /**
     * @param Schedule $schedule
     * @param int      $location_id
     *
     * @return $this
     */
    public function setSchedule( $schedule, $location_id = 0 )
    {
        $this->schedule[ $location_id ] = $schedule;

        return $this;
    }

    /**
     * @return array
     */
    public function getScheduleLocations()
    {
        return array_keys( $this->schedule );
    }

    /**
     * Add holiday.
     *
     * @param string $date  Format Y-m[-d]
     * @return $this
     */
    public function addHoliday( $date )
    {
        foreach ( $this->schedule as $schedule ) {
            $schedule->addHoliday( $date );
        }

        return $this;
    }

    /**
     * @param array $day
     * @return $this
     */
    public function addSpecialDay( $day )
    {

        foreach ( $this->schedule as $schedule ) {
            if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
                $schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
            }
            if ( $day['break_start'] ) {
                $schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
            }
        }

        return $this;
    }

    /**
     * Add booking.
     *
     * @param Booking $booking
     * @return $this
     */
    public function addBooking( Booking $booking )
    {
        $this->bookings[] = $booking;

        $date = $booking->range()->start()->format( 'Y-m-d' );
        if ( ! isset ( $this->workload[ $date ] ) ) {
            $this->workload[ $date ] = 0;
        }
        $this->workload[ $date ] += $booking->rangeWithPadding()->length();

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
     * @param int    $location_id
     * @param double $price
     * @param int    $capacity_min
     * @param int    $capacity_max
     * @param string $staff_preference_rule
     * @param array  $staff_preference_settings
     * @param int    $staff_preference_order
     * @return $this
     */
    public function addService(
        $service_id,
        $location_id,
        $price,
        $capacity_min,
        $capacity_max,
        $staff_preference_rule,
        $staff_preference_settings,
        $staff_preference_order
    )
    {
        $this->services[ $service_id ][ $location_id ] = new Service(
            $price,
            $capacity_min,
            $capacity_max,
            $staff_preference_rule,
            $staff_preference_settings,
            $staff_preference_order
        );

        return $this;
    }

    /**
     * Set working_time_limit
     * @param $working_time_limit
     * @return $this
     */
    public function setWorkingTimeLimit( $working_time_limit )
    {
        $this->working_time_limit = $working_time_limit;

        return $this;
    }

    /**
     * Get working_time_limit
     * @return int
     */
    public function getWorkingTimeLimit()
    {
        return $this->working_time_limit;
    }

    /**
     * Tells whether staff provides given service.
     *
     * @param int $service_id
     * @param int $location_id
     * @return bool
     */
    public function providesService( $service_id, $location_id )
    {
        return isset ( $this->services[ $service_id ][ $location_id ] );
    }

    /**
     * Get service by ID.
     *
     * @param int $service_id
     * @param int $location_id
     * @return Service
     */
    public function getService( $service_id, $location_id )
    {
        return isset ( $this->services[ $service_id ][ $location_id ] )
            ? $this->services[ $service_id ][ $location_id ]
            : $this->services[ $service_id ][0];
    }

    /**
     * Get workload for given date.
     *
     * @param $date
     * @return int
     */
    public function getWorkload( $date )
    {
        if ( isset ( $this->workload[ $date ] ) ) {
            return $this->workload[ $date ];
        }

        return 0;
    }

    /**
     * Get workload for given period.
     *
     * @param DatePoint $from
     * @param DatePoint $to
     * @return int
     */
    public function getWorkloadForPeriod( DatePoint $from, DatePoint $to )
    {
        $result = 0;

        for ( $dp = $from; $dp->lte( $to ); $dp = $dp->modify( '+1 day' ) ) {
            $date = $dp->format( 'Y-m-d' );
            if ( isset ( $this->workload[ $date ] ) ) {
                $result += $this->workload[ $date ];
            }
        }

        return $result;
    }

    /**
     * Check whether this staff if more preferable than the given one for given time slot.
     *
     * @param Staff $staff
     * @param Range $slot
     * @return bool
     */
    public function morePreferableThan( Staff $staff, Range $slot )
    {
        $service_id  = $slot->serviceId();
        $location_id = Proxy\Locations::servicesPerLocationAllowed() ? $slot->locationId() : 0;
        $service     = $this->getService( $service_id, $location_id );

        switch ( $service->getStaffPreferenceRule() ) {
            case Entities\Service::PREFERRED_ORDER:
                return $service->getStaffPreferenceOrder() < $staff->getService( $service_id, $location_id )->getStaffPreferenceOrder();
            case Entities\Service::PREFERRED_LEAST_OCCUPIED:
                $date = $slot->start()->value()->format( 'Y-m-d' );

                return $this->getWorkload( $date ) < $staff->getWorkload( $date );
            case Entities\Service::PREFERRED_MOST_OCCUPIED:
                $date = $slot->start()->value()->format( 'Y-m-d' );

                return $this->getWorkload( $date ) > $staff->getWorkload( $date );
            case Entities\Service::PREFERRED_LEAST_OCCUPIED_FOR_PERIOD:
                $settings = $service->getStaffPreferenceSettings();
                $from     = $slot->start()->modify( sprintf( '-%d days', $settings['period']['before'] ) );
                $to       = $slot->start()->modify( sprintf( '+%d days', $settings['period']['after'] ) );

                return $this->getWorkloadForPeriod( $from, $to ) < $staff->getWorkloadForPeriod( $from, $to );
            case Entities\Service::PREFERRED_MOST_OCCUPIED_FOR_PERIOD:
                $settings = $service->getStaffPreferenceSettings();
                $from     = $slot->start()->modify( sprintf( '-%d days', $settings['period']['before'] ) );
                $to       = $slot->start()->modify( sprintf( '+%d days', $settings['period']['after'] ) );

                return $this->getWorkloadForPeriod( $from, $to ) > $staff->getWorkloadForPeriod( $from, $to );
            case Entities\Service::PREFERRED_LEAST_EXPENSIVE:
                return $service->price() < $staff->getService( $service_id, $location_id )->price();
            case Entities\Service::PREFERRED_MOST_EXPENSIVE:
            default:
                return $service->price() > $staff->getService( $service_id, $location_id )->price();
        }
    }
}