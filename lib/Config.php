<?php
namespace Bookly\Lib;

/**
 * Class Config
 * @package Bookly\Lib
 *
 * @method static bool advancedGoogleCalendarActive()  Check whether Advanced Google Calendar add-on is active or not.
 * @method static bool authorizeNetActive()            Check whether Authorize.Net add-on is active or not.
 * @method static bool cartActive()                    Check whether Cart add-on is active or not.
 * @method static bool chainAppointmentsActive()       Check whether Chain Appointment add-on is active or not.
 * @method static bool compoundServicesActive()        Check whether Compound Services add-on is active or not.
 * @method static bool collaborativeServicesActive()   Check whether Collaborative Services add-on is active or not.
 * @method static bool couponsActive()                 Check whether Coupons add-on is active or not.
 * @method static bool customerGroupsActive()          Check whether Customer Groups add-on is active or not.
 * @method static bool customerInformationActive()     Check whether Customer Information add-on is active or not.
 * @method static bool customFieldsActive()            Check whether Custom Fields add-on is active or not.
 * @method static bool depositPaymentsActive()         Check whether Deposit Payments add-on is active or not.
 * @method static bool filesActive()                   Check whether Files add-on is active or not.
 * @method static bool groupBookingActive()            Check whether Group Booking add-on is active or not.
 * @method static bool invoicesActive()                Check whether Invoices add-on is active or not.
 * @method static bool locationsActive()               Check whether Locations add-on is active or not.
 * @method static bool mollieActive()                  Check whether Mollie add-on is active or not.
 * @method static bool multiplyAppointmentsActive()    Check whether Multiply Appointments add-on is active or not.
 * @method static bool packagesActive()                Check whether Packages add-on is active or not.
 * @method static bool paysonActive()                  Check whether Payson add-on is active or not.
 * @method static bool payuBizActive()                 Check whether PayUbiz add-on is active or not.
 * @method static bool payuLatamActive()               Check whether PayU Latam add-on is active or not.
 * @method static bool proActive()                     Check whether Pro add-on is active or not.
 * @method static bool ratingsActive()                 Check whether Ratings add-on is active or not.
 * @method static bool recurringAppointmentsActive()   Check whether Recurring Appointments add-on is active or not.
 * @method static bool serviceExtrasActive()           Check whether Extras add-on is active or not.
 * @method static bool serviceScheduleActive()         Check whether Service Schedule add-on is active or not.
 * @method static bool specialDaysActive()             Check whether Special Days add-on is active or not.
 * @method static bool specialHoursActive()            Check whether Special Hours add-on is active or not.
 * @method static bool staffCabinetActive()            Check whether Staff Cabinet add-on is active or not.
 * @method static bool stripeActive()                  Check whether Stripe add-on is active or not.
 * @method static bool tasksActive()                   Check whether Tasks add-on is active or not.
 * @method static bool taxesActive()                   Check whether Taxes add-on is active or not.
 * @method static bool waitingListActive()             Check whether Waiting List add-on is active or not.
 * @method static bool customDurationActive()          Check whether Custom Duration add-on is active or not.
 * @method static bool googleMapsAddressActive()       Check whether Google Maps Address add-on is active or not.
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
        $query = Entities\Service::query( 's' )
            ->select( 's.id, s.category_id, s.title, s.position, s.duration, s.price' )
            ->addSelect( sprintf( '%s AS min_capacity, %s AS max_capacity',
                Proxy\Shared::prepareStatement( 1, 'MIN(ss.capacity_min)', 'StaffService' ),
                Proxy\Shared::prepareStatement( 1, 'MAX(ss.capacity_max)', 'StaffService' )
            ) )
            ->innerJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
            ->leftJoin( 'Staff', 'st', 'st.id = ss.staff_id' )
            ->where( 's.type',  Entities\Service::TYPE_SIMPLE )
            ->where( 'st.visibility', 'public' )
            ->groupBy( 's.id' );

        $query = Proxy\Shared::prepareCaSeStQuery( $query );

        if ( ! Proxy\Locations::servicesPerLocationAllowed() ) {
            $query->where( 'ss.location_id', null );
        }

        foreach ( $query->fetchArray() as $row ) {
            $result['services'][ $row['id'] ] = array(
                'id'          => (int) $row['id'],
                'category_id' => (int) $row['category_id'],
                'name'        => $row['title'] == ''
                    ? __( 'Untitled', 'bookly' )
                    : Utils\Common::getTranslatedString( 'service_' . $row['id'], $row['title'] ),
                'duration'     => Utils\DateTime::secondsToInterval( $row['duration'] ),
                'price'        => (float) $row['price'],
                'min_capacity' => (int) $row['min_capacity'],
                'max_capacity' => (int) $row['max_capacity'],
                'has_extras'   => (int) Proxy\ServiceExtras::findByServiceId( $row['id'] ),
                'pos'          => (int) $row['position'],
            );
            if ( ! $row['category_id'] && ! isset ( $result['categories'][0] ) ) {
                $result['categories'][0] = array(
                    'id'   => 0,
                    'name' => __( 'Uncategorized', 'bookly' ),
                    'pos'  => 99999,
                );
            }
            $result = Proxy\Shared::prepareCategoryService( $result, $row );
        }

        // Staff.
        $query = Entities\Staff::query( 'st' )
            ->select( sprintf( 'st.id, st.full_name, st.position, ss.service_id, %s AS capacity_min, %s AS capacity_max, ss.price',
                Proxy\Shared::prepareStatement( 1, 'ss.capacity_min', 'StaffService' ),
                Proxy\Shared::prepareStatement( 1, 'ss.capacity_max', 'StaffService' )
            ) )
            ->innerJoin( 'StaffService', 'ss', 'ss.staff_id = st.id' )
            ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
            ->where( 'st.visibility', 'public' )
            ->whereNot( 's.type', Entities\Service::TYPE_PACKAGE );

        $query = Proxy\Shared::prepareCaSeStQuery( $query );

        if ( ! Proxy\Locations::servicesPerLocationAllowed() ) {
            $query
                ->addSelect( 'ss.location_id' )
                ->where( 'ss.location_id', null );
        }

        $staff_name_with_price = get_option( 'bookly_app_staff_name_with_price' );
        foreach ( $query->fetchArray() as $row ) {
            if ( ! isset ( $result['staff'][ $row['id'] ] ) ) {
                $result['staff'][ $row['id'] ] = array(
                    'id'       => (int) $row['id'],
                    'name'     => Utils\Common::getTranslatedString( 'staff_' . $row['id'], $row['full_name'] ),
                    'services' => array(),
                    'pos'      => (int) $row['position'],
                );
            }

            $location_data = array(
                'min_capacity' => (int) $row['capacity_min'],
                'max_capacity' => (int) $row['capacity_max'],
                'price'        => $staff_name_with_price
                    ? html_entity_decode( Utils\Price::format( $row['price'] ) )
                    : null,
            );
            $location_data = Proxy\Shared::prepareCategoryServiceStaffLocation( $location_data, $row );

            $result['staff'][ $row['id'] ]['services'][ $row['service_id'] ]['locations'][ (int) $row['location_id'] ] = $location_data;
        }

        $result = Proxy\Shared::prepareCaSeSt( $result );

        return Proxy\Ratings::prepareCaSeSt( $result );
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
                ->leftJoin( 'Staff', 'st', '`st`.`id` = `r`.`staff_id`' )
                ->whereNot( 'r.start_time', null )
                ->where( 'st.visibility', 'public' )
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

        if ( $min_start_time ) {
            $bounding = Proxy\Shared::adjustMinAndMaxTimes( array(
                'min_start_time' => Utils\DateTime::buildTimeString( $min_start_time->value(), false ),
                'max_end_time'   => Utils\DateTime::buildTimeString( $max_end_time->value(), false ),
            ) );

            $start        = Slots\TimePoint::fromStr( $bounding['min_start_time'] );
            $end          = Slots\TimePoint::fromStr( $bounding['max_end_time'] );
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

        $dp = Slots\DatePoint::now()->modify( Proxy\Pro::getMinimumTimePriorBooking() )->toClientTz();
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
            || ( Config::twoCheckoutActive() && get_option( 'bookly_2checkout_enabled' ) )
            || ( Config::authorizeNetActive() && get_option( 'bookly_authorize_net_enabled' ) )
            || ( Config::mollieActive() && get_option( 'bookly_mollie_enabled' ) )
            || ( Config::paysonActive() && get_option( 'bookly_payson_enabled' ) )
            || ( Config::payuBizActive() && get_option( 'bookly_payu_biz_enabled' ) )
            || ( Config::payuLatamActive() && get_option( 'bookly_payu_latam_enabled' ) )
            || ( Config::stripeActive() && get_option( 'bookly_stripe_enabled' ) )
            || Config::paypalEnabled()
        );
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
        return self::proActive() && get_option( 'bookly_paypal_enabled' ) != '0';
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
     * Whether to use email confirmation.
     *
     * @return bool
     */
    public static function showEmailConfirm()
    {
        return (bool) get_option( 'bookly_app_show_email_confirm', false );
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
     * Whether to show wide time slots in the time step of booking form.
     *
     * @return bool
     */
    public static function showWideTimeSlots()
    {
        return self::groupBookingActive() && get_option( 'bookly_group_booking_app_show_nop' );
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
     * Whether to show login button at the time step of booking form.
     *
     * @return bool
     */
    public static function showLoginButton()
    {
        return (bool) get_option( 'bookly_app_show_login_button', false );
    }

    /**
     * Whether phone field is required at the Details step or not.
     *
     * @return bool
     */
    public static function phoneRequired()
    {
        return in_array( 'phone', get_option( 'bookly_cst_required_details', array() ) );
    }

    /**
     * Whether email field is required at the Details step or not.
     *
     * @return bool
     */
    public static function emailRequired()
    {
        return in_array( 'email', get_option( 'bookly_cst_required_details', array() ) );
    }

    /**
     * @return bool
     */
    public static function addressRequired()
    {
        return get_option( 'bookly_cst_required_address' ) == 1;
    }

    /**
     * Whether customer duplicates are allowed or not
     *
     * @return bool
     */
    public static function allowDuplicates()
    {
        return get_option( 'bookly_cst_allow_duplicates' ) == 1;
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
     * Whether step Cart is enabled or not.
     *
     * @return bool
     */
    public static function showStepCart()
    {
        return self::cartActive() && get_option( 'bookly_cart_enabled' ) && ! Config::wooCommerceEnabled();
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
        return ( self::proActive() &&  get_option( 'bookly_wc_enabled' ) && get_option( 'bookly_wc_product' ) && class_exists( 'WooCommerce', false ) && ( wc_get_cart_url() !== false ) );
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
        if ( preg_match( '/^(\w+)Active/', $name, $match ) ) {
            // Check if Pro Active
            /** @var \BooklyPro\Lib\Plugin $pro_class */
            $pro_class = '\BooklyPro\Lib\Plugin';
            if ( class_exists( $pro_class, false ) ) {
                /** @var Base\Plugin $plugin_class */
                $plugin_class = sprintf( '\Bookly%s\Lib\Plugin', ucfirst( $match[1] ) );

                return class_exists( $plugin_class, false );
            }

            return false;
        }

        return null;
    }

    /**
     * @return string
     */
    public static function getLocale()
    {
        $locale = get_locale();
        if ( function_exists( 'get_user_locale' ) ) {
            $locale = get_user_locale();
        }

        return $locale;
    }

}