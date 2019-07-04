<?php
namespace Bookly\Backend\Modules\Services;

use Bookly\Backend\Components\Notices\Limitation;
use Bookly\Backend\Modules\Appointments;
use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Services
 */
class Ajax extends Page
{
    /**
     * Get services data for data tables
     */
    public static function getServices()
    {
        $query = Lib\Entities\Service::query( 's' )
            ->whereIn( 's.type', array_keys( Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => Lib\Entities\Service::TYPE_SIMPLE ) ) ) )
            ->sortBy( 'position' );

        $type_icons = Proxy\Shared::prepareServiceIcons( array( Lib\Entities\Service::TYPE_SIMPLE => 'fa-calendar-day' ) );

        $data = array();
        /** @var Lib\Entities\Service $service */
        foreach ( $query->find() as $service ) {
            $sub_services_count = count( $service->getSubServices() );
            $data[]             = array(
                'id'        => $service->getId(),
                'title'     => $service->getTitle(),
                'position'  => sprintf( '%05d-%05d', $service->getPosition(), $service->getId() ),
                'category'  => $service->getCategoryId(),
                'colors'    => Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service->getColor() ), $service->getId(), $service->getType() ),
                'type'      => ucfirst( $service->getType() ),
                'type_icon' => $type_icons[ $service->getType() ],
                'price'     => Lib\Utils\Price::format( $service->getPrice() ),
                'duration'  => in_array( $service->getType(), array(
                    Lib\Entities\Service::TYPE_COLLABORATIVE,
                    Lib\Entities\Service::TYPE_COMPOUND,
                ) )
                    ? sprintf( _n( '%d service', '%d services', $sub_services_count, 'bookly' ), $sub_services_count )
                    : Lib\Utils\DateTime::secondsToInterval( $service->getDuration() ),
            );
        }

        wp_send_json_success( $data );
    }

    /**
     * Update services position.
     */
    public static function updateServicesPosition()
    {
        $services_sorts = self::parameter( 'positions' );
        foreach ( $services_sorts as $position => $service_id ) {
            $services_sort = new Lib\Entities\Service();
            $services_sort->load( $service_id );
            $services_sort->setPosition( $position );
            $services_sort->save();
        }

        wp_send_json_success();
    }

    /**
     * Add service.
     */
    public static function createService()
    {
        ! Lib\Config::proActive() &&
        get_option( 'bookly_updated_from_legacy_version' ) != 'lite' &&
        Lib\Entities\Service::query()->count() > 4 &&
        wp_send_json_error( array( 'message' => Limitation::getHtml() ) );

        $form = new Forms\Service();
        $form->bind( self::postParameters() );
        $form->getObject()->setDuration( Lib\Config::getTimeSlotLength() );
        $service = $form->save();

        Proxy\Shared::serviceCreated( $service, self::postParameters() );

        $sub_services_count = array_sum( array_map( function ( $sub_service ) {
            /** @var Lib\Entities\Service $sub_service */
            return (int) ( $sub_service->getType() == Lib\Entities\SubService::TYPE_SERVICE );
        }, $service->getSubServices() ) );

        wp_send_json_success( array(
            'id'       => $service->getId(),
            'type'     => $service->getType(),
            'title'    => $service->getTitle(),
            'category' => $service->getCategoryId(),
            'colors'   => Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service->getColor() ), $service->getId(), $service->getType() ),
            'duration' => in_array( $service->getType(), array(
                Lib\Entities\Service::TYPE_COLLABORATIVE,
                Lib\Entities\Service::TYPE_COMPOUND,
            ) ) ? sprintf( _n( '%d service', '%d services', $sub_services_count, 'bookly' ), $sub_services_count ) : Lib\Utils\DateTime::secondsToInterval( $service->getDuration() ),
        ) );
    }

    /**
     * Edit Service
     */
    public static function getServiceData()
    {
        $service_id              = self::parameter( 'id' );
        $service_collection_data = Lib\Entities\Service::query( 's' )
            ->select( 's.*, COUNT(staff.id) AS total_staff, GROUP_CONCAT(DISTINCT staff.id) AS staff_ids' )
            ->leftJoin( 'StaffService', 'ss', 'ss.service_id = s.id' )
            ->leftJoin( 'Staff', 'staff', 'staff.id = ss.staff_id' )
            ->whereIn( 's.type', array_keys( Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => Lib\Entities\Service::TYPE_SIMPLE ) ) ) )
            ->groupBy( 's.id' )
            ->fetchArray();
        $service_collection      = array();
        foreach ( $service_collection_data as $current_service ) {
            if ( $current_service['id'] == $service_id ) {
                $service = $current_service;
            }
            $service_collection[ $current_service['id'] ] = $current_service;
        }
        $service['sub_services']       = Lib\Entities\SubService::query()
            ->where( 'service_id', $service['id'] )
            ->sortBy( 'position' )
            ->fetchArray();
        $service['sub_services_count'] = array_sum( array_map( function ( $sub_service ) {
            return (int) ( $sub_service['type'] == Lib\Entities\SubService::TYPE_SERVICE );
        }, $service['sub_services'] ) );
        $service['colors']             = Proxy\Shared::prepareServiceColors( array_fill( 0, 3, $service['color'] ), $service['id'], $service['type'] );

        $staff_dropdown_data = self::getStaffDropDownData();

        $categories_collection = Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray();
        $service_types         = Proxy\Shared::prepareServiceTypes( array( Lib\Entities\Service::TYPE_SIMPLE => __( 'Simple', 'bookly' ) ) );
        $result                = array(
            'general_html'      => self::renderTemplate( 'general', compact( 'service', 'service_types', 'service_collection', 'staff_dropdown_data', 'categories_collection' ), false ),
            'advanced_html'     => self::renderTemplate( 'advanced', compact( 'service', 'service_types', 'service_collection', 'staff_dropdown_data', 'categories_collection' ), false ),
            'time_html'         => self::renderTemplate( 'time', compact( 'service', 'service_types', 'service_collection', 'staff_dropdown_data', 'categories_collection' ), false ),
            'extras_html'       => Proxy\ServiceExtras::getTabHtml( $service_id ),
            'schedule_html'     => Proxy\ServiceSchedule::getTabHtml( $service_id ),
            'special_days_html' => Proxy\ServiceSpecialDays::getTabHtml( $service_id ),
            'additional_html'   => Proxy\Shared::prepareAfterServiceList( '', $service_collection ),
            'title'             => $service['title'],
            'type'              => $service['type'],
            'price'             => Lib\Utils\Price::format( $service['price'] ),
            'duration'          => in_array( $service['type'], array(
                Lib\Entities\Service::TYPE_COLLABORATIVE,
                Lib\Entities\Service::TYPE_COMPOUND,
            ) ) ? sprintf( _n( '%d service', '%d services', $service['sub_services_count'], 'bookly' ), $service['sub_services_count'] ) : Lib\Utils\DateTime::secondsToInterval( $service['duration'] ),
            'staff'             => $staff_dropdown_data,
        );

        wp_send_json_success( $result );
    }

    /**
     * 'Safely' remove services (report if there are future appointments)
     */
    public static function removeServices()
    {
        $service_ids = self::parameter( 'service_ids', array() );
        if ( self::parameter( 'force_delete', false ) ) {
            if ( is_array( $service_ids ) && ! empty ( $service_ids ) ) {
                foreach ( $service_ids as $service_id ) {
                    Proxy\Shared::serviceDeleted( $service_id );
                }
                Lib\Entities\Service::query( 's' )->delete()->whereIn( 's.id', $service_ids )->execute();
            }
        } else {
            $appointment = Lib\Entities\Appointment::query( 'a' )
                ->select( 'a.service_id, a.start_date' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->whereIn( 'a.service_id', $service_ids )
                ->whereGt( 'a.start_date', current_time( 'mysql' ) )
                ->whereIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                    Lib\Entities\CustomerAppointment::STATUS_PENDING,
                    Lib\Entities\CustomerAppointment::STATUS_APPROVED,
                ) ) )
                ->sortBy( 'a.start_date' )
                ->order( 'DESC' )
                ->limit( '1' )
                ->fetchRow();

            if ( $appointment ) {
                $last_month = date_create( $appointment['start_date'] )->modify( 'last day of' )->format( 'Y-m-d' );
                $action     = 'show_modal';
                $filter_url = sprintf( '%s#service=%d&appointment-date=%s-%s',
                    Lib\Utils\Common::escAdminUrl( Appointments\Page::pageSlug() ),
                    $appointment['service_id'],
                    date_create( current_time( 'mysql' ) )->format( 'Y-m-d' ),
                    $last_month );
                wp_send_json_error( compact( 'action', 'filter_url' ) );
            } else if ( $task = Lib\Entities\Appointment::query( 'a' )
                ->select( 'a.service_id' )
                ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                ->whereIn( 'a.service_id', $service_ids )
                ->where( 'a.start_date', null )
                ->whereIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                    Lib\Entities\CustomerAppointment::STATUS_PENDING,
                    Lib\Entities\CustomerAppointment::STATUS_APPROVED,
                ) ) )
                ->limit( 1 )
                ->fetchRow()
            ) {
                $action     = 'show_modal';
                $filter_url = sprintf( '%s#service=%d&tasks',
                    Lib\Utils\Common::escAdminUrl( Appointments\Page::pageSlug() ),
                    $task['service_id'] );
                wp_send_json_error( compact( 'action', 'filter_url' ) );
            } else {
                $action = 'confirm';
                wp_send_json_error( compact( 'action' ) );
            }
        }

        wp_send_json_success();
    }

    /**
     * Update service parameters and assign staff
     */
    public static function updateService()
    {
        $form = new Forms\Service();
        $form->bind( self::postParameters() );
        $service = $form->save();

        $staff_ids = self::parameter( 'staff_ids', array() );
        if ( empty ( $staff_ids ) ) {
            Lib\Entities\StaffService::query()->delete()->where( 'service_id', $service->getId() )->execute();
        } else {
            Lib\Entities\StaffService::query()->delete()->where( 'service_id', $service->getId() )->whereNotIn( 'staff_id', $staff_ids )->execute();
            if ( $service->getType() == Lib\Entities\Service::TYPE_SIMPLE ) {
                if ( self::parameter( 'update_staff', false ) ) {
                    Lib\Entities\StaffService::query()
                        ->update()
                        ->set( 'price', self::parameter( 'price' ) )
                        ->set( 'capacity_min', $service->getCapacityMin() )
                        ->set( 'capacity_max', $service->getCapacityMax() )
                        ->where( 'service_id', self::parameter( 'id' ) )
                        ->execute();
                }
                // Create records for newly linked staff.
                $existing_staff_ids = array();
                $res                = Lib\Entities\StaffService::query()
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
                            ->setCapacityMin( $service->getCapacityMin() )
                            ->setCapacityMax( $service->getCapacityMax() )
                            ->save();
                    }
                }
            }
        }

        // Update services in addons.
        $alert = Proxy\Shared::updateService( array( 'success' => array( __( 'Settings saved.', 'bookly' ) ) ), $service, self::postParameters() );

        wp_send_json_success( Proxy\Shared::prepareUpdateServiceResponse( array( 'alert' => $alert), $service, self::postParameters() ) );
    }

    /**
     * Update service categories
     */
    public static function updateServiceCategories()
    {
        $categories          = self::parameter( 'categories', array() );
        $existing_categories = array();
        foreach ( $categories as $category ) {
            if ( strpos( $category['id'], 'new' ) === false ) {
                $existing_categories[] = $category['id'];
            }
        }
        // Delete categories
        Lib\Entities\Category::query( 'c' )->delete()->whereNotIn( 'c.id', $existing_categories )->execute();
        foreach ( $categories as $position => $category_data ) {
            if ( strpos( $category_data['id'], 'new' ) === false ) {
                $category = Lib\Entities\Category::find( $category_data['id'] );
            } else {
                $category = new Lib\Entities\Category();
            }
            $category
                ->setPosition( $position )
                ->setName( $category_data['name'] )
                ->save();
        }

        wp_send_json_success( Lib\Entities\Category::query()->sortBy( 'position' )->fetchArray() );
    }
}