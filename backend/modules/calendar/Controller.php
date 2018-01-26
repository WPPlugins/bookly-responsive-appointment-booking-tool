<?php
namespace BooklyLite\Backend\Modules\Calendar;

use BooklyLite\Lib;
use BooklyLite\Lib\DataHolders\Booking as DataHolders;

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
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css' ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array( 'bootstrap/js/bootstrap.min.js' => array( 'jquery' ), ),
            'module'   => array(
                'js/fullcalendar.min.js'   => array( 'bookly-moment.min.js' ),
                'js/fc-multistaff-view.js' => array( 'bookly-fullcalendar.min.js' ),
                'js/calendar-common.js'    => array( 'bookly-fc-multistaff-view.js' ),
                'js/calendar.js'           => array( 'bookly-calendar-common.js' ),
            ),
        ) );

        $slot_length_minutes = get_option( 'bookly_gen_time_slot_length', '15' );
        $slot = new \DateInterval( 'PT' . $slot_length_minutes . 'M' );

        $staff_members = Lib\Utils\Common::isCurrentUserAdmin()
            ? Lib\Entities\Staff::query()->where( 'id', 1 )->find()
            : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->find();

        wp_localize_script( 'bookly-calendar.js', 'BooklyL10n', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
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
            'recurring_appointments' => array(
                'active' => (int) Lib\Config::recurringAppointmentsActive(),
                'title'  => __( 'Recurring appointments', 'bookly' ),
            ),
            'waiting_list'    => array(
                'active' => (int) Lib\Config::waitingListActive(),
                'title'  => __( 'On waiting list', 'bookly' ),
            ),
            'packages'    => array(
                'active' => (int) Lib\Config::packagesActive(),
                'title'  => __( 'Package', 'bookly' ),
            ),
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

        $staff_ids = array( 1 );
        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $staff_members = Lib\Entities\Staff::query()
                ->where( 'id', 1 )
                ->find();
        } else {
            $staff_member = Lib\Entities\Staff::query()
                ->where( 'wp_user_id', get_current_user_id() )
                ->findOne();
            $staff_members[] = $staff_member;
        }
        // Load special days.
        $special_days = array();
        foreach ( (array) Lib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) as $day ) {
            $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
        }

        foreach ( $staff_members as $staff ) {
            /** @var Lib\Entities\Staff $staff */
            $result = array_merge( $result, $this->_getAppointmentsForFC( $staff->getId(), $start_date, $end_date ) );

            // Schedule.
            $items = $staff->getScheduleItems();
            $day   = clone $start_date;
            // Find previous day end time.
            $last_end = clone $day;
            $last_end->sub( $one_day );
            $w = (int) $day->format( 'w' );
            $end_time = $items[ $w > 0 ? $w : 7 ]->getEndTime();
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
                if ( isset( $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ] ) ) {
                    $sp_days = $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ];
                    $end = $sp_days[0]['date'] . ' ' . $sp_days[0]['start_time'];
                    if ( $start < $end ) {
                        $result[] = array(
                            'start'     => $start,
                            'end'       => $end,
                            'rendering' => 'background',
                            'staffId'   => $staff->getId(),
                        );
                    }
                    // Breaks.
                    foreach ( $sp_days as $sp_day ) {
                        $break_start = date(
                            'Y-m-d H:i:s',
                            strtotime( $sp_day['date'] ) + Lib\Utils\DateTime::timeToSeconds( $sp_day['break_start'] )
                        );
                        $break_end = date(
                            'Y-m-d H:i:s',
                            strtotime( $sp_day['date'] ) + Lib\Utils\DateTime::timeToSeconds( $sp_day['break_end'] )
                        );
                        $result[] = array(
                            'start'     => $break_start,
                            'end'       => $break_end,
                            'rendering' => 'background',
                            'staffId'   => $staff->getId(),
                        );
                    }
                    $end_time = explode( ':', $sp_days[0]['end_time'] );
                    $last_end = clone $day;
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    /** @var Lib\Entities\StaffScheduleItem $item */
                    $item = $items[ (int) $day->format( 'w' ) + 1 ];
                    if ( $item->getStartTime() && ! $staff->isOnHoliday( $day ) ) {
                        $end = $day->format( 'Y-m-d ' . $item->getStartTime() );
                        if ( $start < $end ) {
                            $result[] = array(
                                'start'     => $start,
                                'end'       => $end,
                                'rendering' => 'background',
                                'staffId'   => $staff->getId(),
                            );
                        }
                        $last_end = clone $day;
                        $end_time = explode( ':', $item->getEndTime() );
                        $last_end->setTime( $end_time[0], $end_time[1] );

                        // Breaks.
                        foreach ( $item->getBreaksList() as $break ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + Lib\Utils\DateTime::timeToSeconds( $break['start_time'] )
                            );
                            $break_end = date(
                                'Y-m-d H:i:s',
                                $day->getTimestamp() + Lib\Utils\DateTime::timeToSeconds( $break['end_time'] )
                            );
                            $result[] = array(
                                'start'     => $break_start,
                                'end'       => $break_end,
                                'rendering' => 'background',
                                'staffId'   => $staff->getId(),
                            );
                        }
                    } else {
                        $result[] = array(
                            'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                            'end'       => $day->format( 'Y-m-d 24:00:00' ),
                            'rendering' => 'background',
                            'staffId'   => $staff->getId(),
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
                    'staffId'   => $staff->getId(),
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
        $type = $this->getParameter( 'type', false ) == 'package' ? Lib\Entities\Service::TYPE_PACKAGE : Lib\Entities\Service::TYPE_SIMPLE;
        $result = array(
            'staff'         => array(),
            'customers'     => array(),
            'start_time'    => array(),
            'end_time'      => array(),
            'week_days'     => array(),
            'time_interval' => Lib\Config::getTimeSlotLength(),
            'status'        => array(
                'items' => array(
                    'pending'    => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_PENDING ),
                    'approved'   => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_APPROVED ),
                    'cancelled'  => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_CANCELLED ),
                    'rejected'   => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_REJECTED ),
                    'waitlisted' => Lib\Entities\CustomerAppointment::statusToString( Lib\Entities\CustomerAppointment::STATUS_WAITLISTED ),
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
            if ( $type == Lib\Entities\Service::TYPE_SIMPLE ) {
                $services[] = array(
                    'id'           => null,
                    'title'        => __( 'Custom', 'bookly' ),
                    'duration'     => Lib\Config::getTimeSlotLength(),
                    'capacity_min' => 1,
                    'capacity_max' => 1,
                );
            }
            foreach ( $staff_member->getStaffServices( $type ) as $staff_service ) {
                $sub_services = $staff_service->service->getSubServices();
                if ( $type == Lib\Entities\Service::TYPE_SIMPLE || ! empty( $sub_services ) ) {
                    $services[] = array(
                        'id'           => $staff_service->service->getId(),
                        'title'        => sprintf(
                            '%s (%s)',
                            $staff_service->service->getTitle(),
                            Lib\Utils\DateTime::secondsToInterval( $staff_service->service->getDuration() )
                        ),
                        'duration'     => $staff_service->service->getDuration(),
                        'capacity_min' => 1,
                        'capacity_max' => 1,
                    );
                }
            }
            $locations = array();
            foreach ( (array) Lib\Proxy\Locations::findByStaffId( $staff_member->getId() ) as $location ) {
                $locations[] = array(
                    'id'   => $location->getId(),
                    'name' => $location->getName(),
                );
            }
            $result['staff'][] = array(
                'id'        => $staff_member->getId(),
                'full_name' => $staff_member->getFullName(),
                'services'  => $services,
                'locations' => $locations,
            );
        }

        /** @var Lib\Entities\Customer $customer */
        // Customers list.
        foreach ( Lib\Entities\Customer::query()->sortBy( 'full_name' )->find() as $customer ) {
            $name = $customer->getFullName();
            if ( $customer->getEmail() != '' || $customer->getPhone() != '' ) {
                $name .= ' (' . trim( $customer->getEmail() . ', ' . $customer->getPhone(), ', ' ) . ')';
            }

            $result['customers'][] = array(
                'id'                => $customer->getId(),
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
                'title' => Lib\Utils\DateTime::formatTime( $time_start ),
            );
            if ( $time_start < DAY_IN_SECONDS ) {
                $result['start_time'][] = $slot;
            }
            $result['end_time'][] = $slot;
            $time_start += $ts_length;
        }

        $days_times = Lib\Config::getDaysAndTimes();
        $weekdays  = array( 1 => 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', );
        foreach ( $days_times['days'] as $index => $abbrev ) {
            $result['week_days'][] = $weekdays[ $index ];
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
                ->select( 'ss.capacity_min as min_capacity,
                    ss.capacity_max AS max_capacity,
                    SUM(ca.number_of_persons) AS total_number_of_persons,
                    a.staff_id,
                    a.staff_any,
                    a.service_id,
                    a.custom_service_name,
                    a.custom_service_price,
                    a.start_date,
                    a.end_date,
                    a.internal_note,
                    a.series_id,
                    a.location_id' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                ->where( 'a.id', $appointment->getId() )
                ->fetchRow();

            $response['data']['total_number_of_persons'] = $info['total_number_of_persons'];
            $response['data']['min_capacity']            = $info['min_capacity'];
            $response['data']['max_capacity']            = $info['max_capacity'];
            $response['data']['start_date']              = $info['start_date'];
            $response['data']['end_date']                = $info['end_date'];
            $response['data']['staff_id']                = $info['staff_id'];
            $response['data']['staff_any']               = (int) $info['staff_any'];
            $response['data']['service_id']              = $info['service_id'];
            $response['data']['custom_service_name']     = $info['custom_service_name'];
            $response['data']['custom_service_price']    = (float) $info['custom_service_price'];
            $response['data']['internal_note']           = $info['internal_note'];
            $response['data']['series_id']               = $info['series_id'];
            $response['data']['location_id']             = $info['location_id'];

            $customers = Lib\Entities\CustomerAppointment::query( 'ca' )
                ->select( 'ca.id,
                    ca.customer_id,
                    ca.package_id,
                    ca.custom_fields,
                    ca.extras,
                    ca.number_of_persons,
                    ca.notes,
                    ca.status,
                    ca.payment_id,
                    ca.compound_service_id,
                    ca.compound_token,
                    p.paid    AS payment,
                    p.total   AS payment_total,
                    p.type    AS payment_type,
                    p.details AS payment_details,
                    p.status  AS payment_status' )
                ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
                ->where( 'ca.appointment_id', $appointment->getId() )
                ->fetchArray();
            foreach ( $customers as $customer ) {
                $payment_title = '';
                if ( $customer['payment'] !== null ) {
                    $payment_title = Lib\Utils\Price::format( $customer['payment'] );
                    if ( $customer['payment'] != $customer['payment_total'] ) {
                        $payment_title = sprintf( __( '%s of %s', 'bookly' ), $payment_title, Lib\Utils\Price::format( $customer['payment_total'] ) );
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
                        $compound_service = $service->getTranslatedTitle();
                    }
                }
                $response['data']['customers'][] = array(
                    'id'                => $customer['customer_id'],
                    'ca_id'             => $customer['id'],
                    'package_id'        => $customer['package_id'],
                    'compound_service'  => $compound_service,
                    'compound_token'    => $customer['compound_token'],
                    'custom_fields'     => (array) json_decode( $customer['custom_fields'], true ),
                    'extras'            => (array) json_decode( $customer['extras'], true ),
                    'number_of_persons' => $customer['number_of_persons'],
                    'notes'             => $customer['notes'],
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

        $appointment_id       = (int) $this->getParameter( 'id', 0 );
        $staff_id             = (int) $this->getParameter( 'staff_id', 0 );
        $service_id           = (int) $this->getParameter( 'service_id', -1 );
        $custom_service_name  = trim( $this->getParameter( 'custom_service_name' ) );
        $custom_service_price = trim( $this->getParameter( 'custom_service_price' ) );
        $location_id          = (int) $this->getParameter( 'location_id', 0 );
        $start_date           = $this->getParameter( 'start_date' );
        $end_date             = $this->getParameter( 'end_date' );
        $repeat               = json_decode( $this->getParameter( 'repeat', '[]' ), true );
        $schedule             = $this->getParameter( 'schedule', array() );
        $customers            = json_decode( $this->getParameter( 'customers', '[]' ), true );
        $notification         = $this->getParameter( 'notification', 'no' );
        $internal_note        = $this->getParameter( 'internal_note' );
        $created_from         = $this->getParameter( 'created_from' );

        if ( ! $service_id ) {
            // Custom service.
            $service_id = null;
        }
        if ( $service_id || $custom_service_name == '' ) {
            $custom_service_name = null;
        }
        if ( $service_id || $custom_service_price == '' ) {
            $custom_service_price = null;
        }
        if ( ! $location_id ) {
            $location_id = null;
        }

        // Check for errors.
        if ( ! $start_date ) {
            $response['errors']['time_interval'] = __( 'Start time must not be empty', 'bookly' );
        } elseif ( ! $end_date ) {
            $response['errors']['time_interval'] = __( 'End time must not be empty', 'bookly' );
        } elseif ( $start_date == $end_date ) {
            $response['errors']['time_interval'] = __( 'End time must not be equal to start time', 'bookly' );
        }
        if ( $service_id == -1 ) {
            $response['errors']['service_required'] = true;
        } else if ( $service_id === null && $custom_service_name === null ) {
            $response['errors']['custom_service_name_required'] = true;
        }
        $total_number_of_persons = 0;
        $max_extras_duration = 0;
        foreach ( $customers as $i => $customer ) {
            if ( $customer['status'] == Lib\Entities\CustomerAppointment::STATUS_PENDING ||
                $customer['status'] == Lib\Entities\CustomerAppointment::STATUS_APPROVED
            ) {
                $total_number_of_persons += $customer['number_of_persons'];
                $extras_duration = Lib\Proxy\ServiceExtras::getTotalDuration( $customer['extras'] );
                if ( $extras_duration > $max_extras_duration ) {
                    $max_extras_duration = $extras_duration;
                }
            }
            $customers[ $i ]['created_from'] = ( $created_from == 'backend' ) ? 'backend' : 'frontend';
        }
        if ( $service_id ) {
            $staff_service  = new Lib\Entities\StaffService();
            $staff_service->loadBy( array(
                'staff_id'   => 1,
                'service_id' => $service_id
            ) );
            if ( $total_number_of_persons > $staff_service->getCapacityMax() ) {
                $response['errors']['overflow_capacity'] = sprintf(
                    __( 'The number of customers should not be more than %d', 'bookly' ),
                    $staff_service->getCapacityMax()
                );
            }
        }

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            if ( $repeat['enabled'] ) {
                // Series.
                if ( ! empty ( $schedule ) ) {
                    // Create new series.
                    $series = new Lib\Entities\Series();
                    $series
                        ->setRepeat( $this->getParameter( 'repeat' ) )
                        ->setToken( Lib\Utils\Common::generateToken( get_class( $series ), 'token' ) )
                        ->save();

                    if ( $notification != 'no' ) {
                        // Create order per each customer to send notifications.
                        /** @var DataHolders\Order[] $orders */
                        $orders = array();
                        foreach ( $customers as $customer ) {
                            $order = DataHolders\Order::create( Lib\Entities\Customer::find( $customer['id'] ) )
                                ->addItem( 0, DataHolders\Series::create( $series ) )
                            ;
                            $orders[ $customer['id'] ] = $order;
                        }
                    }

                    $service = Lib\Entities\Service::find( $service_id );

                    foreach ( $schedule as $slot ) {
                        $slot = json_decode( $slot );
                        $appointment = new Lib\Entities\Appointment();
                        $appointment
                            ->setSeries( $series )
                            ->setLocationId( $location_id )
                            ->setStaffId( $staff_id )
                            ->setServiceId( $service_id )
                            ->setStartDate( $slot[0][2] )
                            ->setEndDate( date( 'Y-m-d H:i:s', strtotime( $slot[0][2] ) + $service->getDuration() ) )
                            ->setInternalNote( $internal_note )
                            ->setExtrasDuration( $max_extras_duration )
                        ;

                        if ( $appointment->save() !== false ) {
                            // Save customer appointments.
                            $ca_list = $appointment->saveCustomerAppointments( $customers );
                            // Google Calendar.
                            $appointment->handleGoogleCalendar();
                            // Waiting list.
                            Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );

                            if ( $notification != 'no' ) {
                                foreach ( $ca_list as $ca ) {
                                    $item = DataHolders\Simple::create( $ca )
                                        ->setService( $service )
                                        ->setAppointment( $appointment )
                                    ;
                                    $orders[ $ca->getCustomerId() ]->getItem( 0 )->addItem( $item );
                                }
                            }
                        }
                    }
                    if ( $notification != 'no' ) {
                        foreach ( $orders as $order ) {
                            Lib\Proxy\RecurringAppointments::sendRecurring( $order->getItem( 0 ), $order );
                        }
                    }
                }
                $response['success'] = true;
                $response['data']    = array( 'staffId' => 1 );  // make FullCalendar refetch events
            } else {
                // Single appointment.
                $appointment = new Lib\Entities\Appointment();
                if ( $appointment_id ) {
                    // Edit.
                    $appointment->load( $appointment_id );
                    if ( $appointment->getStaffId() != $staff_id ) {
                        $appointment->setStaffAny( 0 );
                    }
                }
                $appointment
                    ->setLocationId( $location_id )
                    ->setStaffId( 1 )
                    ->setServiceId( $service_id )
                    ->setCustomServiceName( $custom_service_name )
                    ->setCustomServicePrice( $custom_service_price )
                    ->setStartDate( $start_date )
                    ->setEndDate( $end_date )
                    ->setInternalNote( $internal_note )
                    ->setExtrasDuration( $max_extras_duration )
                ;

                if ( $appointment->save() !== false ) {
                    // Save customer appointments.
                    $ca_status_changed = $appointment->saveCustomerAppointments( $customers );

                    // Google Calendar.
                    $appointment->handleGoogleCalendar();
                    // Waiting list.
                    Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );

                    // Send notifications.
                    if ( $notification == 'changed_status' ) {
                        foreach ( $ca_status_changed as $ca ) {
                            Lib\NotificationSender::sendSingle( DataHolders\Simple::create( $ca )->setAppointment( $appointment ) );
                        }
                    } else if ( $notification == 'all' ) {
                        $ca_list = $appointment->getCustomerAppointments( true );
                        foreach ( $ca_list as $ca ) {
                            Lib\NotificationSender::sendSingle( DataHolders\Simple::create( $ca )->setAppointment( $appointment ) );
                        }
                    }

                    $response['success'] = true;
                    $response['data']    = $this->_getAppointmentForFC( $staff_id, $appointment->getId() );
                } else {
                    $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'bookly' ) );
                }
            }
        }
        update_user_meta( get_current_user_id(), 'bookly_appointment_form_send_notifications', $notification );
        wp_send_json( $response );
    }

    public function executeCheckAppointmentErrors()
    {
        $start_date     = $this->getParameter( 'start_date' );
        $end_date       = $this->getParameter( 'end_date' );
        $staff_id       = (int) $this->getParameter( 'staff_id' );
        $service_id     = (int) $this->getParameter( 'service_id' );
        $appointment_id = (int) $this->getParameter( 'appointment_id' );
        $timestamp_diff = strtotime( $end_date ) - strtotime( $start_date );
        $customers      = json_decode( $this->getParameter( 'customers', '[]' ), true );

        $result = array(
            'date_interval_not_available'  => false,
            'date_interval_warning'        => false,
            'customers_appointments_limit' => array(),
        );

        $max_extras_duration = 0;
        foreach ( $customers as $customer ) {
            if ( $customer['status'] == Lib\Entities\CustomerAppointment::STATUS_PENDING ||
                $customer['status'] == Lib\Entities\CustomerAppointment::STATUS_APPROVED
            ) {
                $extras_duration = Lib\Proxy\ServiceExtras::getTotalDuration( $customer['extras'] );
                if ( $extras_duration > $max_extras_duration ) {
                    $max_extras_duration = $extras_duration;
                }
            }
        }

        $total_end_date = $end_date;
        if ( $max_extras_duration > 0 ) {
            $total_end_date = date_create( $end_date )->modify( '+' . $max_extras_duration . ' sec' )->format( 'Y-m-d H:i:s' );
        }
        if ( ! $this->dateIntervalIsAvailableForAppointment( $start_date, $total_end_date, 1, $appointment_id ) ) {
            $result['date_interval_not_available'] = true;
        }

        if ( $service_id ) {
            $service = new Lib\Entities\Service();
            $service->load( $service_id );

            $duration = $service->getDuration();

            // Service duration interval is not equal to.
            $result['date_interval_warning'] = ( $timestamp_diff != $duration );

            // Check customers for appointments limit
            if ( $start_date ) {
                foreach ( $customers as $index => $customer ) {
                    if ( $service->appointmentsLimitReached( $customer['id'], $start_date ) ) {
                        $customer_error = Lib\Entities\Customer::find( $customer['id'] );
                        $result['customers_appointments_limit'][] = sprintf( __( '%s has reached the limit of bookings for this service', 'bookly' ), $customer_error->getFullName() );
                    }
                }
            }
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
                    array( 'cancellation_reason' => $reason )
                );
            }
        }

        Lib\Entities\Appointment::find( $appointment_id )->delete();

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
            ->whereNot( 'a.id', $appointment_id )
            ->where( 'a.staff_id', $staff_id )
            ->whereLt( 'a.start_date', $end_date )
            ->whereGt( 'a.end_date', $start_date )
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
        $one_participant   = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_one_participant' ) ) . '</div>';
        $many_participants = '<div>' . str_replace( "\n", '</div><div>', get_option( 'bookly_cal_many_participants' ) ) . '</div>';
        $postfix_any       = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );
        $participants      = null;
        $default_codes     = array(
            '{amount_due}'        => '',
            '{amount_paid}'       => '',
            '{appointment_date}'  => '',
            '{appointment_time}'  => '',
            '{booking_number}'    => '',
            '{category_name}'     => '',
            '{client_email}'      => '',
            '{client_name}'       => '',
            '{client_first_name}' => '',
            '{client_last_name}'  => '',
            '{client_phone}'      => '',
            '{company_address}'   => get_option( 'bookly_co_address' ),
            '{company_name}'      => get_option( 'bookly_co_name' ),
            '{company_phone}'     => get_option( 'bookly_co_phone' ),
            '{company_website}'   => get_option( 'bookly_co_website' ),
            '{custom_fields}'     => '',
            '{extras}'            => '',
            '{extras_total_price}'=> 0,
            '{location_name}'     => '',
            '{location_info}'     => '',
            '{on_waiting_list}'   => '',
            '{payment_status}'    => '',
            '{payment_type}'      => '',
            '{service_capacity}'  => '',
            '{service_info}'      => '',
            '{service_name}'      => '',
            '{service_price}'     => '',
            '{signed_up}'         => '',
            '{staff_email}'       => '',
            '{staff_info}'        => '',
            '{staff_name}'        => '',
            '{staff_phone}'       => '',
            '{status}'            => '',
            '{total_price}'       => '',
        );
        $appointments = $query
            ->select( 'a.id, a.series_id, a.staff_any, a.location_id, a.start_date, DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) AS end_date,
                COALESCE(s.title,a.custom_service_name) AS service_name, COALESCE(s.color,"silver") AS service_color, s.info AS service_info,
                1 AS service_capacity, COALESCE(ss.price,a.custom_service_price) AS service_price,
                st.full_name AS staff_name, st.email AS staff_email, st.info AS staff_info, st.phone AS staff_phone,
                (SELECT SUM(ca.number_of_persons) FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id) AS total_number_of_persons,
                ca.number_of_persons,
                ca.custom_fields,
                ca.status AS appointment_status,
                ca.extras,
                ca.package_id,
                ct.name AS category_name,
                c.full_name AS client_name, c.first_name AS client_first_name, c.last_name AS client_last_name, c.phone AS client_phone, c.email AS client_email, c.id AS customer_id,
                p.total, p.type AS payment_gateway, p.status AS payment_status, p.paid,
                (SELECT SUM(ca.number_of_persons) FROM ' . Lib\Entities\CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id AND ca.status = "waitlisted") AS on_waiting_list' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Category', 'ct', 'ct.id = s.category_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->groupBy( 'a.id' )
            ->fetchArray();

        foreach ( $appointments as $key => $appointment ) {
            $codes = $default_codes;
            $codes['{appointment_date}'] = Lib\Utils\DateTime::formatDate( $appointment['start_date'] );
            $codes['{appointment_time}'] = Lib\Utils\DateTime::formatTime( $appointment['start_date'] );
            $codes['{booking_number}']   = $appointment['id'];
            $codes['{on_waiting_list}']  = $appointment['on_waiting_list'];
            $codes['{service_name}']     = $appointment['service_name'] ?  esc_html( $appointment['service_name'] ) : __( 'Untitled', 'bookly' );
            $codes['{service_price}']    = Lib\Utils\Price::format( $appointment['service_price'] );
            $codes['{signed_up}']        = $appointment['total_number_of_persons'];
            foreach ( array( 'staff_name', 'staff_phone', 'staff_info', 'staff_email', 'service_info', 'service_capacity', 'category_name' ) as $field ) {
                $codes[ '{' . $field . '}' ] = esc_html( $appointment[ $field ] );
            }
            if ( $appointment['staff_any'] ) {
                $codes['{staff_name}'] .= $postfix_any;
            }
            // Display customer information only if there is 1 customer. Don't confuse with number_of_persons.
            if ( $appointment['number_of_persons'] == $appointment['total_number_of_persons'] ) {
                $participants = 'one';
                $template     = $one_participant;
                foreach ( array( 'client_name', 'client_first_name', 'client_last_name', 'client_phone', 'client_email' ) as $data_entry ) {
                    if ( $appointment[ $data_entry ] ) {
                        $codes[ '{' . $data_entry . '}' ] = esc_html( $appointment[ $data_entry ] );
                    }
                }

                // Payment.
                if ( $appointment['total'] ) {
                    $codes['{total_price}']    = Lib\Utils\Price::format( $appointment['total'] );
                    $codes['{amount_paid}']    = Lib\Utils\Price::format( $appointment['paid'] );
                    $codes['{amount_due}']     = Lib\Utils\Price::format( $appointment['total'] - $appointment['paid'] );
                    $codes['{total_price}']    = Lib\Utils\Price::format( $appointment['total'] );
                    $codes['{payment_type}']   = Lib\Entities\Payment::typeToString( $appointment['payment_gateway'] );
                    $codes['{payment_status}'] = Lib\Entities\Payment::statusToString( $appointment['payment_status'] );
                }
                // Status.
                $codes['{status}'] = Lib\Entities\CustomerAppointment::statusToString( $appointment['appointment_status'] );
            } else {
                $participants = 'many';
                $template     = $many_participants;
            }

            $codes = Lib\Proxy\Shared::prepareCalendarAppointmentCodesData( $codes, $appointment, $participants );

            $appointments[ $key ] = array(
                'id'         => $appointment['id'],
                'start'      => $appointment['start_date'],
                'end'        => $appointment['end_date'],
                'title'      => ' ',
                'desc'       => strtr( $template, $codes ),
                'color'      => $appointment['service_color'],
                'staffId'    => $staff_id,
                'series_id'  => (int) $appointment['series_id'],
                'package_id' => (int) $appointment['package_id'],
                'waitlisted' => (int) $appointment['on_waiting_list'],
                'staff_any'  => (int) $appointment['staff_any'],
            );
        }

        return $appointments;
    }

}