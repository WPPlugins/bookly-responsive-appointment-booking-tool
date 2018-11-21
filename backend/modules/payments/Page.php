<?php
namespace Bookly\Backend\Modules\Payments;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Payments
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
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array(
                'css/select2.min.css',
                'bootstrap/css/bootstrap-theme.min.css' => array( 'bookly-select2.min.css' ),
                'css/daterangepicker.css',
            ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'         => array( 'jquery' ),
                'js/select2.full.min.js'        => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/payments.js' => array( 'bookly-datatables.min.js', 'bookly-ng-payment_details.js' ) ),
        ) );

        wp_localize_script( 'bookly-daterangepicker.js', 'BooklyL10n', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'today'           => __( 'Today', 'bookly' ),
            'yesterday'       => __( 'Yesterday', 'bookly' ),
            'last_7'          => __( 'Last 7 days', 'bookly' ),
            'last_30'         => __( 'Last 30 days', 'bookly' ),
            'this_month'      => __( 'This month', 'bookly' ),
            'last_month'      => __( 'Last month', 'bookly' ),
            'custom_range'    => __( 'Custom range', 'bookly' ),
            'apply'           => __( 'Apply', 'bookly' ),
            'cancel'          => __( 'Cancel', 'bookly' ),
            'to'              => __( 'To', 'bookly' ),
            'from'            => __( 'From', 'bookly' ),
            'calendar'        => array(
                'longMonths'  => array_values( $wp_locale->month ),
                'shortMonths' => array_values( $wp_locale->month_abbrev ),
                'longDays'    => array_values( $wp_locale->weekday ),
                'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
            ),
            'startOfWeek'     => (int) get_option( 'start_of_week' ),
            'mjsDateFormat'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'zeroRecords'     => __( 'No payments for selected period and criteria.', 'bookly' ),
            'processing'      => __( 'Processing...', 'bookly' ),
            'details'         => __( 'Details', 'bookly' ),
            'are_you_sure'    => __( 'Are you sure?', 'bookly' ),
            'no_result_found' => __( 'No result found', 'bookly' ),
            'invoice'         => array(
                'enabled' => (int) Lib\Config::invoicesActive(),
                'button'  => __( 'Invoice', 'bookly' ),
            ),
        ) );

        $types = array(
            Lib\Entities\Payment::TYPE_LOCAL,
            Lib\Entities\Payment::TYPE_2CHECKOUT,
            Lib\Entities\Payment::TYPE_PAYPAL,
            Lib\Entities\Payment::TYPE_AUTHORIZENET,
            Lib\Entities\Payment::TYPE_STRIPE,
            Lib\Entities\Payment::TYPE_PAYUBIZ,
            Lib\Entities\Payment::TYPE_PAYULATAM,
            Lib\Entities\Payment::TYPE_PAYSON,
            Lib\Entities\Payment::TYPE_MOLLIE,
            Lib\Entities\Payment::TYPE_COUPON,
            Lib\Entities\Payment::TYPE_WOOCOMMERCE,
        );

        $providers = Lib\Entities\Staff::query()->select( 'id, full_name' )->sortBy( 'full_name' )->whereNot( 'visibility', 'archive' )->fetchArray();
        $services  = Lib\Entities\Service::query()->select( 'id, title' )->sortBy( 'title' )->fetchArray();
        $customers = Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.first_name, c.last_name' )->fetchArray();

        self::renderTemplate( 'index', compact( 'types', 'providers', 'services', 'customers' ) );
    }
}