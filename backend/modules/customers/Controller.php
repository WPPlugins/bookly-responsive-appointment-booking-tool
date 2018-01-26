<?php
namespace BooklyLite\Backend\Modules\Customers;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Customers
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-customers';

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
            ),
            'frontend' => array(
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/customers.js' => array( 'bookly-datatables.min.js', 'bookly-ng-customer_dialog.js' ),
            ),
        ) );

        wp_localize_script( 'bookly-customers.js', 'BooklyL10n', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'first_last_name' => (int) Lib\Config::showFirstLastName(),
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
            'limitations'     => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
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
            ->select( 'SQL_CALC_FOUND_ROWS c.*,
                (
                    SELECT MAX(a.start_date) FROM ' . Lib\Entities\Appointment::getTableName() . ' a
                        LEFT JOIN ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca ON ca.appointment_id = a.id
                            WHERE ca.customer_id = c.id
                ) AS last_appointment,
                (
                    SELECT COUNT(DISTINCT ca.appointment_id) FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca
                        WHERE ca.customer_id = c.id
                ) AS total_appointments,
                (
                    SELECT SUM(p.total) FROM ' . Lib\Entities\Payment::getTableName() . ' p
                        WHERE p.id IN (
                            SELECT DISTINCT ca.payment_id FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca
                                WHERE ca.customer_id = c.id
                        )
                ) AS payments,
                wpu.display_name AS wp_user' )
            ->tableJoin( $wpdb->users, 'wpu', 'wpu.ID = c.wp_user_id' )
            ->groupBy( 'c.id' );

        if ( $filter != '' ) {
            $search_value = Lib\Query::escape( $filter );
            $query
                ->whereLike( 'c.full_name', "%{$search_value}%" )
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
                'full_name'          => $row['full_name'],
                'first_name'         => $row['first_name'],
                'last_name'          => $row['last_name'],
                'wp_user'            => $row['wp_user'],
                'wp_user_id'         => $row['wp_user_id'],
                'phone'              => $row['phone'],
                'email'              => $row['email'],
                'notes'              => $row['notes'],
                'birthday'           => $row['birthday'],
                'last_appointment'   => $row['last_appointment'] ? Lib\Utils\DateTime::formatDateTime( $row['last_appointment'] ) : '',
                'total_appointments' => $row['total_appointments'],
                'payments'           => Lib\Utils\Price::format( $row['payments'] ),
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
            if ( ( get_option( 'bookly_cst_first_last_name' ) && $this->getParameter( 'first_name' ) !== '' && $this->getParameter( 'last_name' ) !== '' ) || ( ! get_option( 'bookly_cst_first_last_name' ) && $this->getParameter( 'full_name' ) !== '' ) ) {
                $params = $this->getPostParameters();
                if ( ! $params['wp_user_id'] ) {
                    $params['wp_user_id'] = null;
                }
                if ( ! $params['birthday'] ) {
                    $params['birthday'] = null;
                }
                $form->bind( $params );
                /** @var Lib\Entities\Customer $customer */
                $customer = $form->save();
                if ( $customer ) {
                    $response['success']  = true;
                    $response['customer'] = array(
                        'id'         => $customer->getId(),
                        'wp_user_id' => $customer->getWpUserId(),
                        'full_name'  => $customer->getFullName(),
                        'first_name' => $customer->getFirstName(),
                        'last_name'  => $customer->getLastName(),
                        'phone'      => $customer->getPhone(),
                        'email'      => $customer->getEmail(),
                        'notes'      => $customer->getNotes(),
                        'birthday'   => $customer->getBirthday(),
                    );
                    break;
                }
            }
            $response['success'] = false;
            $response['errors'] = array();
            if (get_option( 'bookly_cst_first_last_name' )) {
                if ( $this->getParameter( 'first_name' ) == '' ) {
                    $response['errors']['first_name']  = array( 'required' );
                }
                if ( $this->getParameter( 'last_name' ) == '' ) {
                    $response['errors']['last_name']  = array( 'required' );
                }
            } else {
                $response['errors'] = array( 'full_name' => array( 'required' ) );
            }
        } while ( 0 );

        wp_send_json( $response );
    }

    /**
     * Import customers from CSV.
     */
    private function importCustomers()
    {
        @ini_set( 'auto_detect_line_endings', true );
        $fields = array();
        foreach ( array( 'full_name', 'first_name', 'last_name', 'phone', 'email', 'birthday' ) as $field ) {
            if ( $this->getParameter( $field ) ) {
                $fields[] = $field;
            }
        }
        $file = fopen( $_FILES['import_customers_file']['tmp_name'], 'r' );
        while ( $line = fgetcsv( $file, null, $this->getParameter( 'import_customers_delimiter' ) ) ) {
            if ( $line[0] != '' ) {
                $customer = new Lib\Entities\Customer();
                foreach ( $line as $number => $value ) {
                    if ( $number < count( $fields ) ) {
                        if ( $fields[ $number ] == 'birthday' ) {
                            $dob = date_create( $value );
                            if ( $dob !== false ) {
                                $customer->setBirthday( $dob->format( 'Y-m-d' ) );
                            }
                        } else {
                            $method = 'set' . implode( '', array_map( 'ucfirst', explode( '_', $fields[ $number ] ) ) );
                            $customer->$method( $value );
                        }
                    }
                }
                $customer->save();
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

}