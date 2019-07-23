<?php
namespace Bookly\Backend\Modules\Staff;

use Bookly\Backend\Components\Notices\Limitation;
use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Staff
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', 'css/jquery-ui-theme/jquery-ui.min.css', 'css/fontawesome-all.min.css' ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/help.js'                    => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/datatables.min.js'          => array( 'jquery' ),
            ),
            'module'   => array(
                'js/staff-list.js'     => array( 'jquery' ),
            ),
        ) );

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueStaffProfileStyles();
        Proxy\Shared::enqueueStaffProfileScripts();
        Proxy\Shared::renderStaffPage( self::parameters() );

        wp_localize_script( 'bookly-staff-list.js', 'BooklyL10n', array(
            'csrfToken'     => Lib\Utils\Common::getCsrfToken(),
            'proRequired'   => (int) ! Lib\Config::proActive(),
            'areYouSure'    => __( 'Are you sure?', 'bookly' ),
            'filter'        => (array) get_user_meta( get_current_user_id(), 'bookly_filter_staff_list', true ),
            'categories'    => (array) Proxy\Pro::getCategoriesList(),
            'uncategorized' => __( 'Uncategorized', 'bookly' ),
            'edit'          => __( 'Edit...', 'bookly' ),
            'reorder'       => esc_attr__( 'Reorder', 'bookly' ),
        ) );

        self::renderTemplate( 'index' );
    }
}