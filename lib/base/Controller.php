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
     * @param bool   $check_access
     */
    public function forward( $action, $check_access = true )
    {
        if ( !$check_access || $this->hasAccess( $action ) ) {
            date_default_timezone_set( 'UTC' );
            call_user_func( array( $this, $action ) );
        } else {
            do_action( 'admin_page_access_denied' );
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

        $this->registerWpActions();
    }

    /**
     * Register WP actions with add_action() function
     * based on public 'execute*' methods of child controller class.
     *
     * @param string $prefix Prefix for auto generated add_action() $tag parameter
     */
    protected function registerWpActions( $prefix = '' )
    {
        $_this = $this;

        foreach ( $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( preg_match( '/^execute(.*)/', $method->name, $match ) ) {
                add_action(
                    $prefix . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[1] ) ),
                    function () use ( $_this, $match ) {
                        $_this->forward( $match[0], true );
                    }
                );
            }
        }
    }

    /**
     * Check if the current user has access to the action.
     *
     * Default access (if is not set with annotation for the controller or action) is "admin"
     * Access type:
     *  "admin"     - check if the current user is super admin
     *  "user"      - check if the current user is authenticated
     *  "anonymous" - anonymous user
     *
     * @param string $action
     * @return bool
     */
    protected function hasAccess( $action )
    {
        $permissions = $this->getPermissions();
        $security    = isset( $permissions[ $action ] ) ? $permissions[ $action ] : null;

        if ( is_null( $security ) ) {
            // Check if controller class has permission
            $security = isset( $permissions['_this'] ) ? $permissions['_this'] : 'admin';
        }
        switch ( $security ) {
            case 'admin'     : return Lib\Utils\Common::isCurrentUserAdmin();
            case 'user'      : return is_user_logged_in();
            case 'anonymous' : return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getPermissions()
    {
        return array();
    }

    /******************************************************************************************************************
     * Private methods                                                                                              *
     ******************************************************************************************************************/
}