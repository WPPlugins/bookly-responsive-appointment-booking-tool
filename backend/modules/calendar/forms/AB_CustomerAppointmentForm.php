<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_AppointmentForm
 */
class AB_CustomerAppointmentForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::$entity_class = 'AB_CustomerAppointment';
        parent::__construct();
    }

}
