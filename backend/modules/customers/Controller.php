<?php
namespace BooklyLite\Backend\Modules\Customers;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Customers
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'ab-customers';

    protected function getPermissions()
    {
        return array(
            'executeSaveCustomer' => 'user',
        );
    }

    public function index()
    {
        if ( $this->hasParameter( 'import-customers' ) ) {
            $this->importCustomers();
        }

        $this->enqueueStyles( array(
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/customers.js' => array( 'ab-datatables.min.js', 'ab-ng-customer_dialog.js' ),
            ),
        ) );

        wp_localize_script( 'ab-customers.js', 'BooklyL10n', array(
            'edit'            => __( 'Edit', 'bookly' ),
            'are_you_sure'    => __( 'Are you sure?', 'bookly' ),
            'wp_users'        => get_users( array( 'fields' => array( 'ID', 'display_name' ), 'orderby' => 'display_name' ) ),
            'zeroRecords'     => __( 'No customers found.', 'bookly' ),
            'processing'      => __( 'Processing...', 'bookly' ),
            'edit_customer'   => __( 'Edit customer', 'bookly' ),
            'new_customer'    => __( 'New customer', 'bookly' ),
            'create_customer' => __( 'Create customer', 'bookly' ),
            'save'            => __( 'Save', 'bookly' ),
            'search'          => __( 'Quick search customer', 'bookly' ),
            'limitations'     => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>If you find the plugin useful for your business please consider buying a licence for the full version.<br>It costs just $59 and for this money you will get many useful functions, lifetime free updates and excellent support!<br>More information can be found here', 'bookly' ) . ': <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        $this->render( 'index' );
    }

    /**
     * Get list of customers.
     */
    public function executeGetCustomers()
    {
        global $wpdb;

        $columns = $this->getParameter( 'columns' );
        $order   = $this->getParameter( 'order' );
        $filter  = $this->getParameter( 'filter' );

        $query = Lib\Entities\Customer::query( 'c' );

        $total = $query->count();

        $query
            ->select( 'SQL_CALC_FOUND_ROWS c.*, MAX(a.start_date) AS last_appointment,
                COUNT(DISTINCT a.id) AS total_appointments,
                SUM(p.total)*COUNT(DISTINCT p.id)/COUNT(*) AS payments,
                wpu.display_name AS wp_user' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.customer_id = c.id' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' );

        if ( $filter != '' ) {
            $search_value = Lib\Query::escape( $filter );
            $query
                ->whereLike( 'c.name', "%{$search_value}%" )
                ->whereLike( 'c.phone', "%{$search_value}%", 'OR' )
                ->whereLike( 'c.email', "%{$search_value}%", 'OR' );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $query->limit( $this->getParameter( 'length' ) )->offset( $this->getParameter( 'start' ) );

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            $data[] = array(
                'id'                 => $row['id'],
                'name'               => $row['name'],
                'wp_user'            => $row['wp_user'],
                'wp_user_id'         => $row['wp_user_id'],
                'phone'              => $row['phone'],
                'email'              => $row['email'],
                'notes'              => $row['notes'],
                'last_appointment'   => $row['last_appointment'] ? Lib\Utils\DateTime::formatDateTime( $row['last_appointment'] ) : '',
                'total_appointments' => $row['total_appointments'],
                'payments'           => Lib\Utils\Common::formatPrice( $row['payments'] ),
            );
        }

        wp_send_json( array(
            'draw'            => ( int ) $this->getParameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => ( int ) $wpdb->get_var( 'SELECT FOUND_ROWS()' ),
            'data'            => $data,
        ) );
    }

    /**
     * Create or edit a customer.
     */
    public function executeSaveCustomer()
    {
        $response = array();
        $form = new Forms\Customer();

        do {
            if ( $this->getParameter( 'name' ) !== '' ) {
                $params = $this->getPostParameters();
                if ( ! $params['wp_user_id'] ) {
                    $params['wp_user_id'] = null;
                }
                $form->bind( $params );
                /** @var Lib\Entities\Customer $customer */
                $customer = $form->save();
                if ( $customer ) {
                    $response['success']  = true;
                    $response['customer'] = array(
                        'id'      => $customer->get( 'id' ),
                        'name'    => $customer->get( 'name' ),
                        'phone'   => $customer->get( 'phone' ),
                        'email'   => $customer->get( 'email' ),
                        'notes'   => $customer->get( 'notes' ),
                        'wp_user_id' => $customer->get( 'wp_user_id' ),
                        'jsonString' => json_encode( array(
                            'name'  => $customer->get( 'name' ),
                            'phone' => $customer->get( 'phone' ),
                            'email' => $customer->get( 'email' ),
                            'notes' => $customer->get( 'notes' )
                        ) )
                    );
                    break;
                }
            }
            $response['success'] = false;
            $response['errors']  = array( 'name' => array( 'required' ) );
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

        if ( in_array( $_FILES['import_customers_file']['type'], $csv_mime_types ) ) {
            $file = fopen( $_FILES['import_customers_file']['tmp_name'], 'r' );
            while ( $line = fgetcsv( $file, null, $this->getParameter( 'import_customers_delimiter' ) ) ) {
                if ( $line[0] != '' ) {
                    $customer = new Lib\Entities\Customer();
                    $customer->set( 'name', $line[0] );
                    if ( isset( $line[1] ) ) {
                        $customer->set( 'phone', $line[1] );
                    }
                    if ( isset( $line[2] ) ) {
                        $customer->set( 'email', $line[2] );
                    }
                    $customer->save();
                }
            }
        }
    }

    /**
     * Delete customers.
     */
    public function executeDeleteCustomers()
    {
        foreach ( $this->getParameter( 'data', array() ) as $id ) {
            $customer = new Lib\Entities\Customer();
            $customer->load( $id );
            $customer->deleteWithWPUser( (bool) $this->getParameter( 'with_wp_user' ) );
        }
        wp_send_json_success();
    }

    /**
     * Export Customers to CSV
     */
    public function executeExportCustomers() { exit; }

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