<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Coupon
 */
class AB_Coupon extends AB_Entity
{
    protected static $table = 'ab_coupons';

    protected static $schema = array(
        'id'          => array( 'format' => '%d' ),
        'code'        => array( 'format' => '%s', 'default' => '' ),
        'discount'    => array( 'format' => '%d', 'default' => 0 ),
        'deduction'   => array( 'format' => '%d', 'default' => 0 ),
        'usage_limit' => array( 'format' => '%d', 'default' => 1 ),
        'used'        => array( 'format' => '%d', 'default' => 0 ),
    );

}
