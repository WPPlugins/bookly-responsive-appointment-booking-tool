<?php
namespace BooklyLite\Backend\Modules\Notifications\Forms;

use BooklyLite\Lib;

/**
 * Class Notifications
 * @package BooklyLite\Backend\Modules\Notifications\Forms
 */
class Notifications extends Lib\Base\Form
{
    public $types = array(
        'single' => array(
            'client_pending_appointment',
            'staff_pending_appointment',
            'client_approved_appointment',
            'staff_approved_appointment',
            'client_cancelled_appointment',
            'staff_cancelled_appointment',
            'client_rejected_appointment',
            'staff_rejected_appointment',
            'client_new_wp_user',
            'client_reminder',
            'client_reminder_1st',
            'client_reminder_2nd',
            'client_reminder_3rd',
            'client_follow_up',
            'client_birthday_greeting',
            'staff_agenda',
        ),
        'combined' => array(
            'client_pending_appointment_cart',
            'client_approved_appointment_cart',
        ),
        'custom' => array(),
    );

    public $gateway;

    /** @var \BooklyLite\Backend\Modules\Notifications\Components|\BooklyLite\Backend\Modules\Sms\Components|Lib\Base\Components */
    protected $codes;

    /**
     * Notifications constructor.
     *
     * @param string              $gateway
     * @param \BooklyLite\Backend\Modules\Notifications\Components|\BooklyLite\Backend\Modules\Sms\Components $components
     */
    public function __construct( $gateway = 'email', Lib\Base\Components $components )
    {
        /*
         * make Visual Mode as default (instead of Text Mode)
         * allowed: tinymce - Visual Mode, html - Text Mode, test - no one Mode selected
         */
        add_filter( 'wp_default_editor', create_function( '', 'return \'tinymce\';' ) );
        $this->types   = Lib\Proxy\Shared::prepareNotificationTypes( $this->types );
        $this->gateway = $gateway;
        if ( ! Lib\Config::combinedNotificationsEnabled() ) {
            $this->types['combined'] = array();
        }
        $this->types['custom'] = Lib\Entities\Notification::getCustomNotificationTypes();
        $this->codes = $components;
        $this->setFields( array( 'id', 'active', 'type', 'subject', 'message', 'to_customer', 'to_staff', 'to_admin', 'attach_ics', 'settings' ) );
        $this->load();
    }

    public function bind( array $_post = array(), array $files = array() )
    {
        $this->data = $_post['notification'];
    }

    /**
     * Save form.
     */
    public function save()
    {
        /** @var Lib\Entities\Notification[] $notifications */
        $notifications = Lib\Entities\Notification::query()
            ->where( 'gateway', $this->gateway )
            ->indexBy( 'id' )
            ->find();
        foreach ( $this->data as $id => $fields ) {
            $notifications[ $id ]->setFields( $fields )->save();
            $data = array_merge( $this->data[ $id ], $notifications[ $id ]->getFields() );
            $this->data[ $id ] = $data;
        }
    }

    public function load()
    {
        $notifications = Lib\Entities\Notification::query()
            ->where( 'gateway', $this->gateway )
            ->fetchArray();
        foreach ( $notifications as $notification ) {
            $this->data[ $notification['id'] ] = $notification;
        }
    }

    /**
     * @param string $group
     * @return array
     */
    public function getNotifications( $group )
    {
        $notifications = array();
        foreach ( $this->types[ $group ] as $type ) {
            foreach ( $this->data as $notification ) {
                if ( $notification['type'] == $type ) {
                    $notifications[] = $notification;
                }
            }
        }

        return $notifications;
    }

    /**
     * Render subject.
     *
     * @param int $id
     */
    public function renderSubject( $id )
    {
        printf(
            '<div class="form-group">
                <label for="%1$s">%2$s</label>
                <input type="text" class="form-control" id="%1$s" name="%3$s" value="%4$s"/>
            </div>',
            'notification_'.$id.'_subject',
            __( 'Subject', 'bookly' ),
            'notification[' . $id . '][subject]',
            esc_attr( $this->data[ $id ]['subject'] )
        );
    }

    /**
     * Render message editor.
     *
     * @param int $id
     */
    public function renderEditor( $id )
    {
        $attr_id = 'notification_' . $id . '_message';
        $name    = 'notification[' . $id . '][message]';
        $value   = $this->data[ $id ]['message'];

        if ( $this->gateway == 'sms' ) {
            printf(
                '<div class="form-group">
                    <label for="%1$s">%2$s</label>
                    <textarea rows="6" id="%1$s" name="%3$s" class="form-control">%4$s</textarea>
                </div>',
                $attr_id,
                __( 'Message', 'bookly' ),
                $name,
                esc_textarea( $value )
            );
        } else {
            $settings = array(
                'textarea_name' => $name,
                'media_buttons' => false,
                'editor_height' => 384,
                'tinymce'       => array(
                    'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,'.
                                                 'bullist,blockquote,|,justifyleft,justifycenter'.
                                                 ',justifyright,justifyfull,|,link,unlink,|'.
                                                 ',spellchecker,wp_fullscreen,wp_adv'
                )
            );

            echo '<div class="form-group"><label>' . __( 'Message', 'bookly' ) . '</label>';
            wp_editor( $value, $attr_id, $settings );
            echo '</div>';
        }
    }

