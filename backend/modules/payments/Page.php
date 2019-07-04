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
            'module'   => array( 'js/payments.js' => array( 'bookly-datatables.min.js', 'bookly-ng-payment_details.js', 'bookly-daterangepicker.js' ) ),
        ) );

        wp_localize_script( 'bookly-payments.js', 'BooklyL10n', array(
            'csrfToken'     => Lib\Utils\Common::getCsrfToken(),
            'datePicker'    => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'     => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
            'zeroRecords'   => __( 'No payments for selected period and criteria.', 'bookly' ),
            'processing'    => __( 'Processing...', 'bookly' ),
            'details'       => __( 'Details', 'bookly' ),
            'areYouSure'    => __( 'Are you sure?', 'bookly' ),
            'noResultFound' => __( 'No result found', 'bookly' ),
            'invoice'       => array(
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