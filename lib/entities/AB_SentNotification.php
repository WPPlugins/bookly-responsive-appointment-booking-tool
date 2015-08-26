<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_SentNotification
 */
class AB_SentNotification extends AB_Entity
{
    protected static $table = 'ab_sent_notifications';

    protected static $schema = array(
        'id'                      => array( 'format' => '%d' ),
        'customer_appointment_id' => array( 'format' => '%d' ),
        'staff_id'                => array( 'format' => '%d' ),
        'gateway'                 => array( 'format' => '%s', 'default' => 'email' ),
        'type'                    => array( 'format' => '%s' ),
        'created'                 => array( 'format' => '%s' ),
    );
}