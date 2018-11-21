<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class LiteRebrandingAjax
 * @package Bookly\Backend\Components\Notices
 */
class LiteRebrandingAjax extends Lib\Base\Ajax
{
    /**
     * Dismiss 'Lite Rebranding' notice.
     */
    public static function dismissLiteRebrandingNotice()
    {
        delete_user_meta( get_current_user_id(), 'bookly_show_lite_rebranding_notice' );

        wp_send_json_success();
    }
}