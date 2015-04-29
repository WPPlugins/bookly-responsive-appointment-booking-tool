<?php

class AB_ServiceBooking {

    /**
     * @var array
     */
    private $categories = array();

    /**
     * @var array
     */
    private $services = array();

    /**
     * @var array
     */
    private $staff = array();

    private $client_timezone_offset = false;

    public function load() {
        global $wpdb;

        $rows = $wpdb->get_results( '
            SELECT c.id AS category_id, c.name AS category_name,
                s.id AS service_id, s.title AS service_name,
                st.id AS staff_id, st.full_name AS staff_name,
                ss.price AS price
            FROM ab_service s
            LEFT JOIN ab_staff_service ss ON s.id = ss.service_id
            LEFT JOIN ab_staff st ON ss.staff_id = st.id
            LEFT JOIN ab_category c ON s.category_id = c.id
            WHERE staff_id IS NOT NULL
            ORDER BY service_name
        ' );

        foreach ($rows as $row) {
            if ( !isset($this->services[$row->service_id]) ) {
                $service = new stdClass();
                $service->id = $row->service_id;
                $service->name = $row->service_name;
                $service->category_id = $row->category_id;
                $service->staff = array();
                $this->services[$row->service_id] = $service;
            }

            if ( !isset($this->staff[$row->staff_id]) ) {
                $employee = new stdClass();
                $employee->id = $row->staff_id;
                $employee->name = $row->staff_name;
                $employee->category_id = $row->category_id;
                $employee->service_id = $row->service_id;
                $this->staff[$row->staff_id] = $employee;
            }

            if ( $row->category_id && !isset($this->categories[ $row->category_id ]) ) {
                $category = new stdClass();
                $category->id = $row->category_id;
                $category->name = $row->category_name;
                $category->services = array();
                $this->categories[$row->category_id] = $category;
            } else if ( !$row->category_id && !isset($this->categories[ 0 ]) ) {
                $category = new stdClass();
                $category->id = 0;
                $category->name = __( 'Uncategorized' );
                $category->services = array();
                $this->categories[0] = $category;
            }

            if ( !isset($this->services[$row->service_id]->staff[$row->staff_id]) ) {
                $employee = clone $this->staff[$row->staff_id];
                $employee->categories = null;
                $employee->services = null;
                if (get_option( 'ab_settings_pay_locally' )){
                    $employee->name .= ' (' . AB_CommonUtils::formatPrice( $row->price ) . ')' ;
                }
                $this->services[$row->service_id]->staff[$row->staff_id] = $employee;
            }

            if ( !isset($this->staff[$row->staff_id]->services[$row->service_id]) ) {
                $service = clone $this->services[$row->service_id];
                $service->staff = null;
                $this->staff[$row->staff_id]->services[$row->service_id] = $service;
            }

            if ( !isset($this->staff[$row->staff_id]->categories[(int)$row->category_id]) ) {
                $category = clone $this->categories[(int)$row->category_id];
                $category->services = null;
                $category->staff = null;
                $this->staff[$row->staff_id]->categories[(int)$row->category_id] = clone $category;
            }

            if ( !isset($this->categories[(int)$row->category_id]->staff[$row->staff_id]) ) {
                $employee = clone $this->staff[$row->staff_id];
                $employee->categories = null;
                $employee->services = null;
                $this->categories[(int)$row->category_id]->staff[$row->staff_id] = $employee;
            }

            if ( !isset($this->categories[(int)$row->category_id]->services[$row->service_id]) ) {
                $service = clone $this->services[$row->service_id];
                $service->staff = null;
                $this->categories[(int)$row->category_id]->services[$row->service_id] = $service;
            }
        }
    }

    /**
     * Fetches ids of the available days + the available time range
     * For the 1st step of the booking wizard
     *
     * @return array
     */
    public function fetchAvailableWorkDaysAndTime()
    {
        /** @var wpdb $wpdb */
        global $wpdb;
        $data = $wpdb->get_row( '
            SELECT
              GROUP_CONCAT(
                DISTINCT si.id
                ORDER BY si.id
              ) AS available_day_ids,
              SUBSTRING_INDEX(MIN(ssi.start_time), \':\', 2) AS min_start_time,
              SUBSTRING_INDEX(MAX(ssi.end_time), \':\', 2) AS max_end_time
            FROM ab_schedule_item si
            INNER JOIN ab_staff_schedule_item ssi ON ssi.schedule_item_id = si.id
            WHERE ssi.start_time IS NOT NULL
        ' );
        $result = array(
            'available_days' => array(),
            'time_range'     => array()
        );

        if ($data !== null) {
            if ( $data->available_day_ids ) {
                $wp_week_start_day  = get_option( 'start_of_week', 1 );
                $available_day_ids = explode( ',', $data->available_day_ids );
                $week_days         = array(
                    1 => __( 'Sun', 'ab' ),
                    2 => __( 'Mon', 'ab' ),
                    3 => __( 'Tue', 'ab' ),
                    4 => __( 'Wed', 'ab' ),
                    5 => __( 'Thu', 'ab' ),
                    6 => __( 'Fri', 'ab' ),
                    7 => __( 'Sat', 'ab' )
                );

                if( $wp_week_start_day > $available_day_ids[0] - 1) {
                    $list_start = array_slice( $week_days, $wp_week_start_day, 7, TRUE );
                    $list_end   = array_slice( $week_days, 0, $wp_week_start_day, TRUE );
                    $week_days  = $list_start + $list_end;
                }

                foreach ( $week_days as $day_id => $day_name ) {
                    if ( in_array( $day_id, $available_day_ids ) ) {
                        $result['available_days'][$day_id] = $day_name;
                    }
                }
            }

            if ( $data->min_start_time && $data->max_end_time ) {
                $start_timestamp = strtotime( sprintf( "1970-01-01 %s", $data->min_start_time ) );
                $end_timestamp   = strtotime( sprintf( "1970-01-01 %s", $data->max_end_time ) );
                $now_timestamp   = $start_timestamp;
                $now_timestamp_print   = $start_timestamp;
                $end_timestamp_print   = $end_timestamp;

                if ($this->client_timezone_offset !== false){
                    $now_timestamp_print -= ($this->client_timezone_offset + get_option( 'gmt_offset' )) * 3600;
                    $end_timestamp_print -= ($this->client_timezone_offset + get_option( 'gmt_offset' )) * 3600;
                }

                $time_format = get_option( 'time_format' );
                while ( $now_timestamp <= $end_timestamp ) {
                    $result['time_range'][date( 'H:i', $now_timestamp )] = date_i18n( $time_format, $now_timestamp_print );
                    // The next value will be rounded to integer number of hours, i.e. e.g. 8:00, 9:00, 10:00 and so on.
                    $now_timestamp = $this->roundTime(strtotime( '+30 minutes', $now_timestamp ));
                    $now_timestamp_print = $this->roundTime(strtotime( '+30 minutes', $now_timestamp_print ));
                }
                // The last value should always be the end time.
                $result['time_range'][date( 'H:i', $end_timestamp )] = date_i18n( $time_format, $end_timestamp_print );
            }
        }

        return $result;
    }

    /**
     * @param $timestamp
     * @param int $precision
     * @return float
     */
    private function roundTime( $timestamp, $precision = 60 ) {
        $precision = 60 * $precision;
        return round($timestamp / $precision ) * $precision;
    }

    public function getCategoriesJson() {
        return json_encode($this->categories);
    }

    public function getServicesJson() {
        return json_encode($this->services);
    }

    public function getStaffJson() {
        return json_encode($this->staff);
    }

    public function getCategories() {
        return $this->categories;
    }

    public function getServices() {
        return $this->services;
    }

    public function getStaff() {
        return $this->staff;
    }

    public function setClientTimeZoneOffset($client_timezone_offset){
        $this->client_timezone_offset = $client_timezone_offset;
    }
}
