<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_CategoryForm.php';
include 'forms/AB_ServiceForm.php';

/**
 * Class AB_ServiceController
 */
class AB_ServiceController extends AB_Controller {

    /**
     *
     */
    public function index() {
        $this->staff_collection    = $this->getStaffCollection();
        $this->category_collection = $this->getCategoryCollection();
        $this->service_collection  = $this->getServiceCollection();
        $this->render( 'index' );
    }

    /**
     *
     */
    public function executeCategoryServices() {
        $this->setDataForServiceList();
        $this->render( 'list' );
        exit;
    }

    /**
     *
     */
    public function executeCategoryForm() {
        $this->form = new AB_CategoryForm();

        if ( count( $this->getPost() ) ) {
            $this->form->bind( $this->getPost() );
            if ( $category = $this->form->save() ) {
                echo "<div class='ab-category-item' data-id='{$category->id}'>
                    <span class='left displayed-value'>{$category->name}</span>
                    <a href='#' class='left ab-hidden ab-edit'></a>
                    <input class=value type=text name=name value='{$category->name}' style='display: none' />
                    <a href='#' class='left ab-hidden ab-delete'></a></div>";
                exit;
            }
        }
        exit;
    }

    /**
     * Update category.
     */
    public function executeUpdateCategory() {
        $form = new AB_CategoryForm();
        $form->bind( $this->getPost() );
        $form->save();
    }

    /**
     * Delete category.
     */


      public function executeDeleteCategory() {
        $category = new AB_Category();
        $category->set( 'id', $this->getParameter( 'id', 0 ) );
        $category->delete();
      }


    public function executeAddService() {
        $form = new AB_ServiceForm();
        $form->bind( $this->getPost() );
        $service = $form->save();
        $this->setDataForServiceList( $service->get( 'category_id' ) );
        $this->render( 'list' );
        exit;
    }

    public function executeRemoveServices() {
        $_post = $this->getPost();
        $service_ids = isset( $_post['service_ids'] ) ? $_post['service_ids'] : array();
        if ( is_array( $service_ids ) && ! empty( $service_ids ) ) {
            $this->getWpdb()->query('DELETE FROM ab_service WHERE id IN (' . implode(', ', $service_ids) . ')');
        }
    }

    public function executeAssignStaff() {
        $_post      = $this->getPost();
        $service_id = isset( $_post['service_id'] ) ? $_post['service_id'] : 0;
        $staff_ids  = isset( $_post['staff_ids'] )  ? $_post['staff_ids']  : array();

        if ( $service_id ) {
            $wpDb = $this->getWpdb();
            $wpDb->query( $wpDb->prepare( 'DELETE FROM ab_staff_service WHERE service_id = %d', $service_id ) );
            $service = new AB_Service();
            if ( ! empty( $staff_ids ) && $service->load( $service_id ) ) {
                foreach ( $staff_ids as $staff_id ) {
                    $staff_service = new AB_StaffService();
                    $staff_service->set( 'staff_id',   $staff_id );
                    $staff_service->set( 'service_id', $service_id );
                    $staff_service->set( 'price',      $service->get( 'price' ) );
                    $staff_service->save();
                }
            }
            echo count( $staff_ids );
            exit;
        }
    }

    public function executeUpdateServiceValue() {
        $form = new AB_ServiceForm();
        $form->bind($this->getPost());
        $form->save();
    }

    private function setDataForServiceList( $category_id = 0 ) {
        if (!$category_id) {
            $category_id = isset( $_GET['category_id'] ) ? intval( $_GET['category_id'] ) : 0;
        }

        $this->service_collection  = $this->getServiceCollection($category_id);
        $this->staff_collection    = $this->getStaffCollection();
        $this->category_collection = $this->getCategoryCollection();
    }

    private function getCategoryCollection() {
        return $this->getWpdb()->get_results( "SELECT * FROM ab_category" );
    }

    private function getStaffCollection() {
        return $this->getWpdb()->get_results( "SELECT * FROM ab_staff" );
    }

    private function getServiceCollection( $id = 0 ) {
        if ( $id )  {
            $query = $this->getWpdb()->prepare(
                'SELECT
                   service.*,
                   COUNT(staff.id) AS "total_staff",
                   GROUP_CONCAT(DISTINCT staff.id) AS "staff_ids"
                 FROM ab_service service
                   LEFT JOIN ab_staff_service staff_service ON service.id = staff_service.service_id
                   LEFT JOIN ab_staff staff ON staff.id = staff_service.staff_id
                 WHERE service.category_id = %d
                 GROUP BY service.id, "total_staff"',
                 $id
            );
        } else {
            $query = 'SELECT
                        service.*,
                        COUNT(staff.id) AS "total_staff",
                        GROUP_CONCAT(DISTINCT staff.id) AS "staff_ids"
                      FROM ab_service service
                        LEFT JOIN ab_staff_service staff_service ON service.id = staff_service.service_id
                        LEFT JOIN ab_staff staff ON staff.id = staff_service.staff_id
                      GROUP BY service.id, "total_staff"';
        }
        return $this->getWpdb()->get_results($query);
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}
