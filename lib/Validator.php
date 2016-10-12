<?php
namespace BooklyLite\Lib;

/**
 * Class Validator
 * @package BooklyLite\Lib
 */
class Validator
{
    private $errors = array();

    /**
     * @param $field
     * @param $data
     */
    public function validateEmail( $field, $data )
    {
        if ( $data['email'] ) {
            if ( ! is_email( $data['email'] ) ) {
                $this->errors[ $field ] = __( 'Invalid email', 'bookly' );
            }
            // Check email for uniqueness when a new WP account will be created.
            if ( get_option( 'ab_settings_create_account', 0 ) && $data['name'] && ! get_current_user_id() ) {
                $wp_user = Entities\Customer::query( 'c' )->where( 'c.name', $data['name'] )->where( 'c.email', $data['email'] )->fetchRow();
                if ( $wp_user == null && email_exists( $data['email'] ) ) {
                    $this->errors[ $field ] = __( 'This email is already in use', 'bookly' );
                }
            }
        } else {
            $this->errors[ $field ] = Utils\Common::getTranslatedOption( 'ab_appearance_text_required_email' );
        }
    }

    /**
     * @param $field
     * @param $phone
     * @param bool $required
     */
    public function validatePhone( $field, $phone, $required = false )
    {
        if ( empty( $phone ) && $required ) {
            $this->errors[ $field ] = Utils\Common::getTranslatedOption( 'ab_appearance_text_required_phone' );
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
                $this->errors[ $field ] = sprintf(
                    __( '"%s" is too long (%d characters max).', 'bookly' ),
                    $string,
                    $max_length
                );
            } elseif ( $min_length > strlen( $string ) ) {
                $this->errors[ $field ] = sprintf(
                    __( '"%s" is too short (%d characters min).', 'bookly' ),
                    $string,
                    $min_length
                );
            }
        } elseif ( $required && $is_name ) {
            $this->errors[ $field ] =  Utils\Common::getTranslatedOption( 'ab_appearance_text_required_name' );
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
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
            if ( ! is_numeric( $number ) ) {
                $this->errors[ $field ] = __( 'Invalid number', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
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
                $this->errors[ $field ] = __( 'Invalid date or time', 'bookly' );
            }
        } elseif ( $required ) {
            $this->errors[ $field ] = __( 'Required', 'bookly' );
        }
    }

    /**
     * @param $value
     * @param $form_id
     * @param $cart_key
     */
    public function validateCustomFields( $value, $form_id, $cart_key )
    {
        $decoded_value = json_decode( $value );
        $fields = array();
        foreach ( json_decode( get_option( 'ab_custom_fields' ) ) as $field ) {
            $fields[ $field->id ] = $field;
        }

        $custom_fields = array();
        foreach ( $decoded_value as $field ) {
            if ( isset( $fields[ $field->id ] ) ) {
                if ( ( $fields[ $field->id ]->type == 'captcha' ) && ! Captcha\Captcha::validate( $form_id, $field->value ) ) {
                    $this->errors['custom_fields'][ $cart_key ][ $field->id ] = __( 'Incorrect code', 'bookly' );
                } elseif ( $fields[ $field->id ]->required && empty ( $field->value ) && $field->value != '0' ) {
                    $this->errors['custom_fields'][ $cart_key ][ $field->id ] = __( 'Required', 'bookly' );
                } else {
                    $custom_fields[ $field->id ] = $field;
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function validateCart( $cart, $form_id )
    {
        foreach ( $cart as $cart_key => $cart_parameters ) {
            foreach ( $cart_parameters as $parameter => $value ) {
                switch ( $parameter ) {
                    case 'custom_fields':
                        $this->validateCustomFields( $value, $form_id, $cart_key );
                        break;
                }
            }
        }
    }

    public function validateChain()
    {

    }

}