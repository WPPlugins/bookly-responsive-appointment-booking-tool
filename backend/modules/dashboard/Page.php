<?php
namespace Bookly\Backend\Modules\Dashboard;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Dashboard
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend' => array(
                'bootstrap/css/bootstrap-theme.min.css',
                'css/daterangepicker.css',
            ),
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/moment.min.js',
                'js/daterangepicker.js'  => array( 'jquery' ),
            ),
            'module' => array(
                'js/dashboard.js'        => array( 'jquery', 'bookly-appointments-dashboard.js' ),
            ),
        ) );
        wp_localize_script( 'bookly-dashboard.js', 'BooklyL10n', array(
            'csrfToken'  => Lib\Utils\Common::getCsrfToken(),
            'datePicker' => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'  => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
        ) );

        self::renderTemplate( 'index' );
    }
}