<?php
namespace Bookly\Backend\Modules\Appointments;

use Bookly\Lib;
use Bookly\Lib\DataHolders\Booking as DataHolders;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Appointments
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Get list of appointments.
     */
    public static function getAppointments()
    {
        $columns = self::parameter( 'columns' );
        $order   = self::parameter( 'order' );
        $filter  = self::parameter( 'filter' );
        $postfix_any = sprintf( ' (%s)', get_option( 'bookly_l10n_option_employee' ) );

        $query = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'a.id,
                ca.payment_id,
                ca.status,
                ca.id        AS ca_id,
                ca.notes,
                ca.number_of_persons,
                ca.extras,
                ca.rating,
                ca.rating_comment,
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
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = st.id AND ss.service_id = s.id AND ss.location_id = a.location_id' );

        $total = $query->count();

        $sub_query = Lib\Proxy\Files::getSubQueryAttachmentExists();
        if ( ! $sub_query ) {
            $sub_query = '0';
        }
        $query->addSelect( '(' . $sub_query . ') AS attachment' );

        if ( $filter['id'] != '' ) {
            $query->where( 'a.id', $filter['id'] );
        }

        if ( $filter['date'] ) {
            list ( $start, $end ) = explode( ' - ', $filter['date'], 2 );
            $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
            $query->whereBetween( 'a.start_date', $start, $end );
        } else {
            $query->where( 'a.start_date', null );
        }

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
                'id'                => $row['id'],
                'start_date'        => $row['start_date'] === null ? __( 'N/A', 'bookly' ) : Lib\Utils\DateTime::formatDateTime( $row['start_date'] ),
                'staff'             => array(
                    'name' => $row['staff_name'] . ( $row['staff_any'] ? $postfix_any : '' ),
                ),
                'customer'          => array(
                    'full_name' => $row['customer_full_name'],
                    'phone'     => $row['customer_phone'],
                    'email'     => $row['customer_email'],
                ),
                'service'           => array(
                    'title'    => $row['service_title'],
                    'duration' => $service_duration,
                    'extras'   => (array) Lib\Proxy\ServiceExtras::getInfo( json_decode( $row['extras'], true ), false ),
                ),
                'status'            => $row['status'],
                'payment'           => $payment_title,
                'notes'             => $row['notes'],
                'number_of_persons' => $row['number_of_persons'],
                'rating'            => $row['rating'],
                'rating_comment'    => $row['rating_comment'],
                'custom_fields'     => $custom_fields,
                'ca_id'             => $row['ca_id'],
                'attachment'        => $row['attachment'],
                'payment_id'        => $row['payment_id'],
            );

            $custom_fields = array_map( function () { return ''; }, $custom_fields );
        }

        unset( $filter['date'] );
        update_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', $filter );

        wp_send_json( array(
            'draw'            => (int) self::parameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => count( $data ),
            'data'            => $data,
        ) );
    }

    /**
     * Delete customer appointments.
     */
    public static function deleteCustomerAppointments()
    {
        /** @var Lib\Entities\CustomerAppointment $ca */
        foreach ( Lib\Entities\CustomerAppointment::query()->whereIn( 'id', self::parameter( 'data', array() ) )->find() as $ca ) {
            if ( self::parameter( 'notify' ) ) {
                switch ( $ca->getStatus() ) {
                    case Lib\Entities\CustomerAppointment::STATUS_PENDING:
                    case Lib\Entities\CustomerAppointment::STATUS_WAITLISTED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_REJECTED );
                        break;
                    case Lib\Entities\CustomerAppointment::STATUS_APPROVED:
                        $ca->setStatus( Lib\Entities\CustomerAppointment::STATUS_CANCELLED );
                        break;
                }
                Lib\Notifications\Sender::sendSingle(
                    DataHolders\Simple::create( $ca ),
                    null,
                    array( 'cancellation_reason' => self::parameter( 'reason' ) )
                );
            }
            $ca->deleteCascade();
        }
        wp_send_json_success();
    }
}