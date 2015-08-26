<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_BookingController
 */
class AB_BookingController extends AB_Controller {

    private $replacement = array();
    protected function getPermissions()
    {
        return array(
          '_this' => 'anonymous',
        );
    }

    /**
     * Render Bookly shortcode.
     *
     * @param $attributes
     * @return string
     */
    public function renderShortCode( $attributes )
    {
        global $sitepress;

        $assets = '';

        if ( get_option( 'ab_settings_link_assets_method' ) == 'print' ) {
            $this->print_assets = ! wp_script_is( 'bookly', 'done' );
            if ( $this->print_assets ) {
                ob_start();

                // The styles and scripts are registered in AB_Frontend.php
                wp_print_styles( 'ab-reset' );
                wp_print_styles( 'ab-picker-date' );
                wp_print_styles( 'ab-picker-classic-date' );
                wp_print_styles( 'ab-picker' );
                wp_print_styles( 'ab-ladda-themeless' );
                wp_print_styles( 'ab-ladda-min' );
                wp_print_styles( 'ab-main' );
                wp_print_styles( 'ab-columnizer' );
                wp_print_styles( 'ab-intlTelInput' );

                wp_print_scripts( 'ab-spin' );
                wp_print_scripts( 'ab-ladda' );
                wp_print_scripts( 'ab-picker' );
                wp_print_scripts( 'ab-picker-date' );
                wp_print_scripts( 'ab-hammer' );
                wp_print_scripts( 'ab-jq-hammer' );
                wp_print_scripts( 'ab-intlTelInput' );
                // Android animation.
                if ( stripos( strtolower( $_SERVER['HTTP_USER_AGENT'] ), 'android' ) !== false ) {
                    wp_print_scripts( 'ab-jquery-animate-enhanced' );
                }
                wp_print_scripts( 'bookly' );

                $assets = ob_get_clean();
            }
        } else {
            $this->print_assets = true; // to print CSS in template.
        }

        // Find bookings with any of PayPal statuses.
        $this->booking_finished = $this->booking_cancelled = false;
        $this->form_id = uniqid();
        if ( isset ( $_SESSION['bookly'] ) ) {
            foreach ( $_SESSION['bookly'] as $form_id => $data ) {
                if ( isset( $data['paypal'] ) ) {
                    if ( ! isset ( $data['paypal']['processed'] ) ) {
                        switch ( $data['paypal']['status'] ) {
                            case 'success':
                                $this->form_id = $form_id;
                                $this->booking_finished = true;
                                break;
                            case 'cancelled':
                            case 'error':
                                $this->form_id = $form_id;
                                $this->booking_cancelled = true;
                                break;
                        }
                        // Mark this form as processed for cases when there are more than 1 booking form on the page.
                        $_SESSION['bookly'][ $form_id ]['paypal']['processed'] = true;
                    }
                } else {
                    unset ( $_SESSION['bookly'][ $form_id ] );
                }
            }
        }

        $this->attributes = json_encode( array(
            'hide_categories'       => @$attributes[ 'hide_categories' ]        ?: ( @$attributes[ 'ch' ]  ?: false ),
            'category_id'           => @$attributes[ 'category_id' ]            ?: ( @$attributes[ 'cid' ] ?: false ),
            'hide_services'         => @$attributes[ 'hide_services' ]          ?: ( @$attributes[ 'hs' ]  ?: false ),
            'service_id'            => @$attributes[ 'service_id' ]             ?: ( @$attributes[ 'sid' ] ?: false ),
            'hide_staff_members'    => @$attributes[ 'hide_staff_members' ]     ?: ( @$attributes[ 'he' ]  ?: false ),
            'staff_member_id'       => @$attributes[ 'staff_member_id' ]        ?: ( @$attributes[ 'eid' ] ?: false ),
            'hide_date_and_time'    => @$attributes[ 'hide_date_and_time' ]     ?: ( @$attributes[ 'ha' ]  ?: false ),
            'show_number_of_persons'=> @$attributes[ 'show_number_of_persons' ] ?: false,
        ) );

        // Prepare URL for AJAX requests.
        $this->ajax_url = admin_url( 'admin-ajax.php' );
        // Support WPML.
        if ( class_exists( 'SitePress' ) && $sitepress instanceof SitePress ) {
            switch ( $sitepress->get_setting( 'language_negotiation_type' ) ) {
                case 1: // url: /de             Different languages in directories.
                    $this->ajax_url .= '/'.$sitepress->get_current_language();
                    break;
                case 2: // url: example.de      A different domain per language. Not available for Multisite
                    break;
                case 3: // url: ?lang=de        Language name added as a parameter.
                    $this->ajax_url .= '?lang='.$sitepress->get_current_language();
                    break;
            }
        }

        return $assets . $this->render( 'short_code', array(), false );
    }

