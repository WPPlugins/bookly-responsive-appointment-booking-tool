<?php

/**
 * Class AB_Customer_Appointment
 */
class AB_Customer_Appointment extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_customer_appointment';
        $this->schema = array(
            'id'             => array( ),
            'customer_id'    => array( 'format' => '%d'),
            'appointment_id' => array( 'format' => '%d'),
            'notes'          => array( 'format' => '%s' ),
            'token'          => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    /**
     * Load data by appointment_id
     *
     * @param integer $appointment_id
     * @param integer $customer_id
     * @return boolean
     */
    public function loadByAppointmentAndCustomer($appointment_id, $customer_id) {
        $row = $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT * FROM `{$this->table_name}` WHERE appointment_id = %d AND customer_id = %d",
            $appointment_id,
            $customer_id
        ) );

        if ($row) {
            $this->setData( $row );
            $this->loaded = true;
        } else {
            $this->loaded = false;
        }

        return $this->loaded;
    }


    /**
     * Load data by appointment_id
     *
     * @param string $token
     *
     * @return boolean
     */
    public function loadByToken($token) {
        $row = $this->wpdb->get_row( $this->wpdb->prepare(
            "SELECT * from `{$this->table_name}` WHERE token = %s",
            $token
        ) );

        if ($row) {
            $this->setData( $row );
            $this->loaded = true;
        } else {
            $this->loaded = false;
        }

        return $this->loaded;
    }

}