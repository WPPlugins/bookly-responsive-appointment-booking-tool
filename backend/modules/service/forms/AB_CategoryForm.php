<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CategoryForm
 */
class AB_CategoryForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_Category';
        parent::__construct();
    }

    /**
     * Configure the form.
     */
    public function configure() {
        $this->setFields( array( 'name' ) );
    }

}
