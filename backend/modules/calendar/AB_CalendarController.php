<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_AppointmentForm.php';

/**
 * Class AB_CalendarController
 *
 * @property $collection
 * @property $staff_services
 * @property $startDate
 * @property $period_start
 * @property $period_end
 * @property $customers
 * @property $staff_id
 * @property $service_id
 * @property $customer_id
 * @property $staff_collection
 * @property $date_interval_not_available
 * @property $date_interval_warning
 * @property $notes
 *
 * @Security user
 */
class AB_CalendarController extends AB_Controller  {

    public function renderCalendar() {
        wp_enqueue_style( 'ab-jquery-ui-css', plugins_url( 'resources/css/jquery-ui-1.10.4/jquery-ui.min.css', __FILE__ ) );
        wp_enqueue_style( 'ab-weekcalendar', plugins_url( 'resources/css/jquery.weekcalendar.css', __FILE__ ) );
        wp_enqueue_style( 'ab-calendar', plugins_url( 'resources/css/calendar.css', __FILE__ ) );
        wp_enqueue_style( 'ab-chosen', plugins_url( 'resources/css/chosen.css', __FILE__ ) );

        wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', dirname( __DIR__ ) ) );
        wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', dirname( __DIR__ ) ) );
        wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', dirname( __DIR__ ) ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-date', plugins_url( 'resources/js/date.js', dirname( __DIR__ ) ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-chosen', plugins_url( 'resources/js/chosen.jquery.js', __FILE__ ) );

        wp_enqueue_script(
            'ab-weekcalendar',
            plugins_url( 'resources/js/jquery.weekcalendar.js', __FILE__ ),
            array(
                'jquery',
                'jquery-ui-widget',
                'jquery-ui-dialog',
                'jquery-ui-button',
                'jquery-ui-draggable',
                'jquery-ui-droppable',
                'jquery-ui-resizable',
                'jquery-ui-datepicker'
            )
        );
        wp_enqueue_script( 'ab-angularjs', plugins_url( 'resources/js/angular-1.0.6.min.js', dirname( __DIR__ ) ) );
        wp_enqueue_script( 'ab-angularui', plugins_url( 'resources/js/angular-ui-0.4.0.min.js', dirname( __DIR__ ) ), array( 'ab-angularjs' ) );
        wp_enqueue_script( 'ab-ng-app',  plugins_url( 'resources/js/ng-app.js', __FILE__ ), array( 'jquery', 'ab-angularjs', 'ab-angularui' ) );
        wp_enqueue_script( 'ab-ng-new_customer_dialog', plugins_url( 'resources/js/ng-new_customer_dialog.js', dirname( __DIR__ ) ), array( 'jquery', 'ab-angularjs' ) );
        wp_enqueue_script( 'ab-calendar_daypicker', plugins_url( 'resources/js/calendar_daypicker.js', __FILE__ ), array( 'jquery', 'ab-weekcalendar' ) );
        wp_enqueue_script( 'ab-calendar_weekpicker', plugins_url( 'resources/js/calendar_weekpicker.js', __FILE__ ), array( 'jquery', 'ab-weekcalendar' ) );
        wp_enqueue_script( 'ab-calendar', plugins_url( 'resources/js/calendar.js', __FILE__ ), array( 'jquery', 'ab-calendar_daypicker', 'ab-calendar_weekpicker', 'ab-ng-app', 'ab-weekcalendar' ) );
        wp_localize_script( 'ab-weekcalendar', 'BooklyL10n', array(
            'new_appointment'  => __( 'New appointment', 'ab' ),
            'edit_appointment' => __( 'Edit appointment', 'ab' ),
            'are_you_sure'     => __( 'Are you sure?', 'ab' ),
            'phone'            => __( 'Phone', 'ab' ),
            'email'            => __( 'Email', 'ab' ),
            'timeslotsPerHour'  => 60 / get_option('ab_settings_time_slot_length'),
            'shortMonths' => array(
                __( 'Jan', 'ab' ),
                __( 'Feb', 'ab' ),
                __( 'Mar', 'ab' ),
                __( 'Apr', 'ab' ),
                __( 'May', 'ab' ),
                __( 'Jun', 'ab' ),
                __( 'Jul', 'ab' ),
                __( 'Aug', 'ab' ),
                __( 'Sep', 'ab' ),
                __( 'Oct', 'ab' ),
                __( 'Nov', 'ab' ),
                __( 'Dec', 'ab' ),
            ),
            'longMonths' => array(
                __( 'January', 'ab' ),
                __( 'February', 'ab' ),
                __( 'March', 'ab' ),
                __( 'April', 'ab' ),
                __( 'May', 'ab' ),
                __( 'June', 'ab' ),
                __( 'July', 'ab' ),
                __( 'August', 'ab' ),
                __( 'September', 'ab' ),
                __( 'October', 'ab' ),
                __( 'November', 'ab' ),
                __( 'December', 'ab' )
            ),
            'shortDays' => array(
                __( 'Sun', 'ab' ),
                __( 'Mon', 'ab' ),
                __( 'Tue', 'ab' ),
                __( 'Wed', 'ab' ),
                __( 'Thu', 'ab' ),
                __( 'Fri', 'ab' ),
                __( 'Sat', 'ab' )
            ),
            'longDays' => array(
                __( 'Sunday', 'ab' ),
                __( 'Monday', 'ab' ),
                __( 'Tuesday', 'ab' ),
                __( 'Wednesday', 'ab' ),
                __( 'Thursday', 'ab' ),
                __( 'Friday', 'ab' ),
                __( 'Saturday', 'ab' ),
            ),
            'PM'         => __( 'PM', 'ab' ),
            'AM'         => __( 'AM', 'ab' ),
            'Week'       => __( 'Week', 'ab' ) . ': ',
            'dateFormat' => $this->dateFormatTojQueryUIDatePickerFormat(),
        ));

        if ( is_super_admin() ) {
            $this->collection = $this->getWpdb()->get_results( "SELECT * FROM ab_staff" );
        } else {
            $this->collection = $this->getWpdb()->get_results( $this->getWpdb()->prepare( "SELECT * FROM ab_staff s WHERE s.wp_user_id = %d", array(get_current_user_id()) ) );
        }

        $this->render( 'calendar' );
    }
    /**
     * Get data for WeekCalendar in `week` mode.
     *
     * @return json
     */
    public function executeWeekStaffAppointments() {
        $result = array( 'events' => array(), 'freebusys' => array() );
        $staff_id = $this->getParameter( 'staff_id' );
        if ( $staff_id ) {
            $staff = new AB_Staff();
            $staff->load( $staff_id );

            $start_date = $this->getParameter( 'start_date' );
            $end_date   = $this->getParameter( 'end_date' );

            $staff_appointments = $staff->getAppointments( $start_date, $end_date );
            foreach ( $staff_appointments as $appointment ) {
                $result['events'][] = $this->getAppointment( $appointment );
            }

            $wpdb     = $this->getWpdb();
            $schedule = $wpdb->get_results( $wpdb->prepare(
                'SELECT
                     ssi.*
                 FROM `ab_staff_schedule_item` ssi
                 WHERE ssi.staff_id = %d',
                $staff_id
            ) );

            $holidays = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ab_holiday WHERE staff_id = %d OR staff_id IS NULL', $staff_id ) );

            if ( ! empty( $schedule ) ) {
                $wp_week_start_day  = get_option( 'start_of_week', 1 );
                $schedule_start_day = $schedule[0]->id - 1;

                // if wp week start day is higher than our
                // cut the list into 2 parts (before and after wp wp week start day)
                // move the second part of the list above the first one
                if ( $wp_week_start_day > $schedule_start_day ) {
                    $schedule_start = array_slice( $schedule, 0, $wp_week_start_day );
                    $schedule_end   = array_slice( $schedule, $wp_week_start_day );
                    $schedule       = $schedule_end;

                    foreach ( $schedule_start as $schedule_item ) {
                        $schedule[] = $schedule_item;
                    }
                }

                $active_schedule_items_ids = array();

                foreach ( $schedule as $item ) {
                    // if start time is NULL we consider that the day is "OFF"
                    if ( null !== $item->start_time ) {
                        $day_name = AB_DateUtils::getWeekDayByNumber($item->day_index - 1);
                        if ($day_name == 'Sunday' && $wp_week_start_day == 0){
                            $date = date( 'Y-m-d', strtotime( $day_name . ' last week', strtotime( $start_date ) ) );
                        }else{
                            $date = date( 'Y-m-d', strtotime( $day_name . ' this week', strtotime( $start_date ) ) );
                        }
                        $startDate = new DateTime( $date . ' ' . $item->start_time );
                        $endDate   = new DateTime( $date . ' ' . $item->end_time );
                        // Skip holidays
                        foreach ( $holidays as $holiday ) {
                            $holidayDate = new DateTime($holiday->holiday);
                            if ( $holiday->repeat_event ) {
                                if ($holidayDate->format('m-d') == $startDate->format('m-d')) {
                                    continue 2;
                                }
                            } else {
                                if ($holidayDate->format('Y-m-d') == $startDate->format('Y-m-d')) {
                                    continue 2;
                                }
                            }
                        }

                        // get available day parts
                        $result['freebusys'][]       = $this->getFreeBusy( $startDate, $endDate, true );
                        $active_schedule_items_ids[] = $item->id;
                    }
                }

                if ( empty( $active_schedule_items_ids ) ) {
                    $active_schedule_items_ids = array( 0 );
                }

                $schedule_breaks = $wpdb->get_results(
                    'SELECT
                         sib.*,
                         ssi.day_index AS "day_index"
                     FROM `ab_schedule_item_break` sib
                     LEFT JOIN `ab_staff_schedule_item` ssi ON sib.staff_schedule_item_id = ssi.id
                     WHERE sib.staff_schedule_item_id IN (' . implode( ', ', $active_schedule_items_ids ) . ')'
                );

                foreach ( $schedule_breaks as $break_item ) {
                    $day_name  = AB_DateUtils::getWeekDayByNumber($break_item->day_index - 1);
                    $date      = date( 'Y-m-d', strtotime( $day_name . ' this week', strtotime( $start_date ) ) );
                    $startDate = new DateTime( $date . ' ' . $break_item->start_time );
                    $endDate   = new DateTime( $date . ' ' . $break_item->end_time );

                    // get breaks
                    $result['freebusys'][] = $this->getFreeBusy( $startDate, $endDate, false );
                }
            }
        }
        echo json_encode( $result );
        exit;
    }

    /**
     * Get data for WeekCalendar in `day` mode.
     *
     * @return json
     */
    public function executeDayStaffAppointments() {
        $result = array( 'events' => array(), 'freebusys' => array() );
        $staff_ids = $this->getParameter( 'staff_id' );
        if (is_array($staff_ids)) {
            $wpdb = $this->getWpdb();

            $start_date = $this->getParameter( 'start_date' );

            $appointments = $wpdb->get_results( sprintf(
                  'SELECT
                        a.id,
                        a.start_date,
                        a.end_date,
                        s.title,
                        s.color,
                        staff.id AS "staff_id",
                        staff.full_name AS "staff_fullname",
                        ss.capacity AS max_capacity,
                        COUNT( ca.id ) AS current_capacity,
                        ca.customer_id,
                        ca.notes AS notes
                    FROM ab_appointment a
                    LEFT JOIN ab_customer_appointment ca ON ca.appointment_id = a.id
                    LEFT JOIN ab_service s ON a.service_id = s.id
                    LEFT JOIN ab_staff staff ON a.staff_id = staff.id
                    LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
                    WHERE DATE(a.start_date) = DATE("%s") AND a.staff_id IN (%s)
                    GROUP BY a.id',
                  $wpdb->_real_escape($start_date),
                  implode(',', array_merge(array(0), array_map('intval', $staff_ids)))
              ) );

            foreach ( $appointments as $appointment ) {
                $result['events'][] = $this->getAppointment( $appointment, $appointment->staff_id, $day_view = true );
            }

            $day_index = date("N", strtotime($start_date)) + 1;
            $schedule  = $wpdb->get_results(
                'SELECT
                     ssi.*,
                     s.id AS "staff_id"
                 FROM `ab_staff_schedule_item` ssi
                 LEFT JOIN `ab_staff` s ON ssi.staff_id = s.id
                 WHERE ssi.day_index = "' . $day_index . '"
                 AND ssi.start_time IS NOT NULL'
            );

            $active_schedule_items_ids = array();

            foreach ( $schedule as $item ) {
                $startDate = new DateTime(date( 'Y-m-d', strtotime( $start_date ) ) . ' ' . $item->start_time);
                $endDate = new DateTime(date( 'Y-m-d', strtotime( $start_date ) ) . ' ' . $item->end_time);

                $holidays = $wpdb->get_results($wpdb->prepare(
                        'SELECT * FROM ab_holiday WHERE staff_id = %d and ((`repeat_event` = 0 and DATE_FORMAT( `holiday` , "%%Y-%%m-%%d" ) = %s) or (`repeat_event` = 1 and DATE_FORMAT( `holiday` , "%%Y-%%m" ) = %s))',
                        array($item->staff_id, $startDate->format('Y-m-d'), $startDate->format('m-d')))
                );
                if (!$holidays){
                    $result['freebusys'][] = $this->getFreeBusy( $startDate, $endDate, true, $item->staff_id );
                    $active_schedule_items_ids[] = $item->id;
                }
            }

            if ( empty($active_schedule_items_ids) ) {
                $active_schedule_items_ids = array( 0 );
            }

            $schedule_breaks = $wpdb->get_results(
                'SELECT
                     sib.*,
                     s.id AS "staff_id"
                 FROM `ab_schedule_item_break` sib
                 LEFT JOIN `ab_staff_schedule_item` ssi ON sib.staff_schedule_item_id = ssi.id
                 LEFT JOIN `ab_staff` s ON ssi.staff_id = s.id
                 WHERE sib.staff_schedule_item_id IN (' . implode( ', ', $active_schedule_items_ids ) . ')'
            );

            foreach ( $schedule_breaks as $break_item ) {
                $startDate = new DateTime(date( 'Y-m-d', strtotime( $start_date ) ) . ' ' . $break_item->start_time);
                $endDate = new DateTime(date( 'Y-m-d', strtotime( $start_date ) ) . ' ' . $break_item->end_time);

                $result['freebusys'][] = $this->getFreeBusy( $startDate, $endDate, false, $break_item->staff_id );
            }
        }
        echo json_encode( $result );
        exit;
    }

    /**
     * Get data needed for appointment form initialisation.
     */
    public function executeGetDataForAppointmentForm() {
        $wpdb    = $this->getWpdb();
        $result  = array(
            'staff'         => array(),
            'customers'     => array(),
            'time'          => array(),
            'time_interval' => get_option( 'ab_settings_time_slot_length' ) * 60
        );

        // Staff list.
        if ( is_super_admin() ) {
            $staff = $wpdb->get_results( 'SELECT `id`, `full_name` FROM `ab_staff`', ARRAY_A );
        } else {
            $staff = $wpdb->get_results( $wpdb->prepare(
                'SELECT `id`, `full_name` FROM `ab_staff` WHERE `wp_user_id` = %d',
                array( get_current_user_id() )
            ), ARRAY_A );
        }
        foreach ( $staff as $st ) {
            $services = $wpdb->get_results( $wpdb->prepare(
                'SELECT
                    `service`.`id`,
                    `service`.`title`,
                    `service`.`duration`
                FROM `ab_service` `service`
                LEFT JOIN `ab_staff_service` `ss` ON `ss`.`service_id` = `service`.`id`
                LEFT JOIN `ab_staff` `staff` ON `ss`.`staff_id` = `staff`.`id`
                WHERE `staff`.`id` = %d',
                $st[ 'id' ]
            ), ARRAY_A );
            array_walk($services, create_function('&$a', '$a[\'title\'] = sprintf(\'%s (%s)\', $a[\'title\'], AB_Service::durationToString($a[\'duration\']));'));
            $result[ 'staff' ][] = array(
                'id'        => $st[ 'id' ],
                'full_name' => $st[ 'full_name' ],
                'services'  => $services
            );
        }

        // Customers list.
        $customers = $this->getWpdb()->get_results(
            'SELECT * FROM `ab_customer` WHERE name <> "" OR phone <> "" OR email <> "" ORDER BY name',
            ARRAY_A
        );
        $customer = new AB_Customer();
        foreach ($customers as $customer_data) {
            $customer->setData( $customer_data );

            $name = $customer->get('name');
            if ($customer->get('email') && $customer->get('phone')){
                $name .= ' (' . $customer->get('email') . ', ' . $customer->get('phone') . ')';
            }elseif($customer->get('email')){
                $name .= ' (' . $customer->get('email') . ')';
            }elseif($customer->get('phone')){
                $name .= ' (' . $customer->get('phone') . ')';
            }

            $result[ 'customers' ][] = array(
                'id'   => $customer->get('id'),
                'name' => $name,
            );
        }

        // Time list.
        $tf         = get_option( 'time_format' );
        $ts_length  = get_option( 'ab_settings_time_slot_length' );
        $time_start = new AB_DateTime( AB_StaffScheduleItem::WORKING_START_TIME, new DateTimeZone( 'UTC' ) );
        $time_end   = new AB_DateTime( AB_StaffScheduleItem::WORKING_END_TIME, new DateTimeZone( 'UTC' ) );

        // Run the loop.
        while ( $time_start->format( 'U' ) <= $time_end->format( 'U' ) ) {
            $result[ 'time' ][ ] = array(
                'value' => $time_start->format( 'H:i' ),
                'title' => date_i18n( $tf, $time_start->format( 'U' ) )
            );
            $time_start->modify( '+' . $ts_length . ' min' );
        }

        echo json_encode( $result );
        exit (0);
    }

    /**
     * Get appointment data when editing the appointment.
     */
    public function executeGetDataForAppointment() {
        /**
         * @var WPDB $wpdb
         */
        global $wpdb;

        $response = array( 'status' => 'error', 'data' => array('customers' => array()) );

        $appointment = new AB_Appointment();
        if ( $appointment->load( $this->getParameter( 'id' ) ) ) {
            $response[ 'status' ] = 'ok';
            $response[ 'data' ][ 'service_id' ]  = $appointment->get( 'service_id' );

            $appointment_additional_info = $wpdb->get_row( $wpdb->prepare(
                'SELECT
                  ss.capacity AS max_capacity,
                  COUNT( ca.id ) AS current_capacity,
                  ca.notes AS notes
              FROM ab_appointment a
              LEFT JOIN ab_customer_appointment ca ON ca.appointment_id = a.id
              LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
              WHERE a.id = %d',
                $appointment->get('id')
            ) );

            $response[ 'data' ][ 'current_capacity' ] = $appointment_additional_info->current_capacity;
            $response[ 'data' ][ 'max_capacity' ] = $appointment_additional_info->max_capacity;
            $response[ 'data' ][ 'notes' ] = $appointment_additional_info->max_capacity == 1 && $appointment_additional_info->notes ? $appointment_additional_info->notes : null;

            $customers = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT ca.*
                     FROM ab_customer_appointment ca
                     LEFT JOIN ab_customer ac ON ac.id = ca.customer_id
                     WHERE ca.appointment_id = %d',
                    $appointment->get('id')
                )
            );

            foreach($customers as $customer){
                $response[ 'data' ][ 'customers' ][] = $customer->customer_id;
            }
        }

