<?php
namespace Bookly\Backend\Components\Dialogs\Payment;

use Bookly\Lib;

/**
 * Class Details
 * @package Bookly\Backend\Components\Dialogs\Payment
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render payment details dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend' => array( 'js/angular.min.js' => array( 'jquery' ), ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array( 'js/ng-payment_details.js' => array( 'bookly-angular.min.js' ), ),
        ) );

        wp_localize_script( 'bookly-ng-payment_details.js', 'BooklyPaymentDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
        ) );

        self::renderTemplate( 'dialog' );
    }
}