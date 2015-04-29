<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH . '/lib/entities/AB_Category.php';
include AB_PATH . '/lib/entities/AB_StaffService.php';

/**
 * Class AB_StaffServicesForm
 */
class AB_StaffServicesForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_StaffService';
        parent::__construct();
    }

    /**
     * @var wpdb $wpdb
     */
    private $wpdb;

    /**
     * @var AB_Category[]
     */
    private $collection = array();

    /**
     * @var array
     */
    private $selected = array();

    /**
     * @var array
     */
    private $category_services = array();

    /**
     * @var array
     */
    private $uncategorized_services = array();

    public function configure() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->setFields( array( 'price', 'service', 'staff_id', 'capacity' ) );
    }

    public function load($staff_id) {
        $data = $this->wpdb->get_results( '
            SELECT c.name AS category_name, s.*
            FROM ab_category c
            INNER JOIN ab_service s ON c.id = s.category_id
        ', ARRAY_A );

        if ( !$data ) {
            $data = array();
        }

        $uncategorized_services = $this->wpdb->get_results( 'SELECT * FROM ab_service WHERE category_id IS NULL' );
        foreach ( $uncategorized_services as $uncategorized_service ) {
            $abService = new AB_Service();
            $abService->setData($uncategorized_service);

            $this->uncategorized_services[] = $abService;
        }

        $rows = $this->wpdb->get_results( $this->wpdb->prepare('
            SELECT s.service_id, s.price, s.capacity
            FROM ab_staff_service s
            WHERE s.staff_id = %d
        ', $staff_id) );

        if ( $rows ) {
            foreach ($rows as $row) {
                $this->selected[$row->service_id] = array('price' => $row->price, 'capacity' => $row->capacity);
            }
        }

        foreach ($data as $row) {
            if ( !isset($this->collection[ $row['category_id'] ]) ) {
                $abCategory = new AB_Category();
                $abCategory->set( 'id', $row['category_id'] );
                $abCategory->set( 'name', $row['category_name'] );
                $this->collection[ $row['category_id'] ] = $abCategory;
            }
            unset( $row['category_name'] );

            $abService = new AB_Service();
            $abService->setData($row);
            $this->category_services[$row['category_id']][] = $abService->get( 'id' );
            $this->collection[ $row['category_id'] ]->addService($abService);
        }
    }

    public function save() {
        $staff_id = $this->data['staff_id'];
        if ( $staff_id ) {
            $this->wpdb->delete( 'ab_staff_service', array( 'staff_id' => $staff_id ), array( '%d' ) );
            if ( isset($this->data['service']) ) {
                foreach ( $this->data['service'] as $service_id ) {
                    $staffService = new AB_StaffService();
                    $staffService->set( 'service_id', $service_id );
                    $staffService->set( 'staff_id', $staff_id );
                    $staffService->set( 'price', $this->data['price'][ $service_id ] );
                    $staffService->save();
                }
            }
        }
    }

    /**
     * @return AB_Category[]|array
     */
    public function getCollection() {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getSelected() {
        return $this->selected;
    }

    /**
     * @return array
     */
    public function getUncategorizedServices()
    {
      return $this->uncategorized_services;
    }
}
