<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$codes = array(
    array( 'code' => 'cart_info',       'description' => __( 'cart information', 'bookly' ) ),
    array( 'code' => 'cart_info_c',     'description' => __( 'cart information with cancel', 'bookly' ) ),
    array( 'code' => 'client_email',    'description' => __( 'email of client', 'bookly' ) ),
    array( 'code' => 'client_name',     'description' => __( 'name of client', 'bookly' ) ),
    array( 'code' => 'client_phone',    'description' => __( 'phone of client', 'bookly' ) ),
    array( 'code' => 'company_name',    'description' => __( 'name of company', 'bookly' ) ),
    array( 'code' => 'company_logo',    'description' => __( 'company logo', 'bookly' ) ),
    array( 'code' => 'company_address', 'description' => __( 'address of company', 'bookly' ) ),
    array( 'code' => 'company_phone',   'description' => __( 'company phone', 'bookly' ) ),
    array( 'code' => 'company_website', 'description' => __( 'company web-site address', 'bookly' ) ),
    array( 'code' => 'payment_type',    'description' => __( 'payment type', 'bookly' ) ),
    array( 'code' => 'total_price',     'description' => __( 'total price of booking (sum of all cart items after applying coupon)', 'bookly' ) ),
);
\BooklyLite\Lib\Utils\Common::Codes( apply_filters( 'bookly_prepare_cart_notification_short_codes', $codes ) );