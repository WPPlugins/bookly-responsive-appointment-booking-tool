<?php
namespace BooklyLite\Lib\Base;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Lib\Base
 */
abstract class Controller extends Components
{
    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Execute given action (if the current user has appropriate permissions).
     *
     * @param string $action
     * @param bool   $check_csrf
     * @param bool   $check_access
     */
    public function forward( $action, $check_csrf = true, $check_access = true )
    {
        if ( ( ! $check_csrf || $this->csrfTokenValid( $action ) ) && ( ! $check_access || $this->hasAccess( $action ) ) ) {
            date_default_timezone_set( 'UTC' );
            call_user_func( array( $this, $action ) );
        } else {
            wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
        }
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->registerWpAjaxActions();
    }

    /**
     * Register WP Ajax actions with add_action() function
     * based on public 'execute*' methods of child controller class.
     *
     * @param bool $with_nopriv  Whether to register 'wp_ajax_nopriv_' actions too
     */
    protected function registerWpAjaxActions( $with_nopriv = false )
    {
        $plugin_class = Lib\Base\Plugin::getPluginFor( $this );

        // Prefixes for auto generated add_action() $tag parameter.
        $prefix = sprintf( 'wp_ajax_%s', $plugin_class::getPrefix() );
        if ( $with_nopriv ) {
            $nopriv_prefix = sprintf( 'wp_ajax_nopriv_%s', $plugin_class::getPrefix() );
        }

        $_this = $this;
        foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( preg_match( '/^execute(.*)/', $method->name, $match ) ) {
                $action   = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[1] ) );
                $function = function () use ( $_this, $match ) {
                    $_this->forward( $match[0], true, true );
                };
                add_action( $prefix . $action, $function );
                if ( $with_nopriv ) {
                    add_action( $nopriv_prefix . $action, $function );
                }
            }
        }
    }

    /**
     * Check if the current user has access to the action.
     *
     * Default access (if is not set in getPermissions for controller or action) is "admin"
     * Access type:
     *  "admin"     - check if the current user is admin
     *  "user"      - check if the current user is authenticated
     *  "anonymous" - anonymous user
     *
     * @param string $action
     * @return bool
     */
    protected function hasAccess( $action )
    {
        $permissions = $this->getPermissions();
        $security    = isset ( $permissions[ $action ] ) ? $permissions[ $action ] : null;

        if ( is_null( $security ) ) {
            // Check if controller class has permission.
            $security = isset ( $permissions['_this'] ) ? $permissions['_this'] : 'admin';
        }

        switch ( $security ) {
            case 'admin'     : return Lib\Utils\Common::isCurrentUserAdmin();
            case 'user'      : return is_user_logged_in();
            case 'anonymous' : return true;
        }

        return false;
    }

    /**
     * Get access permissions for child controller methods.
     * Array structure:
     *  [
     *    <method_name> => Access for specific action
     *    _this         => Default access for controller actions
     *  ]
     *
     * @return array
     */
    protected function getPermissions()
    {
        return array();
    }

    /**
     * Verify CSRF token.
     *
     * @param string $action
     * @return bool
     */
    protected function csrfTokenValid( $action = null )
    {
        return wp_verify_nonce( $this->getParameter( 'csrf_token' ), 'bookly' ) == 1;
    }

    /******************************************************************************************************************
     * Private methods                                                                                              *
     ******************************************************************************************************************/
}