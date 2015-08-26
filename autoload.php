<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @param $className
 */
function bookly_autoload( $className ) {

    $paths = array(
        '/lib/'
    );

    foreach ( $paths as $path ) {
        if ( file_exists( AB_PATH . $path . $className . '.php' ) ) {
            require_once( AB_PATH . $path . $className . '.php' );
        }
    }
}

spl_autoload_register( 'bookly_autoload' );