<?php
namespace Bookly\Frontend\Modules\Booking;

use Bookly\Lib;
use Bookly\Frontend\Modules\Booking\Lib\Errors;

/**
 * Class ShortCode
 * @package Bookly\Frontend\Modules\Booking
 */
class ShortCode extends Lib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register short code.
        add_shortcode( 'bookly-form', array( __CLASS__, 'render' ) );

        add_action(
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ? 'wp_enqueue_scripts' : 'wp_loaded',
            array( __CLASS__, 'linkAssets' )
        );
    }

    /**
     * Link assets.
     */
    public static function linkAssets()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale, $sitepress;

        $link_style  = get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ? 'wp_enqueue_style'  : 'wp_register_style';
        $link_script = get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ? 'wp_enqueue_script' : 'wp_register_script';
        $version     = Lib\Plugin::getVersion();
        $resources   = plugins_url( 'frontend\resources', Lib\Plugin::getBasename() );

        // Assets for [bookly-form].
        if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
            call_user_func( $link_style, 'bookly-intlTelInput', $resources . '/css/intlTelInput.css', array(), $version );
        }
        call_user_func( $link_style, 'bookly-ladda-min',    $resources . '/css/ladda.min.css',       array(), $version );
        call_user_func( $link_style, 'bookly-picker',       $resources . '/css/picker.classic.css',  array(), $version );
        call_user_func( $link_style, 'bookly-picker-date',  $resources . '/css/picker.classic.date.css', array(), $version );
        call_user_func( $link_style, 'bookly-main',         $resources . '/css/bookly-main.css',     get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ? array( 'bookly-intlTelInput', 'bookly-picker-date' ) : array( 'bookly-picker-date' ), $version );
        if ( is_rtl() ) {
            call_user_func( $link_style, 'bookly-rtl',      $resources . '/css/bookly-rtl.css',      array(), $version );
        }
        call_user_func( $link_script, 'bookly-spin',        $resources . '/js/spin.min.js',          array(), $version );
        call_user_func( $link_script, 'bookly-ladda',       $resources . '/js/ladda.min.js',         array( 'bookly-spin' ), $version );
        call_user_func( $link_script, 'bookly-hammer',      $resources . '/js/hammer.min.js',        array( 'jquery' ), $version );
        call_user_func( $link_script, 'bookly-jq-hammer',   $resources . '/js/jquery.hammer.min.js', array( 'jquery' ), $version );
        call_user_func( $link_script, 'bookly-picker',      $resources . '/js/picker.js',            array( 'jquery' ), $version );
        call_user_func( $link_script, 'bookly-picker-date', $resources . '/js/picker.date.js',       array( 'bookly-picker' ), $version );
        if ( get_option( 'bookly_cst_phone_default_country' ) != 'disabled' ) {
            call_user_func( $link_script, 'bookly-intlTelInput', $resources . '/js/intlTelInput.min.js', array( 'jquery' ), $version );
        }

        call_user_func( $link_script, 'bookly', $resources . '/js/bookly.min.js',
            Proxy\Shared::enqueueBookingAssets( array( 'bookly-ladda', 'bookly-hammer', 'bookly-picker-date' ) ),
            $version
        );

        // Prepare URL for AJAX requests.
        $ajaxurl = admin_url( 'admin-ajax.php' );

        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            $ajaxurl = add_query_arg( array( 'lang' => $sitepress->get_current_language() ), $ajaxurl );
        }

        wp_localize_script( 'bookly', 'BooklyL10n', array(
            'ajaxurl'    => $ajaxurl,
            'csrf_token' => Lib\Utils\Common::getCsrfToken(),
            'today'      => __( 'Today', 'bookly' ),
            'months'     => array_values( $wp_locale->month ),
            'days'       => array_values( $wp_locale->weekday ),
            'daysShort'  => array_values( $wp_locale->weekday_abbrev ),
            'nextMonth'  => __( 'Next month', 'bookly' ),
            'prevMonth'  => __( 'Previous month', 'bookly' ),
            'show_more'  => __( 'Show more', 'bookly' ),
        ) );
    }

    /**
     * Render Bookly shortcode.
     *
     * @param $attributes
     * @return string
     * @throws
     */
    public static function render( $attributes )
    {
        // Disable caching.
        Lib\Utils\Common::noCache();

        $assets = '';

        if ( get_option( 'bookly_gen_link_assets_method' ) == 'print' ) {
            $print_assets = ! wp_script_is( 'bookly', 'done' );
            if ( $print_assets ) {
                ob_start();

                // The styles and scripts are registered in Frontend.php
                wp_print_styles( 'bookly-intlTelInput' );
                wp_print_styles( 'bookly-ladda-min' );
                wp_print_styles( 'bookly-picker' );
                wp_print_styles( 'bookly-picker-date' );
                wp_print_styles( 'bookly-main' );

                wp_print_scripts( 'bookly-spin' );
                wp_print_scripts( 'bookly-ladda' );
                wp_print_scripts( 'bookly-picker' );
                wp_print_scripts( 'bookly-picker-date' );
                wp_print_scripts( 'bookly-hammer' );
                wp_print_scripts( 'bookly-jq-hammer' );
                wp_print_scripts( 'bookly-intlTelInput' );

                Proxy\Shared::printBookingAssets();

                wp_print_scripts( 'bookly' );

                $assets = ob_get_clean();
            }
        } else {
            $print_assets = true; // to print CSS in template.
        }

        // Generate unique form id.
        $form_id = uniqid();

        // Find bookings with any of payment statuses ( PayPal, 2Checkout, PayU Latam ).
        $status = array( 'booking' => 'new' );
        foreach ( Lib\Session::getAllFormsData() as $saved_form_id => $data ) {
            if ( isset ( $data['payment'] ) ) {
                if ( ! isset ( $data['payment']['processed'] ) ) {
                    switch ( $data['payment']['status'] ) {
                        case 'success':
                        case 'processing':
                            $form_id = $saved_form_id;
                            $status = array( 'booking' => 'finished' );
                            break;
                        case 'cancelled':
                        case 'error':
                            $form_id = $saved_form_id;
                            end( $data['cart'] );
                            $status = array( 'booking' => 'cancelled', 'cart_key' => key( $data['cart'] ) );
                            break;
                    }
                    // Mark this form as processed for cases when there are more than 1 booking form on the page.
                    $data['payment']['processed'] = true;
                    Lib\Session::setFormVar( $saved_form_id, 'payment', $data['payment'] );
                }
            } elseif ( $data['last_touched'] + 30 * MINUTE_IN_SECONDS < time() ) {
                // Destroy forms older than 30 min.
                Lib\Session::destroyFormData( $saved_form_id );
            }
        }

        // Handle shortcode attributes.
        $fields_to_hide = isset ( $attributes['hide'] ) ? explode( ',', $attributes['hide'] ) : array();
        $location_id    = (int) ( @$_GET['loc_id'] ?: @$attributes['location_id'] );
        $category_id    = (int) ( @$_GET['cat_id'] ?: @$attributes['category_id'] );
        $service_id     = (int) ( @$_GET['service_id'] ?: @$attributes['service_id'] );
        $staff_id       = (int) ( @$_GET['staff_id'] ?: @$attributes['staff_member_id'] );

        $form_attributes = array(
            'hide_categories'        => in_array( 'categories', $fields_to_hide ),
            'hide_services'          => in_array( 'services', $fields_to_hide ),
            'hide_staff_members'     => in_array( 'staff_members', $fields_to_hide ) && ( get_option( 'bookly_app_required_employee' ) ? $staff_id : true ),
            'show_number_of_persons' => (bool) @$attributes['show_number_of_persons'],
            'hide_service_duration'  => true,
            'hide_locations'         => true,
            'hide_quantity'          => true,
            'hide_date'              => in_array( 'date', $fields_to_hide ),
            'hide_week_days'         => in_array( 'week_days', $fields_to_hide ),
            'hide_time_range'        => in_array( 'time_range', $fields_to_hide ),
        );

        // Set service step fields for Add-ons.
        if ( Lib\Config::customDurationActive() && get_option( 'bookly_custom_duration_enabled' ) ) {
            $form_attributes['hide_service_duration'] = in_array( 'service_duration', $fields_to_hide );
        }
        if ( Lib\Config::locationsActive() && get_option( 'bookly_locations_enabled' ) ) {
            $form_attributes['hide_locations'] = in_array( 'locations', $fields_to_hide );
        }
        if ( Lib\Config::multiplyAppointmentsActive() && get_option( 'bookly_multiply_appointments_enabled' ) ) {
            $form_attributes['hide_quantity']  = in_array( 'quantity',  $fields_to_hide );
        }

        $hide_service_part1 = (
            ! $form_attributes['show_number_of_persons'] &&
            $form_attributes['hide_categories'] &&
            $form_attributes['hide_services'] &&
            $service_id &&
            $form_attributes['hide_staff_members'] &&
            $form_attributes['hide_locations'] &&
            $form_attributes['hide_service_duration'] &&
            $form_attributes['hide_quantity']
        );

        $hide_service_part2 = ! array_diff( array( 'date', 'week_days', 'time_range' ), $fields_to_hide );

        if ( $hide_service_part1 && $hide_service_part2 ) {
            Lib\Session::setFormVar( $form_id, 'skip_service_step', true );
        }
        // Store parameters in session for later use.
        Lib\Session::setFormVar( $form_id, 'defaults', compact( 'service_id', 'staff_id', 'location_id', 'category_id' ) );
        Lib\Session::setFormVar( $form_id, 'last_touched', time() );

        $skip_steps = array(
            'service_part1' => (int) $hide_service_part1,
            'service_part2' => (int) $hide_service_part2,
            'extras'        => (int) ( ! Lib\Config::serviceExtrasActive() || ! get_option( 'bookly_service_extras_enabled' ) ),
            'repeat'        => (int) ( ! Lib\Config::recurringAppointmentsActive() || ! get_option( 'bookly_recurring_appointments_enabled' ) ),
            'cart'          => (int) ( Lib\Config::wooCommerceEnabled() ?: ! Lib\Config::showStepCart() ),
        );

        // Custom CSS.
        $custom_css = get_option( 'bookly_app_custom_styles' );

        // Errors.
        $errors = array(
            Errors::SESSION_ERROR               => __( 'Session error.', 'bookly' ),
            Errors::FORM_ID_ERROR               => __( 'Form ID error.', 'bookly' ),
            Errors::CART_ITEM_NOT_AVAILABLE     => Lib\Utils\Common::getTranslatedOption( Lib\Config::showStepCart() ? 'bookly_l10n_step_cart_slot_not_available' : 'bookly_l10n_step_time_slot_not_available' ),
            Errors::PAY_LOCALLY_NOT_AVAILABLE   => __( 'Pay locally is not available.', 'bookly' ),
            Errors::INVALID_GATEWAY             => __( 'Invalid gateway.', 'bookly' ),
            Errors::PAYMENT_ERROR               => __( 'Error.', 'bookly' ),
            Errors::INCORRECT_USERNAME_PASSWORD => __( 'Incorrect username or password.' ),
        );
        $errors = Proxy\Shared::prepareBookingErrorCodes( $errors );

        // Set parameters for bookly form.
        $bookly_options = array(
            'form_id'              => $form_id,
            'status'               => $status,
            'skip_steps'           => $skip_steps,
            'errors'               => $errors,
            'form_attributes'      => $form_attributes,
            'use_client_time_zone' => (int) Lib\Config::useClientTimeZone(),
            'start_of_week'        => (int) get_option( 'start_of_week' ),
            'date_format'          => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_PICKADATE ),
        );

        $bookly_options = Proxy\Shared::booklyFormOptions( $bookly_options );

        return $assets . self::renderTemplate(
            'short_code',
            compact( 'print_assets', 'form_id', 'custom_css', 'bookly_options' ),
            false
        );
    }
}