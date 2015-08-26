<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_ServiceController
 */
class AB_ServiceController extends AB_Controller {

    const page_slug = 'ab-services';
    /**
     * Index page.
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'wp' => array(
                'wp-color-picker',
            ),
            'frontend' => array(
                'css/ladda.min.css',
            ),
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
            ),
            'module' => array(
                'css/service.css',
            )
        ) );

        $this->enqueueScripts( array(
            'wp' => array(
                'wp-color-picker',
            ),
            'backend' => array(
                'js/ab_popup.js' => array( 'jquery' ),
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/service.js' => array( 'jquery-ui-sortable', 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'ab-spin.min.js', 'jquery' ),
            )
        ) );

        wp_localize_script( 'ab-service.js', 'BooklyL10n', array(
            'are_you_sure'      => __( 'Are you sure?', 'bookly' ),
            'no_staff_selected' => __( 'No staff selected', 'bookly' ),
            'please_select_at_least_one_service' => __( 'Please select at least one service.', 'bookly' ),
        ) );

        $this->staff_collection    = $this->getStaffCollection();
        $this->category_collection = $this->getCategoryCollection();
        $this->service_collection  = $this->getServiceCollection();
        $this->render( 'index' );
    }

    /**
     *
     */
    public function executeCategoryServices()
    {
        $this->setDataForServiceList();
        $this->render( '_list' );
        exit;
    }

    /**
     *
     */
    public function executeCategoryForm()
    {
        $this->form = new AB_CategoryForm();

        if ( ! empty ( $_POST ) ) {
            $this->form->bind( $this->getPostParameters() );
            if ( $category = $this->form->save() ) {
                echo "<li class='ab-category-item' data-id='{$category->id}'>
                    <span class='ab-handle'><i class='ab-inner-handle glyphicon glyphicon-move'></i></span>
                    <span class='left displayed-value'>{$category->name}</span>
                    <a href='#' class='left ab-hidden ab-edit'></a>
                    <input class=value type=text name=name value='{$category->name}' style='display: none' />
                    <a href='#' class='left ab-hidden ab-delete'></a></li>";
                // Register string for translate in WPML.
                do_action( 'wpml_register_single_string', 'bookly', 'category_' . $category->id, $category->name );
                exit;
            }
        }
        exit;
    }

    /**
     * Update category.
     */
    public function executeUpdateCategory()
    {
        $form = new AB_CategoryForm();
        $form->bind( $this->getPostParameters() );
        $category = $form->save();
        // Register string for translate in WPML.
        do_action( 'wpml_register_single_string', 'bookly', 'category_' . $category->id, $category->name );
    }

    /**
     * Update category position.
     */
    public function executeUpdateCategoryPosition()
    {
        $category_sorts = $this->getParameter( 'position' );
        foreach ( $category_sorts as $position => $category_id ) {
            $category_sort = new AB_Category();
            $category_sort->load( $category_id );
            $category_sort->set( 'position', $position );
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
            $services_sort = new AB_Service();
            $services_sort->load( $service_ids );
            $services_sort->set( 'position', $position );
            $services_sort->save();
        }
    }

    /**
     * Delete category.
     */
    public function executeDeleteCategory()
    {
        $category = new AB_Category();
        $category->set( 'id', $this->getParameter( 'id', 0 ) );
        $category->delete();
    }

    public function executeAddService()
    {
        $form = new AB_ServiceForm();
        $form->bind( $this->getPostParameters() );
        $form->getObject()->set( 'duration', get_option( 'ab_settings_time_slot_length' ) * 60 );
        $service = $form->save();
        $this->setDataForServiceList( $service->get( 'category_id' ) );
        wp_send_json_success( array( 'html' => $this->render( '_list', array(), false ), 'service_id' =>  $service->get( 'id' ) ) );
    }

    public function executeRemoveServices()
    {
        $service_ids = $this->getParameter( 'service_ids', array() );
        if ( is_array( $service_ids ) && ! empty ( $service_ids ) ) {
            AB_Service::query( 's' )->delete()->whereIn( 's.id', $service_ids )->execute();
        }
    }

