<?php
namespace Bookly\Backend\Components\Dialogs\Service\Create;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Service\Create
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create service dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'css/fontawesome-all.min.css', 'css/select2.min.css' ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'backend'  => array(
                'js/select2.full.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/service-create-dialog.js' => array( 'jquery', ) ),
        ) );

        $type_icons = Proxy\Shared::prepareServiceIcons( array( Lib\Entities\Service::TYPE_SIMPLE => 'fa-calendar-day' ) );

        wp_localize_script( 'bookly-service-create-dialog.js', 'BooklyServiceCreateDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
        ) );

        self::renderTemplate( 'dialog', compact( 'type_icons' ) );
    }
}