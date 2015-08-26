<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class AB_Config {

    /**
     * Get categories, services and staff members for drop down selects
     * for the 1st step of booking wizard.
     *
     * @return array
     */
    public static function getCaSeSt()
    {
        /** @global wpdb $wpdb */
        global $wpdb;

        $result = array(
            'categories' => array(),
            'services'   => array(),
            'staff'      => array(),
        );

        // Select all services (with categories and staff members)
        // which have at least one staff member assigned.
        $rows = AB_Service::query( 's' )
            ->select( 'IFNULL(`c`.`id`,0)    AS `category_id`,
                IFNULL(`c`.`name`, \'' . AB_Query::escape( __( 'Uncategorized', 'bookly' ) ) . '\') AS `category_name`,
                `c`.`position`     AS `category_position`,
                `s`.`id`           AS `service_id`,
                `s`.`position`     AS `service_position`,
                `s`.`title`        AS `service_name`,
                `st`.`id`          AS `staff_id`,
                `st`.`position`    AS `staff_position`,
                `st`.`full_name`   AS `staff_name`,
                `ss`.`capacity`,
                `ss`.`price`' )
            ->innerJoin( 'AB_StaffService', 'ss', 'ss.service_id = s.id ' )
            ->leftJoin( 'AB_Category', 'c', 'c.id = s.category_id' )
            ->leftJoin( 'AB_Staff', 'st', 'st.id = ss.staff_id')
            ->sortBy( 'service_name' )
            ->fetchArray();

        foreach ( $rows as $row ) {
            if ( ! isset( $result['services'][ $row['service_id'] ] ) ) {
                $result['services'][ $row['service_id'] ] = array(
                    'id'           => $row['service_id'],
                    'name'         => AB_Utils::getTranslatedString( 'service_' . $row['service_id'], $row['service_name'] ),
                    'category_id'  => $row['category_id'],
                    'staff'        => array(),
                    'max_capacity' => $row['capacity'],
                    'position'     => $row['service_position'],
                );
            } elseif ( $result['services'][ $row['service_id'] ]['max_capacity'] < $row['capacity'] ) {
                // Detect the max capacity for each service
                //(it is the max capacity from all staff members who provides this service).
                $result['services'][ $row['service_id'] ]['max_capacity'] = $row['capacity'];
            }

            if ( ! isset( $result['staff'][ $row['staff_id'] ] ) ) {
                $result['staff'][ $row['staff_id'] ] = array(
                    'id'       => $row['staff_id'],
                    'name'     => AB_Utils::getTranslatedString( 'staff_' . $row['staff_id'], $row['staff_name'] ),
                    'services' => array(),
                    'position' => $row['staff_position'],
                );
            }

            if ( $row['category_id'] != '' && !isset( $result['categories'][ $row['category_id'] ] ) ) {
                $result['categories'][ $row['category_id'] ] = array(
                    'id'       => $row['category_id'],
                    'name'     => AB_Utils::getTranslatedString( 'category_' . $row['category_id'], $row['category_name'] ),
                    'services' => array(),
                    'position' => $row['category_position'],
                );
            }

            if ( ! isset ( $result['services'][ $row['service_id'] ]['staff'][ $row['staff_id'] ] ) ) {
                $staff_member = $result['staff'][ $row['staff_id'] ];
                unset ( $staff_member[ 'services' ] );
                if ( self::isPaymentDisabled() == false ) {
                    $staff_member['name'] .= ' (' . AB_Utils::formatPrice( $row['price'] ) . ')' ;
                }
                $result['services'][ $row['service_id'] ]['staff'][ $row['staff_id'] ] = $staff_member;
            }

            if ( ! isset ( $result['staff'][ $row['staff_id'] ]['services'][ $row['service_id'] ] ) ) {
                $service = $result['services'][ $row['service_id'] ];
                unset ( $service['staff'] );
                $service['max_capacity'] = $row['capacity'];
                $result['staff'][ $row['staff_id'] ]['services'][ $row['service_id'] ] = $service;
            }

            if ( ! isset ( $result['categories'][ intval( $row['category_id'] ) ]['staff'][ $row['staff_id'] ] ) ) {
                $staff_member = $result['staff'][ $row['staff_id'] ];
                unset ( $staff_member['services'] );
                $result['categories'][ intval( $row['category_id'] ) ]['staff'][ $row['staff_id'] ] = $staff_member;
            }

            if ( ! isset ( $result['categories'][ intval( $row['category_id'] ) ]['services'][ $row['service_id'] ] ) ) {
                $service = $result['services'][ $row[ 'service_id' ] ];
                unset ( $service['staff'] );
                $result['categories'][ intval( $row['category_id'] ) ]['services'][ $row['service_id'] ] = $service;
            }
        }

        return $result;
    }

    /**
     * Get available days and available time ranges
     * for the 1st step of booking wizard.
     *
     * @param $time_zone_offset
     * @return array
     */
    public static function getDaysAndTimes( $time_zone_offset = null )
    {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $result = array(
            'days'  => array(),
            'times' => array()
        );

        $start_of_week = get_option( 'start_of_week' );
        $data = AB_StaffScheduleItem::query()
            ->select(
                "GROUP_CONCAT(
                    DISTINCT `r`.`day_index`
                    ORDER BY IF (`r`.`day_index` + 10 - {$start_of_week} > 10, `r`.`day_index` + 10 - {$start_of_week}, 16 + `r`.`day_index`)
                ) AS `day_ids`,
                SUBSTRING_INDEX(MIN(`r`.`start_time`), ':', 2) AS `min_start_time`,
                SUBSTRING_INDEX(MAX(`r`.`end_time`), ':', 2)   AS `max_end_time`"
            )
            ->whereNot( 'start_time', null )
            ->fetchRow();

        if ( $data['day_ids'] ) {
            $week_days = array_values( $wp_locale->weekday_abbrev );
            foreach ( explode( ',', $data['day_ids'] ) as $day_id ) {
                $result['days'][$day_id] = $week_days[ $day_id - 1 ];
            }
        }

        if ( $data['min_start_time'] && $data['max_end_time'] ) {
            $start        = AB_DateTimeUtils::timeToSeconds( $data['min_start_time'] );
            $end          = AB_DateTimeUtils::timeToSeconds( $data['max_end_time'] );
            $client_start = $start;
            $client_end   = $end;

            if ( $time_zone_offset !== null ) {
                $client_start -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
                $client_end   -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
            }

            while ( $start <= $end ) {
                $result['times'][ AB_DateTimeUtils::buildTimeString( $start, false ) ] = AB_DateTimeUtils::formatTime( $client_start );
                // The next value will be rounded to integer number of hours, i.e. e.g. 8:00, 9:00, 10:00 and so on.
                $start        = self::_roundTime( $start + 30 * 60 );
                $client_start = self::_roundTime( $client_start + 30 * 60 );
            }
            // The last value should always be the end time.
            $result['times'][ AB_DateTimeUtils::buildTimeString( $end, false ) ] = AB_DateTimeUtils::formatTime( $client_end );
        }

        return $result;
    }

    /**
     * @param $timestamp
     * @param int $precision
     * @return float
     */
    private static function _roundTime( $timestamp, $precision = 60 )
    {
        $precision = 60 * $precision;
        return round( $timestamp / $precision ) * $precision;
    }

    /**
     * Get array with bounding days for Pickadate.
     *
     * @param $time_zone_offset
     * @return array
     */
    public static function getBoundingDaysForPickadate( $time_zone_offset = null )
    {
        $result = array();
        $time = current_time( 'timestamp' ) + self::getMinimumTimePriorBooking();
        if ( $time_zone_offset !== null ) {
            $time -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
        }
        $result['date_min'] = array(
            (int)date( 'Y', $time ),
            (int)date( 'n', $time ) - 1,
            (int)date( 'j', $time )
        );
        $time += ( self::getMaximumAvailableDaysForBooking() - 1 ) * DAY_IN_SECONDS;
        $result['date_max'] = array(
            (int)date( 'Y', $time ),
            (int)date( 'n', $time ) - 1,
            (int)date( 'j', $time )
        );

        return $result;
    }

    /**
     * Check if all payment methods are disabled.
     *
     * @return bool
     */
    public static function isPaymentDisabled()
    {
        return (
            get_option( 'ab_settings_pay_locally' ) == 0 &&
            get_option( 'ab_paypal_type' ) == 'disabled' &&
            get_option( 'ab_authorizenet_type' ) == 'disabled' &&
            get_option( 'ab_stripe' ) == 0
        );
    }

    /**
     * Get time slot length in seconds.
     *
     * @return integer
     */
    public static function getTimeSlotLength()
    {
        return (int)get_option( 'ab_settings_time_slot_length' ) * 60;
    }

    /**
     * Get minimum time (in seconds) prior to booking.
     *
     * @return integer
     */
    public static function getMinimumTimePriorBooking()
    {
        return (int)get_option( 'ab_settings_minimum_time_prior_booking' ) * 3600;
    }

    /**
     * @return int
     */
    public static function getMaximumAvailableDaysForBooking()
    {
        return (int)get_option( 'ab_settings_maximum_available_days_for_booking', 365 );
    }

    /**
     * Whether to show calendar in the second step of booking form.
     *
     * @return bool
     */
    public static function showCalendar()
    {
        return (bool)get_option( 'ab_appearance_show_calendar', false );
    }

    /**
     * Whether to show fully booked time slots in the second step of booking form.
     *
     * @return bool
     */
    public static function showBlockedTimeSlots()
    {
        return (bool)get_option( 'ab_appearance_show_blocked_timeslots', false );
    }

    /**
     * Whether to show days in the second step of booking form in separate columns or not.
     *
     * @return bool
     */
    public static function showDayPerColumn()
    {
        return (bool)get_option( 'ab_appearance_show_day_one_column', false );
    }

}