    public function executeAssignStaff()
    {
        $service_id = $this->getParameter( 'service_id', 0 );
        $staff_ids  = $this->getParameter( 'staff_ids', array() );

        if ( $service_id ) {
            AB_StaffService::query()->delete()->where( 'service_id', $service_id )->execute();
            $service = new AB_Service();
            if ( ! empty( $staff_ids ) && $service->load( $service_id ) ) {
                foreach ( $staff_ids as $staff_id ) {
                    $staff_service = new AB_StaffService();
                    $staff_service->set( 'staff_id', $staff_id );
                    $staff_service->set( 'service_id', $service_id );
                    $staff_service->set( 'price', $service->get( 'price' ) );
                    $staff_service->save();
                }
            }
            wp_send_json_success( count( $staff_ids ) );
        }
        wp_send_json_error();
    }

    public function executeUpdateServiceValue()
    {
        /** @var $wpdb  wpdb */
        global $wpdb;

        $form = new AB_ServiceForm();
        $form->bind( $this->getPostParameters() );
        $service = $form->save();

        if ( $this->getParameter( 'update_staff', false ) ) {
            if ( $this->getParameter( 'price' ) ) {
                $wpdb->update( AB_StaffService::getTableName(), array( 'price' => $this->getParameter( 'price' ) ), array( 'service_id' => $this->getParameter( 'id' ) ) );
            }
            if ( $this->getParameter( 'capacity' ) ) {
                $wpdb->update( AB_StaffService::getTableName(), array( 'capacity' => $this->getParameter( 'capacity' ) ), array( 'service_id' => $this->getParameter( 'id' ) ) );
            }
        }
        // Register string for translate in WPML.
        if ( $this->hasParameter( 'title' ) ) {
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $service->id, $service->title );
        }

        $staff_ids  = $this->getParameter( 'staff_ids', array() );
        if ( $service->id ) {
            AB_StaffService::query()->delete()->where( 'service_id', $service->id )->execute();
            if ( ! empty( $staff_ids ) ) {
                foreach ( $staff_ids as $staff_id ) {
                    $staff_service = new AB_StaffService();
                    $staff_service->set( 'staff_id', $staff_id );
                    $staff_service->set( 'service_id', $service->id );
                    $staff_service->set( 'price', $service->get( 'price' ) );
                    $staff_service->save();
                }
            }
        }

        wp_send_json_success( array( 'title' => $service->title, 'price' => AB_Utils::formatPrice( $service->price ), 'color' => $service->color, 'nice_duration' => AB_DateTimeUtils::secondsToInterval( $service->duration ) ) );
    }

    /**
     * @param int $category_id
     */
    private function setDataForServiceList( $category_id = 0 )
    {
        if ( ! $category_id ) {
            $category_id = $this->getParameter( 'category_id', 0 );
        }

        $this->service_collection  = $this->getServiceCollection( $category_id );
        $this->staff_collection    = $this->getStaffCollection();
        $this->category_collection = $this->getCategoryCollection();
    }

    /**
     * @return mixed
     */
    private function getCategoryCollection()
    {
        return AB_Category::query()->sortBy( 'position' )->fetchArray();
    }

    /**
     * @return mixed
     */
    private function getStaffCollection() {

        return AB_Staff::query()->fetchArray();
    }

    /**
     * @param int $id
     * @return mixed
     */
    private function getServiceCollection( $id = 0 )
    {
        $services = AB_Service::query( 's' )
            ->select( 's.*, COUNT(staff.id) AS total_staff, GROUP_CONCAT(DISTINCT staff.id) AS staff_ids' )
            ->leftJoin( 'AB_StaffService', 'ss', 'ss.service_id = s.id' )
            ->leftJoin( 'AB_Staff', 'staff', 'staff.id = ss.staff_id' )
            ->whereRaw( 's.category_id = %d OR !%d', array( $id, $id ) )
            ->groupBy( 's.id' )
            ->sortBy( 'ISNULL(s.position), s.position' );

        return $services->fetchArray();
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}