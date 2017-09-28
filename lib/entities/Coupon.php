<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Coupon
 * @package BooklyLite\Lib\Entities
 */
class Coupon extends Lib\Base\Entity
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

    protected static $cache = array();

    /**
     * Apply coupon.
     *
     * @param $amount
     * @return float
     */
    public function apply( $amount )
    {
        $amount = round( $amount * ( 100 - $this->get( 'discount' ) ) / 100 - $this->get( 'deduction' ), 2 );

        return $amount > 0 ? $amount : 0;
    }

    /**
     * Increase the number of times the coupon has been used.
     *
     * @param int $quantity
     */
    public function claim( $quantity = 1 )
    {
        $this->set( 'used', $this->get( 'used' ) + $quantity );
    }

    /**
     * It's valid if the contains at least one service with  an applicable coupon.
     *
     * @param array $service_ids
     * @return bool
     */
    public function valid( array $service_ids )
    {
        return null !== Lib\Entities\CouponService::query()->whereIn( 'service_id', $service_ids )->where( 'coupon_id', $this->get( 'id' ) )->fetchRow();
    }

}