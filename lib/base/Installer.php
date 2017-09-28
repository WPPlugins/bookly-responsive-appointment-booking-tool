<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Installer
 * @package BooklyLite\Lib\Base
 */
abstract class Installer extends Schema
{
    protected $options = array();

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Install.
     */
    public function install()
    {
        global $wpdb;

        $plugin_class = Plugin::getPluginFor( $this );
        $data_loaded_option_name = $plugin_class::getPrefix() . 'data_loaded';

        // Create tables and load data if it hasn't been loaded yet.
        if ( ! get_option( $data_loaded_option_name ) ) {
            $this->createTables();
            $this->loadData();
        }

        if ( false === \BooklyLite\Lib\Entities\Staff::find( 1 ) ) {
            $wpdb->insert( \BooklyLite\Lib\Entities\Staff::getTableName(), array( 'full_name' => 'Employee', 'id' => 1, 'visibility' => 'public' ) );
            \BooklyLite\Lib\Entities\StaffScheduleItem::query( 'ss' )
                ->delete()->where( 'ss.staff_id', 1 )
                ->execute();
            $fields = array(
                'staff_id'   => 1,
                'day_index'  => 1,
                'start_time' => '08:00:00',
                'end_time'   => '18:00:00',
            );
            for ( $i = 1; $i <= 7; $i ++ ) {
                $fields['day_index'] = $i;
                $schedule            = new \BooklyLite\Lib\Entities\StaffScheduleItem();
                $schedule->setFields( $fields );
                $schedule->save();
            }
        }

        update_option( $data_loaded_option_name, '1' );
    }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        if ( get_option( 'bookly_lite_uninstall_remove_bookly_data' ) ) {
            $this->removeData();
            $this->dropPluginTables();
        }
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Create tables.
     */
    public function createTables()
    {

    }

    /**
     * Drop tables (@see \BooklyLite\Backend\Modules\Debug\Controller ).
     */
    public function dropTables()
    {
        $this->dropPluginTables();
    }

    /**
     * Load data.
     */
    public function loadData()
    {
        // Add default options.
        $plugin_class  = Plugin::getPluginFor( $this );
        $plugin_prefix = $plugin_class::getPrefix();
        add_option( $plugin_prefix . 'data_loaded', '0' );
        add_option( $plugin_prefix . 'db_version',  $plugin_class::getVersion() );
        add_option( $plugin_prefix . 'installation_time', time() );
        add_option( $plugin_prefix . 'grace_start', time() + 2 * WEEK_IN_SECONDS );
        add_option( $plugin_class::getPurchaseCodeOption(), '' );
        if ( Plugin::getPrefix() != 'bookly_' ) {
            add_option( $plugin_prefix . 'enabled', '1' );
        }

        // Add plugin options.
        foreach ( $this->options as $name => $value ) {
            add_option( $name, $value );
            if ( strpos( $name, 'bookly_l10n_' ) === 0 ) {
                do_action( 'wpml_register_single_string', 'bookly', $name, $value );
            }
        }
    }

    /**
     * Remove data.
     */
    public function removeData()
    {
        // Remove options.
        foreach ( $this->options as $name => $value ) {
            delete_option( $name );
        }
        $plugin_class  = Plugin::getPluginFor( $this );
        $plugin_prefix = $plugin_class::getPrefix();
        delete_option( $plugin_prefix . 'data_loaded' );
        delete_option( $plugin_prefix . 'db_version' );
        delete_option( $plugin_prefix . 'installation_time' );
        delete_option( $plugin_prefix . 'grace_start' );
        delete_option( $plugin_prefix . 'enabled' );
        delete_option( $plugin_class::getPurchaseCodeOption() );
    }

}
