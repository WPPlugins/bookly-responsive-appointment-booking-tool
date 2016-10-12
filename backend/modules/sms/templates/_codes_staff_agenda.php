<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$codes = array(
    array( 'code' => 'tomorrow_date',   'description' => __( 'date of next day', 'bookly' ) ),
    array( 'code' => 'next_day_agenda', 'description' => __( 'staff agenda for next day', 'bookly' ) ),
    array( 'code' => 'staff_name',      'description' => __( 'name of staff', 'bookly' ) ),
);
\BooklyLite\Lib\Utils\Common::Codes( $codes );