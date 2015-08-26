<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_Service
 */
class AB_Service extends AB_Entity
{
    protected static $table = 'ab_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'title'         => array( 'format' => '%s' ),
        'duration'      => array( 'format' => '%d', 'default' => 900 ),
        'price'         => array( 'format' => '%.2f', 'default' => '0' ),
        'category_id'   => array( 'format' => '%d' ),
        'color'         => array( 'format' => '%s' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        'position'      => array( 'format' => '%d', 'default' => 9999 ),
        'padding_left'  => array( 'format' => '%d', 'default' => '0' ),
        'padding_right' => array( 'format' => '%d', 'default' => '0' ),
    );

    /**
     * @return string
     */
    public function getTitleWithDuration()
    {
        return sprintf( '%s (%s)', $this->getTitle(), AB_DateTimeUtils::secondsToInterval( $this->get( 'duration' ) ) );
    }

    /**
     * Get title (if empty returns "Untitled").
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->get( 'title' ) != '' ? $this->get( 'title' ) : __( 'Untitled', 'bookly' );
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        if ( $this->get( 'category_id' ) ) {
            $category = new AB_Category();
            $category->load( $this->get( 'category_id' ) );

            return $category->get( 'name' );
        }

        return __( 'Uncategorized', 'bookly' );
    }

}
