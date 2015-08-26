<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffServicesForm
 */
class AB_StaffServicesForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct()
    {
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

    public function configure()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->setFields( array( 'price', 'service', 'staff_id', 'capacity' ) );
    }

    public function load( $staff_id )
    {
        $data = AB_Category::query( 'c' )->select( 'c.name AS category_name, s.*' )->innerJoin( 'AB_Service', 's', 's.category_id = c.id' )->fetchArray();
        if ( !$data ) {
            $data = array();
        }

        $this->uncategorized_services = AB_Service::query( 's' )->where( 's.category_id', null )->fetchArray();

        $staff_services = AB_StaffService::query( 'ss' )
            ->select( 'ss.service_id, ss.price, ss.capacity' )
            ->where( 'ss.staff_id', $staff_id )
            ->fetchArray();
        if ( $staff_services ) {
            foreach ( $staff_services as $staff_service ) {
                $this->selected[ $staff_service['service_id'] ] = array( 'price' => $staff_service['price'], 'capacity' => $staff_service['capacity'] );
            }
        }

        foreach ( $data as $row ) {
            if ( ! isset( $this->collection[ $row['category_id'] ] ) ) {
                $abCategory = new AB_Category();
                $abCategory->set( 'id', $row['category_id'] );
                $abCategory->set( 'name', $row['category_name'] );
                $this->collection[ $row['category_id'] ] = $abCategory;
            }
            unset( $row['category_name'] );

            $abService = new AB_Service( $row );
            $this->category_services[ $row['category_id'] ][] = $abService->get( 'id' );
            $this->collection[ $row['category_id'] ]->addService( $abService );
        }
    }

    public function save()
    {
        $staff_id = $this->data['staff_id'];
        if ( $staff_id ) {
            AB_StaffService::query()->delete()->where( 'staff_id', $staff_id )->execute();
            if ( isset( $this->data['service'] ) ) {
                foreach ( $this->data['service'] as $service_id ) {
                    $staffService = new AB_StaffService();
                    $staffService->set( 'service_id', $service_id );
                    $staffService->set( 'staff_id', $staff_id );
                    $staffService->set( 'price', $this->data['price'][ $service_id ] );
                    $staffService->set( 'capacity', $this->data['capacity'][ $service_id ] );
                    $staffService->save();
                }
            }
        }
    }

    /**
     * @return AB_Category[]|array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getSelected()
    {
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
