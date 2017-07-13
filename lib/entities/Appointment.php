<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Appointment
 * @package BooklyLite\Lib\Entities
 */
class Appointment extends Lib\Base\Entity
{
    protected static $table = 'ab_appointments';

    protected static $schema = array(
        'id'              => array( 'format' => '%d' ),
        'series_id'       => array( 'format' => '%d', 'reference' => array( 'entity' => 'Series' ) ),
        'staff_id'        => array( 'format' => '%d', 'reference' => array( 'entity' => 'Staff' ) ),
        'service_id'      => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service' ) ),
        'start_date'      => array( 'format' => '%s' ),
        'end_date'        => array( 'format' => '%s' ),
        'google_event_id' => array( 'format' => '%s' ),
        'extras_duration' => array( 'format' => '%d', 'default' => 0 ),
        'internal_note'   => array( 'format' => '%s' ),
    );

    protected static $cache = array();

    /**
     * Get color of service
     *
     * @param string $default
     * @return string
     */
    public function getColor( $default = '#DDDDDD' )
    {
        if ( ! $this->isLoaded() ) {
            return $default;
        }

        $service = new Service();

        if ( $service->load( $this->get( 'service_id' ) ) ) {
            return $service->get( 'color' );
        }

        return $default;
    }

    /**
     * Get CustomerAppointment entities associated with this appointment.
     *
     * @param bool $with_cancelled
     * @return CustomerAppointment[]   Array of entities
     */
    public function getCustomerAppointments( $with_cancelled = false )
    {
        $result = array();

        if ( $this->get( 'id' ) ) {
            $appointments = CustomerAppointment::query( 'ca' )
                ->select( 'ca.*, c.name, c.phone, c.email' )
                ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
                ->where( 'ca.appointment_id', $this->get( 'id' ) );
            if ( ! $with_cancelled ) {
                $appointments->whereNot( 'ca.status', Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                $appointments->whereNot( 'ca.status', Lib\Entities\CustomerAppointment::STATUS_REJECTED );
            }

            foreach ( $appointments->fetchArray() as $data ) {
                $ca = new CustomerAppointment( $data );

                // Inject Customer entity.
                $ca->customer = new Customer();
                $data['id']   = $data['customer_id'];
                $ca->customer->setFields( $data, true );

                $result[] = $ca;
            }
        }

        return $result;
    }

    /**
     * Set array of customers associated with this appointment.
     *
     * @param array  $cst_data  Array of customer IDs, custom_fields, number_of_persons, extras and status
     * @return array            IDs of customer_appointment with changed status
     */
    public function saveCustomerAppointments( array $cst_data )
    {
        $ca_status_changed = array();
        $ca_data = array();
        foreach ( $cst_data as $item ) {
            if ( array_key_exists( 'ca_id', $item ) ) {
                $ca_id = $item['ca_id'];
            } else do {
                // New CustomerAppointment.
                $ca_id = 'new-' . mt_rand( 1, 999 );
            } while ( array_key_exists( $ca_id, $ca_data ) === true );
            $ca_data[ $ca_id ] = $item;
        }

        // Retrieve customer appointments IDs currently associated with this appointment.
        $current_ids   = array_map( function( CustomerAppointment $ca ) { return $ca->get( 'id' ); }, $this->getCustomerAppointments( true ) );
        $ids_to_delete = array_diff( $current_ids, array_keys( $ca_data ) );
        if ( ! empty ( $ids_to_delete ) ) {
            // Remove redundant customer appointments.
            CustomerAppointment::query()->delete()->whereIn( 'id', $ids_to_delete )->execute();
        }
        // Add new customer appointments.
        foreach ( array_diff( array_keys( $ca_data ), $current_ids ) as $id ) {
            $customer_appointment = new CustomerAppointment();
            $customer_appointment
                ->set( 'appointment_id',    $this->get( 'id' ) )
                ->set( 'customer_id',       $ca_data[ $id ]['id'] )
                ->set( 'custom_fields',     json_encode( $ca_data[ $id ]['custom_fields'] ) )
                ->set( 'extras',            json_encode( $ca_data[ $id ]['extras'] ) )
                ->set( 'status',            $ca_data[ $id ]['status'] )
                ->set( 'number_of_persons', $ca_data[ $id ]['number_of_persons'] )
                ->set( 'location_id',       $ca_data[ $id ]['location_id'] ? $ca_data[ $id ]['location_id'] : null )
                ->save();
            $ca_status_changed[] = $customer_appointment->get( 'id' );
        }

        // Update existing customer appointments.
        foreach ( array_intersect( $current_ids, array_keys( $ca_data ) ) as $id ) {
            $customer_appointment = new CustomerAppointment();
            $customer_appointment->load( $id );

            if ( $customer_appointment->get( 'status' ) != $ca_data[ $id ]['status'] ) {
                $ca_status_changed[] = $customer_appointment->get( 'id' );
                $customer_appointment->set( 'status', $ca_data[ $id ]['status'] );
            }
            $customer_appointment
                ->set( 'number_of_persons', $ca_data[ $id ]['number_of_persons'] )
                ->set( 'custom_fields',     json_encode( $ca_data[ $id ]['custom_fields'] ) )
                ->set( 'extras',            json_encode( $ca_data[ $id ]['extras'] ) )
                ->set( 'location_id',       $ca_data[ $id ]['location_id'] ? $ca_data[ $id ]['location_id'] : null )
                ->save();
        }

        return $ca_status_changed;
    }

    /**
     * Save appointment to database
     *(and delete event in Google Calendar if staff changes).
     *
     * @return false|int
     */
    public function save()
    {
        // Google Calendar.
        if ( $this->isLoaded() && $this->hasGoogleCalendarEvent() ) {
            $modified = $this->getModified();
            if ( array_key_exists( 'staff_id', $modified ) ) {
                // Delete event from the Google Calendar of the old staff if the staff was changed.
                $staff_id = $this->get( 'staff_id' );
                $this->set( 'staff_id', $modified['staff_id'] );
                $this->deleteGoogleCalendarEvent();
                $this->set( 'staff_id', $staff_id );
                $this->set( 'google_event_id', null );
            }
        }

        return parent::save();
    }

    /**
     * Delete entity from database
     *(and delete event in Google Calendar if it exists).
     *
     * @return bool|false|int
     */
    public function delete()
    {
        // Delete all CustomerAppointments for current appointments
        $ca_list = Lib\Entities\CustomerAppointment::query()
            ->where( 'appointment_id', $this->get( 'id' ) )
            ->find();
        /** @var Lib\Entities\CustomerAppointment $ca */
        foreach ( $ca_list as $ca ) {
            $ca->deleteCascade();
        }

        $result = parent::delete();
        if ( $result ) {
            if ( $this->get( 'series_id' ) !== null ) {
                if ( Appointment::query()->where( 'series_id', $this->get( 'series_id' ) )->count() === 0 ) {
                    Series::query()->delete()->where( 'id', $this->get( 'series_id' ) )->execute();
                }
            }
        }

        return $result;
    }

    /**
     * Create or update event in Google Calendar.
     *
     * @return bool
     */
    public function handleGoogleCalendar()
    {
        return false;
    }

    /**
     * Check whether this appointment has an associated event in Google Calendar.
     *
     * @return bool
     */
    public function hasGoogleCalendarEvent()
    {
        return false;
    }

    /**
     * Create a new event in Google Calendar and associate it to this appointment.
     *
     * @return string|false
     */
    public function createGoogleCalendarEvent()
    {
        return false;
    }

    public function updateGoogleCalendarEvent()
    {
        return false;
    }

    /**
     * Delete event from Google Calendar associated to this appointment.
     *
     * @return bool
     */
    public function deleteGoogleCalendarEvent()
    {
        return false;
    }

    /**
     * Get max sum extras duration in customer appointments
     *
     * @return int
     */
    public function getMaxExtrasDuration()
    {
        $duration = 0;
        $customer_appointments = CustomerAppointment::query()
            ->select( 'extras' )
            ->where( 'appointment_id', $this->get( 'id' ) )
            ->whereNot( 'status', CustomerAppointment::STATUS_CANCELLED )
            ->whereNot( 'status', CustomerAppointment::STATUS_REJECTED )
            ->fetchArray();
        foreach ( $customer_appointments as $customer_appointment ) {
            if ( $customer_appointment['extras'] != '[]' ) {
                $extras_duration = apply_filters( 'bookly_service_extras_get_total_duration', 0, (array) json_decode( $customer_appointment['extras'], true ) );
                if ( $extras_duration > $duration ) {
                    $duration = $extras_duration;
                }
            }
        }

        return $duration;
    }

}