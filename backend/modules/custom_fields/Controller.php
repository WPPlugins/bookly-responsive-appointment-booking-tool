<?php
namespace BooklyLite\Backend\Modules\CustomFields;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\CustomFields
 */
class Controller extends Lib\Base\Controller
{
    /**
     *  Default Action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'module'   => array( 'js/custom_fields.js' => array( 'jquery-ui-sortable' ) ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
        ) );

        wp_localize_script( 'ab-custom_fields.js', 'BooklyL10n', array(
            'custom_fields' => get_option( 'ab_custom_fields' ),
            'saved'    => __( 'Settings saved.', 'bookly' ),
            'selector' => array(
                'all_selected'     => __( 'All services', 'bookly' ),
                'nothing_selected' => __( 'No service selected', 'bookly' ),
            ),
            'limitations' => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>If you find the plugin useful for your business please consider buying a licence for the full version.<br>It costs just $59 and for this money you will get many useful functions, lifetime free updates and excellent support!<br>More information can be found here', 'bookly' ) . ': <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        $services = $this->render( '_services', array( 'services' => Lib\Entities\Service::query()->select( 'id, title' )->where( 'type', Lib\Entities\Service::TYPE_SIMPLE )->fetchArray() ), false );
        $this->render( 'index', array( 'services_html' => $services ) );
    }

    /**
     * Save custom fields.
     */
    public function executeSaveCustomFields()
    {
        $custom_fields = $this->getParameter( 'fields' );
        foreach ( json_decode( $custom_fields ) as $custom_field ) {
            switch ( $custom_field->type ) {
                case 'textarea':
                case 'text-content':
                case 'text-field':
                case 'captcha':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' .sanitize_title( $custom_field->label ), $custom_field->label );
                    break;
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ), $custom_field->label );
                    foreach ( $custom_field->items as $label ) {
                        do_action( 'wpml_register_single_string', 'bookly', 'custom_field_' . $custom_field->id . '_' . sanitize_title( $custom_field->label ) . '=' . sanitize_title( $label ), $label );
                    }
                    break;
            }
        }
        update_option( 'ab_custom_fields', $custom_fields );
        update_option( 'ab_custom_fields_per_service', '0' );
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