<?php
namespace BooklyLite\Lib\Base;

/**
 * Class ProxyInvoker
 * Base class to invoke methods provided by add-ons.
 *
 * @package BooklyLite\Lib\Base
 */
abstract class ProxyInvoker
{
    /**
     * @var array
     */
    public static $prefixes = array();

    /**
     * Run apply_filters for called method.
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public static function __callStatic( $method, $args )
    {
        $called_class = get_called_class();

        if ( ! isset ( self::$prefixes[ $called_class ] ) ) {
            $reflection = new \ReflectionClass( $called_class );
            $class_name = $reflection->getShortName();

            if ( $class_name == 'Shared' ) {
                self::$prefixes[ $called_class ] = 'bookly_';
            } else {
                self::$prefixes[ $called_class ] = 'bookly_' . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $class_name ) ) . '_';
            }
        }

        $filter_name = self::$prefixes[ $called_class ] . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $method ) );

        if ( has_filter( $filter_name ) ) {
            return apply_filters_ref_array( $filter_name, empty ( $args ) ? array( null ) : $args );
        }

        // Return null for void methods or methods with "get" and "find" prefixes.
        return empty ( $args ) || preg_match( '/^(?:get|find)/', $method )
            ? null
            : $args[0];
    }
}
