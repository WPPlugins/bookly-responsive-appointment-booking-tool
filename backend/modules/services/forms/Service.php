<?php
namespace BooklyLite\Backend\Modules\Services\Forms;

use BooklyLite\Lib;

/**
 * Class Service
 * @method Lib\Entities\Service getObject
 *
 * @package BooklyLite\Backend\Modules\Services\Forms
 */
class Service extends Lib\Base\Form
{
    protected static $entity_class = 'Service';

    public function configure()
    {
        $fields = array(
            'id',
            'title',
            'duration',
            'price',
            'category_id',
            'color',
            'capacity_min',
            'capacity_max',
            'padding_left',
            'padding_right',
            'package_life_time',
            'package_size',
            'appointments_limit',
            'limit_period',
            'info',
            'start_time_info',
            'end_time_info',
            'type',
            'sub_services',
            'staff_preference',
            'recurrence_enabled',
            'recurrence_frequencies',
            'visibility',
            'positions',
        );

        $this->setFields( $fields );
    }

    /**
     * Bind values to form.
     *
     * @param array $_post
     * @param array $files
     */
    public function bind( array $_post, array $files = array() )
    {
        // Field with NULL
        if ( array_key_exists( 'category_id', $_post ) && ! $_post['category_id'] ) {
            $_post['category_id'] = null;
        }

        parent::bind( $_post, $files );
    }

    /**
     * @return \BooklyLite\Lib\Entities\Service
     */
    public function save()
    {
        if ( $this->isNew() ) {
            // When adding new service - set its color randomly.
            $this->data['color'] = sprintf( '#%06X', mt_rand( 0, 0x64FFFF ) );
        }

        if ( $this->data['type'] == Lib\Entities\Service::TYPE_SIMPLE ) {
            Lib\Entities\SubService::query()->delete()->where( 'service_id', $this->data['id'] )->execute();
        }

        if ( $this->data['limit_period'] == 'off' || ! $this->data['appointments_limit'] ) {
            $this->data['appointments_limit'] = null;
        }

        $this->data = Lib\Proxy\Shared::prepareUpdateService( $this->data );

        /** @var Lib\Entities\Service $service */
        $service = parent::save();

        // Saving staff preferences for service

        /** @var Lib\Entities\StaffPreferenceOrder[] $staff_preferences */
        $staff_preferences = Lib\Entities\StaffPreferenceOrder::query()
            ->where( 'service_id', $service->getId() )
            ->indexBy( 'staff_id' )
            ->find();
        foreach ( (array) $this->data['positions'] as $position => $staff_id ) {
            if ( array_key_exists( $staff_id, $staff_preferences ) ) {
                $staff_preferences[ $staff_id ]->setPosition( $position )->save();
            } else {
                $preference = new Lib\Entities\StaffPreferenceOrder();
                $preference
                    ->setServiceId( $service->getId() )
                    ->setStaffId( $staff_id )
                    ->setPosition( $position )
                    ->save();
            }
        }

        return $service;
    }

}