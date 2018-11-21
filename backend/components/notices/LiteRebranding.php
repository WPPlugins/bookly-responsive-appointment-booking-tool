<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class LiteRebranding
 * @package Bookly\Backend\Components\Notices
 */
class LiteRebranding extends Lib\Base\Component
{
    /**
     * Render subscribe notice.
     */
    public static function render()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() &&
            get_user_meta( get_current_user_id(), 'bookly_show_lite_rebranding_notice', true ) ) {

            self::enqueueScripts( array(
                'module' => array( 'js/lite-rebranding.js' => array( 'jquery' ), ),
            ) );

            self::renderTemplate( 'lite_rebranding' );
        }
    }
}