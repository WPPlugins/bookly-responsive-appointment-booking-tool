<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Validator {

    private $errors = array();

    /**
     * @param $field
     * @param $data
     */
    public function validateEmail( $field, $data )
    {
        if ( $data['email'] ) {
            if ( !is_email( $data['email'] ) ) {
                $this->errors[ $field ] = __( 'Invalid email', 'bookly' );
            }
            // Check email for uniqueness when a new WP account will be created.
            if ( get_option( 'ab_settings_create_account', 0 ) && $data['name'] && ! get_current_user_id() ) {
                $wp_user = AB_Customer::query( 'c' )->select( 'c.wp_user_id' )->where( 'c.name', $data['name'] )->where( 'c.email', $data['email'] )->fetchArray();
                $wp_user_id = $wp_user['0']['wp_user_id'];
                if ( ! $wp_user_id && email_exists( $data['email'] ) ) {
                    $this->errors[ $field ] = __( 'This email is already in use', 'bookly' );
                }
            }
        } else {
            $this->errors[$field] = __( 'Please tell us your email', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $phone
     * @param bool $required
     */
    public function validatePhone( $field, $phone, $required = false )
    {
        if ( $phone  ) {
            if ( !preg_match('/^[0-9\s\+\-\(\)]+$/', $phone) ) {
                $this->errors[$field] = __( 'Invalid phone number', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( 'Please tell us your phone', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $string
     * @param $max_length
     * @param bool $required
     * @param bool $is_name
     * @param int $min_length
     */
    public function validateString( $field, $string, $max_length, $required = false, $is_name = false, $min_length = 0 )
    {
        if ( $string ) {
            if ( strlen( $string ) > $max_length ) {
                $this->errors[$field] = sprintf(
                    __( '"%s" is too long (%d characters max).', 'bookly' ),
                    $string,
                    $max_length
                );
            } elseif ( $min_length > strlen( $string ) ) {
                $this->errors[$field] = sprintf(
                    __( '"%s" is too short (%d characters min).', 'bookly' ),
                    $string,
                    $min_length
                );
            }
        } elseif ( $required && $is_name  ) {
            $this->errors[$field] = __( 'Please tell us your name', 'bookly' );
        } elseif ( $required ) {
            $this->errors[$field] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $number
     * @param bool $required
     */
    public function validateNumber( $field, $number, $required = false )
    {
        if ( $number ) {
            if ( !is_numeric( $number ) ) {
                $this->errors[$field] = __( 'Invalid number', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $start_time
     * @param $end_time
     */
    public function validateTimeGt( $field, $start_time, $end_time )
    {
        if ( AB_DateTimeUtils::timeToSeconds( $start_time ) >= AB_DateTimeUtils::timeToSeconds( $end_time ) ) {
            $this->errors[$field] = __( 'The start time must be less than the end time', 'bookly' );
        }
    }

    /**
     * @param $field
     * @param $datetime
     * @param bool $required
     */
    public function validateDateTime( $field, $datetime, $required = false )
    {
        if ( $datetime ) {
            if ( date_create( $datetime ) === false ) {
                $this->errors[$field] = __( 'Invalid date or time', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $value
     */
    public function validateCustomFields( $value )
    {
        $native_custom_fields_obj = json_decode( get_option( 'ab_custom_fields' ) );
        $native_custom_fields = array();
        foreach ( $native_custom_fields_obj as $native_custom_field_obj ) {
            $native_custom_fields[ $native_custom_field_obj->id ] = $native_custom_field_obj;
        }

        $request_custom_fields_obj = json_decode( $value );
        $request_custom_fields = array();
        foreach ( $request_custom_fields_obj as $request_custom_field_obj ) {
            if ( isset( $native_custom_fields[ $request_custom_field_obj->id ] ) ) {
                if ( $native_custom_fields[ $request_custom_field_obj->id ]->required && $request_custom_field_obj->value == '' ) {
                    $this->errors['custom_fields'][ 'ab-custom-field-' . $request_custom_field_obj->id ] = __( 'Required', 'bookly' );
                } else {
                    $request_custom_fields[ $request_custom_field_obj->id ] = $request_custom_field_obj;
                }
            }
        }

        // find the missing fields
        foreach ( array_diff_key( $native_custom_fields, $request_custom_fields ) as $missing_field ) {
            if ( $missing_field->required ) {
                $this->errors['custom_fields']['ab-custom-field-' . $missing_field->id] = __( 'Required', 'bookly' );
            }
        }

        // TODO extra fields in request
        foreach (array_diff_key($request_custom_fields, $native_custom_fields) as $extra_field) {

        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

}