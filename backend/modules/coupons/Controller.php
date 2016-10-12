<?php
namespace BooklyLite\Backend\Modules\Coupons;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Coupons
 */
class Controller extends Lib\Base\Controller
{
    /**
     * Default action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array( 'js/coupons.js' => array( 'jquery' ) )
        ) );

        wp_localize_script( 'ab-coupons.js', 'BooklyL10n', array(
            'edit'         => __( 'Edit', 'bookly' ),
            'zeroRecords'  => __( 'No coupons found.', 'bookly' ),
            'processing'   => __( 'Processing...', 'bookly' ),
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'selector' => array(
                'all_selected'      => __( 'All services', 'bookly' ),
                'nothing_selected'  => __( 'No service selected', 'bookly' ),
                'collection' => array(),
            ),
            'limitations'  => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>If you find the plugin useful for your business please consider buying a licence for the full version.<br>It costs just $59 and for this money you will get many useful functions, lifetime free updates and excellent support!<br>More information can be found here', 'bookly' ) . ': <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        $this->render( 'index' );
    }

    // Protected methods.

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