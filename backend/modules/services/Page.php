<?php
namespace Bookly\Backend\Modules\Services;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Proxy;

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
                'js/datatables.min.js'          => array( 'jquery' ),
                'js/alert.js'                   => array( 'jquery' ),
                'js/dropdown.js'                => array( 'jquery' ),
                'js/range_tools.js'             => array( 'jquery' ),
            ),
            'module'   => array( 'js/services-list.js' => array( 'jquery-ui-sortable', 'bookly-dropdown.js' ) ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'bookly-spin.min.js', 'jquery' ),
            ),
        ) );

        $staff = array();
        foreach ( self::getStaffDropDownData() as $category ) {
            foreach ( $category['items'] as $employee ) {
                $staff[ $employee['id'] ] = $employee['full_name'];
            }
        }

        wp_localize_script( 'bookly-services-list.js', 'BooklyL10n', array(
            'csrfToken'        => Lib\Utils\Common::getCsrfToken(),
            'are_you_sure'     => __( 'Are you sure?', 'bookly' ),
            'edit'             => __( 'Edit...', 'bookly' ),
            'reorder'          => esc_attr__( 'Reorder', 'bookly' ),
            'staff'            => $staff,
            'categories'       => Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray(),
            'uncategorized'    => __( 'Uncategorized', 'bookly' ),
            'capacity_error'   => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'recurrence_error' => __( 'You must select at least one repeat option for recurring services.', 'bookly' ),
            'show_type'        => count( Proxy\Shared::prepareServiceTypes( array() ) ) > 0,
        ) );

        // Allow add-ons to enqueue their assets.
        Proxy\Shared::enqueueAssetsForServices();

        $services = Lib\Entities\Service::query( 's' )
            ->whereIn( 's.type', array_keys( Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => Lib\Entities\Service::TYPE_SIMPLE ) ) ) )
            ->sortBy( 'position' )
            ->fetchArray();
        foreach ( $services as &$service ) {
            $service['colors']             = Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service['color'] ), $service['id'], $service['type'] );
            $service['sub_services']       = Lib\Entities\SubService::query()
                ->where( 'service_id', $service['id'] )
                ->sortBy( 'position' )
                ->fetchArray();
            $service['sub_services_count'] = array_sum( array_map( function ( $sub_service ) {
                return (int) ( $sub_service['type'] == Lib\Entities\SubService::TYPE_SERVICE );
            }, $service['sub_services'] ) );
        }
        $data['services'] = $services;
        $data['service_types'] = Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => __( 'Simple', 'bookly' ) ) );

        self::renderTemplate( 'index', $data );
    }

    /**
     * Get data for staff drop-down.
     *
     * @return array
     */
    public static function getStaffDropDownData()
    {
        if ( Lib\Config::proActive() ) {
            return Lib\Proxy\Pro::getStaffDataForDropDown();
        } else {
            $items = Lib\Entities\Staff::query()
                ->select( 'id, full_name' )
                ->whereNot( 'visibility', 'archive' )
                ->sortBy( 'position' )
                ->fetchArray();

            return array(
                0 => array(
                    'name'  => '',
                    'items' => $items,
                ),
            );
        }
    }
}