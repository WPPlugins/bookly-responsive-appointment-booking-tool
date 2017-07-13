<?php
namespace BooklyLite\Lib;

/**
 * Class Config
 * @package BooklyLite\Lib
 */
abstract class Config
{
    private static $addons = array();

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
                'id'   => (int) $row['id'],
                'name' => Utils\Common::getTranslatedString( 'category_' . $row['id'], $row['name'] ),
                'pos'  => (int) $row['position'],
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
                'id'          => (int) $row['id'],
                'category_id' => (int) $row['category_id'],
                'name'        => $row['title'] == ''
                    ? __( 'Untitled', 'bookly' )
                    : Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
                'max_capacity' => (int) $row['max_capacity'],
                'pos'          => (int) $row['position'],
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
                    'id'       => (int) $row['id'],
                    'name'     => Utils\Common::getTranslatedString( 'staff_' . $row['id'], $row['full_name'] ),
                    'services' => array(),
                    'pos'      => (int) $row['position'],
                );
            }
            $result['staff'][ $row['id'] ]['services'][ $row['service_id'] ] = array(
                'capacity' => (int) $row['capacity'],
                'price'    => get_option( 'bookly_app_staff_name_with_price' )
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
            'times' => array(),
        );

        $data = Entities\StaffScheduleItem::query()
            ->select(
                'GROUP_CONCAT(DISTINCT `r`.`day_index`) AS `day_ids`,
                SUBSTRING_INDEX(MIN(`r`.`start_time`), ":", 2) AS `min_start_time`,
                SUBSTRING_INDEX(MAX(`r`.`end_time`), ":", 2)   AS `max_end_time`'
            )
            ->whereNot( 'start_time', null )
            ->fetchRow();

        $data['day_ids'] = $data['day_ids'] ? explode( ',', $data['day_ids'] ) : array();

        $data = apply_filters( 'bookly_special_days_adjust_config_days_and_times', $data );

        $start_of_week = get_option( 'start_of_week' );
        $week_days     = array_values( $wp_locale->weekday_abbrev );

        // Sort days considering start_of_week;
        usort( $data['day_ids'], function ( $a, $b ) use ( $start_of_week ) {
            $a -= $start_of_week;
            $b -= $start_of_week;
            if ( $a < 1 ) {
                $a += 7;
            }
            if ( $b < 1 ) {
                $b += 7;
            }

            return $a - $b;
        } );

        // Fill days.
        foreach ( $data['day_ids'] as $day_id ) {
            $result['days'][ $day_id ] = $week_days[ $day_id - 1 ];
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
        return array( 'AED', 'ARS', 'AUD', 'BGN', 'BHD', 'BRL', 'CAD', 'CHF', 'CLP', 'COP', 'CRC', 'CZK', 'DKK', 'DOP', 'EGP', 'EUR', 'GBP', 'GEL', 'GTQ', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JPY', 'KES', 'KRW', 'KZT', 'LAK', 'MUR', 'MXN', 'MYR', 'NAD', 'NGN', 'NOK', 'NZD', 'OMR', 'PEN', 'PHP', 'PKR', 'PLN', 'QAR', 'RMB', 'RON', 'RUB', 'SAR', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'UAH', 'UGX', 'USD', 'VND', 'XAF', 'XOF', 'ZAR', 'ZMW', );
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
            (int) date( 'j', $time ),
        );
        $time += ( self::getMaximumAvailableDaysForBooking() - 1 ) * DAY_IN_SECONDS;
        $result['date_max'] = array(
            (int) date( 'Y', $time ),
            (int) date( 'n', $time ) - 1,
            (int) date( 'j', $time ),
        );

        return $result;
    }

    /**
     * Get value of option for given payment type.
     *
     * @param string $type
     * @return string
     */
    public static function getPaymentTypeOption( $type )
    {
        return get_option( 'bookly_pmt_' . $type, 'disabled' );
    }

    /**
     * Check whether given payment type is enabled.
     *
     * @param string $type
     * @return bool
     */
    public static function isPaymentTypeEnabled( $type )
    {
        return self::getPaymentTypeOption( $type ) != 'disabled';
    }

    /**
     * Check whether payment step is disabled.
     *
     * @return bool
     */
    public static function isPaymentStepDisabled()
    {
        $types = array(
            Entities\Payment::TYPE_2CHECKOUT,
            Entities\Payment::TYPE_AUTHORIZENET,
            Entities\Payment::TYPE_LOCAL,
            Entities\Payment::TYPE_MOLLIE,
            Entities\Payment::TYPE_PAYPAL,
            Entities\Payment::TYPE_PAYSON,
            Entities\Payment::TYPE_PAYULATAM,
            Entities\Payment::TYPE_STRIPE,
        );

        foreach ( $types as $type ) {
            if ( self::isPaymentTypeEnabled( $type ) ) {
                return false;
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
        return (int) get_option( 'bookly_gen_time_slot_length', 15 ) * MINUTE_IN_SECONDS;
    }

    /**
     * Check whether service duration should be used instead of slot length on the frontend.
     *
     * @return bool
     */
    public static function useServiceDurationAsSlotLength()
    {
        return (bool) get_option( 'bookly_gen_service_duration_as_slot_length', false );
    }

    /**
     * Check whether use client time zone.
     *
     * @return bool
     */
    public static function useClientTimeZone()
    {
        return (bool) get_option( 'bookly_gen_use_client_time_zone' );
    }

    /**
     * Get minimum time (in seconds) prior to booking.
     *
     * @return integer
     */
    public static function getMinimumTimePriorBooking()
    {
        return (int) get_option( 'bookly_gen_min_time_prior_booking' ) * 3600;
    }

    /**
     * @return int
     */
    public static function getMaximumAvailableDaysForBooking()
    {
        return (int) get_option( 'bookly_gen_max_days_for_booking', 365 );
    }

    /**
     * Whether to show calendar in the second step of booking form.
     *
     * @return bool
     */
    public static function showCalendar()
    {
        return (bool) get_option( 'bookly_app_show_calendar', false );
    }

    /**
     * Whether to show fully booked time slots in the second step of booking form.
     *
     * @return bool
     */
    public static function showBlockedTimeSlots()
    {
        return (bool) get_option( 'bookly_app_show_blocked_timeslots', false );
    }

    /**
     * Whether to show days in the second step of booking form in separate columns or not.
     *
     * @return bool
     */
    public static function showDayPerColumn()
    {
        return (bool) get_option( 'bookly_app_show_day_one_column', false );
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
        return get_option( 'bookly_cst_combined_notifications' ) == 1;
    }

    /**
     * Whether step Cart is enabled or not.
     *
     * @return bool
     */
    public static function showStepCart()
    {
        return get_option( 'bookly_cart_enabled' ) == 1 && ! Config::isWooCommerceEnabled();
    }

    /**
     * Check if emails are sent as HTML or plain text.
     *
     * @return bool
     */
    public static function sendEmailAsHtml()
    {
        return get_option( 'bookly_email_send_as' ) == 'html';
    }

    /******************************************************************************************************************
     * Add-ons                                                                                                        *
     ******************************************************************************************************************/

    /**
     * Check whether Extras add-on is enabled or not.
     *
     * @return bool
     */
    public static function isServiceExtrasEnabled()
    {
        return self::_isAddOnEnabled( 'service-extras' );
    }

    /**
     * Check whether Service Schedule add-on is enabled or not.
     *
     * @return bool
     */
    public static function isServiceScheduleEnabled()
    {
        return self::_isAddOnEnabled( 'service-schedule' );
    }

    /**
     * Check whether Chain Appointment add-on is enabled or not.
     *
     * @return bool
     */
    public static function isChainAppointmentsEnabled()
    {
        return self::_isAddOnEnabled( 'chain-appointments' );
    }

    /**
     * Check whether Locations add-on is enabled or not.
     *
     * @return bool
     */
    public static function isLocationsEnabled()
    {
        return self::_isAddOnEnabled( 'locations' );
    }

    /**
     * Check whether Multiply Appointments add-on is enabled or not.
     *
     * @return bool
     */
    public static function isMultiplyAppointmentsEnabled()
    {
        return self::_isAddOnEnabled( 'multiply-appointments' );
    }

    /**
     * Check whether Deposit Payments add-on is enabled or not.
     *
     * @return bool
     */
    public static function isDepositPaymentsEnabled()
    {
        return self::_isAddOnEnabled( 'deposit-payments' );
    }

    /**
     * Check whether Special Days add-on is enabled or not.
     *
     * @return bool
     */
    public static function isSpecialDaysEnabled()
    {
        return self::_isAddOnEnabled( 'special-days' );
    }

    /**
     * Check whether Special Hours add-on is enabled or not.
     *
     * @return bool
     */
    public static function isSpecialHoursEnabled()
    {
        return self::_isAddOnEnabled( 'special-hours' );
    }

    /**
     * Check whether Recurring Appointments add-on is enabled or not.
     *
     * @return bool
     */
    public static function isRecurringAppointmentsEnabled()
    {
        return self::_isAddOnEnabled( 'recurring-appointments' );
    }

    /**
     * WooCommerce Plugin enabled or not.
     *
     * @return bool
     */
    public static function isWooCommerceEnabled()
    {
        return ( get_option( 'bookly_wc_enabled' ) && get_option( 'bookly_wc_product' ) && class_exists( 'WooCommerce', false ) && ( WC()->cart->get_cart_url() !== false ) );
    }

    /******************************************************************************************************************
     * Private methods                                                                                                *
     ******************************************************************************************************************/

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

    /**
     * Check whether given add-on enabled or not.
     *
     * @param string $add_on
     * @return bool
     */
    private static function _isAddOnEnabled( $add_on )
    {
        if ( ! array_key_exists( $add_on, self::$addons ) ) {
            $enabled = get_option( 'bookly_' . str_replace( '-', '_', $add_on ) . '_enabled' ) == 1;
            if ( $enabled ) {
                $enabled = Utils\Common::isPluginActive( 'bookly-addon-' . $add_on . '/main.php' );
            }
            self::$addons[ $add_on ] = $enabled;
        }

        return self::$addons[ $add_on ];
    }

}