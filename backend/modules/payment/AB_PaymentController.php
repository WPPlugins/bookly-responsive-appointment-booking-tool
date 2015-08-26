<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_PaymentController extends AB_Controller {

    public function index() {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/daterangepicker.css',
                'css/bootstrap-select.min.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
                'js/bootstrap-select.min.js',
            )
        ) );

        wp_localize_script( 'ab-daterangepicker.js', 'BooklyL10n', array(
            'today'         => __( 'Today', 'bookly' ),
            'yesterday'     => __( 'Yesterday', 'bookly' ),
            'last_7'        => __( 'Last 7 Days', 'bookly' ),
            'last_30'       => __( 'Last 30 Days', 'bookly' ),
            'this_month'    => __( 'This Month', 'bookly' ),
            'last_month'    => __( 'Last Month', 'bookly' ),
            'custom_range'  => __( 'Custom Range', 'bookly' ),
            'apply'         => __( 'Apply', 'bookly' ),
            'cancel'        => __( 'Cancel', 'bookly' ),
            'to'            => __( 'To', 'bookly' ),
            'from'          => __( 'From', 'bookly' ),
            'months'        => array_values( $wp_locale->month ),
            'days'          => array_values( $wp_locale->weekday_abbrev ),
            'startOfWeek'   => (int) get_option( 'start_of_week' ),
            'mjsDateFormat' => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_MOMENT_JS ),
        ));

        $this->collection = false;

        $this->types     = array();
        $this->customers = array();
        $this->providers = array();
        $this->services  = array();

        $this->render( 'index' );
    }

    /**
     * Sort payments.
     */
    public function executeSortPayments()
    {
        $this->executeFilterPayments();
    }

    /**
     * Filter payments.
     */
    public function executeFilterPayments()
    {
        $this->render( '_body', array( 'collection' => false ) );
        exit;
    }


    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}