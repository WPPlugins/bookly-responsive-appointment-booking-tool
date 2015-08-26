<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Backend {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );

        // Backend controllers.
        $this->apearanceController     = new AB_AppearanceController();
        $this->calendarController      = new AB_CalendarController();
        $this->customerController      = new AB_CustomerController();
        $this->notificationsController = new AB_NotificationsController();
        $this->paymentController       = new AB_PaymentController();
        $this->serviceController       = new AB_ServiceController();
        $this->smsController           = new AB_SmsController();
        $this->settingsController      = new AB_SettingsController();
        $this->staffController         = new AB_StaffController();
        $this->couponsController       = new AB_CouponsController();
        $this->customFieldsController  = new AB_CustomFieldsController();
        $this->appointmentsController  = new AB_AppointmentsController();

        // Frontend controllers that work via admin-ajax.php.
        $this->bookingController       = new AB_BookingController();

        add_action( 'wp_loaded',     array( $this, 'init' ) );
        add_action( 'admin_init',    array( $this, 'addTinyMCEPlugin' ) );
    }

    public function addTinyMCEPlugin() {
        /** @var WP_User $current_user */
        global $current_user;
        new AB_TinyMCE_Plugin();
    }

    public function init() {
        if ( ! session_id() ) {
            @session_start();
        }

        if ( isset( $_POST[ 'action' ] ) ) {
            switch ( $_POST[ 'action' ] ) {
                case 'ab_update_staff':
                    $this->staffController->updateStaff();
                    break;
            }
        }
    }

    public function addAdminMenu() {
        /** @var WP_User $current_user */
        global $current_user;

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

        if ( $current_user->has_cap( 'administrator' ) || AB_Staff::query()->where( 'wp_user_id', $current_user->ID )->count() ) {
            if ( function_exists( 'add_options_page' ) ) {
                $dynamic_position = '80.0000001' . mt_rand( 1, 1000 ); // position always is under `Settings`
                add_menu_page( 'Bookly', 'Bookly', 'read', 'ab-system', '',
                    plugins_url('resources/images/menu.png', __FILE__), $dynamic_position );
                add_submenu_page( 'ab-system', $calendar, $calendar, 'read', 'ab-calendar',
                    array( $this->calendarController, 'index' ) );
                add_submenu_page( 'ab-system', $appointments, $appointments, 'manage_options', 'ab-appointments',
                    array( $this->appointmentsController, 'index' ) );
                if ( $current_user->has_cap( 'administrator' ) ) {
                    add_submenu_page( 'ab-system', $staff_members, $staff_members, 'manage_options', AB_StaffController::page_slug,
                        array( $this->staffController, 'index' ) );
                } else {
                    if ( 1 == get_option( 'ab_settings_allow_staff_members_edit_profile' ) ) {
                        add_submenu_page( 'ab-system', __( 'Profile', 'bookly' ), __( 'Profile', 'bookly' ), 'read', AB_StaffController::page_slug,
                            array( $this->staffController, 'index' ) );
                    }
                }
                add_submenu_page( 'ab-system', $services, $services, 'manage_options', AB_ServiceController::page_slug,
                    array( $this->serviceController, 'index' ) );
                add_submenu_page( 'ab-system', $customers, $customers, 'manage_options', AB_CustomerController::page_slug,
                    array( $this->customerController, 'index' ) );
                add_submenu_page( 'ab-system', $notifications, $notifications, 'manage_options', 'ab-notifications',
                    array( $this->notificationsController, 'index' ) );
                add_submenu_page( 'ab-system', $sms, $sms, 'manage_options', AB_SmsController::page_slug,
                    array( $this->smsController, 'index' ) );
                add_submenu_page( 'ab-system', $payments, $payments, 'manage_options', 'ab-payments',
                    array( $this->paymentController, 'index' ) );
                add_submenu_page( 'ab-system', $appearance, $appearance, 'manage_options', 'ab-appearance',
                    array( $this->apearanceController, 'index' ) );
                add_submenu_page( 'ab-system', $custom_fields, $custom_fields, 'manage_options', 'ab-custom-fields',
                    array( $this->customFieldsController, 'index' ) );
                add_submenu_page( 'ab-system', $coupons, $coupons, 'manage_options', 'ab-coupons',
                    array( $this->couponsController, 'index' ) );
                add_submenu_page( 'ab-system', $settings, $settings, 'manage_options', AB_SettingsController::page_slug,
                    array( $this->settingsController, 'index' ) );

                global $submenu;
                do_action( 'bookly_addons_menu', 'ab-system' );
                unset( $submenu[ 'ab-system' ][ 0 ] );
            }
        }
    }

}