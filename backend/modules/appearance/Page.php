<?php
namespace Bookly\Backend\Modules\Appearance;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Appearance
 */
class Page extends Lib\Base\Component
{
    /**
     *  Render page.
     */
    public static function render()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        self::enqueueStyles( array(
            'frontend' => array_merge(
                ( get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' ) ),
                array(
                    'css/ladda.min.css',
                    'css/picker.classic.css',
                    'css/picker.classic.date.css',
                    'css/bookly-main.css',
                )
            ),
            'backend' => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'wp'      => array( 'wp-color-picker', ),
            'module'  => array( 'css/bootstrap-editable.css', )
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/picker.js' => array( 'jquery' ),
                    'js/picker.date.js' => array( 'jquery' ),
                    'js/spin.min.js'    => array( 'jquery' ),
                    'js/ladda.min.js'   => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'wp'     => array( 'wp-color-picker' ),
            'module' => array(
                'js/bootstrap-editable.min.js'    => array( 'bookly-bootstrap.min.js' ),
                'js/bootstrap-editable.bookly.js' => array( 'bookly-bootstrap-editable.min.js' ),
                'js/appearance.js'                => array( 'bookly-bootstrap-editable.bookly.js' )
            )
        ) );

        wp_localize_script( 'bookly-picker.date.js', 'BooklyL10n', array(
            'csrf_token'    => Lib\Utils\Common::getCsrfToken(),
            'nop_format'    => get_option( 'bookly_group_booking_nop_format' ),
            'today'         => __( 'Today', 'bookly' ),
            'months'        => array_values( $wp_locale->month ),
            'days'          => array_values( $wp_locale->weekday_abbrev ),
            'nextMonth'     => __( 'Next month', 'bookly' ),
            'prevMonth'     => __( 'Previous month', 'bookly' ),
            'date_format'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_PICKADATE ),
            'start_of_week' => (int) get_option( 'start_of_week' ),
            'saved'         => __( 'Settings saved.', 'bookly' ),
            'intlTelInput'  => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
        ) );

        // Initialize steps (tabs).
        $steps = array(
            1 => array( 'step' => 1, 'show' => true, 'title' => get_option( 'bookly_l10n_step_service' ) ),
            3 => array( 'step' => 3, 'show' => true, 'title' => get_option( 'bookly_l10n_step_time' ) ),
            6 => array( 'step' => 6, 'show' => true, 'title' => get_option( 'bookly_l10n_step_details' ) ),
            7 => array( 'step' => 7, 'show' => true, 'title' => get_option( 'bookly_l10n_step_payment' ) ),
            8 => array( 'step' => 8, 'show' => true, 'title' => get_option( 'bookly_l10n_step_done' ) ),
        );
        if ( Lib\Config::serviceExtrasActive() ) {
            if ( get_option( 'bookly_service_extras_after_step_time' ) ) {
                $steps[2] = $steps[3];
                $steps[3] = array( 'step' => 2, 'show' => get_option( 'bookly_service_extras_enabled' ), 'title' => get_option( 'bookly_l10n_step_extras' ) );
            } else {
                $steps[2] = array( 'step' => 2, 'show' => get_option( 'bookly_service_extras_enabled' ), 'title' => get_option( 'bookly_l10n_step_extras' ) );
            }
        }
        if ( Lib\Config::recurringAppointmentsActive() ) {
            $steps[4] = array( 'step' => 4, 'show' => get_option( 'bookly_recurring_appointments_enabled' ), 'title' => get_option( 'bookly_l10n_step_repeat' ) );
        }
        if ( Lib\Config::cartActive() ) {
            $steps[5] = array( 'step' => 5, 'show' => get_option( 'bookly_cart_enabled' ), 'title' => get_option( 'bookly_l10n_step_cart' ) );
        }
        ksort( $steps );

        $custom_css = get_option( 'bookly_app_custom_styles' );

        // Render general layout.
        self::renderTemplate( 'index', compact( 'steps', 'custom_css' ) );
    }
}