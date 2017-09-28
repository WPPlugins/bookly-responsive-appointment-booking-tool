<?php
namespace BooklyLite\Backend\Modules\Calendar;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Calendar
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-calendar';

    protected function getPermissions()
    {
        return array( '_this' => 'user' );
    }

    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'module'   => array( 'css/fullcalendar.min.css', ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array( 'bootstrap/js/bootstrap.min.js' => array( 'jquery' ), ),
            'module'   => array(
                'js/fullcalendar.min.js'   => array( 'bookly-moment.min.js' ),
                'js/fc-multistaff-view.js' => array( 'bookly-fullcalendar.min.js' ),
                'js/calendar.js'           => array( 'bookly-fc-multistaff-view.js' ),
            ),
        ) );

        $slot_length_minutes = get_option( 'bookly_gen_time_slot_length', '15' );
        $slot = new \DateInterval( 'PT' . $slot_length_minutes . 'M' );

        $staff_members = Lib\Utils\Common::isCurrentUserAdmin()
            ? Lib\Entities\Staff::query()->sortBy( 'position' )->find()
            : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->find();

        wp_localize_script( 'bookly-calendar.js', 'BooklyL10n', array(
            'slotDuration'    => $slot->format( '%H:%I:%S' ),
            'calendar'        => array(
                'shortMonths' => array_values( $wp_locale->month_abbrev ),
                'longMonths'  => array_values( $wp_locale->month ),
                'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
                'longDays'    => array_values( $wp_locale->weekday ),
            ),
            'dpDateFormat'    => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_JQUERY_DATEPICKER ),
            'mjsDateFormat'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'mjsTimeFormat'   => Lib\Utils\DateTime::convertFormat( 'time', Lib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'today'           => __( 'Today', 'bookly' ),
            'week'            => __( 'Week',  'bookly' ),
            'day'             => __( 'Day',   'bookly' ),
            'month'           => __( 'Month', 'bookly' ),
            'allDay'          => __( 'All Day', 'bookly' ),
            'delete'          => __( 'Delete',  'bookly' ),
            'noStaffSelected' => __( 'No staff selected', 'bookly' ),
            'are_you_sure'    => __( 'Are you sure?',     'bookly' ),
            'startOfWeek'     => (int) get_option( 'start_of_week' ),
            'recurring_appointments_enabled' => (int) Lib\Config::isRecurringAppointmentsEnabled(),
        ) );

        $this->render( 'calendar', compact( 'staff_members' ) );
    }

    /**
     * Get data for FullCalendar.
     *
     * return string json
     */
    public function executeGetStaffAppointments()
    {
        $result        = array();
        $staff_members = array();
        $one_day       = new \DateInterval( 'P1D' );
        $start_date    = new \DateTime( $this->getParameter( 'start' ) );
        $end_date      = new \DateTime( $this->getParameter( 'end' ) );
        // FullCalendar sends end date as 1 day further.
        $end_date->sub( $one_day );

        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $staff_members = Lib\Entities\Staff::query()
                ->where( 'id', 1 )
                ->find();
        } else {
            $staff_members[] = Lib\Entities\Staff::query()
                ->where( 'wp_user_id', get_current_user_id() )
                ->findOne();
            $staff_ids = array( 1 );
        }
        // Load special days.
        $special_days = array();
        foreach ( apply_filters( 'bookly_special_days_get_data_for_available_time', array(), $staff_ids, $start_date, $end_date ) as $day ) {
            $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
        }

        foreach ( $staff_members as $staff ) {
            /** @var Lib\Entities\Staff $staff */
            $result = array_merge( $result, $this->_getAppointmentsForFC( $staff->get( 'id' ), $start_date, $end_date ) );

            // Schedule.
            $items = $staff->getScheduleItems();
            $day   = clone $start_date;
            // Find previous day end time.
            $last_end = clone $day;
            $last_end->sub( $one_day );
            $w = (int) $day->format( 'w' );
            $end_time = $items[ $w > 0 ? $w : 7 ]->get( 'end_time' );
            if ( $end_time !== null ) {
                $end_time = explode( ':', $end_time );
                $last_end->setTime( $end_time[0], $end_time[1] );
            } else {
                $last_end->setTime( 24, 0 );
            }
            // Do the loop.
            while ( $day <= $end_date ) {
                $start = $last_end->format( 'Y-m-d H:i:s' );
                // Check if $day is Special Day for current staff.
                if ( isset( $special_days[ $staff->get( 'id' ) ][ $day->format( 'Y-m-d' ) ] ) ) {
                    $sp_days = $special_days[ $staff->get( 'id' ) ][ $day->format( 'Y-m-d' ) ];
                    $sp_day  = array_shift( $sp_days );
                    $end = $sp_day['date'] . ' ' . $sp_day['start_time'];
                    if ( $start < $end ) {
                        $result[] = array(
                            'start'     => $start,
                            'end'       => $end,
                            'rendering' => 'background',
                            'staffId'   => $staff->get( 'id' ),
                        );
                        // Check if the first break exists.
                        if ( isset( $sp_day['break_start'] ) ) {
                            $result[] = array(
                                'start'     => $sp_day['date'] . ' ' . $sp_day['break_start'],
                                'end'       => $sp_day['date'] . ' ' . $sp_day['break_end'],
                                'rendering' => 'background',
                                'staffId'   => $staff->get( 'id' ),
                            );
                        }
                    }
                    // Breaks.
                    foreach ( $sp_days as $sp_day ) {
                        $result[] = array(
                            'start'     => $sp_day['date'] . ' ' . $sp_day['break_start'],
                            'end'       => $sp_day['date'] . ' ' . $sp_day['break_end'],
                            'rendering' => 'background',
                            'staffId'   => $staff->get( 'id' ),
                        );
                    }
                    $end_time = explode( ':', $sp_day['end_time'] );
                    $last_end = clone $day;
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    /** @var Lib\Entities\StaffScheduleItem $item */
                    $item = $items[ (int) $day->format( 'w' ) + 1 ];
                    if ( $item->get( 'start_time' ) && ! $staff->isOnHoliday( $day ) ) {
                        $end = $day->format( 'Y-m-d ' . $item->get( 'start_time' ) );
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
                                'start'     => $day->format( 'Y-m-d ' . $break['start_time'] ),
                                'end'       => $day->format( 'Y-m-d ' . $break['end_time'] ),
                                'rendering' => 'background',
                                'staffId'   => $staff->get( 'id' ),
                            );
                        }
                    } else {
                        $result[] = array(
                            'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                            'end'       => $day->format( 'Y-m-d 24:00:00' ),
                            'rendering' => 'background',
                            'staffId'   => $staff->get( 'id' ),
                        );
                        $last_end = clone $day;
                        $last_end->setTime( 24, 0 );
                    }
                }

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
            'time_interval' => Lib\Config::getTimeSlotLength(),
            'status'        => array(
                'items' => array(
                    'pending'   => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_PENDING ),
                    'approved'  => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_APPROVED ),
                    'cancelled' => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_CANCELLED ),
                    'rejected'  => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_REJECTED ),//@todo I didn't find the place it uses
                ),
                'default' => get_option( 'bookly_gen_default_appointment_status' ),
            ),
        );

        // Staff list.
        $staff_members = Lib\Utils\Common::isCurrentUserAdmin()
            ? Lib\Entities\Staff::query()->sortBy( 'position' )->find()
            : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->find();

        /** @var Lib\Entities\Staff $staff_member */
        foreach ( $staff_members as $staff_member ) {
            $services = array();
            foreach ( $staff_member->getStaffServices() as $staff_service ) {
                $services[] = array(
                    'id'       => $staff_service->service->get( 'id' ),
                    'title'    => sprintf(
                        '%s (%s)',
                        $staff_service->service->get( 'title' ),
                        Lib\Utils\DateTime::secondsToInterval( $staff_service->service->get( 'duration' ) )
                    ),
                    'duration' => $staff_service->service->get( 'duration' ),
                    'capacity' => $staff_service->get( 'capacity' )
                );
            }
            $result['staff'][] = array(
                'id'        => $staff_member->get( 'id' ),
                'full_name' => $staff_member->get( 'full_name' ),
                'services'  => $services
            );
        }

        // Customers list.
        foreach ( Lib\Entities\Customer::query()->sortBy( 'name' )->find() as $customer ) {
            $name = $customer->get( 'name' );
            if ( $customer->get( 'email' ) != '' || $customer->get( 'phone' ) != '' ) {
                $name .= ' (' . trim( $customer->get( 'email' ) . ', ' . $customer->get( 'phone' ) , ', ' ) . ')';
            }

            $result['customers'][] = array(
                'id'                => $customer->get( 'id' ),
                'name'              => $name,
                'custom_fields'     => array(),
                'number_of_persons' => 1,
            );
        }

        // Time list.
        $ts_length  = Lib\Config::getTimeSlotLength();
        $time_start = 0;
        $time_end   = DAY_IN_SECONDS * 2;

        // Run the loop.
        while ( $time_start <= $time_end ) {
            $slot = array(
                'value' => Lib\Utils\DateTime::buildTimeString( $time_start, false ),
                'title' => Lib\Utils\DateTime::formatTime( $time_start )
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

        $appointment = new Lib\Entities\Appointment();
        if ( $appointment->load( $this->getParameter( 'id' ) ) ) {
            $response['success'] = true;

            $info = Lib\Entities\Appointment::query( 'a' )
                ->select( 'ss.capacity AS max_capacity, SUM( ca.number_of_persons ) AS total_number_of_persons, a.staff_id, a.service_id, a.start_date, a.end_date, a.internal_note' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                ->where( 'a.id', $appointment->get( 'id' ) )
                ->fetchRow();

            $response['data']['total_number_of_persons'] = $info['total_number_of_persons'];
            $response['data']['max_capacity']  = $info['max_capacity'];
            $response['data']['start_date']    = $info['start_date'];
            $response['data']['end_date']      = $info['end_date'];
            $response['data']['staff_id']      = $info['staff_id'];
            $response['data']['service_id']    = $info['service_id'];
            $response['data']['internal_note'] = $info['internal_note'];
            $customers = Lib\Entities\CustomerAppointment::query( 'ca' )
                ->select( 'ca.id,
                    ca.customer_id,
                    ca.custom_fields,
                    ca.extras,
                    ca.number_of_persons,
                    ca.status,
                    ca.payment_id,
                    ca.compound_service_id,
                    ca.compound_token,
                    ca.location_id,
                    p.paid    AS payment,
                    p.total   AS payment_total,
                    p.type    AS payment_type,
                    p.details AS payment_details,
                    p.status  AS payment_status' )
                ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
                ->where( 'ca.appointment_id', $appointment->get( 'id' ) )
                ->fetchArray();
            foreach ( $customers as $customer ) {
                $payment_title = '';
                if ( $customer['payment'] !== null ) {
                    $payment_title = Lib\Utils\Common::formatPrice( $customer['payment'] );
                    if ( $customer['payment'] != $customer['payment_total'] ) {
                        $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, Lib\Utils\Common::formatPrice( $customer['payment_total'] ) );
                    }
                    $payment_title .= sprintf(
                        ' %s <span%s>%s</span>',
                        Lib\Entities\Payment::typeToString( $customer['payment_type'] ),
                        $customer['payment_status'] == Lib\Entities\Payment::STATUS_PENDING ? ' class="text-danger"' : '',
                        Lib\Entities\Payment::statusToString( $customer['payment_status'] )
                    );
                }
                $compound_service = '';
                if ( $customer['compound_service_id'] !== null ) {
                    $service = new Lib\Entities\Service();
                    if ( $service->load( $customer['compound_service_id'] ) ) {
                        $compound_service = $service->getTitle();
                    }
                }
                $response['data']['customers'][] = array(
                    'id'                => $customer['customer_id'],
                    'ca_id'             => $customer['id'],
                    'compound_service'  => $compound_service,
                    'compound_token'    => $customer['compound_token'],
                    'custom_fields'     => (array) json_decode( $customer['custom_fields'], true ),
                    'extras'            => (array) json_decode( $customer['extras'], true ),
                    'location_id'       => $customer['location_id'],
                    'number_of_persons' => $customer['number_of_persons'],
                    'payment_id'        => $customer['payment_id'],
                    'payment_type'      => $customer['payment'] != $customer['payment_total'] ? 'partial' : 'full',
                    'payment_title'     => $payment_title,
                    'status'            => $customer['status'],
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

        $internal_note  = $this->getParameter( 'internal_note' );
        $start_date     = $this->getParameter( 'start_date' );
        $end_date       = $this->getParameter( 'end_date' );
        $service_id     = $this->getParameter( 'service_id' );
        $appointment_id = $this->getParameter( 'id', 0 );
        $repeat         = json_decode( $this->getParameter( 'repeat', '[]' ), true );
        $schedule       = $this->getParameter( 'schedule', array() );
        $customers      = json_decode( $this->getParameter( 'customers', '[]' ), true );

        $staff_service  = new Lib\Entities\StaffService();
        $staff_service->loadBy( array(
            'staff_id'   => 1,
            'service_id' => $service_id
        ) );

        // Check for errors.
        if ( ! $start_date ) {
            $response['errors']['time_interval'] = __( 'Start time must not be empty', 'bookly' );
        } elseif ( ! $end_date ) {
            $response['errors']['time_interval'] = __( 'End time must not be empty', 'bookly' );
        } elseif ( $start_date == $end_date ) {
            $response['errors']['time_interval'] = __( 'End time must not be equal to start time', 'bookly' );
        }
        if ( ! $service_id ) {
            $response['errors']['service_required'] = true;
        }
        if ( empty ( $customers ) ) {
            $response['errors']['customers_required'] = true;
        }
        $total_number_of_persons = 0;
        $max_extras_duration = 0;
        foreach ( $customers as $customer ) {
            if (
                ( $customer['status'] != Lib\Entities\CustomerAppointment::STATUS_CANCELLED )
                && ( $customer['status'] != Lib\Entities\CustomerAppointment::STATUS_REJECTED )
            ) {
                $total_number_of_persons += $customer['number_of_persons'];
                $extras_duration = apply_filters( 'bookly_service_extras_get_total_duration', 0, $customer['extras'] );
                if ( $extras_duration > $max_extras_duration ) {
                    $max_extras_duration = $extras_duration;
                }
            }
        }
        if ( $total_number_of_persons > $staff_service->get( 'capacity' ) ) {
            $response['errors']['overflow_capacity'] = sprintf(
                __( 'The number of customers should not be more than %d', 'bookly' ),
                $staff_service->get( 'capacity' )
            );
        }
        $total_end_date = $end_date;
        if ( $max_extras_duration > 0 ) {
            $total_end_date = date_create( $end_date )->modify( '+' . $max_extras_duration . ' sec' )->format( 'Y-m-d H:i:s' );
        }
        if ( ! $this->dateIntervalIsAvailableForAppointment( $start_date, $total_end_date, 1, $appointment_id ) ) {
            $response['errors']['date_interval_not_available'] = true;
        }
        $notification   = $this->getParameter( 'notification' );

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            if ( $repeat['enabled'] ) {
                if ( ! empty ( $schedule ) ) {
                    $recurring_list = array( 'customers' => $customers, 'appointments' => array() );
                    // Create new series.
                    $series = new Lib\Entities\Series();
                    $series
                        ->set( 'repeat', $this->getParameter( 'repeat' ) )
                        ->set( 'token', Lib\Utils\Common::generateToken( get_class( $series ), 'token' ) )
                        ->save();

                    $service = new Lib\Entities\Service();
                    $service->load( $service_id );

                    foreach ( $schedule as $slot ) {
                        $slot = json_decode( $slot );
                        $appointment = new Lib\Entities\Appointment();
                        $appointment
                            ->set( 'series_id',       $series->get( 'id' ) )
                            ->set( 'staff_id',        1 )
                            ->set( 'service_id',      $service_id )
                            ->set( 'start_date',      date( 'Y-m-d H:i:s', $slot[0][2] ) )
                            ->set( 'end_date',        date( 'Y-m-d H:i:s', $slot[0][2] + $service->get( 'duration' ) ) )
                            ->set( 'internal_note',   $internal_note )
                            ->set( 'extras_duration', $max_extras_duration )
                        ;

                        if ( $appointment->save() !== false ) {
                            // Google Calendar.
                            $appointment->handleGoogleCalendar();
                            $appointment->saveCustomerAppointments( $customers );

                            if ( $notification != 'no' ) {
                                // Collect all appointments for sending recurring notification
                                $recurring_list['appointments'][] = $appointment ;
                            }
                        }
                    }
                    Lib\NotificationSender::sendRecurring( $recurring_list );
                }
                $response['success'] = true;
                $response['data']    = array( 'staffId' => 1 );  // make FullCalendar refetch events
            } else {
                $appointment = new Lib\Entities\Appointment();
                if ( $appointment_id ) {
                    // Edit.
                    $appointment->load( $appointment_id );
                }
                $appointment->set( 'start_date', $start_date );
                $appointment->set( 'end_date',   $end_date );
                $appointment->set( 'staff_id',   1 );
                $appointment->set( 'service_id', $service_id );
                $appointment->set( 'internal_note', $internal_note );
                $appointment->set( 'extras_duration', $max_extras_duration );

                if ( $appointment->save() !== false ) {
                    // Save customer appointments.
                    $ca_status_changed = $appointment->saveCustomerAppointments( $customers );
                    $customer_appointments = $appointment->getCustomerAppointments( true );

                    // Google Calendar.
                    $appointment->handleGoogleCalendar();

                    // Send notifications.
                    if ( $notification != 'no' ) {
                        foreach ( $customer_appointments as $ca ) {
                            // Send notification.
                            if ( $notification == 'all' || in_array( $ca->get( 'id' ), $ca_status_changed ) ) {
                                Lib\NotificationSender::send( $ca );
                            }
                        }
                    }

                    $response['success'] = true;
                    $response['data']    = $this->_getAppointmentForFC( 1, $appointment->get( 'id' ) );
                } else {
                    $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'bookly' ) );
                }
            }
        }

        wp_send_json( $response );
    }

    public function executeCheckAppointmentDateSelection()
    {
        $start_date     = $this->getParameter( 'start_date' );
        $end_date       = $this->getParameter( 'end_date' );
        $service_id     = $this->getParameter( 'service_id' );
        $appointment_id = $this->getParameter( 'appointment_id' );
        $timestamp_diff = strtotime( $end_date ) - strtotime( $start_date );

        $result = array(
            'date_interval_not_available' => false,
            'date_interval_warning' => false,
        );

        if ( ! $this->dateIntervalIsAvailableForAppointment( $start_date, $end_date, 1, $appointment_id ) ) {
            $result['date_interval_not_available'] = true;
        }

        if ( $service_id ) {
            $service = new Lib\Entities\Service();
            $service->load( $service_id );

            $duration = $service->get( 'duration' );

            // Service duration interval is not equal to.
            $result['date_interval_warning'] = ( $timestamp_diff != $duration );
        }

        wp_send_json( $result );
    }

    /**
     * Delete single appointment.
     */
    public function executeDeleteAppointment()
    {
        $appointment_id = $this->getParameter( 'appointment_id' );
        $reason         = $this->getParameter( 'reason' );

        if ( $this->getParameter( 'notify' ) ) {
            $ca_list = Lib\Entities\CustomerAppointment::query()
                ->where( 'appointment_id', $appointment_id )
                ->find();
            /** @var Lib\Entities\CustomerAppointment $ca */
            foreach ( $ca_list as $ca ) {
                switch ( $ca->get('status') ) {
                    case Lib\Entities\CustomerAppointment::STATUS_PENDING:
                        $ca->set( 'status', Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                        break;
                    case Lib\Entities\CustomerAppointment::STATUS_APPROVED:
                        $ca->set( 'status', Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                        break;
                }
                Lib\NotificationSender::send( $ca, array( 'cancellation_reason' => $reason ) );
            }
        }

        $appointment = new Lib\Entities\Appointment();
        $appointment->load( $appointment_id );
        $appointment->delete();

        wp_send_json_success();
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
        return Lib\Entities\Appointment::query( 'a' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->whereNot( 'a.id', $appointment_id )
            ->where( 'a.staff_id', $staff_id )
            ->whereNot( 'ca.status', Lib\Entities\CustomerAppointment::STATUS_CANCELLED )
            ->whereNot( 'ca.status', Lib\Entities\CustomerAppointment::STATUS_REJECTED )
            ->whereLt( 'start_date', $end_date )
            ->whereGt( 'end_date', $start_date )
            ->count() == 0;
    }

    /**
     * Get appointments for FullCalendar.
     *
     * @param integer $staff_id
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     * @return array
     */
    private function _getAppointmentsForFC( $staff_id, \DateTime $start_date, \DateTime $end_date )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->where( 'st.id', $staff_id )
            ->whereBetween( 'DATE(a.start_date)', $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );

        return $this->_buildAppointmentsForFC( $staff_id, $query );
    }

    /**
     * Get appointment for FullCalendar.
     *
     * @param integer $staff_id
     * @param int $appointment_id
     * @return array
     */
    private function _getAppointmentForFC( $staff_id, $appointment_id )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->where( 'a.id', $appointment_id );

        $appointments = $this->_buildAppointmentsForFC( $staff_id, $query );

        return $appointments[0];
    }

    /**
     * Build appointments for FullCalendar.
     *
     * @param integer $staff_id
     * @param Lib\Query $query
     * @return mixed
     */
    private function _buildAppointmentsForFC( $staff_id, Lib\Query $query )
    {
        $appointments = $query
            ->select( 'a.id, a.series_id, a.start_date, DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) AS end_date,
                s.title AS service_title, s.color AS service_color,
                ss.capacity AS max_capacity,
                (SELECT SUM(ca.number_of_persons) FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id) AS total_number_of_persons,
                ca.number_of_persons,
                ca.custom_fields,
                ca.status AS appointment_status,
                ca.extras,
                ca.location_id,
                c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email, c.id AS customer_id,
                p.total,
                p.type   AS payment_gateway,
                p.status AS payment_status' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->where( 'st.id', 1 )
            ->groupBy( 'a.id' )
            ->fetchArray();

        foreach ( $appointments as $key => $appointment ) {
            $desc = '';
            // Display customer information only if there is 1 customer. Don't confuse with number_of_persons.
            if ( $appointment['number_of_persons'] == $appointment['total_number_of_persons'] ) {
                // Customer information.
                foreach ( array( 'customer_name', 'customer_phone', 'customer_email' ) as $data_entry ) {
                    if ( $appointment[ $data_entry ] ) {
                        $desc .= '<div>' . esc_html( $appointment[ $data_entry ] ) . '</div>';
                    }
                }
                $desc = apply_filters( 'bookly_prepare_calendar_appointment_description', $desc, $appointment );
                // Custom fields.
                if ( $appointment['custom_fields'] != '[]' ) {
                    $desc .= '<br/>';
                    $ca = new Lib\Entities\CustomerAppointment();
                    $ca->set( 'custom_fields',  $appointment['custom_fields'] );
                    $ca->set( 'appointment_id', $appointment['id'] );
                    foreach ( $ca->getCustomFields() as $custom_field ) {
                        $desc .= sprintf( '<div>%s: %s</div>', wp_strip_all_tags( $custom_field['label'] ), nl2br( esc_html( $custom_field['value'] ) ) );
                    }
                }
                // Payment.
                if ( $appointment['total'] ) {
                    $desc .= sprintf(
                        '<br/><div>%s: %s %s %s</div>',
                        __( 'Payment', 'bookly' ),
                        Lib\Utils\Common::formatPrice( $appointment['total'] ),
                        Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] ),
                        Lib\Entities\Payment::statusToString( $appointment['payment_status'] )
                    );
                }
                // Status.
                $desc .= sprintf( '<br/><div>%s: %s</div>', __( 'Status', 'bookly' ), Lib\Entities\CustomerAppointment::statusToString( $appointment['appointment_status'] ) );
                // Signed up & Capacity.
                if ( $appointment['max_capacity'] > 1 ) {
                    $desc .= sprintf( '<br/><div>%s: %d</div>', __( 'Signed up', 'bookly' ), $appointment['total_number_of_persons'] );
                    $desc .= sprintf( '<div>%s: %d</div>', __( 'Capacity',  'bookly' ),  $appointment['max_capacity'] );
                }
            } else {
                $desc .= sprintf( '<div>%s: %d</div>', __( 'Signed up', 'bookly' ), $appointment['total_number_of_persons'] );
                $desc .= sprintf( '<div>%s: %d</div>', __( 'Capacity',  'bookly' ), $appointment['max_capacity'] );
            }
            $appointments[ $key ] = array(
                'id'        => $appointment['id'],
                'series_id' => (int) $appointment['series_id'],
                'start'     => $appointment['start_date'],
                'end'       => $appointment['end_date'],
                'title'     => $appointment['service_title'] ? esc_html( $appointment['service_title'] ) : __( 'Untitled', 'bookly' ),
                'desc'      => $desc,
                'color'     => $appointment['service_color'],
                'staffId'   => 1,
            );
        }

        return $appointments;
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