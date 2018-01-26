<?php
namespace BooklyLite\Lib\Base;

/**
 * Class ProxyProvider
 * @package BooklyLite\Lib\Base
 */
abstract class ProxyProvider
{
    /**
     * Register methods of child class.
     */
    public static function registerMethods()
    {
        $called_class = get_called_class();
        $reflection   = new \ReflectionClass( $called_class );

        if ( $reflection->getShortName() == 'Shared' ) {
            $prefix = 'bookly_';
        } else {
            $plugin_class = substr( $called_class, 0, strpos( $called_class, '\\' ) ) . '\Lib\Plugin';
            $prefix = $plugin_class::getPrefix();
        }

        foreach ( $reflection->getMethods() as $method ) {
            if ( $method->isPublic() && $method->name != 'registerMethods' || $method->isProtected() && is_admin() ) {
                $method->setAccessible( true );
                add_filter(
                    $prefix . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $method->name ) ),
                    function () use ( $method ) {
                        $args = func_get_args();
                        $res  = $method->invokeArgs( null, $args );

                        return $res === null ?  $args[0] : $res;
                    },
                    10,
                    $method->getNumberOfParameters() ?: 1
                );
            }
        }
    }
}