    /**
     * 1. Render first step.
     *
     * @return string JSON
     */
    public function executeRenderService()
    {
        $response = null;
        $form_id  = $this->getParameter( 'form_id' );

        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                $time_zone_offset = $this->getParameter( 'time_zone_offset' );
                $userData->saveData( array(
                    'time_zone_offset' => $time_zone_offset,
                    'date_from' => date( 'Y-m-d', current_time( 'timestamp' ) + AB_Config::getMinimumTimePriorBooking() - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $time_zone_offset * 60 ) )
                ) );
            }

            $this->_prepareProgressTracker( 1, $userData->getServicePrice() );
            $this->info_text = $this->_prepareInfoText( get_option( 'ab_appearance_text_info_first_step' ), $userData );

            $days_times = AB_Config::getDaysAndTimes( $userData->get( 'time_zone_offset' ) );
            // Prepare week days that need to be checked.
            $days_checked = $userData->get( 'days' );
            if ( empty( $days_checked ) ) {
                // Check all available days.
                $days_checked = array_keys( $days_times['days'] );
            }
            $bounding = AB_Config::getBoundingDaysForPickadate( $userData->get( 'time_zone_offset' ) );
            $casest   = AB_Config::getCaSeSt();

            $response = array(
                'success'    => true,
                'html'       => $this->render( '1_service', array(
                    'userData'     => $userData,
                    'days'         => $days_times['days'],
                    'times'        => $days_times['times'],
                    'days_checked' => $days_checked
                ), false ),
                'categories' => $casest['categories'],
                'staff'      => $casest['staff'],
                'services'   => $casest['services'],
                'date_max'   => $bounding['date_max'],
                'date_min'   => $bounding['date_min'],
                'attributes' => $userData->get( 'service_id' )
                    ? array(
                        'service_id'        => $userData->get( 'service_id' ),
                        'staff_member_id'   => $userData->getStaffId(),
                        'number_of_persons' => $userData->get( 'number_of_persons' ),
                    )
                    : null
            );
        } else {
            $response = array( 'success' => false, 'error' => __( 'Form ID error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 2. Render second step.
     *
     * @return string JSON
     */
    public function executeRenderTime()
    {
        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $availableTime = new AB_AvailableTime( $userData );
            if ( $this->hasParameter( 'selected_date' ) ) {
                $availableTime->setSelectedDate( $this->getParameter( 'selected_date' ) );
            } else {
                $availableTime->setSelectedDate( $userData->get( 'date_from' ) );
            }
            $availableTime->load();

            $this->_prepareProgressTracker( 2, $userData->getServicePrice() );
            $this->info_text = $this->_prepareInfoText( get_option( 'ab_appearance_text_info_second_step' ), $userData );

            // Render slots by groups (day or month).
            $slots = array();
            foreach ( $availableTime->getSlots() as $group => $group_slots ) {
                $slots[ $group ] = preg_replace( '/>\s+</', '><', $this->render( '_time_slots', array(
                     'group' => $group,
                     'slots' => $group_slots,
                     'is_whole_day_service' => $availableTime->isWholeDayService(),
                ), false ) );
            }

            // Set response.
            $response = array(
                'success'        => true,
                'has_slots'      => ! empty ( $slots ),
                'has_more_slots' => $availableTime->hasMoreSlots(),
                'day_one_column' => AB_Config::showDayPerColumn(),
                'slots'          => $slots,
                'html'           => $this->render( '2_time', array(
                    'date'      => AB_Config::showCalendar() ? $availableTime->getSelectedDateForPickadate() : null,
                    'has_slots' => ! empty ( $slots )
                ), false ),

            );
            if ( AB_Config::showCalendar() ) {
                $bounding = AB_Config::getBoundingDaysForPickadate( $userData->get( 'time_zone_offset' ) );
                $response['date_max'] = $bounding['date_max'];
                $response['date_min'] = $bounding['date_min'];
                $response['disabled_days'] = $availableTime->getDisabledDaysForPickadate();
            }
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 3. Render third step.
     *
     * @return string JSON
     */
    public function executeRenderDetails()
    {
        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            // Prepare custom fields data.
            $cf_data = array();
            $custom_fields = $userData->get( 'custom_fields' );
            if ( $custom_fields !== null ) {
                foreach ( json_decode( $custom_fields, true ) as $field ) {
                    $cf_data[ $field[ 'id' ] ] = $field[ 'value' ];
                }
            }

            $this->info_text = $this->_prepareInfoText( get_option( 'ab_appearance_text_info_third_step' ), $userData );
            $this->info_text_guest = ( get_current_user_id() == 0 ) ? $this->_prepareInfoText( get_option( 'ab_appearance_text_info_third_step_guest' ), $userData ) : '';
            $this->_prepareProgressTracker( 3, $userData->getServicePrice() );
            $response = array(
                'success' => true,
                'html'   => $this->render( '3_details', array(
                    'userData'      => $userData,
                    'custom_fields' => json_decode( get_option( 'ab_custom_fields' ) ),
                    'cf_data'       => $cf_data
                ), false )
            );
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 4. Render fourth step.
     *
     * @return string JSON
     */
    public function executeRenderPayment()
    {
        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $payment_disabled = AB_Config::isPaymentDisabled();

            if ( $userData->getServicePrice() <= 0 ) {
                $payment_disabled = true;
            }

            if ( $payment_disabled == false ) {
                $this->form_id   = $this->getParameter( 'form_id' );
                $this->info_text = $this->_prepareInfoText( get_option( 'ab_appearance_text_info_fourth_step' ), $userData );
                $this->info_text_coupon = $this->_prepareInfoText( get_option( 'ab_appearance_text_info_coupon' ), $userData );

                $service = $userData->getService();
                $price   = $userData->getFinalServicePrice();

                $this->_prepareProgressTracker( 4, $price );

                // Set response.
                $response = array(
                    'success'  => true,
                    'disabled' => false,
                    'html'     => $this->render( '4_payment', array(
                        'userData'      => $userData,
                        'paypal_status' => $userData->extractPayPalStatus()
                    ), false )
                );
            } else {
                $response = array(
                    'success'  => true,
                    'disabled' => true,
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * 5. Render fifth step.
     *
     * @return string JSON
     */
    public function executeRenderComplete()
    {
        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $this->_prepareProgressTracker( 5, $userData->getServicePrice() );
            $success_html = $this->progress_tracker . $this->_prepareInfoText( get_option( 'ab_appearance_text_info_fifth_step' ), $userData );

            $response = array (
                'success' => true,
                'html'   => array (
                    'success' => $success_html,
                    'error'   =>  __( '<h3>The selected time is not available anymore. Please, choose another time slot.</h3>', 'bookly' )
                ),
                'final_step_url' => get_option( 'ab_settings_final_step_url' )
            );
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Save booking data in session.
     */
    public function executeSessionSave()
    {
        $form_id = $this->getParameter( 'form_id' );
        $errors  = array();
        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();
            $errors = $userData->validate( $this->getParameters() );
            if ( empty ( $errors ) ) {
                $userData->saveData( $this->getParameters() );
            }
        }

        wp_send_json( $errors );
    }

    /**
     * Save appointment (final action).
     */
    public function executeSaveAppointment()
    {
        $form_id = $this->getParameter( 'form_id' );
        $time_is_available = false;

        if ( $form_id ) {
            $userData = new AB_UserBookingData( $form_id );
            $userData->load();

            if ( AB_Config::isPaymentDisabled() ||
                get_option( 'ab_settings_pay_locally' ) ||
                $userData->getFinalServicePrice() == 0
            ) {
                $availableTime = new AB_AvailableTime( $userData );
                // check if appointment's time is still available
                if ( $availableTime->checkBookingTime() ) {
                    $userData->save();
                    $time_is_available = true;
                }
            }
        }
        $time_is_available ? wp_send_json_success() : wp_send_json_error();
    }

    /**
     * render Progress Tracker for Backend Appearance
     */
    public function executeRenderProgressTracker( )
    {
        $booking_step = $this->getParameter( 'booking_step' );

        if ( $booking_step ) {
            $this->_prepareProgressTracker( $booking_step );

            wp_send_json( array(
                'html' => $this->progress_tracker
            ) );
        }
        exit;
    }

    public function executeRenderNextTime()
    {
        $response = null;
        $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );

        if ( $userData->load() ) {
            $availableTime = new AB_AvailableTime( $userData );
            $availableTime->setLastFetchedSlot( $this->getParameter( 'last_slot' ) );
            $availableTime->load();

            $html = '';
            foreach ( $availableTime->getSlots() as $group => $group_slots ) {
                $html .= $this->render( '_time_slots', array(
                    'group' => $group,
                    'slots' => $group_slots,
                    'is_whole_day_service' => $availableTime->isWholeDayService(),
                ), false );
            }

            // Set response.
            $response = array(
                'success'        => true,
                'html'           => preg_replace( '/>\s+</', '><', $html ),
                'has_slots'      => $html != '',
                'has_more_slots' => $availableTime->hasMoreSlots() // show/hide the next button
            );
        } else {
            $response = array( 'success' => false, 'error' => __( 'Session error.', 'bookly' ) );
        }

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Cancel Appointment using token.
     */
    public function executeCancelAppointment()
    {
        $customer_appointment = new AB_CustomerAppointment();

        if ( $customer_appointment->loadBy( array( 'token' => $this->getParameter( 'token' ) ) ) ) {

            $customer_appointment->delete();

            $appointment = new AB_Appointment();
            $appointment->load( $customer_appointment->get( 'appointment_id' ) );

            // Delete appointment, if there aren't customers.
            $count = AB_CustomerAppointment::query( 'ca' )->where( 'ca.appointment_id', $customer_appointment->get( 'appointment_id' ) )->count();

            if ( !$count ) {
                $appointment->delete();
            } else {
                $appointment->handleGoogleCalendar();
            }

            if ( $this->url = get_option( 'ab_settings_cancel_page_url' ) ) {
                wp_redirect( $this->url );
                $this->render( 'cancel_appointment' );

                exit ( 0 );
            }
        }

        $this->url = home_url();
        if ( isset ( $_SERVER['HTTP_REFERER'] ) ) {
            if ( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) == parse_url( $this->url, PHP_URL_HOST ) ) {
                // Redirect back if user came from our site.
                $this->url = $_SERVER['HTTP_REFERER'];
            }
        }
        wp_redirect( $this->url );
        $this->render( 'cancel_appointment' );

        exit ( 0 );
    }

    /**
     * Authentification WP_User in booking form
     */
    public function executeWpUserLogin()
    {
        /** @var WP_User $user */
        $user = wp_signon();
        if ( is_wp_error( $user ) ) {
            wp_send_json_error( array( 'message' => __( 'Incorrect username or password.' ) ) );
        } else {
            $customer = new AB_Customer();
            if ( $customer->loadBy( array( 'wp_user_id' => $user->ID ) ) ) {
                $user_info = array(
                    'name'  => $customer->get( 'name' ),
                    'email' => $customer->get( 'email' ),
                    'phone' => $customer->get( 'phone' )
                );
            } else {
                $user_info  = array(
                    'name'  =>  $user->display_name,
                    'email' =>  $user->user_email
                );
            }
            $userData = new AB_UserBookingData( $this->getParameter( 'form_id' ) );
            $userData->load();
            $userData->saveData( $user_info );
            wp_send_json_success( $user_info );
        }
    }

    /**
     * Get info for current IP
     */
    public function executeIpInfo()
    {
        $curl = new AB_Curl();
        $curl->options['CURLOPT_CONNECTTIMEOUT'] = 8;
        $curl->options['CURLOPT_TIMEOUT']        = 10;
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '';
        }
        @header( 'Content-Type: application/json; charset=UTF-8' );
        echo $curl->get( 'http://ipinfo.io/' . $ip .'/json' );
        wp_die();
    }

    /**
     * Render progress tracker into a variable.
     *
     * @param int $booking_step
     * @param int|bool $price
     */
    private function _prepareProgressTracker( $booking_step, $price = false )
    {
        if ( get_option( 'ab_appearance_show_progress_tracker' ) ) {
            $payment_disabled = (
                AB_Config::isPaymentDisabled()
                ||
                // If price is passed and it is zero then do not display payment step.
                $price !== false &&
                $price <= 0
            );

             $tracker = $this->render( '_progress_tracker', array(
                'booking_step'     => $booking_step,
                'payment_disabled' => $payment_disabled
            ), false );
        } else {
            $tracker = '';
        }
        $this->progress_tracker = $tracker;
    }

    /**
     * Render info text into a variable.
     *
     * @param string $text
     * @param AB_UserBookingData $userData
     * @param int $preset_price
     *
     * @return string
     */
    private function _prepareInfoText( $text, $userData, $preset_price = null )
    {
        if ( empty( $this->replacement ) ) {
            $service           = $userData->getService();
            $category_name     = $service->getCategoryName();
            $staff_name        = $userData->getStaffName();
            $price             = ( $preset_price === null ) ? $userData->getServicePrice() : $preset_price;
            $number_of_persons = $userData->get( 'number_of_persons' );
            $service_date      = AB_DateTimeUtils::formatDate( $userData->get( 'appointment_datetime' ) );
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                $service_time  = AB_DateTimeUtils::formatTime( AB_DateTimeUtils::applyTimeZoneOffset( $userData->get( 'appointment_datetime' ), $userData->get( 'time_zone_offset' ) ) );
            } else {
                $service_time  = AB_DateTimeUtils::formatTime( $userData->get( 'appointment_datetime' ) );
            }

            $this->replacement  = array(
                '[[STAFF_NAME]]'        => '<b>' . $staff_name . '</b>',
                '[[SERVICE_NAME]]'      => '<b>' . $service->get( 'title' ) . '</b>',
                '[[CATEGORY_NAME]]'     => '<b>' . $category_name . '</b>',
                '[[NUMBER_OF_PERSONS]]' => '<b>' . $number_of_persons . '</b>',
                '[[SERVICE_TIME]]'      => '<b>' . $service_time . '</b>',
                '[[SERVICE_DATE]]'      => '<b>' . $service_date . '</b>',
                '[[SERVICE_PRICE]]'     => '<b>' . AB_Utils::formatPrice( $price ) . '</b>',
                '[[TOTAL_PRICE]]'       => '<b>' . AB_Utils::formatPrice( $price * $number_of_persons ) . '</b>',
                '[[LOGIN_FORM]]'        => ( get_current_user_id() == 0 ) ? $this->render( '_login_form', array(), false ) : '',
            );
        }

        return strtr( nl2br( $text ), $this->replacement );
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
        parent::registerWpActions( 'wp_ajax_nopriv_ab_' );
    }

}