    /**
     * Render to admin.
     *
     * @param array $notification
     */
    public function renderCopy( array $notification )
    {
        if (   strpos( $notification['type'], 'staff' ) === 0
            || strpos( $notification['type'], 'custom_notification' ) === 0
        ) {
            $id   = $notification['id'];
            $name = 'notification[' . $notification['id'] . '][to_admin]';
            printf(
                '<div class="form-group">
                    <input name="%1$s" type="hidden" value="0">
                    <div class="checkbox"><label for="%2$s"><input id="%2$s" name="%1$s" type="checkbox" value="1" %3$s> %4$s</label></div>
                </div>',
                $name,
                'notification_' . $id . '_copy',
                checked( $notification['to_admin'], true, false ),
                __( 'Send copy to administrators', 'bookly' )
            );
        }
    }

    /**
     * Render attach ICS file.
     *
     * @param array $notification
     */
    public function renderAttachIcs( array $notification )
    {
        if ( in_array( $notification['type'], array(
            'client_pending_appointment',
            'staff_pending_appointment',
            'client_approved_appointment',
            'staff_approved_appointment',
            'client_cancelled_appointment',
            'staff_cancelled_appointment',
            'client_rejected_appointment',
            'staff_rejected_appointment',
            'client_waitlisted_appointment',
            'staff_waitlisted_appointment',
            'client_reminder',
            'client_reminder_1st',
            'client_reminder_2nd',
            'client_reminder_3rd',
            'client_follow_up',
        ) ) ) {
            $id   = $notification['id'];
            $name = sprintf( 'notification[%d][attach_ics]', $id );
            printf(
                '<div class="form-group">
                    <input name="%1$s" type="hidden" value="0">
                    <div class="checkbox"><label for="%2$s"><input id="%2$s" name="%1$s" type="checkbox" value="1" %3$s> %4$s</label></div>
                </div>',
                $name,
                'notification_' . $id . '_ics',
                checked( $notification['attach_ics'], true, false ),
                __( 'Attach ICS file', 'bookly' )
            );
        }
    }

    /**
     * Render sending time.
     *
     * @param array $notification
     */
    public function renderSendingTime( array $notification )
    {
        $type = $notification['type'];
        if ( in_array( $type, array( 'staff_agenda', 'client_follow_up', 'client_reminder', 'client_reminder_1st', 'client_reminder_2nd', 'client_reminder_3rd', 'client_birthday_greeting' ) ) ) {
            $cron_reminder = (array) get_option( 'bookly_cron_reminder_times' );
            $before_hour = strpos( $type, 'client_reminder_' ) !== false;
            $data = array(
                $before_hour ? $type . '_cron_before_hour' : $type . '_cron_hour',
                __( 'Sending time', 'bookly' ),
                __( 'Set the time you want the notification to be sent.', 'bookly' ),
            );
            if ( $before_hour ) {
                $data[] = implode( '', array_map( function ( $hour ) use ( $type, $cron_reminder ) {
                    return sprintf(
                        '<option value="%s" %s>%s</option>',
                        $hour,
                        selected( $cron_reminder[ $type ], $hour, false ),
                        sprintf( __( '%s before', 'bookly' ), \BooklyLite\Lib\Utils\DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) )
                    );
                }, array_merge( range( 1, 24 ), range( 48, 336, 24 ) ) ) );
            } else {
                $data[] = implode( '', array_map( function ( $hour ) use ( $type, $cron_reminder ) {
                    return sprintf(
                        '<option value="%s" %s>%s</option>',
                        $hour,
                        selected( $cron_reminder[ $type ], $hour, false ),
                        Lib\Utils\DateTime::buildTimeString( $hour * HOUR_IN_SECONDS, false )
                    );
                }, range( 0, 23 ) ) );
            }

            vprintf(
                '<div class="form-group">
                    <label for="%1$s">%2$s</label>
                    <p class="help-block">%3$s</p>
                    <select class="form-control" name="%1$s" id="%1$s">
                        %4$s
                    </select>
                </div>',
                $data
            );
        }
    }

    /**
     * @param string $notification_type
     */
    public function renderCodes( $notification_type )
    {
        $this->codes->renderCodes( $notification_type );
    }

}