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
    }

    public function bind( array $_post, array $files = array() )
    {
        $fields = Lib\Proxy\Shared::preparePaymentOptions( array(
            'bookly_pmt_currency',
            'bookly_pmt_price_format',
            'bookly_pmt_local',
        ) );

        $_post = Lib\Proxy\Shared::preparePaymentOptionsData( $_post );

        $this->setFields( $fields );
        parent::bind( $_post, $files );
    }

    public function save()
    {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }

}