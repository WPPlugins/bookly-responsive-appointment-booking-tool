<?php
namespace Bookly\Backend\Modules\Notifications;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Notifications
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        self::enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/angular.min.js',
                'js/help.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/dropdown.js' => array( 'jquery' ),
            ),
            'module'   => array(
                'js/notification.js' => array( 'jquery' ),
                'js/ng-app.js' => array( 'jquery', 'bookly-angular.min.js', 'bookly-dropdown.js' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );
        $cron_reminder = (array) get_option( 'bookly_cron_reminder_times' );
        $form  = new Forms\Notifications( 'email' );
        $alert = array( 'success' => array() );
        $current_notification_id = null;
        // Services in custom notifications where the recipient is client only.
        $only_client = Lib\Entities\Service::query()->whereIn( 'type', array( Lib\Entities\Service::TYPE_COMPOUND, Lib\Entities\Service::TYPE_COLLABORATIVE ) )->fetchCol( 'id' );
        // Save action.
        if ( ! empty ( $_POST ) && self::csrfTokenValid() ) {
            $form->bind( self::postParameters() );
            $form->save();
            $alert['success'][] = __( 'Settings saved.', 'bookly' );
            update_option( 'bookly_email_send_as', self::parameter( 'bookly_email_send_as' ) );
            update_option( 'bookly_email_reply_to_customers', self::parameter( 'bookly_email_reply_to_customers' ) );
            update_option( 'bookly_email_sender', self::parameter( 'bookly_email_sender' ) );
            update_option( 'bookly_email_sender_name', self::parameter( 'bookly_email_sender_name' ) );
            update_option( 'bookly_ntf_processing_interval', (int) self::parameter( 'bookly_ntf_processing_interval' ) );
            foreach ( array( 'staff_agenda', 'client_follow_up', 'client_reminder', 'client_birthday_greeting' ) as $type ) {
                $cron_reminder[ $type ] = self::parameter( $type . '_cron_hour' );
            }
            foreach ( array( 'client_reminder_1st', 'client_reminder_2nd', 'client_reminder_3rd', ) as $type ) {
                $cron_reminder[ $type ] = self::parameter( $type . '_cron_before_hour' );
            }
            update_option( 'bookly_cron_reminder_times', $cron_reminder );
            $current_notification_id = self::parameter( 'new_notification_id' );
        }
        wp_localize_script( 'bookly-alert.js', 'BooklyL10n',  array(
            'csrf_token'   => Lib\Utils\Common::getCsrfToken(),
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'alert'        => $alert,
            'onlyClient'   => $only_client,
            'current_notification_id' => $current_notification_id,
            'sent_successfully'       => __( 'Sent successfully.', 'bookly' ),
        ) );
        foreach ( range( 1, 23 ) as $hours ) {
            $bookly_ntf_processing_interval_values[] = array( $hours, Lib\Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
        }
        self::renderTemplate( 'index', compact( 'form', 'cron_reminder', 'bookly_ntf_processing_interval_values' ) );
    }
}