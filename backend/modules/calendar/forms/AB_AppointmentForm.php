<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH.'/lib/entities/AB_Appointment.php';
include AB_PATH.'/lib/entities/AB_Customer_Appointment.php';

/**
 * Class AB_AppointmentForm
 */
class AB_AppointmentForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_Appointment';
        parent::__construct();
    }

    public function configure() {
        //$this->setFields( array() );
    }
}
