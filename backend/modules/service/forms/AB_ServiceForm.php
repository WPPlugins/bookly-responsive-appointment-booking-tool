<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include AB_PATH . '/lib/entities/AB_Service.php';

/**
 * Class AB_ServiceForm
 */
class AB_ServiceForm extends AB_Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::$entity_class = 'AB_Service';
        parent::__construct();
    }

    public function configure() {
        $this->setFields(array('id', 'title', 'duration', 'price', 'category_id', 'color', 'capacity'));
    }

    /**
     * Bind values to form.
     *
     * @param array $post
     * @param array $files
     */
    public function bind( array $post, array $files = array() ) {
        if ( array_key_exists('category_id', $post) && !$post['category_id'] ) {
            $post['category_id'] = null;
        }
        parent::bind($post, $files);
    }

    public function save() {
        if ( $this->isNew() ) {
            $colors = array('#B0171F', '#DA70D6', '#BF3EFF', '#8470FF', '#4876FF', '#63B8FF', '#00B2EE', '#00F5FF', '#00C78C', '#BDFCC9', '#B4EEB4', '#7CFC00', '#ADFF2F', '#FFFFF0', '#CDCDB4', '#FFFF00', '#FFF68F', '#F0E68C', '#E3CF57', '#FFEBCD', '#FF8C00', '#EE4000', '#FA8072', '#F08080', '#CD0000');
            // when adding new service - set its color randomly
            $this->data[ 'color' ] = $colors[mt_rand(0, count($colors) - 1)];
        }

        $this->data['capacity'] = 1;

        return parent::save();
    }
}