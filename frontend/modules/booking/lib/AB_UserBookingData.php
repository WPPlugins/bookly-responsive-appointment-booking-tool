<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH . '/lib/AB_Validator.php';

class AB_UserBookingData {

    /**
     * @var int
     */
    private $form_id;

    /**
     * @var int
     */
    private $service_id;

    /**
     * @var array
     */
    private $staff_id = array();

    /**
     * @var string
     */
    private $requested_date_from;

    /**
     * @var string
     */
    private $requested_time_from;

    /**
     * @var string
     */
    private $requested_time_to;

    /**
     * @var string
     */
    private $booked_datetime;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var bool
     */
    private $create_account;

    /**
     * @var int
     */
    private $client_time_offset = 0;

    /**
     * @var array
     */
    private $available_days = array();

    public function __construct( $form_id ) {
        $this->form_id = $form_id;
        
        $this->requested_date_from = date( 'Y-m-d' );
    }

    public function hasData() {
        return isset($_SESSION[ 'appointment_booking' ][ $this->form_id ]);
    }

    public function load() {
        if ( isset($_SESSION[ 'appointment_booking' ][ $this->form_id ]) ) {
            $reflection = new ReflectionObject($this);
            foreach ( $reflection->getProperties() as $reflectionProperty ) {
                $field_name = $reflectionProperty->getName();
                if ( isset($_SESSION['appointment_booking'][ $this->form_id ][ $field_name ]) ) {
                    $this->$field_name = $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ];
                }
            }
        }
    }

    public function loadTemporaryForExpressCheckout() {
        if ( isset( $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] ) &&
            $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] === true) {
            $reflection = new ReflectionObject($this);
            foreach ( $reflection->getProperties() as $reflectionProperty ) {
                $field_name = $reflectionProperty->getName();
                $tmp_booking_data = unserialize( $_SESSION[ 'tmp_booking_data' ] );
                $tmp_booking_data = get_object_vars( $tmp_booking_data );

                if ( isset( $tmp_booking_data[ $field_name ] ) ) {
                    $this->$field_name = $tmp_booking_data[ $field_name ];
                }
            }
        }
    }

    public function loadTemporaryForLocalPayment() {
        if ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
            $reflection = new ReflectionObject($this);
            foreach ( $reflection->getProperties() as $reflectionProperty ) {
                $field_name = $reflectionProperty->getName();
                $tmp_booking_data = unserialize( $_SESSION[ 'tmp_booking_data' ] );
                $tmp_booking_data = get_object_vars( $tmp_booking_data );

                if ( isset( $tmp_booking_data[ $field_name ] ) ) {
                    $this->$field_name = $tmp_booking_data[ $field_name ];
                }
            }
        }
    }

    public function setData( $data ) {
        $reflection = new ReflectionObject($this);
        $default_properties = $reflection->getDefaultProperties();
        if ( !$this->hasData( $this->form_id ) ) {
            $_SESSION['appointment_booking'][ $this->form_id ] = array();
        }
        foreach ( $reflection->getProperties() as $reflectionProperty ) {
            $field_name = $reflectionProperty->getName();
            if ( isset ( $data[ $field_name ] ) ) {
                $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] = $data[ $field_name ];
            // overwrite to default property only if there are no property or it's empty
            } elseif ( !isset( $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] ) ||
                empty( $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] ) ) {
                    $_SESSION['appointment_booking'][ $this->form_id ][ $field_name ] = $default_properties[ $field_name ];
                }
        }
    }

    public function validate( $data ) {
        $reflection = new ReflectionObject($this);
        $validator = new AB_Validator();
        foreach ( $reflection->getProperties() as $reflectionProperty ) {
            $field_name = $reflectionProperty->getName();
            if ( isset($data[ $field_name ]) ) {
                switch ( $field_name ) {
                    case 'email':
                        $validator->validateEmail( $field_name, $data[ $field_name ], true );
                        break;
                    case 'phone':
                        $validator->validatePhone( $field_name, $data[ $field_name ], true );
                        break;
                    case 'requested_date_from':
                    case 'requested_time_from':
                    case 'requested_time_to':
                    case 'booked_datetime':
                        $validator->validateDateTime( $field_name, $data[ $field_name ], true );
                        break;
                    case 'name':
                        $validator->validateString( $field_name, $data[ $field_name ], 255, true, true, 3 );
                        break;
                    case 'service_id':
                        $validator->validateNumber( $field_name, $data[ $field_name ] );
                        break;
                }
            }
        }

        if ( isset( $data['requested_time_from'] ) && isset( $data['requested_time_to'] ) ) {
            $validator->validateTimeGt( 'requested_time_from', $data['requested_time_from'], $data['requested_time_to'] );
        }

        return $validator->getErrors();
    }

    /**
     * @return AB_Appointment
     */
    public function save() {
        /** @var wpdb $wpdb */
        global $wpdb;

        add_filter('wp_mail_from', create_function( '$content_type',
            'return get_option( \'ab_settings_sender_email\' ) == \'\' ?
                get_option( \'admin_email\' ) : get_option( \'ab_settings_sender_email\' );'
        ) );
        add_filter('wp_mail_from_name', create_function( '$name',
            'return get_option( \'ab_settings_sender_name\' ) == \'\' ?
                get_option( \'blogname\' ) : get_option( \'ab_settings_sender_name\' );'
        ) );

        // #11094: if customer with such name & e-mail exists, append new booking to him, otherwise - create new customer
        $customer_exists = $wpdb->get_results( $wpdb->prepare(
           'SELECT c.* FROM ab_customer c WHERE c.name = %s AND c.email = %s', $this->name, $this->email
        ) );

        if ( count( $customer_exists ) ) {
            $customer =  new AB_Customer();
            $customer->set( 'id',    $customer_exists[ 0 ]->id );
            $customer->set( 'name',  $customer_exists[ 0 ]->name );
            $customer->set( 'email', $customer_exists[ 0 ]->email );
            $customer->set( 'phone', $customer_exists[ 0 ]->phone );
        } else {
            $customer =  new AB_Customer();
            $customer->set( 'name',  $this->name );
            $customer->set( 'email', $this->email );
            $customer->set( 'phone', $this->phone );
            $customer->save();
        }

        if ( $this->create_account ) {
            $user_id = username_exists( $this->username );
            if ( !$user_id && email_exists( $this->email ) == false ) {
                $random_password = wp_generate_password( 8, false );
                $user_data = array(
                    'user_pass'     => $random_password,
                    'user_login'    => $this->username,
                    'user_nicename' => $this->username,
                    'user_email'    => $this->email,
                    'first_name'    => $this->name,
                );

                wp_insert_user( $user_data );
            }
        }

        $service = new AB_Service();
        $service->load( $this->service_id );

        $appointment = new AB_Appointment();
        $appointment->set( 'staff_id', $this->getStaffId() );
        $appointment->set( 'service_id', $this->service_id );
        $appointment->set( 'start_date', $this->booked_datetime );
        $appointment->set( 'token' , md5($this->form_id) );
        $appointment->set( 'notes', $this->notes );
        $endDate = new DateTime($this->booked_datetime);
        $di = "+ {$service->get( 'duration' )} sec";
        $endDate->modify( $di );

        $appointment->set( 'end_date', $endDate->format('Y-m-d H:i:s') );
        $appointment->set( 'customer_id', $customer->get( 'id' ) );
        $appointment->save();

        return $appointment;
    }

    public function clean() {
        unset( $_SESSION[ 'appointment_booking' ][ $this->form_id ] );
    }

    /**
     * @return string
     */
    public function getRequestedDateFrom() {
        return $this->requested_date_from;
    }

    public function getFormattedRequestedDateFrom() {
        if ( get_option('ab_settings_no_current_day_appointments' ) ) {
            return date_i18n( 'j F, Y', strtotime('+1 day') );
        }
        else {
            return date_i18n( 'j F, Y', strtotime( $this->requested_date_from ) );
        }
    }

    /**
     * @return array
     */
    public function getAvailableDays() {
        return $this->available_days;
    }

    /**
     * @return string
     */
    public function getRequestedTimeFrom() {
        if ( !$this->requested_time_from ) {
            /** @var wpdb $wpdb */
            global $wpdb;
            $this->requested_time_from = $wpdb->get_var( "SELECT SUBSTRING_INDEX(MIN(start_time), ':', 2) AS min_end_time FROM ab_staff_schedule_item WHERE start_time IS NOT NULL" );
        }

        return $this->requested_time_from;
    }

    /**
     * @return string
     */
    public function getRequestedTimeTo() {
        if ( !$this->requested_time_to ) {
            /** @var wpdb $wpdb */
            global $wpdb;
            $this->requested_time_to = $wpdb->get_var( "SELECT SUBSTRING_INDEX(MAX(end_time), ':', 2) AS max_end_time FROM ab_staff_schedule_item WHERE end_time IS NOT NULL" );
        }

        return $this->requested_time_to;
    }

    /**
     * @return int
     */
    public function getServiceId() {
        return $this->service_id;
    }

    /**
     * @param int $service_id
     *
     * @return mixed
     */
    public function getServiceNameById( $service_id ) {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT abs.title as service_name FROM `ab_service` as abs WHERE abs.id = %d', $service_id ) );
    }

    public function getStaffIds() {
        return $this->staff_id;
    }

    /**
     * @return int
     */
    public function getStaffId() {
        if ( count( $this->staff_id ) == 1 ) {
            return $this->staff_id[ 0 ];
        } elseif ( count( $this->staff_id ) > 1 ) {
            $first_value = $this->staff_id[ 0 ];
            for ( $i = 1; $i < count( $this->staff_id ); $i++ ) { // checking for uniqueness
                return $this->staff_id[ $i ] == $first_value ? $first_value : 0;
            }
        }
        return 0;
    }

    /**
     * @param int $staff_id
     *
     * @return mixed
     */
    public function getStaffNameById( $staff_id ) {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT abs.full_name as staff_name FROM `ab_staff` as abs WHERE abs.id = %d', $staff_id ) );
    }

    /**
     * Return "clean" Service's Price
     *
     * @param int $service_id
     *
     * @return mixed
     */
    public function getServicePriceById( $service_id ) {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT abs.price as service_price FROM `ab_service` as abs WHERE abs.id = %d', $service_id ) );
    }

    /**
     * Return Staff's price for Service
     *
     * @param int $service_id
     * @param int $staff_id
     *
     * @return mixed
     */
    public function getServicePriceByStaffId( $service_id, $staff_id ) {
        /** @var wpdb $wpdb */
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare( 'SELECT abss.price as service_price FROM `ab_staff_service` as abss WHERE abss.service_id = %d AND abss.staff_id = %d', $service_id, $staff_id ) );
    }

    /**
     * @return string
     */
    public function getBookedDatetime() {
        return $this->booked_datetime;
    }

    /**
     * @return boolean
     */
    public function getCreateAccount() {
        return $this->create_account;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    public function setPaymentId( $payment_id ) {
        $_SESSION['appointment_booking'][ $this->form_id ]['payment_id'] = $payment_id;
    }

    public function getPaymentId() {
        if ( isset($_SESSION['appointment_booking'][ $this->form_id ]['payment_id']) ) {
            return $_SESSION['appointment_booking'][ $this->form_id ]['payment_id'];
        }

        return null;
    }

    public function setBookingFinished( $finished ) {
        $_SESSION['appointment_booking'][ $this->form_id ]['finished'] = $finished;
    }

    public function getBookingFinished() {
        if ( isset($_SESSION['appointment_booking'][ $this->form_id ]['finished']) ) {
            return $_SESSION['appointment_booking'][ $this->form_id ]['finished'];
        } elseif ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
            $tmp_booking_data = unserialize( $_SESSION[ 'tmp_booking_data' ] );
            if ( is_array( $tmp_booking_data ) ) {
                $tmp_booking_data = (object)$tmp_booking_data;
            }
            $tmp_booking_data = get_object_vars( $tmp_booking_data );
            $tmp_form_id = $tmp_booking_data[ 'form_id' ];

            if ( isset( $_SESSION[ 'appointment_booking' ][ $tmp_form_id ][ 'finished' ] ) ) {
                return $_SESSION[ 'appointment_booking' ][ $tmp_form_id ][ 'finished' ];
            }
        }

        return false;
    }

    public function setBookingCancelled( $cancelled ) {
        $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] = $cancelled;
    }

    public function getBookingCancelled() {
        if ( isset( $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ] ) ) {
            return $_SESSION[ 'appointment_booking' ][ $this->form_id ][ 'cancelled' ];
        } elseif ( isset( $_SESSION[ 'tmp_booking_data' ] ) ) {
            $tmp_booking_data = unserialize( $_SESSION[ 'tmp_booking_data' ] );
            if ( is_array( $tmp_booking_data ) ) {
                $tmp_booking_data = (object)$tmp_booking_data;
            }
            $tmp_booking_data = get_object_vars( $tmp_booking_data );
            $tmp_form_id = $tmp_booking_data[ 'form_id' ];

            if ( isset( $_SESSION[ 'appointment_booking' ][ $tmp_form_id ][ 'cancelled' ] ) ) {
                return $_SESSION[ 'appointment_booking' ][ $tmp_form_id ][ 'cancelled' ];
            }
        }

        return false;
    }

    public function setClientTimeOffset($time_offset){
        $this->client_time_offset = $time_offset;
    }
}