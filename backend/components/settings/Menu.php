<?php
namespace Bookly\Backend\Components\Settings;

/**
 * Class Menu
 * @package Bookly\Backend\Components\Settings
 */
class Menu
{
    /**
     * Render menu item on settings page.
     *
     * @param string $title
     * @param string $tab
     */
    public static function renderItem( $title, $tab )
    {
        printf( '<li class="bookly-nav-item" data-target="#bookly_settings_%s" data-toggle="tab">%s</li>',
            $tab,
            $title
        );
    }
}