<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'lib/AB_UserBookingData.php';
include 'lib/AB_AvailableTime.php';

class AB_BookingController extends AB_Controller {

    public function renderShortCode( $attributes ) {
        global $post;
        $booking = new AB_ServiceBooking();
        $booking->load();

        $userData = new AB_UserBookingData( $post->ID );
        $userData->load();

        $this->attributes = json_encode($attributes);
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
            $booking = new AB_ServiceBooking();
            $booking->load();

            if (get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' )){
                $booking->setClientTimeZoneOffset($this->getParameter( 'client_time_zone_offset' ) / 60);
            }

            $this->work_day_time_data = $booking->fetchAvailableWorkDaysAndTime();
            $this->userData = new AB_UserBookingData( $form_id );
            $this->userData->load();
            $this->progressTracker( 1 );
            $this->info_text = nl2br( esc_html( get_option( 'ab_appearance_text_info_first_step' ) ) );
            echo json_encode( array(
                'html'       => $this->render( 'service', array(), false ),
                'categories' => $booking->getCategories(),
                'staff'      => $booking->getStaff(),
                'services'   => $booking->getServices(),
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
        $params  = $this->getParameters();
        $errors  = array();
        if ( $form_id && $params ) {
            $userBookingData = new AB_UserBookingData( $form_id );
            $errors = $userBookingData->validate( $params );
            if ( empty ( $errors ) ) {
                $userBookingData->setData( $params );
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
            if ( isset( $_SESSION[ 'appointment_booking' ][ $form_id ] ) ) {
                $user_data = $_SESSION[ 'appointment_booking' ][ $form_id ];
            } elseif ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
                $tmp_booking_data = AB_CommonUtils::getTemporaryBookingData();

                if ( !empty( $tmp_booking_data ) ) {
                    $wpdb = $this->getWpdb();
                    $query = $wpdb->get_var( $wpdb->prepare( '
                    SELECT COUNT(aba.id) FROM ab_appointment as aba
                        WHERE aba.staff_id IN (' . implode( ', ', $tmp_booking_data[ 'staff_id' ] ) . ') AND aba.start_date = %s
                    ', $tmp_booking_data[ 'booked_datetime' ]
                    ));
                    $time_is_available = $query ? false : true;

                    // check if appointment's time is still available
                    if ( $time_is_available ) {
                        // save appointment to DB
                        $userBookingData = new AB_UserBookingData( $form_id );
                        $userBookingData->loadTemporaryForLocalPayment();
                        $userBookingData->save();
                    }
                }
            }


            if ( ! empty ( $user_data ) ) {
                $wpdb = $this->getWpdb();
                $query = $wpdb->get_var( $wpdb->prepare( '
                    SELECT COUNT(aba.id) FROM ab_appointment as aba
                        WHERE aba.staff_id IN (' . implode( ', ', $user_data[ 'staff_id' ] ) . ') AND aba.start_date = %s
                    ', $user_data[ 'booked_datetime' ]
                ));
                $time_is_available = $query ? false : true;

                // check if appointment's time is still available
                if ( $time_is_available ) {
                    // save appointment to DB
                    $userBookingData = new AB_UserBookingData( $form_id );
                    $userBookingData->load();
                    if (get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' )){
                        $userBookingData->setClientTimeOffset($this->getParameter( 'client_time_zone_offset' ) / 60 + get_option( 'gmt_offset' ));
                    }
                    $userBookingData->save();
                }
            } // if
        } /*else $time_is_available = true;*/ // if
        echo json_encode ( array ( 'state' => $time_is_available ) );
        exit;
}

    public function executeRenderTime() {
        $form_id = $this->getParameter( 'form_id' );

        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $availableTime = new AB_AvailableTime();
                $availableTime->setUserData( $userData );

                if ( get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' )) {
                    $availableTime->setClientTimeZoneOffset($this->getParameter('client_time_zone_offset'));
                }

                $availableTime->load();
                $this->time = $availableTime->getTime();
                $this->progressTracker( 2 );
                $this->injectServiceData( $this->getParameter( 'form_id' ), 2 );

                if ( count( $this->time ) ) {
                    $this->render( 'time' );
                } else {
                    $this->progressTracker( 2 );
                    $progress_tracker = intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ?
                        $this->progress_tracker : "";
                    echo json_encode( array(
                        "error" => __( "<h3>No time is available for selected criteria.</h3>", "ab" ),
                        "progress_tracker" => $progress_tracker,
                        "back_btn"  => '<a href="javascript:void(0)" class="ab-time-no-resize ab-left ab-to-first-step"
                            style="margin-right: 10px;">' . __( "Back", "ab" ) . '</a>'
                    ) );
                }
            }
        }
        exit;
    }

