<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$codes = array(
    array( 'code' => 'appointment_date',  'description' => __( 'date of appointment', 'bookly' ), ),
    array( 'code' => 'appointment_time',  'description' => __( 'time of appointment', 'bookly' ), ),
    array( 'code' => 'category_name',     'description' => __( 'name of category', 'bookly' ), ),
    array( 'code' => 'number_of_persons', 'description' => __( 'number of persons', 'bookly' ), ),
    array( 'code' => 'service_info',      'description' => __( 'info of service', 'bookly' ), ),
    array( 'code' => 'service_name',      'description' => __( 'name of service', 'bookly' ), ),
    array( 'code' => 'service_price',     'description' => __( 'price of service', 'bookly' ), ),
    array( 'code' => 'staff_info',        'description' => __( 'info of staff', 'bookly' ), ),
    array( 'code' => 'staff_name',        'description' => __( 'name of staff', 'bookly' ), ),
);
\BooklyLite\Lib\Utils\Common::Codes( apply_filters( 'bookly_woocommerce_short_codes', $codes ) );