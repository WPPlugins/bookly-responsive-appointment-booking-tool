<?php
namespace BooklyLite\Backend\Modules\Appointments;

use BooklyLite\Lib;
use BooklyLite\Lib\DataHolders\Booking as DataHolders;

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
                'css/select2.min.css',
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'   => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'  => array( 'jquery' ),
                'js/select2.full.min.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/appointments.js' => array( 'bookly-datatables.min.js' ), ),
        ) );

        // Custom fields without captcha & text content field.
        $custom_fields = (array) Lib\Proxy\CustomFields::getWhichHaveData();

        wp_localize_script( 'bookly-appointments.js', 'BooklyL10n', array(
            'csrf_token'    => Lib\Utils\Common::getCsrfToken(),
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
            'show_notes'    => Lib\Config::showNotes(),
            'cf_columns'    => array_map( function ( $custom_field ) { return $custom_field->id; }, $custom_fields ),
            'filter'        => (array) get_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', true ),
            'no_result_found' => __( 'No result found', 'bookly' ),
            'limitations'   => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        // Filters data
        $staff_members = Lib\Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->where( 'id', 1 )->fetchArray();
        $customers = Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.first_name, c.last_name' )->fetchArray();
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
        $postfix_any = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );

        $query = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'a.id,
                ca.payment_id,
                ca.status,
                ca.id        AS ca_id,
                ca.notes,
                ca.extras,
                a.start_date,
                a.staff_any,
                c.full_name  AS customer_full_name,
                c.phone      AS customer_phone,
                c.email      AS customer_email,
                st.full_name AS staff_name,
                p.paid       AS payment,
                p.total      AS payment_total,
                p.type       AS payment_type,
                p.status     AS payment_status,
                COALESCE(s.title, a.custom_service_name) AS service_title,
                TIME_TO_SEC(TIMEDIFF(a.end_date, a.start_date)) + a.extras_duration AS service_duration' )
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

        if ( $filter['staff'] != '' ) {
            $query->where( 'a.staff_id', $filter['staff'] );
        }

        if ( $filter['customer'] != '' ) {
            $query->where( 'ca.customer_id', $filter['customer'] );
        }

        if ( $filter['service'] != '' ) {
            $query->where( 'a.service_id', $filter['service'] ?: null );
        }

        if ( $filter['status'] != '' ) {
            $query->where( 'ca.status', $filter['status'] );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? Lib\Query::ORDER_DESCENDING : Lib\Query::ORDER_ASCENDING );
        }

        $custom_fields = array();
        $fields_data = (array) Lib\Proxy\CustomFields::getWhichHaveData();
        foreach ( $fields_data as $field_data ) {
            $custom_fields[ $field_data->id ] = '';
        }

        $data = array();
        foreach ( $query->fetchArray() as $row ) {
            // Service duration.
            $service_duration = Lib\Utils\DateTime::secondsToInterval( $row['service_duration'] );
            // Appointment status.
            $row['status'] = Lib\Entities\CustomerAppointment::statusToString( $row['status'] );
            // Payment title.
            $payment_title = '';
            if ( $row['payment'] !== null ) {
                $payment_title = Lib\Utils\Price::format( $row['payment'] );
                if ( $row['payment'] != $row['payment_total'] ) {
                    $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, Lib\Utils\Price::format( $row['payment_total'] ) );
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
            foreach ( (array) Lib\Proxy\CustomFields::getForCustomerAppointment( $customer_appointment ) as $custom_field ) {
                $custom_fields[ $custom_field['id'] ] = $custom_field['value'];
            }

            $data[] = array(
                'id'         => $row['id'],
                'start_date' => Lib\Utils\DateTime::formatDateTime( $row['start_date'] ),
                'staff'      => array(
                    'name' => $row['staff_name'] . ( $row['staff_any'] ? $postfix_any : '' ),
                ),
                'customer'      => array(
                    'full_name' => $row['customer_full_name'],
                    'phone'     => $row['customer_phone'],
                    'email'     => $row['customer_email'],
                ),
                'service'    => array(
                    'title'    => $row['service_title'],
                    'duration' => $service_duration,
                    'extras'   => (array) Lib\Proxy\ServiceExtras::getInfo( json_decode( $row['extras'], true ), false ),
                ),
                'status'        => $row['status'],
                'payment'       => $payment_title,
                'notes'         => $row['notes'],
                'custom_fields' => $custom_fields,
                'ca_id'         => $row['ca_id'],
                'payment_id'    => $row['payment_id'],
            );

            $custom_fields = array_map( function () { return ''; }, $custom_fields );
        }

        unset( $filter['date'] );
        update_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', $filter );

        wp_send_json( array(
            'draw'            => (int) $this->getParameter( 'draw' ),
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
                switch ( $ca->getStatus() ) {
                    case Lib\Entities\CustomerAppointment::STATUS_PENDING:
                    case Lib\Entities\CustomerAppointment::STATUS_WAITLISTED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                        break;
                    case Lib\Entities\CustomerAppointment::STATUS_APPROVED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                        break;
                }
                Lib\NotificationSender::sendSingle(
                    DataHolders\Simple::create( $ca ),
                    null,
                    array( 'cancellation_reason' => $this->getParameter( 'reason' ) )
                );
            }
            $ca->deleteCascade();
        }
        wp_send_json_success();
    }
}