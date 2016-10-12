<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Installer
 * @package BooklyLite\Lib\Base
 */
abstract class Installer
{
    protected $notifications;
    protected $options;
    protected $tables;

    /**
     * Install.
     */
    public function install()
    {
        $plugin_class = $this->getPluginClass();
        $data_loaded_option_name = $plugin_class::getPrefix() . 'data_loaded';

        // Create tables and load data if it hasn't been loaded yet.
        if ( ! get_option( $data_loaded_option_name ) ) {

            $this->_create_tables();
            $this->_load_data();
        }
        update_option( $data_loaded_option_name, '1' );
    }

    /**
     * Load data.
     */
    protected function _load_data()
    {
        // Add options.
        foreach ( $this->options as $name => $value ) {
            add_option( $name, $value );
            if ( strpos( $name, 'ab_appearance_text_' ) === 0 ) {
                do_action( 'wpml_register_single_string', 'bookly', $name, $value );
            }
        }
    }

    /**
     * Create tables in database.
     */
    protected function _create_tables() { }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        if ( get_option( 'ab_lite_uninstall_remove_bookly_data', true ) ) {
            $this->_remove_data();
            $this->_drop_tables();
        }
    }

    /**
     * Remove data.
     */
    protected function _remove_data()
    {
        // Remove options.
        foreach ( $this->options as $name => $value ) {
            delete_option( $name );
        }
        $plugin_class = $this->getPluginClass();
        $meta_dismiss_admin_notice = $plugin_class::getPrefix() . 'dismiss_admin_notice';
        // Remove user meta.
        foreach ( get_users( array( 'role' => 'administrator' ) ) as $admin ) {
            delete_user_meta( $admin->ID, $meta_dismiss_admin_notice );
        }
    }

    /**
     * Drop Foreign Keys
     * @param $tables
     */
    protected function _drop_fk( $tables )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $query_foreign_keys =
            'SELECT table_name, constraint_name
               FROM information_schema.key_column_usage
              WHERE REFERENCED_TABLE_SCHEMA=SCHEMA()
                AND REFERENCED_TABLE_NAME IN (' . implode( ', ', array_fill( 0, count( $tables ), '%s' ) ) .
            ')';
        $schema = $wpdb->get_results( $wpdb->prepare( $query_foreign_keys, $tables ) );
        foreach ( $schema as $foreign_key )
        {
            $wpdb->query( "ALTER TABLE `$foreign_key->table_name` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
    }

    /**
     * Drop Tables
     */
    protected function _drop_tables()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $this->_drop_fk( $this->tables );
        $wpdb->query( 'DROP TABLE IF EXISTS `' . implode( '`, `', $this->tables ) . '` CASCADE;' );
    }

    /**
     * @return Plugin
     */
    private function getPluginClass()
    {
        $class = get_class( $this );
        return substr( $class, 0, strpos( $class, '\\' ) ) . '\\Lib\\Plugin';
    }

}
