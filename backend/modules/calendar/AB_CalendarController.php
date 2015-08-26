<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CalendarController
 */
class AB_CalendarController extends AB_Controller {

    protected function getPermissions()
    {
        return array( '_this' => 'user' );
    }

    public function index()
    {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array(
                'css/intlTelInput.css',
            ),
            'module' => array(
                'css/calendar.css',
                'css/fullcalendar.min.css',
            ),
            'backend' => array(
                'css/chosen.min.css',
                'css/jquery-ui-theme/jquery-ui.min.css',
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'js/angular.min.js'                => array( 'jquery' ),
                'js/angular-ui-date-0.0.8.js'      => array( 'ab-angular.min.js' ),
                'js/ng-new_customer_dialog.js'     => array( 'ab-angular.min.js' ),
                'js/moment.min.js'                 => array( 'jquery' ),
                'bootstrap/js/bootstrap.min.js'    => array( 'jquery' ),
                'js/chosen.jquery.min.js'          => array( 'jquery' ),
                'js/ng-edit_appointment_dialog.js' => array( 'ab-angular-ui-date-0.0.8.js', 'jquery-ui-datepicker' ),
            ),
            'module' => array(
                'js/fullcalendar.min.js'   => array( 'ab-moment.min.js' ),
                'js/fc-multistaff-view.js' => array( 'ab-fullcalendar.min.js' ),
                'js/calendar.js'           => array( 'ab-fc-multistaff-view.js', 'ab-intlTelInput.min.js' ),
            ),
            'frontend' => array(
                'js/intlTelInput.min.js'   => array( 'jquery' ),
            )
        ) );

        $slot_length_minutes = get_option( 'ab_settings_time_slot_length', '15' );
        $slot = new DateInterval( 'PT' . $slot_length_minutes . 'M' );

        $this->staff_members = AB_Utils::isCurrentUserAdmin()
            ? AB_Staff::query()->sortBy( 'position' )->find()
            : AB_Staff::query()->where( 'wp_user_id', get_current_user_id() )->find();


        wp_localize_script( 'ab-calendar.js', 'BooklyL10n', array(
            'slotDuration'     => $slot->format( '%H:%I:%S' ),
            'shortMonths'      => array_values( $wp_locale->month_abbrev ),
            'longMonths'       => array_values( $wp_locale->month ),
            'shortDays'        => array_values( $wp_locale->weekday_abbrev ),
            'longDays'         => array_values( $wp_locale->weekday ),
            'AM'               => $wp_locale->meridiem[ 'AM' ],
            'PM'               => $wp_locale->meridiem[ 'PM' ],
            'dpDateFormat'     => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_JQUERY_DATEPICKER ),
            'mjsDateFormat'    => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_MOMENT_JS ),
            'mjsTimeFormat'    => AB_DateTimeUtils::convertFormat( 'time', AB_DateTimeUtils::FORMAT_MOMENT_JS ),
            'today'            => __( 'Today', 'bookly' ),
            'week'             => __( 'Week',  'bookly' ),
            'day'              => __( 'Day',   'bookly' ),
            'month'            => __( 'Month', 'bookly' ),
            'allDay'           => __( 'All Day', 'bookly' ),
            'noStaffSelected'  => __( 'No staff selected', 'bookly' ),
            'newAppointment'   => __( 'New appointment',   'bookly' ),
            'editAppointment'  => __( 'Edit appointment',  'bookly' ),
            'are_you_sure'     => __( 'Are you sure?',     'bookly' ),
            'startOfWeek'      => (int) get_option( 'start_of_week' ),
            'country'          => get_option( 'ab_settings_phone_default_country' ),
            'intlTelInput_utils' => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
        ) );

        $this->render( 'calendar' );
    }

    /**
     * Get data for FullCalendar.
     *
     * @return json
     */
    public function executeGetStaffAppointments()
    {
        $result        = array();
        $staff_members = array();
        $one_day       = new DateInterval( 'P1D' );
        $start_date    = new DateTime( $this->getParameter( 'start' ) );
        $end_date      = new DateTime( $this->getParameter( 'end' ) );
        // FullCalendar sends end date as 1 day further.
        $end_date->sub( $one_day );

        if ( AB_Utils::isCurrentUserAdmin() ) {
            $staff_members = AB_Staff::query()
                ->where( 'id', 1 )
                ->find();
        } else {
            $staff_members[] = AB_Staff::query()
                ->where( 'wp_user_id', get_current_user_id() )
                ->findOne();
        }

        foreach ( $staff_members as $staff ) {
            /** @var AB_Staff $staff */
            $result = array_merge( $result, $staff->getAppointmentsForFC( $start_date, $end_date ) );

            // Schedule.
            $items = $staff->getScheduleItems();
            $day   = clone $start_date;
            // Find previous day end time.
            $last_end = clone $day;
            $last_end->sub( $one_day );
            $w = $day->format( 'w' );
            $end_time = $items[ $w > 0 ? $w : 7 ]->get( 'end_time' );
            if ( $end_time !== null ) {
                $end_time = explode( ':', $end_time );
                $last_end->setTime( $end_time[0], $end_time[1] );
            } else {
                $last_end->setTime( 24, 0 );
            }
            // Do the loop.
            while ( $day <= $end_date ) {
                do {
                    /** @var AB_StaffScheduleItem $item */
                    $item = $items[ $day->format( 'w' ) + 1 ];
                    if ( $item->get( 'start_time' ) && ! $staff->isOnHoliday( $day ) ) {
                        $start = $last_end->format( 'Y-m-d H:i:s' );
                        $end   = $day->format( 'Y-m-d '.$item->get( 'start_time' ) );
                        if ( $start < $end ) {
                            $result[] = array(
                                'start'     => $start,
                                'end'       => $end,
                                'rendering' => 'background',
                                'staffId'   => $staff->get( 'id' ),
                            );
                        }
                        $last_end = clone $day;
                        $end_time = explode( ':', $item->get( 'end_time' ) );
                        $last_end->setTime( $end_time[0], $end_time[1] );

                        // Breaks.
                        foreach ( $item->getBreaksList() as $break ) {
                            $result[] = array(
                                'start'     => $day->format( 'Y-m-d '.$break['start_time'] ),
                                'end'       => $day->format( 'Y-m-d '.$break['end_time'] ),
                                'rendering' => 'background',
                                'staffId'   => $staff->get( 'id' ),
                            );
                        }

                        break;
                    }

                    $result[] = array(
                        'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                        'end'       => $day->format( 'Y-m-d 24:00:00' ),
                        'rendering' => 'background',
                        'staffId'   => $staff->get( 'id' ),
                    );
                    $last_end = clone $day;
                    $last_end->setTime( 24, 0 );

                } while ( 0 );

                $day->add( $one_day );
            }

            if ( $last_end->format( 'H' ) != 24 ) {
                $result[] = array(
                    'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                    'end'       => $last_end->format( 'Y-m-d 24:00:00' ),
                    'rendering' => 'background',
                    'staffId'   => $staff->get( 'id' ),
                );
            }
        }

        wp_send_json( $result );
    }

    /**
     * Get data needed for appointment form initialisation.
     */
    public function executeGetDataForAppointmentForm()
    {
        $result = array(
            'staff'         => array(),
            'customers'     => array(),
            'start_time'    => array(),
            'end_time'      => array(),
            'time_interval' => get_option( 'ab_settings_time_slot_length' ) * 60
        );

        // Staff list.
        $staff_members = AB_Staff::query()->where( 'id', 1 )->sortBy( 'position' )->find();


        /** @var AB_Staff $staff_member */
        foreach ( $staff_members as $staff_member ) {
            $services = array();
            foreach ( $staff_member->getStaffServices() as $staff_service ) {
                $services[] = array(
                    'id'       => $staff_service->service->get( 'id' ),
                    'title'    => sprintf(
                        '%s (%s)',
                        $staff_service->service->get( 'title' ),
                        AB_DateTimeUtils::secondsToInterval( $staff_service->service->get( 'duration' ) )
                    ),
                    'duration' => $staff_service->service->get( 'duration' ),
                    'capacity' => $staff_service->get( 'capacity' )
                );
            }
            $result['staff'][] = array(
                'id'        => 1,
                'full_name' => $staff_member->get( 'full_name' ),
                'services'  => $services
            );
        }

        // Customers list.
        foreach ( AB_Customer::query()->sortBy( 'name' )->find() as $customer ) {
            $name = $customer->get( 'name' );
            if ( $customer->get( 'email' ) != '' || $customer->get( 'phone' ) != '' ) {
                $name .= ' (' . trim( $customer->get( 'email' ) . ', ' . $customer->get( 'phone' ) , ', ') . ')';
            }

            $result[ 'customers' ][] = array(
                'id'                => $customer->get( 'id' ),
                'name'              => $name,
                'custom_fields'     => array(),
                'number_of_persons' => 1,
            );
        }

        // Time list.
        $ts_length  = AB_Config::getTimeSlotLength();
        $time_start = 0;
        $time_end   = DAY_IN_SECONDS * 2;

        // Run the loop.
        while ( $time_start <= $time_end ) {
            $slot = array(
                'value' => AB_DateTimeUtils::buildTimeString( $time_start, false ),
                'title' => AB_DateTimeUtils::formatTime( $time_start )
            );
            if ( $time_start < DAY_IN_SECONDS ) {
                $result['start_time'][] = $slot;
            }
            $result['end_time'][] = $slot;
            $time_start += $ts_length;
        }

        wp_send_json( $result );
    }

    /**
     * Get appointment data when editing an appointment.
     */
    public function executeGetDataForAppointment()
    {
        $response = array( 'success' => false, 'data' => array( 'customers' => array() ) );

        $appointment = new AB_Appointment();
        if ( $appointment->load( $this->getParameter( 'id' ) ) ) {
            $response['success'] = true;

            $info = AB_Appointment::query( 'a' )
                ->select( 'ss.capacity AS max_capacity, SUM( ca.number_of_persons ) AS total_number_of_persons, a.staff_id, a.service_id, a.start_date, a.end_date' )
                ->leftJoin( 'AB_CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                ->where( 'a.id', $appointment->get( 'id' ) )
                ->fetchRow();

            $response['data']['total_number_of_persons'] = $info['total_number_of_persons'];
            $response['data']['max_capacity']            = $info['max_capacity'];
            $response['data']['start_date']              = $info['start_date'];
            $response['data']['end_date']                = $info['end_date'];
            $response['data']['staff_id']                = $info['staff_id'];
            $response['data']['service_id']              = $info['service_id'];

            $customer_appointments = AB_CustomerAppointment::query()->where( 'appointment_id', $appointment->get( 'id' ) )->fetchArray();

            foreach ( $customer_appointments as $customer_appointment ) {
                $response[ 'data' ][ 'customers' ][] = array(
                    'id' => $customer_appointment['customer_id'],
                    'custom_fields' => $customer_appointment['custom_fields'] ? json_decode( $customer_appointment['custom_fields'], true ) : array(),
                    'number_of_persons' => $customer_appointment['number_of_persons']
                );
            }
        }
        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public function executeSaveAppointmentForm()
    {
        $response = array( 'success' => false );

        $start_date     = date( 'Y-m-d H:i:s', strtotime( $this->getParameter( 'start_date' ) ) );
        $end_date       = date( 'Y-m-d H:i:s', strtotime( $this->getParameter( 'end_date' ) ) );
        $staff_id       = $this->getParameter( 'staff_id' );
        $service_id     = $this->getParameter( 'service_id' );
        $appointment_id = $this->getParameter( 'id',  0 );
        $customers      = json_decode( $this->getParameter( 'customers', '[]' ), true );

        $staff_service = new AB_StaffService();
        $staff_service->loadBy( array(
            'staff_id'   => $staff_id,
            'service_id' => $service_id
        ) );

        // Check for errors.
        if ( ! $service_id ) {
            $response['errors']['service_required'] = true;
        }
        if ( empty ( $customers ) ) {
            $response['errors']['customers_required'] = true;
        }
        if ( !$this->dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id ) ) {
            $response['errors']['date_interval_not_available'] = true;
        }
        $number_of_persons = 0;
        foreach ( $customers as $customer ) {
            $number_of_persons += $customer['number_of_persons'];
        }
        if ( $number_of_persons > $staff_service->get( 'capacity' ) ) {
            $response['errors']['overflow_capacity'] = __( 'The number of customers should be not more than ', 'bookly' ) . $staff_service->get( 'capacity' );
        }
        if ( ! $this->getParameter( 'start_date' ) ) {
            $response['errors']['time_interval'] = __( 'Start time must not be empty', 'bookly' );
        } elseif ( ! $this->getParameter( 'end_date' ) ) {
            $response['errors']['time_interval'] = __( 'End time must not be empty', 'bookly' );
        } elseif ( $start_date == $end_date ) {
            $response['errors']['time_interval'] = __( 'End time must not be equal to start time', 'bookly' );
        }

        // If no errors then try to save the appointment.
        if ( !isset ( $response[ 'errors' ] ) ) {
            $appointment = new AB_Appointment();
            if ( $appointment_id ) {
                // Edit.
                $appointment->load( $appointment_id );
            }
            $appointment->set( 'start_date', $start_date );
            $appointment->set( 'end_date',   $end_date );
            $appointment->set( 'staff_id',   $staff_id );
            $appointment->set( 'service_id', $service_id );

            if ( $appointment->save() !== false ) {
                // Save customers.
                $appointment->setCustomers( $customers );

                // Google Calendar.
                $appointment->handleGoogleCalendar();

                $startDate = new DateTime( $appointment->get( 'start_date' ) );
                $endDate   = new DateTime( $appointment->get( 'end_date' ) );
                $desc      = array();
                if ( $staff_service->get( 'capacity' ) == 1 ) {
                    $customer_appointments = $appointment->getCustomerAppointments();
                    if ( !empty ( $customer_appointments ) ) {
                        $ca = $customer_appointments[ 0 ]->customer;
                        foreach ( array( 'name', 'phone', 'email' ) as $data_entry ) {
                            $entry_value = $ca->get( $data_entry );
                            if ( $entry_value ) {
                                $desc[] = '<div class="fc-employee">' . esc_html( $entry_value ) . '</div>';
                            }
                        }

                        foreach ( $customer_appointments[0]->getCustomFields() as $custom_field ) {
                            $desc[] = '<div class="fc-notes">' . wp_strip_all_tags( $custom_field['label'] ) . ': ' . esc_html( $custom_field['value'] ) . '</div>';
                        }
                    }
                } else {
                    $signed_up = 0;
                    foreach ( $appointment->getCustomerAppointments() as $ca ) {
                        $signed_up += $ca->get( 'number_of_persons' );
                    }

                    $desc[] = '<div class="fc-notes">' . __( 'Signed up', 'bookly' ) . ' ' . $signed_up . '</div>';
                    $desc[] = '<div class="fc-notes">' . __( 'Capacity', 'bookly' ) . ' ' . $staff_service->get( 'capacity' ) . '</div>';
                }

                $service   = new AB_Service();
                $service->load( $service_id );

                $response['success'] = true;
                $response['data']   = array(
                    'id'      => (int)$appointment->get( 'id' ),
                    'start'   => $startDate->format( 'Y-m-d H:i:s' ),
                    'end'     => $endDate->format( 'Y-m-d H:i:s' ),
                    'desc'    => implode( '', $desc ),
                    'title'   => $service->get( 'title' ) ? $service->get( 'title' ) : __( 'Untitled', 'bookly' ),
                    'color'   => $service->get( 'color' ),
                    'staffId' => $appointment->get( 'staff_id' ),
                );
            } else {
                $response[ 'errors' ] = array( 'db' => __( 'Could not save appointment in database.', 'bookly' ) );
            }
        }

        wp_send_json( $response );
    }

    public function executeCheckAppointmentDateSelection()
    {
        $start_date     = $this->getParameter( 'start_date' );
        $end_date       = $this->getParameter( 'end_date' );
        $staff_id       = $this->getParameter( 'staff_id' );
        $service_id     = $this->getParameter( 'service_id' );
        $appointment_id = $this->getParameter( 'appointment_id' );
        $timestamp_diff = strtotime( $end_date ) - strtotime( $start_date );

        $result = array(
            'date_interval_not_available' => false,
            'date_interval_warning' => false,
        );

        if ( !$this->dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id ) ) {
            $result['date_interval_not_available'] = true;
        }

        if ( $service_id ) {
            $service = new AB_Service();
            $service->load( $service_id );

            $duration = $service->get( 'duration' );

            // Service duration interval is not equal to.
            $result['date_interval_warning'] = ( $timestamp_diff != $duration );
        }

        wp_send_json( $result );
    }

    public function executeDeleteAppointment()
    {
        $appointment = new AB_Appointment();
        $appointment->load( $this->getParameter( 'appointment_id' ) );
        $appointment->delete();
        exit;
    }

    /**
     * @param $start_date
     * @param $end_date
     * @param $staff_id
     * @param $appointment_id
     * @return bool
     */
    private function dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id )
    {
        return AB_Appointment::query()
            ->whereNot( 'id', $appointment_id )
            ->where( 'staff_id', $staff_id )
            ->whereRaw(
                '(start_date > %s AND start_date < %s OR (end_date > %s AND end_date < %s) OR (start_date < %s AND end_date > %s) OR (start_date = %s OR end_date = %s) )',
                array( $start_date, $end_date, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date )
            )
            ->count() == 0;
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
