<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_ExportController
 */
class AB_ExportController extends AB_Controller {

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
        $this->render( 'index' );
    }

    /**
     * Export Appointment to CSV
     */
    public function executeExportToCSV ( ) {
        $start_date = new DateTime( $this->getParameter( 'date_start' ) );
        $start_date = $start_date->format('Y-m-d H:i:s');
        $end_date   = new DateTime( $this->getParameter( 'date_end' ) );
        $end_date->modify( '+1 day' );
        $end_date =  $end_date->format('Y-m-d H:i:s');

        header('Content-Type: text/csv; charset=' . (get_locale() == 'ru_RU') ? 'windows-1251': 'utf-8');
        header('Content-Disposition: attachment; filename=appointment.csv');

        if (get_locale() == 'ru_RU'){
            $headings = array(
                iconv('utf-8', 'windows-1251', __('Customer name', 'ab')),
                iconv('utf-8', 'windows-1251', __('Service title', 'ab')),
                iconv('utf-8', 'windows-1251', __('Start date', 'ab')),
                iconv('utf-8', 'windows-1251', __('End date', 'ab')),
                iconv('utf-8', 'windows-1251',  __('Notes', 'ab')));
        }else{
            $headings = array(
                __('Customer name', 'ab'),
                __('Service title', 'ab'),
                __('Start date', 'ab'),
                __('End date', 'ab'),
                __('Notes', 'ab'));
        }
        $output = fopen('php://output', 'w');
        fputcsv($output, $headings);

        $appointments = $this->getWpdb()->get_results("
        SELECT c.name AS customer_name,
               s.title AS service_title,
               a.start_date AS start_date,
               a.end_date AS end_date,
               ca.notes AS notes
        FROM ab_customer_appointment ca
        LEFT JOIN ab_appointment a ON a.id = ca.appointment_id
        LEFT JOIN ab_service s ON a.service_id = s.id
        LEFT JOIN ab_customer c ON ca.customer_id = c.id
        WHERE a.start_date between '{$start_date}' AND '{$end_date}'
        ORDER BY a.start_date DESC
        ", ARRAY_A);

        foreach( $appointments as $appointment ) {
            if (get_locale() == 'ru_RU'){
                $appointment['customer_name'] = iconv('utf-8', 'windows-1251', $appointment['customer_name']);
                $appointment['notes'] = iconv('utf-8', 'windows-1251', $appointment['notes']);
                $appointment['service_title'] = iconv('utf-8', 'windows-1251', $appointment['service_title']);
            }
            fputcsv($output, $appointment);
        }
        fclose($output);
        exit();
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}