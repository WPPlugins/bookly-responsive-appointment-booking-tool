<?php
namespace BooklyLite\Lib;

/**
 * Class Config
 * @package BooklyLite\Lib
 *
 * @method static bool authorizeNetActive()            Check whether Authorize.Net add-on is active or not.
 * @method static bool chainAppointmentsActive()       Check whether Chain Appointment add-on is active or not.
 * @method static bool compoundServicesActive()        Check whether Compound Services add-on is active or not.
 * @method static bool couponsActive()                 Check whether Coupons add-on is active or not.
 * @method static bool customFieldsActive()            Check whether Custom Fields add-on is active or not.
 * @method static bool depositPaymentsActive()         Check whether Deposit Payments add-on is active or not.
 * @method static bool locationsActive()               Check whether Locations add-on is active or not.
 * @method static bool mollieActive()                  Check whether Mollie add-on is active or not.
 * @method static bool multiplyAppointmentsActive()    Check whether Multiply Appointments add-on is active or not.
 * @method static bool packagesActive()                Check whether Packages add-on is active or not.
 * @method static bool paysonActive()                  Check whether Payson add-on is active or not.
 * @method static bool payuLatamActive()               Check whether PayU Latam add-on is active or not.
 * @method static bool recurringAppointmentsActive()   Check whether Recurring Appointments add-on is active or not.
 * @method static bool serviceExtrasActive()           Check whether Extras add-on is active or not.
 * @method static bool serviceScheduleActive()         Check whether Service Schedule add-on is active or not.
 * @method static bool specialDaysActive()             Check whether Special Days add-on is active or not.
 * @method static bool specialHoursActive()            Check whether Special Hours add-on is active or not.
 * @method static bool staffCabinetActive()            Check whether Staff Cabinet add-on is active or not.
 * @method static bool stripeActive()                  Check whether Stripe add-on is active or not.
 * @method static bool waitingListActive()             Check whether Waiting List add-on is active or not.
 *
 *
 * @method static bool authorizeNetEnabled()           Check whether Authorize.Net add-on is enabled or not.
 * @method static bool chainAppointmentsEnabled()      Check whether Chain Appointment add-on is enabled or not.
 * @method static bool compoundServicesEnabled()       Check whether Compound Services add-on is enabled or not.
 * @method static bool couponsEnabled()                Check whether Coupons add-on is enabled or not.
 * @method static bool customFieldsEnabled()           Check whether Custom Fields add-on is enabled or not.
 * @method static bool depositPaymentsEnabled()        Check whether Deposit Payments add-on is enabled or not.
 * @method static bool locationsEnabled()              Check whether Locations add-on is enabled or not.
 * @method static bool mollieEnabled()                 Check whether Mollie add-on is enabled or not.
 * @method static bool multiplyAppointmentsEnabled()   Check whether Multiply Appointments add-on is enabled or not.
 * @method static bool packagesEnabled()               Check whether Packages add-on is enabled or not.
 * @method static bool paysonEnabled()                 Check whether Payson add-on is enabled or not.
 * @method static bool payuLatamEnabled()              Check whether PayU Latam add-on is enabled or not.
 * @method static bool recurringAppointmentsEnabled()  Check whether Recurring Appointments add-on is enabled or not.
 * @method static bool serviceExtrasEnabled()          Check whether Extras add-on is enabled or not.
 * @method static bool serviceScheduleEnabled()        Check whether Service Schedule add-on is enabled or not.
 * @method static bool specialDaysEnabled()            Check whether Special Days add-on is enabled or not.
 * @method static bool specialHoursEnabled()           Check whether Special Hours add-on is enabled or not.
 * @method static bool staffCabinetEnabled()           Check whether Staff Cabinet add-on is enabled or not.
 * @method static bool stripeEnabled()                 Check whether Stripe add-on is enabled or not.
 * @method static bool waitingListEnabled()            Check whether Waiting List add-on is enabled or not.
 */
