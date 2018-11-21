<?php
namespace Bookly\Backend\Components\Dialogs\Customer\Delete;

use Bookly\Lib;
use Bookly\Backend\Components\Controls\Buttons;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Customer\Delete
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render customer dialog.
     */
    public static function render()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/delete-customers.js' => array( 'jquery' ), )
        ) );

        wp_localize_script( 'bookly-delete-customers.js', 'BooklyL10nCustomerDelete', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
        ) );

        static::renderTemplate( 'dialog' );
    }

    /**
     * Render delete button on page (sub Customers table)
     */
    public static function renderDeleteButton()
    {
        Buttons::renderDelete();
    }
}