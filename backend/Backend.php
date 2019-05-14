<?php
namespace Bookly\Backend;

use Bookly\Lib;

/**
 * Class Backend
 * @package Bookly\Backend
 */
abstract class Backend
{
    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        add_action( 'admin_menu', array( __CLASS__, 'addAdminMenu' ) );

        add_action( 'admin_notices', function () {
            $bookly_page = isset ( $_REQUEST['page'] ) && strncmp( $_REQUEST['page'], 'bookly-', 7 ) === 0;
            if ( $bookly_page ) {
                // Subscribe notice.
                Components\Notices\Subscribe::render();
                // Subscribe notice.
                Components\Notices\LiteRebranding::render();
                // NPS notice.
                Components\Notices\Nps::render();
                // Collect stats notice.
                Components\Notices\CollectStats::render();
            }
            // Let add-ons render admin notices.
            Lib\Proxy\Shared::renderAdminNotices( $bookly_page );
        }, 10, 0 );
    }

    /**
     * Admin menu.
     */
    public static function addAdminMenu()
    {
        /** @var \WP_User $current_user */
        global $current_user, $submenu;

        $is_staff = Lib\Entities\Staff::query()->where( 'wp_user_id', $current_user->ID )->count() > 0;
        if ( $current_user->has_cap( 'administrator' ) || $current_user->has_cap( 'manage_bookly_appointments' ) || $is_staff ) {
            $dynamic_position = '80.0000001' . mt_rand( 1, 1000 ); // position always is under `Settings`
            $badge_number = Modules\Messages\Page::getMessagesCount() +
                Modules\Shop\Page::getNotSeenCount() +
                Lib\SMS::getUndeliveredSmsCount()
            ;

            if ( $badge_number ) {
                add_menu_page( 'Bookly', sprintf( 'Bookly <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $badge_number, $badge_number ), 'read', 'bookly-menu', '',
                    plugins_url( 'resources/images/menu.png', __FILE__ ), $dynamic_position );
            } else {
                add_menu_page( 'Bookly', 'Bookly', 'read', 'bookly-menu', '',
                    plugins_url( 'resources/images/menu.png', __FILE__ ), $dynamic_position );
            }
            if ( Lib\Proxy\Pro::graceExpired() ) {
                Lib\Proxy\Pro::addLicenseBooklyMenuItem();
            } else {
                // Translated submenu pages.
                $dashboard      = __( 'Dashboard',           'bookly' );
                $calendar       = __( 'Calendar',            'bookly' );
                $appointments   = __( 'Appointments',        'bookly' );
                $staff_members  = __( 'Staff Members',       'bookly' );
                $services       = __( 'Services',            'bookly' );
                $notifications  = __( 'Email Notifications', 'bookly' );
                $customers      = __( 'Customers',           'bookly' );
                $payments       = __( 'Payments',            'bookly' );
                $appearance     = __( 'Appearance',          'bookly' );
                $settings       = __( 'Settings',            'bookly' );

                add_submenu_page( 'bookly-menu', $dashboard, $dashboard, 'read',
                    Modules\Dashboard\Page::pageSlug(), function () { Modules\Dashboard\Page::render(); } );
                add_submenu_page( 'bookly-menu', $calendar, $calendar, 'read',
                    Modules\Calendar\Page::pageSlug(), function () { Modules\Calendar\Page::render(); } );
                if ( $current_user->has_cap( 'manage_options' ) || $current_user->has_cap( 'manage_bookly_appointments' ) ) {
                    add_submenu_page( 'bookly-menu', $appointments, $appointments, 'read',
                        Modules\Appointments\Page::pageSlug(), function () { Modules\Appointments\Page::render(); } );
                }
                Lib\Proxy\Locations::addBooklyMenuItem();
                if ( $current_user->has_cap( 'manage_options' ) || $current_user->has_cap( 'manage_bookly_appointments' ) ) {
                    Lib\Proxy\Packages::addBooklyMenuItem();
                }
                if ( $current_user->has_cap( 'administrator' ) ) {
                    add_submenu_page( 'bookly-menu', $staff_members, $staff_members, 'manage_options',
                        Modules\Staff\Page::pageSlug(), function () { Modules\Staff\Page::render(); } );
                } elseif ( $is_staff ) {
                    if ( get_option( 'bookly_gen_allow_staff_edit_profile' ) == 1 ) {
                        add_submenu_page( 'bookly-menu', __( 'Profile', 'bookly' ), __( 'Profile', 'bookly' ), 'read',
                            Modules\Staff\Page::pageSlug(), function () { Modules\Staff\Page::render(); } );
                    }
                }
                add_submenu_page( 'bookly-menu', $services, $services, 'manage_options',
                    Modules\Services\Page::pageSlug(), function () { Modules\Services\Page::render(); } );
                Lib\Proxy\Taxes::addBooklyMenuItem();
                if ( $current_user->has_cap( 'manage_options' ) || $current_user->has_cap( 'manage_bookly_appointments' ) ) {
                    add_submenu_page( 'bookly-menu', $customers, $customers, 'read',
                        Modules\Customers\Page::pageSlug(), function () { Modules\Customers\Page::render(); } );
                }
                Lib\Proxy\CustomerInformation::addBooklyMenuItem();
                Lib\Proxy\CustomerGroups::addBooklyMenuItem();
                add_submenu_page( 'bookly-menu', $notifications, $notifications, 'manage_options',
                    Modules\Notifications\Page::pageSlug(), function () { Modules\Notifications\Page::render(); } );
                Modules\Sms\Page::addBooklyMenuItem();
                if ( $current_user->has_cap( 'manage_options' ) || $current_user->has_cap( 'manage_bookly_appointments' ) ) {
                    add_submenu_page( 'bookly-menu', $payments, $payments, 'read',
                        Modules\Payments\Page::pageSlug(), function () { Modules\Payments\Page::render(); } );
                }
                add_submenu_page( 'bookly-menu', $appearance, $appearance, 'manage_options',
                    Modules\Appearance\Page::pageSlug(), function () { Modules\Appearance\Page::render(); } );
                Lib\Proxy\Coupons::addBooklyMenuItem();
                Lib\Proxy\CustomFields::addBooklyMenuItem();
                add_submenu_page( 'bookly-menu', $settings, $settings, 'manage_options',
                    Modules\Settings\Page::pageSlug(), function () { Modules\Settings\Page::render(); } );
                Modules\Messages\Page::addBooklyMenuItem();
                Modules\Shop\Page::addBooklyMenuItem();

                if ( isset ( $_GET['page'] ) && $_GET['page'] == 'bookly-debug' ) {
                    add_submenu_page( 'bookly-menu', 'Debug', 'Debug', 'manage_options',
                        Modules\Debug\Page::pageSlug(), function () { Modules\Debug\Page::render(); } );
                }
                if ( ! Lib\Config::proActive() ) {
                    $submenu['bookly-menu'][] = array( esc_attr__( 'Get Bookly Pro', 'bookly' ) . ' <i class="fas fa-certificate" style="color: #f4662f"></i>', 'read', Lib\Utils\Common::prepareUrlReferrers( 'https://codecanyon.net/item/bookly/7226091?ref=ladela', 'admin_menu' ), );
                }
            }

            unset ( $submenu['bookly-menu'][0] );
        }
    }
}