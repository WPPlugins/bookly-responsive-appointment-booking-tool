<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_AvailableTime {

    /**
     * @var AB_UserBookingData
     */
    private $_userData;

    private $_staffIdsStr = '0';

    private $service_duration = 0;

    private $staff_working_hours = array();

    private $start_date;

    private $bookings = array();

    private $prices = array();

    private $holidays = array();

    /**
     * @var array
     */
    private $time = array();

    private $client_time_zone_offset = 0;

    private $time_format;

    /**
     * Constructor.
     *
     * @param $userBookingData
     */
    public function __construct( AB_UserBookingData $userBookingData ) {

        // Store $userBookingData.
        $this->_userData = $userBookingData;

        // Prepare staff ids string for SQL queries.
        $this->_staffIdsStr = implode( ', ', array_merge(
            array_map( 'intval', $userBookingData->getStaffIds() ),
            array( 0 )
        ) );
    }

    public function load() {
        /** @var WPDB $wpdb */
        global $wpdb;

        // Load staff hours with breaks.
        $this->staff_working_hours = $this->_getStaffHours();

        // Load bookings.
        $this->bookings = $this->_getBookings();

        // Merge Google Calendar events with original bookings.
        foreach ($this->_getGCEvents() as $staff_id => $events ) {
            if ( isset ( $this->bookings[ $staff_id ] ) ) {
                $this->bookings[ $staff_id ] = array_merge( $this->bookings[ $staff_id ], $events );
            }
            else {
                $this->bookings[ $staff_id ] = $events;
            }
        }

        // Load service prices for every staff member.
        $this->prices = $this->_getPrices();

        // Load holidays for every staff member.
        $this->holidays = $this->_getHolidays();

        // Service duration
        $this->service_duration = (int) $wpdb->get_var( $wpdb->prepare(
            'SELECT `duration` FROM `ab_service` WHERE `id` = %d',
            $this->_userData->getServiceId()
        ) );

        $date = new DateTime( $this->start_date ? ($this->start_date . '+1 day') : $this->_userData->getRequestedDateFrom() );
        $now = new DateTime();
        if ( $now > $date ) {
            $date = $now;
        }
        $date = $date->format('Y-m-d');

        if ( count( $this->_userData->getAvailableDays() ) && !empty ( $this->staff_working_hours ) ) {
            $this->time_format = get_option('time_format');
            $i = 0;
            $items_number = 0;

            while ( $items_number < 100 && $i < 365 /* check maximum 365 days */ ) {
                $date = $this->_findAvailableDay( $date );
                if ( $date ) {
                    ++ $i;
                    $available_time = $this->_findAvailableTime( $date );
                    if ( !empty ( $available_time ) ) {
                        // Adds date into the column if it was not added
                        if ($this->_addDate($date)){
                            ++ $items_number;
                        }

                        // Client time zone offset.
                        $client_diff = get_option( 'ab_settings_use_client_time_zone' )
                            ? get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $this->client_time_zone_offset * 60
                            : 0;

                        foreach ( $available_time as $item ) {
                            // Loop from start to end with time slot length step.
                            for (
                                $time = $item[ 'start' ];
                                $time <= ($item[ 'end' ] - $this->service_duration);
                                $time += AB_BookingConfiguration::getTimeSlotLength()
                            ) {
                                // Resolves intersections
                                if ( !isset($this->time[ $date ][ $time ] ) ) {
                                    if ($time - $client_diff >= 86400){
                                        $temp_date = date( 'Y-m-d', strtotime( $date . ' +1 day' ) );

                                        if ($this->_addDate($temp_date)){
                                            ++ $items_number;
                                        }

                                        $this->_addTime($temp_date, $time - $client_diff, $item['staff_id'], $time);
                                    }
                                    else {
                                        $this->_addTime($date, $time - $client_diff, $item['staff_id'], $time);
                                    }
                                    ++ $items_number;
                                }
                                else {
                                    // Change staff member for this slot if the other one has higher price.
                                    if ( $this->prices[ $this->time[ $date ][ $time ]->staff_id ] < $this->prices[ $item[ 'staff_id' ] ] ) {
                                        $this->time[ $date ][ $time ]->staff_id = $item[ 'staff_id' ];
                                    }
                                }
                            }
                        }
                    }
                }
                else {
                    break;
                }
                $date = date( 'Y-m-d', strtotime( $date . ' +1 day' ) );
            }
            if ( !$this->start_date && count($this->time) ) {
                $current_time = current($this->time);
                $start = next($current_time);
                if ( $start ) { // must be true
                    $this->start_time = $start->timestamp;
                    $this->start_date = $start->date;
                }
            }
        }
    }

    /**
     * Find a day which is available for booking based on
     * user requested set of days.
     *
     * @access private
     * @param string $date
     * @return string | false
     */
    private function _findAvailableDay( $date ) {
        $datetime = new DateTime( $date );
        $attempt  = 0;
        // Finds day when customer is available
        $customer_available_days = $this->_userData->getAvailableDays();
        while ( !in_array( $datetime->format( 'w' ) + 1, $customer_available_days ) ) {
            $datetime->modify( '+1 day' );
            if ( ++ $attempt >= 7 ) {
                return false;
            }
        }

        return $datetime->format( 'Y-m-d' );
    }

    /**
     * Find array of time slots available for booking
     * for given date.
     *
     * @access private
     * @param string $date
     * @return array
     */
    private function _findAvailableTime( $date ) {
        $result    = array();
        $dayofweek = date( 'w', strtotime( $date ) ) + 1; // 1-7
        $is_date_today = ($date == date( 'Y-m-d', current_time( 'timestamp' ) ) );
        $current_time  = date( 'H:i:s', ceil( current_time( 'timestamp' ) / AB_BookingConfiguration::getTimeSlotLength() ) * AB_BookingConfiguration::getTimeSlotLength() );
        foreach ( $this->staff_working_hours as $staff_id => $hours ) {
            if ( isset ( $hours[ $dayofweek ] ) && $this->isWorkingDay( $date, $staff_id )) {
                // Find intersection between working and requested hours
                //(excluding time slots in the past).
                $working_start_time = ($is_date_today && $current_time > $hours[ $dayofweek ][ 'start_time' ])
                    ? $current_time
                    : $hours[ $dayofweek ][ 'start_time' ];
                $intersection = $this->_findIntersection(
                    $this->_strToTime( $working_start_time ),
                    $this->_strToTime( $hours[ $dayofweek ][ 'end_time' ] ),
                    $this->_strToTime( $this->_userData->getRequestedTimeFrom() ),
                    $this->_strToTime( $this->_userData->getRequestedTimeTo() )
                );

                if (is_array($intersection) && !array_key_exists('start', $intersection)){
                    $intersections = $intersection;
                    foreach ($intersections as $intersection){
                        if ( $intersection && $this->service_duration <= ( $intersection[ 'end' ] - $intersection[ 'start' ] ) ) {
                            // Initialize time frames.
                            $timeframes = array( array(
                                'start'    => $intersection[ 'start' ],
                                'end'      => $intersection[ 'end' ],
                                'staff_id' => $staff_id
                            ) );
                            // Remove breaks from the time frames.
                            foreach ( $hours[ $dayofweek ][ 'breaks' ] as $break ) {
                                $timeframes = $this->_removeTimePeriod(
                                    $timeframes,
                                    $this->_strToTime( $break[ 'start' ] ),
                                    $this->_strToTime( $break[ 'end' ] )
                                );
                            }
                            // Remove bookings from the time frames.
                            $bookings = isset ( $this->bookings[ $staff_id ] ) ? $this->bookings[ $staff_id ] : array();
                            foreach ( $bookings as $booking ) {
                                $bookingStart = new DateTime( $booking->start_date );
                                if ( $date == $bookingStart->format('Y-m-d') ) {
                                    $bookingEnd    = new DateTime( $booking->end_date );
                                    $booking_start = $bookingStart->format( 'U' ) % (24 * 60 * 60);
                                    $booking_end   = $bookingEnd->format( 'U' ) % (24 * 60 * 60);
                                    $timeframes    = $this->_removeTimePeriod( $timeframes, $booking_start, $booking_end );
                                }
                            }
                            $result = array_merge( $result, $timeframes );
                        }
                    }
                }
                else {
                    if ( $intersection && $this->service_duration <= ( $intersection[ 'end' ] - $intersection[ 'start' ] ) ) {
                        // Initialize time frames.
                        $timeframes = array( array(
                            'start'    => $intersection[ 'start' ],
                            'end'      => $intersection[ 'end' ],
                            'staff_id' => $staff_id
                        ) );
                        // Remove breaks from the time frames.
                        foreach ( $hours[ $dayofweek ][ 'breaks' ] as $break ) {
                            $timeframes = $this->_removeTimePeriod(
                                $timeframes,
                                $this->_strToTime( $break[ 'start' ] ),
                                $this->_strToTime( $break[ 'end' ] )
                            );
                        }
                        // Remove bookings from the time frames.
                        $bookings = isset ( $this->bookings[ $staff_id ] ) ? $this->bookings[ $staff_id ] : array();
                        foreach ( $bookings as $booking ) {
                            $bookingStart = new DateTime( $booking->start_date );
                            if ( $date == $bookingStart->format('Y-m-d') ) {
                                $bookingEnd    = new DateTime( $booking->end_date );
                                $booking_start = $bookingStart->format( 'U' ) % (24 * 60 * 60);
                                $booking_end   = $bookingEnd->format( 'U' ) % (24 * 60 * 60);
                                $timeframes    = $this->_removeTimePeriod( $timeframes, $booking_start, $booking_end );
                            }
                        }
                        $result = array_merge( $result, $timeframes );
                    }
                }
            }
        }
        usort( $result, create_function( '$a, $b', 'return $a[\'start\'] - $b[\'start\'];' ) );

        return $result;
    }

    /**
     * Checks if the date is not a holiday for this employee
     * @param string $date
     * @param int $staff_id
     * @return bool
     */
    private function isWorkingDay( $date, $staff_id ) {
        $working_day = true;
        $date = new DateTime($date);
        if ( isset($this->holidays[ $staff_id ]) ) {
            foreach ( $this->holidays[ $staff_id ] as $holiday ) {
                $holidayDate = new DateTime($holiday->holiday);
                if ( $holiday->repeat_event ) {
                    $working_day = $holidayDate->format('m-d') != $date->format('m-d');
                } else {
                    $working_day = $holidayDate->format('Y-m-d') != $date->format('Y-m-d');
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
     * @return array | false
     */
    private function _findIntersection( $p1_start, $p1_end, $p2_start, $p2_end ) {
        $result = false;

        if ($p2_start > $p2_end){
            $result = array();
            $result[] = $this->_findIntersection($p1_start, $p1_end, 0, $p2_end);
            $result[] = $this->_findIntersection($p1_start, $p1_end, $p2_start, 86400);
        }else{
            if ( $p1_start <= $p2_start && $p1_end >= $p2_start && $p1_end <= $p2_end ) {
                $result = array( 'start' => $p2_start, 'end' => $p1_end );
            } else if ( $p1_start <= $p2_start && $p1_end >= $p2_end ) {
                $result = array( 'start' => $p2_start, 'end' => $p2_end );
            } else if ( $p1_start >= $p2_start && $p1_start <= $p2_end && $p1_end >= $p2_end ) {
                $result = array( 'start' => $p1_start, 'end' => $p2_end );
            } else if ( $p1_start >= $p2_start && $p1_end <= $p2_end ) {
                $result = array( 'start' => $p1_start, 'end' => $p1_end );
            }
        }

        return $result;
    }

    /**
     * Remove time period from the set of time frames.
     *
     * @param array $timeframes
     * @param mixed $p_start
     * @param mixed $p_end
     * @return array
     */
    private function _removeTimePeriod( array $timeframes, $p_start, $p_end ) {
        $result = array();
        foreach ( $timeframes as $timeframe ) {
            $intersection = $this->_findIntersection(
                $timeframe[ 'start' ],
                $timeframe[ 'end' ],
                $p_start,
                $p_end
            );
            if ( $intersection ) {
                if ( $timeframe[ 'start' ] < $intersection[ 'start' ] && $this->service_duration <= ( $intersection[ 'start' ] - $timeframe[ 'start' ] ) ) {
                    $result[] = array(
                        'start'    => $timeframe[ 'start' ],
                        'end'      => $intersection[ 'start' ],
                        'staff_id' => $timeframe[ 'staff_id' ]
                    );
                }
                if ( $timeframe[ 'end' ] > $intersection[ 'end' ] && $this->service_duration <= ( $timeframe[ 'end' ] - $intersection[ 'end' ] ) ) {
                    $result[] = array(
                        'start'    => $intersection[ 'end' ],
                        'end'      => $timeframe[ 'end' ],
                        'staff_id' => $timeframe[ 'staff_id' ]
                    );
                }
            } else {
                $result[] = $timeframe;
            }
        }

        return $result;
    }

    /**
     * Convert string to timestamp.
     *
     * @param $str
     * @return int
     */
    private function _strToTime( $str ) {
        return strtotime( sprintf( '1970-01-01 %s', $str ) );
    }

    /**
     * @return array
     */
    public function getTime() {
        return $this->time;
    }

    public function setStartDate( $start_date ) {
        $this->start_date = $start_date;
    }

    public function getStartDate() {
        return $this->start_date;
    }

    public function setClientTimeZoneOffset($client_time_zone_offset){
        $this->client_time_zone_offset = $client_time_zone_offset;
    }

    /*******************
     * Private methods *
     *******************/

    /**
     * Get staff working hours with breaks.
     *
     * @return array
     * [
     *  [
     *      start_time => H:i:s,
     *      end_time   => H:i:s,
     *      breaks     => [ [start => H:i:s, end => H:i:s], ... ]
     *  ],
     *  ...
     * ]
     */
    private function _getStaffHours() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $result = array();

        $rows = $wpdb->get_results( "
            SELECT `item`.*, `break`.`start_time` AS `break_start`, `break`.`end_time` AS `break_end`
                FROM `ab_staff_schedule_item` `item`
                LEFT JOIN `ab_schedule_item_break` `break` ON `item`.`id` = `break`.`staff_schedule_item_id`
            WHERE `item`.`staff_id` IN ({$this->_staffIdsStr}) AND `item`.`start_time` IS NOT NULL
        " );

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                if ( !isset ( $result[ $row->staff_id ][ $row->day_index ] ) ) {
                    $result[ $row->staff_id ][ $row->day_index ] = array(
                        'start_time' => $row->start_time,
                        'end_time'   => $row->end_time,
                        'breaks'     => array(),
                    );
                }
                if ( $row->break_start ) {
                    $result[ $row->staff_id ][ $row->day_index ][ 'breaks' ][] = array(
                        'start' => $row->break_start,
                        'end'   => $row->break_end
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get array of appointments.
     *
     * @return array
     * [
     *  staff_id => [ appointment_data ],
     *  ...
     * ]
     */
    private function _getBookings() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $result = array();

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT `a`.*, `ss`.`capacity`, COUNT(*) AS `number_of_bookings`
                FROM `ab_customer_appointment` `ca`
                LEFT JOIN `ab_appointment` a ON `a`.`id` = `ca`.`appointment_id`
                LEFT JOIN `ab_staff_service` `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`
             WHERE `a`.`staff_id` IN ({$this->_staffIdsStr}) AND `a`.`start_date` >= %s
             GROUP BY `a`.`start_date`, `a`.`staff_id`, `a`.`service_id`",
            $this->_userData->getRequestedDateFrom() ) );

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $result[ $row->staff_id ][] = $row;
            }
        }

        return $result;
    }

    /**
     * Get Google Calendar events for each staff member who has it attached.
     *
     * @return array
     * [
     *  staff_id => [ appointment_data ],
     *  ...
     * ]
     */
    private function _getGCEvents() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $result = array();

        return $result;
    }

    /**
     * Get service prices of every staff member.
     *
     * @return array
     */
    private function _getPrices() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $result = array();

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM `ab_staff_service` WHERE `staff_id` IN ({$this->_staffIdsStr}) AND `service_id` = %d",
            $this->_userData->getServiceId()
        ) );

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $result[ $row->staff_id ] = $row->price;
            }
        }

        return $result;
    }

    /**
     * Get holidays of every staff member.
     *
     * @return array
     */
    private function _getHolidays() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $result = array();

        $rows = $wpdb->get_results( "SELECT * FROM `ab_holiday` WHERE `staff_id` IN ({$this->_staffIdsStr})" );

        if ( is_array( $rows ) ) {
            foreach ( $rows as $row ) {
                $result[ $row->staff_id ][] = $row;
            }
        }

        return $result;
    }

    /**
     * Add date to Time Table (step 2)
     *
     * @param date $date
     * @return bool
     */
    private function _addDate( $date ) {
        if ( !isset ( $this->time[ $date ][ 'day' ] ) ) {
            $object = new stdClass();
            $object->label = date_i18n( 'D, M d', strtotime( $date ) );
            $object->is_day = true;
            $object->value = $date;
            $object->staff_id = '';
            $this->time[ $date ]['day'] = $object;

            return true;
        }

        return false;
    }

    /**
     * Add time to Time Table (step 2)
     *
     * @param date $date
     * @param int $label_time
     * @param int $staff_id
     * @param int $time
     */
    private function _addTime( $date, $label_time, $staff_id, $time ) {
        $object = new stdClass();

        $object->label = date_i18n( $this->time_format, $label_time);
        $object->clean_date = $date;
        $object->value = date('Y-m-d H:i', strtotime($date) + $time);
        $object->staff_id = $staff_id;
        $object->timestamp = $time;
        $object->date = $date;
        $object->is_day = false;
        $this->time[ $date ][ $time ] = $object;
    }
}
