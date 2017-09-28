<?php
namespace BooklyLite\Backend\Modules\Settings\Forms;

use BooklyLite\Lib;

/**
 * Class Payments
 * @package BooklyLite\Backend\Modules\Settings
 */
class Payments extends Lib\Base\Form
{
    public function __construct()
    {
        $this->setFields( array(
            'bookly_pmt_currency',
            'bookly_pmt_local',
        ) );
    }

    public function save()
    {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }

}