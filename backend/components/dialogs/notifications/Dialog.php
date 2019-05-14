<?php
namespace Bookly\Backend\Components\Dialogs\Notifications;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Sms\Dialog as SmsDialog;

/**
 * Class Dialog
 * @package Bookly\Backend\Components\Dialogs\Notifications
 */
class Dialog extends SmsDialog
{
    /**
     * Render payment details dialog.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'css/fontawesome-all.min.css', 'css/select2.min.css' ),
        ) );

        self::enqueueScripts( array(
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'backend'  => array( 'js/select2.full.min.js' => array( 'jquery' ), ),
            'bookly'   => array( 'backend/components/dialogs/sms/resources/js/notification-dialog.js' => array( 'jquery' ), ),
        ) );

        wp_localize_script( 'bookly-notification-dialog.js', 'BooklyNotificationDialogL10n', array(
            'csrfToken'       => BooklyLib\Utils\Common::getCsrfToken(),
            'recurringActive' => (int) BooklyLib\Config::recurringAppointmentsActive(),
            'defaultNotification' => self::getDefaultNotification(),
            'title' => array(
                'container' => __( 'Email', 'bookly' ),
                'new'       => __( 'New email notification', 'bookly' ),
                'edit'      => __( 'Edit email notification', 'bookly' ),
                'create'    => __( 'Create notification', 'bookly' ),
                'save'      => __( 'Save notification', 'bookly' ),
            ),
        ) );

        SmsDialog::renderTemplate( 'dialog', array( 'self' => __CLASS__ ) );
    }
}