abstract class Config
{
    /** @var string */
    private static $wp_timezone;

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
            ->select( 's.id, s.category_id, s.title, s.position, s.duration,
                MIN(ss.capacity_min) AS min_capacity, MAX(ss.capacity_max) AS max_capacity' )
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
                'duration'     => \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $row['duration'] ),
                'min_capacity' => (int) $row['min_capacity'],
                'max_capacity' => (int) $row['max_capacity'],
                'has_extras'   => (int) ( \BooklyLite\Lib\Proxy\ServiceExtras::findByServiceId( $row['id'] ) ),
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
            ->select( 'st.id, st.full_name, st.position, ss.service_id, ss.capacity_min, ss.capacity_max, ss.price' )
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
                'min_capacity' => (int) $row['capacity_min'],
                'max_capacity' => (int) $row['capacity_max'],
                'price'        => get_option( 'bookly_app_staff_name_with_price' )
                    ? html_entity_decode( Utils\Price::format( $row['price'] ) )
                    : null,
            );
        }

        return Proxy\Shared::prepareCaSeSt( $result );
    }

    /**
     * Get available days and available time ranges
     * for the 1st step of booking wizard.
     *
     * @return array
     */
    public static function getDaysAndTimes()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $result = array(
            'days'  => array(),
            'times' => array(),
        );

        $res = array_merge(
            Entities\StaffScheduleItem::query()
                ->select( '`r`.`day_index`, MIN(`r`.`start_time`) AS `start_time`, MAX(`r`.`end_time`) AS `end_time`' )
                ->leftJoin( 'Staff', 's', '`s`.`id` = `r`.`staff_id`' )
                ->whereNot( 'r.start_time', null )
                ->whereNot( 's.visibility', 'private' )
                ->groupBy( 'day_index' )
                ->fetchArray(),
            (array) Proxy\SpecialDays::getDaysAndTimes()
        );

        /** @var Slots\TimePoint $min_start_time */
        /** @var Slots\TimePoint $max_end_time */
        $min_start_time = null;
        $max_end_time   = null;
        $days           = array();

        foreach ( $res as $row ) {
            $start_time = Slots\TimePoint::fromStr( $row['start_time'] );
            $end_time   = Slots\TimePoint::fromStr( $row['end_time'] );

            if ( $min_start_time === null || $min_start_time->gt( $start_time ) ) {
                $min_start_time = $start_time;
            }
            if ( $max_end_time === null || $max_end_time->lt( $end_time ) ) {
                $max_end_time = $end_time;
            }

            // Convert to client time zone.
            $start_time = $start_time->toClientTz();
            $end_time   = $end_time->toClientTz();

            // Add day(s).
            if ( $start_time->value() < 0 ) {
                $prev_day = $row['day_index'] - 1;
                if ( $prev_day < 1 ) {
                    $prev_day = 7;
                }
                $days[ $prev_day ] = true;
            }
            if ( $start_time->value() < HOUR_IN_SECONDS * 24 && $end_time->value() > 0 ) {
                $days[ $row['day_index'] ] = true;
            }
            if ( $end_time->value() > HOUR_IN_SECONDS * 24 ) {
                $next_day = $row['day_index'] + 1;
                if ( $next_day > 7 ) {
                    $next_day = 1;
                }
                $days[ $next_day ] = true;
            }
        }

        $start_of_week = get_option( 'start_of_week' );
        $week_days     = array_values( $wp_locale->weekday_abbrev );

        // Sort days considering start_of_week;
        uksort( $days, function ( $a, $b ) use ( $start_of_week ) {
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
        foreach ( array_keys( $days ) as $day_id ) {
            $result['days'][ $day_id ] = $week_days[ $day_id - 1 ];
        }

        if ( $min_start_time && $max_end_time ) {
            $start        = $min_start_time;
            $end          = $max_end_time;
            $client_start = $start->toClientTz();
            $client_end   = $end->toClientTz();

            while ( $start->lte( $end ) ) {
                $result['times'][ Utils\DateTime::buildTimeString( $start->value(), false ) ] = $client_start->formatI18nTime();
                // The next value will be rounded to integer number of hours, i.e. e.g. 8:00, 9:00, 10:00 and so on.
                $start        = $start->modify( HOUR_IN_SECONDS - ( $start->value() % HOUR_IN_SECONDS ) );
                $client_start = $client_start->modify( HOUR_IN_SECONDS - ( $client_start->value() % HOUR_IN_SECONDS ) );
            }
            // The last value should always be the end time.
            $result['times'][ Utils\DateTime::buildTimeString( $end->value(), false ) ] = $client_end->formatI18nTime();
        }

        return $result;
    }

    /**
     * Get array with bounding days for Pickadate.
     *
     * @return array
     */
    public static function getBoundingDaysForPickadate()
    {
        $result = array();

        $dp = Slots\DatePoint::now()->modify( self::getMinimumTimePriorBooking() )->toClientTz();
        $result['date_min'] = array(
            (int) $dp->format( 'Y'),
            (int) $dp->format( 'n' ) - 1,
            (int) $dp->format( 'j' ),
        );
        $dp = $dp->modify( ( self::getMaximumAvailableDaysForBooking() - 1 ) . ' days' );
        $result['date_max'] = array(
            (int) $dp->format( 'Y' ),
            (int) $dp->format( 'n' ) - 1,
            (int) $dp->format( 'j' ),
        );

        return $result;
    }

    /**
     * Check whether payment step is disabled.
     *
     * @return bool
     */
    public static function paymentStepDisabled()
    {
        return ! ( Config::payLocallyEnabled()
            || Config::twoCheckoutEnabled()
            || Config::authorizeNetEnabled()
            || Config::mollieEnabled()
            || Config::paysonEnabled()
            || Config::payuLatamEnabled()
            || Config::stripeEnabled()
            || Config::paypalEnabled()
        );
    }

    /**
     * @return bool
     */
    public static function twoCheckoutEnabled()
    {
        return self::__callStatic( '2checkoutEnabled', array() );
    }

    /**
     * @return bool
     */
    public static function payLocallyEnabled()
    {
        return get_option( 'bookly_pmt_local' ) == 1;
    }

    /**
     * @return bool
     */
    public static function paypalEnabled()
    {
        return get_option( 'bookly_paypal_enabled' ) != '0';
    }

    /**
     *
     * @return bool
     */
    public static function twoCheckoutActive()
    {
        return self::__callStatic( '2checkoutActive', array() );
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
        return (int) ( get_option( 'bookly_gen_min_time_prior_booking' ) * 3600 );
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
     * Whether to use first and last customer name instead full name.
     *
     * @return bool
     */
    public static function showFirstLastName()
    {
        return (bool) get_option( 'bookly_cst_first_last_name', false );
    }

    /**
     * Whether to show notes field.
     *
     * @return bool
     */
    public static function showNotes()
    {
        return (bool) get_option( 'bookly_app_show_notes', false );
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
     * Whether phone field is required at the Details step or not.
     *
     * @return bool
     */
    public static function phoneRequired()
    {
        return get_option( 'bookly_cst_required_phone' ) == 1;
    }

    /**
     * Whether custom fields attached to services or not.
     *
     * @return bool
     */
    public static function customFieldsPerService()
    {
        return get_option( 'bookly_custom_fields_per_service' ) == 1;
    }

    /**
     * Whether to show single instance of custom fields for repeating services.
     *
     * @return bool
     */
    public static function customFieldsMergeRepeating()
    {
        return get_option( 'bookly_custom_fields_merge_repeating' ) == 1;
    }

    /**
     * Whether combined notifications for cart are enabled or not.
     *
     * @return bool
     */
    public static function combinedNotificationsEnabled()
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
        return false;
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

    /**
     * Get WordPress time zone setting.
     *
     * @return string
     */
    public static function getWPTimeZone()
    {
        if ( self::$wp_timezone === null ) {
            if ( $timezone = get_option( 'timezone_string' ) ) {
                // If site timezone string exists, return it.
                self::$wp_timezone = $timezone;
            } else {
                // Otherwise return offset.
                $gmt_offset = get_option( 'gmt_offset' );
                self::$wp_timezone = sprintf( '%s%02d:%02d', $gmt_offset >= 0 ? '+' : '-', abs( $gmt_offset ), abs( $gmt_offset ) * 60 % 60 );
            }
        }

        return self::$wp_timezone;
    }

    /******************************************************************************************************************
     * Add-ons                                                                                                        *
     ******************************************************************************************************************/

    /**
     * WooCommerce Plugin enabled or not.
     *
     * @return bool
     */
    public static function wooCommerceEnabled()
    {
        return false;
    }

    /**
     * Call magic functions.
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic( $name , array $arguments )
    {
        // <add-on>Active
        // <add-on>Enabled
        if ( preg_match( '/^(\w+)(Active|Enabled)/', $name, $match ) ) {
            /** @var Base\Plugin $plugin_class */
            $plugin_class = sprintf( '\Bookly%s\Lib\Plugin', ucfirst( $match[1] ) );

            return class_exists( $plugin_class, false ) && ( $match[2] == 'Active' || $plugin_class::enabled() );
        }

        return null;
    }
}