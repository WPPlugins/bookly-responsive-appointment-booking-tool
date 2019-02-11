<?php
namespace Bookly\Lib\Notifications\Assets\Base;

use Bookly\Lib\Utils;

/**
 * Class Codes
 * @package Bookly\Lib\Notifications\Assets\Base
 */
abstract class Codes
{
    /**
     * Do replacements.
     *
     * @param string $text
     * @param string $format
     * @return string
     */
    public function replace( $text, $format = 'text' )
    {
        $codes = $this->getReplaceCodes( $format );

        return strtr( $text, $codes );
    }

    /**
     * Do replacements for SMS.
     * Returns both personal and impersonal text.
     *
     * @param string $text
     * @return array
     */
    public function replaceForSms( $text )
    {
        $codes = $this->getReplaceCodes( 'text' );

        // Impersonal codes.
        $impersonal_codes = array();
        foreach ( $codes as $name => $code ) {
            $count = Utils\SMSCounter::count( strval( $code ) );
            if ( $count->encoding == Utils\SMSCounter::UTF16 ) {
                $impersonal_symbol = "ϔ";
            } else {
                $impersonal_symbol = "X";
            }
            $impersonal_codes[ $name ] = preg_replace( '/[^\s]/', $impersonal_symbol, $code );
        }

        return array(
            'personal'   => strtr( $text, $codes ),
            'impersonal' => strtr( $text, $impersonal_codes ),
        );
    }

    /**
     * Get replacement codes for given format.
     *
     * @param string $format
     * @return array
     */
    protected function getReplaceCodes( $format )
    {
        $company_logo = '';

        if ( $format == 'html' ) {
            $img = wp_get_attachment_image_src( get_option( 'bookly_co_logo_attachment_id' ), 'full' );
            // Company logo as <img> tag.
            if ( $img ) {
                $company_logo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $img[0] ),
                    esc_attr( get_option( 'bookly_co_name' ) )
                );
            }
        }

        return array(
            '{company_address}' => $format == 'html' ? nl2br( get_option( 'bookly_co_address' ) ) : get_option( 'bookly_co_address' ),
            '{company_logo}'    => $company_logo,
            '{company_name}'    => get_option( 'bookly_co_name' ),
            '{company_phone}'   => get_option( 'bookly_co_phone' ),
            '{company_website}' => get_option( 'bookly_co_website' ),
        );
    }
}