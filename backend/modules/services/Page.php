<?php
namespace Bookly\Backend\Modules\Services;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Services
 */
class Page extends Lib\Base\Ajax
{
    /**
     * Render page.
     */
    public static function render()
    {
        wp_enqueue_media();
        self::enqueueStyles( array(
            'wp'       => array( 'wp-color-picker' ),
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css' ),
        ) );

        self::enqueueScripts( array(
            'wp'       => array( 'wp-color-picker' ),
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/help.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/range_tools.js' => array( 'jquery' ),
            ),
            'module'   => array( 'js/service.js' => array( 'jquery-ui-sortable', 'jquery' ) ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'bookly-spin.min.js', 'jquery' ),
            )
        ) );

        $data = self::_getCaSeStCollections();
        $staff = array();
        foreach ( $data['staff_collection'] as $employee ) {
            $staff[ $employee['id'] ] = $employee['full_name'];
        }

        wp_localize_script( 'bookly-service.js', 'BooklyL10n', array(
            'csrf_token'            => Lib\Utils\Common::getCsrfToken(),
            'capacity_error'        => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'are_you_sure'          => __( 'Are you sure?', 'bookly' ),
            'service_special_day'   => Lib\Config::specialDaysActive(),
            'reorder'               => esc_attr__( 'Reorder', 'bookly' ),
            'staff'                 => $staff,
        ) );

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueAssetsForServices();

        self::renderTemplate( 'index', $data );
    }

    /**
     * Array for rendering service list.
     *
     * @param int $category_id
     * @return array
     */
    protected static function _getCaSeStCollections( $category_id = 0 )
    {
        if ( ! $category_id ) {
            $category_id = self::parameter( 'category_id', 0 );
        }

        return array(
            'service_collection'  => self::_getServiceCollection( $category_id ),
            'staff_collection'    => self::_getStaffCollection(),
            'category_collection' => self::_getCategoryCollection(),
        );
    }

    /**
     * Get category collection.
     *
     * @return array
     */
    protected static function _getCategoryCollection()
    {
        return Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray();
    }

    /**
     * Get staff collection.
     *
     * @return array
     */
    protected static function _getStaffCollection()
    {
        return Lib\Entities\Staff::query()->fetchArray();
    }

    /**
     * Get service collection.
     *
     * @param int $id
     * @return array
     */
    protected static function _getServiceCollection( $id = 0 )
    {
        $services = Lib\Entities\Service::query( 's' )
            ->select( 's.*, COUNT(staff.id) AS total_staff, GROUP_CONCAT(DISTINCT staff.id) AS staff_ids' )
            ->leftJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
            ->leftJoin( 'Staff', 'staff', 'staff.id = ss.staff_id' )
            ->whereRaw( 's.category_id = %d OR !%d', array( $id, $id ) )
            ->groupBy( 's.id' )
            ->indexBy( 'id' )
            ->sortBy( 's.position' );
        if ( ! Lib\Config::packagesActive() ) {
            $services->whereNot( 's.type', Lib\Entities\Service::TYPE_PACKAGE );
        }
        $result = $services->fetchArray();
        foreach ( $result as &$service ) {
            $service['sub_services'] = Lib\Entities\SubService::query()
                ->where( 'service_id', $service['id'] )
                ->sortBy( 'position' )
                ->fetchArray()
            ;
            $service['sub_services_count'] = array_sum( array_map( function ( $sub_service ) {
                return (int) ( $sub_service['type'] == Lib\Entities\SubService::TYPE_SERVICE );
            }, $service['sub_services'] ) );
            $service['colors'] = Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service['color'] ), $service['id'], $service['type'] );
        }

        return $result;
    }
}