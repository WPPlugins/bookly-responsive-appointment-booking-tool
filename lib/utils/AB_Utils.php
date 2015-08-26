<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Utils
 */
class AB_Utils {

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
        if ( get_option( 'ab_email_content_type' ) == 'plain' ) {
            $headers[] = 'Content-Type: text/plain; charset=utf-8';
        } else {
            $headers[] = 'Content-Type: text/html; charset=utf-8';
        }
        $headers[] = 'From: '. get_option( 'ab_settings_sender_name' ) . ' <' . get_option( 'ab_settings_sender_email' ) . '>';
        if ( isset ( $extra['reply-to'] ) ) {
            $headers[] = 'Reply-To: ' . $extra['reply-to']['name'] . ' <' . $extra['reply-to']['email'] . '>';
        }

        return $headers;
    }

    /**
     * Format price based on currency settings (Settings -> Payments).
     *
     * @param  string $price
     * @return string
     */
    public static function formatPrice( $price )
    {
        $price  = floatval( $price );
        switch ( get_option( 'ab_paypal_currency' ) ) {
            case 'AUD' : return 'A$' . number_format_i18n( $price, 2 );
            case 'BRL' : return 'R$ ' . number_format_i18n( $price, 2 );
            case 'CAD' : return 'C$' . number_format_i18n( $price, 2 );
            case 'CHF' : return number_format_i18n( $price, 2 ) . ' CHF';
            case 'CLP' : return 'CLP $' . number_format_i18n( $price, 2 );
            case 'COP' : return '$' . number_format_i18n( $price ) . ' COP';
            case 'CZK' : return number_format_i18n( $price, 2 ) . ' Kč';
            case 'DKK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'EUR' : return '€' . number_format_i18n( $price, 2 );
            case 'GBP' : return '£' . number_format_i18n( $price, 2 );
            case 'GTQ' : return 'Q' . number_format_i18n( $price, 2 );
            case 'HKD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'HUF' : return number_format_i18n( $price, 2 ) . ' Ft';
            case 'IDR' : return number_format_i18n( $price, 2 ) . ' Rp';
            case 'INR' : return number_format_i18n( $price, 2 ) . ' ₹';
            case 'ILS' : return number_format_i18n( $price, 2 ) . ' ₪';
            case 'JPY' : return '¥' . number_format_i18n( $price, 2 );
            case 'KRW' : return number_format_i18n( $price, 2 ) . ' ₩';
            case 'KZT' : return number_format_i18n( $price, 2 ) . ' тг.';
            case 'MXN' : return number_format_i18n( $price, 2 ) . ' $';
            case 'MYR' : return number_format_i18n( $price, 2 ) . ' RM';
            case 'NOK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'NZD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'PHP' : return number_format_i18n( $price, 2 ) . ' ₱';
            case 'PLN' : return number_format_i18n( $price, 2 ) . ' zł';
            case 'QAR' : return number_format_i18n( $price, 2 ) . ' QAR';
            case 'RON' : return number_format_i18n( $price, 2 ) . ' lei';
            case 'RMB' : return number_format_i18n( $price, 2 ) . ' ¥';
            case 'RUB' : return number_format_i18n( $price, 2 ) . ' руб.';
            case 'SAR' : return number_format_i18n( $price, 2 ) . ' SAR';
            case 'SEK' : return number_format_i18n( $price, 2 ) . ' kr';
            case 'SGD' : return number_format_i18n( $price, 2 ) . ' $';
            case 'THB' : return number_format_i18n( $price, 2 ) . ' ฿';
            case 'TRY' : return number_format_i18n( $price, 2 ) . ' TL';
            case 'TWD' : return number_format_i18n( $price, 2 ) . ' NT$';
            case 'UGX' : return 'UGX ' . number_format_i18n( $price );
            case 'USD' : return '$' . number_format_i18n( $price, 2 );
            case 'ZAR' : return 'R ' . number_format_i18n( $price, 2 );
        }

        return number_format_i18n( $price, 2 );
    }

    /**
     * @return string
     */
    public static function getCurrentPageURL()
    {
        return ($_SERVER['REQUEST_SCHEME'] ? $_SERVER['REQUEST_SCHEME'] : 'http') . "://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * @return mixed|string|void
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
    static function escAdminUrl( $page_slug, $params = array() )
    {
        $path = 'admin.php?page='.$page_slug;
        if ( ( $query = build_query( $params ) ) != '' ) {
            $path .= '&'.$query;
        }

        return esc_url( admin_url( $path ) );
    }

    /**
     * Build control for boolean options
     *
     * @param $option_name
     * @param array $options
     */
    public static function optionToggle( $option_name, array $options = array() )
    {
        $options = array_merge( array(
            't' => array( 1, __( 'Enabled',  'bookly' ) ),
            'f' => array( 0, __( 'Disabled', 'bookly' ) )
        ), $options );

        $control = '<select class="form-control" name="'.$option_name.'" id="'.$option_name.'">';
        foreach ( $options as $attr ) {
            $control .= sprintf( '<option value="%s" %s>%s</option>', $attr[0], selected( get_option( $option_name ), $attr[0], false ), $attr[1] );
        }
        echo $control . "</select>";
    }

    /**
     * Build popover control
     *
     * @param $text
     * @param string $style
     * @param bool $echo
     * @return string
     */
    public static function popover( $text, $style = '', $echo = true )
    {
        $control = sprintf(
            '<img src="%s" alt="" class="ab-popover" data-content="%s" %s/>',
            esc_attr( plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ),
            esc_attr( $text ),
            $style != '' ? 'style="' . esc_attr( $style ) . '" ' : ''
        );

        if ( $echo ) {
            echo $control;
        }

        return $control;
    }

    /**
     * Get option translated with WPML.
     *
     * @param $option_name
     * @return mixed|void
     */
    public static function getTranslatedOption( $option_name )
    {
        return self::getTranslatedString( $option_name, get_option( $option_name ) );
    }

    /**
     * Get string translated with WPML.
     *
     * @param $name
     * @param string $original_value
     * @return mixed|void
     */
    public static function getTranslatedString( $name, $original_value = '' )
    {
        return apply_filters( 'wpml_translate_single_string', $original_value, 'bookly', $name );
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
     */
    public static function submitButton( $id = '', $class = '' )
    {
        $html = sprintf(
            '<button %s type="submit" class="btn btn-info ladda-button %s" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">' . __( 'Save', 'bookly' ) . '</span></button>'
            , empty( $id ) ? '' : 'id="' . esc_attr( $id ) . '" ', esc_attr( $class )
        );

        echo $html;
    }

    /**
     * Reset button helper
     *
     * @param string $id
     * @param string $class
     */
    public static function resetButton( $id = '', $class = '' )
    {
        $html = sprintf(
            '<button %s class="ab-reset-form %s" type="reset">' . __( 'Reset', 'bookly' ) . '</button>'
            , empty( $id ) ? '' : 'id="' . esc_attr( $id ) . '" ', esc_attr( $class )
        );

        echo $html;
    }

    /**
     * Echo WP like notice
     *
     * @param $messages
     * @param string $class
     * @param bool|true $show
     */
    public static function notice( $messages, $class = 'notice-success', $show = true )
    {
        $html = '<div class="%s ab-notice notice is-dismissible" %s><p>%s</p></div>';
        if ( ! empty( $messages ) ) {
            $text = is_array( $messages ) ? implode( '</p><p>', $messages ) : $messages;

            echo sprintf( $html, $class, $show ? '' : 'style="display:none"', $text );
        }
    }

}