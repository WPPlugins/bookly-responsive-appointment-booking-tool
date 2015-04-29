<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_PaymentController extends AB_Controller {

    public function index() {
        $path = dirname( __DIR__ );
        wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', $path ) );
        wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', $path ) );
        wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', $path ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-date', plugins_url( 'resources/js/date.js', $path ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-daterangepicker-js', plugins_url( 'resources/js/daterangepicker.js', $path ), array( 'jquery' ) );
        wp_enqueue_style( 'ab-daterangepicker-css', plugins_url( 'resources/css/daterangepicker.css', $path ) );
        wp_enqueue_script( 'ab-bootstrap-select-js', plugins_url( 'resources/js/bootstrap-select.min.js', $path ));
        wp_enqueue_style( 'ab-bootstrap-select-css', plugins_url( 'resources/css/bootstrap-select.min.css', $path ));
        wp_localize_script( 'ab-daterangepicker-js', 'BooklyL10n', array(
            'today'        => __( 'Today', 'ab' ),
            'yesterday'    => __( 'Yesterday', 'ab' ),
            'last_7'       => __( 'Last 7 Days', 'ab' ),
            'last_30'      => __( 'Last 30 Days', 'ab' ),
            'this_month'   => __( 'This Month', 'ab' ),
            'last_month'   => __( 'Last Month', 'ab' ),
            'custom_range' => __( 'Custom Range', 'ab' ),
            'apply'        => __( 'Apply', 'ab' ),
            'clear'        => __( 'Clear', 'ab' ),
            'to'           => __( 'To', 'ab' ),
            'from'         => __( 'From', 'ab' ),
            'month'        => array(
                0      => __( 'January', 'ab' ),
                1      => __( 'February', 'ab' ),
                2      => __( 'March', 'ab' ),
                3      => __( 'April', 'ab' ),
                4      => __( 'May', 'ab' ),
                5      => __( 'June', 'ab' ),
                6      => __( 'July', 'ab' ),
                7      => __( 'August', 'ab' ),
                8      => __( 'September', 'ab' ),
                9      => __( 'October', 'ab' ),
                10     => __( 'November', 'ab' ),
                11     => __( 'December', 'ab' )
            ),
            'day'          => array(
                'Mon'  => __( 'Mon', 'ab' ),
                'Tue'  => __( 'Tue', 'ab' ),
                'Wed'  => __( 'Wed', 'ab' ),
                'Thu'  => __( 'Thu', 'ab' ),
                'Fri'  => __( 'Fri', 'ab' ),
                'Sat'  => __( 'Sat', 'ab' ),
                'Sun'  => __( 'Sun', 'ab' )
            )
        ));

        $this->types = $this->customers = $this->providers = $this->services = array();

        $this->render( 'index' );
    }

    /**
     * Translate date-ranges
     */
    public function executeL10nRanges() {
        $start = '';
        $end   = '';
        if ( $this->hasParameter( 'start' ) && $this->hasParameter( 'end' ) ) {
            $start = date_i18n( get_option( 'date_format' ), strtotime( $this->getParameter( 'start' ) ) );
            $end   = date_i18n( get_option( 'date_format' ), strtotime( $this->getParameter( 'end' ) ) );
        }

        echo json_encode( (object) array( 'start' => $start, 'end' => $end ) );
        exit;
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}