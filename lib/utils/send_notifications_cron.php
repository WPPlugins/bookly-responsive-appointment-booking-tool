<?php
namespace BooklyLite\Lib\Utils;

use BooklyLite\Lib;
use BooklyLite\Lib\Entities;

define( 'WP_USE_THEMES', false );
if ( isset( $argv ) ) {
    foreach ( $argv as $argument ) {
        if ( strpos( $argument, 'host=' ) === 0 ) {
            $_SERVER['HTTP_HOST'] = substr( $argument, 5 );
        }
    }
}
require_once __DIR__ . '/../../../../../wp-load.php';
require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/general-template.php';
require_once ABSPATH . WPINC . '/pluggable.php';
require_once ABSPATH . WPINC . '/link-template.php';

if ( ! class_exists( '\BooklyLite\Lib\Plugin' ) ) {
    // Bookly on host is inactive.
    if ( is_multisite() ) {
        $working_directory = getcwd();
        // absolute path for dir appointment-booking
        chdir( realpath( __DIR__ . '/../../' ) );
        include_once 'autoload.php';
        // Restore working directory.
        chdir( $working_directory );
    } else {
        die( 'Bookly is inactive' );
    }
} else {
    add_action( 'bookly_send_notifications', function() { new Notifier(); } );
}

/**
 * Class Notifier
 * @package BooklyLite\Lib\Utils
 */
class Notifier
{
    private $mysql_now; // format: YYYY-MM-DD HH:MM:SS

    /** @var Lib\SMS $sms */
    private $sms;

    /**
     * @param Entities\Notification $notification
     */
    public function processNotification( Entities\Notification $notification )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $date  = new \DateTime();
        $hours = get_option( 'bookly_cron_reminder_times' );
        $compounds = array();

        switch ( $notification->get( 'type' ) ) {
            case 'staff_agenda':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    /** @var \stdClass[] $rows */
                    $rows = $wpdb->get_results(
                        'SELECT
                            `a`.*,
                            `ca`.`locale`,
                            `ca`.`extras`,
                            `ca`.`location_id`,
                            `c`.`name`       AS `customer_name`,
                            `s`.`title`      AS `service_title`,
                            `s`.`info`       AS `service_info`,
                            `st`.`email`     AS `staff_email`,
                            `st`.`phone`     AS `staff_phone`,
                            `st`.`full_name` AS `staff_name`,
                            `st`.`info`      AS `staff_info`
                        FROM `' . Entities\CustomerAppointment::getTableName() . '` `ca`
                        LEFT JOIN `' . Entities\Appointment::getTableName() . '` `a`   ON `a`.`id` = `ca`.`appointment_id`
                        LEFT JOIN `' . Entities\Customer::getTableName() . '` `c`      ON `c`.`id` = `ca`.`customer_id`
                        LEFT JOIN `' . Entities\Service::getTableName() . '` `s`       ON `s`.`id` = `a`.`service_id`
                        LEFT JOIN `' . Entities\Staff::getTableName() . '` `st`        ON `st`.`id` = `a`.`staff_id`
                        LEFT JOIN `' . Entities\StaffService::getTableName() . '` `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`
                        WHERE `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_CANCELLED . '" AND
                        `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_REJECTED . '" AND
                        DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Entities\SentNotification::getTableName() . '` `sn` WHERE
                                DATE(`sn`.`created`) = DATE("' . $this->mysql_now . '") AND
                                `sn`.`gateway`       = "sms" AND
                                `sn`.`type`          = "staff_agenda" AND
                                `sn`.`staff_id`      = `a`.`staff_id`
                        )
                        ORDER BY `a`.`start_date`'
                    );

