<?php
namespace Bookly\Backend\Components\Dialogs\Service\Edit;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Page;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Service\Edit
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
            'module'   => array( 'js/service-edit-dialog.js' => array( 'jquery', ) ),
        ) );

        $staff = array();
        foreach ( Page::getStaffDropDownData() as $category ) {
            foreach ( $category['items'] as $employee ) {
                $staff[ $employee['id'] ] = $employee['full_name'];
            }
        }

        wp_localize_script( 'bookly-service-edit-dialog.js', 'BooklyServiceEditDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
            'staff'     => $staff,
        ) );

        self::renderTemplate( 'dialog' );
    }
}