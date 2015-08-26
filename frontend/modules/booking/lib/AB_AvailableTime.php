<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_AvailableTime
 */
class AB_AvailableTime {

    /** @var DateInterval */
    private $one_day = null;

    /** @var AB_UserBookingData */
    private $userData;

    private $staffData = array();

    private $staff_ids = array();

    private $service_duration = 0;

    private $last_fetched_slot = null;

    private $selected_date = null;

    private $has_more_slots = false;

    private $slots = array();

    /**
     * Constructor.
     *
     * @param AB_UserBookingData $userData
     */
    public function __construct( AB_UserBookingData $userData )
    {
        $this->one_day = new DateInterval( 'P1D' );

        $this->userData = $userData;

        // Service duration.
        $this->service_duration = (int) $userData->getService()->get( 'duration' );

        $this->staff_ids = array_merge( $userData->get( 'staff_ids' ), array( 0 ) );
    }

    public function load()
    {
        $slots               = 0; // number of handled slots
        $groups              = 0; // number of handled groups
        $show_calendar       = AB_Config::showCalendar();
        $show_day_per_column = AB_Config::showDayPerColumn();
        $time_slot_length    = AB_Config::getTimeSlotLength();
        $client_diff         = get_option( 'ab_settings_use_client_time_zone' )
            ? get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $this->userData->get( 'time_zone_offset' ) * 60
            : 0;
        /**
         * @var int $req_timestamp
         * @var DateTime $date
         * @var DateTime $max_date
         */
        list ( $req_timestamp, $date, $max_date ) = $this->_prepareDates();

        // Prepare staff data.
        $this->_prepareStaffData( $date );

        // The main loop.
        while ( ( $date = $this->_findAvailableDay( $date, $max_date ) ) && (
            $show_calendar ||
            $show_day_per_column && $groups < 10 ||   // one group/column
            ! $show_day_per_column && $slots < 100    // 10 slots/column * 10 columns
        ) ) {
            foreach ( $this->_findAvailableTime( $date ) as $frame ) {
                // Loop from start to:
                //   1. end minus time slot length when 'booked' or 'not_full' is set.
                //   2. end minus service duration when nothing is set.
                $end = null;
                if ( isset ( $frame['booked'] ) || isset ( $frame['not_full'] ) ) {
                    $end = $frame['end'] - $time_slot_length;
                } else {
                    $end = $frame['end'] - $this->service_duration;
                }
                for ( $time = $frame['start']; $time <= $end; $time += $time_slot_length ) {

                    $timestamp        = $date->getTimestamp() + $time;
                    $client_timestamp = $timestamp - $client_diff;

                    if ( $client_timestamp < $req_timestamp ) {
                        // When we start 1 day before the requested date we may not need all found slots,
                        // we should skip those slots which do not fit the requested date in client's time zone.
                        continue;
                    }

                    $group = date( 'Y-m-d', ( $this->isWholeDayService() && ! $show_calendar )
                        ? strtotime( 'first day of this month', $client_timestamp )     // group slots by months
                        : intval( $client_timestamp / DAY_IN_SECONDS ) * DAY_IN_SECONDS // group slots by days
                    );

                    if ( ! isset ( $this->slots[ $group ] ) ) {
                        $this->slots[ $group ] = array();
                        ++ $slots;
                        ++ $groups;
                    }

                    // Create/update slots.
                    if ( ! isset ( $this->slots[ $group ][ $client_timestamp ] ) ) {
                        $this->slots[ $group ][ $client_timestamp ] = array(
                            'timestamp' => $timestamp,
                            'staff_id'  => $frame['staff_id'],
                            'booked'    => isset ( $frame['booked'] ),
                        );
                        ++ $slots;
                    } else if ( ! isset ( $frame['booked'] ) ) {
                        if ( $this->slots[ $group ][ $client_timestamp ]['booked'] ) {
                            // Set slot to available if it was marked as 'booked' before.
                            $this->slots[ $group ][ $client_timestamp ]['staff_id'] = $frame['staff_id'];
                            $this->slots[ $group ][ $client_timestamp ]['booked']   = false;
                        }
                        // Change staff member for this slot if the other staff member has higher price.
                        else if ( $this->staffData[ $this->slots[ $group ][ $client_timestamp ]['staff_id'] ]['price'] < $this->staffData[ $frame['staff_id'] ]['price'] ) {
                            $this->slots[ $group ][ $client_timestamp ]['staff_id'] = $frame['staff_id'];
                        }
                    }
                }
            }

            $date->add( $this->one_day );
        }

        // Detect if there are more slots.
        if ( ! $show_calendar && $date !== false ) {
            while ( $date = $this->_findAvailableDay( $date, $max_date ) ) {
                $available_time = $this->_findAvailableTime( $date );
                if ( ! empty ( $available_time ) ) {
                    $this->has_more_slots = true;
                    break;
                }
                $date->add( $this->one_day );
            }
        }
    }

