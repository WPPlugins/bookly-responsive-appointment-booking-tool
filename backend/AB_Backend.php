<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'modules/appearance/AB_AppearanceController.php';
include 'modules/staff/AB_StaffController.php';
include 'modules/service/AB_ServiceController.php';
include 'modules/calendar/AB_CalendarController.php';
include 'modules/payment/AB_PaymentController.php';
include 'modules/notifications/AB_NotificationsController.php';
include 'modules/settings/AB_SettingsController.php';
include 'modules/customer/AB_CustomerController.php';
include 'modules/tinymce/AB_TinyMCE_Plugin.php';
include 'modules/export/AB_ExportController.php';

class AB_Backend {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
        // Appearance
        $this->apearanceController = new AB_AppearanceController();
        // Staff members
        $this->staffController = new AB_StaffController();
        // Services
        $this->serviceController = new AB_ServiceController();
        // Calendar
        $this->calendarController = new AB_CalendarController();
        // Payments
        $this->paymentController = new AB_PaymentController();
        // Notifications
        $this->notificationsController = new AB_NotificationsController();
        // Settings
        $this->settingsController = new AB_SettingsController();
        // Customers
        $this->customerController = new AB_CustomerController();
        // Frontend booking ajax requests
        $this->bookingController = new AB_BookingController();
        // Export
        $this->exportController = new AB_ExportController();

