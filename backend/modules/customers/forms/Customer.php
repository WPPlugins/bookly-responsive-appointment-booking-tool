<?php
namespace BooklyLite\Backend\Modules\Customers\Forms;

use BooklyLite\Lib;

/**
 * Class Customer
 * @package BooklyLite\Backend\Modules\Customers\Forms
 */
class Customer extends Lib\Base\Form
{
    protected static $entity_class = 'Customer';

    public function configure()
    {
        $this->setFields( array(
            'name',
            'wp_user_id',
            'phone',
            'email',
            'notes'
        ) );
    }

}
