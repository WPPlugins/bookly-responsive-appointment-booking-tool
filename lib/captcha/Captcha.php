<?php
namespace BooklyLite\Lib\Captcha;

use BooklyLite\Lib;

/**
 * Class Captcha
 * @package BooklyLite\Lib\Captcha
 */
class Captcha
{
    /**
     * Init.
     *
     * @param $form_id
     * @param array $config
     */
    public static function init( $form_id, $config = array() )
    {
        // Check for GD library
        if ( ! function_exists( 'gd_info' ) ) {
            return;
        }

        $bg_path = __DIR__ . '/backgrounds/';

        // Default values
        $captcha_config = array(
            'code' => '',
            'min_length'  => 5,
            'max_length'  => 5,
            'backgrounds' => array(
                $bg_path . '45-degree-fabric.png',
                $bg_path . 'cloth-alike.png',
                $bg_path . 'grey-sandbag.png',
                $bg_path . 'kinda-jean.png',
                $bg_path . 'polyester-lite.png',
                $bg_path . 'stitched-wool.png',
                $bg_path . 'white-carbon.png',
                $bg_path . 'white-wave.png'
            ),
            'fonts' => array(
                __DIR__ . '/fonts/veteran_typewriter.ttf'
            ),
            'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
            'min_font_size' => 36,
            'max_font_size' => 36,
            'color' => get_option( 'bookly_app_color' ),
            'angle_min' => 0,
            'angle_max' => 5,
            'shadow' => true,
            'shadow_color' => '#fff',
            'shadow_offset_x' => -1,
            'shadow_offset_y' => 1
        );

        // Overwrite defaults with custom config values
        if ( is_array( $config ) ) {
            foreach( $config as $key => $value ) $captcha_config[ $key ] = $value;
        }

        // Restrict certain values
        if ( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
        if ( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
        if ( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
        if ( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
        if ( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
        if ( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];

        // Generate CAPTCHA code if not set by user
        if ( $captcha_config['code'] == '' ) {
            $length = rand( $captcha_config['min_length'], $captcha_config['max_length'] );
            while ( strlen( $captcha_config['code'] ) < $length ) {
                $captcha_config['code'] .= substr( $captcha_config['characters'], rand() % ( strlen( $captcha_config['characters'] ) ), 1 );
            }
        }

        Lib\Session::setFormVar( $form_id, 'captcha', serialize( $captcha_config ) );
    }

    /**
     * Output a PNG image to either the browser
     *
     * @param $form_id
     */
    public static function draw( $form_id )
    {
        $captcha_config = unserialize( Lib\Session::getFormVar( $form_id, 'captcha' ) );
        if ( ! $captcha_config || ! function_exists( 'gd_info' ) ) {
            return;
        }

        // Pick random background, get info, and start captcha
        $background = $captcha_config['backgrounds'][ rand( 0, count( $captcha_config['backgrounds'] ) - 1 ) ];

        $captcha = imagecreatefrompng( $background );

        $color = self::hex2rgb( $captcha_config['color'] );
        $color = imagecolorallocate( $captcha, $color['r'], $color['g'], $color['b'] );

        // Determine text angle
        $angle = rand( $captcha_config['angle_min'], $captcha_config['angle_max'] ) * ( rand( 0, 1 ) == 1 ? - 1 : 1 );

        // Select font randomly
        $font = $captcha_config['fonts'][ rand( 0, count( $captcha_config['fonts'] ) - 1 ) ];

        //Set the font size.
        $font_size     = rand( $captcha_config['min_font_size'], $captcha_config['max_font_size'] );
        $text_box_size = imagettfbbox( $font_size, $angle, $font, $captcha_config['code'] );

        // Determine text position
        $box_width      = abs( $text_box_size[6] - $text_box_size[2] );
        $box_height     = abs( $text_box_size[5] - $text_box_size[1] );
        $text_pos_x_min = 0;
        $text_pos_x     = $text_pos_x_min + 4;
        $text_pos_y     = $box_height + 10;

        // Draw shadow
        if ( $captcha_config['shadow'] ) {
            $shadow_color = self::hex2rgb( $captcha_config['shadow_color'] );
            $shadow_color = imagecolorallocate( $captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b'] );
            imagettftext( $captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code'] );
        }

        // Draw text
        imagettftext( $captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $captcha_config['code'] );

        // Output image
        header( 'Content-type: image/png' );
        imagepng( $captcha );
    }

    /**
     * Friendly validation
     *
     * @param $form_id
     * @param $value
     * @return bool
     */
    public static function validate( $form_id, $value )
    {
        if ( $captcha = Lib\Session::getFormVar( $form_id, 'captcha', false ) ) {
            $captcha_config = unserialize( $captcha );
            if ( ! $captcha_config ) {
                return true;
            } else {
                return strtolower( $value ) == strtolower( $captcha_config['code'] );
            }
        } else {
            return true;
        }
    }

    /**
     * HEX to RGB.
     *
     * @param $hex_str
     * @param bool|false $return_string
     * @param string $separator
     * @return array|bool|string
     */
    private static function hex2rgb( $hex_str, $return_string = false, $separator = ',' )
    {
        $hex_str = preg_replace( '/[^0-9A-Fa-f]/', '', $hex_str ); // Gets a proper hex string
        $rgb_array = array();
        if ( strlen( $hex_str ) == 6 ) {
            $color_val = hexdec( $hex_str );
            $rgb_array['r'] = 0xFF & ( $color_val >> 0x10 );
            $rgb_array['g'] = 0xFF & ( $color_val >> 0x8 );
            $rgb_array['b'] = 0xFF & $color_val;
        } elseif ( strlen( $hex_str ) == 3 ) {
            $rgb_array['r'] = hexdec( str_repeat( substr( $hex_str, 0, 1 ), 2 ) );
            $rgb_array['g'] = hexdec( str_repeat( substr( $hex_str, 1, 1 ), 2 ) );
            $rgb_array['b'] = hexdec( str_repeat( substr( $hex_str, 2, 1 ), 2 ) );
        } else {

            return false;
        }

        return $return_string ? implode( $separator, $rgb_array ) : $rgb_array;
    }

}