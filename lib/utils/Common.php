<?php
namespace BooklyLite\Lib\Utils;

use BooklyLite\Lib;

/**
 * Class Common
 * @package BooklyLite\Lib\Utils
 */
abstract class Common
{
    /**
     * Format price based on currency settings (Settings -> Payments).
     *
     * @param  $price
     * @return string
     */
    public static function formatPrice( $price )
    {
        $price  = (float) $price;
        switch ( get_option( 'bookly_pmt_currency' ) ) {
            case 'AED' : return number_format_i18n( $price, 2 ) . ' AED';
            case 'ARS' : return '$' . number_format_i18n( $price, 2 );
            case 'AUD' : return 'A$' . number_format_i18n( $price, 2 );
            case 'BGN' : return number_format_i18n( $price, 2 ) . ' лв.';
            case 'BHD' : return 'BHD ' . number_format_i18n( $price, 2 );
            case 'BRL' : return 'R$ ' . number_format_i18n( $price, 2 );
            case 'CAD' : return 'C$' . number_format_i18n( $price, 2 );
            case 'CHF' : return number_format_i18n( $price, 2 ) . ' CHF';
            case 'CLP' : return 'CLP $' . number_format_i18n( $price, 2 );
            case 'COP' : return '$' . number_format_i18n( $price ) . ' COP';
            case 'CRC' : return '₡' . number_format_i18n( $price, 2 );
            case 'CZK' : return number_format_i18n( $price, 2 ) . ' Kč';
            case 'DKK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'DOP' : return 'RD$' . number_format_i18n( $price, 2 );
            case 'EGP' : return 'EGP ' . number_format_i18n( $price, 2 );
            case 'EUR' : return '€' . number_format_i18n( $price, 2 );
            case 'GBP' : return '£' . number_format_i18n( $price, 2 );
            case 'GEL' : return number_format_i18n( $price, 2 ) . ' lari';
            case 'GTQ' : return 'Q' . number_format_i18n( $price, 2 );
            case 'HKD' : return 'HK$' . number_format_i18n( $price, 2 );
            case 'HRK' : return number_format_i18n( $price, 2 ) . ' kn';
            case 'HUF' : return number_format_i18n( $price, 2 ) . ' Ft';
            case 'IDR' : return number_format_i18n( $price, 2 ) . ' Rp';
            case 'ILS' : return number_format_i18n( $price, 2 ) . ' ₪';
            case 'INR' : return number_format_i18n( $price, 2 ) . ' ₹';
            case 'ISK' : return number_format_i18n( $price ) . ' kr';
            case 'JPY' : return '¥' . number_format_i18n( $price );
            case 'KES' : return 'KSh ' . number_format_i18n( $price, 2 );
            case 'KRW' : return number_format_i18n( $price, 2 ) . ' ₩';
            case 'KZT' : return number_format_i18n( $price, 2 ) . ' тг.';
            case 'LAK' : return number_format_i18n( $price ) . ' ₭';
            case 'MUR' : return 'Rs' . number_format_i18n( $price, 2 );
            case 'MXN' : return '$' . number_format_i18n( $price, 2 );
            case 'MYR' : return number_format_i18n( $price, 2 ) . ' RM';
            case 'NAD' : return 'N$' . number_format_i18n( $price, 2 );
            case 'NGN' : return '₦' . number_format_i18n( $price, 2 );
            case 'NOK' : return 'Kr ' . number_format_i18n( $price, 2 );
            case 'NZD' : return '$' . number_format_i18n( $price, 2 );
            case 'OMR' : return number_format_i18n( $price, 3 ) . ' OMR';
            case 'PEN' : return 'S/.' . number_format_i18n( $price, 2 );
            case 'PHP' : return number_format_i18n( $price, 2 ) . ' ₱';
            case 'PKR' : return 'Rs. ' . number_format_i18n( $price );
            case 'PLN' : return number_format_i18n( $price, 2 ) . ' zł';
            case 'QAR' : return number_format_i18n( $price, 2 ) . ' QAR';
            case 'RMB' : return number_format_i18n( $price, 2 ) . ' ¥';
            case 'RON' : return number_format_i18n( $price, 2 ) . ' lei';
            case 'RUB' : return number_format_i18n( $price, 2 ) . ' руб.';
            case 'SAR' : return number_format_i18n( $price, 2 ) . ' SAR';
            case 'SEK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'SGD' : return '$' . number_format_i18n( $price, 2 );
            case 'THB' : return number_format_i18n( $price, 2 ) . ' ฿';
            case 'TRY' : return number_format_i18n( $price, 2 ) . ' TL';
            case 'TWD' : return number_format_i18n( $price, 2 ) . ' NT$';
            case 'UAH' : return number_format_i18n( $price, 2 ) . ' ₴';
            case 'UGX' : return 'UGX ' . number_format_i18n( $price );
            case 'USD' : return '$' . number_format_i18n( $price, 2 );
            case 'VND' : return number_format_i18n( $price ) . ' VNĐ';
            case 'XAF' : return number_format_i18n( $price ) . ' FCFA';
            case 'XOF' : return 'CFA ' . number_format_i18n( $price, 2 );
            case 'ZAR' : return 'R ' . number_format_i18n( $price, 2 );
            case 'ZMW' : return 'K' . number_format_i18n( $price, 2 );
        }

        return number_format_i18n( $price, 2 );
    }

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
     * @return string
     */
    public static function getTimezoneString()
    {
        // if site timezone string exists, return it
        if ( $timezone = get_option( 'timezone_string' ) ) {
            return $timezone;
        }

        // get UTC offset, if it isn't set then return UTC
        if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
            return 'UTC';
        }

