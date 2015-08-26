<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Notifications
 */
class AB_Notification extends AB_Entity
{
    protected static $table = 'ab_notifications';

    protected static $schema = array(
        'id'      => array( 'format' => '%d' ),
        'gateway' => array( 'format' => '%s', 'default' => 'email' ),
        'type'    => array( 'format' => '%s', 'default' => '' ),
        'active'  => array( 'format' => '%d', 'default' => 0 ),
        'copy'    => array( 'format' => '%d', 'default' => 0 ),
        'subject' => array( 'format' => '%s', 'default' => '' ),
        'message' => array( 'format' => '%s', 'default' => '' ),
    );
}