        exit ( json_encode( $response ) );
    }

    /**
     * Save appointment form (for both create and edit).
     */
    public function executeSaveAppointmentForm() {
        /**
         * @var WPDB $wpdb
         */
        global $wpdb;

        $response = array( 'status' => 'error' );

        $start_date     = date('Y-m-d H:i:s', strtotime( $this->getParameter('start_date' ) ) );
        $end_date       = date('Y-m-d H:i:s', strtotime( $this->getParameter( 'end_date' ) ) );
        $staff_id       = $this->getParameter( 'staff_id' );
        $service_id     = $this->getParameter( 'service_id', null );
        $appointment_id = $this->getParameter( 'id',  0 );
        $customers      = json_decode( $this->getParameter( 'customers', '[]' ) );
        $notes          = $this->getParameter( 'notes', '' );

        $staff_service = new AB_StaffService();
        $staff_service->loadByStaffAndService($staff_id, $service_id);

        // Check for errors.
        if ( !$this->dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id ) ) {
            $response[ 'errors' ] = array( 'date_interval_not_available' => true );
        }
        if (count($customers) > $staff_service->get('capacity')) {
            $response['errors']['overflow_capacity']         = true;
            $response['errors']['overflow_capacity_message'] = __( 'Number of customers should be not more than ', 'ab' ) . $staff_service->get('capacity');
        }

        // If no errors then try to save the appointment.
        if ( !isset ( $response[ 'errors' ] ) ) {
            $appointment = new AB_Appointment();
            if ( $appointment_id ) {
                // edit
                $appointment->load( $appointment_id );
            }
            $appointment->set('start_date',  $start_date);
            $appointment->set('end_date',    $end_date);
            $appointment->set('staff_id',    $staff_id );
            $appointment->set('service_id',  $service_id );

            if ( $appointment->save() !== false ) {
                // save customers
                $current_customers = $appointment->getCustomers();

                foreach (array_diff(array_keys($current_customers), $customers) as $el){
                    $wpdb->delete('ab_customer_appointment', array('appointment_id' => $appointment->get('id'), 'customer_id' => $el));
                }

                foreach (array_diff($customers, array_keys($current_customers)) as $el){
                    $customer_appointment = new AB_Customer_Appointment();
                    $customer_appointment->set('appointment_id', $appointment->get('id'));
                    $customer_appointment->set('customer_id', $el);

                    while (true) {
                        $token  = md5(uniqid(time(), true));
                        $result = $wpdb->get_row( $wpdb->prepare('SELECT * FROM `ab_customer_appointment` WHERE token = %s', $token));
                        if (!$result) {
                            break;
                        }
                    }
                    $customer_appointment->set('token', $token);
                    $customer_appointment->save();
                }

                $startDate = new DateTime( $appointment->get('start_date') );
                $endDate   = new DateTime( $appointment->get('end_date') );
                $staff = new AB_Staff();
                $staff->load( $staff_id );
                $service   = new AB_Service();
                $service->load( $service_id );
                $response[ 'status' ] = 'ok';
                $desc = array();

                $appointment_additional_info = $wpdb->get_row( $wpdb->prepare(
                    'SELECT
                      ss.capacity AS max_capacity,
                      COUNT( ca.id ) AS current_capacity,
                      ca.customer_id,
                      ca.notes,
                      ca.id AS ca_id
                  FROM ab_appointment a
                  LEFT JOIN ab_customer_appointment ca ON ca.appointment_id = a.id
                  LEFT JOIN ab_staff_service ss ON ss.staff_id = a.staff_id AND ss.service_id = a.service_id
                  WHERE a.id = %d',
                    $appointment->get('id')
                ) );

                if ($appointment_additional_info->max_capacity == 1){

                    // save notes
                    $customer_appointment = new AB_Customer_Appointment();
                    $customer_appointment->load($appointment_additional_info->ca_id);
                    $customer_appointment->set( 'notes', $notes );
                    $customer_appointment->save();

                    $customer = new AB_Customer();
                    $customer->load($appointment_additional_info->customer_id);
                    foreach ( array( 'name', 'phone', 'email' ) as $data_entry ) {
                        $entry_value = $customer->get( $data_entry );
                        if ( $entry_value ) {
                            $desc[] = '<div class="wc-employee">' . esc_html( $entry_value ) . '</div>';
                        }
                    }

                    $desc[] = '<div class="wc-notes">' . nl2br( esc_html($notes ?: $appointment_additional_info->notes ) ) . '</div>';
                }
                else {
                    // save notes
                    $customer_appointment = new AB_Customer_Appointment();
                    $customer_appointment->load($appointment_additional_info->ca_id);
                    $customer_appointment->set( 'notes', null );
                    $customer_appointment->save();

                    $desc[] = '<div class="wc-notes">Signed up ' . $appointment_additional_info->current_capacity . '</div>';
                    $desc[] = '<div class="wc-notes">Capacity ' . $appointment_additional_info->max_capacity . '</div>';
                }

                $response[ 'data' ]   = array(
                    'id'     => (int)$appointment->get( 'id' ),
                    'start'  => $startDate->format( 'm/d/Y H:i' ),
                    'end'    => $endDate->format( 'm/d/Y H:i' ),
                    'desc'   => implode('', $desc),
                    'title'  => $service->get( 'title' ) ? $service->get( 'title' ) : __( 'Untitled', 'ab' ),
                    'color'  => $service->get( 'color' ),
                    'userId' => (int)$appointment->get( 'staff_id' ),
                );

                // refresh data
                $current_customers = $appointment->getCustomers();

                if ( $this->getParameter( 'email_notification' ) === 'true' ) {
                    // Send email notification to client with appointment info
                    $client_notification = $wpdb->get_row( 'SELECT * FROM ab_notifications WHERE slug = "client_info" AND active = 1' );

                    // Send email notification to service provider with appointment info
                    $staff_notification = $wpdb->get_row( 'SELECT * FROM ab_notifications WHERE slug = "provider_info" AND active = 1' );

                    foreach ( $current_customers as $customer ) {
                        if ( $client_notification ) {
                            $replacement = new AB_NotificationReplacement();
                            $replacement->setClientName( $customer->name );
                            $replacement->setClientPhone( $customer->phone );
                            $replacement->setClientEmail( $customer->email );
//                            $replacement->setClientNotes( nl2br( esc_html( $notes ) ) );
                            $replacement->setAppointmentTime( $appointment->get('start_date') );
                            $replacement->setServiceName( $service->get( 'title' ) ? $service->get( 'title' ) : __( 'Untitled', 'ab' ) );
                            $replacement->setServicePrice( $staff_service->get( 'price' ) );
                            $replacement->setAppointmentToken( $customer->token );
                            $replacement->setStaffName( $staff->get( 'full_name' ) );
                            $message = wpautop( $replacement->replace( $client_notification->message ) );
                            $subject = $replacement->replaceSubject( $client_notification->subject );
                            wp_mail( $customer->email, $subject, $message, AB_CommonUtils::getEmailHeaderFrom() );
                        }

                        if ( $staff_notification ) {
                            $replacement = new AB_NotificationReplacement();
                            $replacement->setClientName( $customer->name );
                            $replacement->setClientPhone( $customer->phone );
                            $replacement->setClientEmail( $customer->email );
//                            $replacement->setClientNotes( nl2br( esc_html( $notes ) ) );
                            $replacement->setAppointmentTime( $appointment->get('start_date') );
                            $replacement->setServiceName( $service->get( 'title' ) ? $service->get( 'title' ) : __( 'Untitled', 'ab' ) );
                            $replacement->setServicePrice( $staff_service->get( 'price' ) );
                            $replacement->setAppointmentToken( $customer->token );
                            $replacement->setStaffName($staff->get( 'full_name' ) );
                            $message = wpautop( $replacement->replace( $staff_notification->message ) );
                            $subject = $replacement->replaceSubject( $staff_notification->subject );

                            // Send copy to administrators
                            if ( $staff_notification->copy ) {
                                $admin_emails = AB_CommonUtils::getAdminEmails();
                                if ( ! empty ( $admin_emails ) ) {
                                    wp_mail( $admin_emails, $subject, $message, AB_CommonUtils::getEmailHeaderFrom() );
                                }
                            }

                            wp_mail( $staff->get( 'email' ), $subject, $message, AB_CommonUtils::getEmailHeaderFrom() );
                        }
                    }
                }
            } else {
                $response[ 'errors' ] = array( 'unknown' => true );
            }
        }

        exit (json_encode($response));
    }

    public function executeCheckAppointmentDateSelection() {
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

            // service duration interval is not equal to
            $result['date_interval_warning'] = ($timestamp_diff != $duration);
        }

        echo json_encode( $result );
        exit;
    }

    public function executeCheckAppointmentMaxSelectedOptions(){
        $staff_id   = $this->getParameter( 'staff_id' );
        $service_id = $this->getParameter( 'service_id' );

        $staff_service = new AB_StaffService();
        $staff_service->loadByStaffAndService($staff_id, $service_id);

        echo json_encode( array('max_selected_options' => $staff_service->get('capacity')) );
        exit;
    }

    public function executeDeleteAppointment() {
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
    private function dateIntervalIsAvailableForAppointment( $start_date, $end_date, $staff_id, $appointment_id ) {
        return ! is_object( $this->getWpdb()->get_row( $this->getWpdb()->prepare(
            'SELECT * FROM `ab_appointment`
             WHERE (
                 start_date > %s AND start_date < %s
                 OR (end_date > %s AND end_date < %s)
                 OR (start_date < %s AND end_date > %s)
                 OR (start_date = %s OR end_date = %s)
             )
             AND staff_id = %d
             AND id <> %d',
            $start_date,
            $end_date,
            $start_date,
            $end_date,
            $start_date,
            $end_date,
            $start_date,
            $end_date,
            $staff_id,
            $appointment_id
        ) ) );
    }

    /**
     * @param $id
     *
     * @return AB_Customer
     */
    public function getCustomer( $id ) {
        $customer      = new AB_Customer();
        $customer_data = $this->getWpdb()->get_row( $this->getWpdb()->prepare(
            'SELECT * FROM `ab_customer` WHERE id = %d', $id
        ) );
        // populate customer with data
        if ( $customer_data ) {
            $customer->setData( $customer_data );
        }

        return $customer;
    }

    /**
     * Get appointment data
     *
     * @param stdClass     $appointment
     * @param null         $user_id
     * @param bool         $day_view
     *
     * @return array
     */
    private function getAppointment( stdClass $appointment, $user_id = null, $day_view = false ) {
        $startDate = new DateTime( $appointment->start_date );
        $endDate   = new DateTime( $appointment->end_date );
        $desc = array();

        if ($appointment->max_capacity == 1){
            $customer = new AB_Customer();
            $customer->load($appointment->customer_id);
            foreach ( array( 'name', 'phone', 'email' ) as $data_entry ) {
                $entry_value = $customer->get( $data_entry );
                if ( $entry_value ) {
                    $desc[] = '<div class="wc-employee">' . esc_html( $entry_value ) . '</div>';
                }
            }

            if ($appointment->notes) {
                $desc[] = '<div class="wc-notes">' . nl2br( esc_html( $appointment->notes ) ) . '</div>';
            }
        }else{
            $desc[] = '<div class="wc-notes">Signed up ' . $appointment->current_capacity . '</div>';
            $desc[] = '<div class="wc-notes">Capacity ' . $appointment->max_capacity . '</div>';
        }

        $appointment_data = array(
            'id'    => $appointment->id,
            'start' => $startDate->format( 'm/d/Y H:i' ),
            'end'   => $endDate->format( 'm/d/Y H:i' ),
            'title' => $appointment->title ? esc_html( $appointment->title ) : __( 'Untitled', 'ab' ),
            'desc'  => implode('', $desc),
            'color' => $appointment->color,
            'notes' => $appointment->max_capacity == 1 && $appointment->notes ? $appointment->notes : null
        );

        // if needed to be rendered for a specific user
        // pass the the user id
        if ( null !== $user_id ) {
            $appointment_data['userId'] = $user_id;
        }
        return $appointment_data;
    }

    /**
     * Get free busy data
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param          $free
     * @param null     $user_id
     *
     * @return array
     */
    private function getFreeBusy( DateTime $startDate, DateTime $endDate, $free, $user_id = null ) {
        $freebusy_data = array(
            'start' => $startDate->format( 'm/d/Y H:i' ),
            'end'   => $endDate->format( 'm/d/Y H:i' ),
            'free'  => $free
        );
        // if needed to be rendered for a specific user
        // pass the the user id
        if ( null !== $user_id ) {
            $freebusy_data['userId'] = $user_id;
        }
        return $freebusy_data;
    }

    /**
     * @return string
     */
    private function dateFormatTojQueryUIDatePickerFormat() {
        $chars = array(
            // Day
            'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
            // Month
            'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
            // Year
            'Y' => 'yy', 'y' => 'y',
        );

        return strtr((string)get_option('date_format'), $chars);
    }

     /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}
