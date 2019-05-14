<?php
namespace Bookly\Backend\Modules\Dashboard;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Dashboard
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        self::enqueueStyles( array(
            'backend' => array(
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'  => array( 'jquery' ),
            ),
            'module' => array(
                'js/dashboard.js'        => array( 'jquery', 'bookly-appointments-dashboard.js' ),
            ),
        ) );
        wp_localize_script( 'bookly-dashboard.js', 'BooklyL10n', array(
            'csrfToken'  => Lib\Utils\Common::getCsrfToken(),
            'datePicker' => array(
                'last_7'        => __( 'Last 7 days', 'bookly' ),
                'last_30'       => __( 'Last 30 days', 'bookly' ),
                'thisMonth'     => __( 'This month', 'bookly' ),
                'lastMonth'     => __( 'Last month', 'bookly' ),
                'customRange'   => __( 'Custom range', 'bookly' ),
                'apply'         => __( 'Apply', 'bookly' ),
                'cancel'        => __( 'Cancel', 'bookly' ),
                'to'            => __( 'To', 'bookly' ),
                'from'          => __( 'From', 'bookly' ),
                'mjsDateFormat' => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
                'startOfWeek'   => (int) get_option( 'start_of_week' ),
            ),
            'calendar'   => array(
                'longMonths'  => array_values( $wp_locale->month ),
                'shortMonths' => array_values( $wp_locale->month_abbrev ),
                'longDays'    => array_values( $wp_locale->weekday ),
                'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
            ),
        ) );

        self::renderTemplate( 'index' );
    }
}