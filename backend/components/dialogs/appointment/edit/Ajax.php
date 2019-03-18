<?php
namespace Bookly\Backend\Components\Dialogs\Appointment\Edit;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking as DataHolders;
use Bookly\Backend\Modules\Calendar;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Appointment\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'user' );
    }

    /**
     * Get data needed for appointment form initialisation.
     */
    public static function getDataForAppointmentForm()
    {
        $type = self::parameter( 'type', false ) == 'package'
            ? Lib\Entities\Service::TYPE_PACKAGE
            : Lib\Entities\Service::TYPE_SIMPLE;

        $statuses = Lib\Proxy\CustomStatuses::prepareAllStatuses( array(
            Lib\Entities\CustomerAppointment::STATUS_PENDING,
            Lib\Entities\CustomerAppointment::STATUS_APPROVED,
            Lib\Entities\CustomerAppointment::STATUS_CANCELLED,
            Lib\Entities\CustomerAppointment::STATUS_REJECTED,
            Lib\Entities\CustomerAppointment::STATUS_WAITLISTED,
            Lib\Entities\CustomerAppointment::STATUS_DONE,
        ) );
        $status_items = array();
        foreach ( $statuses as $status ) {
            $status_items[ $status ] = Lib\Entities\CustomerAppointment::statusToString( $status );
        }

        $result = array(
            'staff'                    => array(),
            'customers'                => array(),
            'start_time'               => array(),
            'end_time'                 => array(),
            'app_start_time'           => null,  // Appointment start time which may not be in the list of start times.
            'app_end_time'             => null,  // Appointment end time which may not be in the list of end times.
            'week_days'                => array(),
            'time_interval'            => Lib\Config::getTimeSlotLength(),
            'status'                   => array(
                'items' => $status_items,
            ),
            'extras_consider_duration' => (int) Lib\Proxy\ServiceExtras::considerDuration( true ),
            'extras_multiply_nop'      => (int) get_option( 'bookly_service_extras_multiply_nop', 1 ),
        );

        // Staff list.
        $staff         = Lib\Entities\Staff::query()->findOne();
        $staff_members = $staff ? Lib\Config::proActive() ? Lib\Utils\Common::isCurrentUserSupervisor() ? Lib\Entities\Staff::query()->sortBy( 'position' )->find() : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->find() : array( $staff ) : array();
        $postfix_archived = sprintf( ' (%s)', __( 'Archived', 'bookly' ) );

        $max_duration  = 0;
        $has_categories = (bool) Lib\Entities\Category::query()->findOne();

        foreach ( $staff_members as $staff_member ) {
            $services = array();
            if ( $type == Lib\Entities\Service::TYPE_SIMPLE ) {
                $services = Proxy\Pro::addCustomService( $services );
            }
            foreach ( $staff_member->getStaffServices( $type ) as $staff_service ) {
                $sub_services = $staff_service->service->getSubServices();
                if ( $type == Lib\Entities\Service::TYPE_SIMPLE || ! empty( $sub_services ) ) {
                    if ( $staff_service->getLocationId() === null || Lib\Proxy\Locations::prepareStaffLocationId( $staff_service->getLocationId(), $staff_service->getStaffId() ) == $staff_service->getLocationId() ) {
                        if ( ! in_array( $staff_service->service->getId(), array_map( function ( $service ) { return $service['id']; }, $services ) ) ) {
                            $category = Lib\Entities\Category::find( Lib\Entities\Service::find( $staff_service->getServiceId() )->getCategoryId() );
                            $services[] = array(
                                'id'              => $staff_service->service->getId(),
                                'title'           => sprintf(
                                    '%s (%s)',
                                    $staff_service->service->getTitle(),
                                    Lib\Utils\DateTime::secondsToInterval( $staff_service->service->getDuration() )
                                ),
                                'category'        => $category ? $category->getName() : ( $has_categories ? __( 'Uncategorized', 'bookly' ) : ''),
                                'duration'        => $staff_service->service->getDuration(),
                                'units_min'       => $staff_service->service->getUnitsMin(),
                                'units_max'       => $staff_service->service->getUnitsMax(),
                                'locations'       => array(
                                    ( $staff_service->getLocationId() ?: 0 ) => array(
                                        'capacity_min' => Lib\Config::groupBookingActive() ? $staff_service->getCapacityMin() : 1,
                                        'capacity_max' => Lib\Config::groupBookingActive() ? $staff_service->getCapacityMax() : 1,
                                    ),
                                ),
                            );
                            $max_duration = max( $max_duration, $staff_service->service->getUnitsMax() * $staff_service->service->getDuration() );
                        } else {
                            array_walk( $services, function ( &$item ) use ( $staff_service ) {
                                if ( $item['id'] == $staff_service->service->getId() ) {
                                    $item['locations'][ $staff_service->getLocationId() ?: 0 ] = array(
                                        'capacity_min' => Lib\Config::groupBookingActive() ? $staff_service->getCapacityMin() : 1,
                                        'capacity_max' => Lib\Config::groupBookingActive() ? $staff_service->getCapacityMax() : 1,
                                    );
                                }
                            } );
                        }
                    }
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
                'full_name' => $staff_member->getFullName() . ( $staff_member->getVisibility() == 'archive' ? $postfix_archived : '' ),
                'archived'  => $staff_member->getVisibility() == 'archive',
                'services'  => $services,
                'locations' => $locations,
                'category'  => Lib\Proxy\Pro::getStaffCategoryName( $staff_member->getCategoryId() ),
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
                'id'                 => $customer->getId(),
                'name'               => $name,
                'status'             => Lib\Proxy\CustomerGroups::prepareDefaultAppointmentStatus( get_option( 'bookly_gen_default_appointment_status' ), $customer->getGroupId() ),
                'custom_fields'      => array(),
                'timezone'           => Lib\Proxy\Pro::getLastCustomerTimezone( $customer->getId() ),
                'number_of_persons'  => 1,
            );
        }

        // Time list.
        $ts_length  = Lib\Config::getTimeSlotLength();
        $time_start = 0;
        $time_end   = max( $max_duration + DAY_IN_SECONDS, DAY_IN_SECONDS * 2 );

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

        if ( $type == Lib\Entities\Service::TYPE_PACKAGE ) {
            $result = Proxy\Shared::prepareDataForPackage( $result );
        }

        wp_send_json( $result );
    }

    /**
     * Get appointment data when editing an appointment.
     */
    public static function getDataForAppointment()
    {
        $response = array( 'success' => false, 'data' => array( 'customers' => array() ) );

        $appointment = new Lib\Entities\Appointment();
        if ( $appointment->load( self::parameter( 'id' ) ) ) {
            $response['success'] = true;

            $query = Lib\Entities\Appointment::query( 'a' )
                ->select( 'SUM(ca.number_of_persons) AS total_number_of_persons,
                    a.staff_id,
                    a.staff_any,
                    a.service_id,
                    a.custom_service_name,
                    a.custom_service_price,
                    a.start_date,
                    a.end_date,
                    a.internal_note,
                    a.location_id' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id AND ss.location_id = a.location_id' )
                ->where( 'a.id', $appointment->getId() );
            if ( Lib\Config::groupBookingActive() ) {
                $query->addSelect( 'ss.capacity_min AS min_capacity, ss.capacity_max AS max_capacity' );
            } else {
                $query->addSelect( '1 AS min_capacity, 1 AS max_capacity' );
            }

            $info = $query->fetchRow();
            $response['data']['total_number_of_persons'] = $info['total_number_of_persons'];
            $response['data']['min_capacity']            = $info['min_capacity'];
            $response['data']['max_capacity']            = $info['max_capacity'];
            $response['data']['start_date']              = $info['start_date'];
            $response['data']['end_date']                = $info['end_date'];
            $response['data']['start_time']              = $info['start_date'] ?
                array(
                    'value' => date( 'H:i', strtotime( $info['start_date'] ) ),
                    'title' => Lib\Utils\DateTime::formatTime( $info['start_date'] ),
                ) : null;
            $response['data']['end_time']                = $info['end_date'] ?
                array(
                    'value' => date( 'H:i', strtotime( $info['end_date'] ) ),
                    'title' => Lib\Utils\DateTime::formatTime( $info['end_date'] ),
                ) : null;
            $response['data']['staff_id']                = $info['staff_id'];
            $response['data']['staff_any']               = (int) $info['staff_any'];
            $response['data']['service_id']              = $info['service_id'];
            $response['data']['custom_service_name']     = $info['custom_service_name'];
            $response['data']['custom_service_price']    = (float) $info['custom_service_price'];
            $response['data']['internal_note']           = $info['internal_note'];
            $response['data']['location_id']             = $info['location_id'];

            $customers = Lib\Entities\CustomerAppointment::query( 'ca' )
                ->select( 'ca.id,
                    ca.series_id,
                    ca.customer_id,
                    ca.package_id,
                    ca.custom_fields,
                    ca.extras,
                    ca.extras_multiply_nop,
                    ca.extras_consider_duration,
                    ca.number_of_persons,
                    ca.notes,
                    ca.status,
                    ca.payment_id,
                    ca.collaborative_service_id,
                    ca.collaborative_token,
                    ca.compound_service_id,
                    ca.compound_token,
                    ca.time_zone,
                    ca.time_zone_offset,
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
                $collaborative_service = '';
                if ( $customer['collaborative_service_id'] !== null ) {
                    $service = new Lib\Entities\Service();
                    if ( $service->load( $customer['collaborative_service_id'] ) ) {
                        $collaborative_service = $service->getTranslatedTitle();
                    }
                }
                $compound_service = '';
                if ( $customer['compound_service_id'] !== null ) {
                    $service = new Lib\Entities\Service();
                    if ( $service->load( $customer['compound_service_id'] ) ) {
                        $compound_service = $service->getTranslatedTitle();
                    }
                }
                $custom_fields = (array) json_decode( $customer['custom_fields'], true );
                $response['data']['customers'][] = array(
                    'id'                       => $customer['customer_id'],
                    'ca_id'                    => $customer['id'],
                    'series_id'                => $customer['series_id'],
                    'package_id'               => $customer['package_id'],
                    'collaborative_service'    => $collaborative_service,
                    'collaborative_token'      => $customer['collaborative_token'],
                    'compound_service'         => $compound_service,
                    'compound_token'           => $customer['compound_token'],
                    'custom_fields'            => $custom_fields,
                    'files'                    => Lib\Proxy\Files::getFileNamesForCustomFields( $custom_fields ),
                    'extras'                   => (array) json_decode( $customer['extras'], true ),
                    'extras_multiply_nop'      => $customer['extras_multiply_nop'],
                    'extras_consider_duration' => (int) $customer['extras_consider_duration'],
                    'number_of_persons'        => $customer['number_of_persons'],
                    'notes'                    => $customer['notes'],
                    'payment_id'               => $customer['payment_id'],
                    'payment_type'             => $customer['payment'] != $customer['payment_total'] ? 'partial' : 'full',
                    'payment_title'            => $payment_title,
                    'status'                  => $customer['status'],
                    'timezone'                 => Lib\Proxy\Pro::getCustomerTimezone( $customer['time_zone'], $customer['time_zone_offset'] ),
                );
            }
        }

        wp_send_json( $response );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public static function saveAppointmentForm()
    {
        $response = array( 'success' => false );

        $appointment_id       = (int) self::parameter( 'id', 0 );
        $staff_id             = (int) self::parameter( 'staff_id', 0 );
        $service_id           = (int) self::parameter( 'service_id', -1 );
        $custom_service_name  = trim( self::parameter( 'custom_service_name' ) );
        $custom_service_price = trim( self::parameter( 'custom_service_price' ) );
        $location_id          = (int) self::parameter( 'location_id', 0 );
        $skip_date            = self::parameter( 'skip_date', 0 );
        $start_date           = self::parameter( 'start_date' );
        $end_date             = self::parameter( 'end_date' );
        $repeat               = json_decode( self::parameter( 'repeat', '[]' ), true );
        $schedule             = self::parameter( 'schedule', array() );
        $customers            = json_decode( self::parameter( 'customers', '[]' ), true );
        $notification         = self::parameter( 'notification', 'no' );
        $internal_note        = self::parameter( 'internal_note' );
        $created_from         = self::parameter( 'created_from' );

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
        if ( ! $skip_date ) {
            if ( ! $start_date ) {
                $response['errors']['time_interval'] = __( 'Start time must not be empty', 'bookly' );
            } elseif ( ! $end_date ) {
                $response['errors']['time_interval'] = __( 'End time must not be empty', 'bookly' );
            } elseif ( $start_date == $end_date ) {
                $response['errors']['time_interval'] = __( 'End time must not be equal to start time', 'bookly' );
            }
        }

        if ( $service_id == -1 ) {
            $response['errors']['service_required'] = true;
        } else if ( $service_id === null && $custom_service_name === null ) {
            $response['errors']['custom_service_name_required'] = true;
        }
        $total_number_of_persons = 0;
        $max_extras_duration = 0;
        foreach ( $customers as $i => $customer ) {
            if ( in_array( $customer['status'], Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_PENDING,
                Lib\Entities\CustomerAppointment::STATUS_APPROVED
            ) ) ) ) {
                $total_number_of_persons += $customer['number_of_persons'];
                if ( $customer['extras_consider_duration'] ) {
                    $extras_duration = Lib\Proxy\ServiceExtras::getTotalDuration( $customer['extras'] );
                    if ( $extras_duration > $max_extras_duration ) {
                        $max_extras_duration = $extras_duration;
                    }
                }
            }
            $customers[ $i ]['created_from'] = ( $created_from == 'backend' ) ? 'backend' : 'frontend';
        }
        if ( $service_id ) {
            $staff_service = new Lib\Entities\StaffService();
            $staff_service->loadBy( array(
                'staff_id'    => $staff_id,
                'service_id'  => $service_id,
                'location_id' => $location_id ?: null,
            ) );
            if ( ! $staff_service->isLoaded() ) {
                $staff_service->loadBy( array(
                    'staff_id'    => $staff_id,
                    'service_id'  => $service_id,
                    'location_id' => null,
                ) );
            }
            if ( $total_number_of_persons > $staff_service->getCapacityMax() ) {
                $response['errors']['overflow_capacity'] = sprintf(
                    __( 'The number of customers should not be more than %d', 'bookly' ),
                    $staff_service->getCapacityMax()
                );
            }
        }

        // If no errors then try to save the appointment.
        if ( ! isset ( $response['errors'] ) ) {
            $duration = Lib\Slots\DatePoint::fromStr( $end_date )->diff( Lib\Slots\DatePoint::fromStr( $start_date ) );
            if ( ! $skip_date && $repeat['enabled'] ) {
                // Series.
                if ( ! empty ( $schedule ) ) {
                    /** @var DataHolders\Order[] $orders */
                    $orders = array();

                    if ( $service_id ) {
                        $service = Lib\Entities\Service::find( $service_id );
                    } else {
                        $service = new Lib\Entities\Service();
                        $service
                            ->setTitle( $custom_service_name )
                            ->setDuration( $duration )
                            ->setPrice( $custom_service_price );
                    }

                    foreach ( $customers as $customer ) {
                        // Create new series.
                        $series = new Lib\Entities\Series();
                        $series
                            ->setRepeat( self::parameter( 'repeat' ) )
                            ->setToken( Lib\Utils\Common::generateToken( get_class( $series ), 'token' ) )
                            ->save();

                        // Create order
                        if ( $notification != 'no' ) {
                            $orders[ $customer['id'] ] = DataHolders\Order::create( Lib\Entities\Customer::find( $customer['id'] ) )
                                ->addItem( 0, DataHolders\Series::create( $series ) );
                        }

                        foreach ( $schedule as $i => $slot ) {
                            $slot       = json_decode( $slot, true );
                            $start_date = $slot[0][2];
                            $end_date   = Lib\Slots\DatePoint::fromStr( $start_date )->modify( $duration )->format( 'Y-m-d H:i:s' );
                            // Try to find existing appointment
                            $appointment = Lib\Entities\Appointment::query( 'a' )
                                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                                ->where( 'a.staff_id', $staff_id )
                                ->where( 'a.service_id', $service_id )
                                ->whereNotIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                                    Lib\Entities\CustomerAppointment::STATUS_CANCELLED,
                                    Lib\Entities\CustomerAppointment::STATUS_REJECTED
                                ) ) )
                                ->where( 'start_date', $start_date )
                                ->findOne();

                            $ca_customers = array();
                            if ( ! $appointment ) {
                                // Create appointment.
                                $appointment = new Lib\Entities\Appointment();
                                $appointment
                                    ->setLocationId( $location_id )
                                    ->setStaffId( $staff_id )
                                    ->setServiceId( $service_id )
                                    ->setCustomServiceName( $custom_service_name )
                                    ->setCustomServicePrice( $custom_service_price )
                                    ->setStartDate( $start_date )
                                    ->setEndDate( $end_date )
                                    ->setInternalNote( $internal_note )
                                    ->setExtrasDuration( $max_extras_duration )
                                    ->save();
                            } else {
                                foreach ( $appointment->getCustomerAppointments( true ) as $ca ) {
                                    $ca_customer                  = $ca->getFields();
                                    $ca_customer['ca_id']         = $ca->getId();
                                    $ca_customer['extras']        = json_decode( $ca_customer['extras'], true );
                                    $ca_customer['custom_fields'] = json_decode( $ca_customer['custom_fields'], true );
                                    $ca_customers[]               = $ca_customer;
                                }
                            }

                            if ( $appointment->getId() ) {
                                // Save customer appointments.
                                $ca_list = $appointment->saveCustomerAppointments( array_merge( $ca_customers, array( $customer ) ), $series->getId() );
                                // Google Calendar.
                                Lib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                                // Outlook Calendar.
                                Lib\Proxy\OutlookCalendar::syncEvent( $appointment );
                                // Waiting list.
                                Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );

                                if ( $notification != 'no' ) {
                                    foreach ( $ca_list as $ca ) {
                                        $item = DataHolders\Simple::create( $ca )
                                            ->setService( $service )
                                            ->setAppointment( $appointment );
                                        $orders[ $ca->getCustomerId() ]->getItem( 0 )->addItem( $i, $item );
                                    }
                                }
                            }
                        }
                        if ( $customer['payment_create'] === true ) {
                            Proxy\RecurringAppointments::createBackendPayment( $series, $customer );
                        }
                    }
                    if ( $notification != 'no' ) {
                        foreach ( $orders as $order ) {
                            Lib\Notifications\Booking\Sender::sendForOrder( $order, array(), $notification == 'all' );
                        }
                    }
                }
                $response['success'] = true;
                $response['data']    = array( 'staffId' => $staff_id );  // make FullCalendar refetch events
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
                    ->setStaffId( $staff_id )
                    ->setServiceId( $service_id )
                    ->setCustomServiceName( $custom_service_name )
                    ->setCustomServicePrice( $custom_service_price )
                    ->setStartDate( $skip_date ? null : $start_date )
                    ->setEndDate( $skip_date ? null : $end_date )
                    ->setInternalNote( $internal_note )
                    ->setExtrasDuration( $max_extras_duration );

                if ( $appointment->save() !== false ) {
                    // Save customer appointments.
                    $ca_status_changed = $appointment->saveCustomerAppointments( $customers );

                    foreach ( $customers as $customer ) {
                        if ( $customer['payment_create'] === true && $customer['series_id'] ) {
                            Proxy\RecurringAppointments::createBackendPayment( Lib\Entities\Series::find( $customer['series_id'] ), $customer );
                        }
                    }

                    // Google Calendar.
                    Lib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                    // Outlook Calendar.
                    Lib\Proxy\OutlookCalendar::syncEvent( $appointment );
                    // Waiting list.
                    Lib\Proxy\WaitingList::handleParticipantsChange( $appointment );

                    // Send notifications.
                    if ( $notification == 'changed_status' ) {
                        foreach ( $ca_status_changed as $ca ) {
                            Lib\Notifications\Booking\Sender::sendForCA( $ca, $appointment );
                        }
                    } elseif ( $notification == 'all' ) {
                        $ca_list = $appointment->getCustomerAppointments( true );
                        foreach ( $ca_status_changed as $ca ) {
                            // The value "just_created" was initialized for the objects of this array
                            Lib\Notifications\Booking\Sender::sendForCA( $ca, $appointment, array(), true );
                            unset( $ca_list[ $ca->getId() ] );
                        }
                        foreach ( $ca_list as $ca ) {
                            Lib\Notifications\Booking\Sender::sendForCA( $ca, $appointment, array(), true );
                        }
                    }

                    $response['success'] = true;
                    $response['data']    = self::_getAppointmentForFC( $staff_id, $appointment->getId() );
                } else {
                    $response['errors'] = array( 'db' => __( 'Could not save appointment in database.', 'bookly' ) );
                }
            }
        }
        update_user_meta( get_current_user_id(), 'bookly_appointment_form_send_notifications', $notification );

        wp_send_json( $response );
    }

    /**
     * Check whether appointment settings produce errors.
     */
    public static function checkAppointmentErrors()
    {
        $start_date     = self::parameter( 'start_date' );
        $end_date       = self::parameter( 'end_date' );
        $staff_id       = (int) self::parameter( 'staff_id' );
        $service_id     = (int) self::parameter( 'service_id' );
        $location_id    = Lib\Proxy\Locations::prepareStaffScheduleLocationId( self::parameter( 'location_id' ), $staff_id ) ?: null;
        $appointment_id = (int) self::parameter( 'appointment_id' );
        $appointment_duration = strtotime( $end_date ) - strtotime( $start_date );
        $customers      = json_decode( self::parameter( 'customers', '[]' ), true );
        $service        = Lib\Entities\Service::find( $service_id );
        $service_duration = $service ? $service->getDuration() : 0;

        $result = array(
            'date_interval_not_available'      => false,
            'date_interval_warning'            => false,
            'interval_not_in_staff_schedule'   => false,
            'interval_not_in_service_schedule' => false,
            'staff_reaches_working_time_limit' => false,
            'customers_appointments_limit'     => array(),
        );

        $max_extras_duration = 0;
        foreach ( $customers as $customer ) {
            if ( in_array( $customer['status'], Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_PENDING,
                Lib\Entities\CustomerAppointment::STATUS_APPROVED
            ) ) ) ) {
                if ( $customer['extras_consider_duration'] ) {
                    $extras_duration = Lib\Proxy\ServiceExtras::getTotalDuration( $customer['extras'] );
                    if ( $extras_duration > $max_extras_duration ) {
                        $max_extras_duration = $extras_duration;
                    }
                }
            }
        }
        if ( $start_date && $end_date ) {
            $total_end_date = $end_date;
            if ( $max_extras_duration > 0 ) {
                $total_end_date = date_create( $end_date )->modify( '+' . $max_extras_duration . ' sec' )->format( 'Y-m-d H:i:s' );
            }
            if ( ! self::_dateIntervalIsAvailableForAppointment( $start_date, $total_end_date, $staff_id, $appointment_id ) ) {
                $result['date_interval_not_available'] = true;
            }

            // Check if selected interval fit into staff schedule.
            $interval_valid = true;
            if ( $staff_id && $start_date ) {
                $staff = Lib\Entities\Staff::find( $staff_id );

                // Check if interval is suitable for staff's hours limit
                $result['staff_reaches_working_time_limit'] = Lib\Proxy\Pro::getWorkingTimeLimitError( $staff, $start_date, $end_date, $appointment_duration + $max_extras_duration, $appointment_id ) ?: false;

                if ( $service_duration >= DAY_IN_SECONDS ) {
                    // For services with duration 24+ hours check holidays and days off
                    for ( $day = 0; $day < $service_duration / DAY_IN_SECONDS; $day ++ ) {
                        $work_date = date_create( $start_date )->modify( sprintf( '%s days', $day ) );
                        $week_day  = $work_date->format( 'w' ) + 1;
                        // Check staff schedule for days off
                        if ( $staff->isOnHoliday( $work_date ) ||
                             ! Lib\Entities\StaffScheduleItem::query()
                                 ->select( 'id' )
                                 ->where( 'staff_id', $staff_id )
                                 ->where( 'location_id', $location_id )
                                 ->where( 'day_index', $week_day )
                                 ->whereNot( 'start_time', null )
                                 ->fetchRow()
                        ) {
                            $interval_valid = false;
                            break;
                        }
                    }
                } else {
                    // Check day before and current day to get night schedule from previous day.
                    $interval_valid = false;
                    for ( $day = 0; $day <= 1; $day ++ ) {
                        $day_start_date = date_create( $start_date )->modify( sprintf( '%s days', $day - 1 ) );
                        $day_end_date   = date_create( $end_date )->modify( sprintf( '%s days', $day - 1 ) );
                        if ( ! $staff->isOnHoliday( $day_start_date ) ) {
                            $day_start_hour = ( 1 - $day ) * 24 + $day_start_date->format( 'G' );
                            $day_end_hour   = ( 1 - $day ) * 24 + $day_end_date->format( 'G' );
                            $day_start_time = sprintf( '%02d:%02d:00', $day_start_hour, $day_start_date->format( 'i' ) );
                            $day_end_time   = sprintf( '%02d:%02d:00', $day_end_hour >= $day_start_hour ? $day_end_hour : $day_end_hour + 24, $day_end_date->format( 'i' ) );

                            $special_days = (array) Lib\Proxy\SpecialDays::getSchedule( array( $staff_id ), $day_start_date, $day_start_date );
                            if ( ! empty( $special_days ) ) {
                                // Check if interval fit into special day schedule.
                                $special_day = current( $special_days );
                                if ( ( $special_day['start_time'] <= $day_start_time ) && ( $special_day['end_time'] >= $day_end_time ) ) {
                                    if ( ! ( $special_day['break_start'] && ( $special_day['break_start'] < $day_end_time ) && ( $special_day['break_end'] > $day_start_time ) ) ) {
                                        $interval_valid = true;
                                        break;
                                    }
                                }
                            } else {
                                // Check if interval fit into regular staff working schedule.
                                $week_day = $day_start_date->format( 'w' ) + 1;
                                $ssi      = Lib\Entities\StaffScheduleItem::query()
                                    ->select( 'id' )
                                    ->where( 'staff_id', $staff_id )
                                    ->where( 'location_id', $location_id )
                                    ->where( 'day_index', $week_day )
                                    ->whereNot( 'start_time', null )
                                    ->whereLte( 'start_time', $day_start_time )
                                    ->whereGte( 'end_time', $day_end_time )
                                    ->fetchRow();
                                if ( $ssi ) {
                                    // Check if interval not intercept with breaks.
                                    if ( Lib\Entities\ScheduleItemBreak::query()
                                             ->where( 'staff_schedule_item_id', $ssi['id'] )
                                             ->whereLt( 'start_time', $day_end_time )
                                             ->whereGt( 'end_time', $day_start_time )
                                             ->count() == 0
                                    ) {
                                        $interval_valid = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ( ! $interval_valid ) {
                $result['interval_not_in_staff_schedule'] = true;
            }
            if ( $service ) {
                if ( $service_duration >= DAY_IN_SECONDS ) {
                    // For services with duration 24+ hours check days off
                    $service_schedule = (array) Lib\Proxy\ServiceSchedule::getSchedule( $service_id );
                    $interval_valid   = true;

                    // Check service schedule and service special days
                    for ( $day = 0; $day < $service_duration / DAY_IN_SECONDS; $day ++ ) {
                        $work_date = date_create( $start_date )->modify( sprintf( '%s days', $day ) );
                        $week_day  = $work_date->format( 'w' ) + 1;
                        // Check service schedule for days off
                        $service_schedule_valid = true;
                        if ( Lib\Config::serviceScheduleActive() ) {
                            $service_schedule_valid = false;
                            foreach ( $service_schedule as $day_schedule ) {
                                if ( $day_schedule['day_index'] == $week_day && $day_schedule['start_time'] ) {
                                    $service_schedule_valid = true;
                                    break;
                                }
                            }
                        }
                        if ( ! $service_schedule_valid ) {
                            $interval_valid = false;
                            break;
                        }
                        // Check service special days for days off
                        $service_special_days_valid = true;
                        if ( Lib\Config::specialDaysActive() ) {
                            $special_days = (array) Lib\Proxy\SpecialDays::getServiceSchedule( $service_id, $work_date, $work_date );
                            if ( ! empty( $special_days ) ) {
                                $service_special_days_valid = false;
                                $schedule                   = current( $special_days );
                                if ( $schedule['start_time'] ) {
                                    $service_special_days_valid = true;
                                }
                            }
                        }
                        if ( ! $service_special_days_valid ) {
                            $interval_valid = false;
                            break;
                        }
                    }
                    if ( ! $interval_valid ) {
                        $result['interval_not_in_service_schedule'] = true;
                    }
                    // Check staff schedule and staff special days
                    $interval_valid = true;
                    for ( $day = 0; $day < $service_duration / DAY_IN_SECONDS; $day ++ ) {
                        $work_date = date_create( $start_date )->modify( sprintf( '%s days', $day ) );
                        $week_day  = $work_date->format( 'w' ) + 1;
                        if ( Lib\Entities\StaffScheduleItem::query()
                                 ->where( 'staff_id', $staff_id )
                                 ->where( 'day_index', $week_day )
                                 ->whereNot( 'start_time', null )
                                 ->count() == 0
                        ) {
                            $interval_valid = false;
                            break;
                        }
                    }
                    if ( ! $interval_valid ) {
                        $result['interval_not_in_staff_schedule'] = true;
                    }
                } else {
                    // Check if selected interval fit into service schedule.
                    $interval_valid = false;
                    // Check day before and current day to get night schedule from previous day.
                    for ( $day = 0; $day <= 1; $day ++ ) {
                        $day_start_date = date_create( $start_date )->modify( sprintf( '%s days', $day - 1 ) );
                        $day_end_date   = date_create( $end_date )->modify( sprintf( '%s days', $day - 1 ) );

                        $day_start_hour = ( 1 - $day ) * 24 + $day_start_date->format( 'G' );
                        $day_end_hour   = ( 1 - $day ) * 24 + $day_end_date->format( 'G' );
                        $day_start_time = sprintf( '%02d:%02d:00', $day_start_hour, $day_start_date->format( 'i' ) );
                        $day_end_time   = sprintf( '%02d:%02d:00', $day_end_hour >= $day_start_hour ? $day_end_hour : $day_end_hour + 24, $day_end_date->format( 'i' ) );

                        $special_days = (array) Lib\Proxy\SpecialDays::getServiceSchedule( $service_id, $day_start_date, $day_start_date );
                        if ( ! empty( $special_days ) ) {
                            // Check if interval fit into special day schedule.
                            $special_day = current( $special_days );
                            if ( ( $special_day['start_time'] <= $day_start_time ) && ( $special_day['end_time'] >= $day_end_time ) ) {
                                if ( ! ( $special_day['break_start'] && ( $special_day['break_start'] < $day_end_time ) && ( $special_day['break_end'] > $day_start_time ) ) ) {
                                    $interval_valid = true;
                                    break;
                                }
                            }
                        } else {
                            // Check if interval fit into service working schedule.
                            $schedule = (array) Lib\Proxy\ServiceSchedule::getSchedule( $service_id );
                            if ( ! empty ( $schedule ) ) {
                                $week_day = $day_start_date->format( 'w' ) + 1;
                                foreach ( $schedule as $schedule_day ) {
                                    if ( $schedule_day['day_index'] == $week_day ) {
                                        if ( ( $schedule_day['start_time'] <= $day_start_time ) && ( $schedule_day['end_time'] >= $day_end_time ) ) {
                                            $interval_valid = true;
                                            if ( $schedule_day['break_start'] && ( $schedule_day['break_start'] < $day_end_time ) && ( $schedule_day['break_end'] > $day_start_time ) ) {
                                                $interval_valid = false;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $interval_valid = true;
                                break;
                            }
                        }
                    }
                    if ( ! $interval_valid ) {
                        $result['interval_not_in_service_schedule'] = true;
                    }
                    // Service duration interval is not equal to.
                    $result['date_interval_warning'] = ! ( $appointment_duration >= $service->getMinDuration() && $appointment_duration <= $service->getMaxDuration() && ( $service_duration == 0 || $appointment_duration % $service_duration == 0 ) );
                }

                // Check customers for appointments limit
                foreach ( $customers as $index => $customer ) {
                    if ( $service->appointmentsLimitReached( $customer['id'], array( $start_date ) ) ) {
                        $customer_error                           = Lib\Entities\Customer::find( $customer['id'] );
                        $result['customers_appointments_limit'][] = sprintf( __( '%s has reached the limit of bookings for this service', 'bookly' ), $customer_error->getFullName() );
                    }
                }
            }
        }

        wp_send_json( $result );
    }

    /**
     * Get appointment for FullCalendar.
     *
     * @param integer $staff_id
     * @param int $appointment_id
     * @return array
     */
    private static function _getAppointmentForFC( $staff_id, $appointment_id )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->where( 'a.id', $appointment_id );

        $appointments = Calendar\Page::buildAppointmentsForFC( $staff_id, $query );

        return $appointments[0];
    }

    /**
     * Check whether interval is available for given appointment.
     *
     * @param $start_date
     * @param $end_date
     * @param $staff_id
     * @param $appointment_id
     * @return bool
     */
    private static function _dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id )
    {
        return Lib\Entities\Appointment::query( 'a' )
            ->whereNot( 'a.id', $appointment_id )
            ->where( 'a.staff_id', $staff_id )
            ->whereLt( 'a.start_date', $end_date )
            ->whereGt( 'a.end_date', $start_date )
            ->count() == 0;
    }
}