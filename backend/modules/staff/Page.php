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
        wp_enqueue_media();
        self::enqueueStyles( array(
            'frontend' => array_merge(
                array( 'css/ladda.min.css', ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', 'css/jquery-ui-theme/jquery-ui.min.css', 'css/fontawesome-all.min.css' ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/jCal.js'                    => array( 'jquery' ),
                'js/help.js'                    => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/dropdown.js'                => array( 'jquery' ),
                'js/range_tools.js'             => array( 'jquery' ),
                'js/moment.min.js',
            ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'module'   => array(
                'js/staff-details.js'  => array( 'bookly-alert.js', 'bookly-dropdown.js' ),
                'js/staff-services.js' => array( 'bookly-staff-details.js' ),
                'js/staff-schedule.js' => array( 'bookly-staff-services.js' ),
                'js/staff-days-off.js' => array( 'bookly-staff-schedule.js' ),
                'js/staff.js'          => array( 'jquery-ui-sortable', 'jquery-ui-datepicker', 'bookly-range_tools.js', 'bookly-staff-days-off.js' ),
            ),
        ) );

        $query = Lib\Utils\Common::isCurrentUserAdmin()
            ? Lib\Entities\Staff::query()->sortBy( 'position' )
            : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() );

        $staff_members = $query
            ->addSelect( sprintf( '%s AS category_id', Lib\Proxy\Shared::prepareStatement( 'null', 'category_id', 'Staff' ) ) )
            ->fetchArray();

        $active_staff_id = 0;
        if ( self::hasParameter( 'staff_id' ) ) {
            $active_staff_id = self::parameter( 'staff_id' );
        }

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueStaffProfileStyles();
        Proxy\Shared::enqueueStaffProfileScripts();
        $active_staff_id = Proxy\Shared::renderStaffPage( $active_staff_id, self::parameters() );

        wp_localize_script( 'bookly-staff.js', 'BooklyL10n', array(
            'are_you_sure'          => __( 'Are you sure?', 'bookly' ),
            'saved'                 => __( 'Settings saved.', 'bookly' ),
            'capacity_error'        => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'intlTelInput'          => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => is_rtl() ? '' : plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
            'csrf_token'            => Lib\Utils\Common::getCsrfToken(),
            'locations_custom'      => (int) Lib\Proxy\Locations::servicesPerLocationAllowed(),
            'pro_required'          => (int) ! Lib\Config::proActive(),
            'limitation'            => Limitation::getHtml(),
            'schedule_intersection' => __( 'The working time in the provider\'s schedule is associated with another location.', 'bookly' ),
            'active_staff_id'       => $active_staff_id
        ) );

        $form            = new Forms\StaffMember();
        $users_for_staff = $form->getUsersForStaff();

        $categories_filter = get_user_meta( get_current_user_id(), 'bookly_filter_staff_categories', true );
        if ( $categories_filter == '' ) $categories_filter = array();
        $categories = (array) Proxy\Pro::getCategoriesList();
        $categories[] = array( 'id' => null, 'name' => __( 'Uncategorized', 'bookly' ) );
        foreach ( $categories as $index => $category ) {
            $categories[ $index ]['collapsed'] = isset( $categories_filter[ $category['id'] ?: 0 ] ) && $categories_filter[ $category['id'] ?: 0 ];
        }

        self::renderTemplate( 'index', compact( 'staff_members', 'categories', 'users_for_staff', 'active_staff_id' ) );
    }
}