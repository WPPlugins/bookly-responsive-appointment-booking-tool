<?php
namespace Bookly\Backend\Components\Dialogs\Staff\Edit;

use Bookly\Backend\Components\Notices\Limitation;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;
use Bookly\Lib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Staff\Edit
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render create service dialog.
     */
    public static function render()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        wp_enqueue_media();

        self::enqueueStyles( array(
            'frontend' => array_merge(
                array( 'css/ladda.min.css', ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array( 'css/fontawesome-all.min.css', 'css/select2.min.css' ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'backend'  => array(
                'js/jCal.js'             => array( 'jquery' ),
                'js/dropdown.js'         => array( 'jquery' ),
                'js/range_tools.js'      => array( 'jquery' ),
                'js/moment.min.js',
                'js/select2.full.min.js' => array( 'jquery' ),
            ),
            'module'   => array(
                'js/staff-details.js'  => array( 'jquery' ),
                'js/staff-services.js' => array( 'bookly-staff-details.js' ),
                'js/staff-schedule.js' => array( 'bookly-staff-services.js' ),
                'js/staff-days-off.js' => array( 'bookly-staff-schedule.js' ),
                'js/staff-edit-dialog.js' => array( 'jquery-ui-sortable', 'jquery-ui-datepicker', 'bookly-range_tools.js', 'bookly-staff-days-off.js' )
            ),
        ) );

        wp_localize_script( 'bookly-staff-edit-dialog.js', 'BooklyStaffEditDialogL10n', array(
            'csrfToken' => Lib\Utils\Common::getCsrfToken(),
            'intlTelInput'          => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
            'holidays' => array(
                'loading_img'        => plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ),
                'firstDay'           => (int) get_option( 'start_of_week' ),
                'days'               => array_values( $wp_locale->weekday_abbrev ),
                'months'             => array_values( $wp_locale->month ),
                'close'              => __( 'Close', 'bookly' ),
                'repeat'             => __( 'Repeat every year', 'bookly' ),
                'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            ),
            'services' => array(
                'capacity_error' => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            ),
            'createStaff'         => __( 'Create staff', 'bookly' ),
            'editStaff'           => __( 'Edit staff', 'bookly' ),
            'areYouSure'          => __( 'Are you sure?', 'bookly' ),
            'settingsSaved'       => __( 'Settings saved.', 'bookly' ),
            'proRequired'         => (int) ! Lib\Config::proActive(),
            'limitation'          => Limitation::getHtml(),
            'activeStaffId'       => self::parameter( 'staff_id', 0 )
        ) );

        self::renderTemplate( 'dialog' );

        Proxy\Pro::renderArchivingComponents();
    }
}