<?php
namespace BooklyLite\Lib\Base;

use BooklyLite\Lib;

/**
 * Class Plugin
 * @package BooklyLite\Lib\Base
 */
abstract class Plugin
{
    /**
     * Prefix for options and metas.
     *
     * @staticvar string
     */
    protected static $prefix;

    /**
     * Plugin title (used in method "title").
     *
     * @staticvar string
     */
    protected static $title;

    /**
     * Plugin version (used in method "version").
     *
     * @staticvar string
     */
    protected static $version;

    /**
     * Plugin slug (used in method "slug").
     *
     * @staticvar string
     */
    protected static $slug;

    /**
     * Path to plugin directory (used in method "directory").
     *
     * @staticvar string
     */
    protected static $directory;

    /**
     * Path to plugin main file (used in method "mainFile").
     *
     * @staticvar string
     */
    protected static $main_file;

    /**
     * Plugin basename (used in method "basename").
     *
     * @staticvar string
     */
    protected static $basename;

    /**
     * Plugin text domain (used in method "textDomain").
     *
     * @staticvar string
     */
    protected static $text_domain;

    /**
     * Root namespace of plugin classes.
     *
     * @staticvar string
     */
    protected static $root_namespace;

    /**
     * Start Bookly plugin.
     */
    public static function run()
    {
        static::registerHooks();
        static::initUpdateChecker();
        // Run updates.
        $updater_class = static::getRootNamespace() . '\Lib\Updater';
        $updater = new $updater_class();
        $updater->run();
    }

    /**
     * Activate plugin.
     *
     * @param bool $network_wide
     */
    public static function activate( $network_wide )
    {
        if ( $network_wide && has_action( 'bookly_plugin_activate' ) ) {
            do_action( 'bookly_plugin_activate', static::getSlug() );
        } else {
            $installer_class = static::getRootNamespace() . '\Lib\Installer';
            $installer = new $installer_class();
            $installer->install();
        }
    }

    /**
     * Deactivate plugin.
     *
     * @param bool $network_wide
     */
    public static function deactivate( $network_wide )
    {
        if ( $network_wide && has_action( 'bookly_plugin_deactivate' ) ) {
            do_action( 'bookly_plugin_deactivate', static::getSlug() );
        } else {
            unload_textdomain( 'bookly' );
        }
    }

    /**
     * Uninstall plugin.
     *
     * @param string|bool $network_wide
     */
    public static function uninstall( $network_wide )
    {
        if ( $network_wide !== false && has_action( 'bookly_plugin_uninstall' ) ) {
            do_action( 'bookly_plugin_uninstall', static::getSlug() );
        } else {
            $installer_class = static::getRootNamespace() . '\Lib\Installer';
            $installer = new $installer_class();
            $installer->uninstall();
        }
    }

    /**
     * Get prefix.
     *
     * @return mixed
     */
    public static function getPrefix()
    {
        if ( static::$prefix === null ) {
            static::$prefix = str_replace( array( '-addon', '-' ), array( '', '_' ), static::getSlug() ) . '_';
        }

        return static::$prefix;
    }

    /**
     * Get plugin purchase code option.
     *
     * @return string
     */
    public static function getPurchaseCode()
    {
        return static::getPrefix() . 'envato_purchase_code';
    }

    /**
     * Get plugin title.
     *
     * @return string
     */
    public static function getTitle()
    {
        if ( static::$title === null ) {
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_data = get_plugin_data( static::getMainFile() );
            static::$version     = $plugin_data['Version'];
            static::$title       = $plugin_data['Name'];
            static::$text_domain = $plugin_data['TextDomain'];
        }

        return static::$title;
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public static function getVersion()
    {
        if ( static::$version === null ) {
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_data = get_plugin_data( static::getMainFile() );
            static::$version     = $plugin_data['Version'];
            static::$title       = $plugin_data['Name'];
            static::$text_domain = $plugin_data['TextDomain'];
        }

        return static::$version;
    }

    /**
     * Get plugin slug.
     *
     * @return string
     */
    public static function getSlug()
    {
        if ( static::$slug === null ) {
            static::$slug = basename( static::getDirectory() );
        }

        return static::$slug;
    }

    /**
     * Get path to plugin directory.
     *
     * @return string
     */
    public static function getDirectory()
    {
        if ( static::$directory === null ) {
            $reflector = new \ReflectionClass( get_called_class() );
            static::$directory = dirname( dirname( $reflector->getFileName() ) );
        }

        return static::$directory;
    }

    /**
     * Get path to plugin main file.
     *
     * @return string
     */
    public static function getMainFile()
    {
        if ( static::$main_file === null ) {
            static::$main_file = static::getDirectory() . '/main.php';
        }

        return static::$main_file;
    }

    /**
     * Get plugin basename.
     *
     * @return string
     */
    public static function getBasename()
    {
        if ( static::$basename === null ) {
            static::$basename = plugin_basename( static::getMainFile() );
        }

        return static::$basename;
    }

    /**
     * Get plugin text domain.
     *
     * @return string
     */
    public static function getTextDomain()
    {
        if ( static::$text_domain === null ) {
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }
            $plugin_data = get_plugin_data( static::getMainFile() );
            static::$version     = $plugin_data['Version'];
            static::$title       = $plugin_data['Name'];
            static::$text_domain = $plugin_data['TextDomain'];
        }

        return static::$text_domain;
    }

    /**
     * Get root namespace of called class.
     *
     * @return string
     */
    public static function getRootNamespace()
    {
        if ( static::$root_namespace === null ) {
            $called_class = get_called_class();
            static::$root_namespace = substr( $called_class, 0, strpos( $called_class, '\\' ) );
        }

        return static::$root_namespace;
    }

    /**
     * Check whether the plugin is network active.
     *
     * @return bool
     */
    public static function isNetworkActive()
    {
        return is_plugin_active_for_network( static::getBasename() );
    }

    /**
     * Register hooks.
     */
    public static function registerHooks()
    {
        /** @var Plugin $plugin_class */
        $plugin_class = get_called_class();

        register_activation_hook( static::getMainFile(),   array( $plugin_class, 'activate' ) );
        register_deactivation_hook( static::getMainFile(), array( $plugin_class, 'deactivate' ) );
        register_uninstall_hook( static::getMainFile(),    array( $plugin_class, 'uninstall' ) );

        add_action( 'plugins_loaded', function () use ( $plugin_class ) {
            // l10n.
            load_plugin_textdomain( $plugin_class::getTextDomain(), false, $plugin_class::getSlug() . '/languages' );
        } );

        if ( is_admin() ) {
            // Add handlers to Bookly filters.
            add_filter( 'bookly_plugins', function ( array $plugins ) use ( $plugin_class ) {
                $plugins[ $plugin_class::getSlug() ] = $plugin_class;

                return $plugins;
            } );
        }
    }

    /**
     * Init update checker.
     */
    public static function initUpdateChecker() { }

}