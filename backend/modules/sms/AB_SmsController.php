<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_SmsController
 */
class AB_SmsController extends AB_Controller {

    const page_slug = 'ab-sms';

    public function index()
    {
        $this->enqueueStyles(
            array(
                'backend' => array(
                    'css/bookly.main-backend.css',
                    'bootstrap/css/bootstrap.min.css',
                ),
                'module'  => array(
                    'css/sms.css',
                    'css/flags.css',
                )
            )
        );

        $this->enqueueScripts(
            array(
                'backend' => array(
                    'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                ),
                'module'  => array(
                    'js/sms.js' => array( 'jquery' ),
                ),
            )
        );

        $this->prices       = array();
        $this->sms          = new AB_SMS();

        if( $response = $this->sms->getPriceList() ){
            $this->prices = $response->list;
        }

        $this->render( 'index' );
    } // index

}