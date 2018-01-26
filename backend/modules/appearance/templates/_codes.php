<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use BooklyLite\Lib;

$codes = array(
    array( 'code' => 'appointments_count', 'description' => __( 'total quantity of appointments in cart', 'bookly' ), 'flags' => array( 'step' => 7, 'extra_codes' => true ) ),
    array( 'code' => 'booking_number',     'description' => __( 'booking number', 'bookly' ),                         'flags' => array( 'step' => 8, 'extra_codes' => true ) ),
    array( 'code' => 'category_name',      'description' => __( 'name of category', 'bookly' ) ),
    array( 'code' => 'login_form',         'description' => __( 'login form', 'bookly' ),                             'flags' => array( 'step' => 6, 'extra_codes' => true ) ),
    array( 'code' => 'service_date',       'description' => __( 'date of service', 'bookly' ),                        'flags' => array( 'step' => '>3' ) ),
    array( 'code' => 'service_info',       'description' => __( 'info of service', 'bookly' ) ),
    array( 'code' => 'service_name',       'description' => __( 'name of service', 'bookly' ) ),
    array( 'code' => 'service_price',      'description' => __( 'price of service', 'bookly' ) ),
    array( 'code' => 'service_time',       'description' => __( 'time of service', 'bookly' ),                        'flags' => array( 'step' => '>3' ) ),
    array( 'code' => 'staff_info',         'description' => __( 'info of staff', 'bookly' ) ),
    array( 'code' => 'staff_name',         'description' => __( 'name of staff', 'bookly' ) ),
    array( 'code' => 'total_price',        'description' => __( 'total price of booking', 'bookly' ) ),
);

Lib\Utils\Common::codes( Lib\Proxy\Shared::prepareAppearanceCodes( $codes ), array( 'step' => $step, 'extra_codes' => isset ( $extra_codes ) ) );