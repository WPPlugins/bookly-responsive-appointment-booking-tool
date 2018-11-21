<?php
namespace Bookly\Backend\Components\Dialogs\Common;

use Bookly\Lib;

/**
 * Class CascadeDelete
 * @package Bookly\Backend\Components\Dialogs\Common
 */
class CascadeDelete extends Lib\Base\Component
{
    /**
     * Render cascade delete dialog (used in services and staff lists).
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'bookly-spin.min.js', 'jquery' ),
            )
        ) );

        self::renderTemplate( 'delete_cascade' );
    }
}