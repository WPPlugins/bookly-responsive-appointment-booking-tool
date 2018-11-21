<?php
namespace Bookly\Backend\Components\Dialogs\Customer\Edit;

use Bookly\Lib;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Customer\Edit
 */
class Dialog extends Lib\Base\Component
{
    /**
     * Render customer dialog.
     */
    public static function render()
    {
        global $wp_locale;

        self::enqueueStyles( array(
            'backend'  => array( 'css/jquery-ui-theme/jquery-ui.min.css', 'css/select2.min.css', ),
            'frontend' => get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                ? array()
                : array( 'css/intlTelInput.css' ),
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'js/angular.min.js' => array( 'jquery' ),
                'js/select2.full.min.js'      => array( 'jquery' ),
                'js/angular-ui-date-0.0.8.js' => array( 'bookly-angular.min.js', 'jquery-ui-datepicker' ),
             ),
            'frontend' => get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                ? array()
                : array( 'js/intlTelInput.min.js' => array( 'jquery' ) ),
            'module' => array( 'js/ng-customer.js' => array( 'bookly-angular.min.js' ), )
        ) );

        wp_localize_script( 'bookly-ng-customer.js', 'BooklyL10nCustDialog', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'first_last_name' => (int) Lib\Config::showFirstLastName(),
            'default_status'  => get_option( 'bookly_gen_default_appointment_status' ),
            'intlTelInput'    => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
            'dateOptions'     => array(
                'dateFormat'      => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_JQUERY_DATEPICKER ),
                'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
                'monthNames'      => array_values( $wp_locale->month ),
                'dayNamesMin'     => array_values( $wp_locale->weekday_abbrev ),
                'longDays'        => array_values( $wp_locale->weekday ),
                'firstDay'        => (int) get_option( 'start_of_week' ),
                'yearRange'       => sprintf( '%s:%s', date_create()->modify( '-100 years' )->format( 'Y' ), date( 'Y' ) ),
                'changeYear'      => true,
            ),
            'infoFields'      => (array) Lib\Proxy\CustomerInformation::getFieldsWhichMayHaveData(),
            'noResultFound'   => __( 'No result found', 'bookly' ),
        ) );

        static::renderTemplate( 'edit' );
    }
}