<?php
namespace Bookly\Backend\Components\Controls;

use Bookly\Lib\Utils\Common;

/**
 * Class Inputs
 * @package Bookly\Backend\Components\Controls
 */
class Inputs
{
    /**
     * Add hidden input with CSRF token.
     */
    public static function renderCsrf()
    {
        printf(
            '<input type="hidden" name="csrf_token" value="%s" />',
            esc_attr( Common::getCsrfToken() )
        );
    }
}