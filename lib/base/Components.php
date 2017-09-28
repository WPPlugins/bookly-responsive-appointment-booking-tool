<?php
namespace BooklyLite\Lib\Base;

use BooklyLite\Lib;

/**
 * Class Components
 * @package BooklyLite\Lib\Base
 */
abstract class Components
{
    /**
     * Reflection object for reverse-engineering of child classes.
     * @var \ReflectionClass
     */
    protected $reflection = null;

    /**
     * User created variables
     * @var array
     */
    private $vars = array();

    /**
     * Array of child class instances
     * @var Components[]
     */
    private static $instances = array();

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Get class instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();
        if ( ! isset ( self::$instances[ $class ] ) ) {
            self::$instances[ $class ] = new $class();
        }

        return self::$instances[ $class ];
    }

    /**
     * Set template variable.
     *
     * @param string $name
     * @param string $value
     */
    public function __set( $name, $value )
    {
        $this->vars[ $name ] = $value;
    }

    /**
     * Checks whether variable exists.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset( $name )
    {
        return isset ( $this->vars[ $name ] );
    }

    /**
     * Get template variable.
     *
     * @param string $name
     * @return mixed
     */
    public function __get( $name )
    {
        if ( array_key_exists( $name, $this->vars ) ) {
            return $this->vars[ $name ];
        }

        $trace = debug_backtrace();
        trigger_error(
            sprintf(
                'Undefined property via __get(): %s in %s on line %s',
                $name,
                $trace[0]['file'],
                $trace[0]['line']
            ),
            E_USER_NOTICE
        );

        return null;
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->reflection = new \ReflectionClass( $this );
    }

    /**
     * Enqueue scripts with wp_enqueue_script.
     *
     * @param array $sources
     */
    protected function enqueueScripts( array $sources )
    {
        $this->_enqueue( 'scripts', $sources );
    }

    /**
     * Enqueue styles with wp_enqueue_style.
     *
     * @param array $sources
     */
    protected function enqueueStyles( array $sources )
    {
        $this->_enqueue( 'styles', $sources );
    }

    /**
     * Get path to directory of the current module.
     *
     * @return string
     */
    protected function getModuleDirectory()
    {
        return dirname( $this->reflection->getFileName() );
    }

    /**
     * Get request parameter by name (first removing slashes).
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getParameter( $name, $default = null )
    {
        return $this->hasParameter( $name ) ? stripslashes_deep( $_REQUEST[ $name ] ) : $default;
    }

    /**
     * Get all request parameters (first removing slashes).
     *
     * @return mixed
     */
    protected function getParameters()
    {
        return stripslashes_deep( $_REQUEST );
    }

    /**
     * Get all POST parameters (first removing slashes).
     *
     * @return mixed
     */
    protected function getPostParameters()
    {
        return stripslashes_deep( $_POST );
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    protected function hasParameter( $name )
    {
        return array_key_exists( $name, $_REQUEST );
    }

    /**
     * Render a template file.
     *
     * @param $template
     * @param array $variables
     * @param bool $echo
     * @return string
     */
    protected function render( $template, $variables = array(), $echo = true )
    {
        extract( $this->vars );
        extract( $variables );

        // Start output buffering.
        ob_start();
        ob_implicit_flush( 0 );

        try {
            include $this->getModuleDirectory() . '/templates/' . $template . '.php';
        } catch ( \Exception $e ) {
            ob_end_clean();
            throw $e;
        }

        if ( $echo ) {
            echo ob_get_clean();
        } else {
            return ob_get_clean();
        }
    }

    /******************************************************************************************************************
     * Private methods                                                                                              *
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
    private function _enqueue( $type, array $sources )
    {
        $func = ( $type == 'scripts' ) ? 'wp_enqueue_script' : 'wp_enqueue_style';

        $namespace = $this->reflection->getNamespaceName();
        /** @var Lib\Base\Plugin $plugin_class */
        $plugin_class  = substr( $namespace, 0, strpos( $namespace, '\\' ) ) . '\Lib\Plugin';

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
                    $path = $this->getModuleDirectory() . '/resources/path';
                    break;
                case 'bookly':
                    $path = Lib\Plugin::getDirectory() . '/path';
                    break;
                default:
                    $path = $source . '/path';
            }

            foreach ( $files as $key => $value ) {
                $file = is_array( $value ) ? $key : $value;
                $deps = is_array( $value ) ? $value : array();

                if ( $path === false ) {
                    call_user_func( $func, $file, false, $deps, $plugin_class::getVersion() );
                } else {
                    call_user_func( $func, 'bookly-' . basename( $file ), plugins_url( $file, $path ), $deps, $plugin_class::getVersion() );
                }
            }
        }
    }

}