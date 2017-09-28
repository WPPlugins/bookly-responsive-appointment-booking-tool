<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class StaffService
 * @package BooklyLite\Lib\Entities
 */
class StaffService extends Lib\Base\Entity
{
    protected static $table = 'ab_staff_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'staff_id'      => array( 'format' => '%d', 'reference' => array( 'entity' => 'Staff' ) ),
        'service_id'    => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service' ) ),
        'price'         => array( 'format' => '%f', 'default' => '0' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        'deposit'       => array( 'format' => '%s', 'default' => '100%' ),
    );

    protected static $cache = array();

    /** @var Service */
    public $service = null;

}