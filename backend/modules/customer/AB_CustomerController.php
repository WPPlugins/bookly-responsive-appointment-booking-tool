<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_CustomerForm.php';

class AB_CustomerController extends AB_Controller {

    public function index() {
        if (count($this->getPost())){
            $this->importCustomers();
        }

        $path = dirname( dirname(__FILE__) );

        wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', $path ) );
        wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', $path ) );
        wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', $path ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-angularjs', plugins_url( 'resources/js/angular-1.0.6.min.js', $path ) );
        wp_enqueue_script( 'ab-angularui', plugins_url( 'resources/js/angular-ui-0.4.0.min.js', $path ) );
        wp_enqueue_script( 'ab-ng-sanitize', plugins_url( 'resources/js/angular-sanitize-1.0.6.min.js', $path ) );
        wp_enqueue_script( 'ab-ng-app',  plugins_url( 'resources/js/ng-app.js', __FILE__ ), array( 'jquery', 'ab-angularjs', 'ab-angularui', 'ab-ng-sanitize' ) );
        wp_enqueue_script( 'ab-ng-new_customer_dialog', plugins_url( 'resources/js/ng-new_customer_dialog.js', dirname(__FILE__) . '/../../AB_Backend.php' ), array( 'jquery', 'ab-angularjs' ) );

        $this->render('index');
    }

    /**
     * Get Amount of Payments via PayPal and Last and Total Appointments
     *
     * @param string $query
     * @return mixed $query
     */
    public function getCustomerData( $query ) {
        $wpdb = $this->getWpdb();
        $query = $wpdb->get_results( $query );
        if ( count( $query ) ) {
            foreach( $query as $num => $customer_data ) {
                // get Total Appointments
                $query[ $num ]->total_appointments = $wpdb->get_var("
                   SELECT COUNT(a.id) as total_appointments
                   FROM ab_appointment a
                   WHERE a.customer_id = {$customer_data->id}
                ");
                // get Last Appointment
                $query[ $num ]->last_appointment = $wpdb->get_var("
                    SELECT MAX(a.start_date) as last_appointment
                    FROM ab_appointment a
                    WHERE a.customer_id = {$customer_data->id}
                ");
                $query[ $num ]->last_appointment = AB_CommonUtils::getFormattedDateTime(
                    $query[ $num ]->last_appointment
                );

                $query[ $num ]->payments = 0;
            }
        }
        return $query;
    } // getCustomerData

    /**
     * Get list of customers.
     */
    public function executeGetCustomers() {
        $response = array(
            'status' => 'ok',
            'data'   => array(
                'customers' => array(),
                'total'       => 0,
                'pages'       => 0,
                'active_page' => 0,
            )
        );

        $page   = (int) ( $this->_post[ 'page' ] ? $this->_post[ 'page' ] : 1 );
        $sort   = in_array( $this->_post[ 'sort' ], array( 'name', 'phone', 'email' ) ) ? $this->_post[ 'sort' ] : 'name';
        $order  = in_array( $this->_post[ 'order' ], array( 'asc', 'desc' )) ? $this->_post[ 'order' ] : 'asc';
        $filter = $this->_post[ 'filter' ] ? $this->_post[ 'filter' ] : '';

        $items_per_page = 20;
        $total = $this->getWpdb()->get_var( 'SELECT COUNT(*) FROM `ab_customer`' );
        $pages = ceil( $total / $items_per_page );
        if ( $page < 1 || $page > $pages ) {
            $page = 1;
        }

        if ( $total ) {
            $query = 'SELECT * FROM `ab_customer`';

            // WHERE
            if ( $filter !== '' ) {
                $query .= " WHERE name LIKE '%{$filter}%' OR phone  LIKE '%{$filter}%' OR  email LIKE '%{$filter}%'";
            }
            // ORDER BY
            $query .= " ORDER BY {$sort} {$order}";
            // LIMIT
            $start = ( $page - 1) * $items_per_page;
            $query .= " LIMIT {$start}, {$items_per_page}";
            $customer_data = self::getCustomerData( $query );
            // Populate response.
            $response[ 'data' ][ 'customers' ]   = $customer_data;
            $response[ 'data' ][ 'total' ]       = $total;
            $response[ 'data' ][ 'pages' ]       = $pages;
            $response[ 'data' ][ 'active_page' ] = $page;
        }

        echo json_encode( $response );
        exit ( 0 );
    }

    /**
     * Create or edit a customer.
     */
    public function executeSaveCustomer() {
        $response = array();
        $form = new AB_CustomerForm();

        do {
            if ( $this->_post[ 'name' ] !== '' ) {
                $form->bind( $this->getPost() );
                /** @var AB_Customer $customer */
                $customer = $form->save();
                if ( $customer ) {
                    $response[ 'status' ]   = 'ok';
                    $response[ 'customer' ] = array(
                        'id'      => $customer->id,
                        'name'    => $customer->name,
                        'phone'   => $customer->phone,
                        'email'   => $customer->email,
                        'notes'   => $customer->notes,
                        'jsonString' => json_encode( array(
                            'name'    => $customer->name,
                            'phone'   => $customer->phone,
                            'email'   => $customer->email,
                            'notes' => $customer->notes
                        ) )
                    );
                    break;
                }
            }
            $response[ 'status' ] = 'error';
            $response[ 'errors' ] = array( 'name' => array( 'required' ) );
        } while ( 0 );

        echo json_encode( $response );
        exit ( 0 );
    }

    /**
     * Import customers from CSV.
     */
    private function importCustomers() {
        $csv_mimetypes = array(
            'text/csv',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel'
        );

        if (in_array($this->_files['importCustomers']['type'], $csv_mimetypes)) {
            $file = fopen($this->_files['importCustomers']['tmp_name'], "r");
            while ($line = fgetcsv($file)){
                if (!empty($line[0])){
                    $customer = new AB_Customer();
                    $customer->set('name', $line[0]);
                    if (isset($line[1])){
                        $customer->set('phone', $line[1]);
                    }
                    if (isset($line[2])){
                        $customer->set('email', $line[2]);
                    }
                    $customer->save();
                }
            }
        }
    }

    /**
     * Get angulars template for new customer dialog.
     */
    public function executeGetNgNewCustomerDialogTemplate() {
        $this->render( 'ng-new_customer_dialog' );
        exit ( 0 );
    }

    /**
     * Delete a customer.
     */
    public function executeDeleteCustomer() {
        if ( $this->_post[ 'id' ] !== '' ) {
            $this->getWpdb()->query($this->getWpdb()->prepare("DELETE FROM ab_customer  WHERE id = %d",
                $this->_post[ 'id' ]
            ));
        }
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}