    /**
     * render Progress Tracker for Backend Appearance
     */
    public function executeRenderProgressTracker( ) {
        $booking_step = $this->getParameter( 'booking_step' );

        if ( $booking_step ) {
            $this->progressTracker( $booking_step );

            echo json_encode( array(
                'html' => $this->progress_tracker
            ) );
        }
        exit;
    }

    /**
     * Displays current step of booking
     *
     * @param int $booking_step
     * @return string $this->progress_tracker
     */
    public function progressTracker( $booking_step ) {
        $payment_disabled = get_option( 'ab_settings_pay_locally' ) == 0;
        $progress_tracker_type_option = '';
        $progress_tracker_type_class = '';

        switch ( get_option( 'ab_appearance_progress_tracker_type' ) ) {
            case 'Standard':
                $progress_tracker_type_option = 'Standard';
                $progress_tracker_type_class = 'nav-3';
                break;
        }

        $booking_steps = array(
            'first_step'  => esc_html(get_option( 'ab_appearance_text_step_service' )),
            'second_step' => esc_html(get_option( 'ab_appearance_text_step_time' )),
            'third_step'  => esc_html(get_option( 'ab_appearance_text_step_details' )),
            'fourth_step' => esc_html(get_option( 'ab_appearance_text_step_payment' )),
            'fifth_step'  => esc_html(get_option( 'ab_appearance_text_step_done' ))
        );

        // current step teaser is only for Standard Progress Tracker
        if ( $progress_tracker_type_option == 'Standard' ) {
            switch ( $booking_step ) {
                case 1 :
                    $current_step_teaser = '
                        <li class="ab-step-tabs first active">
                            <a href="javascript:void(0)">'. $booking_steps[ 'first_step' ] .'</a>
                            <div class="step"></div>
                        </li>';
                    break;
                case 2 :
                    $current_step_teaser = '
                        <li class="ab-step-tabs active">
                            <a href="javascript:void(0)">'. $booking_steps[ 'second_step' ] .'</a>
                            <div class="step"></div>
                        </li>';
                    break;
                case 3 :
                    $current_step_teaser = '
                        <li class="ab-step-tabs active">
                            <a href="javascript:void(0)">'. $booking_steps[ 'third_step' ] .'</a>
                            <div class="step"></div>
                        </li>';
                    break;
                case 4 :
                    $current_step_teaser = '
                        <li class="ab-step-tabs active">
                            <a href="javascript:void(0)">'. $booking_steps[ 'fourth_step' ] .'</a>
                            <div class="step"></div>
                        </li>';
                    break;
                case 5 :
                    $current_step_teaser = '
                        <li class="ab-step-tabs last active">
                            <a href="javascript:void(0)">'. $booking_steps[ 'fifth_step' ] .'</a>
                            <div class="step"></div>
                        </li>';
                    break;
            }
        }

        // booking steps numbers depends on payment's inclusion
        if ( $payment_disabled ) {
            $this->payment_disabled = true;
            if ( $progress_tracker_type_option == 'Standard' ) {
                switch ( $booking_step ) {
                    case 1 :
                        $this->progress_tracker = '
                            <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                            </ul>';
                        break;
                    case 2 :
                        $this->progress_tracker = '
                           <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs second active">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                           </ul> ';
                        break;
                    case 3 :
                        $this->progress_tracker = '
                           <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs active">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs third active">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                           </ul> ';
                        break;
                    case 4 :
                        $this->progress_tracker = '';
                        break;
                    case 5 :
                        $this->progress_tracker = '
                            <div class="ab-progress-tracker ab-progress-tracker-four-steps">
                                <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                    <li class="ab-step-tabs first active">
                                        <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs active">
                                        <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs active">
                                        <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs last fifth active">
                                        <a href="javascript:void(0)">4. '. $booking_steps[ 'fifth_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                </ul>
                            </div>';
                        break;
                }
            }
        } else { // payment enabled
            $this->payment_disabled = false;
            if ( $progress_tracker_type_option == 'Standard' ) {
                switch ( $booking_step ) {
                    case 1 :
                        $this->progress_tracker = '
                            <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fourth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">5. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                            </ul>';
                        break;
                    case 2 :
                        $this->progress_tracker = '
                            <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs second active">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fourth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">5. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                            </ul>';
                        break;
                    case 3 :
                        $this->progress_tracker = '
                            <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs active">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs third active">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fourth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">5. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                            </ul>';
                        break;
                    case 4 :
                        $this->progress_tracker = '
                            <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                <li class="ab-step-tabs first active">
                                    <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs active">
                                    <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs active">
                                    <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs fourth active">
                                    <a href="javascript:void(0)">4. '. $booking_steps[ 'fourth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                                <li class="ab-step-tabs last">
                                    <a href="javascript:void(0)">5. '. $booking_steps[ 'fifth_step' ] .'</a>
                                    <div class="step"></div>
                                </li>
                            </ul>';
                        break;
                    case 5 :
                        $this->progress_tracker = '
                            <div class="ab-progress-tracker">
                                <ul class="ab-progress-bar '. $progress_tracker_type_class .'">
                                    <li class="ab-step-tabs first active">
                                        <a href="javascript:void(0)">1. '. $booking_steps[ 'first_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs active">
                                        <a href="javascript:void(0)">2. '. $booking_steps[ 'second_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs active">
                                        <a href="javascript:void(0)">3. '. $booking_steps[ 'third_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs active">
                                        <a href="javascript:void(0)">4. '. $booking_steps[ 'fourth_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                    <li class="ab-step-tabs fifth active last">
                                        <a href="javascript:void(0)">5. '. $booking_steps[ 'fifth_step' ] .'</a>
                                        <div class="step"></div>
                                    </li>
                                </ul>
                            </div>';
                        break;
                }
            }
        }
    }

