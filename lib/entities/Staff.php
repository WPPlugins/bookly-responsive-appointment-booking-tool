<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Staff
 * @package BooklyLite\Lib\Entities
 */
class Staff extends Lib\Base\Entity
{
    protected static $table = 'ab_staff';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'wp_user_id'         => array( 'format' => '%d' ),
        'full_name'          => array( 'format' => '%s' ),
        'email'              => array( 'format' => '%s' ),
        'attachment_id'      => array( 'format' => '%d' ),
        'phone'              => array( 'format' => '%s' ),
        'google_data'        => array( 'format' => '%s' ),
        'google_calendar_id' => array( 'format' => '%s' ),
        'position'           => array( 'format' => '%d', 'default' => 9999 ),
        'info'               => array( 'format' => '%s' ),
        'visibility'         => array( 'format' => '%s', 'default' => 'public' ),
    );

    protected static $cache = array();

    public function save()
    {
        self::$schema['id'] = 1;
        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'staff_1', $this->get( 'full_name' ) );
            do_action( 'wpml_register_single_string', 'bookly', 'staff_1_info', $this->get( 'info' ) );
        }

        return $return;
    }

    /**
     * Get schedule items of staff member.
     *
     * @return StaffScheduleItem[]
     */
    public function getScheduleItems()
    {
        $start_of_week = (int) get_option( 'start_of_week' );
        // Start of week affects the sorting.
        // If it is 0(Sun) then the result should be 1,2,3,4,5,6,7.
        // If it is 1(Mon) then the result should be 2,3,4,5,6,7,1.
        // If it is 2(Tue) then the result should be 3,4,5,6,7,1,2. Etc.
        return StaffScheduleItem::query()
            ->where( 'staff_id',  1 )
            ->sortBy( "IF(r.day_index + 10 - {$start_of_week} > 10, r.day_index + 10 - {$start_of_week}, 16 + r.day_index)" )
            ->indexBy( 'day_index' )
            ->find();
    }

    /**
     * Get appointments for FullCalendar.
     *
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     * @return array
     */
    public function getAppointmentsForFC( \DateTime $start_date, \DateTime $end_date )
    {
        $appointments = Appointment::query( 'a' )
            ->select( 'a.id, a.start_date, DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) AS end_date,
                s.title AS service_title, s.color AS service_color,
                ss.capacity AS max_capacity,
                (SELECT SUM(ca.number_of_persons) FROM ' . CustomerAppointment::getTableName() . ' ca WHERE ca.appointment_id = a.id) AS total_number_of_persons,
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
            ->whereBetween( 'DATE(a.start_date)', $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) )
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
                $desc = apply_filters( 'bookly_calendar_appointment_description', $desc, $appointment );
                // Custom fields.
                if ( $appointment['custom_fields'] != '[]' ) {
                    $desc .= '<br/>';
                    $ca = new CustomerAppointment();
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
                'id'      => $appointment['id'],
                'start'   => $appointment['start_date'],
                'end'     => $appointment['end_date'],
                'title'   => $appointment['service_title'] ? esc_html( $appointment['service_title'] ) : __( 'Untitled', 'bookly' ),
                'desc'    => $desc,
                'color'   => $appointment['service_color'],
                'staffId' => 1,
            );
        }

        return $appointments;
    }

    /**
     * Get StaffService entities associated with this staff member.
     *
     * @return StaffService[]
     */
    public function getStaffServices()
    {
        $result = array();
        $staff_services = StaffService::query( 'ss' )
            ->select( 'ss.*, s.title, s.duration, s.price AS service_price, s.color, s.capacity AS service_capacity' )
            ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
            ->where( 'ss.staff_id', 1 )
            ->fetchArray();

        foreach ( $staff_services as $data ) {
            $ss = new StaffService( $data );

            // Inject Service entity.
            $ss->service      = new Service();
            $data['id']       = $data['service_id'];
            $data['price']    = $data['service_price'];
            $data['capacity'] = $data['service_capacity'];
            $ss->service->setFields( $data, true );

            $result[] = $ss;
        }

        return $result;
    }

    /**
     * Check whether staff is on holiday on given day.
     *
     * @param \DateTime $day
     * @return bool
     */
    public function isOnHoliday( \DateTime $day )
    {
        $query = Holiday::query()
            ->whereRaw( '( DATE_FORMAT( date, %s ) = %s AND repeat_event = 1 ) OR date = %s', array( '%m-%d', $day->format( 'm-d' ), $day->format( 'Y-m-d' ) ) )
            ->whereRaw( 'staff_id = %d OR staff_id IS NULL', array( 1 ) )
            ->limit( 1 );
        $rows = $query->execute( Lib\Query::HYDRATE_NONE );

        return $rows != 0;
    }

    /**
     * Delete staff member.
     */
    public function delete() { }

    public function getName()
    {
        return Lib\Utils\Common::getTranslatedString( 'staff_1', $this->get( 'full_name' ) );
    }

    public function getInfo()
    {
        return Lib\Utils\Common::getTranslatedString( 'staff_1_info', $this->get( 'info' ) );
    }

}
