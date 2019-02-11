<?php
namespace Bookly\Backend\Components\Gutenberg\BooklyForm;

use Bookly\Lib;

/**
 * Class Block
 * @package Bookly\Backend\Components\Gutenberg\BooklyForm
 */
class Block extends Lib\Base\Block
{
    /**
     * @inheritdoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/booking-form-block.js' => array( 'jquery', 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        wp_localize_script( 'bookly-booking-form-block.js', 'BooklyFormL10n', array(
            'casest'         => Lib\Config::getCaSeSt(),
            'block' => array(
                'title'       => 'Bookly - ' . __( 'Booking form', 'bookly' ),
                'description' => __( 'A custom block for displaying booking form', 'bookly' ),
            ),
            'selectLocation' => __( 'Select location', 'bookly' ),
            'selectCategory' => __( 'Select category', 'bookly' ),
            'any'            => __( 'Any', 'bookly' ),
            'formFields'     => __( 'Form fields', 'bookly' ),
            'location'       => __( 'Default value for location', 'bookly' ),
            'category'       => __( 'Default value for category', 'bookly' ),
            'service'        => __( 'Default value for service', 'bookly' ),
            'staff'          => __( 'Default value for employee', 'bookly' ),
            'nop'            => __( 'Number of persons', 'bookly' ),
            'quantity'       => __( 'Quantity', 'bookly' ),
            'date'           => __( 'Date', 'bookly' ),
            'weekDays'       => __( 'Week days', 'bookly' ),
            'timeRange'      => __( 'Time range', 'bookly' ),
            'hide'           => __( 'hide', 'bookly' ),
            'fields'         => __( 'Fields', 'bookly' ),
            'duration'       => __( 'Duration', 'bookly' ),
            'locationCustom' => (int) Lib\Proxy\Locations::servicesPerLocationAllowed(),
            'addons'         => array(
                'customDuration'       => (int) Lib\Config::customDurationActive(),
                'groupBooking'         => (int) Lib\Config::groupBookingActive(),
                'locations'            => (int) Lib\Config::locationsActive(),
                'multiplyAppointments' => (int) Lib\Config::multiplyAppointmentsActive(),
            ),
        ) );

        register_block_type( 'bookly/form-block', array(
            'editor_script' => 'bookly-booking-form-block.js',
        ) );
    }
}