<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_Instance {

    const plugin_path = 'appointment-booking/main.php';
    const version     = '7.6.1';

    public static function activate( $register_hook = true )
    {
        $installer = new AB_Installer();
        $installer->install();
        $register_hook ? do_action( 'bookly_activate' ) : null;
    }

    public static function deactivate( $register_hook = true )
    {
        unload_textdomain( 'bookly' );
        $register_hook ? do_action( 'bookly_deactivate' ) : null;
    }

    public static function uninstall( $register_hook = true )
    {
        $installer = new AB_Installer();
        $installer->uninstall();
        $register_hook ? do_action( 'bookly_uninstall' ) : null;
    }

}