<?php
namespace BooklyLite\Backend\Modules\Calendar;

use BooklyLite\Lib;

/**
 * Class Components
 * @package BooklyLite\Backend\Modules\Calendar
 */
class Components extends Lib\Base\Components
{
    /**
     * Render appointment dialog.
     * @throws \Exception
     */
    public function renderAppointmentDialog()
    {
        global $wp_locale;

        $this->enqueueStyles( array(
            'backend'  => array( 'css/jquery-ui-theme/jquery-ui.min.css', ),
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'js/angular.min.js'           => array( 'jquery' ),
                'js/angular-ui-date-0.0.8.js' => array( 'bookly-angular.min.js' ),
                'js/moment.min.js'            => array( 'jquery' ),
                'js/chosen.jquery.min.js'     => array( 'jquery' ),
                'js/help.js'                  => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/ng-appointment_dialog.js' => array( 'bookly-angular-ui-date-0.0.8.js', 'jquery-ui-datepicker' ),
            )
        ) );

        wp_localize_script( 'bookly-ng-appointment_dialog.js', 'BooklyL10nAppDialog', array(
            'calendar' => array(
                'shortMonths' => array_values( $wp_locale->month_abbrev ),
                'longMonths'  => array_values( $wp_locale->month ),
                'shortDays'   => array_values( $wp_locale->weekday_abbrev ),
                'longDays'    => array_values( $wp_locale->weekday ),
            ),
            'dpDateFormat'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_JQUERY_DATEPICKER ),
            'startOfWeek'    => (int) get_option( 'start_of_week' ),
            'cf_per_service' => (int) Lib\Config::customFieldsPerService(),
            'title'          => array(
                'edit_appointment' => __( 'Edit appointment', 'bookly' ),
                'new_appointment'  => __( 'New appointment',  'bookly' ),
            ),
        ) );

        // Custom fields without captcha field.
        $custom_fields = array_filter(
            json_decode( get_option( 'bookly_custom_fields' ) ),
            function( $field ) { return ! in_array( $field->type, array( 'captcha', 'text-content' ) ); }
        );

        $this->render( '_appointment_dialog', compact( 'custom_fields' ) );
    }

    /**
     * Render delete appointment dialog
     */
    public function renderDeleteDialog()
    {
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/delete_dialog.js' => array( 'jquery' ),
            ),
        ) );
        $this->render( '_delete_dialog' );
    }

}