    /**
     * Injects additional Data of Service to rendering template for special booking-step
     *
     * @param string $form_id
     * @param int $booking_step
     */
    public function injectServiceData( $form_id, $booking_step ) {
        $userData = new AB_UserBookingData( $form_id );
        $userData->load();

        if ( $userData->hasData() ) {
            $this->info_text = '';
            $service_name = count ($userData->getServiceNameById( $userData->getServiceId() ) ) ?
                $userData->getServiceNameById( $userData->getServiceId() ) : '';
            $staff_name = $userData->getStaffId() == 0 ?
                '' : $userData->getStaffNameById( $userData->getStaffId() );
            $price = count( $userData->getServicePriceByStaffId( $userData->getServiceId(), $userData->getStaffId() ) ) ?
                    $userData->getServicePriceByStaffId( $userData->getServiceId(), $userData->getStaffId() ) :
                    $userData->getServicePriceById( $userData->getServiceId() );

            // Convenient Time
            if ( $booking_step === 2 ) {
                $_SESSION[ 'appointment_booking' ][ $form_id ][ 'staff_id_from_first_step' ] = $userData->getStaffId();
                // staff was selected
                if ( is_object( $staff_name ) && is_object( $service_name ) ) {
                    $replacement = array(
                        '[[STAFF_NAME]]'   => '<b>' . $staff_name->staff_name . '</b>',
                        '[[SERVICE_NAME]]' => '<b>' . $service_name->service_name . '</b>',
                        // backward compatibility
                        '[[BY_SERVICE_PROVIDER_NAME]]' => '<b>' . $staff_name->staff_name . '</b>',
                    );
                } else if ( is_object( $service_name ) ) {
                    $replacement = array(
                        '[[STAFF_NAME]]'   => '<b>' . __( 'Any', 'ab' ) . '</b>',
                        '[[SERVICE_NAME]]' => '<b>' . $service_name->service_name . '</b>',
                        // backward compatibility
                        '[[BY_SERVICE_PROVIDER_NAME]]' => '<b>' . __( 'Any', 'ab' ) . '</b>',
                    );
                }
                $this->info_text = str_replace( array_keys( $replacement ), array_values( $replacement ),
                    nl2br( esc_html( get_option( 'ab_appearance_text_info_second_step' ) ) )
                );
            }

            // Your Details
            if ( $booking_step === 3 ) {
                $staff_name = $_SESSION[ 'appointment_booking' ][ $form_id ][ 'staff_id_from_first_step' ] == 0 ?
                    $staff_name : $userData->getStaffNameById( $userData->getStaffId() );

                if (get_option( 'ab_settings_use_client_time_zone' ) && $this->getParameter( 'client_time_zone_offset' ) ) {
                    $service_time = date_i18n( get_option( 'time_format' ), strtotime( $userData->getBookedDatetime() ) - (($this->getParameter('client_time_zone_offset') + get_option( 'gmt_offset' ) * 60) * 60));
                }else{
                    $service_time = date_i18n( get_option( 'time_format' ), strtotime( $userData->getBookedDatetime() ) );
                }
                $service_date = date_i18n( get_option( 'date_format' ), strtotime( $userData->getBookedDatetime() ) );

                // staff was selected
                if ( is_object( $staff_name ) && is_object( $service_name ) ) {
                    $replacement = array(
                        '[[STAFF_NAME]]'    => '<b>' . $staff_name->staff_name . '</b>',
                        '[[SERVICE_NAME]]'  => '<b>' . $service_name->service_name . '</b>',
                        '[[SERVICE_TIME]]'  => '<b>' . $service_time . '</b>',
                        '[[SERVICE_DATE]]'  => '<b>' . $service_date . '</b>',
                        '[[SERVICE_PRICE]]' => '<b>' . AB_CommonUtils::formatPrice( $price->service_price ) . '</b>',
                        // backward compatibility
                        '[[BY_STAFF_NAME]]' => '<b>' . $staff_name->staff_name . '</b>',
                    );
                } else if ( is_object( $service_name ) ) {
                    $replacement = array(
                        '[[STAFF_NAME]]'    => '<b>' . __( 'Any', 'ab' ) . '</b>',
                        '[[SERVICE_NAME]]'  => '<b>' . $service_name->service_name . '</b>',
                        '[[SERVICE_TIME]]'  => '<b>' . $service_time. '</b>',
                        '[[SERVICE_DATE]]'  => '<b>' . $service_date . '</b>',
                        '[[SERVICE_PRICE]]' => '<b>' . AB_CommonUtils::formatPrice( $price->service_price ) . '</b>',
                        // backward compatibility
                        '[[BY_STAFF_NAME]]' => '<b>' . $staff_name->staff_name . '</b>',
                    );
                }
                $this->info_text = str_replace( array_keys( $replacement ), array_values( $replacement ),
                    nl2br( esc_html( get_option( 'ab_appearance_text_info_third_step' ) ) )
                );
            }
        }
    }

