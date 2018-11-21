<?php
namespace Bookly\Backend\Modules\Messages;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Messages
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend' => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
            ),
            'module'  => array( 'js/message.js' => array( 'jquery' ) ),
        ) );

        wp_localize_script( 'bookly-message.js', 'BooklyL10n', array(
            'csrf_token'  => Lib\Utils\Common::getCsrfToken(),
            'datatable' => array(
                'zeroRecords' => __( 'No records.', 'bookly' ),
                'processing'  => __( 'Processing...', 'bookly' ),
                'per_page'    => __( 'messages', 'bookly' ),
                'paginate' => array(
                    'first'    => __( 'First', 'bookly' ),
                    'previous' => __( 'Previous', 'bookly' ),
                    'next'     => __( 'Next', 'bookly' ),
                    'last'     => __( 'Last', 'bookly' ),
                )
            )
        ) );

        self::renderTemplate( 'index' );
    }

    /**
     * @return int
     */
    public static function getMessagesCount()
    {
        return Lib\Entities\Message::query()
            ->where( 'seen', 0 )
            ->count();
    }

    /**
     * Show 'Messages' submenu with counter inside Bookly main menu
     */
    public static function addBooklyMenuItem()
    {
        $messages = __( 'Messages', 'bookly' );
        $count    = self::getMessagesCount();
        if ( $count ) {
            add_submenu_page( 'bookly-menu', $messages, sprintf( '%s <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $messages, $count, $count ), 'manage_options',
                self::pageSlug(), function () { Page::render(); } );
        } else {
            add_submenu_page( 'bookly-menu', $messages, $messages, 'manage_options',
                self::pageSlug(), function () { Page::render(); } );
        }
    }

}