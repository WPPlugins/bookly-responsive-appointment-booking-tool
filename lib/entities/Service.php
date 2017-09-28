<?php
namespace BooklyLite\Lib\Entities;

use BooklyLite\Lib;

/**
 * Class Service
 * @package BooklyLite\Lib\Entities
 */
class Service extends Lib\Base\Entity
{
    const TYPE_SIMPLE   = 'simple';
    const TYPE_COMPOUND = 'compound';

    protected static $table = 'ab_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'category_id'   => array( 'format' => '%d', 'reference' => array( 'entity' => 'Category' ) ),
        'title'         => array( 'format' => '%s' ),
        'duration'      => array( 'format' => '%d', 'default' => 900 ),
        'price'         => array( 'format' => '%f', 'default' => '0' ),
        'color'         => array( 'format' => '%s' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        'padding_left'  => array( 'format' => '%d', 'default' => '0' ),
        'padding_right' => array( 'format' => '%d', 'default' => '0' ),
        'info'          => array( 'format' => '%s' ),
        'type'          => array( 'format' => '%s', 'default' => 'simple' ),
        'sub_services'  => array( 'format' => '%s', 'default' => '[]' ),
        'start_time'    => array( 'format' => '%s' ),
        'end_time'      => array( 'format' => '%s' ),
        'visibility'    => array( 'format' => '%s', 'default' => 'public' ),
        'position'      => array( 'format' => '%d', 'default' => 9999 ),
    );

    protected static $cache = array();

    /**
     * Get translated title (if empty returns "Untitled").
     *
     * @return string
     */
    public function getTitle()
    {
        return Lib\Utils\Common::getTranslatedString(
            'service_' . $this->get( 'id' ),
            $this->get( 'title' ) != '' ? $this->get( 'title' ) : __( 'Untitled', 'bookly' )
        );
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        if ( $this->get( 'category_id' ) ) {
            return Category::find( $this->get( 'category_id' ) )->getName();
        }

        return __( 'Uncategorized', 'bookly' );
    }

    /**
     * Get translated info.
     *
     * @return mixed|void
     */
    public function getInfo()
    {
        return Lib\Utils\Common::getTranslatedString( 'service_' . $this->get( 'id' ) . '_info', $this->get( 'info' ) );
    }

    /**
     * Get sub services.
     *
     * @return Service[]
     */
    public function getSubServices()
    {
        $result = array();
        $sub_service_ids = json_decode( $this->get( 'sub_services' ), true );
        $services = self::query()
            ->whereIn( 'id', $sub_service_ids )
            ->where( 'type', self::TYPE_SIMPLE )
            ->indexBy( 'id' )
            ->find();
        // Order services like in sub_services array.
        foreach ( $sub_service_ids as $service_id ) {
            $result[] = $services[ $service_id ];
        }

        return $result;
    }

    /**
     * Save service.
     *
     * @return false|int
     */
    public function save()
    {
        $return = parent::save();
        if ( $this->isLoaded() ) {
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $this->get( 'id' ), $this->get( 'title' ) );
            do_action( 'wpml_register_single_string', 'bookly', 'service_' . $this->get( 'id' ) . '_info', $this->get( 'info' ) );
        }

        return $return;
    }

}
