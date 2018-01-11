<?php
namespace BooklyLite\Backend\Modules\Appointments;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Appointments
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-appointments';

    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array(
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'  => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/appointments.js' => array( 'bookly-datatables.min.js' ), ),
        ) );

        // Custom fields without captcha field.
        $custom_fields = array_filter( json_decode( get_option( 'bookly_custom_fields' ) ), function( $field ) {
            return ! in_array( $field->type, array( 'captcha', 'text-content' ) );
        } );

        wp_localize_script( 'bookly-appointments.js', 'BooklyL10n', array(
            'tomorrow'      => __( 'Tomorrow', 'bookly' ),
            'today'         => __( 'Today', 'bookly' ),
            'yesterday'     => __( 'Yesterday', 'bookly' ),
            'last_7'        => __( 'Last 7 Days', 'bookly' ),
            'last_30'       => __( 'Last 30 Days', 'bookly' ),
            'this_month'    => __( 'This Month', 'bookly' ),
            'next_month'    => __( 'Next Month', 'bookly' ),
            'custom_range'  => __( 'Custom Range', 'bookly' ),
            'apply'         => __( 'Apply', 'bookly' ),
            'cancel'        => __( 'Cancel', 'bookly' ),
            'to'            => __( 'To', 'bookly' ),
            'from'          => __( 'From', 'bookly' ),
            'calendar'      => array(
                'longMonths'  => array_values( $wp_locale->month ),
                'shortMonths' => array_values( $wp_locale->month_abbrev ),
                'longDays'    => array_values( $wp_locale->weekday ),
                'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
            ),
            'mjsDateFormat' => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'startOfWeek'   => (int) get_option( 'start_of_week' ),
            'are_you_sure'  => __( 'Are you sure?', 'bookly' ),
            'zeroRecords'   => __( 'No appointments for selected period.', 'bookly' ),
            'processing'    => __( 'Processing...', 'bookly' ),
            'edit'          => __( 'Edit', 'bookly' ),
            'cf_columns'    => array_map( function ( $custom_field ) { return $custom_field->id; }, $custom_fields ),
            'filter'        => (array) get_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', true ),
            'limitations'   => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        // Filters data
        $staff_members = Lib\Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->fetchArray();
        $customers = Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.name' )->fetchArray();
        $services  = Lib\Entities\Service::query( 's' )->select( 's.id, s.title' )->where( 'type', Lib\Entities\Service::TYPE_SIMPLE )->fetchArray();

        $this->render( 'index', compact( 'custom_fields', 'staff_members', 'customers', 'services' ) );
    }

    /**
     * Get list of appointments.
     */
    public function executeGetAppointments()
    {
        $columns = $this->getParameter( 'columns' );
        $order   = $this->getParameter( 'order' );
        $filter  = $this->getParameter( 'filter' );

        $query = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'a.id,
                ca.payment_id,
                ca.status,
                ca.id        AS ca_id,
                ca.extras,
                a.start_date,
                a.extras_duration,
                c.name       AS customer_name,
                c.phone      AS customer_phone,
                c.email      AS customer_email,
                s.title      AS service_title,
                s.duration   AS service_duration,
                st.full_name AS staff_name,
                p.paid       AS payment,
                p.total      AS payment_total,
                p.type       AS payment_type,
                p.status     AS payment_status' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = st.id AND ss.service_id = s.id' );

        $total = $query->count();

        if ( $filter['id'] != '' ) {
            $query->where( 'a.id', $filter['id'] );
        }

        list ( $start, $end ) = explode( ' - ', $filter['date'], 2 );
        $end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) );
        $query->whereBetween( 'a.start_date', $start, $end );

        if ( $filter['staff'] != -1 ) {
            $query->where( 'a.staff_id', $filter['staff'] );
        }

        if ( $filter['customer'] != -1 ) {
            $query->where( 'ca.customer_id', $filter['customer'] );
        }

        if ( $filter['service']  != -1 ) {
            $query->where( 'a.service_id', $filter['service'] );
        }

        if ( $filter['status'] != -1 ) {
            $query->where( 'ca.status', $filter['status'] );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $custom_fields = array();
        $fields_data = array_filter( json_decode( get_option( 'bookly_custom_fields' ) ), function( $field ) {
            return ! in_array( $field->type, array( 'captcha', 'text-content' ) );
        } );
        foreach ( $fields_data as $field_data ) {
            $custom_fields[ $field_data->id ] = '';
        }

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            // Service duration.
            $service_duration = Lib\Utils\DateTime::secondsToInterval( $row['service_duration'] );
            if ( $row['extras_duration'] > 0 ) {
                $service_duration .= ' + ' . Lib\Utils\DateTime::secondsToInterval( $row['extras_duration'] );
            }
            // Appointment status.
            $row['status'] = Lib\Entities\CustomerAppointment::statusToString( $row['status'] );

            // Payment title.
            $payment_title = '';
            if ( $row['payment'] !== null ) {
                $payment_title = Lib\Utils\Common::formatPrice( $row['payment'] );
                if ( $row['payment'] != $row['payment_total'] ) {
                    $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, Lib\Utils\Common::formatPrice( $row['payment_total'] ) );
                }
                $payment_title .= sprintf(
                    ' %s <span%s>%s</span>',
                    Lib\Entities\Payment::typeToString( $row['payment_type'] ),
                    $row['payment_status'] == Lib\Entities\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                    Lib\Entities\Payment::statusToString( $row['payment_status'] )
                );
            }
            // Custom fields
            $customer_appointment = new Lib\Entities\CustomerAppointment();
            $customer_appointment->load( $row['ca_id'] );
            foreach ( $customer_appointment->getCustomFields() as $custom_field ) {
                $custom_fields[ $custom_field['id'] ] = $custom_field['value'];
            }

            $data[] = array(
                'id'         => $row['id'],
                'start_date' => Lib\Utils\DateTime::formatDateTime( $row['start_date'] ),
                'staff'      => array(
                    'name' => $row['staff_name'],
                ),
                'customer'   => array(
                    'name'  => $row['customer_name'],
                    'phone' => $row['customer_phone'],
                    'email' => $row['customer_email'],
                ),
                'service'    => array(
                    'title'    => $row['service_title'],
                    'duration' => $service_duration,
                    'extras'   => apply_filters( 'bookly_service_extras_get_data_for_appointment', array(), $row['extras'], false ),
                ),
                'status'        => $row['status'],
                'payment'       => $payment_title,
                'custom_fields' => $custom_fields,
                'ca_id'         => $row['ca_id'],
                'payment_id'    => $row['payment_id'],
            );

            $custom_fields = array_map( function () { return ''; }, $custom_fields );
        }

        unset( $filter['date'] );
        update_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', $filter );

        wp_send_json( array(
            'draw'            => ( int ) $this->getParameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => count( $data ),
            'data'            => $data,
        ) );
    }

    /**
     * Delete customer appointments.
     */
    public function executeDeleteCustomerAppointments()
    {
        /** @var Lib\Entities\CustomerAppointment $ca */
        foreach ( Lib\Entities\CustomerAppointment::query()->whereIn( 'id', $this->getParameter( 'data', array() ) )->find() as $ca ) {
            if ( $this->getParameter( 'notify' ) ) {
                if ( $ca->get('status') === Lib\Entities\CustomerAppointment::STATUS_PENDING ) {
                    $ca->set( 'status', Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                } else { // STATUS_APPROVED
                    $ca->set( 'status', Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                }
                \BooklyLite\Lib\NotificationSender::send( $ca, array( 'cancellation_reason' => $this->getParameter( 'reason' ) ) );
            }
            $ca->deleteCascade();
        }
        wp_send_json_success();
    }

    /**
     * Override parent method to add 'wp_ajax_bookly_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}