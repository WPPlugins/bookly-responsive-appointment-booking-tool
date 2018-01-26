<?php
namespace BooklyLite\Lib\Utils;

use BooklyLite\Lib;

/**
 * Class Common
 * @package BooklyLite\Lib\Utils
 */
abstract class Common
{
    /** @var string CSRF token */
    private static $csrf = null;


    /**
     * Get e-mails of wp-admins
     *
     * @return array
     */
    public static function getAdminEmails()
    {
        return array_map(
            create_function( '$a', 'return $a->data->user_email;' ),
            get_users( 'role=administrator' )
        );
    } // getAdminEmails

    /**
     * Generates email's headers FROM: Sender Name < Sender E-mail >
     *
     * @param array $extra
     * @return array
     */
    public static function getEmailHeaders( $extra = array() )
    {
        $headers = array();
        if ( Lib\Config::sendEmailAsHtml() ) {
            $headers[] = 'Content-Type: text/html; charset=utf-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=utf-8';
        }
        $headers[] = 'From: ' . get_option( 'bookly_email_sender_name' ) . ' <' . get_option( 'bookly_email_sender' ) . '>';
        if ( isset ( $extra['reply-to'] ) ) {
            $headers[] = 'Reply-To: ' . $extra['reply-to']['name'] . ' <' . $extra['reply-to']['email'] . '>';
        }

        return apply_filters( 'bookly_email_headers', $headers );
    }

    /**
     * @return string
     */
    public static function getCurrentPageURL()
    {
        if ( ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443 ) {
            $url = 'https://';
        } else {
            $url = 'http://';
        }
        $url .= isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];

