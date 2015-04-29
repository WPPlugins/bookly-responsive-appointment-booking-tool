<?php

/**
 * Class AB_Appointment
 */
class AB_Appointment extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_appointment';
        $this->schema = array(
            'id'          => array(),
            'staff_id'    => array( 'format' => '%d' ),
            'service_id'  => array( 'format' => '%d' ),
            'customer_id' => array( 'format' => '%d' ),
            'start_date'  => array( 'format' => '%s' ),
            'end_date'    => array( 'format' => '%s' ),
            'notes'       => array( 'format' => '%s' ),
            'token'       => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    public function getColor( $default = '#DDDDDD' ) {
        if ( ! $this->loaded ) {
            return $default;
        }

        $appointmentService = $this->wpdb->get_row( $this->wpdb->prepare(
            'SELECT * FROM `ab_service` WHERE id = %d',
            $this->get( 'service_id' )
        ) );

        if ( $appointmentService ) {
            return $appointmentService->color;
        }

        return $default;
    }
}
