<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_ServiceForm
 */
class AB_ServiceForm extends AB_Form
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::$entity_class = 'AB_Service';
        parent::__construct();
    }

    public function configure()
    {
        $this->setFields( array( 'id', 'title', 'duration', 'price', 'category_id', 'color', 'capacity', 'padding_left', 'padding_right' ) );
    }

    /**
     * Bind values to form.
     *
     * @param array $post
     * @param array $files
     */
    public function bind( array $post, array $files = array() )
    {
        if ( array_key_exists( 'category_id', $post ) && !$post['category_id'] ) {
            $post['category_id'] = null;
        }
        parent::bind( $post, $files );
    }

    public function save()
    {
        if ( $this->isNew() ) {
            // When adding new service - set its color randomly.
            $this->data['color'] = sprintf( '#%06X', mt_rand( 0, 0x64FFFF ) );
        }
        $this->data['capacity'] = 1;

        return parent::save();
    }

}