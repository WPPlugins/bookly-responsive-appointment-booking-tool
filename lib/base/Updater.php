<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Updater
 * @package BooklyLite\Lib\Base
 */
abstract class Updater
{
    public function run()
    {
        $updater = $this;
        add_action( 'plugins_loaded', function () use ( $updater ) {
            $class = get_class( $updater );
            /** @var Plugin $plugin_class */
            $plugin_class        = substr( $class, 0, strpos( $class, '\\' ) ) . '\\Lib\\Plugin';
            $version_option_name = $plugin_class::getPrefix() . 'db_version';
            $db_version          = get_option( $version_option_name );
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

    protected function drop( $tables )
    {
        global $wpdb;

        if ( ! is_array( $tables ) ) {
            $tables = array( $tables );
        }
        $get_ab_foreign_keys = "SELECT table_name, constraint_name FROM information_schema.key_column_usage WHERE REFERENCED_TABLE_SCHEMA=SCHEMA() AND REFERENCED_TABLE_NAME IN (" . implode( ', ', array_fill( 0, count( $tables ), '%s' ) ) . ")";
        $schema = $wpdb->get_results( $wpdb->prepare( $get_ab_foreign_keys, $tables ) );
        foreach ( $schema as $foreign_key ) {
            $wpdb->query( "ALTER TABLE `$foreign_key->table_name` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
        $wpdb->query( 'DROP TABLE IF EXISTS `' . implode( "`,\r\n `", $tables ) . '` CASCADE;' );
    }

    protected function drop_columns( $table, array $columns )
    {
        global $wpdb;

        $get_foreign_keys = "SELECT constraint_name FROM information_schema.key_column_usage WHERE REFERENCED_TABLE_SCHEMA=SCHEMA() AND table_name = '$table' AND column_name IN (" . implode( ', ', array_fill( 0, count( $columns ), '%s' ) ) . ")";
        $constraints = $wpdb->get_results( $wpdb->prepare( $get_foreign_keys, $columns ) );
        foreach ( $constraints as $foreign_key ) {
            $wpdb->query( "ALTER TABLE `$table` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
        foreach ( $columns as $column ) {
            $wpdb->query( "ALTER TABLE `$table` DROP COLUMN `$column`" );
        }
    }

    protected function rename_options( array $options )
    {
        foreach ( $options as $deprecated_name => $option_name ) {
            add_option( $option_name, get_option( $deprecated_name ) );
            delete_option( $deprecated_name );
        }
    }

    protected function rename_l10n_strings( array $strings )
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
    }

    protected function field_exist( $table, $field )
    {
        global $wpdb;
        $query = "SELECT count(*) FROM information_schema.COLUMNS WHERE COLUMN_NAME = '$field' AND TABLE_NAME = '$table' AND TABLE_SCHEMA=SCHEMA()";

        return (boolean) $wpdb->get_var( $query );
    }

    protected function register_l10n_options( array $options )
    {
        foreach ( $options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
            do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
        }
    }

}