<?php
namespace Bookly\Lib;

use Bookly\Backend;
use Bookly\Frontend;

/**
 * Class Plugin
 * @package Bookly\Lib
 */
abstract class Plugin extends Base\Plugin
{
    protected static $prefix = 'bookly_';
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;
    protected static $embedded;

    /**
     * @inheritdoc
     */
    public static function init()
    {
        // Init ajax.
        Backend\Components\Dashboard\Appointments\Ajax::init();
        Backend\Components\Dashboard\Appointments\Widget::init();
        Backend\Components\Dialogs\Appointment\Delete\Ajax::init();
        Backend\Components\Dialogs\Appointment\Edit\Ajax::init();
        Backend\Components\Dialogs\Customer\Delete\Ajax::init();
        Backend\Components\Dialogs\Customer\Edit\Ajax::init();
        Backend\Components\Dialogs\Payment\Ajax::init();
        Backend\Components\Dialogs\Sms\Ajax::init();
        Backend\Components\Dialogs\Staff\Edit\Ajax::init();
        Backend\Components\Gutenberg\BooklyForm\Block::init();
        Backend\Components\Notices\CollectStatsAjax::init();
        Backend\Components\Notices\LiteRebrandingAjax::init();
        Backend\Components\Notices\NpsAjax::init();
        Backend\Components\Notices\SubscribeAjax::init();
        Backend\Components\Support\ButtonsAjax::init();
        Backend\Components\TinyMce\Tools::init();
        Backend\Modules\Appearance\Ajax::init();
        Backend\Modules\Appointments\Ajax::init();
        Backend\Modules\Calendar\Ajax::init();
        Backend\Modules\Customers\Ajax::init();
        Backend\Modules\Debug\Ajax::init();
        Backend\Modules\Messages\Ajax::init();
        Backend\Modules\Notifications\Ajax::init();
        Backend\Modules\Payments\Ajax::init();
        Backend\Modules\Services\Ajax::init();
        Backend\Modules\Settings\Ajax::init();
        Backend\Modules\Shop\Ajax::init();
        Backend\Modules\Sms\Ajax::init();
        Backend\Modules\Staff\Ajax::init();
        Frontend\Modules\Booking\Ajax::init();

        if ( ! is_admin() ) {
            // Init short code.
            Frontend\Modules\Booking\ShortCode::init();
        }
    }

    /**
     * @inheritdoc
     */
    public static function run()
    {
        // l10n.
        load_plugin_textdomain( 'bookly', false, self::getSlug() . '/languages' );

        parent::run();
    }

    /**
     * @inheritdoc
     */
    public static function registerHooks()
    {
        parent::registerHooks();

        if ( is_admin() ) {
            Backend\Backend::registerHooks();
        } else {
            Frontend\Frontend::registerHooks();
        }

        if ( get_option( 'bookly_gen_collect_stats' ) ) {
            // Store admin preferred language.
            add_filter( 'wp_authenticate_user', function ( $user ) {
                if ( $user instanceof \WP_User && $user->has_cap( 'manage_options' ) && isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
                    $locale = strtok( $_SERVER['HTTP_ACCEPT_LANGUAGE'], ',;' );
                    update_option( 'bookly_admin_preferred_language', $locale );
                }

                return $user;
            }, 99, 1 );
        }

        // Gutenberg category
        add_filter( 'block_categories', function ( $categories, $post ) {
            return array_merge( array(
                array(
                    'slug'  => 'bookly-blocks',
                    'title' => 'Bookly',
                ), ),
                $categories
            );
        }, 10, 2 );

        // Register and schedule routines.
        Routines::init();
    }
}