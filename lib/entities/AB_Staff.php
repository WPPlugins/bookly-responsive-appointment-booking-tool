<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Staff
 */
class AB_Staff extends AB_Entity
{
    protected static $table = 'ab_staff';

    protected static $schema = array(
        'id'                 => array( 'format' => '%d' ),
        'wp_user_id'         => array( 'format' => '%d' ),
        'full_name'          => array( 'format' => '%s' ),
        'email'              => array( 'format' => '%s' ),
        'avatar_path'        => array( 'format' => '%s' ),
        'avatar_url'         => array( 'format' => '%s' ),
        'phone'              => array( 'format' => '%s' ),
        'google_data'        => array( 'format' => '%s' ),
        'google_calendar_id' => array( 'format' => '%s' ),
        'position'           => array( 'format' => '%d', 'default' => 9999 ),
    );

    public function save()
    {
        $is_new = ! $this->get( 'id' );

        if ( $is_new && $this->get( 'wp_user_id' ) ) {
            $user = get_user_by( 'id', $this->get( 'wp_user_id' ) );
            if( $user ) {
                $this->set( 'email', $user->get( 'user_email' ) );
            }
        }

        parent::save();

        if ( $is_new ) {
            // Schedule items.
            $staff_id = $this->get( 'id' );
            $index    = 1;
            foreach ( array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) as $week_day ) {
                $item = new AB_StaffScheduleItem();
                $item->set( 'staff_id', $staff_id );
                $item->set( 'day_index', $index ++ );
                $item->set( 'start_time', get_option( "ab_settings_{$week_day}_start" ) ?: null );
                $item->set( 'end_time', get_option( "ab_settings_{$week_day}_end" ) ?: null );
                $item->save();
            }

            // Create holidays for staff
            $this->wpdb->query( sprintf(
                'INSERT INTO `' . AB_Holiday::getTableName(). '` (`parent_id`, `staff_id`, `date`, `repeat_event`, `title`)
                SELECT `id`, %d, `date`, `repeat_event`, `title` FROM `' . AB_Holiday::getTableName() . '` WHERE `staff_id` IS NULL',
                $staff_id
            ) );
        }
    }

    /**
     * Get schedule items of staff member.
     *
     * @return array
     */
    public function getScheduleItems()
    {
        $start_of_week = (int) get_option( 'start_of_week' );
        // Start of week affects the sorting.
        // If it is 0(Sun) then the result should be 1,2,3,4,5,6,7.
        // If it is 1(Mon) then the result should be 2,3,4,5,6,7,1.
        // If it is 2(Tue) then the result should be 3,4,5,6,7,1,2. Etc.
        return AB_StaffScheduleItem::query()
            ->where( 'staff_id',  $this->get( 'id' ) )
            ->sortBy( "IF(r.day_index + 10 - {$start_of_week} > 10, r.day_index + 10 - {$start_of_week}, 16 + r.day_index)" )
            ->indexBy( 'day_index' )
            ->find();
    }

    /**
     * Get appointments for FullCalendar.
     *
     * @param DateTime $start_date
     * @param DateTime $end_date
     *
     * @return array
     */
    public function getAppointmentsForFC( DateTime $start_date, DateTime $end_date )
    {
        $appointments = AB_Appointment::query( 'a' )
            ->select( 'a.id, a.start_date, a.end_date,
                    s.title AS service_title, s.color AS service_color,
                    ss.capacity AS max_capacity,
                    SUM(ca.number_of_persons) AS total_number_of_persons, ca.custom_fields,
                    c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email' )
            ->leftJoin( 'AB_CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'AB_Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'AB_Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'AB_Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'AB_StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->where( 'st.id', $this->get( 'id' ) )
            ->whereBetween( 'DATE(a.start_date)', $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) )
            ->groupBy( 'a.start_date' )
            ->fetchArray();

        foreach ( $appointments as $key => $appointment ) {
            $desc = '';
            if ( $appointment['max_capacity'] == 1 ) {
                foreach ( array( 'customer_name', 'customer_phone', 'customer_email' ) as $data_entry ) {
                    if ( $appointment[ $data_entry ] ) {
                        $desc .= '<div class="fc-employee">' . esc_html( $appointment[ $data_entry ] ) . '</div>';
                    }
                }
                $ca = new AB_CustomerAppointment();
                $ca->set( 'custom_fields', $appointment['custom_fields'] );
                foreach ( $ca->getCustomFields() as $custom_field ) {
                    $desc .= sprintf( '<div class="fc-notes">%s : %s</div>', wp_strip_all_tags( $custom_field['label'] ), esc_html( $custom_field['value'] ) );
                }
            } else {
                $desc .= sprintf( '<div class="fc-notes">%s %s</div>', __( 'Signed up', 'bookly' ), $appointment['total_number_of_persons'] );
                $desc .= sprintf( '<div class="fc-notes">%s %s</div>', __( 'Capacity', 'bookly' ), $appointment['max_capacity'] );
            }

            $appointments[ $key ] = array(
                'id'       => $appointment['id'],
                'start'    => $appointment['start_date'],
                'end'      => $appointment['end_date'],
                'title'    => $appointment['service_title'] ? esc_html( $appointment['service_title'] ) : __( 'Untitled', 'bookly' ),
                'desc'     => $desc,
                'color'    => $appointment['service_color'],
                'staffId'  => $this->get( 'id' )
            );
        }

        return $appointments;
    }

    /**
     * Get AB_StaffService entities associated with this staff member.
     *
     * @return array  Array of entities
     */
    public function getStaffServices()
    {
        $result = array();

        if ( $this->get( 'id' ) ) {
            $staff_services = AB_StaffService::query( 'ss' )
                ->select( 'ss.*, s.title, s.duration, s.price AS service_price, s.color, s.capacity AS service_capacity' )
                ->leftJoin( 'AB_Service', 's', 's.id = ss.service_id' )
                ->where( 'ss.staff_id',  $this->get( 'id' ) )
                ->fetchArray();

            foreach ( $staff_services as $data ) {
                $ss = new AB_StaffService( $data );

                // Inject AB_Service entity.
                $ss->service        = new AB_Service();
                $data[ 'id' ]       = $data[ 'service_id' ];
                $data[ 'price' ]    = $data[ 'service_price' ];
                $data[ 'capacity' ] = $data[ 'service_capacity' ];
                $ss->service->setFields( $data, true );

                $result[] = $ss;
            }
        }

        return $result;
    }

    /**
     * Check whether staff is on holiday on given day.
     *
     * @param DateTime $day
     * @return bool
     */
    public function isOnHoliday( DateTime $day )
    {
        // Check one-time holidays.
        if ( AB_Holiday::query()
            ->where( 'date', $day->format( 'Y-m-d' ) )
            ->whereRaw( 'staff_id = %d OR staff_id IS NULL', array( $this->get( 'id' ) ) )
            ->count() == 0 ) {
            // Check repeating holidays.
            if ( AB_Holiday::query()
                ->whereRaw( 'MONTH(date) = %d AND DAY(date) = %s', array( $day->format( 'm' ), $day->format( 'd' ) ) )
                ->whereRaw( 'staff_id = %d OR staff_id IS NULL', array( $this->get( 'id' ) ) )
                ->count() == 0 ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete staff member.
     */
    public function delete()
    {
        if ( file_exists( $this->get( 'avatar_path' ) ) ) {
            unlink( $this->get( 'avatar_path' ) );
        }
        AB_Holiday::query()->delete()->where( 'staff_id', $this->get( 'id' ) )->execute();
        AB_StaffScheduleItem::query()->delete()->where( 'staff_id', $this->get( 'id' ) )->execute();
        AB_StaffService::query()->delete()->where( 'staff_id', $this->get( 'id' ) )->execute();

        parent::delete();
    }
}
