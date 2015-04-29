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
            'id'              => array(),
            'staff_id'        => array( 'format' => '%d' ),
            'service_id'      => array( 'format' => '%d' ),
            'start_date'      => array( 'format' => '%s' ),
            'end_date'        => array( 'format' => '%s' ),
            'google_event_id' => array( 'format' => '%s' ),
        );
        parent::__construct();
    }

    /**
     * Get color of service
     *
     * @param string $default
     * @return string
     */
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

    /**
     * Get array of customers, signed for this appointment
     *
     * @return array
     */
    public function getCustomers(){
        $result = array();

        if ($this->get( 'id' )){
            $customers = $this->wpdb->get_results($this->wpdb->prepare(
                'SELECT c.*, ca.token, ca.notes
                 FROM `ab_customer_appointment` ca
                 LEFT JOIN `ab_customer` c on c.id = ca.customer_id
                 WHERE appointment_id = %d',
                $this->get('id')
            ));

            foreach($customers as $customer){
                $result[$customer->id] = $customer;
            }
        }

        return $result;
    }

    /**
     * Delete entity from database.
     *
     * @return bool|false|int
     */
    public function delete(){
        $parent_result = parent::delete();
        if ($parent_result && $this->get('google_event_id')){
            $google = new AB_Google();
            $google->loadByStaffId($this->get('staff_id'));
            $google->delete($this->get('google_event_id'));

            return $parent_result;
        }else{
            return false;
        }
    }
}
