<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_PaymentsForm extends AB_Form {

    public function __construct() {
        $this->setFields(array(
            'ab_settings_pay_locally',
            'ab_paypal_currency',
        ));
    }

    public function save() {
        foreach ( $this->data as $field => $value ) {
            update_option( $field, $value );
        }
    }
}