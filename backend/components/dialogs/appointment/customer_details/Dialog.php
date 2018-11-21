<?php
namespace Bookly\Backend\Components\Dialogs\Appointment\CustomerDetails;

use Bookly\Lib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Appointment\CustomerDetails
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render customer details dialog.
     */
    public static function render()
    {
        static::renderTemplate( 'customer_details' );
    }
}