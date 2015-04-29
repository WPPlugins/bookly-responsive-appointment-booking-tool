<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Updates {

}

function ab_plugin_get_version() {
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'main.php' );

    return $plugin_data['Version'];
}

function ab_plugin_update_db() {
    $db_version     = get_option( 'ab_db_version' );
    $plugin_version = ab_plugin_get_version();

    if ( $plugin_version > $db_version ) {
        $update_class   = new AB_Updates();

        $db_version_underscored = str_replace( '.', '_', $db_version );
        $plugin_version_underscored = str_replace( '.', '_', $plugin_version );

        foreach ( get_class_methods( $update_class ) as $method ) {
            if ( $method > 'update_' . $db_version_underscored && $method <= 'update_' . $plugin_version_underscored ) {
                call_user_func( array( $update_class, $method ) );
            }
        }
        update_option( 'ab_db_version', $plugin_version );
    }
}