<?php
namespace Bookly\Lib\Base;

use Bookly\Lib;

/**
 * Class Component
 * @package Bookly\Lib\Base
 */
abstract class Component extends Cache
{
    /**
     * Array of reflection objects of child classes.
     * @var \ReflectionClass[]
     */
    private static $reflections = array();

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Get admin page slug.
     *
     * @return string
     */
    public static function pageSlug()
    {
        return 'bookly-' . str_replace( '_', '-', basename( static::directory() ) );
    }

    /**
     * Render a template file.
     *
     * @param string $template
     * @param array  $variables
     * @param bool   $echo
     * @return void|string
     */
    public static function renderTemplate( $template, $variables = array(), $echo = true )
    {
        extract( array( 'self' => get_called_class() ) );
        extract( $variables );

        // Start output buffering.
        ob_start();
        ob_implicit_flush( 0 );

        include static::directory() . '/templates/' . $template . '.php';

        if ( ! $echo ) {
            return ob_get_clean();
        }

        echo ob_get_clean();
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Verify CSRF token.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        return wp_verify_nonce( static::parameter( 'csrf_token' ), 'bookly' ) == 1;
    }

    /**
     * Get path to component directory.
     *
     * @return string
     */
    protected static function directory()
    {
        return dirname( static::reflection()->getFileName() );
    }

    /**
     * Enqueue scripts with wp_enqueue_script.
     *
     * @param array $sources
     */
    protected static function enqueueScripts( array $sources )
    {
        static::_enqueue( 'scripts', $sources );
    }

    /**
     * Enqueue styles with wp_enqueue_style.
     *
     * @param array $sources
     */
    protected static function enqueueStyles( array $sources )
    {
        static::_enqueue( 'styles', $sources );
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    protected static function hasParameter( $name )
    {
        return array_key_exists( $name, $_REQUEST );
    }

    /**
     * Get class reflection object.
     *
     * @return \ReflectionClass
     */
    protected static function reflection()
    {
        $class = get_called_class();
        if ( ! isset ( self::$reflections[ $class ] ) ) {
            self::$reflections[ $class ] = new \ReflectionClass( $class );
        }

        return self::$reflections[ $class ];
    }

    /**
     * Get request parameter by name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function parameter( $name, $default = null )
    {
        return static::hasParameter( $name ) ? stripslashes_deep( $_REQUEST[ $name ] ) : $default;
    }

    /**
     * Get all request parameters.
     *
     * @return mixed
     */
    protected static function parameters()
    {
        return stripslashes_deep( $_REQUEST );
    }

    /**
     * Get all POST parameters.
     *
     * @return mixed
     */
    protected static function postParameters()
    {
        return stripslashes_deep( $_POST );
    }


    /******************************************************************************************************************
     * Private methods                                                                                                *
     ******************************************************************************************************************/

    /**
     * Enqueue scripts or styles with wp_enqueue_script/wp_enqueue_style.
     *
     * @param string $type
     * @param array $sources
     * array(
     *  resource_directory => array(
     *      file[ => deps],
     *      ...
     *  ),
     *  ...
     * )
     */
    private static function _enqueue( $type, array $sources )
    {
        $func = ( $type == 'scripts' ) ? 'wp_enqueue_script' : 'wp_enqueue_style';

        $plugin_class   = Lib\Base\Plugin::getPluginFor( get_called_class() );
        $assets_version = $plugin_class::getVersion();

        foreach ( $sources as $source => $files ) {
            switch ( $source ) {
                case 'wp':
                    $path = false;
                    break;
                case 'backend':
                    $path = $plugin_class::getDirectory() . '/backend/resources/path';
                    break;
                case 'frontend':
                    $path = $plugin_class::getDirectory() . '/frontend/resources/path';
                    break;
                case 'module':
                    $path = static::directory() . '/resources/path';
                    break;
                case 'bookly':
                    $path = Lib\Plugin::getDirectory() . '/path';
                    $assets_version = Lib\Plugin::getVersion();
                    break;
                case 'addon':
                    $path = $plugin_class::getDirectory() . '/path';
                    break;
                default:
                    $path = $source . '/path';
            }

            foreach ( $files as $key => $value ) {
                $file = is_array( $value ) ? $key : $value;
                $deps = is_array( $value ) ? $value : array();

                if ( $path === false ) {
                    call_user_func( $func, $file, false, $deps, $assets_version );
                } else {
                    call_user_func( $func, 'bookly-' . basename( $file ), plugins_url( $file, $path ), $deps, $assets_version );
                }
            }
        }
    }
}