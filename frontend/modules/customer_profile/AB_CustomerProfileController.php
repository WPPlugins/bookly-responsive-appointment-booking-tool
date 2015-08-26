<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_CustomerProfileController
 */
class AB_CustomerProfileController extends AB_Controller {

    public function renderShortCode( $attributes )
    {
        $this->enqueueStyles( array(
            'module' => array(
                'css/customer_profile.css'
            )
        ) );

        $this->customer = new AB_Customer();
        $this->customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );

        $this->appointments = $this->customer->getAppointmentsForProfile();

        return $this->render( 'short_code', array( 'attr' => $attributes ), false );
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