<?php
namespace Bookly\Backend\Modules\Settings\Proxy;

use Bookly\Lib\Base\Component;
use Bookly\Lib\Config;
use BooklyPro\Lib\Base\Proxy as ProxyPro;

/**
 * Class ProSettings
 * @package Bookly\Backend\Modules\Settings\Proxy
 *
 * @method static void renderProMenuItem()
 * @method static void renderProTab()
 */
abstract class ProSettings extends Component
{
    /**
     * Register proxy methods.
     */
    public static function init()
    {
        ProxyPro::init( get_called_class(), static::reflection() );
    }

    /**
     * Invoke proxy method.
     *
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public static function __callStatic( $method, $args )
    {
        if ( Config::proActive() && ProxyPro::canInvoke( get_called_class(), $method ) ) {
            return ProxyPro::invoke( get_called_class(), $method, $args );
        }
    }

    /**
     * @inheritdoc
     */
    protected static function directory()
    {
        return dirname( parent::directory() );
    }
}