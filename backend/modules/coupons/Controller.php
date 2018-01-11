<?php
namespace BooklyLite\Backend\Modules\Coupons;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Coupons
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-coupons';

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

        wp_localize_script( 'bookly-coupons.js', 'BooklyL10n', array(
            'edit'         => __( 'Edit', 'bookly' ),
            'zeroRecords'  => __( 'No coupons found.', 'bookly' ),
            'processing'   => __( 'Processing...', 'bookly' ),
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'selector' => array(
                'all_selected'      => __( 'All services', 'bookly' ),
                'nothing_selected'  => __( 'No service selected', 'bookly' ),
                'collection' => array(),
            ),
            'limitations'    => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        $this->render( 'index' );
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_bookly_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}