<?php
namespace BooklyLite\Backend\Modules\Services;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Services
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-services';

    /**
     * Index page.
     */
    public function index()
    {
        wp_enqueue_media();
        $this->enqueueStyles( array(
            'wp'       => array( 'wp-color-picker' ),
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css' ),
        ) );

        $this->enqueueScripts( array(
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

        $data = $this->getCaSeStSpCollections();
        $staff = array();
        foreach ( $data['staff_collection'] as $employee ) {
            $staff[ $employee['id'] ] = $employee['full_name'];
        }

        wp_localize_script( 'bookly-service.js', 'BooklyL10n', array(
            'csrf_token'            => Lib\Utils\Common::getCsrfToken(),
            'capacity_error'        => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'are_you_sure'          => __( 'Are you sure?', 'bookly' ),
            'service_special_day'   => Lib\Config::specialDaysEnabled() && Lib\Config::specialDaysEnabled(),
            'reorder'               => esc_attr__( 'Reorder', 'bookly' ),
            'staff'                 => $staff,
            'limitations'           => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        // Allow add-ons to enqueue their assets.
        Lib\Proxy\Shared::enqueueAssetsForServices();

        $this->render( 'index', $data );
    }

    /**
     *
     */
    public function executeGetCategoryServices()
    {
        wp_send_json_success( $this->render( '_list', $this->getCaSeStSpCollections(), false ) );
    }

    /**
     *
     */
    public function executeAddCategory()
    {
        $html = '';
        if ( ! empty ( $_POST ) ) {
            if ( $this->csrfTokenValid() ) {
                $form = new Forms\Category();
                $form->bind( $this->getPostParameters() );
                if ( $category = $form->save() ) {
                    $html = $this->render( '_category_item', array( 'category' => $category->getFields() ), false );
                }
            }
        }
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Update category.
     */
    public function executeUpdateCategory()
    {
        $form = new Forms\Category();
        $form->bind( $this->getPostParameters() );
        $form->save();
    }

    /**
     * Update category position.
     */
    public function executeUpdateCategoryPosition()
    {
        $category_sorts = $this->getParameter( 'position' );
        foreach ( $category_sorts as $position => $category_id ) {
            $category_sort = new Lib\Entities\Category();
            $category_sort->load( $category_id );
            $category_sort->setPosition( $position );
            $category_sort->save();
        }
    }

    /**
     * Update services position.
     */
    public function executeUpdateServicesPosition()
    {
        $services_sorts = $this->getParameter( 'position' );
        foreach ( $services_sorts as $position => $service_ids ) {
            $services_sort = new Lib\Entities\Service();
            $services_sort->load( $service_ids );
            $services_sort->setPosition( $position );
            $services_sort->save();
        }
    }

    /**
     * Reorder staff preferences for service
     */
    public function executeUpdateServiceStaffPreferenceOrders()
    {
        $service_id  = $this->getParameter( 'service_id' );
        $positions = (array) $this->getParameter( 'positions' );
        /** @var Lib\Entities\StaffPreferenceOrder[] $staff_preferences */
        $staff_preferences = Lib\Entities\StaffPreferenceOrder::query()
            ->where( 'service_id', $service_id )
            ->indexBy( 'staff_id' )
            ->find();
        foreach ( $positions as $position => $staff_id ) {
            if ( array_key_exists( $staff_id, $staff_preferences ) ) {
                $staff_preferences[ $staff_id ]->setPosition( $position )->save();
            } else {
                $preference = new Lib\Entities\StaffPreferenceOrder();
                $preference
                    ->setServiceId( $service_id )
                    ->setStaffId( $staff_id )
                    ->setPosition( $position )
                    ->save();
            }
        }

        wp_send_json_success();
    }

    /**
     * Delete category.
     */
    public function executeDeleteCategory()
    {
        $category = new Lib\Entities\Category();
        $category->setId( $this->getParameter( 'id', 0 ) );
        $category->delete();
    }

    public function executeAddService()
    {
        $form = new Forms\Service();
        $form->bind( $this->getPostParameters() );
        $form->getObject()->setDuration( Lib\Config::getTimeSlotLength() );
        $service = $form->save();
        $data = $this->getCaSeStSpCollections( $service->getCategoryId() );
        Lib\Proxy\Shared::serviceCreated( $service, $this->getPostParameters() );
        wp_send_json_success( array( 'html' => $this->render( '_list', $data, false ), 'service_id' => $service->getId() ) );
    }

    public function executeRemoveServices()
    {
        $service_ids = $this->getParameter( 'service_ids', array() );
        if ( is_array( $service_ids ) && ! empty ( $service_ids ) ) {
            foreach ( $service_ids as $service_id ) {
                Lib\Proxy\Shared::serviceDeleted( $service_id );
            }
            Lib\Entities\Service::query( 's' )->delete()->whereIn( 's.id', $service_ids )->execute();
        }
        wp_send_json_success();
    }

    /**
     * Update service parameters and assign staff
     */
    public function executeUpdateService()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $form = new Forms\Service();
        $form->bind( $this->getPostParameters() );
        $service = $form->save();

        $staff_ids = $this->getParameter( 'staff_ids', array() );
        if ( empty ( $staff_ids ) ) {
            Lib\Entities\StaffService::query()->delete()->where( 'service_id', $service->getId() )->execute();
        } else {
            Lib\Entities\StaffService::query()->delete()->where( 'service_id', $service->getId() )->whereNotIn( 'staff_id', $staff_ids )->execute();
            if ( $service->getType() == Lib\Entities\Service::TYPE_SIMPLE ) {
                if ( $this->getParameter( 'update_staff', false ) ) {
                    $data = array(
                        'price'        => $this->getParameter( 'price' ),
                        'capacity_min' => 1,
                        'capacity_max' => 1,
                    );
                    $wpdb->update(
                        Lib\Entities\StaffService::getTableName(),
                        $data,
                        array( 'service_id' => $this->getParameter( 'id' ) )
                    );
                }
                // Create records for newly linked staff.
                $existing_staff_ids = array();
                $res = Lib\Entities\StaffService::query()
                    ->select( 'staff_id' )
                    ->where( 'service_id', $service->getId() )
                    ->fetchArray();
                foreach ( $res as $staff ) {
                    $existing_staff_ids[] = $staff['staff_id'];
                }
                foreach ( $staff_ids as $staff_id ) {
                    if ( ! in_array( $staff_id, $existing_staff_ids ) ) {
                        $staff_service = new Lib\Entities\StaffService();
                        $staff_service->setStaffId( $staff_id )
                            ->setServiceId( $service->getId() )
                            ->setPrice( $service->getPrice() )
                            ->setCapacityMin( 1 )
                            ->setCapacityMax( 1 )
                            ->save();
                    }
                }
            }
        }

        // Update services in addons.
        $alert = Lib\Proxy\Shared::updateService( array( 'success' => array( __( 'Settings saved.', 'bookly' ) ) ), $service, $this->getPostParameters() );

        $price = Lib\Utils\Price::format( $service->getPrice() );
        $nice_duration = Lib\Utils\DateTime::secondsToInterval( $service->getDuration() );
        $title = $service->getTitle();
        $colors = array_fill( 0, 3, $service->getColor() );
        wp_send_json_success( Lib\Proxy\Shared::prepareUpdateServiceResponse( compact( 'title', 'price', 'colors', 'nice_duration', 'alert' ), $service, $this->getPostParameters() ) );
    }

    /**
     * Array for rendering service list.
     *
     * @param int $category_id
     * @return array
     */
    private function getCaSeStSpCollections( $category_id = 0 )
    {
        if ( ! $category_id ) {
            $category_id = $this->getParameter( 'category_id', 0 );
        }

        return array(
            'service_collection'  => $this->getServiceCollection( $category_id ),
            'staff_collection'    => $this->getStaffCollection(),
            'category_collection' => $this->getCategoryCollection(),
            'staff_preference'    => array(
                Lib\Entities\Service::PREFERRED_ORDER           => __( 'Specified order', 'bookly' ),
                Lib\Entities\Service::PREFERRED_LEAST_OCCUPIED  => __( 'Least occupied that day', 'bookly' ),
                Lib\Entities\Service::PREFERRED_MOST_OCCUPIED   => __( 'Most occupied that day', 'bookly' ),
                Lib\Entities\Service::PREFERRED_LEAST_EXPENSIVE => __( 'Least expensive', 'bookly' ),
                Lib\Entities\Service::PREFERRED_MOST_EXPENSIVE  => __( 'Most expensive', 'bookly' ),
            ),
        );
    }

    /**
     * @return array
     */
    private function getCategoryCollection()
    {
        return Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray();
    }

    /**
     * @return array
     */
    private function getStaffCollection()
    {
        return Lib\Entities\Staff::query()->fetchArray();
    }

    /**
     * @param int $id
     * @return array
     */
    private function getServiceCollection( $id = 0 )
    {
        $services = Lib\Entities\Service::query( 's' )
            ->select( 's.*, COUNT(staff.id) AS total_staff, GROUP_CONCAT(DISTINCT staff.id) AS staff_ids, GROUP_CONCAT(DISTINCT sp.staff_id ORDER BY sp.position ASC) AS pref_staff_ids' )
            ->leftJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
            ->leftJoin( 'StaffPreferenceOrder', 'sp', 'sp.service_id = s.id' )
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
            $service['colors'] = Lib\Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service['color'] ), $service['id'], $service['type'] );
        }

        return $result;
    }

    public function executeUpdateExtraPosition()
    {
        Lib\Proxy\ServiceExtras::reorder( $this->getParameter( 'position' ) );
        wp_send_json_success();
    }
}