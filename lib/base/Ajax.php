<?php
namespace Bookly\Lib\Base;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Lib\Base
 */
abstract class Ajax extends Component
{
    /**
     * Register WP Ajax actions.
     */
    public static function init()
    {
        if ( defined( 'DOING_AJAX' ) ) {
            /** @var static $called_class */
            $called_class  = get_called_class();
            $plugin_prefix = call_user_func( array( Lib\Base\Plugin::getPluginFor( $called_class ), 'getPrefix' ) );
            $anonymous     = in_array( 'anonymous', $called_class::permissions() );

            foreach ( static::reflection()->getMethods( \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC ) as $method ) {
                if ( $method->class !== $called_class ) {
                    // Stop if parent class reached.
                    break;
                }
                // Register Ajax action.
                $action   = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $method->name ) );
                $function = function () use ( $called_class, $method ) {
                    $called_class::forward( $method->name, true, true );
                };
                add_action( sprintf( 'wp_ajax_%s%s', $plugin_prefix, $action ), $function );
                if ( $anonymous ) {
                    add_action( sprintf( 'wp_ajax_nopriv_%s%s', $plugin_prefix, $action ), $function );
                }
            }
        }
    }

    /**
     * Execute given action (if the current user has appropriate permissions).
     *
     * @param string $action
     * @param bool   $check_csrf
     * @param bool   $check_access
     */
    public static function forward( $action, $check_csrf = true, $check_access = true )
    {
        if ( ( ! $check_csrf || static::csrfTokenValid( $action ) ) && ( ! $check_access || static::hasAccess( $action ) ) ) {
            date_default_timezone_set( 'UTC' );
            call_user_func( array( get_called_class(), $action ) );
        } else {
            wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
        }
    }

    /**
     * Check if the current user has access to the action.
     *
     * Default access (if is not set in permissions()) is "admin"
     * Access type:
     *  "admin"     - check if the current user is admin
     *  "user"      - check if the current user is authenticated
     *  "anonymous" - anonymous user
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        $permissions = static::permissions();
        $security    = isset ( $permissions[ $action ] ) ? $permissions[ $action ] : null;

        if ( is_null( $security ) ) {
            // Check if default permission is set.
            $security = isset ( $permissions['_default'] ) ? $permissions['_default'] : 'admin';
        }

        switch ( $security ) {
            case 'admin'      : return Lib\Utils\Common::isCurrentUserAdmin();
            case 'supervisor' : return Lib\Utils\Common::isCurrentUserSupervisor();
            case 'user'       : return is_user_logged_in();
            case 'anonymous'  : return true;
        }

        return false;
    }

    /**
     * Get access permissions for child controller methods.
     * Array structure:
     *  [
     *    <method_name> => Access for specific action
     *    _default      => Default access for controller actions
     *  ]
     *
     * @return array
     */
    protected static function permissions()
    {
        return array();
    }
}