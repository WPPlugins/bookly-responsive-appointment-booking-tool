<?php
namespace BooklyLite\Lib;

abstract class API
{
    const API_URL = 'http://api.booking-wp-plugin.com/1.0';

    /**
     * Register subscriber.
     *
     * @param string $email
     * @return bool
     */
    public static function registerSubscriber( $email )
    {
        try {
            $url = self::API_URL . '/subscribers';
            $curl = new Curl\Curl();
            $curl->options['CURLOPT_HEADER']  = 0;
            $curl->options['CURLOPT_TIMEOUT'] = 25;
            $data = array( 'email' => $email, 'site_url' => site_url() );
            $response = json_decode( $curl->post( $url, $data ), true );
            if ( $response instanceof \WP_Error ) {

            } elseif ( isset( $response['success'] ) && $response['success'] ) {
                return true;
            }
        } catch ( \Exception $e ) {

        }

        return false;
    }

}