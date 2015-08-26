<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Holiday
 */
class AB_Holiday extends AB_Entity
{
    protected static $table = 'ab_holidays';

    protected static $schema = array(
        'id'           => array( 'format' => '%d' ),
        'staff_id'     => array( 'format' => '%d' ),
        'parent_id'    => array( 'format' => '%d' ),
        'date'         => array( 'format' => '%s' ),
        'repeat_event' => array( 'format' => '%s' ),
        'title'        => array( 'format' => '%s', 'default' => '' ),
    );

}
