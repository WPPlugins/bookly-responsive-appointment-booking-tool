<?php
namespace Bookly\Backend\Components\Sms\Custom;

use Bookly\Lib as BooklyLib;

/**
 * Class Notification
 * @package Bookly\Backend\Components\Sms\Custom
 */
class Notification extends BooklyLib\Base\Component
{
    /**
     * Render custom notification
     *
     * @param \Bookly\Backend\Modules\Notifications\Forms\Notifications $form
     * @param array $notification
     * @param bool  $echo
     * @return string|void
     */
    public static function render( $form, $notification, $echo = true )
    {
        $unique = self::getFromCache( 'unique', 1 );

        return self::renderTemplate( 'layout', compact( 'form', 'notification', 'unique' ), $echo );
    }

    /**
     * Render settings for notification.
     *
     * @param string  $set
     * @param integer $id
     * @param array   $settings
     */
    public static function renderSet( $set, $id, $settings )
    {
        $statuses = BooklyLib\Entities\CustomerAppointment::getStatuses();
        if ( ! self::hasInCache( 'service_dropdown_data' ) ) {
            $service_dropdown_data = BooklyLib\Utils\Common::getServiceDataForDropDown( 's.type <> "package"' );
            self::putInCache( 'service_dropdown_data', $service_dropdown_data );
        }

        $service_dropdown_data = self::getFromCache( 'service_dropdown_data' );
        $unique = self::getFromCache( 'unique', 2 );
        self::renderTemplate( $set, compact( 'id', 'settings', 'statuses', 'service_dropdown_data', 'unique' ) );
    }
}