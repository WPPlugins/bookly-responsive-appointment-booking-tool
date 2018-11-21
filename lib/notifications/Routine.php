<?php
namespace Bookly\Lib\Notifications;

use Bookly\Lib;
use Bookly\Lib\Entities;
use Bookly\Lib\DataHolders;

/**
 * Class Routine
 * @package Bookly\Lib\Notifications
 */
abstract class Routine
{
    /** @var Lib\Slots\DatePoint */
    private static $date_point;
    /** @var Lib\Slots\DatePoint */
    private static $today;
    /** @var string Format: YYYY-MM-DD */
    private static $mysql_today;
    /** @var int hours */
    private static $processing_interval;
    /** @var int */
    private static $hours;

    /** @var Lib\SMS $sms */
    private static $sms;

    /**
     * Build in notification
     *
     * @param Entities\Notification $notification
     */
    public static function processBuiltInNotification( Entities\Notification $notification )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $hours = get_option( 'bookly_cron_reminder_times' );
        /** @var DataHolders\Booking\Compound[] $compounds */
        $compounds = array();
        /** @var DataHolders\Booking\Collaborative[] $collaboratives */
        $collaboratives = array();

        switch ( $notification->getType() ) {
            case 'staff_agenda':
                if ( self::isTimeToSend( $hours[ $notification->getType() ] ) ) {
                    // Make settings for staff agenda from default values of Custom notifications (offset -24hours)
                    $ntf = new Entities\Notification();
                    $settings_default = DataHolders\Notification\Settings::getDefault();
                    $settings_default[ DataHolders\Notification\Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE ]['at_hour'] = $hours[ $notification->getType() ];
                    $ntf->setType( Entities\Notification::TYPE_STAFF_DAY_AGENDA )
                        ->setSettings( json_encode( $settings_default ) );

                    $settings = new DataHolders\Notification\Settings( $ntf );
                    $notification->setToStaff( true );
                    self::sendStaffAgenda( $notification, $settings );
                }
                break;
            case 'client_follow_up':
                if ( self::isTimeToSend( $hours[ $notification->getType() ] ) ) {
                    $appointments = $wpdb->get_results( sprintf(
                        'SELECT `ca`.*  FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                        WHERE `ca`.`status` IN("%s","%s")
                            AND DATE("%s") = DATE(`a`.`start_date`)
                            AND NOT EXISTS (
                                SELECT * FROM `%s` `sn`
                                WHERE DATE(`sn`.`created`) = DATE("%s")
                                    AND `sn`.`notification_id` = %d
                                    AND `sn`.`ref_id` = `ca`.`id`
                            ) ORDER BY `a`.`start_date`',
                        Entities\CustomerAppointment::getTableName(),
                        Entities\Appointment::getTableName(),
                        Entities\CustomerAppointment::STATUS_PENDING,
                        Entities\CustomerAppointment::STATUS_APPROVED,
                        self::$mysql_today,
                        Entities\SentNotification::getTableName(),
                        self::$mysql_today,
                        $notification->getId()
                    ), ARRAY_A );

                    if ( $appointments ) {
                        foreach ( $appointments as $ca ) {
                            if ( $ca['compound_token'] != '' ) {
                                if ( ! isset ( $compounds[ $ca['compound_token'] ] ) ) {
                                    $compounds[ $ca['compound_token'] ] = DataHolders\Booking\Compound::create(
                                        Entities\Service::find( $ca['compound_service_id'] )
                                    );
                                }
                                $compounds[ $ca['compound_token'] ]->addItem( DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) ) );
                            } else if ( $ca['collaborative_token'] != '' ) {
                                if ( ! isset ( $collaboratives[ $ca['collaborative_token'] ] ) ) {
                                    $collaboratives[ $ca['collaborative_token'] ] = DataHolders\Booking\Collaborative::create(
                                        Entities\Service::find( $ca['collaborative_service_id'] )
                                    );
                                }
                                $collaboratives[ $ca['collaborative_token'] ]->addItem( DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) ) );
                            } else {
                                $simple = DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) );
                                if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $simple ) ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $ca['id'] );
                                }
                            }
                        }
                        foreach ( $compounds as $compound ) {
                            if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $compound ) ) {
                                /** @var DataHolders\Booking\Simple $item */
                                foreach ( $compound->getItems() as $item ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                                }
                            }
                        }
                        foreach ( $collaboratives as $collaborative ) {
                            if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $collaborative ) ) {
                                /** @var DataHolders\Booking\Simple $item */
                                foreach ( $collaborative->getItems() as $item ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                                }
                            }
                        }
                    }
                }
                break;
            case 'client_reminder':
                if ( self::isTimeToSend( $hours[ $notification->getType() ] ) ) {
                    $appointments = $wpdb->get_results( sprintf(
                        'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                        WHERE `ca`.`status` IN("%s","%s")
                            AND DATE("%s") = DATE(`a`.`start_date`)
                            AND NOT EXISTS (
                                SELECT * FROM `%s` `sn`
                                WHERE DATE(`sn`.`created`) = DATE("%s")
                                    AND `sn`.`notification_id` = %d
                                    AND `sn`.`ref_id` = `ca`.`id`
                            ) ORDER BY `a`.`start_date`',
                        Entities\CustomerAppointment::getTableName(),
                        Entities\Appointment::getTableName(),
                        Entities\CustomerAppointment::STATUS_PENDING,
                        Entities\CustomerAppointment::STATUS_APPROVED,
                        self::$date_point->modify( DAY_IN_SECONDS )->format( 'Y-m-d' ),
                        Entities\SentNotification::getTableName(),
                        self::$mysql_today,
                        $notification->getId()
                    ), ARRAY_A );

                    if ( $appointments ) {
                        foreach ( $appointments as $ca ) {
                            if ( $ca['compound_token'] != '' ) {
                                if ( ! isset ( $compounds[ $ca['compound_token'] ] ) ) {
                                    $compounds[ $ca['compound_token'] ] = DataHolders\Booking\Compound::create(
                                        Entities\Service::find( $ca['compound_service_id'] )
                                    );
                                }
                                $compounds[ $ca['compound_token'] ]->addItem( DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) ) );
                            } else if ( $ca['collaborative_token'] != '' ) {
                                if ( ! isset ( $collaboratives[ $ca['collaborative_token'] ] ) ) {
                                    $collaboratives[ $ca['collaborative_token'] ] = DataHolders\Booking\Collaborative::create(
                                        Entities\Service::find( $ca['collaborative_service_id'] )
                                    );
                                }
                                $collaboratives[ $ca['collaborative_token'] ]->addItem( DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) ) );
                            } else {
                                $simple = DataHolders\Booking\Simple::create( new Entities\CustomerAppointment( $ca ) );
                                if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $simple ) ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $ca['id'] );
                                }
                            }
                        }
                        foreach ( $compounds as $compound ) {
                            if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $compound ) ) {
                                /** @var DataHolders\Booking\Simple $item */
                                foreach ( $compound->getItems() as $item ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                                }
                            }
                        }
                        foreach ( $collaboratives as $collaborative ) {
                            if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $collaborative ) ) {
                                /** @var DataHolders\Booking\Simple $item */
                                foreach ( $collaborative->getItems() as $item ) {
                                    Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                                }
                            }
                        }
                    }
                }
                break;
            case 'client_reminder_1st':
            case 'client_reminder_2nd':
            case 'client_reminder_3rd':
                $ca_list = self::getClientReminderCustomerAppointments( $notification, $hours[ $notification->getType() ] * 60 /* minutes */ );
                foreach ( $ca_list as $ca ) {
                    if ( $ca->getCompoundToken() != '' ) {
                        if ( ! isset ( $compounds[ $ca->getCompoundToken() ] ) ) {
                            $compounds[ $ca->getCompoundToken() ] = DataHolders\Booking\Compound::create(
                                Entities\Service::find( $ca->getCompoundServiceId() )
                            );
                        }
                        $compounds[ $ca->getCompoundToken() ]->addItem( DataHolders\Booking\Simple::create( $ca ) );
                    } else if ( $ca->getCollaborativeToken() != '' ) {
                        if ( ! isset ( $collaboratives[ $ca->getCollaborativeToken() ] ) ) {
                            $collaboratives[ $ca->getCollaborativeToken() ] = DataHolders\Booking\Collaborative::create(
                                Entities\Service::find( $ca->getCollaborativeServiceId() )
                            );
                        }
                        $collaboratives[ $ca->getCollaborativeToken() ]->addItem( DataHolders\Booking\Simple::create( $ca ) );
                    } else {
                        $simple = DataHolders\Booking\Simple::create( $ca );
                        if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $simple ) ) {
                            Lib\Notifications\Sender::wasSent( $notification, $ca->getId() );
                        }
                    }
                }
                foreach ( $compounds as $compound ) {
                    if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $compound ) ) {
                        /** @var DataHolders\Booking\Simple $item */
                        foreach ( $compound->getItems() as $item ) {
                            Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                        }
                    }
                }
            foreach ( $collaboratives as $collaborative ) {
                if ( Lib\Notifications\Sender::sendFromCronToClient( $notification, $collaborative ) ) {
                    /** @var DataHolders\Booking\Simple $item */
                    foreach ( $collaborative->getItems() as $item ) {
                        Lib\Notifications\Sender::wasSent( $notification, $item->getCA()->getId() );
                    }
                }
            }
                break;
            case 'client_birthday_greeting':
                if ( self::isTimeToSend( $hours[ $notification->getType() ] ) ) {
                    $customers = $wpdb->get_results( sprintf(
                        'SELECT `c`.* FROM `%s` `c`
                        WHERE `c`.`birthday` IS NOT NULL
                            AND DATE_FORMAT(`c`.`birthday`, "%%m-%%d") = "%s"
                            AND NOT EXISTS (
                                SELECT * FROM `%s` `sn`
                                WHERE DATE(`sn`.`created`) = DATE("%s")
                                    AND `sn`.`notification_id` = %d
                                    AND `sn`.`ref_id` = `c`.`id`
                            )',
                        Entities\Customer::getTableName(),
                        self::$date_point->format( 'm-d' ),
                        Entities\SentNotification::getTableName(),
                        self::$mysql_today,
                        $notification->getId()
                    ), ARRAY_A );

                    if ( $customers ) {
                        foreach ( $customers as $data ) {
                            $customer = new Entities\Customer( $data );
                            if ( Lib\Notifications\Sender::sendFromCronBirthdayGreeting( $notification, $customer ) ) {
                                Lib\Notifications\Sender::wasSent( $notification, $customer->getId() );
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Custom notification
     *
     * @param Entities\Notification $notification
     */
    public static function processCustomNotification( Entities\Notification $notification )
    {
        $settings = new DataHolders\Notification\Settings( $notification );

        if ( ! $settings->getInstant() ) {
            $ca_list   = array();
            $customers = array();

            switch ( $notification->getType() ) {
                // Appointment start date add time.
                case Entities\Notification::TYPE_APPOINTMENT_START_TIME:
                    $ca_list = self::getCustomerAppointments( $notification, $settings );
                    break;

                // Customer appointment status changed.
                case Entities\Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
                    $ca_list = self::getCustomerAppointmentsStatusChanged( $notification, $settings );
                    break;

                // New customer appointment.
                case Entities\Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED:
                    $ca_list = self::getNewCustomerAppointments( $notification, $settings );
                    break;

                // Last appointment.
                case Entities\Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
                    $ca_list = self::getLastCustomerAppointments( $notification, $settings );
                    break;

                // Client birthday.
                case Entities\Notification::TYPE_CUSTOMER_BIRTHDAY:
                    if ( $notification->getToCustomer() ) {
                        $customers = self::getCustomersWithBirthday( $notification, $settings );
                    }
                    break;

                // Staff Agenda.
                case Entities\Notification::TYPE_STAFF_DAY_AGENDA:
                    self::sendStaffAgenda( $notification, $settings );
                    break;
            }

            if ( $ca_list ) {
                Lib\Notifications\Sender::sendCustomNotification( $notification, $ca_list );
            } else {
                foreach ( $customers as $customer ) {
                    if ( Lib\Notifications\Sender::sendFromCronBirthdayGreeting( $notification, $customer ) ) {
                        Lib\Notifications\Sender::wasSent( $notification, $customer->getId() );
                    }
                }
            }
        }
    }

    /**
     * @param Entities\Notification $notification
     * @param integer               $minutes_interval
     * @return Entities\CustomerAppointment[]
     */
    private static function getClientReminderCustomerAppointments( Entities\Notification $notification, $minutes_interval )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $rows = (array) $wpdb->get_results( sprintf(
            'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
            WHERE `ca`.`status` IN("%s","%s")
                AND `a`.`start_date` BETWEEN "%s" AND "%s"
                AND NOT EXISTS (
                    SELECT * FROM `%s` `sn`
                    WHERE `sn`.`created` >= "%s"
                        AND `sn`.`notification_id` = %d
                        AND `sn`.`ref_id` = `ca`.`id`
                ) ORDER BY `a`.`start_date`',
            Entities\CustomerAppointment::getTableName(),
            Entities\Appointment::getTableName(),
            Entities\CustomerAppointment::STATUS_PENDING,
            Entities\CustomerAppointment::STATUS_APPROVED,
            self::$date_point->modify( - ( self::$processing_interval * 60 - $minutes_interval ) * MINUTE_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
            self::$date_point->modify( $minutes_interval * MINUTE_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
            Entities\SentNotification::getTableName(),
            self::$date_point->modify( -15 * DAY_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
            $notification->getId()
        ), ARRAY_A );

        $ca_list = array();
        foreach ( $rows as $fields ) {
            $ca_list[] = new Entities\CustomerAppointment( $fields );
        }

        return $ca_list;
    }

    /**
     * Get customer appointments for notification
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     * @return Entities\CustomerAppointment[]
     */
    private static function getCustomerAppointments( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $ca_list  = array();
        $services = $settings->forServices();
        if ( $services != 'any' && count( $services ) == 0 ) {
            return $ca_list;
        }

        if ( $settings->getAtHour() !== null ) {
            // Send at time after start_date date (some day at 08:00)
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                $query = sprintf(
                    'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                    WHERE DATE(`a`.`start_date`) = DATE("%s")',
                    Entities\CustomerAppointment::getTableName(),
                    Entities\Appointment::getTableName(),
                    self::$today->modify( - $settings->getOffsetHours()  * HOUR_IN_SECONDS )->format( 'Y-m-d' )
                );
            } else {
                return $ca_list;
            }
        } else {
            $query = sprintf(
                'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                WHERE `a`.`start_date` BETWEEN "%s" AND "%s"',
                Entities\CustomerAppointment::getTableName(),
                Entities\Appointment::getTableName(),
                self::$date_point->modify( - ( $settings->getOffsetHours() + self::$processing_interval ) * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
                self::$date_point->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' )
            );
        }

        // Select appointments for which reminders need to be sent today.
        $query .= sprintf( ' AND NOT EXISTS ( %s )',
            self::getQueryIfNotificationWasSent( $notification )
        );
        if ( $settings->getStatus() != 'any' ) {
            $query .= sprintf( ' AND `ca`.`status` = "%s"', $settings->getStatus() );
        }
        if ( is_array( $services ) ) {
            $query .= sprintf( ' AND ( `a`.`service_id` IN (%1$s) OR `ca`.`compound_service_id` IN (%1$s) )', implode( ', ', $services ) );
        }
        foreach ( (array) $wpdb->get_results( $query, ARRAY_A ) as $fields ) {
            $ca_list[] = new Entities\CustomerAppointment( $fields );
        }

        return $ca_list;
    }

    /**
     * Get customer appointments with changed status
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     * @return Entities\CustomerAppointment[]
     */
    private static function getCustomerAppointmentsStatusChanged( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $ca_list  = array();
        $services = $settings->forServices();
        if ( $services != 'any' && count( $services ) == 0 ) {
            return $ca_list;
        }

        if ( $settings->getAtHour() !== null ) {
            // Send at time after status changed date (some day at 08:00)
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                $query = sprintf(
                    'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                    WHERE DATE(`ca`.`status_changed_at`) = DATE("%s")',
                    Entities\CustomerAppointment::getTableName(),
                    Entities\Appointment::getTableName(),
                    self::$today->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d' )
                );
            } else {
                return $ca_list;
            }
        } else {
            // Select appointments for which reminders need to be sent today.
            $query = sprintf(
                'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                WHERE `ca`.`status_changed_at` BETWEEN "%s" AND "%s"',
                Entities\CustomerAppointment::getTableName(),
                Entities\Appointment::getTableName(),
                self::$date_point->modify( - ( $settings->getOffsetHours() + self::$processing_interval ) * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
                self::$date_point->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' )
            );
        }

        // Select appointments for which reminders need to be sent today.
        $query .= sprintf( ' AND NOT EXISTS ( %s )',
            self::getQueryIfNotificationWasSent( $notification )
        );
        if ( $settings->getStatus() != 'any' ) {
            $query .= sprintf( ' AND `ca`.`status` = "%s"', $settings->getStatus() );
        }
        if ( is_array( $services ) ) {
            $query .= sprintf( ' AND ( `a`.`service_id` IN (%1$s) OR `ca`.`compound_service_id` IN (%1$s) )', implode( ', ', $services ) );
        }
        foreach ( (array) $wpdb->get_results( $query, ARRAY_A ) as $fields ) {
            $ca_list[] = new Entities\CustomerAppointment( $fields );
        }

        return $ca_list;
    }

    /**
     * Get approved customer appointments
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     * @return Entities\CustomerAppointment[]
     */
    private static function getNewCustomerAppointments( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $ca_list = array();
        $services = $settings->forServices();
        if ( $services != 'any' && count( $services ) == 0 ) {
            return $ca_list;
        }

        if ( $settings->getAtHour() !== null ) {
            // Send at time after created date (some day at 08:00)
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                $query = sprintf(
                    'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                    WHERE DATE(`ca`.`created`) = DATE("%s")',
                    Entities\CustomerAppointment::getTableName(),
                    Entities\Appointment::getTableName(),
                    self::$today->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d' )
                );
            } else {
                return $ca_list;
            }
        } else {
            // Select appointments for which reminders need to be sent today.
            $query = sprintf(
                'SELECT `ca`.* FROM `%s` `ca` LEFT JOIN `%s` `a` ON `a`.`id` = `ca`.`appointment_id`
                WHERE `ca`.`created` BETWEEN "%s" AND "%s"',
                Entities\CustomerAppointment::getTableName(),
                Entities\Appointment::getTableName(),
                self::$date_point->modify( - ( $settings->getOffsetHours() + self::$processing_interval ) * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
                self::$date_point->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' )
            );
        }

        // Select appointments for which reminders need to be sent today.
        $query .= sprintf( ' AND NOT EXISTS ( %s )',
            self::getQueryIfNotificationWasSent( $notification )
        );
        if ( $settings->getStatus() != 'any' ) {
            $query .= sprintf( ' AND `ca`.`status` = "%s"', $settings->getStatus() );
        }
        if ( is_array( $services ) ) {
            $query .= sprintf( ' AND ( `a`.`service_id` IN (%1$s) OR `ca`.`compound_service_id` IN (%1$s) )', implode( ', ', $services ) );
        }
        foreach ( (array) $wpdb->get_results( $query, ARRAY_A ) as $fields ) {
            $ca_list[] = new Entities\CustomerAppointment( $fields );
        }

        return $ca_list;
    }

    /**
     * Get last customer appointments for notification
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     * @return Entities\CustomerAppointment[]
     */
    private static function getLastCustomerAppointments( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $ca_list  = array();
        $services = $settings->forServices();
        if ( $services != 'any' && count( $services ) == 0 ) {
            return $ca_list;
        }

        $replace = array(
            '{bookly_appointments}'          => Entities\Appointment::getTableName(),
            '{bookly_customer_appointments}' => Entities\CustomerAppointment::getTableName(),
            '{bookly_customers}'             => Entities\Customer::getTableName(),
            '{bookly_sent_notifications}'    => Entities\SentNotification::getTableName(),
            '{ca2_status_equal}'             => 'true',
            '{ca3_status_equal}'             => 'true',
        );

        if ( $settings->getAtHour() !== null ) {
            // Send at time after created date (some day at 08:00)
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                $replace['{sent_time_interval}'] = sprintf( 'DATE("%s") = DATE(`start_date`)',
                    self::$today->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d' )
                );
            } else {
                return $ca_list;
            }
        } else {
            $replace['{sent_time_interval}'] = sprintf( '`start_date` BETWEEN "%s" AND "%s"',
                self::$date_point->modify( - ( $settings->getOffsetHours() + self::$processing_interval ) * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' ),
                self::$date_point->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'Y-m-d H:i:s' )
            );
        }

        if ( self::$hours >= $settings->getSendAtHour() ) {
            $query = sprintf(
                'SELECT `ca`.*, `a`.`start_date` FROM `{bookly_customer_appointments}` `ca`
                    LEFT JOIN `{bookly_appointments}` `a` ON `a`.`id` = `ca`.`appointment_id`
                WHERE `ca`.`id` IN(
                    SELECT (
                        SELECT `ca2`.`id` FROM `{bookly_appointments}` `a`
                            INNER JOIN `{bookly_customer_appointments}` `ca2` ON `ca2`.`appointment_id` = `a`.`id` 
                        WHERE `ca2`.`customer_id` = `c`.`id`
                            AND {ca2_status_equal}
                            AND `a`.`start_date` = (
                                SELECT MAX(`a2`.`start_date`) FROM `{bookly_appointments}` `a2`
                                    INNER JOIN `{bookly_customer_appointments}` `ca3` ON `ca3`.`appointment_id` = `a2`.`id`
                                WHERE `ca3`.`customer_id` = `c`.`id` AND {ca3_status_equal}
                            ) LIMIT 1
                        ) `last_ca_id` FROM `{bookly_customers}` `c`
                    )
                    AND {sent_time_interval}
                    AND NOT EXISTS ( %s )',
                self::getQueryIfNotificationWasSent( $notification )
            );

            if ( $settings->getStatus() != 'any' ) {
                $replace['{ca2_status_equal}'] = sprintf( '`ca2`.`status` = "%s"', $settings->getStatus() );
                $replace['{ca3_status_equal}'] = sprintf( '`ca3`.`status` = "%s"', $settings->getStatus() );
            }

            $query = strtr( $query, $replace );
            if ( is_array( $services ) ) {
                $query .= sprintf( ' AND ( `a`.`service_id` IN (%1$s) OR `ca`.`compound_service_id` IN (%1$s) )', implode( ', ', $services ) );
            }
            foreach ( (array) $wpdb->get_results( $query, ARRAY_A ) as $fields ) {
                $ca_list[] = new Entities\CustomerAppointment( $fields );
            }
        }

        return $ca_list;
    }

    /**
     * Customers for birthday congratulations
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     * @return Entities\Customer[]
     */
    private static function getCustomersWithBirthday( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $customers = array();

        if ( self::isTimeToSend( $settings->getAtHour() ) ) {
            $rows = (array) $wpdb->get_results( sprintf(
                'SELECT `c`.* FROM `%s` `c`
                WHERE `c`.`birthday` IS NOT NULL
                    AND DATE_FORMAT(`c`.`birthday`, "%%m-%%d") = "%s"
                    AND NOT EXISTS (
                        SELECT * FROM `%s` `sn`
                        WHERE DATE(`sn`.`created`) = DATE("%s")
                            AND `sn`.`notification_id` = %d
                            AND `sn`.`ref_id` = `c`.`id`
                    )',
                Entities\Customer::getTableName(),
                self::$today->modify( - $settings->getOffsetHours() * HOUR_IN_SECONDS )->format( 'm-d' ),
                Entities\SentNotification::getTableName(),
                self::$mysql_today,
                $notification->getId()
            ), ARRAY_A );

            foreach ( $rows as $fields ) {
                $customers[] = new Entities\Customer( $fields );
            }
        }

        return $customers;
    }

    /**
     * Send Staff Agenda
     *
     * @param Entities\Notification             $notification
     * @param DataHolders\Notification\Settings $settings
     */
    private static function sendStaffAgenda( Entities\Notification $notification, DataHolders\Notification\Settings $settings )
    {
        if ( $notification->getToStaff() || $notification->getToAdmin() ) {
            if ( self::isTimeToSend( $settings->getAtHour() ) ) {
                global $wpdb;
                /** @var \stdClass[] $rows */
                $rows = $wpdb->get_results( sprintf(
                    'SELECT
                    `a`.*,
                    `ca`.`locale`,
                    `ca`.`extras`,
                    `ca`.`id`        AS `ca_id`,
                    `c`.`full_name`  AS `customer_name`,
                    COALESCE(`s`.`title`, `a`.`custom_service_name`) AS `service_title`,
                    `s`.`info`       AS `service_info`,
                    `st`.`email`     AS `staff_email`,
                    `st`.`phone`     AS `staff_phone`,
                    `st`.`full_name` AS `staff_name`,
                    `st`.`info`      AS `staff_info`,
                    `st`.`attachment_id` AS `staff_attachment_id`
                FROM `%s` `ca`
                LEFT JOIN `%s` `a`  ON `a`.`id` = `ca`.`appointment_id`
                LEFT JOIN `%s` `c`  ON `c`.`id` = `ca`.`customer_id`
                LEFT JOIN `%s` `s`  ON `s`.`id`  = `a`.`service_id`
                LEFT JOIN `%s` `st` ON `st`.`id` = `a`.`staff_id`
                LEFT JOIN `%s` `ss` ON `ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`
                WHERE `st`.`visibility` != "archive"
                    AND `ca`.`status` IN("%s","%s")
                    AND DATE("%s") = DATE(`a`.`start_date`)
                    AND NOT EXISTS (
                        SELECT * FROM `%s` `sn` 
                        WHERE DATE(`sn`.`created`) = DATE("%s")
                            AND `sn`.`notification_id` = %d
                            AND `sn`.`ref_id` = `a`.`staff_id`
                    )
                ORDER BY `a`.`start_date`',
                    Entities\CustomerAppointment::getTableName(),
                    Entities\Appointment::getTableName(),
                    Entities\Customer::getTableName(),
                    Entities\Service::getTableName(),
                    Entities\Staff::getTableName(),
                    Entities\StaffService::getTableName(),
                    Entities\CustomerAppointment::STATUS_PENDING,
                    Entities\CustomerAppointment::STATUS_APPROVED,
                    self::$today->modify( abs( $settings->getOffsetHours() ) * HOUR_IN_SECONDS )->format( 'Y-m-d' ),
                    Entities\SentNotification::getTableName(),
                    self::$mysql_today,
                    $notification->getId()
                ) );

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
                    if ( Lib\Config::locationsActive() && get_option( 'bookly_locations_enabled' ) ) {
                        $columns['{20_location}'] = __( 'Location', 'bookly' );
                    }
                    $columns_extended = $columns;
                    if ( Lib\Config::customFieldsActive() && get_option( 'bookly_custom_fields_enabled' ) ) {
                        $columns_extended['{50_custom_fields}']  = __( 'Custom Fields', 'bookly' );
                        $columns_extended['{60_internal_notes}'] = __( 'Internal Notes', 'bookly' );
                    }
                    ksort( $columns );
                    ksort( $columns_extended );
                    $is_html = ( get_option( 'bookly_email_send_as' ) == 'html' && $notification->getGateway() != 'sms' );
                    if ( $is_html ) {
                        $table          = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><td>'
                            . implode( '</td><td>', $columns )
                            . '</td></tr></thead><tbody>%s</tbody></table>';
                        $table_extended = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><td>'
                            . implode( '</td><td>', $columns_extended )
                            . '</td></tr></thead><tbody>%s</tbody></table>';
                        $tr             = '<tr><td>' . implode( '</td><td>', array_keys( $columns ) ) . '</td></tr>';
                        $tr_extended    = '<tr><td>' . implode( '</td><td>', array_keys( $columns_extended ) ) . '</td></tr>';
                    } else {
                        $table          = '%s';
                        $table_extended = '%s';
                        $tr             = implode( ', ', array_keys( $columns ) ) . PHP_EOL;
                        $tr_extended    = implode( ', ', array_keys( $columns_extended ) ) . PHP_EOL;
                    }

                    foreach ( $appointments as $staff_id => $collection ) {
                        $sent            = false;
                        $staff_email     = null;
                        $staff_phone     = null;
                        $agenda          = '';
                        $agenda_extended = '';
                        foreach ( $collection as $appointment ) {
                            if ( ! Lib\Proxy\Pro::graceExpired() ) {
                                $tr_data = array(
                                    '{10_date}'     => Lib\Utils\DateTime::formatTime( $appointment->start_date ) . '-' . Lib\Utils\DateTime::formatTime( $appointment->end_date ),
                                    '{40_customer}' => $appointment->customer_name,
                                );

                                $location                 = Lib\Proxy\Locations::findById( $appointment->location_id );
                                $tr_data['{20_location}'] = $location ? $location->getName() : '';

                                // Extras
                                $extras  = '';
                                $_extras = (array) Lib\Proxy\ServiceExtras::getInfo( json_decode( $appointment->extras, true ), false );
                                if ( ! empty ( $_extras ) ) {
                                    foreach ( $_extras as $extra ) {
                                        if ( $is_html ) {
                                            $extras .= sprintf( '<li>%s</li>', $extra['title'] );
                                        } else {
                                            $extras .= sprintf( ', %s', str_replace( '&nbsp;&times;&nbsp;', ' x ', $extra['title'] ) );
                                        }
                                    }
                                    if ( $is_html ) {
                                        $extras = '<ul>' . $extras . '</ul>';
                                    }
                                }

                                $tr_data['{30_service}'] = $appointment->service_title . $extras;
                                $tr_data_extended        = $tr_data;
                                if ( Lib\Config::customFieldsActive() && get_option( 'bookly_custom_fields_enabled' ) ) {
                                    $ca = new Entities\CustomerAppointment();
                                    $ca->load( $appointment->ca_id );
                                    $custom_filed_str = '';
                                    foreach ( Lib\Proxy\CustomFields::getForCustomerAppointment( $ca ) as $custom_field ) {
                                        if ( $is_html ) {
                                            $custom_filed_str .= sprintf( '%s: %s<br/>', $custom_field['label'], $custom_field['value'] );
                                        } else {
                                            $custom_filed_str .= sprintf( '%s: %s ', $custom_field['label'], $custom_field['value'] );
                                        }
                                    }
                                    $tr_data_extended['{50_custom_fields}']  = $custom_filed_str;
                                    $tr_data_extended['{60_internal_notes}'] = $appointment->internal_note;
                                }
                                $agenda          .= strtr( $tr, $tr_data );
                                $agenda_extended .= strtr( $tr_extended, $tr_data_extended );
                            } else {
                                $agenda          = __( 'To view the details of these appointments, please contact your website administrator in order to verify Bookly Pro license.', 'bookly' );
                                $agenda_extended = __( 'To view the details of these appointments, please contact your website administrator in order to verify Bookly Pro license.', 'bookly' );
                            }
                            $staff_email = $appointment->staff_email;
                            $staff_phone = $appointment->staff_phone;
                        }

                        if ( $notification->getGateway() == 'email' && $staff_email != ''
                            || $notification->getGateway() == 'sms' && $staff_phone != ''
                        ) {
                            $staff_photo                     = wp_get_attachment_image_src( $appointment->staff_attachment_id, 'full' );
                            $codes                           = new Lib\Notifications\Codes();
                            $codes->agenda_date              = Lib\Utils\DateTime::formatDate( date( 'Y-m-d', current_time( 'timestamp' ) + abs( $settings->getOffsetHours() * HOUR_IN_SECONDS ) ) );
                            $codes->appointment_start        = $appointment->start_date;
                            $codes->next_day_agenda          = sprintf( $table, $agenda );
                            $codes->next_day_agenda_extended = sprintf( $table_extended, $agenda_extended );
                            $codes->service_info             = $appointment->service_info;
                            $codes->staff_email              = $appointment->staff_email;
                            $codes->staff_info               = $appointment->staff_info;
                            $codes->staff_name               = $appointment->staff_name;
                            $codes->staff_phone              = $appointment->staff_phone;
                            $codes->staff_photo              = $staff_photo ? $staff_photo[0] : null;

                            $sent = Lib\Notifications\Sender::sendStaffAgendaFromCron( $notification, $codes, $staff_email, $staff_phone );
                        }

                        if ( $sent ) {
                            Lib\Notifications\Sender::wasSent( $notification, $staff_id );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Entities\Notification $notification
     * @return string
     */
    private static function getQueryIfNotificationWasSent( Entities\Notification $notification )
    {
        return sprintf('
                SELECT * FROM `%s` `sn` 
                WHERE `sn`.`ref_id` = `ca`.`id`
                  AND `sn`.`notification_id` = %d
            ',
            Entities\SentNotification::getTableName(),
            $notification->getId()
        );
    }

    /**
     * @param int $at_hour
     * @return bool
     */
    private static function isTimeToSend( $at_hour )
    {
        $range = Lib\Slots\Range::fromDates(
            sprintf( '%02d:00:00', $at_hour ),
            sprintf( '%02d:00:00 + %d hours', $at_hour, self::$processing_interval )
        );

        return $range->contains( self::$date_point );
    }

    /**
     * Send notifications.
     */
    public static function sendNotifications()
    {
        // Disable caching.
        Lib\Utils\Common::noCache();

        date_default_timezone_set( 'UTC' );

        self::$date_point  = Lib\Slots\DatePoint::now();
        self::$today       = Lib\Slots\DatePoint::fromStr( 'today' );
        self::$mysql_today = self::$today->format( 'Y-m-d' );
        self::$hours       = self::$date_point->format( 'H' );
        self::$sms         = new Lib\SMS();
        self::$processing_interval = (int) get_option( 'bookly_ntf_processing_interval' );

        // Built in notifications.
        $built_in_notifications = Entities\Notification::query()
            ->where( 'active', 1 )
            ->whereIn( 'type', array( 'staff_agenda', 'client_follow_up', 'client_reminder', 'client_reminder_1st', 'client_reminder_2nd', 'client_reminder_3rd', 'client_birthday_greeting' ) )
            ->find();
        /** @var Entities\Notification $notification */
        foreach ( $built_in_notifications as $notification ) {
            if ( Lib\Config::proActive() || in_array( $notification->getType(), Lib\Entities\Notification::$bookly_notifications[ $notification->getGateway() ] ) ) {
                self::processBuiltInNotification( $notification );
            }
        }

        // Custom notifications.
        $custom_notifications = Entities\Notification::query()
            ->where( 'active', 1 )
            ->whereIn( 'type', Entities\Notification::getCustomNotificationTypes() )
            ->find();

        foreach ( $custom_notifications as $notification ) {
            if ( Lib\Config::proActive() || $notification->getGateway() == 'sms' ) {
                self::processCustomNotification( $notification );
            }
        }
    }
}