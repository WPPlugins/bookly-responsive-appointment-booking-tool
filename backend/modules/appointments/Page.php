<?php
namespace Bookly\Backend\Modules\Appointments;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Appointments
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array(
                'css/select2.min.css',
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/datatables.min.js'   => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'  => array( 'jquery' ),
                'js/select2.full.min.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/appointments.js' => array( 'bookly-datatables.min.js' ), ),
        ) );

        // Custom fields without captcha, text content field & file.
        $custom_fields = $cf_columns = array();
        foreach ( (array) Lib\Proxy\CustomFields::getWhichHaveData() as $cf ) {
            if ( $cf->type != 'file' ) {
                $cf_columns[]    = $cf->id;
                $custom_fields[] = $cf;
            }
        }
        // Show column attachments.
        $show_attachments = Lib\Config::filesActive() && count( Lib\Proxy\Files::getAllIds() ) > 0;
        wp_localize_script( 'bookly-appointments.js', 'BooklyL10n', array(
            'csrf_token'      => Lib\Utils\Common::getCsrfToken(),
            'datePicker'      => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'       => Lib\Utils\DateTime::dateRangeOptions( array( 'anyTime' => __( 'Any time', 'bookly' ), 'createdAtAnyTime' => __( 'Created at any time', 'bookly' ), ) ),
            'are_you_sure'    => __( 'Are you sure?', 'bookly' ),
            'zeroRecords'     => __( 'No appointments for selected period.', 'bookly' ),
            'processing'      => __( 'Processing...', 'bookly' ),
            'edit'            => __( 'Edit', 'bookly' ),
            'add_columns'     => array( 'ratings' => Lib\Config::ratingsActive(), 'number_of_persons' => Lib\Config::groupBookingActive(), 'notes' => Lib\Config::showNotes(), 'attachments' => $show_attachments, ),
            'cf_columns'      => $cf_columns,
            'filter'          => (array) get_user_meta( get_current_user_id(), 'bookly_filter_appointments_list', true ),
            'no_result_found' => __( 'No result found', 'bookly' ),
            'attachments'     => __( 'Attachments', 'bookly' ),
            'tasks'           => array(
                'enabled' => Lib\Config::tasksActive(),
                'title'   => Proxy\Tasks::getFilterText(),
            ),
        ) );

        // Filters data
        $staff_members = Lib\Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->whereNot( 'visibility', 'archive' )->fetchArray();
        $customers = Lib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.first_name, c.last_name' )->fetchArray();
        $services  = Lib\Entities\Service::query( 's' )->select( 's.id, s.title' )->where( 'type', Lib\Entities\Service::TYPE_SIMPLE )->fetchArray();

        self::renderTemplate( 'index', compact( 'custom_fields', 'staff_members', 'customers', 'services', 'show_attachments' ) );
    }
}