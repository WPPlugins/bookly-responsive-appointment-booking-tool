<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_CustomerController extends AB_Controller {

    const page_slug = 'ab-customers';

    protected function getPermissions()
    {
        return array(
            'executeSaveCustomer' => 'user',
            'executeGetNgNewCustomerDialogTemplate' => 'user',
        );
    }

    public function index()
    {
        if ( $this->hasParameter( 'import-customers' ) ) {
            $this->importCustomers();
        }

        $this->enqueueStyles( array(
            'module' => array(
                'css/customers.css' => array( 'ab-intlTelInput.css' ),
            ),
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            ),
            'frontend' => array(
                'css/intlTelInput.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/angular.min.js',
                'js/angular-sanitize.min.js',
                'js/angular-ui-utils-0.2.1.min.js',
                'js/angular-ui-date-0.0.8.js',
                'js/ng-new_customer_dialog.js' => array( 'jquery', 'ab-angular.min.js' ),
            ),
            'module' => array(
                'js/ng-app.js' => array(
                    'jquery',
                    'ab-angular.min.js',
                    'ab-angular-ui-utils-0.2.1.min.js',
                    'ab-angular-ui-date-0.0.8.js',
                    'ab-intlTelInput.utils.js',
                ),
            ),
            'frontend' => array(
                'js/intlTelInput.min.js' => array( 'jquery' ),
                'js/intlTelInput.utils.js' => array( 'jquery' )
            )
        ) );

        wp_localize_script( 'ab-ng-app.js', 'BooklyL10n', array(
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'wp_users'     => $this->getWpUsers(),
            'country'      => get_option( 'ab_settings_phone_default_country' ),
            'intlTelInput_utils' => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
            'please_select_at_least_one_row' => __( 'Please select at least one customer.', 'bookly' ),
        ) );

        $this->render( 'index' );
    }

    /**
     * Get list of customers.
     */
    public function executeGetCustomers()
    {
        $wpdb = $this->getWpdb();
        $items_per_page = 20;
        $response = array(
            'customers' => array(),
            'total'       => 0,
            'pages'       => 0,
            'active_page' => 0,
        );

        $page   = intval( $this->getParameter( 'page' ) );
        $sort   = in_array( $this->getParameter( 'sort' ), array( 'name', 'phone', 'email', 'notes', 'last_appointment', 'total_appointments', 'payments', 'wp_user' ) )
            ? $this->getParameter( 'sort' ) : 'name';
        $order  = in_array( $this->getParameter( 'order' ), array( 'asc', 'desc' ) ) ? $this->getParameter( 'order' ) : 'asc';
        $filter = $wpdb->_real_escape( $this->getParameter( 'filter' ) );

        $query = AB_Customer::query( 'c' );
        // WHERE
        if ( $filter !== '' ) {
            $query->whereLike( 'c.name', "%{$filter}%")
                  ->whereLike( 'c.phone', "%{$filter}%", 'OR' )
                  ->whereLike( 'c.email', "%{$filter}%", 'OR' );
        }
        $total = $query->count();

        $pages = ceil( $total / $items_per_page );
        if ( $page < 1 || $page > $pages ) {
            $page = 1;
        }

        $data = $query->select( 'c.*, MAX(a.start_date) AS last_appointment,
                COUNT(a.id) AS total_appointments,
                COALESCE(SUM(p.total),0) AS payments,
                wpu.display_name AS wp_user' )
            ->leftJoin( 'AB_CustomerAppointment', 'ca', 'ca.customer_id = c.id' )
            ->leftJoin( 'AB_Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'AB_Payment', 'p', 'p.customer_appointment_id = ca.id' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' )
            ->sortBy( $sort )
            ->order( $order )
            ->limit( $items_per_page )
            ->offset( ( $page - 1 ) * $items_per_page )
            ->fetchArray();

        array_walk( $data, function ( &$row ) {
            if ( $row['last_appointment'] ) {
                $row['last_appointment'] = AB_DateTimeUtils::formatDateTime( $row['last_appointment'] );
            }
            $row['payments'] = AB_Utils::formatPrice( $row['payments'] );
        } );

        // Populate response.
        $response[ 'customers' ]   = $data;
        $response[ 'total' ]       = $total;
        $response[ 'pages' ]       = $pages;
        $response[ 'active_page' ] = $page;

        wp_send_json_success( $response );
    }

    /**
     * Get WP users array.
     *
     * @return array
     */
    public function getWpUsers()
    {
        return get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) );
    }

    /**
     * Create or edit a customer.
     */
    public function executeSaveCustomer()
    {
        $response = array();
        $form = new AB_CustomerForm();

        do {
            if ( $this->getParameter( 'name' ) !== '' ) {
                $form->bind( $this->getPostParameters() );
                /** @var AB_Customer $customer */
                $customer = $form->save();
                if ( $customer ) {
                    $response[ 'success' ]  = true;
                    $response[ 'customer' ] = array(
                        'id'      => $customer->id,
                        'name'    => $customer->name,
                        'wp_user_id' => $customer->wp_user_id,
                        'phone'   => $customer->phone,
                        'email'   => $customer->email,
                        'notes'   => $customer->notes,
                        'jsonString' => json_encode( array(
                            'name'  => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'notes' => $customer->notes
                        ) )
                    );
                    break;
                }
            }
            $response[ 'success' ] = false;
            $response[ 'errors' ]  = array( 'name' => array( 'required' ) );
        } while ( 0 );

        wp_send_json( $response );
    }

    /**
     * Import customers from CSV.
     */
    private function importCustomers()
    {
        @ini_set( 'auto_detect_line_endings', true );

        $csv_mime_types = array(
            'text/csv',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel'
        );

        if ( in_array( $_FILES[ 'import_customers_file' ][ 'type' ], $csv_mime_types ) ) {
            $file = fopen ( $_FILES[ 'import_customers_file' ][ 'tmp_name' ], 'r' );
            while ( $line = fgetcsv( $file, null, $this->getParameter( 'import_customers_delimiter' ) ) ) {
                if ( !empty ( $line[ 0 ] ) ) {
                    $customer = new AB_Customer();
                    $customer->set( 'name', $line[ 0 ] );
                    if ( isset ( $line[ 1 ] ) ) {
                        $customer->set( 'phone', $line[ 1 ] );
                    }
                    if ( isset ( $line[ 2 ] ) ) {
                        $customer->set( 'email', $line[ 2 ] );
                    }
                    $customer->save();
                }
            }
        }
    }

    /**
     * Get angulars template for new customer dialog.
     */
    public function executeGetNgNewCustomerDialogTemplate()
    {
        $this->render( 'ng-new_customer_dialog', array(
            'custom_fields' => json_decode( get_option( 'ab_custom_fields' ) ),
            'module'        => $this->getParameter( 'module' ),
            'wp_users'      => $this->getWpUsers()
        ) );
        exit ( 0 );
    }

    /**
     * Delete a customer.
     */
    public function executeDeleteCustomer()
    {
        foreach ( $this->getParameter( 'ids' ) as $id ) {
            $customer = new AB_Customer();
            $customer->load( $id );
            $customer->deleteWithWPUser( (bool) $this->getParameter( 'with_wp_user' ) );
        }
        wp_send_json_success();
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