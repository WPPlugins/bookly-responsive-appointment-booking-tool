<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_AvailableTime {

    /**
     * @var AB_UserBookingData
     */
    private $userData;

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

    private function time_step(){
        return get_option( 'ab_settings_time_slot_length' ) * 60;
    }

    /**
     * Add date to Time Table (step 2)
     *
     * @param date $date
     * @return bool
     */
    private function add_date($date){
        if (!isset($this->time[ $date ]['day'])){
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
    private function add_time($date, $label_time, $staff_id, $time){
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

    public function load() {
        /** @var WPDB $wpdb */
        global $wpdb;

        $service_id = $this->userData->getServiceId();
        $staff_ids = implode( ', ', array_merge(
            array_map( 'intval', $this->userData->getStaffIds() ),
            array( 0 )
        ) );

        // Staff hours
        $rows = $wpdb->get_results( "
            SELECT ssi.*, ssib.start_time AS break_start, ssib.end_time AS break_end
              FROM ab_staff_schedule_item ssi
              LEFT JOIN ab_schedule_item_break ssib ON ssi.id = ssib.staff_schedule_item_id
            WHERE ssi.staff_id IN ($staff_ids) AND ssi.start_time IS NOT NULL
        " );

        foreach ( $rows as $row ) {
            if ( !isset ( $this->staff_working_hours[ $row->staff_id ][ $row->schedule_item_id ] ) ) {
                $this->staff_working_hours[ $row->staff_id ][ $row->schedule_item_id ] = array(
                    'start_time' => $row->start_time,
                    'end_time'   => $row->end_time,
                    'breaks'     => array(),
                );
            }
            if ( $row->break_start ) {
                $this->staff_working_hours[ $row->staff_id ][ $row->schedule_item_id ][ 'breaks' ][] = array(
                    'start' => $row->break_start,
                    'end'   => $row->break_end
                );
            }
        }

        // Bookings
        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM ab_appointment a WHERE a.staff_id IN ($staff_ids) AND a.start_date > %s",
                $this->userData->getRequestedDateFrom()
            )
        );

        if ( $bookings ) {
            foreach ( $bookings as $booking ) {
                $this->bookings[ $booking->staff_id ][] = $booking;
            }
        }

        // Prices for the service by staff
        $service_prices = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM ab_staff_service WHERE staff_id IN ($staff_ids) AND service_id = %d",
            $service_id
        ) );
        if ( $service_prices ) {
            foreach ( $service_prices as $row ) {
                $this->prices[ $row->staff_id ] = $row->price;
            }
        }

        // Holidays by staff
        $holidays = $wpdb->get_results( "SELECT * FROM ab_holiday WHERE staff_id IN ($staff_ids)" );
        if ( $holidays ) {
            foreach ( $holidays as $row ) {
                $this->holidays[ $row->staff_id ][] = $row;
            }
        }

        // Service duration
        $service = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ab_service WHERE id = %d', $this->userData->getServiceId() ) );
        if ( $service ) {
            $this->service_duration = $service->duration;
        }

        $date = new DateTime( $this->start_date ? ($this->start_date . '+1 day') : $this->userData->getRequestedDateFrom() );
        $now = new DateTime();
        if ( $now->format('Ymd') > $date->format('Ymd') ) {
            $date = $now;
        }
        $date = $date->format('Y-m-d');

        if ( count( $this->userData->getAvailableDays() ) && count( $this->staff_working_hours ) ) {
            $this->time_format = get_option('time_format');
            $i = 0;
            $items_number = 0;
            while ( $items_number < 100 && $i < 7 ) {
                $date = $this->_findAvailableDay( $date );
                if ( $date ) {
                    $available_time = $this->_findAvailableTime( $date );
                    $i++;
                    if ( count( $available_time ) ) {
                        $i = 0;
                        // Adds date into the column if it was not added
                        if ($this->add_date($date)){
                            $items_number++;
                        }

                        if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                            $client_diff = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $this->client_time_zone_offset * 60;
                        }else{
                            $client_diff = 0;
                        }

                        foreach ( $available_time as $item ) {
                            for ( $time = $item[ 'start' ]; $time <= ($item[ 'end' ] - $this->service_duration); $time += self::time_step() ) {
                                // Resolves intersections
                                if ( !isset($this->time[ $date ][ $time ]) ) {
                                    if ($time - $client_diff >= 86400){
                                        $temp_date = date( 'Y-m-d', strtotime( $date . ' +1 day' ) );

                                        if ($this->add_date($temp_date)){
                                            $items_number++;
                                        }

                                        $this->add_time($temp_date, $time - $client_diff, $item['staff_id'], $time);
                                    }else{
                                        $this->add_time($date, $time - $client_diff, $item['staff_id'], $time);
                                    }
                                    $items_number++;
                                } else {
                                    if ($time - $client_diff >= 86400){
                                        $temp_date = date( 'Y-m-d', strtotime( $date . ' +1 day' ) );

                                        if ($this->add_date($temp_date)){
                                            $items_number++;
                                        }

                                        $this->add_time($temp_date, $time - $client_diff, $item['staff_id'], $time);
                                    }elseif ( count( $this->prices ) > 1 ) {
                                        if ( $this->prices[ $this->time[ $date ][ $time ]->staff_id ] < $this->prices[ $item[ 'staff_id' ] ] ) {
                                            $this->time[ $date ][ $time ]->staff_id = $item[ 'staff_id' ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
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
        $customer_available_days = $this->userData->getAvailableDays();
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
        $current_time  = date( 'H:i:s', ceil( current_time( 'timestamp' ) / self::time_step() ) * self::time_step() );
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
                    $this->_strToTime( $this->userData->getRequestedTimeFrom() ),
                    $this->_strToTime( $this->userData->getRequestedTimeTo() )
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
                }else{
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

    /**
     * @param AB_UserBookingData $userData
     */
    public function setUserData( AB_UserBookingData $userData ) {
        $this->userData = $userData;
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
}
