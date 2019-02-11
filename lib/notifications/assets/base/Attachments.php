<?php
namespace Bookly\Lib\Notifications\Assets\Base;

use Bookly\Lib\Entities\Notification;

/**
 * Class Attachments
 * @package Bookly\Lib\Notifications\Assets\Base
 */
abstract class Attachments
{
    /** @var array */
    protected $files = array();

    /**
     * Create attachment files.
     *
     * @param Notification $notification
     * @return array
     */
    abstract public function createFor( Notification $notification );

    /**
     * Remove attachment files.
     */
    public function clear()
    {
        foreach ( $this->files as $file ) {
            unlink( $file );
        }

        $this->files = array();
    }
}