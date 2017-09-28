<?php
namespace BooklyLite\Backend\Modules\Services\Forms;

use BooklyLite\Lib;

/**
 * Class Service
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
            'capacity',
            'padding_left',
            'padding_right',
            'info',
            'type',
            'sub_services',
            'visibility',
        );
        if ( Lib\Config::isServiceScheduleEnabled() ) {
            $fields[] = 'start_time';
            $fields[] = 'end_time';
        }

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
        // Fields with NULL
        foreach ( array( 'category_id', 'start_time', 'end_time' ) as $field_name ) {
            if ( array_key_exists( $field_name, $_post ) && ! $_post[ $field_name ] ) {
                $_post[ $field_name ] = null;
            }
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

        if ( $this->data['type'] == Lib\Entities\Service::TYPE_SIMPLE || ! array_key_exists( 'sub_services', $this->data ) || empty( $this->data['sub_services'] ) ) {
            $this->data['sub_services'] = '[]';
        } elseif ( is_array( $this->data['sub_services'] ) ) {
            $this->data['sub_services'] = json_encode( (array) $this->data['sub_services'] );
        }

        return parent::save();
    }

}