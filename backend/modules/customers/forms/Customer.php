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
            'wp_user_id',
            'full_name',
            'first_name',
            'last_name',
            'phone',
            'email',
            'notes',
            'birthday',
        ) );
    }

}
