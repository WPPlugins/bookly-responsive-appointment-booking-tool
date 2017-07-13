<?php
namespace BooklyLite\Backend\Modules\Payments;

use BooklyLite\Lib;

/**
 * Class Components
 * @package BooklyLite\Backend\Modules\Payments
 */
class Components extends Lib\Base\Components
{
    /**
     * Render payment details dialog.
     * @throws \Exception
     */
    public function renderPaymentDetailsDialog()
    {
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array( 'js/angular.min.js' => array( 'jquery' ), ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array( 'js/ng-payment_details_dialog.js' => array( 'bookly-angular.min.js' ), ),
        ) );

        $this->render( '_payment_details_dialog' );
    }

}