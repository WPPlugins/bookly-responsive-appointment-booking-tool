<?php
namespace Bookly\Backend\Components\Dialogs\Common;

use Bookly\Lib;

/**
 * Class UnsavedChanges
 * @package Bookly\Backend\Components\Dialogs\Common
 */
class UnsavedChanges extends Lib\Base\Component
{
    /**
     * Render unsaved data confirm dialog.
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

        self::renderTemplate( 'unsaved_changes' );
    }
}