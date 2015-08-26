<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CouponsController
 */
class AB_CouponsController extends AB_Controller {

    /**
     * Default action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            ),
            'module' => array(
                'css/coupons.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'js/ab_popup.js' => array( 'jquery' ),
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/coupons.js' => array( 'jquery' ),
            )
        ) );

        wp_localize_script( 'ab-coupons.js', 'BooklyL10n', array(
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'please_select_at_least_one_coupon' => __( 'Please select at least one coupon.', 'bookly' ),
        ) );

        $this->coupons_collection  = false;

        $this->render( 'index' );
    }

}