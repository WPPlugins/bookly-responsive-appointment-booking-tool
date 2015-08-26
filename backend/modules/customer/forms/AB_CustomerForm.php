<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CustomerForm
 */
class AB_CustomerForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::$entity_class = 'AB_Customer';
        parent::__construct();
    }

    public function configure()
    {
        $this->setFields( array(
            'name',
            'wp_user_id',
            'phone',
            'email',
            'notes'
        ) );
    }

}
