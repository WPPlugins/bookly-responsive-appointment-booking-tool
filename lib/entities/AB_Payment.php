<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Payment
 */
class AB_Payment extends AB_Entity
{
    protected static $table = 'ab_payments';

    protected static $schema = array(
        'id'                        => array( 'format' => '%d' ),
        'created'                   => array( 'format' => '%s' ),
        'type'                      => array( 'format' => '%s', 'default' => 'paypal' ),
        'token'                     => array( 'format' => '%s', 'default' => '' ),
        'transaction'               => array( 'format' => '%s', 'default' => '' ),
        'total'                     => array( 'format' => '%.2f' ),
        'customer_appointment_id'   => array( 'format' => '%d' ),
    );
}