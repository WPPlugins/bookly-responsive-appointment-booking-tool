<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class Limitation
 * @package Bookly\Backend\Components\Notices
 */
class Limitation extends Lib\Base\Component
{
    /**
     * Render limitation notice.
     */
    public static function getHtml()
    {
        return self::renderTemplate( 'limitation', array(), false );
    }
}