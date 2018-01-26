<?php
namespace BooklyLite\Backend\Modules\Notifications;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Notifications
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-notifications';

    public function index()
    {
        $this->enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css' ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/angular.min.js',
                'js/help.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'module'   => array(
                'js/notification.js' => array( 'jquery' ),
                'js/ng-app.js' => array( 'jquery', 'bookly-angular.min.js' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );
        $cron_reminder = (array) get_option( 'bookly_cron_reminder_times' );
        $form  = new Forms\Notifications( 'email', Components::getInstance() );
        $alert = array( 'success' => array() );
        // Save action.
        if ( ! empty ( $_POST ) ) {
            if ( $this->csrfTokenValid() ) {
                $form->bind( $this->getPostParameters() );
                $form->save();
                $alert['success'][] = __( 'Settings saved.', 'bookly' );
                update_option( 'bookly_email_send_as', $this->getParameter( 'bookly_email_send_as' ) );
                update_option( 'bookly_email_reply_to_customers', $this->getParameter( 'bookly_email_reply_to_customers' ) );
                update_option( 'bookly_email_sender', $this->getParameter( 'bookly_email_sender' ) );
                update_option( 'bookly_email_sender_name', $this->getParameter( 'bookly_email_sender_name' ) );
                update_option( 'bookly_ntf_processing_interval', (int) $this->getParameter( 'bookly_ntf_processing_interval' ) );
                foreach ( array( 'staff_agenda', 'client_follow_up', 'client_reminder', 'client_birthday_greeting' ) as $type ) {
                    $cron_reminder[ $type ] = $this->getParameter( $type . '_cron_hour' );
                }
                foreach ( array( 'client_reminder_1st', 'client_reminder_2nd', 'client_reminder_3rd', ) as $type ) {
                    $cron_reminder[ $type ] = $this->getParameter( $type . '_cron_before_hour' );
                }
                update_option( 'bookly_cron_reminder_times', $cron_reminder );
            }
        }
        $cron_uri = plugins_url( 'lib/utils/send_notifications_cron.php', Lib\Plugin::getMainFile() );
        wp_localize_script( 'bookly-alert.js', 'BooklyL10n',  array(
            'csrf_token'   => Lib\Utils\Common::getCsrfToken(),
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'alert'        => $alert,
            'sent_successfully' => __( 'Sent successfully.', 'bookly' ),
            'limitations'  => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );
        $statuses = Lib\Entities\CustomerAppointment::getStatuses();
        foreach ( range( 1, 23 ) as $hours ) {
            $bookly_ntf_processing_interval_values[] = array( $hours, Lib\Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
        }
        $this->render( 'index', compact( 'form', 'cron_uri', 'cron_reminder', 'statuses', 'bookly_ntf_processing_interval_values' ) );
    }

    public function executeGetEmailNotificationsData()
    {
        $form = new Forms\Notifications( 'email', Components::getInstance() );

        $bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
            get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );

        $bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
            get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );

        $notifications = array();
        foreach ( $form->getData() as $notification ) {
            $name = Lib\Entities\Notification::getName( $notification['type'] );
            if ( in_array( $notification['type'], Lib\Entities\Notification::getCustomNotificationTypes() ) && $notification['subject'] != '' ) {
                // In window Test Email Notification
                // for custom notification, subject is name.
                $name = $notification['subject'];
            }
            $notifications[] = array(
                'type'   => $notification['type'],
                'name'   => $name,
                'active' => $notification['active'],
            );
        }

        $result = array(
            'notifications' => $notifications,
            'sender_email'  => $bookly_email_sender,
            'sender_name'   => $bookly_email_sender_name,
            'send_as'       => get_option( 'bookly_email_send_as' ),
            'reply_to_customers' => get_option( 'bookly_email_reply_to_customers' ),
        );

        wp_send_json_success( $result );
    }

    public function executeTestEmailNotifications(){}

    /**
     * Create new custom notification
     */
    public function executeCreateCustomNotification(){}

    /**
     * Delete custom notification
     */
    public function executeDeleteCustomNotification()
    {
        $id = $this->getParameter( 'id' );
        Lib\Entities\Notification::query()
            ->delete()
            ->where( 'id', $id )
            ->whereIN( 'type', Lib\Entities\Notification::getCustomNotificationTypes() )
            ->execute();

        wp_send_json_success();
    }

}