        // adjust UTC offset from hours to seconds
        $utc_offset *= 3600;

        // attempt to guess the timezone string from the UTC offset
        if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
            return $timezone;
        }

        // last try, guess timezone string manually
        $is_dst = date( 'I' );

        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                    return $city['timezone_id'];
            }
        }

        // fallback to UTC
        return 'UTC';
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
     * @param null   $label
     * @param array  $options
     * @param null   $help
     */
    public static function optionToggle( $option_name, $label = null, $help = null, array $options = array() )
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

        echo self::getOptionTemplate( $label, $option_name, $help, $control . '</select>' );
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

        echo self::getOptionTemplate( $label, $option_name, $help, '<div class="ab-flags" id="' . $option_name . '">' . $control . '</div>' );
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
     * Get translated custom fields
     *
     * @param integer $service_id
     * @param string $language_code       Return the translation in this language
     * @return \stdClass[]
     */
    public static function getTranslatedCustomFields( $service_id = null, $language_code = null )
    {
        $custom_fields  = json_decode( get_option( 'bookly_custom_fields' ) );
        foreach ( $custom_fields as $key => $custom_field ) {
            if ( $service_id === null || in_array( $service_id, $custom_field->services ) ) {
                switch ( $custom_field->type ) {
                    case 'textarea':
                    case 'text-content':
                    case 'text-field':
                    case 'captcha':
                        $custom_field->label = self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label, $language_code );
                        break;
                    case 'checkboxes':
                    case 'radio-buttons':
                    case 'drop-down':
                        $items = $custom_field->items;
                        foreach ( $items as $pos => $label ) {
                            $items[ $pos ] = array(
                                'value' => $label,
                                'label' => self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ) . '=' . sanitize_title( $label ), $label, $language_code )
                            );
                        }
                        $custom_field->label = self::getTranslatedString( 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label, $language_code );
                        $custom_field->items = $items;
                        break;
                }
            } else {
                unset( $custom_fields[ $key ] );
            }
        }

        return $custom_fields;
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
     * @param string $plugin Like 'bookly-addon-service-extras/main.php'
     * @return bool
     */
    public static function isPluginActive( $plugin )
    {
        // In MultiSite exist 2 methods activation plugin for site.
        return (
            in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
            is_plugin_active_for_network( $plugin )
        );
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
     * @param int   $step
     * @param bool  $login
     */
    public static function Codes( array $codes, $step = 1, $login = false )
    {
        $tbody = '';
        uasort( $codes, function ( $code_a, $code_b ) {
            return ( $code_a['code'] < $code_b['code'] ) ? - 1 : 1;
        } );
        foreach ( $codes as $code ) {
            if ( empty( $code['step'] ) || $step == $code['step'] ) {
                if ( empty( $code['min_step'] ) || $step > $code['min_step'] ) {
                    if ( empty( $code['login'] ) || $login ) {
                        $tbody .= '<tr><td><input value="{' . $code['code'] . '}" readonly="readonly" onclick="this.select()" /> - ' . $code['description'] . '</td></tr>';
                    }
                }
            }
        }
        echo '<table class="bookly-codes"><tbody>' . $tbody . '</tbody></table>';
    }

    /**
     * Check if running on HHVM
     *
     * @return bool
     */
    public static function isHHVM() {
        return defined( 'HHVM_VERSION' );
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

}