        add_action( 'wp_loaded', array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'addTinyMCEPlugin' ) );
        add_action( 'admin_notices', array( $this->settingsController, 'showAdminNotice' ) );
    }

    public function addTinyMCEPlugin() {
	    /** @var WP_User $current_user */
	    global $current_user;
        new AB_TinyMCE_Plugin();
    }

    public function init() {
        if ( !session_id() ) {
            @session_start();
        }

        if ( isset( $_POST[ 'action' ] ) ) {
            switch ( $_POST[ 'action' ] ) {
                case 'ab_update_staff':
                    $this->staffController->updateStaff();
                    break;
            }
        }

        // for Appearance\Services\Settings all CSS and JS must be located directly in <HEAD>
        if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'ab-system-appearance' ) { // Appearance
            // include StyleSheets
            //wp_enqueue_style( 'ab-reset', plugins_url( 'css/ab-reset.css', dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', __FILE__ ) );
            wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', __FILE__ ) );
            wp_enqueue_style( 'ab-bootstrap-editable', plugins_url( 'resources/bootstrap/css/bootstrap-editable.css', __FILE__ ) );
            wp_enqueue_style( 'ab-ladda-themeless', plugins_url( 'css/ladda-themeless.min.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-ladda-min', plugins_url( 'css/ladda.min.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-frontend-style', plugins_url( 'css/ab_frontend_style.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-columnizer', plugins_url( 'css/ab-columnizer.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-pickadate', plugins_url( 'css/pickadate.classic.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'ab-columnizer', plugins_url( 'resources/css/ab-columnizer.css',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ) );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'ab-appearance', plugins_url( 'modules/appearance/resources/css/appearance.css', __FILE__ ) );
            // include JavaScript
            wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js',
                __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-bootstrap-editable', plugins_url( 'resources/bootstrap/js/bootstrap-editable.min.js',
                __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-appearance',
                plugins_url( 'modules/appearance/resources/js/appearance.js', __FILE__ ),
                array( 'jquery' )
            );
            wp_enqueue_script( 'ab-pickadate', plugins_url( 'js/pickadate.legacy.min.js',
                dirname(__FILE__). '../../frontend/resources/AB_Frontend.php' ), array( 'jquery' ) );
            wp_localize_script( 'ab-pickadate', 'BooklyL10n', array(
                'today'        => __( 'Today', 'ab' ),
                'month'        => array(
                    'January'    => __( 'January', 'ab' ),
                    'February'   => __( 'February', 'ab' ),
                    'March'      => __( 'March', 'ab' ),
                    'April'      => __( 'April', 'ab' ),
                    'May'        => __( 'May', 'ab' ),
                    'June'       => __( 'June', 'ab' ),
                    'July'       => __( 'July', 'ab' ),
                    'August'     => __( 'August', 'ab' ),
                    'September'  => __( 'September', 'ab' ),
                    'October'    => __( 'October', 'ab' ),
                    'November'   => __( 'November', 'ab' ),
                    'December'   => __( 'December', 'ab' )
                ),
                'day'            => array(
                    'Sun'        => __( 'Sun', 'ab' ),
                    'Mon'        => __( 'Mon', 'ab' ),
                    'Tue'        => __( 'Tue', 'ab' ),
                    'Wed'        => __( 'Wed', 'ab' ),
                    'Thu'        => __( 'Thu', 'ab' ),
                    'Fri'        => __( 'Fri', 'ab' ),
                    'Sat'        => __( 'Sat', 'ab' )
                )
            ) );
            wp_enqueue_script( 'wp-color-picker' );
        } elseif ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'ab-system-services' ) { // Services
            // include StyleSheets
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', __FILE__ ) );
            wp_enqueue_style( 'ab-service', plugins_url( 'modules/service/resources/css/service.css', __FILE__ ) );
            wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', __FILE__ ) );
            // include JavaScript
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'ab-popup', plugins_url( 'resources/js/ab_popup.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-service', plugins_url( 'modules/service/resources/js/service.js', __FILE__ ), array( 'jquery' ) );
            wp_localize_script( 'ab-service', 'BooklyL10n', array(
                'are_you_sure' => __( 'Are you sure?', 'ab' ),
                'please_select_at_least_one_service' => __( 'Please select at least one service.', 'ab'),
            ) );
        } elseif ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'ab-system-settings' ) { // Settings
            // include StyleSheets
            wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', __FILE__ ) );
            wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', __FILE__ ) );
            wp_enqueue_style( 'ab-jCal', plugins_url( 'resources/css/jCal.css', __FILE__ ) );
            // include JavaScript
            wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-settings', plugins_url( 'modules/settings/resources/js/settings.js', __FILE__ ), array( 'jquery' ) );
            wp_enqueue_script( 'ab-jCal', plugins_url( 'resources/js/jCal.js', __FILE__ ), array( 'jquery' ) );
            wp_localize_script( 'ab-jCal', 'BooklyL10n',  array(
                'we_are_not_working' => __( 'We are not working on this day', 'ab' ),
                'repeat'             => __( 'Repeat every year', 'ab' ),
                'month'              => array(
                    'January'    => __( 'January', 'ab' ),
                    'February'   => __( 'February', 'ab' ),
                    'March'      => __( 'March', 'ab' ),
                    'April'      => __( 'April', 'ab' ),
                    'May'        => __( 'May', 'ab' ),
                    'June'       => __( 'June', 'ab' ),
                    'July'       => __( 'July', 'ab' ),
                    'August'     => __( 'August', 'ab' ),
                    'September'  => __( 'September', 'ab' ),
                    'October'    => __( 'October', 'ab' ),
                    'November'   => __( 'November', 'ab' ),
                    'December'   => __( 'December', 'ab' )
                ),
                'day'                => array(
                    'Mon'        => __( 'Mon', 'ab' ),
                    'Tue'        => __( 'Tue', 'ab' ),
                    'Wed'        => __( 'Wed', 'ab' ),
                    'Thu'        => __( 'Thu', 'ab' ),
                    'Fri'        => __( 'Fri', 'ab' ),
                    'Sat'        => __( 'Sat', 'ab' ),
                    'Sun'        => __( 'Sun', 'ab' )
                )
            ) );
        }
    }

    public function addAdminMenu() {
        /** @var wpdb $wpdb */
        global $wpdb;
        /** @var WP_User $current_user */
        global $current_user;

        // translated submenu pages
        $calendar       = __( 'Calendar', 'ab' );
        $staff_members  = __( 'Staff members', 'ab' );
        $services       = __( 'Services', 'ab' );
        $customers      = __( 'Customers', 'ab' );
        $notifications  = __( 'Notifications', 'ab' );
        $payments       = __( 'Payments', 'ab' );
        $appearance     = __( 'Appearance', 'ab' );
        $settings       = __( 'Settings', 'ab' );
        $export         = __( 'Export', 'ab' );

        if ( in_array( 'administrator', $current_user->roles )
            || $wpdb->get_var( $wpdb->prepare(
                'SELECT COUNT(id) AS numb FROM ab_staff WHERE wp_user_id = %d', $current_user->ID
            ) ) ) {
            if ( function_exists( 'add_options_page' ) ) {
	            $dynamic_position = '80.0000001' . mt_rand( 1, 1000 ); // position always is under `Settings`
                add_menu_page( 'Bookly', 'Bookly', 'read', 'ab-system',
                    array( $this->staffController, 'renderStaffMembers'),
                    plugins_url('resources/images/menu.png', __FILE__), $dynamic_position );
                add_submenu_page( 'ab-system', $calendar, $calendar, 'read', 'ab-system-calendar',
                    array( $this->calendarController, 'renderCalendar' ) );
                add_submenu_page( 'ab-system', $staff_members, $staff_members, 'manage_options', 'ab-system-staff',
                    array( $this->staffController, 'renderStaffMembers' ) );
                add_submenu_page( 'ab-system', $services, $services, 'manage_options', 'ab-system-services',
                    array( $this->serviceController, 'index' ) );
                add_submenu_page( 'ab-system', $customers, $customers, 'manage_options', 'ab-system-customers',
                    array( $this->customerController, 'index' ) );
                add_submenu_page( 'ab-system', $notifications, $notifications, 'manage_options', 'ab-system-notifications',
                    array( $this->notificationsController, 'index' ) );
                add_submenu_page( 'ab-system', $payments, $payments, 'manage_options', 'ab-system-payments',
                    array( $this->paymentController, 'index' ) );
                add_submenu_page( 'ab-system', $appearance, $appearance, 'manage_options', 'ab-system-appearance',
                    array( $this->apearanceController, 'index' ) );
                add_submenu_page( 'ab-system', $settings, $settings, 'manage_options', 'ab-system-settings',
                    array( $this->settingsController, 'index' ) );
                add_submenu_page( 'ab-system', $export, $export, 'manage_options', 'ab-system-export',
                    array( $this->exportController, 'index' ) );

                global $submenu;
                unset( $submenu[ 'ab-system' ][ 0 ] );
            }
        }
    }

}
