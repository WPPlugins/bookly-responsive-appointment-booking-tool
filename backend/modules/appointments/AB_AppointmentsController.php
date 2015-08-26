<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_AppointmentsController
 */
class AB_AppointmentsController extends AB_Controller {

    public function index()
    {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array(
                'css/intlTelInput.css',
            ),
            'backend' => array(
                'css/jquery-ui-theme/jquery-ui.min.css',
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/daterangepicker.css',
                'css/chosen.min.css'
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/angular.min.js',
                'js/angular-sanitize.min.js' => array( 'ab-angular.min.js' ),
                'js/angular-ui-utils-0.2.1.min.js' => array( 'ab-angular.min.js' ),
                'js/ng-new_customer_dialog.js' => array( 'ab-intlTelInput.min.js', 'ab-angular.min.js' ),
                'js/angular-ui-date-0.0.8.js' => array( 'ab-angular.min.js' ),
                'js/moment.min.js',
                'js/daterangepicker.js' => array( 'jquery' ),
                'js/chosen.jquery.min.js' => array( 'jquery' ),
                'js/ng-edit_appointment_dialog.js' => array( 'ab-angular-ui-date-0.0.8.js', 'jquery-ui-datepicker' ),
            ),
            'frontend' => array(
                'js/intlTelInput.min.js',
            ),
            'module' => array(
                'js/ng-app.js' => array( 'jquery', 'ab-angular.min.js', 'ab-angular-ui-utils-0.2.1.min.js' ),
            ),
        ) );

        wp_localize_script( 'ab-ng-app.js', 'BooklyL10n', array(
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
            'editAppointment' => __( 'Edit appointment', 'bookly' ),
            'newAppointment'  => __( 'New appointment', 'bookly' ),
            'longMonths'    => array_values( $wp_locale->month ),
            'shortMonths'   => array_values( $wp_locale->month_abbrev ),
            'shortDays'     => array_values( $wp_locale->weekday_abbrev ),
            'dpDateFormat'  => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_JQUERY_DATEPICKER ),
            'mjsDateFormat' => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_MOMENT_JS ),
            'startOfWeek'   => (int) get_option( 'start_of_week' ),
            'country'       => get_option( 'ab_settings_phone_default_country' ),
            'intlTelInput_utils' => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
            'please_select_at_least_one_row' => __( 'Please select at least one appointment.', 'bookly' ),
        ));

        $this->render( 'index' );
    }

    /**
     * Get list of appointments.
     */
    public function executeGetAppointments()
    {
        $response = array(
            'appointments' => array(),
            'total'       => 0,
            'pages'       => 0,
            'active_page' => 0
        );

        $page   = intval( $this->getParameter( 'page' ) );
        $sort   = in_array( $this->getParameter( 'sort' ), array( 'staff_name', 'service_title', 'start_date', 'price' ) )
            ? $this->getParameter( 'sort' ) : 'start_date';
        $order  = in_array( $this->getParameter( 'order' ), array( 'asc', 'desc' ) ) ? $this->getParameter( 'order' ) : 'asc';

        $start_date = new DateTime( $this->getParameter( 'date_start' ) );
        $start_date = $start_date->format( 'Y-m-d H:i:s' );
        $end_date   = new DateTime( $this->getParameter( 'date_end' ) );
        $end_date   = $end_date->modify( '+1 day' )->format( 'Y-m-d H:i:s' );

        $items_per_page = 20;
        $total = AB_Appointment::query()->whereBetween( 'start_date', $start_date, $end_date )->count();
        $pages = ceil( $total / $items_per_page );
        if ( $page < 1 || $page > $pages ) {
            $page = 1;
        }

        if ( $total ) {
            $query = AB_CustomerAppointment::query( 'ca' )
                ->select( 'ca.id,
                       ca.number_of_persons,
                       ca.coupon_discount,
                       ca.coupon_deduction,
                       ca.appointment_id,
                       a.start_date,
                       a.end_date,
                       a.staff_id,
                       st.full_name AS staff_name,
                       s.title      AS service_title,
                       s.duration   AS service_duration,
                       c.name       AS customer_name,
                       ss.price' )
                ->leftJoin( 'AB_Appointment', 'a', 'a.id = ca.appointment_id' )
                ->leftJoin( 'AB_Service', 's', 's.id = a.service_id' )
                ->leftJoin( 'AB_Customer', 'c', 'c.id = ca.customer_id' )
                ->leftJoin( 'AB_Staff', 'st', 'st.id = a.staff_id' )
                ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = st.id AND ss.service_id = s.id')
                ->whereBetween( 'a.start_date', $start_date, $end_date )
                ->sortBy( $sort )
                ->order( $order );

            // LIMIT.
            $start = ( $page - 1 ) * $items_per_page;
            $query->offset( $start )->limit( $items_per_page );

            $rows = $query->fetchArray();
            foreach ( $rows as &$row ) {
                $row['price'] *= $row['number_of_persons'];
                $row['price']  = AB_Utils::formatPrice( $row['price'] );
                $row['start_date_f'] = AB_DateTimeUtils::formatDateTime( $row['start_date'] );
                $row['service_duration'] = AB_DateTimeUtils::secondsToInterval( $row['service_duration'] );
            }

            // Populate response.
            $response['appointments'] = $rows;
            $response['total']        = $total;
            $response['pages']        = $pages;
            $response['active_page']  = $page;
        }

        wp_send_json_success( $response );
    }

    /**
     * Delete customer appointment.
     */
    public function executeDeleteCustomerAppointment()
    {
        if ( $this->hasParameter( 'ids' ) ) {
            foreach ( $this->getParameter( 'ids' ) as $id ) {
                $customer_appointment = new AB_CustomerAppointment();
                $customer_appointment->load( $id );

                $appointment = new AB_Appointment();
                $appointment->load( $customer_appointment->get( 'appointment_id' ) );

                $customer_appointment->delete();

                // Delete appointment, if there aren't customers.
                $count = AB_CustomerAppointment::query()->where( 'appointment_id', $customer_appointment->get( 'appointment_id' ) )->count();

                if ( ! $count ) {
                    $appointment->delete();
                } else {
                    $appointment->handleGoogleCalendar();
                }
            }
        }
        wp_send_json_success();
    }

    /**
     * Export Appointment to CSV
     */
    public function executeExportToCSV()
    {
        $start_date = new DateTime( $this->getParameter( 'date_start' ) );
        $start_date = $start_date->format( 'Y-m-d H:i:s' );
        $end_date   = new DateTime( $this->getParameter( 'date_end' ) );
        $end_date   = $end_date->modify( '+1 day' )->format( 'Y-m-d H:i:s' );
        $delimiter  = $this->getParameter( 'delimiter', ',' );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=Appointments.csv' );

        $header = array(
            __( 'Booking Time', 'bookly' ),
            __( 'Staff Member', 'bookly' ),
            __( 'Service',      'bookly' ),
            __( 'Duration',     'bookly' ),
            __( 'Price',        'bookly' ),
            __( 'Customer',     'bookly' ),
            __( 'Phone',        'bookly' ),
            __( 'Email',        'bookly' ),
        );

        $custom_fields = array();
        $fields_data = json_decode( get_option( 'ab_custom_fields' ) );
        foreach ( $fields_data as $field_data ) {
            $custom_fields[$field_data->id] = '';
            $header[] = $field_data->label;
        }

        $output = fopen( 'php://output', 'w' );
        fwrite( $output, pack( 'CCC', 0xef, 0xbb, 0xbf ) );
        fputcsv( $output, $header, $delimiter );
        $rows =  AB_CustomerAppointment::query()
            ->select( 'r.id,
               r.number_of_persons,
               r.coupon_discount,
               r.coupon_deduction,
               st.full_name  AS staff_name,
               s.title       AS service_title,
               s.duration    AS service_duration,
               c.name        AS customer_name,
               c.phone       AS customer_phone,
               c.email       AS customer_email,
               ss.price,
               a.start_date' )
            ->leftJoin( 'AB_Appointment', 'a', 'a.id = r.appointment_id' )
            ->leftJoin( 'AB_Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'AB_Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'AB_Customer', 'c', 'c.id = r.customer_id' )
            ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = st.id AND ss.service_id = s.id' )
            ->whereBetween( 'a.start_date', $start_date, $end_date )
            ->sortBy( 'a.start_date' )
            ->order( AB_Query::ORDER_DESCENDING )
            ->fetchArray();

        foreach( $rows as $row ) {
            $row['price'] *= $row['number_of_persons'];

            $row_data = array(
                $row['start_date'],
                $row['staff_name'],
                $row['service_title'],
                AB_DateTimeUtils::secondsToInterval( $row['service_duration'] ),
                AB_Utils::formatPrice( $row['price'] ),
                $row['customer_name'],
                $row['customer_phone'],
                $row['customer_email'],
            );

            $customer_appointment = new AB_CustomerAppointment();
            $customer_appointment->load( $row['id'] );
            foreach ( $customer_appointment->getCustomFields() as $custom_field ) {
                $custom_fields[$custom_field['id']] = $custom_field['value'];
            }

            fputcsv( $output, array_merge( $row_data, $custom_fields ), $delimiter );

            $custom_fields = array_map( function () { return ''; }, $custom_fields );
        }
        fclose( $output );

        exit();
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