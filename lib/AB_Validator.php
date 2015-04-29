<?php
 
class AB_Validator {

    private $errors = array();

    public function validateEmail( $field, $email, $required = false ) {
        if ( $email ) {
            if ( !is_email( $email ) ) {
                $this->errors[$field] = __('* Invalid email', 'ab');
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( '* Please tell us your email', 'ab' );
        }
    }

    public function validatePhone( $field, $phone, $required = false ) {
        if ( $phone  ) {
            if ( !preg_match('/^[0-9\s\+\-\(\)]+$/', $phone) ) {
                $this->errors[$field] = __( '* Invalid phone number', 'ab' );
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( '* Please tell us your phone', 'ab' );
        }
    }

    public function validateString( $field, $string, $max_length, $required = false, $is_name = false, $min_length = 0 ) {
        if ( $string ) {
            $long     =  __( 'is too long', 'ab' );
            $short    = __( 'is too short', 'ab' );
            $char_max = __( 'characters max', 'ab' );
            $char_min = __( 'characters min', 'ab' );
            if ( strlen( $string ) > $max_length ) {
                $this->errors[$field] = __(sprintf('"%s" is too ' . $long . ' (%d '. $char_max .').', $string, $max_length ), 'ab');
            } elseif ( $min_length > strlen( $string ) ) {
                $this->errors[$field] = __(sprintf('"%s" is too ' . $short . ' (%d '. $char_min .').', $string, $min_length ), 'ab');
            }
        } elseif ( $required && $is_name  ) {
            $this->errors[$field] = __( '* Please tell us your name', 'ab' );
        } elseif ( $required ) {
            $this->errors[$field] = __( '* Required', 'ab' );
        }
    }

    public function validateNumber( $field, $number, $required = false ) {
        if ( $number ) {
            if ( !is_numeric( $number ) ) {
                $this->errors[$field] = __('Invalid number', 'ab');
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( 'Required', 'ab' );
        }
    }

    public function validateTimeGt( $field, $start_time, $end_time ) {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        if ( $start->format( 'U' ) >= $end->format( 'U' ) ) {
            $this->errors[$field] = __('* The start time must be less than the end time', 'ab');
        }
    }

    public function validateDateTime( $field, $datetime, $required = false ) {
        if ( $datetime ) {
            if ( date_create( $datetime ) === false ) {
                $this->errors[$field] = __('Invalid date or time', 'ab');
            }
        } elseif ( $required ) {
            $this->errors[$field] = __( '* Required', 'ab' );
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}