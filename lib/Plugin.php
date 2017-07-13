<?php
namespace BooklyLite\Lib;

/**
 * Class Plugin
 * @package BooklyLite\Lib
 */
abstract class Plugin extends Base\Plugin
{
    protected static $prefix = 'bookly_';
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;

    public static function registerHooks()
    {
        parent::registerHooks();
        if ( is_admin() ) {
            add_action( 'admin_notices', function () {
                if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], 'bookly-' ) === 0 ) {
                    // Subscribe notice.
                    \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderSubscribeNotice();
                }
            }, 10, 0 );
        }

        add_action( 'bookly_daily_routine', function () {
            // SMS Summary routine
            if ( get_option( 'bookly_sms_notify_weekly_summary' ) && get_option( 'bookly_sms_token' ) ) {
                if ( get_option( 'bookly_sms_notify_weekly_summary_sent' ) != date( 'W' ) ) {
                    $admin_emails = Utils\Common::getAdminEmails();
                    if ( ! empty ( $admin_emails ) ) {
                        $sms     = new SMS();
                        $start   = date_create( 'last week' )->format( 'Y-m-d 00:00:00' );
                        $end     = date_create( 'this week' )->format( 'Y-m-d 00:00:00' );
                        $summary = $sms->getSummary( Utils\DateTime::applyTimeZoneOffset( $start, 0 ), Utils\DateTime::applyTimeZoneOffset( $end, 0 ) );
                        if ( $summary !== false ) {
                            $notification_list = '';
                            foreach ( $summary->notifications as $type_id => $count ) {
                                $notification_list .= PHP_EOL . Entities\Notification::getName( Entities\Notification::getTypeString( $type_id ) ) . ': ' . $count->delivered;
                                if ( $count->delivered < $count->sent ) {
                                    $notification_list .= ' (' . $count->sent . ' ' . __( 'sent to our system', 'bookly' ) . ')';
                                }
                            }
                            // For balance.
                            $sms->loadProfile();
                            $message =
                                __( 'Hope you had a good weekend! Here\'s a summary of messages we\'ve delivered last week:
{notification_list}

Your system sent a total of {total} messages last week (that\'s {delta} {sign} than the week before).
Cost of sending {total} messages was {amount}. You current Bookly SMS balance is {balance}.

Thank you for using Bookly SMS. We wish you a lucky week!
Bookly SMS Team.', 'bookly' );
                            $message = strtr( $message,
                                array(
                                    '{notification_list}' => $notification_list,
                                    '{total}'             => $summary->total,
                                    '{delta}'             => abs( $summary->delta ),
                                    '{sign}'              => $summary->delta >= 0 ? __( 'more', 'bookly' ) : __( 'less', 'bookly' ),
                                    '{amount}'            => '$' . $summary->amount,
                                    '{balance}'           => '$' . $sms->getBalance(),
                                )
                            );
                            wp_mail( $admin_emails, __( 'Bookly SMS weekly summary', 'bookly' ), $message );
                            update_option( 'bookly_sms_notify_weekly_summary_sent', date( 'W' ) );
                        }
                    }
                }
            }
        }, 10, 0 );
    }

}