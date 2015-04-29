<?php

/**
 * Class AB_StaffService
 */
class AB_StaffService extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_staff_service';
        $this->schema = array(
            'id'            => array( ),
            'staff_id'      => array( 'format' => '%d' ),
            'service_id'    => array( 'format' => '%d' ),
            'price'         => array( 'format' => '%.2f', 'default' => '0' ),
            'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        );
        parent::__construct();
    }

    /**
     * Load data by staff and service
     *
     * @param integer $staff_id
     * @param integer $service_id
     * @return boolean
     */
    public function loadByStaffAndService( $staff_id, $service_id ) {
        $row = $this->wpdb->get_row( sprintf( 'SELECT * FROM %s WHERE staff_id = %d and service_id = %d', $this->table_name, $staff_id, $service_id) );

        if ( $row ) {
            $this->setData( $row );
            $this->loaded = true;
        } else {
            $this->loaded = false;
        }

        return $this->loaded;
    }
}
