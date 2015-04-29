<?php

include dirname(__FILE__) . '/../AB_Entity.php';
include 'AB_StaffScheduleItem.php';

/**
 * Class AB_Staff
 */
class AB_Staff extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_staff';
        $this->schema = array(
            'id'          => array( ),
            'wp_user_id'  => array( 'format' => '%d' ),
            'full_name'   => array( 'format' => '%s' ),
            'email'       => array( 'format' => '%s' ),
            'avatar_path' => array( 'format' => '%s' ),
            'avatar_url'  => array( 'format' => '%s' ),
            'phone'       => array( 'format' => '%s' )
        );
        parent::__construct();
    }

    public function save() {
        if ( $this->get( 'id' ) ) {
            parent::save();
        } else {
            $user_exist = $this->wpdb->get_results( $this->wpdb->prepare( 'SELECT * FROM ab_staff WHERE wp_user_id = %d', $this->get( 'wp_user_id' ) ) );

            if ( !count( $user_exist ) ) {
                $formats = array( '%s', '%s' );
                $values = array(
                    'full_name' => $this->get( 'full_name' ),
                    'email'     => $this->get( 'email' )
                );

                if ( $this->get( 'wp_user_id' ) ) {
                    $formats[] = '%d';
                    $values['wp_user_id'] = $this->get( 'wp_user_id' );
	                  $user = get_user_by('id',$this->get('wp_user_id'));
	                  if( $user )
	                  {
		                  $values['email'] = $user->get('user_email');
	                  }
                }

                $this->wpdb->insert( 'ab_staff', $values, $formats );
                $this->set( 'id', $this->wpdb->insert_id);

                // Schedule items.
                $values   = array();
                $staff_id = $this->get( 'id' );
                $id       = 1;
                foreach ( array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) as $week_day ) {
                    $start = get_option( "ab_settings_{$week_day}_start", null );
                    $end   = get_option( "ab_settings_{$week_day}_end", null );
                    $values[] = sprintf(
                        '(NULL, %d, %d, %s, %s)',
                        $staff_id,
                        $id ++,
                        ($start ? "\"$start\"" : 'NULL'),
                        ($end ? "\"$end\"" : 'NULL')
                    );
                }

                $this->wpdb->query('INSERT INTO ab_staff_schedule_item VALUES ' . implode( ',', $values ) );

                // Create holidays for staff
                $this->wpdb->query('INSERT INTO ab_holiday (parent_id, staff_id, holiday, repeat_event, title) SELECT id, '.$this->get( 'id' ).', holiday, repeat_event, title FROM ab_holiday WHERE staff_id IS NULL' );
            }
        }
    }

    public function getScheduleList() {
        if ( ! $this->loaded ) {
            return array();
        }
        $list = $this->wpdb->get_results( $this->wpdb->prepare(
            'SELECT
                si.id,
                si.name,
                ssi.id AS "schedule_item_id",
                ssi.staff_id,
                ssi.start_time,
                ssi.end_time
            FROM `ab_schedule_item` si
            LEFT JOIN `ab_staff_schedule_item` ssi ON ssi.schedule_item_id = si.id
            WHERE ssi.staff_id = %d
            ORDER BY si.id',
            $this->get( 'id' )
        ) );

        if ( ! empty( $list ) ) {
            $wp_week_start_day = get_option('start_of_week', 1);
            $list_start_day    = $list[0]->id - 1;

            // if wp week start day is higher than our
            // cut the list into 2 parts (before and after wp wp week start day)
            // move the second part of the list above the first one
            if ( $wp_week_start_day > $list_start_day ) {
                $list_start = array_slice( $list, 0, $wp_week_start_day );
                $list_end   = array_slice( $list, $wp_week_start_day );
                $list       = $list_end;

                foreach ( $list_start as $list_item ) {
                    $list[] = $list_item;
                }
            }
        }

        return $list;
    }

    /**
     * Get staff appointments for period
     *
     * @param      $start_date
     * @param null $end_date
     *
     * @return array|mixed
     */
    public function getAppointments( $start_date, $end_date = null ) {
        if ( ! $this->loaded ) {
            return array();
        }
        $args = array(
            'SELECT
                  a.id,
                  a.start_date,
                  a.end_date,
                  a.notes,
                  a.customer_id AS "customer_id",
                  service.title,
                  service.color,
                  staff.id AS "staff_id",
                  staff.full_name
              FROM ab_appointment a
              LEFT JOIN ab_service service ON a.service_id = service.id
              LEFT JOIN ab_staff staff ON a.staff_id = staff.id
              WHERE staff.id = %d',
            $this->get( 'id' )
        );

        if ( null !== $end_date ) {
            $args[0] .=' AND DATE(a.start_date) BETWEEN %s AND %s';
            $args[]   = $start_date;
            $args[]   = $end_date;
        } else {
            $args[0] .= ' AND DATE(a.start_date) = %s';
            $args[]   = $start_date;
        }

        $args[0] .= ' GROUP BY a.id';

        return $this->wpdb->get_results( call_user_func_array( array( $this->wpdb, 'prepare' ), $args ) );
    }

    public function delete() {
        parent::delete();
        if ( $this->get( 'avatar_path' ) ) {
            unlink( $this->get( 'avatar_path' ) );
        }
    }
}