        return $url . $_SERVER['REQUEST_URI'];
    }

    /**
     * Escape params for admin.php?page
     *
     * @param $page_slug
     * @param array $params
     * @return string
     */
    public static function escAdminUrl( $page_slug, $params = array() )
    {
        $path = 'admin.php?page=' . $page_slug;
        if ( ( $query = build_query( $params ) ) != '' ) {
            $path .= '&' . $query;
        }

        return esc_url( admin_url( $path ) );
    }

    /**
     * Build control for boolean option
     *
     * @param string $option_name
     * @param string $label caption
     * @param array  $options
     * @param string $help detailed text
     */
    public static function optionToggle( $option_name, $label = '', $help = '', array $options = array() )
    {
        if ( empty( $options ) ) {
            $options = array(
                array( 0, __( 'Disabled', 'bookly' ) ),
                array( 1, __( 'Enabled',  'bookly' ) ),
            );
        }
        $control = sprintf( '<select class="form-control" name="%1$s" id="%1$s">', esc_attr( $option_name ) );
        foreach ( $options as $attr ) {
            $control .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $attr[0] ), selected( get_option( $option_name ), $attr[0], false ), $attr[1] );
        }
        $control .= '</select>';

        echo self::getOptionTemplate( $label, $option_name, $help, $control );
    }

    /**
     * Build control for numeric option
     *
     * @param string   $option_name
     * @param string   $label
     * @param string   $help
     * @param int|null $min
     * @param int|null $step
     * @param int|null $max
     */
    public static function optionNumeric( $option_name, $label, $help, $min = 1, $step = 1, $max = null )
    {
        $control = sprintf( '<input type="number" class="form-control" name="%1$s" id="%1$s" value="%2$s"%3$s%4$s%5$s>',
            esc_attr( $option_name ),
            esc_attr( get_option( $option_name ) ),
            $min  !== null ? ' min="' . $min . '"' : '',
            $max  !== null ? ' max="' . $max . '"' : '',
            $step !== null ? ' step="' . $step . '"' : ''
        );

        echo self::getOptionTemplate( $label, $option_name, $help, $control );
    }

    /**
     * Build control for multi values option
     *
     * @param string $option_name
     * @param array  $options
     * @param null   $label
     * @param null   $help
     */
    public static function optionFlags( $option_name, array $options = array(), $label = null, $help = null )
    {
        $values = (array) get_option( $option_name );
        $control = '';
        foreach ( $options as $attr ) {
            $control .= sprintf( '<div class="checkbox"><label><input type="checkbox" name="%s[]" value="%s" %s>%s</label></div>', $option_name, esc_attr( $attr[0] ), checked( in_array( $attr[0], $values ), true, false ), $attr[1] );
        }

        echo self::getOptionTemplate( $label, $option_name, $help, '<div class="bookly-flags" id="' . $option_name . '">' . $control . '</div>' );
    }

    /**
     * Helper for text option.
     *
     * @param string $option_name
     * @param string $label
     * @param null $help
     */
    public static function optionText( $option_name, $label, $help = null )
    {
        echo self::getOptionTemplate( $label, $option_name, $help, sprintf( '<input id="%1$s" class="form-control" type="text" name="%1$s" value="%2$s">', $option_name, esc_attr( get_option( $option_name ) ) ) );
    }

    /**
     * Get option translated with WPML.
     *
     * @param $option_name
     * @return string
     */
    public static function getTranslatedOption( $option_name )
    {
        return self::getTranslatedString( $option_name, get_option( $option_name ) );
    }

    /**
     * Get string translated with WPML.
     *
     * @param             $name
     * @param string      $original_value
     * @param null|string $language_code Return the translation in this language
     * @return string
     */
    public static function getTranslatedString( $name, $original_value = '', $language_code = null )
    {
        return apply_filters( 'wpml_translate_single_string', $original_value, 'bookly', $name, $language_code );
    }

    /**
     * Check whether the current user is administrator or not.
     *
     * @return bool
     */
    public static function isCurrentUserAdmin()
    {
        return current_user_can( 'manage_options' );
    }

    /**
     * Submit button helper
     *
     * @param string $id
     * @param string $class
     * @param string $title
     */
    public static function submitButton( $id = 'bookly-save', $class = '', $title = '' )
    {
        printf(
            '<button%s type="submit" class="btn btn-lg btn-success ladda-button%s" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">%s</span></button>',
            empty( $id ) ? null : ' id="' . $id . '"',
            empty( $class ) ? null : ' ' . $class,
            $title ?: __( 'Save', 'bookly' )
        );
    }

    /**
     * Reset button helper
     *
     * @param string $id
     * @param string $class
     */
    public static function resetButton( $id = '', $class = '' )
    {
        printf(
            '<button%s class="btn btn-lg btn-default%s" type="reset">' . __( 'Reset', 'bookly' ) . '</button>',
            empty( $id ) ? null : ' id="' . $id . '"',
            empty( $class ) ? null : ' ' . $class
        );
    }

    /**
     * Delete button helper
     *
     * @param string $id
     * @param string $class
     * @param string $modal selector for modal window should be opened after click
     */
    public static function deleteButton( $id = 'bookly-delete', $class = '', $modal = null )
    {
        printf(
            '<button type="button"%s class="btn btn-danger ladda-button%s" data-spinner-size="40" data-style="zoom-in"%s><span class="ladda-label"><i class="glyphicon glyphicon-trash"></i> ' . __( 'Delete', 'bookly' ) . '</span></button>',
            empty( $id ) ? null : ' id="' . $id . '"',
            empty( $class ) ? null : ' ' . $class,
            empty( $modal ) ? null : ' data-toggle="modal" data-target="' . $modal . '"'
        );
    }

    /**
     * Custom button helper.
     *
     * @param string $id
     * @param string $class
     * @param string $title
     * @param array  $attributes
     * @param string $type
     */
    public static function customButton( $id = null, $class = 'btn-success', $title = null, array $attributes = array(), $type = 'button' )
    {
        if ( ! empty( $id ) ) {
            $attributes['id'] = $id;
        }
        printf(
            '<button type="%s" class="btn ladda-button%s" data-spinner-size="40" data-style="zoom-in"%s><span class="ladda-label">%s</span></button>',
            $type,
            empty( $class ) ? null : ' ' . $class,
            self::joinAttributes( $attributes ),
            $title ?: __( 'Save', 'bookly' )
        );
    }

    /**
     * Add hidden input with CSRF token.
     */
    public static function csrf()
    {
        printf(
            '<input type="hidden" name="csrf_token" value="%s">',
            esc_attr( Lib\Utils\Common::getCsrfToken() )
        );
    }

    /**
     * Build attributes for html entity.
     *
     * @param array $attributes
     * @return string|null
     */
    public static function joinAttributes( array $attributes )
    {
        $joined = null;
        foreach ( $attributes as $attr => $value ) {
            $joined .= ' ' . $attr . '="' . $value . '"';
        }

        return $joined;
    }

    /**
     * XOR encrypt/decrypt.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    private static function _xor( $str, $password = '' )
    {
        $len   = strlen( $str );
        $gamma = '';
        $n     = $len > 100 ? 8 : 2;
        while ( strlen( $gamma ) < $len ) {
            $gamma .= substr( pack( 'H*', sha1( $password . $gamma ) ), 0, $n );
        }

        return $str ^ $gamma;
    }

    /**
     * XOR encrypt with Base64 encode.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    public static function xorEncrypt( $str, $password = '' )
    {
        return base64_encode( self::_xor( $str, $password ) );
    }

    /**
     * XOR decrypt with Base64 decode.
     *
     * @param string $str
     * @param string $password
     * @return string
     */
    public static function xorDecrypt( $str, $password = '' )
    {
        return self::_xor( base64_decode( $str ), $password );
    }

    /**
     * Codes table helper
     *
     * @param array $codes
     * @param array $flags
     */
    public static function codes( array $codes, $flags = array() )
    {
        // Sort codes alphabetically.
        usort( $codes, function ( $code_a, $code_b ) {
            return strcmp( $code_a['code'], $code_b['code'] );
        } );

        $tbody = '';
        foreach ( $codes as $code ) {
            $valid = true;
            if ( isset ( $code['flags'] ) ) {
                foreach ( $code['flags'] as $flag => $value ) {
                    $valid = false;
                    if ( isset ( $flags[ $flag ] ) ) {
                        if ( is_string( $value ) && preg_match( '/([!>=<]+)(\d+)/', $value, $match ) ) {
                            switch ( $match[1] ) {
                                case '<':  $valid = $flags[ $flag ] < $match[2];  break;
                                case '<=': $valid = $flags[ $flag ] <= $match[2]; break;
                                case '=':  $valid = $flags[ $flag ] == $match[2]; break;
                                case '!=': $valid = $flags[ $flag ] != $match[2]; break;
                                case '>=': $valid = $flags[ $flag ] >= $match[2]; break;
                                case '>':  $valid = $flags[ $flag ] > $match[2];  break;
                            }
                        } else {
                            $valid = $flags[ $flag ] == $value;
                        }
                    }
                    if ( ! $valid ) {
                        break;
                    }
                }
            }
            if ( $valid ) {
                $tbody .= sprintf(
                    '<tr><td><input value="{%s}" readonly="readonly" onclick="this.select()" /> - %s</td></tr>',
                    $code['code'],
                    $code['description']
                );
            }
        }
        $class = isset( $flags['type'] ) ? 'bookly-js-codes-' . $flags['type'] : '';

        echo '<table class="bookly-codes ' . $class . '"><tbody>' . $tbody . '</tbody></table>';
    }

    /**
     * Return html for option
     *
     * @param string $label
     * @param string $option_name
     * @param string $help
     * @param string $control
     * @return string
     */
    private static function getOptionTemplate( $label, $option_name, $help, $control )
    {
        return strtr( '<div class="form-group">{label}{help}{control}</div>',
            array(
                '{label}'   => empty( $label ) ? '' : sprintf( '<label for="%s">%s</label>', $option_name, $label ),
                '{help}'    => empty( $help ) ? '' : sprintf( '<p class="help-block">%s</p>', $help ),
                '{control}' => $control,
            )
        );
    }

    /**
     * Generate unique value for entity field.
     *
     * @param string $entity_class_name
     * @param string $token_field
     * @return string
     */
    public static function generateToken( $entity_class_name, $token_field )
    {
        /** @var Lib\Base\Entity $entity */
        $entity = new $entity_class_name();
        do {
            $token = md5( uniqid( time(), true ) );
        }
        while ( $entity->loadBy( array( $token_field => $token ) ) === true );

        return $token;
    }


    /**
     * Get CSRF token.
     *
     * @return string
     */
    public static function getCsrfToken()
    {
        if ( self::$csrf === null ) {
            self::$csrf = wp_create_nonce( 'bookly' );
        }

        return self::$csrf;
    }

    /**
     * Set nocache constants.
     */
    public static function noCache()
    {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) {
            define( 'DONOTCACHEPAGE', true );
        }
        if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
            define( 'DONOTCACHEOBJECT', true );
        }
        if ( ! defined( 'DONOTCACHEDB' ) ) {
            define( 'DONOTCACHEDB', true );
        }
    }
}