<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Hooks
 * @package BooklyLite\Lib\Base
 */
abstract class Hooks
{
    /**
     * Array of child class instances
     * @var Hooks[]
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

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Register hooks with add_action() or add_filter() function
     * based on public 'hook*' methods of child class.
     */
    protected function registerHooks()
    {
        $reflection = new \ReflectionClass( $this );
        $function   = $reflection->getShortName() == 'Filters' ? 'add_filter' : 'add_action';
        /** @var \BooklyLite\Lib\Base\Plugin $plugin_class */
        $plugin_class = $reflection->getNamespaceName() . '\\Plugin';

        foreach ( $reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( preg_match( '/^(_?)(hook|bookly)(.*)/', $method->name, $match ) ) {
                if ( $match[1] == '' || is_admin() ) {
                    call_user_func(
                        $function,
                        ( $match[2] == 'hook' ? $plugin_class::getPrefix() : 'bookly_' ) .
                        strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[3] ) ),
                        array( $this, $match[0] ),
                        10,
                        $method->getNumberOfParameters()
                    );
                }
            }
        }
    }
}