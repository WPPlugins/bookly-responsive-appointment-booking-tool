<?php
namespace Bookly\Backend\Components\Support;

use Bookly\Lib;
use Bookly\Backend\Modules;
use Bookly\Backend\Components\Notices;

/**
 * Class Buttons
 * @package Bookly\Backend\Components\Support
 */
class Buttons extends Lib\Base\Component
{
    /**
     * Render support buttons.
     *
     * @param string $page_slug
     */
    public static function render( $page_slug )
    {
        self::enqueueStyles( array(
            'backend'  => array( 'css/fontawesome-all.min.css' ),
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array( 'js/alert.js' => array( 'jquery' ), ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array( 'js/support.js' => array( 'bookly-alert.js', 'bookly-ladda.min.js', ), ),
        ) );

        wp_localize_script( 'bookly-support.js', 'SupportL10n', array(
            'csrf_token' => Lib\Utils\Common::getCsrfToken()
        ) );

        // Documentation link.
        $doc_link = 'http://api.booking-wp-plugin.com/go/' . $page_slug;

        $days_in_use = (int) ( ( time() - Lib\Plugin::getInstallationTime() ) / DAY_IN_SECONDS );

        // Whether to show contact us notice or not.
        $show_contact_us_notice = $days_in_use < 7 &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_contact_us_notice', true ) &&
            ! Notices\CollectStats::needShowCollectStatNotice();

        // Whether to show feedback notice.
        $show_feedback_notice = $days_in_use >= 7 &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_feedback_notice', true ) &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'contact_us_btn_clicked', true );

        $current_user = wp_get_current_user();

        $messages = Lib\Entities\Message::query( 'm' )
            ->select( 'm.created, m.subject, m.seen' )
            ->sortBy( 'm.seen, m.message_id' )
            ->order( 'DESC' )
            ->limit( 10 )
            ->fetchArray();
        $messages_new = Lib\Entities\Message::query( 'm' )->where( 'm.seen', '0' )->count();
        $messages_link = Lib\Utils\Common::escAdminUrl( Modules\Messages\Ajax::pageSlug() );

        static::renderTemplate( 'buttons', compact(
            'doc_link',
            'show_contact_us_notice',
            'show_feedback_notice',
            'current_user',
            'messages',
            'messages_new',
            'messages_link'
        ) );
    }
}