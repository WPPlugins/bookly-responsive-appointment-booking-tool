<?php

/**
 * Web Controller abstract class.
 */
abstract class AB_Controller
{
    /**
     * Reflection object for reverse-engineering of child controller classes.
     * @var ReflectionClass
     */
    protected $reflection = null;

    /**
     * User created variables
     * @var array
     */
    private $vars = array();


    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reflection = new ReflectionClass($this);

        $this->registerWpActions();
    }

    /**
     * Set template variable.
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Checks whether variable exists.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset( $name ) {
        return isset ( $this->vars[ $name ] );
    }

    /**
     * Get template variable.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
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

    /**
     * Execute given action (if the current user has appropriate permissions).
     *
     * @param string $action
     * @param bool   $check_access
     */
    public function forward( $action, $check_access = true ) {
        if ( !$check_access || $this->hasAccess( $action ) ) {
            call_user_func( array( $this, $action ) );
        } else {
            do_action( 'admin_page_access_denied' );
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Get path to directory of the current module.
     *
     * @return string
     */
    protected function getModuleDirectory() {
        return dirname($this->reflection->getFileName());
    }

    /**
     * Get request parameter by name (first removing slashes).
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getParameter($name, $default = null) {
        return $this->hasParameter($name) ? stripslashes_deep($_REQUEST[$name]) : $default;
    }

    /**
     * Get all request parameters (first removing slashes).
     *
     * @return mixed
     */
    protected function getParameters() {
        return stripslashes_deep($_REQUEST);
    }

    /**
     * Get all POST parameters (first removing slashes).
     *
     * @return mixed
     */
    protected function getPostParameters() {
        return stripslashes_deep($_POST);
    }

    /**
     * Register WP actions with add_action() function
     * based on public 'execute*' methods of child controller class.
     *
     * @param string $prefix Prefix for auto generated add_action() $tag parameter
     */
    protected function registerWpActions( $prefix = '' ) {
        $_this = $this;

        foreach ( $this->reflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( preg_match( '/^execute(.*)/', $method->name, $match ) ) {
                add_action(
                    $prefix . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1_$2', $match[ 1 ] ) ),
                    function () use ( $_this, $match ) {
                        $_this->forward( $match[ 0 ], true );
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
    protected function hasAccess( $action ) {
        // Check if action has "@Security" annotation.
        $reader = new AB_AnnotationsReader( $this, $action );
        $security = $reader->getParameter( 'Security' );
        if ( is_null( $security ) ) {
            // Check if controller class has "@Security" annotation.
            $reader = new AB_AnnotationsReader( $this );
            $security = $reader->getParameter( 'Security' );
            if ( is_null( $security ) ) {
                $security = 'admin';
            }
        }

        switch ( $security ) {
            case 'admin'     : return is_super_admin();
            case 'user'      : return is_user_logged_in();
            case 'anonymous' : return true;
        }

        return false;
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    protected function hasParameter($name)
    {
        return array_key_exists($name, $_REQUEST);
    }

    /**
     * Render a template file.
     *
     * @param $template
     * @param array $variables
     * @param bool $echo
     * @return string
     * @throws Exception
     */
    protected function render($template, $variables = array(), $echo = true) {
        extract($this->vars);
        extract($variables);

        // Start output buffering.
        ob_start();
        ob_implicit_flush(0);

        try {
            include $this->getModuleDirectory() . '/templates/' . $template . '.php';
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        if ( $echo ) {
            echo ob_get_clean();
        } else {
            return ob_get_clean();
        }
    }

    /**
     * @return wpdb
     */
    protected function getWpdb() {
        global $wpdb;

        return $wpdb;
    }
}