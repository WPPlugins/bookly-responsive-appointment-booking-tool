<?php
namespace Bookly\Lib\Base;

use Bookly\Lib;

/**
 * Class Block
 * @package Bookly\Lib\Base
 */
abstract class Block extends Component
{
    /**
     * Register WP Ajax actions.
     */
    public static function init()
    {
        if ( is_admin() && function_exists( 'register_block_type' ) ) {
            if ( substr( $_SERVER['PHP_SELF'], '-8' ) == 'post.php' ||
                substr( $_SERVER['PHP_SELF'], '-12' ) == 'post-new.php'
            ) {
                /** @var static $class */
                $class = get_called_class();
                add_action( 'init', function () use ( $class ) {
                    $class::registerBlockType();
                } );
            }
        }
    }

    /**
     * Register block for gutenberg
     */
    public static function registerBlockType()
    {

    }

}