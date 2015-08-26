<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_SMS
 */
class AB_SMS {

    const API_URL = 'http://sms.booking-wp-plugin.com/1.0';

    const GET_PRICES          = '/prices.json';                   //GET

    private $token;


    private $errors = array();

    public function __construct()
    {

    }

    /**
     * Get Price list.
     *
     * @return array|mixed
     */
    public function getPriceList()
    {
            $response = $this->sendGetRequest( self::GET_PRICES );
            if ( $response ) {

                return $response;
            }

        return array();
    }

    /**
     * Send GET request.
     *
     * @param $path
     * @param array $data
     * @return mixed
     */
    private function sendGetRequest( $path, array $data = array() )
    {
        $url = self::API_URL . str_replace( '%token%', $this->token, $path );

        return $this->_handleResponse( $this->_sendRequest( 'GET', $url, $data ) );
    }

    /**
     * Send HTTP request.
     *
     * @param $method
     * @param $url
     * @param $data
     *
     * @return mixed
     */
    private function _sendRequest( $method, $url, $data )
    {
        $curl = new AB_Curl();
        $curl->options['CURLOPT_CONNECTTIMEOUT'] = 8;
        $curl->options['CURLOPT_TIMEOUT']        = 30;

        $method   = strtolower( $method );
        $response = $curl->$method( $url, $data );
        $error = $curl->error();
        if ( $error ) {
            $this->errors[] = $error;
        }

        return $response;
    }

    /**
     * Check response for errors.
     *
     * @param mixed $response
     * @return mixed
     */
    private function _handleResponse( $response )
    {
        $response = json_decode( $response );

        if ( $response !== null && property_exists( $response, 'success' ) ) {
            if ( $response->success == true ) {

                return $response;
            }
        } else {
            $this->errors[] = __( 'Error connecting to server.', 'bookly' );
        }

        return false;
    }

}