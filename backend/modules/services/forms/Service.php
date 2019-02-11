<?php
namespace Bookly\Backend\Modules\Services\Forms;

use Bookly\Lib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Service
 * @method Lib\Entities\Service getObject
 *
 * @package Bookly\Backend\Modules\Services\Forms
 */
class Service extends Lib\Base\Form
{
    protected static $entity_class = 'Service';

    public function configure()
    {
        $fields = array(
            'id',
            'category_id',
            'title',
            'duration',
            'slot_length',
            'price',
            'color',
            'deposit',
            'capacity_min',
            'capacity_max',
            'one_booking_per_slot',
            'padding_left',
            'padding_right',
            'package_life_time',
            'package_size',
            'package_unassigned',
            'appointments_limit',
            'limit_period',
            'info',
            'start_time_info',
            'end_time_info',
            'type',
            'sub_services',
            'staff_preference',
            'staff_preferred_period_before',
            'staff_preferred_period_after',
            'recurrence_enabled',
            'recurrence_frequencies',
            'visibility',
            'positions',
            'taxes',
            'unit_duration',
            'units_min',
            'units_max',
            'time_requirements',
            'collaborative_equal_duration',
        );

        $this->setFields( $fields );
    }

    /**
     * Bind values to form.
     *
     * @param array $params
     * @param array $files
     */
    public function bind( array $params, array $files = array() )
    {
        // Field with NULL
        if ( array_key_exists( 'category_id', $params ) && ! $params['category_id'] ) {
            $params['category_id'] = null;
        }

        parent::bind( $params, $files );
    }

    /**
     * @return \Bookly\Lib\Entities\Service
     */
    public function save()
    {
        if ( $this->isNew() ) {
            // When adding new service - set its color randomly.
            $this->data['color'] = sprintf( '#%06X', mt_rand( 0, 0x64FFFF ) );
        } else {
            if ( $this->data['type'] == Lib\Entities\Service::TYPE_SIMPLE ) {
                Lib\Entities\SubService::query()->delete()->where( 'service_id', $this->data['id'] )->execute();
            }

            if ( $this->data['limit_period'] == 'off' || ! $this->data['appointments_limit'] ) {
                $this->data['appointments_limit'] = null;
            }

            if ( $this->data['deposit'] ) {
                $this->data['deposit'] = preg_replace( '/[^0-9%.]/', '', str_replace( ',', '.', $this->data['deposit'] ) );
            }

            $this->data = Proxy\Shared::prepareUpdateService( $this->data );
        }

        return parent::save();
    }

}