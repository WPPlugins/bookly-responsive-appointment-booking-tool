<?php
namespace BooklyLite\Backend\Modules\Settings\Forms;

use \BooklyLite\Lib;

/**
 * Class Payments
 * @package BooklyLite\Backend\Modules\Settings
 */
class Payments extends Lib\Base\Form
{
    public function __construct()
    {
        $this->setFields( array(
            'ab_currency',
            'ab_settings_pay_locally',
        ) );
    }

    public function save()
    {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }

}