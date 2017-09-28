<?php
namespace BooklyLite\Backend;

use BooklyLite\Backend\Modules;
use BooklyLite\Frontend;
use BooklyLite\Lib;

/**
 * Class Backend
 * @package BooklyLite\Backend
 */
class Backend
{
    public function __construct()
    {
        // Backend controllers.
        $this->apearanceController     = Modules\Appearance\Controller::getInstance();
        $this->appointmentsController  = Modules\Appointments\Controller::getInstance();
        $this->calendarController      = Modules\Calendar\Controller::getInstance();
        $this->couponsController       = Modules\Coupons\Controller::getInstance();
        $this->customerController      = Modules\Customers\Controller::getInstance();
        $this->customFieldsController  = Modules\CustomFields\Controller::getInstance();
        $this->debugController         = Modules\Debug\Controller::getInstance();
        $this->notificationsController = Modules\Notifications\Controller::getInstance();
        $this->paymentController       = Modules\Payments\Controller::getInstance();
        $this->serviceController       = Modules\Services\Controller::getInstance();
        $this->settingsController      = Modules\Settings\Controller::getInstance();
        $this->supportController       = Modules\Support\Controller::getInstance();
        $this->smsController           = Modules\Sms\Controller::getInstance();
        $this->staffController         = Modules\Staff\Controller::getInstance();

        // Frontend controllers that work via admin-ajax.php.
        $this->bookingController = Frontend\Modules\Booking\Controller::getInstance();
        $this->customerProfileController = Frontend\Modules\CustomerProfile\Controller::getInstance();

        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
        add_action( 'wp_loaded',  array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'addTinyMCEPlugin' ) );
    }

    public function init()
    {
        if ( ! session_id() ) {
            @session_start();
        }
    }

    public function addTinyMCEPlugin()
    {
        new Modules\TinyMce\Plugin();
    }

    public function addAdminMenu()
    {
        /** @var \WP_User $current_user */
        global $current_user;

        if ( $current_user->has_cap( 'administrator' ) || Lib\Entities\Staff::query()->where( 'wp_user_id', $current_user->ID )->count() ) {
            $dynamic_position = '80.0000001' . mt_rand( 1, 1000 ); // position always is under `Settings`
            add_menu_page( 'Bookly', 'Bookly', 'read', 'bookly-menu', '',
                plugins_url( 'resources/images/menu.png', __FILE__ ), $dynamic_position );

            // Translated submenu pages.
            $calendar       = __( 'Calendar',      'bookly' );
            $appointments   = __( 'Appointments',  'bookly' );
            $staff_members  = __( 'Staff Members', 'bookly' );
            $services       = __( 'Services',      'bookly' );
            $sms            = __( 'SMS Notifications', 'bookly' );
            $notifications  = __( 'Email Notifications', 'bookly' );
            $customers      = __( 'Customers',     'bookly' );
            $payments       = __( 'Payments',      'bookly' );
            $appearance     = __( 'Appearance',    'bookly' );
            $settings       = __( 'Settings',      'bookly' );
            $coupons        = __( 'Coupons',       'bookly' );
            $custom_fields  = __( 'Custom Fields', 'bookly' );

            add_submenu_page( 'bookly-menu', $calendar, $calendar, 'read',
                Modules\Calendar\Controller::page_slug, array( $this->calendarController, 'index' ) );
            add_submenu_page( 'bookly-menu', $appointments, $appointments, 'manage_options',
                Modules\Appointments\Controller::page_slug, array( $this->appointmentsController, 'index' ) );
            do_action( 'bookly_render_menu_after_appointments' );
            if ( $current_user->has_cap( 'administrator' ) ) {
                add_submenu_page( 'bookly-menu', $staff_members, $staff_members, 'manage_options',
                    Modules\Staff\Controller::page_slug, array( $this->staffController, 'index' ) );
            } else {
                if ( get_option( 'bookly_gen_allow_staff_edit_profile' ) == 1 ) {
                    add_submenu_page( 'bookly-menu', __( 'Profile', 'bookly' ), __( 'Profile', 'bookly' ), 'read',
                        Modules\Staff\Controller::page_slug, array( $this->staffController, 'index' ) );
                }
            }
            add_submenu_page( 'bookly-menu', $services, $services, 'manage_options',
                Modules\Services\Controller::page_slug, array( $this->serviceController, 'index' ) );
            add_submenu_page( 'bookly-menu', $customers, $customers, 'manage_options',
                Modules\Customers\Controller::page_slug, array( $this->customerController, 'index' ) );
            add_submenu_page( 'bookly-menu', $notifications, $notifications, 'manage_options',
                Modules\Notifications\Controller::page_slug, array( $this->notificationsController, 'index' ) );
            add_submenu_page( 'bookly-menu', $sms, $sms, 'manage_options',
                Modules\Sms\Controller::page_slug, array( $this->smsController, 'index' ) );
            add_submenu_page( 'bookly-menu', $payments, $payments, 'manage_options',
                Modules\Payments\Controller::page_slug, array( $this->paymentController, 'index' ) );
            add_submenu_page( 'bookly-menu', $appearance, $appearance, 'manage_options',
                Modules\Appearance\Controller::page_slug, array( $this->apearanceController, 'index' ) );
            add_submenu_page( 'bookly-menu', $custom_fields, $custom_fields, 'manage_options',
                Modules\CustomFields\Controller::page_slug, array( $this->customFieldsController, 'index' ) );
            add_submenu_page( 'bookly-menu', $coupons, $coupons, 'manage_options',
                Modules\Coupons\Controller::page_slug, array( $this->couponsController, 'index' ) );
            add_submenu_page( 'bookly-menu', $settings, $settings, 'manage_options',
                Modules\Settings\Controller::page_slug, array( $this->settingsController, 'index' ) );

            if ( isset ( $_GET['page'] ) && $_GET['page'] == 'bookly-debug' ) {
                add_submenu_page( 'bookly-menu', 'Debug', 'Debug', 'manage_options',
                    Modules\Debug\Controller::page_slug, array( $this->debugController, 'index' ) );
            }

            global $submenu;
            do_action( 'bookly_admin_menu', 'bookly-menu' );
            unset ( $submenu['bookly-menu'][0] );
        }
    }

}