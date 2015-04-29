<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'lib/AB_UserBookingData.php';
include 'lib/AB_AvailableTime.php';

/**
 * Class AB_BookingController
 *
 * @Security anonymous
 */
class AB_BookingController extends AB_Controller {

    public function renderShortCode( $attributes ) {
        $this->form_id = uniqid();
        $userData = new AB_UserBookingData( $this->form_id );
        $userData->load();

        $this->attributes = json_encode(is_array( $attributes ) ? $attributes : array() );
        $this->booking_finished = $userData->getBookingFinished();
        $this->booking_cancelled = $userData->getBookingCancelled();

        if ( $this->booking_finished ) {
            $userData->clean();
        } elseif ( isset( $_COOKIE[ 'first_step' ] ) ) {
            $cookie_data = json_decode( stripslashes( $_COOKIE[ 'first_step' ] ) );
            $form_id = $cookie_data->form_id;

            if ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ],
                $_SESSION[ 'appointment_booking' ][ $form_id ][ 'finished' ] ) &&
                $_SESSION[ 'appointment_booking' ][ $form_id ][ 'finished' ] === true
            ) {
                $userData->clean();
                $this->booking_finished = true;
            }
        }

        if ( $this->booking_cancelled ) {
            $userData->clean();
        } elseif ( isset( $_COOKIE[ 'first_step' ] ) ) {
            $cookie_data = json_decode( stripslashes( $_COOKIE[ 'first_step' ] ) );
            $form_id = $cookie_data->form_id;

            if ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ],
                $_SESSION[ 'appointment_booking' ][ $form_id ][ 'cancelled' ] ) &&
                $_SESSION[ 'appointment_booking' ][ $form_id ][ 'cancelled' ] === true
            ) {
                $userData->clean();
                $this->booking_cancelled = true;

            }
        }

        return $this->render( 'short_code', array(), false );
    }

    public function executeRenderService() {
        $form_id = $this->getParameter( 'form_id' );

        if ( $form_id ) {
            $configuration = new AB_BookingConfiguration();

            if ( get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' ) ) {
                $configuration->setClientTimeZoneOffset( $this->getParameter( 'client_time_zone_offset' ) / 60 );
            }

            $this->work_day_time_data = $configuration->fetchAvailableWorkDaysAndTime();
            $this->userData = new AB_UserBookingData( $form_id );
            $this->userData->load();
            $this->_prepareProgressTracker( 1, $this->userData->getServicePrice() );
            $this->info_text = nl2br( esc_html( get_option( 'ab_appearance_text_info_first_step' ) ) );
            echo json_encode( array(
                'html'       => $this->render( 'service', array(), false ),
                'categories' => $configuration->getCategories(),
                'staff'      => $configuration->getStaff(),
                'services'   => $configuration->getServices(),
                'attributes' => $this->userData->hasData()
                    ? array(
                        'sid'   => $this->userData->getServiceId(),
                        'eid'   => $this->userData->getStaffId()
                    )
                    : null
            ) );
        }
        exit;
    }

    public function executeSessionSave() {
        $form_id = $this->getParameter( 'form_id' );
        $errors  = array();
        if ( $form_id ) {
            $userBookingData = new AB_UserBookingData( $form_id );
            $errors = $userBookingData->validate( $this->getParameters() );
            if ( empty ( $errors ) ) {
                $userBookingData->setData( $this->getParameters() );
            }
        }

        header( 'Content-Type: application/json' );
        echo json_encode( $errors );
        exit;
    }

    public function executeSaveAppointment() {
        $form_id = $this->getParameter( 'form_id' );
        $payment_type = $this->getParameter( 'payment_type' );
        $time_is_available = false;

        if ( $form_id ) { // save appointment only for Local Payment
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ] ) ) {
                $user_data = $_SESSION[ 'appointment_booking' ][ $form_id ];
            } elseif ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
                $tmp_booking_data = AB_CommonUtils::getTemporaryBookingData();

                if ( !empty( $tmp_booking_data ) ) {
                    // check if appointment's time is still available
                    if (!$this->findIntersections($tmp_booking_data['staff_id'], $tmp_booking_data['service_id'], $tmp_booking_data['booked_datetime'])) {
                        // save appointment to DB
                        $userBookingData = new AB_UserBookingData( $form_id );
                        $userBookingData->loadTemporaryForLocalPayment();
                        $userBookingData->save();
                        $time_is_available = true;
                    }
                }
            }

            if ( ! empty ( $user_data ) ) {
                // check if appointment's time is still available
                if ( !$this->findIntersections($userData->getStaffid(), $userData->getServiceId(), $userData->getBookedDatetime()) ) {
                    // save appointment to DB
                    $userBookingData = new AB_UserBookingData( $form_id );
                    $userBookingData->load();
                    if ( get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' ) ) {
                        $userBookingData->setClientTimeOffset( $this->getParameter( 'client_time_zone_offset' ) / 60 + get_option( 'gmt_offset' ) );
                    }
                    $userBookingData->save();
                    $time_is_available = true;
                }
            }
        }

        exit(json_encode(array('state' => $time_is_available)));
    }

    /**
     * Verify if user booking datetime is still available
     *
     * @param $staff_id int
     * @param $service_id int
     * @param $booked_datetime string
     *
     * @return mixed
     */
    public function findIntersections($staff_id, $service_id, $booked_datetime){
        $wpdb = $this->getWpdb();

        $requested_service = new AB_Service();
        $requested_service->load($service_id);

        $endDate = new DateTime($booked_datetime);
        $di = "+ {$requested_service->get( 'duration' )} sec";
        $endDate->modify( $di );

        $query = $wpdb->prepare(
            "SELECT `a`.*, `ss`.`capacity`, COUNT(*) AS `number_of_bookings`
                FROM `ab_customer_appointment` `ca`
                LEFT JOIN `ab_appointment` `a` ON `a`.`id` = `ca`.`appointment_id`
                LEFT JOIN `ab_staff_service` `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`
                WHERE `a`.`staff_id` = %d
                GROUP BY `a`.`start_date` , `a`.`staff_id` , `a`.`service_id`
                HAVING
                      (`a`.`start_date` = %s AND `service_id` =  %d and `number_of_bookings` >= `capacity`) OR
                      (`a`.`start_date` = %s AND `service_id` <> %d) OR
                      (`a`.`start_date` > %s AND `a`.`end_date` <= %s) OR
                      (`a`.`start_date` < %s  AND `a`.`end_date` > %s) OR
                      (`a`.`start_date` < %s  AND `a`.`end_date` > %s)
                ",
            $staff_id,
            $booked_datetime, $service_id,
            $booked_datetime, $service_id,
            $booked_datetime, $endDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'),
            $booked_datetime, $booked_datetime
        );

        return $wpdb->get_row($query);
    }

    /**
     * Render second step.
     *
     * @return string JSON
     */
    public function executeRenderTime() {
        $form_id = $this->getParameter( 'form_id' );

        $response = null;

        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $availableTime = new AB_AvailableTime( $userData );

                if ( get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' ) ) {
                    $availableTime->setClientTimeZoneOffset( $this->getParameter( 'client_time_zone_offset' ) );
                }

                $availableTime->load();
                $this->time = $availableTime->getTime();
                $this->_prepareProgressTracker( 2, $userData->getServicePrice() );
                $this->info_text = $this->_prepareInfoText( 2, $userData );

                // Set response.
                $response = array(
                    'status' => empty ( $this->time ) ? 'error' : 'success',
                    'html'   => $this->render( 'time', array(), false )
                );
            }
        }

        // Output JSON response.
        if ( $response === null ) {
            $response = array( 'status' => 'no-data' );
        }
        //header( 'Content-Type: application/json' );
        echo json_encode( $response );

        exit (0);
    }

    /**
     * render Progress Tracker for Backend Appearance
     */
    public function executeRenderProgressTracker( ) {
        $booking_step = $this->getParameter( 'booking_step' );

        if ( $booking_step ) {
            $this->_prepareProgressTracker( $booking_step );

            echo json_encode( array(
                'html' => $this->progress_tracker
            ) );
        }
        exit;
    }

    public function executeRenderNextTime() {
        $form_id = $this->getParameter( 'form_id' );

        $response = null;

        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $availableTime = new AB_AvailableTime( $userData );
                $availableTime->setStartDate( $this->getParameter( 'start_date' ) );
                $availableTime->load();

                if ( count( $availableTime->getTime() ) ) { // check, if there are available time
                    $html = '';
                    foreach ( $availableTime->getTime() as $date => $hours ) {
                        foreach ($hours as $object) {
                            $button = sprintf(
                                '<button data-date="%s" data-staff_id="%s" class="%s" value="%s">',
                                $object->is_day ? '' : $object->clean_date, $object->staff_id,
                                $object->is_day ? 'ab-available-day' : 'ab-available-hour ladda-button zoom-in',
                                $object->value
                            );
                            if ( !$object->is_day ) {
                                $button .= '<span class="ab_label"><i class="ab-hour-icon"><span></span></i>' . $object->label . '</span><span class="spinner"></span>';
                            } else {
                                $button .= $object->label . '</button>';
                            }
                            $html .= $button;
                        }
                    }
                    // Set response.
                    $response = array(
                        'status' => 'success',
                        'html'   => $html
                    );
                }
                else {
                    // Set response.
                    $response = array(
                        'status' => 'error',
                        'html'   => sprintf(
                            '<h3>%s</h3>',
                            __( 'The selected time is not available anymore. Please, choose another time slot.', 'ab' )
                        )
                    );
                }
             }
        }

        // Output JSON response.
        if ( $response === null ) {
            $response = array( 'status' => 'no-data' );
        }
        header( 'Content-Type: application/json' );
        echo json_encode( $response );

        exit (0);
    }

    public function executeRenderYourDetails() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $this->userData = $userData;
                $this->info_text = $this->_prepareInfoText( 3, $userData );
                $this->_prepareProgressTracker( 3, $userData->getServicePrice() );
                $this->render( 'your_details' );
            }
        }
        exit;
    }

    public function executeRenderPayment() {
        $form_id = $this->getParameter( 'form_id' );

        $response = null;

        if ( $form_id ) {
            $payment_disabled = AB_BookingConfiguration::isPaymentDisabled();

            $this->userData = new AB_UserBookingData( $form_id );
            $this->userData->load();
            if ($this->userData->hasData()) {
                if ($this->userData->getServicePrice() <= 0) {
                    $payment_disabled = true;
                }
            }

            if ( $payment_disabled == false ) {
                $this->form_id = $form_id;
                $this->info_text = nl2br( esc_html( get_option( 'ab_appearance_text_info_fourth_step' ) ) );
                $this->info_text_coupon = $this->_prepareInfoText(4, $this->userData);

                if ( $this->userData->hasData() ) {
                    $employee = new AB_Staff();
                    $employee->load( $this->userData->getStaffId() );

                    $service = new AB_Service();
                    $service->load( $this->userData->getServiceId() );

                    $price = $this->getWpdb()->get_var( $this->getWpdb()->prepare( '
                        SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
                            $employee->get( 'id' ), $service->get( 'id' )
                    ) );

                    $this->_prepareProgressTracker( 4, $price );

                    // Set response.
                    $response = array(
                        'status' => 'success',
                        'html'   => $this->render( 'payment', array(), false )
                    );
                }
                else if ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
                    $tmp_booking_data = AB_CommonUtils::getTemporaryBookingData();

                    if ( !empty( $tmp_booking_data ) ) {
                        $tmp_form_id = $tmp_booking_data[ 'form_id' ];
                        if ( isset( $_SESSION[ 'appointment_booking' ][ $tmp_form_id ] ) &&
                            $_SESSION[ 'appointment_booking' ][ $tmp_form_id ][ 'cancelled' ] === true
                        ) {
                            $employee = new AB_Staff();
                            $employee->load( $tmp_booking_data[ 'staff_id' ][ 0 ] );

                            $service = new AB_Service();
                            $service->load( $tmp_booking_data[ 'service_id' ] );

                            $price = $this->getWpdb()->get_var( $this->getWpdb()->prepare( '
                                SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
                                    $employee->get( 'id' ), $service->get( 'id' )
                            ) );

                            // create a paypal object
                            $paypal = new PayPal();
                            $product = new stdClass();
                            $product->name  = $service->get( 'title' );
                            $product->desc  = $service->getTitleWithDuration();
                            $product->price = $price;
                            $product->qty   = 1;
                            $paypal->addProduct($product);

                            // get the products information from the $_POST and create the Product objects
                            $this->paypal = $paypal;
                            $this->_prepareProgressTracker( 4, $price );
                            $error_msg = isset($_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error']) ? $_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error'] : "";
                            unset($_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error']);

                            // Set response.
                            $response = array(
                                'status' => 'success',
                                'html'   => $this->render( 'payment', array( 'form_id' => $tmp_form_id, 'error_msg' =>  $error_msg), false )
                            );
                        }
                    }
                }
            }
        }

        // Output JSON response.
        if ( $response === null ) {
            $response = array( 'status' => 'no-data' );
        }
        header( 'Content-Type: application/json' );
        echo json_encode( $response );

        exit (0);
    }

    public function executeRenderComplete() {
        $state = array (
            'success' => nl2br( esc_html( get_option( 'ab_appearance_text_info_fifth_step' ) ) ),
            'error' =>  __( '<h3>The selected time is not available anymore. Please, choose another time slot.</h3>', 'ab' )
        );

        $this->progress_tracker = intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ? true : false;

        // Show Progress Tracker if enabled in settings
        if ( $this->progress_tracker ) {
            $price = null;
            if ($form_id  = $this->getParameter( 'form_id' ) ) {
                $userData = new AB_UserBookingData($form_id);
                $userData->load();

                $price = $userData->getServicePrice();
            }

            $this->_prepareProgressTracker( 5, $price );
            echo json_encode ( array (
                'state' => $state,
                'step'  => $this->progress_tracker
            ) );
        } else {
            echo json_encode ( array ( 'state' => $state ) );
        }

        if ( isset( $_SESSION[ 'appointment_booking' ] ) ) {
            unset( $_SESSION[ 'appointment_booking' ] );
        }
        if ( isset( $_SESSION[ 'ab_payment_total' ] ) ) {
            unset( $_SESSION[ 'ab_payment_total' ] );
        }
        if ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
            unset( $_SESSION[ 'tmp_booking_data' ] );
        }
        exit;
    }

    public function executeDestroyUserData() {
        session_destroy();
        if ( isset( $_COOKIE[ 'first_step' ] ) ) {
            unset( $_COOKIE[ 'first_step' ] );
            setcookie( 'first_step', '', time() - 3600 ); // empty value and old timestamp
        }
        exit;
    }

    /**
     * Cancel Appointment using token.
     */
    public function executeCancelAppointment() {
        $customer_appointment = new AB_Customer_Appointment();

        if ( $customer_appointment->loadByToken( $this->getParameter( 'token' ) ) ) {
            $customer_appointment->delete();

            // Delete appointment, if there aren't customers
            $current_capacity = $this->getWpdb()->get_var($this->getWpdb()->prepare('SELECT count(*) from `ab_customer_appointment` WHERE appointment_id = %d', $customer_appointment->get('appointment_id')));
            if (!$current_capacity){
                $appointment = new AB_Appointment();
                $appointment->load($customer_appointment->get('appointment_id'));
                $appointment->delete();
            }

            if (get_option( 'ab_settings_cancel_page_url' )){
                exit(wp_redirect(get_option( 'ab_settings_cancel_page_url' )));
            }
        }

        exit(wp_redirect(home_url()));
    }

    /**
     * Apply coupon
     */
    public function executeApplyCoupon(){
        $form_id = $this->getParameter( 'form_id' );
        $ab_coupon = $this->getParameter( 'ab_coupon' );

        $response = null;

        if (get_option('ab_settings_coupons') and $form_id) {
            $userData = new AB_UserBookingData($form_id);
            $userData->load();

            if ($userData->hasData()) {
                $price = $this->getWpdb()->get_var($this->getWpdb()->prepare(
                    'SELECT price FROM ab_staff_service WHERE staff_id = %d AND service_id = %d',
                    $userData->getStaffId(), $userData->getServiceId()
                ));

                if ($ab_coupon === ''){
                    $userData->setCoupon(NULL);
                    $response = array(
                        'status' => 'reset',
                        'text'   => $this->_prepareInfoText(4, $userData, $price)
                    );
                }
                else {
                    $discount = $this->getWpdb()->get_var($this->getWpdb()->prepare(
                        'SELECT `discount` FROM `ab_coupons` WHERE UPPER(`code`) = %s AND `used` = 0',
                        strtoupper($ab_coupon)
                    ));

                    if ($discount) {
                        $userData->setCoupon($ab_coupon);
                        $price -= $price * $discount / 100;
                        $response = array(
                            'status' => 'success',
                            'text'   => $this->_prepareInfoText(4, $userData, $price)
                        );
                    } else {
                        $userData->setCoupon(NULL);
                        $response = array(
                            'status' => 'error',
                            'error'  => __('* This coupon code is invalid or has been used', 'ab'),
                            'text'   => $this->_prepareInfoText(4, $userData, $price)
                        );
                    }
                }
            }
        }

        // Output JSON response.
        if ( $response === null ) {
            $response = array( 'status' => 'no-data' );
        }
        header( 'Content-Type: application/json' );
        echo json_encode( $response );

        exit (0);
    }

    /**
     * Render progress tracker into a variable.
     *
     * @param int $booking_step
     * @param int|null $price
     */
    private function _prepareProgressTracker( $booking_step, $price = null ) {
        $payment_disabled = (
            AB_BookingConfiguration::isPaymentDisabled()
            ||
            // If price is passed and it is zero then do not display payment step.
            $price !== null &&
            $price <= 0
        );

        $this->progress_tracker = $this->render( '_progress_tracker', array(
            'booking_step'     => $booking_step,
            'payment_disabled' => $payment_disabled
        ), false );
    }

    /**
     * Render info text into a variable.
     *
     * @param int $booking_step
     * @param AB_UserBookingData $userData
     * @param int $preset_price
     *
     * @return string
     */
    private function _prepareInfoText( $booking_step, $userData, $preset_price = null ) {
        if ($userData->hasData()) {
            $service_name = $userData->getServiceName();
            $category_name = $userData->getCategoryName();
            $staff_name = $userData->getStaffName();
            $price = ($preset_price === null)? $userData->getServicePrice() : $preset_price;

            // Convenient Time
            if ( $booking_step === 2 ) {
                $replacement = array(
                    '[[STAFF_NAME]]'   => '<b>' . $staff_name . '</b>',
                    '[[SERVICE_NAME]]' => '<b>' . $service_name . '</b>',
                    '[[CATEGORY_NAME]]' => '<b>' . $category_name . '</b>',
                );

                return str_replace( array_keys( $replacement ), array_values( $replacement ),
                    nl2br( esc_html( get_option( 'ab_appearance_text_info_second_step' ) ) )
                );
            }

            // Your Details
            if ( $booking_step === 3 ) {
                if ( get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' ) ) {
                    $service_time = date_i18n( get_option( 'time_format' ), strtotime( $userData->getBookedDatetime() ) - (($this->getParameter( 'client_time_zone_offset' ) + get_option( 'gmt_offset' ) * 60) * 60));
                }else{
                    $service_time = date_i18n( get_option( 'time_format' ), strtotime( $userData->getBookedDatetime() ) );
                }
                $service_date = date_i18n( get_option( 'date_format' ), strtotime( $userData->getBookedDatetime() ) );

                $replacement = array(
                    '[[STAFF_NAME]]'    => '<b>' . $staff_name . '</b>',
                    '[[SERVICE_NAME]]'  => '<b>' . $service_name . '</b>',
                    '[[CATEGORY_NAME]]' => '<b>' . $category_name . '</b>',
                    '[[SERVICE_TIME]]'  => '<b>' . $service_time . '</b>',
                    '[[SERVICE_DATE]]'  => '<b>' . $service_date . '</b>',
                    '[[SERVICE_PRICE]]' => '<b>' . AB_CommonUtils::formatPrice( $price ) . '</b>',
                );

                return str_replace( array_keys( $replacement ), array_values( $replacement ),
                    nl2br( esc_html( get_option( 'ab_appearance_text_info_third_step' ) ) )
                );
            }

            // Coupon Text
            if ($booking_step === 4) {
                $replacement = array(
                    '[[SERVICE_PRICE]]' => '<b>' . AB_CommonUtils::formatPrice($price) . '</b>',
                );

                return str_replace(array_keys($replacement), array_values($replacement),
                    nl2br(esc_html(get_option('ab_appearance_text_info_coupon')))
                );
            }
        }

        return '';
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }
}