                    if ( $rows ) {
                        $appointments = array();
                        foreach ( $rows as $row ) {
                            $appointments[ $row->staff_id ][] = $row;
                        }

                        $columns = array(
                            '{10_date}'     => __( 'Date', 'bookly' ),
                            '{30_service}'  => __( 'Service', 'bookly' ),
                            '{40_customer}' => __( 'Customer', 'bookly' ),
                        );
                        if ( Lib\Config::isLocationsEnabled() ) {
                            $columns['{20_location}'] = __( 'Location', 'bookly' );
                        }
                        ksort( $columns );
                        $table = '%s';
                        $tr    = implode( ', ', array_keys( $columns ) ) . PHP_EOL;

                        foreach ( $appointments as $staff_id => $collection ) {
                            $sent = false;
                            $staff_email = null;
                            $staff_phone = null;
                            $agenda = '';
                            foreach ( $collection as $appointment ) {
                                $tr_data = array(
                                    '{10_date}'     => Lib\Utils\DateTime::formatTime( $appointment->start_date ) . '-' . Lib\Utils\DateTime::formatTime( $appointment->end_date ),
                                    '{40_customer}' => $appointment->customer_name,
                                );

                                $location                 = apply_filters( 'bookly_locations_find_by_id', false, $appointment->location_id );
                                $tr_data['{20_location}'] = $location ? $location->get( 'name' ) : '';

                                // Extras
                                $extras  = '';
                                $_extras = apply_filters( 'bookly_service_extras_get_data_for_appointment', array(), $appointment->extras, false );
                                if ( ! empty ( $_extras ) ) {
                                    foreach ( $_extras as $extra ) {
                                        $extras .= sprintf( ', %s', str_replace( '&nbsp;&times;&nbsp;', ' x ', $extra['title'] ) );
                                    }
                                }

                                $tr_data['{30_service}'] = $appointment->service_title . $extras;
                                $agenda .= strtr( $tr, $tr_data );

                                $staff_email = $appointment->staff_email;
                                $staff_phone = $appointment->staff_phone;
                            }

                            if ( $notification->get( 'gateway' ) == 'email' && $staff_email != '' || $notification->get( 'gateway' ) == 'sms' && $staff_phone != '' ) {
                                $codes = new Lib\NotificationCodes();
                                $codes->set( 'next_day_agenda',   sprintf( $table, $agenda ) );
                                $codes->set( 'appointment_start', $appointment->start_date );
                                $codes->set( 'staff_name',        $appointment->staff_name );
                                $codes->set( 'staff_info',        $appointment->staff_info );
                                $codes->set( 'service_info',      $appointment->service_info );

                                $sent = Lib\NotificationSender::sendFromCronToStaff( $notification, $codes, $staff_email, $staff_phone );
                            }

                            if ( $sent ) {
                                $this->_notificationSent( $notification->get( 'type' ), $notification->get( 'gateway' ), null, $staff_id );
                            }
                        }
                    }
                }
                break;
            case 'client_follow_up':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    $appointments = $wpdb->get_results(
                        'SELECT `ca`.*
                        FROM `' . Entities\CustomerAppointment::getTableName() . '` `ca`
                        LEFT JOIN `' . Entities\Appointment::getTableName() . '` `a` ON `a`.`id` = `ca`.`appointment_id`
                        WHERE `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_CANCELLED . '" AND
                        `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_REJECTED . '" AND
                        DATE("' . $this->mysql_now . '") = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Entities\SentNotification::getTableName() . '` `sn` WHERE
                                DATE(`sn`.`created`)           = DATE("' . $this->mysql_now . '") AND
                                `sn`.`gateway`                 = "sms" AND
                                `sn`.`type`                    = "client_follow_up" AND
                                `sn`.`customer_appointment_id` = `ca`.`id`
                        ) ORDER BY `a`.`start_date`',
                        ARRAY_A
                    );

                    if ( $appointments ) {
                        foreach ( $appointments as $ca ) {
                            if ( ! in_array( $ca['compound_token'], $compounds ) ) {
                                $customer_appointment = new Entities\CustomerAppointment( $ca );
                                if ( Lib\NotificationSender::sendFromCronToClient( $notification, $customer_appointment ) ) {
                                    $this->_notificationSent( $notification->get( 'type' ), $notification->get( 'gateway' ), $ca['id'] );
                                }
                                if ( ! empty( $ca['compound_token'] ) ) {
                                    $compounds[] = $ca['compound_token'];
                                }
                            } else {
                                // Mark the 'sent' notifications for services included in the compound service.
                                $this->_notificationSent( $notification->get( 'type' ), $notification->get( 'gateway' ), $ca['id'] );
                            }
                        }
                    }
                }
                break;
            case 'client_reminder':
                if ( $date->format( 'H' ) >= $hours[ $notification->get( 'type' ) ] ) {
                    $appointments = $wpdb->get_results(
                        'SELECT `ca`.*
                        FROM `' . Entities\CustomerAppointment::getTableName() . '` `ca`
                        LEFT JOIN `' . Entities\Appointment::getTableName() . '` `a` ON `a`.`id` = `ca`.`appointment_id`
                        WHERE `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_CANCELLED . '" AND
                        `ca`.`status` != "' . Entities\CustomerAppointment::STATUS_REJECTED . '" AND
                        DATE(DATE_ADD("' . $this->mysql_now . '", INTERVAL 1 DAY)) = DATE(`a`.`start_date`) AND NOT EXISTS (
                            SELECT * FROM `' . Entities\SentNotification::getTableName() . '` `sn` WHERE
                                DATE(`sn`.`created`)           = DATE("' . $this->mysql_now . '") AND
                                `sn`.`gateway`                 = "sms" AND
                                `sn`.`type`                    = "client_reminder" AND
                                `sn`.`customer_appointment_id` = `ca`.`id`
                        ) ORDER BY `a`.`start_date`',
                        ARRAY_A
                    );

                    if ( $appointments ) {
                        foreach ( $appointments as $ca ) {
                            if ( ! in_array( $ca['compound_token'], $compounds ) ) {
                                $customer_appointment = new Entities\CustomerAppointment( $ca );
                                if ( Lib\NotificationSender::sendFromCronToClient( $notification, $customer_appointment ) ) {
                                    $this->_notificationSent( $notification->get( 'type' ), $notification->get( 'gateway' ), $ca['id'] );
                                }
                                if ( ! empty( $ca['compound_token'] ) ) {
                                    $compounds[] = $ca['compound_token'];
                                }
                            } else {
                                // Mark the 'sent' notifications for services included in the compound service.
                                $this->_notificationSent( $notification->get( 'type' ), $notification->get( 'gateway' ), $ca['id'] );
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Mark sent notification.
     *
     * @param      $type
     * @param      $gateway
     * @param      $ca_id
     * @param      $staff_id
     */
    private function _notificationSent( $type, $gateway, $ca_id, $staff_id = 1 )
    {
        $sent_notification = new Entities\SentNotification();
        $sent_notification->set( 'customer_appointment_id', $ca_id )
            ->set( 'type',     $type )
            ->set( 'staff_id', 1 )
            ->set( 'gateway',  $gateway )
            ->set( 'created',  $this->mysql_now )
            ->save();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        date_default_timezone_set( Common::getTimezoneString() );

        wp_load_translations_early();

        $now = new \DateTime();
        $this->mysql_now = $now->format( 'Y-m-d H:i:s' );
        $this->sms = new Lib\SMS();

        $query = Entities\Notification::query()
            ->where( 'active', 1 )
            ->where( 'gateway', 'sms' )
            ->whereIn( 'type', array( 'staff_agenda', 'client_follow_up', 'client_reminder' ) );

        foreach ( $query->find() as $notification ) {
            $this->processNotification( $notification );
        }
    }

}

do_action( 'bookly_send_notifications' );