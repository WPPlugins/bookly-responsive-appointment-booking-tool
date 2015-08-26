<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffService
 */
class AB_StaffService extends AB_Entity
{
    protected static $table = 'ab_staff_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'staff_id'      => array( 'format' => '%d' ),
        'service_id'    => array( 'format' => '%d' ),
        'price'         => array( 'format' => '%.2f', 'default' => '0' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
    );

    /** @var AB_Service */
    public $service = null;
}