    /**
     * Determine requested timestamp and start and max date.
     *
     * @return array
     */
    private function _prepareDates()
    {
        if ( $this->last_fetched_slot ) {
            $start_date = new DateTime( substr( $this->last_fetched_slot, 0, 10 ) );
            $req_timestamp = $start_date->getTimestamp();
            // The last_fetched_slot is always in WP time zone (see AB_BookingController::executeRenderNextTime()).
            // We increase it by 1 day to get the date to start with.
            $start_date->add( $this->one_day );
        } else {
            $start_date = new DateTime( $this->selected_date ? $this->selected_date : $this->userData->get( 'date_from' ) );
            if ( AB_Config::showCalendar() ) {
                // Get slots for selected month.
                $start_date->modify( 'first day of this month' );
            }
            $req_timestamp = $start_date->getTimestamp();
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                // The userData::date_from is in client's time zone so we need to check the previous day too
                // because some available slots can be found in the previous day due to time zone offset.
                $start_date->sub( $this->one_day );
            }
        }

        $max_date = date_create(
            '@' . ( (int)current_time( 'timestamp' ) + AB_Config::getMaximumAvailableDaysForBooking() * DAY_IN_SECONDS )
        )->setTime( 0, 0 );
        if ( AB_Config::showCalendar() ) {
            $next_month = clone $start_date;
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                // Add one day since it was subtracted hereinabove.
                $next_month->add( $this->one_day );
            }
            $next_month->modify( 'first day of next month' );
            if ( $next_month < $max_date ) {
                $max_date = $next_month;
            }
        }

        return array( $req_timestamp, $start_date, $max_date );
    }

    /**
     * Find a day which is available for booking based on
     * user requested set of days.
     *
     * @access private
     * @param DateTime $date
     * @param DateTime $max_date
     * @return DateTime
     */
    private function _findAvailableDay( DateTime $date, DateTime $max_date )
    {
        $attempt = 0;
        // Find available day within requested days.
        $requested_days = $this->userData->get( 'days' );
        while ( !in_array( $date->format( 'w' ) + 1, $requested_days ) ) {
            $date->add( $this->one_day );
            if ( ++ $attempt >= 7 ) {
                return false;
            }
        }

        return $date >= $max_date ? false : $date;
    }

    /**
     * Find array of time slots available for booking
     * for given date.
     *
     * @access private
     * @param DateTime $date
     * @return array
     */
    private function _findAvailableTime( DateTime $date )
    {
        $result             = array();
        $time_slot_length   = AB_Config::getTimeSlotLength();
        $prior_time         = AB_Config::getMinimumTimePriorBooking();
        $show_blocked_slots = AB_Config::showBlockedTimeSlots();
        $current_timestamp  = (int)current_time( 'timestamp' ) + $prior_time;
        $current_date       = date_create( '@' . $current_timestamp )->setTime( 0, 0 );

        if ( $date < $current_date ) {
            return array();
        }

        $day_of_week = $date->format( 'w' ) + 1; // 1-7
        $start_time  = date( 'H:i:s', ceil( $current_timestamp / $time_slot_length ) * $time_slot_length );

        foreach ( $this->staffData as $staff_id => $staff ) {

            if ( $staff['capacity'] < $this->userData->get( 'number_of_persons' ) ) {
                continue;
            }

            if ( isset ( $staff['working_hours'][ $day_of_week ] ) && $this->isWorkingDay( $date, $staff_id ) ) {

                if ( $this->isWholeDayService() ) {
                    // For whole day services do not check staff working hours.
                    $intersections = array( array(
                        'start' => 0,
                        'end'   => DAY_IN_SECONDS,
                    ) );
                } else {
                    // Find intersection between working and requested hours
                    //(excluding time slots in the past).
                    $working_start_time = ( $date == $current_date && $start_time > $staff['working_hours'][ $day_of_week ]['start_time'] )
                        ? $start_time
                        : $staff['working_hours'][ $day_of_week ]['start_time'];

                    $intersections = $this->_findIntersections(
                        AB_DateTimeUtils::timeToSeconds( $working_start_time ),
                        AB_DateTimeUtils::timeToSeconds( $staff['working_hours'][ $day_of_week ]['end_time'] ),
                        AB_DateTimeUtils::timeToSeconds( $this->userData->get( 'time_from' ) ),
                        AB_DateTimeUtils::timeToSeconds( $this->userData->get( 'time_to' ) )
                    );
                }

                foreach ( $intersections as $intersection ) {
                    if ( $intersection['end'] - $intersection['start'] >= $this->service_duration ) {
                        // Initialize time frames.
                        $frames = array( array(
                            'start'    => $intersection['start'],
                            'end'      => $intersection['end'],
                            'staff_id' => $staff_id
                        ) );
                        if ( ! $this->isWholeDayService() ) {
                            // Remove breaks from time frames for non whole day services only.
                            foreach ( $staff['working_hours'][ $day_of_week ]['breaks'] as $break ) {
                                $frames = $this->_removeTimePeriod(
                                    $frames,
                                    AB_DateTimeUtils::timeToSeconds( $break['start'] ),
                                    AB_DateTimeUtils::timeToSeconds( $break['end'] )
                                );
                            }
                        }
                        // Remove bookings from time frames.
                        foreach ( $staff['bookings'] as $booking ) {
                            // Work with bookings for the current day only.
                            if ( $date->format( 'Y-m-d' ) == substr( $booking['start_date'], 0, 10 ) ) {

                                $booking_start = AB_DateTimeUtils::timeToSeconds( substr( $booking['start_date'], 11 ) );
                                $booking_end   = AB_DateTimeUtils::timeToSeconds( substr( $booking['end_date'], 11 ) );
                                $frames = $this->_removeTimePeriod( $frames, $booking_start, $booking_end, $removed );

                                if ( $removed ) {
                                    // Handle not full bookings (when number of bookings is less than capacity).
                                    if (
                                        $booking['from_google'] == false &&
                                        $booking['service_id'] == $this->userData->get( 'service_id' ) &&
                                        $booking_start >= $intersection['start'] &&
                                        $staff['capacity'] - $booking['number_of_bookings'] >= $this->userData->get( 'number_of_persons' )
                                    ) {
                                        // Show only the first slot as available.
                                        $frames[] = array(
                                            'start'    => $booking_start,
                                            'end'      => $booking_start + $time_slot_length,
                                            'staff_id' => $staff_id,
                                            'not_full' => true,
                                        );

                                        if ( $this->isWholeDayService() ) {
                                            // For whole day services there can be just one not full appointment
                                            // for a day, thus we break the loop and do not add 'booked' slots.
                                            break;
                                        }

                                        if ( $show_blocked_slots ) {
                                            // When displaying blocked slots then the rest must be shown as blocked.
                                            $frames[] = array(
                                                'start'    => $booking_start + $time_slot_length,
                                                'end'      => $booking_end <= $intersection['end'] ? $booking_end : $intersection['end'],
                                                'staff_id' => $staff_id,
                                                'booked'   => true
                                            );
                                        }
                                    }
                                    // Handle fully booked slots when displaying blocked slots.
                                    elseif ( $show_blocked_slots ) {
                                        // Show removed slots as blocked.
                                        if ( $this->isWholeDayService() ) {
                                            $frames[] = array(
                                                'start'    => 0,
                                                'end'      => $time_slot_length,
                                                'staff_id' => $staff_id,
                                                'booked'   => true
                                            );
                                            // For whole day services we break the loop since the day is not available
                                            // and we do not need more 'booked' slots.
                                            break;
                                        } else {
                                            $frames[] = array(
                                                'start'    => $booking_start >= $intersection['start'] ? $booking_start : $intersection['start'],
                                                'end'      => $booking_end <= $intersection['end'] ? $booking_end : $intersection['end'],
                                                'staff_id' => $staff_id,
                                                'booked'   => true
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        $result = array_merge( $result, $frames );
                    }
                }
            }
        }
        usort( $result, function ( $a, $b ) { return $a['start'] - $b['start']; } );

        return $result;
    }

    /**
     * Checks if the date is not a holiday for this employee
     *
     * @param DateTime $date
     * @param int $staff_id
     *
     * @return bool
     */
    private function isWorkingDay( DateTime $date, $staff_id )
    {
        $working_day = true;
        if ( $this->staffData[ $staff_id ]['holidays'] ) {
            foreach ( $this->staffData[ $staff_id ]['holidays'] as $holiday ) {
                $holidayDate = new DateTime( $holiday['date'] );
                if ( $holiday['repeat_event'] ) {
                    $working_day = $holidayDate->format( 'm-d' ) != $date->format( 'm-d' );
                } else {
                    $working_day = $holidayDate != $date;
                }
                if ( !$working_day ) {
                    break;
                }
            }
        }

        return $working_day;
    }

    /**
     * Find intersection between 2 time periods.
     *
     * @param mixed $p1_start
     * @param mixed $p1_end
     * @param mixed $p2_start
     * @param mixed $p2_end
     * @return array
     */
    private function _findIntersections( $p1_start, $p1_end, $p2_start, $p2_end )
    {
        $result = array();

        if ( $p2_start > $p2_end ) {
            $result[] = $this->_findIntersections( $p1_start, $p1_end, $p2_start, 86400 );
            $result[] = $this->_findIntersections( $p1_start, $p1_end, 0, $p2_end );
        } else {
            if ( $p1_start <= $p2_start && $p1_end > $p2_start && $p1_end <= $p2_end ) {
                $result[] = array( 'start' => $p2_start, 'end' => $p1_end );
            } elseif ( $p1_start <= $p2_start && $p1_end >= $p2_end ) {
                $result[] = array( 'start' => $p2_start, 'end' => $p2_end );
            } elseif ( $p1_start >= $p2_start && $p1_start < $p2_end && $p1_end >= $p2_end ) {
                $result[] = array( 'start' => $p1_start, 'end' => $p2_end );
            } elseif ( $p1_start >= $p2_start && $p1_end <= $p2_end ) {
                $result[] = array( 'start' => $p1_start, 'end' => $p1_end );
            }
        }

        return $result;
    }

    /**
     * Remove time period from the set of time frames.
     *
     * @param array $frames
     * @param mixed $p_start
     * @param mixed $p_end
     * @param bool& $removed  Whether the period was removed or not
     * @return array
     */
    private function _removeTimePeriod( array $frames, $p_start, $p_end, &$removed = false )
    {
        $result  = array();
        $removed = false;
        foreach ( $frames as $frame ) {
            $intersections = $this->_findIntersections(
                $frame['start'],
                $frame['end'],
                $p_start,
                $p_end
            );
            foreach ( $intersections as $intersection ) {
                if ( $intersection['start'] - $frame['start'] >= $this->service_duration ) {
                    $result[] = array_merge( $frame, array(
                        'end' => $intersection['start'],
                    ) );
                }
                if ( $frame['end'] - $intersection['end'] >= $this->service_duration ) {
                    $result[] = array_merge( $frame, array(
                        'start' => $intersection['end'],
                    ) );
                }
            }
            if ( empty ( $intersections ) ) {
                $result[] = $frame;
            } else {
                $removed = true;
            }
        }

        return $result;
    }

    /**
     * Prepare data for staff.
     *
     * @param DateTime $start_date
     */
    private function _prepareStaffData( DateTime $start_date )
    {
        $this->staffData = array();

        $services = AB_StaffService::query( 'ss' )
            ->select( 'ss.staff_id, ss.price, ss.capacity' )
            ->whereIn( 'ss.staff_id', $this->staff_ids )
            ->where( 'ss.service_id', $this->userData->get( 'service_id' ) )
            ->fetchArray();

        foreach ( $services as $item ) {
            $this->staffData[ $item['staff_id'] ] = array(
                'price'         => $item['price'],
                'capacity'      => $item['capacity'],
                'holidays'      => array(),
                'bookings'      => array(),
                'working_hours' => array(),
            );
        }

        // Load holidays.
        $holidays = AB_Holiday::query( 'h' )->whereIn( 'h.staff_id', $this->staff_ids )->fetchArray();
        foreach ( $holidays as $item ) {
            $this->staffData[ $item['staff_id'] ]['holidays'][] = $item;
        }

        // Load working schedule.
        $working_schedule = AB_StaffScheduleItem::query( 'ssi' )
            ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
            ->leftJoin( 'AB_ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
            ->whereIn( 'ssi.staff_id', $this->staff_ids )
            ->whereNot( 'ssi.start_time', null )
            ->fetchArray();

        foreach ( $working_schedule as $item ) {
            if ( ! isset ( $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] ) ) {
                $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] = array(
                    'start_time' => $item['start_time'],
                    'end_time'   => $item['end_time'],
                    'breaks'     => array(),
                );
            }
            if ( $item['break_start'] ) {
                $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ]['breaks'][] = array(
                    'start' => $item['break_start'],
                    'end'   => $item['break_end']
                );
            }
        }

        $service = $this->userData->getService();
        $padding_left  = (int)$service->get( 'padding_left' );
        $padding_right = (int)$service->get( 'padding_right' );
        // Load bookings.
        $bookings = AB_CustomerAppointment::query( 'ca' )
            ->select( 'a.id, a.staff_id, a.service_id, a.google_event_id,
                       DATE_SUB(a.start_date, INTERVAL (COALESCE(s.padding_left,0) + ' . $padding_right . ') SECOND) as start_date,
                       DATE_ADD(a.end_date, INTERVAL (COALESCE(s.padding_right,0) + ' . $padding_left . ') SECOND) as end_date,
                       SUM(ca.number_of_persons) AS number_of_bookings' )
            ->leftJoin( 'AB_Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->leftJoin( 'AB_Service', 's', 's.id = a.service_id' )
            ->whereIn( 'a.staff_id', $this->staff_ids )
            ->whereGte( 'a.start_date', $start_date->format( 'Y-m-d' ) )
            ->groupBy( 'a.start_date' )->groupBy( 'a.staff_id' )->groupBy( 'a.service_id' )
            ->fetchArray();
        foreach ( $bookings as $item ) {
            $item['from_google'] = false;
            $appointment_end_time = substr( $item['end_date'], 11 );
            // Handle bookings which end at 24:00.
            if ( $appointment_end_time == '00:00:00' ) {
                // Set time to 24:00:00 (date part does not matter, it just needs to be 10 characters length).
                $item['end_date'] = 'YEAR-MM-DD 24:00:00';
            } elseif ( substr( $item['start_date'], 11 ) > $appointment_end_time ) {
                // Ending of appointment on next day.
                $item['end_date'] = 'YEAR-MM-DD ' .  AB_DateTimeUtils::buildTimeString( DAY_IN_SECONDS + AB_DateTimeUtils::timeToSeconds( $appointment_end_time ) );
            }
            $this->staffData[ $item['staff_id'] ]['bookings'][] = $item;
        }

    }

    /**
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get disabled days in Pickadate format.
     *
     * @return array
     */
    public function getDisabledDaysForPickadate()
    {
        $result = array();
        $date = new DateTime( $this->selected_date ? $this->selected_date : $this->userData->get( 'date_from' ) );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int)$date->format( 'Y' );
        $n = (int)$date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int)$date->format( 'j' ) );
            }
            $date->add( $this->one_day );
        }

        return $result;
    }

    public function setLastFetchedSlot( $last_fetched_slot )
    {
        $this->last_fetched_slot = $last_fetched_slot;
    }

    public function setSelectedDate( $selected_date )
    {
        $this->selected_date = $selected_date;
    }

    public function getSelectedDateForPickadate()
    {
        if ( $this->selected_date ) {
            foreach ( $this->slots as $group => $slots ) {
                if ( $group >= $this->selected_date ) {
                    return $group;
                }
            }

            if ( empty( $this->slots ) ) {
                return $this->selected_date;
            } else {
                reset( $this->slots );
                return key( $this->slots );
            }
        }

        if ( ! empty ( $this->slots ) ) {
            reset( $this->slots );
            return key( $this->slots );
        }

        return $this->userData->get( 'date_from' );
    }

    public function hasMoreSlots()
    {
        return $this->has_more_slots;
    }

    /**
     * Check whether the service is whole day or not.
     * A whole day service has duration set to 86400 seconds.
     *
     * @return bool
     */
    public function isWholeDayService()
    {
        return $this->service_duration == DAY_IN_SECONDS;
    }

    /**
     * Check if booking time is still available
     * Return TRUE if time is available
     *
     * @return bool
     */
    public function checkBookingTime()
    {
        /** @var WPDB $wpdb */
        global $wpdb;

        $booked_datetime = $this->userData->get( 'appointment_datetime' );
        $endDate = new DateTime( $booked_datetime );
        $endDate->modify( "+ {$this->userData->getService()->get( 'duration' )} sec" );
        $query = AB_CustomerAppointment::query( 'ca' )
            ->select( 'a.*, ss.capacity, SUM(ca.number_of_persons) AS total_number_of_persons' )
            ->leftJoin( 'AB_Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->where( 'a.staff_id', $this->userData->getStaffId() )
            ->groupBy( 'a.start_date' )->groupBy( 'a.staff_id' )->groupBy( 'a.service_id' )
            ->havingRaw( '(a.start_date = %s AND service_id =  %d AND total_number_of_persons >= capacity) OR
                          (a.start_date = %s AND service_id <> %d) OR
                          (a.start_date > %s AND a.end_date <= %s) OR
                          (a.start_date < %s AND a.end_date > %s) OR
                          (a.start_date < %s AND a.end_date > %s)',
                array(  $booked_datetime, $this->userData->get( 'service_id' ),
                        $booked_datetime, $this->userData->get( 'service_id' ),
                        $booked_datetime, $endDate->format( 'Y-m-d H:i:s' ),
                        $endDate->format( 'Y-m-d H:i:s' ), $endDate->format( 'Y-m-d H:i:s' ),
                        $booked_datetime, $booked_datetime ),
                null )->limit( 1 );

        return !(bool)$wpdb->get_row( $query );
    }

}