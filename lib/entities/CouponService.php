<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class CouponService
 * @package BooklyLite\Lib\Entities
 */
class CouponService extends Lib\Base\Entity
{
    protected static $table = 'ab_coupon_services';

    protected static $schema = array(
        'id'          => array( 'format' => '%d' ),
        'coupon_id'   => array( 'format' => '%d', 'default' => 0, 'reference' => array( 'entity' => 'Coupon' ) ),
        'service_id'  => array( 'format' => '%d', 'default' => 0, 'reference' => array( 'entity' => 'Service' ) ),
    );

    protected static $cache = array();

}
