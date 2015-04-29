<?php

/**
 * Class AB_Category
 */
class AB_Category extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_category';
        $this->schema = array(
            'id'    => array(),
            'name'  => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    /**
     * @var AB_Service[]
     */
    private $services;

    public function addService(AB_Service $service) {
        $this->services[] = $service;
    }

    /**
     * @return AB_Service[]
     */
    public function getServices() {
        return $this->services;
    }
}
