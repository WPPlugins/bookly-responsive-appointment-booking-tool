<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CustomFieldsController
 */
class AB_CustomFieldsController extends AB_Controller {

    /**
     *  Default Action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'module' => array(
                'css/custom_fields.css'
            ),
            'frontend' => array(
                'css/ladda.min.css'
            ),
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            )
        ) );

        $this->enqueueScripts( array(
            'module' => array(
                'js/custom_fields.js' => array( 'jquery-ui-sortable' )
            ),
            'frontend' => array(
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        wp_localize_script( 'ab-custom_fields.js', 'BooklyL10n', array(
            'custom_fields' => get_option( 'ab_custom_fields' )
        ) );

        $this->render( 'index' );
    } // index

    /**
     * Save custom fields.
     */
    public function executeSaveCustomFields()
    {
        update_option( 'ab_custom_fields', $this->getParameter( 'fields' ) );

        wp_send_json_success();
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}