<?php
namespace BooklyLite\Lib;

/**
 * Class Config
 * @package BooklyLite\Lib
 */
abstract class Config
{
    /**
     * Get categories, services and staff members for drop down selects
     * for the 1st step of booking wizard.
     *
     * @return array
     */
    public static function getCaSeSt()
    {
        $result = array(
            'locations'  => array(),
            'categories' => array(),
            'services'   => array(),
            'staff'      => array(),
        );

        // Categories.
        $rows = Entities\Category::query()->fetchArray();
        foreach ( $rows as $row ) {
            $result['categories'][ $row['id'] ] = array(
                'id'   => intval( $row['id'] ),
                'name' => Utils\Common::getTranslatedString( 'category_' . $row['id'], $row['name'] ),
                'pos'  => intval( $row['position'] ),
            );
        }

        // Services.
        $rows = Entities\Service::query( 's' )
            ->select( 's.id, s.category_id, s.title, s.position, MAX(ss.capacity) AS max_capacity' )
            ->innerJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
            ->where( 's.type',  Entities\Service::TYPE_SIMPLE )
            ->whereNot( 's.visibility', 'private' )
            ->groupBy( 's.id' )
            ->fetchArray();
        foreach ( $rows as $row ) {
            $result['services'][ $row['id'] ] = array(
                'id'          => intval( $row['id'] ),
                'category_id' => intval( $row['category_id'] ),
                'name'        => $row['title'] == ''
                    ? __( 'Untitled', 'bookly' )
                    : Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
                'max_capacity' => intval( $row['max_capacity'] ),
                'pos'          => intval( $row['position'] ),
            );

            if ( ! $row['category_id'] && ! isset ( $result['categories'][0] ) ) {
                $result['categories'][0] = array(
                    'id'   => 0,
                    'name' => __( 'Uncategorized', 'bookly' ),
                    'pos'  => 99999,
                );
            }
        }

        // Staff.
        $rows = Entities\Staff::query( 'st' )
            ->select( 'st.id, st.full_name, st.position, ss.service_id, ss.capacity, ss.price' )
            ->innerJoin( 'StaffService', 'ss', 'ss.staff_id = st.id' )
            ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
            ->whereNot( 'st.visibility', 'private' )
            ->whereNot( 's.visibility', 'private' )
            ->fetchArray();
        foreach ( $rows as $row ) {
            if ( ! isset ( $result['staff'][ $row['id'] ] ) ) {
                $result['staff'][ $row['id'] ] = array(
                    'id'       => intval( $row['id'] ),
                    'name'     => Utils\Common::getTranslatedString( 'staff_' . $row['id'], $row['full_name'] ),
                    'services' => array(),
                    'pos'      => intval( $row['position'] ),
                );
            }
            $result['staff'][ $row['id'] ]['services'][ $row['service_id'] ] = array(
                'capacity' => intval( $row['capacity'] ),
                'price'    => get_option( 'ab_appearance_staff_name_with_price' )
                    ? html_entity_decode( Utils\Common::formatPrice( $row['price'] ) )
                    : null,
            );
        }

        $result = apply_filters( 'bookly_prepare_casest', $result );

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
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $result = array(
            'days'  => array(),
            'times' => array()
        );

        $start_of_week = get_option( 'start_of_week' );
        $data = Entities\StaffScheduleItem::query()
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
                $result['days'][ $day_id ] = $week_days[ $day_id - 1 ];
            }
        }

        if ( $data['min_start_time'] && $data['max_end_time'] ) {
            $start        = Utils\DateTime::timeToSeconds( $data['min_start_time'] );
            $end          = Utils\DateTime::timeToSeconds( $data['max_end_time'] );
            $client_start = $start;
            $client_end   = $end;

            if ( $time_zone_offset !== null ) {
                $client_start -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
                $client_end   -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
            }

            while ( $start <= $end ) {
                $result['times'][ Utils\DateTime::buildTimeString( $start, false ) ] = Utils\DateTime::formatTime( $client_start );
                // The next value will be rounded to integer number of hours, i.e. e.g. 8:00, 9:00, 10:00 and so on.
                $start        = self::_roundTime( $start + 30 * 60 );
                $client_start = self::_roundTime( $client_start + 30 * 60 );
            }
            // The last value should always be the end time.
            $result['times'][ Utils\DateTime::buildTimeString( $end, false ) ] = Utils\DateTime::formatTime( $client_end );
        }

        return $result;
    }

    /**
     * Currency list
     *
     * @return array
     */
    public static function getCurrencyCodes()
    {
        return array( 'AED', 'ARS', 'AUD', 'BGN', 'BHD', 'BRL', 'CAD', 'CHF', 'CLP', 'COP', 'CRC', 'CZK', 'DKK', 'EGP', 'EUR', 'GBP', 'GTQ', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JPY', 'KRW', 'KZT', 'LAK', 'MUR', 'MXN', 'MYR', 'NAD', 'NGN', 'NOK', 'NZD', 'OMR', 'PEN', 'PHP', 'PKR', 'PLN', 'QAR', 'RMB', 'RON', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'UGX', 'USD', 'VND', 'XOF', 'ZAR', 'ZMW', );
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
        $time   = current_time( 'timestamp' ) + self::getMinimumTimePriorBooking();
        if ( $time_zone_offset !== null ) {
            $time -= $time_zone_offset * MINUTE_IN_SECONDS + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
        }
        $result['date_min'] = array(
            (int) date( 'Y', $time ),
            (int) date( 'n', $time ) - 1,
            (int) date( 'j', $time )
        );
        $time += ( self::getMaximumAvailableDaysForBooking() - 1 ) * DAY_IN_SECONDS;
        $result['date_max'] = array(
            (int) date( 'Y', $time ),
            (int) date( 'n', $time ) - 1,
            (int) date( 'j', $time )
        );

        return $result;
    }

    /**
     * Check if payment methods are disabled.
     *
     * @param null $gateway
     * @return bool
     */
    public static function isPaymentDisabled( $gateway = null )
    {
        $gateways = array(
            Entities\Payment::TYPE_2CHECKOUT    => 'ab_2checkout',
            Entities\Payment::TYPE_AUTHORIZENET => 'ab_authorizenet_type',
            Entities\Payment::TYPE_LOCAL        => 'ab_settings_pay_locally',
            Entities\Payment::TYPE_MOLLIE       => 'ab_mollie',
            Entities\Payment::TYPE_PAYPAL       => 'ab_paypal_type',
            Entities\Payment::TYPE_PAYSON       => 'ab_payson',
            Entities\Payment::TYPE_PAYULATAM    => 'ab_payulatam',
            Entities\Payment::TYPE_STRIPE       => 'ab_stripe',
        );
        switch ( $gateway ) {
            case Entities\Payment::TYPE_2CHECKOUT:
            case Entities\Payment::TYPE_AUTHORIZENET:
            case Entities\Payment::TYPE_LOCAL:
            case Entities\Payment::TYPE_MOLLIE:
            case Entities\Payment::TYPE_PAYPAL:
            case Entities\Payment::TYPE_PAYSON:
            case Entities\Payment::TYPE_PAYULATAM:
            case Entities\Payment::TYPE_STRIPE:
                return get_option( $gateways[ $gateway ] ) == 'disabled';
            default:
                foreach ( $gateways as $gateway ) {
                    if ( get_option( $gateway ) != 'disabled' ) {
                        return false;
                    }
                }
        }

        return true;
    }

    /**
     * Get time slot length in seconds.
     *
     * @return integer
     */
    public static function getTimeSlotLength()
    {
        return (int) get_option( 'ab_settings_time_slot_length' ) * MINUTE_IN_SECONDS;
    }

    /**
     * Get minimum time (in seconds) prior to booking.
     *
     * @return integer
     */
    public static function getMinimumTimePriorBooking()
    {
        return (int) get_option( 'ab_settings_minimum_time_prior_booking' ) * 3600;
    }

    /**
     * @return int
     */
    public static function getMaximumAvailableDaysForBooking()
    {
        return (int) get_option( 'ab_settings_maximum_available_days_for_booking', 365 );
    }

    /**
     * Whether to show calendar in the second step of booking form.
     *
     * @return bool
     */
    public static function showCalendar()
    {
        return (bool) get_option( 'ab_appearance_show_calendar', false );
    }

    /**
     * Whether to show fully booked time slots in the second step of booking form.
     *
     * @return bool
     */
    public static function showBlockedTimeSlots()
    {
        return (bool) get_option( 'ab_appearance_show_blocked_timeslots', false );
    }

    /**
     * Whether to show days in the second step of booking form in separate columns or not.
     *
     * @return bool
     */
    public static function showDayPerColumn()
    {
        return (bool) get_option( 'ab_appearance_show_day_one_column', false );
    }

    /**
     * Whether step Extras is enabled or not.
     *
     * @return bool
     */
    public static function extrasEnabled()
    {
        return self::isAddonEnabled( 'service-extras' );
    }

    /**
     * Chain Appointment Add-on enabled or not.
     *
     * @return bool
     */
    public static function chainAppointmentsEnabled()
    {
        return self::isAddonEnabled( 'chain-appointments' );
    }

    /**
     * Locations Add-on enabled or not.
     *
     * @return bool
     */
    public static function locationsEnabled()
    {
        return Utils\Common::isPluginActive( 'bookly-addon-locations/main.php' );
    }

    /**
     * Multiply Appointments Add-on enabled or not.
     *
     * @return bool
     */
    public static function multiplyAppointmentsEnabled()
    {
        return Utils\Common::isPluginActive( 'bookly-addon-multiply-appointments/main.php' );
    }

    /**
     * Deposit Add-on enabled or not.
     *
     * @return bool
     */
    public static function depositEnabled()
    {
        return self::isAddonEnabled( 'deposit-payments' );
    }

    /**
     * Add-on enabled or not.
     *
     * @param $addon
     * @return bool
     */
    private static function isAddonEnabled( $addon )
    {
        if ( get_option( 'bookly_' . str_replace( '-', '_', $addon ) . '_enabled' ) == 1 ) {
            return Utils\Common::isPluginActive( 'bookly-addon-' . $addon . '/main.php' );
        }

        return false;
    }

    /**
     * Whether step Cart is enabled or not.
     *
     * @return bool
     */
    public static function showStepCart()
    {
        return false;
    }

    /**
     * WooCommerce Plugin enabled or not.
     *
     * @return bool
     */
    public static function WooCommerceEnabled()
    {
        return false;
    }

    /**
     * Whether custom fields attached to services or not.
     *
     * @return bool
     */
    public static function customFieldsPerService()
    {
        return false;
    }

    /**
     * Whether combined notifications for cart are enabled or not.
     *
     * @return bool
     */
    public static function areCombinedNotificationsEnabled()
    {
        return get_option( 'ab_settings_cart_notifications_combined' ) == 1;
    }

    /**
     * Round time in seconds to precision in minutes.
     *
     * @param $timestamp
     * @param int $precision
     * @return float
     */
    private static function _roundTime( $timestamp, $precision = 60 )
    {
        $precision = 60 * $precision;

        return round( $timestamp / $precision ) * $precision;
    }

}