    public function executeRenderNextTime() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $availableTime = new AB_AvailableTime();
                $availableTime->setStartDate( $this->getParameter( 'start_date' ) );
                $availableTime->setUserData( $userData );
                $availableTime->load();

                if ( count( $availableTime->getTime() ) ) { // check, if there are available time
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
                            echo $button;
                        }
                    }
                } else {
                    echo json_encode( array(
                        "error" => __( '<h3>The selected time is not available anymore. Please, choose another time slot.</h3>', 'ab' ) ) );
                }
             }
        }
        exit;
    }

    public function executeRenderYourDetails() {
        $form_id = $this->getParameter( 'form_id' );
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $this->userData = $userData;
                $this->injectServiceData( $this->getParameter( 'form_id' ), 3 );
                $this->progressTracker( 3 );
                $this->render( 'your_details' );
            }
        }
        exit;
    }

    public function executeRenderPayment() {
        $form_id = $this->getParameter( 'form_id' );
        $payment_disabled = get_option( 'ab_settings_pay_locally' ) == 0;
        if ( $form_id && !$payment_disabled ) {
            $this->form_id = $form_id;
            $this->info_text = nl2br( esc_html( get_option( 'ab_appearance_text_info_fourth_step' ) ) );
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( $userData->hasData() ) {
                $employee = new AB_Staff();
                $employee->load( $userData->getStaffId() );

                $service = new AB_Service();
                $service->load( $userData->getServiceId() );

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
                $this->progressTracker( 4 );
                $this->render( 'payment' );
            } elseif ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
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
                        $this->progressTracker( 4 );
	                    $error_msg = isset($_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error']) ? $_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error'] : "";
	                    unset($_SESSION[ 'appointment_booking' ][$tmp_form_id]['paypal_error']);
                        $this->render( 'payment', array( 'form_id' => $tmp_form_id, 'error_msg' =>  $error_msg) );
                    }
                }

            }
        }
        exit;
    }

    public function executeRenderComplete() {
        $state = array (
            'success' => nl2br( esc_html( get_option( 'ab_appearance_text_info_fifth_step' ) ) ),
            'error' =>  __( '<h3>The selected time is not available anymore. Please, choose another time slot.</h3>', 'ab' )
        );

        $this->progress_tracker = intval( get_option( 'ab_appearance_show_progress_tracker' ) == 1 ) ? true : false;

        // Show Progress Tracker if enabled in settings
        if ( $this->progress_tracker ) {
            $this->progressTracker( 5 );
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
     * Cancel Appointment.
     */
    public function executeCancelAppointment() {
        $query = $this->getWpdb()->query($this->getWpdb()->prepare("DELETE FROM ab_appointment WHERE token = %d",
            $this->getParameter( 'token' )));
        if ( $query ) {
            if (get_option( 'ab_settings_cancel_page_url' )){
                exit(wp_redirect(get_option( 'ab_settings_cancel_page_url' )));
            }
        }
        exit(wp_redirect(home_url()));
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