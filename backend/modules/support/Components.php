<?php
namespace BooklyLite\Backend\Modules\Support;

use BooklyLite\Lib;
use BooklyLite\Backend\Modules;

/**
 * Class Components
 * @package BooklyLite\Backend\Modules\Support
 */
class Components extends Lib\Base\Components
{
    const BOOKLY_WORDPRESS_URL = 'https://wordpress.org/support/plugin/bookly-responsive-appointment-booking-tool/reviews/';

    /**
     * Render support buttons.
     */
    public function renderButtons( $page_slug )
    {
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array( 'js/alert.js' => array( 'jquery' ), ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'  => array( 'js/support.js' => array( 'bookly-alert.js', 'bookly-ladda.min.js', ), ),
        ) );

        // Documentation link.
        $doc_link = 'http://api.booking-wp-plugin.com/go/' . $page_slug;

        $days_in_use = (int) ( ( time() - Lib\Plugin::getInstallationTime() ) / DAY_IN_SECONDS );

        // Whether to show contact us notice or not.
        $show_contact_us_notice = $days_in_use < 7 &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_contact_us_notice', true );

        // Whether to show feedback notice.
        $show_feedback_notice = $days_in_use >= 7 &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_feedback_notice', true ) &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'contact_us_btn_clicked', true );

        $current_user = wp_get_current_user();

        wp_localize_script( 'bookly-support.js', 'SupportL10n', array(
            'csrf_token'   => \BooklyLite\Lib\Utils\Common::getCsrfToken()
        ) );
        $messages     = Lib\Entities\Message::query( 'm' )->select( 'm.created, m.subject, m.seen' )->sortBy( 'm.seen, m.message_id' )->order( 'DESC' )->limit( 10 )->fetchArray();
        $messages_new = Lib\Entities\Message::query( 'm' )->where( 'm.seen', '0' )->count();
        $messages_link = Lib\Utils\Common::escAdminUrl( \BooklyLite\Backend\Modules\Message\Controller::page_slug );
        $this->render( '_buttons', compact( 'doc_link', 'show_contact_us_notice', 'show_feedback_notice', 'current_user', 'messages', 'messages_new', 'messages_link' ) );
    }

    /**
     * Render subscribe notice.
     */
    public function renderSubscribeNotice()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() &&
            ! get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_subscribe_notice', true ) ) {

            // Show notice 1 day after installation time.
            if ( time() - Lib\Plugin::getInstallationTime() >= DAY_IN_SECONDS ) {
                $this->enqueueStyles( array(
                    'frontend' => array( 'css/ladda.min.css', ),
                ) );
                $this->enqueueScripts( array(
                    'backend' => array( 'js/alert.js' => array( 'jquery' ), ),
                    'frontend' => array(
                        'js/spin.min.js'  => array( 'jquery' ),
                        'js/ladda.min.js' => array( 'jquery' ),
                    ),
                    'module'  => array( 'js/subscribe.js' => array( 'bookly-alert.js', 'bookly-ladda.min.js', ), ),
                ) );
                $this->render( '_subscribe_notice' );
            }
        }
    }

    /**
     * Render Net Promoter Score notice.
     */
    public function renderNpsNotice()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $dismiss_value = get_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_nps_notice', true );
            // Show notice 1 month after it was closed the last time.
            if ( ! $dismiss_value || $dismiss_value > 1 && time() - $dismiss_value >= 30 * DAY_IN_SECONDS ) {
                // Show notice 1 month after installation time.
                if ( time() - Lib\Plugin::getInstallationTime() >= 30 * DAY_IN_SECONDS ) {
                    $this->enqueueStyles( array(
                        'frontend' => array( 'css/ladda.min.css', ),
                        'module'   => array( 'css/bootstrap-stars.css', ),
                    ) );
                    $this->enqueueScripts( array(
                        'backend' => array(
                            'js/alert.js' => array( 'jquery' ),
                        ),
                        'frontend' => array(
                            'js/spin.min.js'  => array( 'jquery' ),
                            'js/ladda.min.js' => array( 'jquery' ),
                        ),
                        'module'  => array(
                            'js/jquery.barrating.min.js' => array( 'jquery' ),
                            'js/nps.js' => array( 'bookly-jquery.barrating.min.js', 'bookly-alert.js', 'bookly-ladda.min.js', ),
                        ),
                    ) );

                    $this->render( '_nps_notice', array( 'current_user' => wp_get_current_user() ) );
                }
            }
        }
    }
}