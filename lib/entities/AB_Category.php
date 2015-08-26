<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Category
 */
class AB_Category extends AB_Entity
{
    protected static $table = 'ab_categories';

    protected static $schema = array(
        'id'        => array( 'format' => '%d' ),
        'name'      => array( 'format' => '%s' ),
        'position'  => array( 'format' => '%d', 'default' => 9999 ),
    );

    /**
     * @var AB_Service[]
     */
    private $services;

    public function addService(AB_Service $service)
    {
        $this->services[] = $service;
    }

    /**
     * @return AB_Service[]
     */
    public function getServices()
    {
        return $this->services;
    }
}
