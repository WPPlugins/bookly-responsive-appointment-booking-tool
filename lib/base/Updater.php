<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Updater
 * @package BooklyLite\Lib\Base
 */
abstract class Updater extends Schema
{
    public function run()
    {
        $updater = $this;
        add_action( 'plugins_loaded', function () use ( $updater ) {
            $plugin_class        = Plugin::getPluginFor( $updater );
            $version_option_name = $plugin_class::getPrefix() . 'db_version';
            $db_version = get_option( $version_option_name );
            if ( $plugin_class == 'BooklyLite\Lib\Plugin' ) {
                /** @deprecate option ab_db_version Bookly < 11.7 */
                // get MAX db_version
                $db_version = strnatcmp( get_option( 'ab_db_version' ), $db_version ) <= 0 ? $db_version : get_option( 'ab_db_version' );
            }
            if ( $db_version !== false && version_compare( $plugin_class::getVersion(), $db_version, '>' ) ) {
                set_time_limit( 0 );

                $db_version_underscored     = 'update_' . str_replace( '.', '_', $db_version );
                $plugin_version_underscored = 'update_' . str_replace( '.', '_', $plugin_class::getVersion() );

                $updates = array_filter(
                    get_class_methods( $updater ),
                    function ( $method ) { return strstr( $method, 'update_' ); }
                );
                usort( $updates, 'strnatcmp' );

                foreach ( $updates as $method ) {
                    if ( strnatcmp( $method, $db_version_underscored ) > 0 && strnatcmp( $method, $plugin_version_underscored ) <= 0 ) {
                        call_user_func( array( $updater, $method ) );
                    }
                }

                update_option( $version_option_name, $plugin_class::getVersion() );
            }
        } );
    }

    protected function rename_options( array $options )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ( $options as $deprecated_name => $option_name ) {
            $wpdb->query( $wpdb->prepare( 'UPDATE `' . $wpdb->options . '` SET option_name = %s WHERE option_name = %s', $option_name, $deprecated_name ) );
        }
    }

    protected function rename_l10n_strings( array $strings, $with_options = true )
    {
        global $wpdb;
        // WPML 'move' customer translations
        $wpml_strings_table = $wpdb->prefix . 'icl_strings';
        $result = $wpdb->query( "SELECT table_name FROM information_schema.tables WHERE table_name = '$wpml_strings_table' AND TABLE_SCHEMA=SCHEMA()" );
        if ( $result == 1 ) {
            $query = "SELECT count(*) FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'domain_name_context_md5' AND TABLE_NAME = '$wpml_strings_table' AND TABLE_SCHEMA=SCHEMA()";
            $domain_name_context_md5_exists = $wpdb->get_var( $query );
            if ( $domain_name_context_md5_exists ) {
                foreach ( $strings as $deprecated_name => $name ) {
                    $wpdb->query( "UPDATE $wpml_strings_table SET name='$name', domain_name_context_md5=MD5(CONCAT(`context`,'" . $name . "',`gettext_context`)) WHERE name='$deprecated_name'" );
                }
            } else {
                foreach ( $strings as $deprecated_name => $name ) {
                    $wpdb->query( "UPDATE $wpml_strings_table SET name='$name' WHERE name='$deprecated_name'" );
                }
            }
        }
        if ( $with_options ) {
            $this->rename_options( $strings );
        }
    }

    protected function register_l10n_options( array $options )
    {
        foreach ( $options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
            do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
        }
    }

}