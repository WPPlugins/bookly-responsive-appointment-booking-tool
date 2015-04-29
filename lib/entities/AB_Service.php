<?php

/**
 * Class AB_Service
 */
class AB_Service extends AB_Entity {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->table_name = 'ab_service';
        $this->schema = array(
            'id'          => array( ),
            'title'       => array( 'format' => '%s' ),
            'duration'    => array( 'format' => '%d', 'default' => 900 ),
            'price'       => array( 'format' => '%.2f', 'default' => '0' ),
            'category_id' => array( 'format' => '%d' ),
            'color'       => array( 'format' => '%s' ),
            'capacity'    => array( 'format' => '%d', 'default' => '1' ),
        );
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitleWithDuration() {
        return sprintf( '%s (%s)', $this->get( 'title' ), self::durationToString( $this->get( 'duration' ) ) );
    }

    /**
     * Convert number of seconds into string "[XX hr] XX min".
     *
     * @param int $duration
     * @return string
     */
    public static function durationToString( $duration ) {
        $hours   = (int)( $duration / 3600 );
        $minutes = (int)( ( $duration % 3600 ) / 60 );
        $result  = '';
        if ( $hours > 0 ) {
          $result = sprintf( __( '%d h', 'ab' ), $hours );
          if ( $minutes > 0 ) {
            $result .= ' ';
          }
        }
        if ( $minutes > 0 ) {
          $result .= sprintf( __( '%d min', 'ab' ), $minutes );
        }

        return $result;
    }
}
