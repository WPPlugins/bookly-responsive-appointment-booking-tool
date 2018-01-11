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
        $form  = new Forms\Notifications( 'email' );
        $alert = array( 'success' => array() );
        // Save action.
        if ( ! empty ( $_POST ) ) {
            $form->bind( $this->getPostParameters() );
            $form->save();
            $alert['success'][] = __( 'Settings saved.', 'bookly' );
            update_option( 'bookly_email_send_as',            $this->getParameter( 'bookly_email_send_as' ) );
            update_option( 'bookly_email_reply_to_customers', $this->getParameter( 'bookly_email_reply_to_customers' ) );
            update_option( 'bookly_email_sender',             $this->getParameter( 'bookly_email_sender' ) );
            update_option( 'bookly_email_sender_name',        $this->getParameter( 'bookly_email_sender_name' ) );
            foreach ( array( 'staff_agenda', 'client_follow_up', 'client_reminder' ) as $type ) {
                $cron_reminder[ $type ] = $this->getParameter( $type . '_cron_hour' );
            }
            update_option( 'bookly_cron_reminder_times', $cron_reminder );
        }
        $cron_path = realpath( Lib\Plugin::getDirectory() . '/lib/utils/send_notifications_cron.php' );
        wp_localize_script( 'bookly-alert.js', 'BooklyL10n',  array(
            'alert' => $alert,
            'sent_successfully' => __( 'Sent successfully.', 'bookly' ),
            'limitations' => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );
        $this->render( 'index', compact( 'form', 'cron_path', 'cron_reminder' ) );
    }

    public function executeGetEmailNotificationsData()
    {
        $form = new Forms\Notifications( 'email' );

        $bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
            get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );

        $bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
            get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );

        $notifications = array();
        foreach ( $form->getData() as $notification ) {
            $notifications[] = array(
                'type'   => $notification['type'],
                'name'   => $notification['name'],
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

    public function executeTestEmailNotifications()
    {
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_bookly_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}