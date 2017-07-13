<?php
namespace BooklyLite\Backend\Modules\Coupons\Forms;

use BooklyLite\Lib;

/**
 * Class Coupon
 * @package BooklyLite\Backend\Modules\Coupons\Forms
 */
class Coupon extends Lib\Base\Form
{
    protected static $entity_class = 'Coupon';

    public function configure()
    {
        $this->setFields( array( 'id', 'code', 'discount', 'deduction', 'usage_limit' ) );
    }

}