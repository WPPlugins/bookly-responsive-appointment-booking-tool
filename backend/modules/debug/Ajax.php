<?php
namespace Bookly\Backend\Modules\Debug;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Debug
 */
class Ajax extends Page
{
    /**
     * Export database data.
     */
    public static function exportData()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $result = array();

        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin ) {
            /** @var Lib\Base\Plugin $plugin */
            $installer_class = $plugin::getRootNamespace() . '\Lib\Installer';
            /** @var Lib\Base\Installer $installer */
            $installer = new $installer_class();

            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $table_name = $entity_class::getTableName();
                $result['entities'][ $entity_class ] = array(
                    'fields' => self::_getTableStructure( $table_name ),
                    'values' => $wpdb->get_results( 'SELECT * FROM ' . $table_name, ARRAY_N )
                );
            }
            $plugin_prefix   = $plugin::getPrefix();
            $options_postfix = array( 'data_loaded', 'grace_start', 'db_version', 'installation_time' );
            foreach ( $options_postfix as $option ) {
                $option_name = $plugin_prefix . $option;
                $result['options'][ $option_name ] = get_option( $option_name );
            }

            $result['options'][ $plugin::getPurchaseCodeOption() ] = $plugin::getPurchaseCode();
            foreach ( $installer->getOptions() as $option_name => $option_value ) {
                $result['options'][ $option_name ] = get_option( $option_name );
            }
        }

        header( 'Content-type: application/json' );
        header( 'Content-Disposition: attachment; filename=bookly_db_export_' . date( 'YmdHis' ) . '.json' );
        echo json_encode( $result );

        exit ( 0 );
    }

    /**
     * Import database data.
     */
    public static function importData()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if ( $file = $_FILES['import']['name'] ) {
            $json = file_get_contents( $_FILES['import']['tmp_name'] );
            if ( $json !== false) {
                $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

                $data = json_decode( $json, true );
                /** @var Lib\Base\Plugin[] $bookly_plugins */
                $bookly_plugins = apply_filters( 'bookly_plugins', array() );
                foreach ( array_merge( array( 'bookly-responsive-appointment-booking-tool', 'bookly-addon-pro' ), array_keys( $bookly_plugins ) ) as $slug ) {
                    if ( ! array_key_exists( $slug, $bookly_plugins ) ) {
                        continue;
                    }
                    /** @var Lib\Base\Plugin $plugin */
                    $plugin = $bookly_plugins[ $slug ];
                    unset( $bookly_plugins[ $slug ] );
                    $installer_class = $plugin::getRootNamespace() . '\Lib\Installer';
                    /** @var Lib\Base\Installer $installer */
                    $installer = new $installer_class();

                    // Drop all data and options.
                    $installer->removeData();
                    $installer->dropTables();
                    $installer->createTables();

                    // Insert tables data.
                    foreach ( $plugin::getEntityClasses() as $entity_class ) {
                        if ( isset ( $data['entities'][ $entity_class ]['values'][0] ) ) {
                            $table_name = $entity_class::getTableName();
                            $query = sprintf(
                                'INSERT INTO `%s` (`%s`) VALUES (%%s)',
                                $table_name,
                                implode( '`,`', $data['entities'][ $entity_class ]['fields'] )
                            );
                            $placeholders = array();
                            $values       = array();
                            $counter      = 0;
                            foreach ( $data['entities'][ $entity_class ]['values'] as $row ) {
                                $params = array();
                                foreach ( $row as $value ) {
                                    if ( $value === null ) {
                                        $params[] = 'NULL';
                                    } else {
                                        $params[] = '%s';
                                        $values[] = $value;
                                    }
                                }
                                $placeholders[] = implode( ',', $params );
                                if ( ++ $counter > 50 ) {
                                    // Flush.
                                    $wpdb->query( $wpdb->prepare( sprintf( $query, implode( '),(', $placeholders ) ), $values ) );
                                    $placeholders = array();
                                    $values       = array();
                                    $counter      = 0;
                                }
                            }
                            if ( ! empty ( $placeholders ) ) {
                                $wpdb->query( $wpdb->prepare( sprintf( $query, implode( '),(', $placeholders ) ), $values ) );
                            }
                        }
                    }

                    // Insert options data.
                    foreach ( $installer->getOptions() as $option_name => $option_value ) {
                        add_option( $option_name, $data['options'][ $option_name ] );
                    }

                    $plugin_prefix   = $plugin::getPrefix();
                    $options_postfix = array( 'data_loaded', 'grace_start', 'db_version' );
                    foreach ( $options_postfix as $option ) {
                        $option_name = $plugin_prefix . $option;
                        add_option( $option_name, $data['options'][ $option_name ] );
                    }
                }

                header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug&status=imported' ) );
            }
        }

        header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug' ) );

        exit ( 0 );
    }

    public static function getFieldData()
    {
        /** @global \wpdb */
        global $wpdb;

        $table      = self::parameter( 'table' );
        $column     = self::parameter( 'column' );

        /** SELECT CONCAT ( '\'', CONCAT_WS( '.', SUBSTR(TABLE_NAME,4), COLUMN_NAME ), '\' => "' , COLUMN_TYPE, ' ', IF(IS_NULLABLE = 'YES','null', 'not null') ,
                IF ( EXTRA = 'auto_increment', ' auto_increment primary key',
            CONCAT ( IF (COLUMN_DEFAULT is NULL, IF(IS_NULLABLE = 'NO', '', ' default null' ), CONCAT(' default \'',COLUMN_DEFAULT, '\'')))) , '",') AS data
              FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = SCHEMA()
               AND TABLE_NAME LIKE 'wp_bookly_%'
          ORDER BY TABLE_NAME, COLUMN_NAME
         */

        $fields = array(
            'bookly_appointments.created'                               => "datetime not null",
            'bookly_appointments.created_from'                          => "enum('bookly','google','outlook') not null default 'bookly'",
            'bookly_appointments.custom_service_name'                   => "varchar(255) null default null",
            'bookly_appointments.custom_service_price'                  => "decimal(10,2) null default null",
            'bookly_appointments.end_date'                              => "datetime null default null",
            'bookly_appointments.extras_duration'                       => "int(11) not null default '0'",
            'bookly_appointments.google_event_etag'                     => "varchar(255) null default null",
            'bookly_appointments.google_event_id'                       => "varchar(255) null default null",
            'bookly_appointments.id'                                    => "int(10) unsigned not null auto_increment primary key",
            'bookly_appointments.internal_note'                         => "text null default null",
            'bookly_appointments.location_id'                           => "int(10) unsigned null default null",
            'bookly_appointments.outlook_event_change_key'              => "varchar(255) null default null",
            'bookly_appointments.outlook_event_id'                      => "varchar(255) null default null",
            'bookly_appointments.outlook_event_series_id'               => "varchar(255) null default null",
            'bookly_appointments.service_id'                            => "int(10) unsigned null default null",
            'bookly_appointments.staff_any'                             => "tinyint(1) not null default '0'",
            'bookly_appointments.staff_id'                              => "int(10) unsigned not null",
            'bookly_appointments.start_date'                            => "datetime null default null",
            'bookly_categories.id'                                      => "int(10) unsigned not null auto_increment primary key",
            'bookly_categories.name'                                    => "varchar(255) not null",
            'bookly_categories.position'                                => "int(11) not null default '9999'",
            'bookly_coupons.code'                                       => "varchar(255) not null default ''",
            'bookly_coupons.date_limit_end'                             => "date null default null",
            'bookly_coupons.date_limit_start'                           => "date null default null",
            'bookly_coupons.deduction'                                  => "decimal(10,2) not null default '0.00'",
            'bookly_coupons.discount'                                   => "decimal(3,0) not null default '0'",
            'bookly_coupons.id'                                         => "int(10) unsigned not null auto_increment primary key",
            'bookly_coupons.max_appointments'                           => "int(10) unsigned null default null",
            'bookly_coupons.min_appointments'                           => "int(10) unsigned not null default '1'",
            'bookly_coupons.once_per_customer'                          => "tinyint(1) not null default '0'",
            'bookly_coupons.usage_limit'                                => "int(10) unsigned not null default '1'",
            'bookly_coupons.used'                                       => "int(10) unsigned not null default '0'",
            'bookly_coupon_customers.coupon_id'                         => "int(10) unsigned not null",
            'bookly_coupon_customers.customer_id'                       => "int(10) unsigned not null",
            'bookly_coupon_customers.id'                                => "int(10) unsigned not null auto_increment primary key",
            'bookly_coupon_services.coupon_id'                          => "int(10) unsigned not null",
            'bookly_coupon_services.id'                                 => "int(10) unsigned not null auto_increment primary key",
            'bookly_coupon_services.service_id'                         => "int(10) unsigned not null",
            'bookly_coupon_staff.coupon_id'                             => "int(10) unsigned not null",
            'bookly_coupon_staff.id'                                    => "int(10) unsigned not null auto_increment primary key",
            'bookly_coupon_staff.staff_id'                              => "int(10) unsigned not null",
            'bookly_customers.additional_address'                       => "varchar(255) null default null",
            'bookly_customers.birthday'                                 => "date null default null",
            'bookly_customers.city'                                     => "varchar(255) null default null",
            'bookly_customers.country'                                  => "varchar(255) null default null",
            'bookly_customers.created'                                  => "datetime not null",
            'bookly_customers.email'                                    => "varchar(255) not null default ''",
            'bookly_customers.facebook_id'                              => "bigint(20) unsigned null default null",
            'bookly_customers.first_name'                               => "varchar(255) not null default ''",
            'bookly_customers.full_name'                                => "varchar(255) not null default ''",
            'bookly_customers.group_id'                                 => "int(10) unsigned null default null",
            'bookly_customers.id'                                       => "int(10) unsigned not null auto_increment primary key",
            'bookly_customers.info_fields'                              => "text null default null",
            'bookly_customers.last_name'                                => "varchar(255) not null default ''",
            'bookly_customers.notes'                                    => "text not null",
            'bookly_customers.phone'                                    => "varchar(255) not null default ''",
            'bookly_customers.postcode'                                 => "varchar(255) null default null",
            'bookly_customers.state'                                    => "varchar(255) null default null",
            'bookly_customers.street'                                   => "varchar(255) null default null",
            'bookly_customers.street_number'                            => "varchar(255) null default null",
            'bookly_customers.wp_user_id'                               => "bigint(20) unsigned null default null",
            'bookly_customer_appointments.appointment_id'               => "int(10) unsigned not null",
            'bookly_customer_appointments.collaborative_service_id'     => "int(10) unsigned null default null",
            'bookly_customer_appointments.collaborative_token'          => "varchar(255) null default null",
            'bookly_customer_appointments.compound_service_id'          => "int(10) unsigned null default null",
            'bookly_customer_appointments.compound_token'               => "varchar(255) null default null",
            'bookly_customer_appointments.created'                      => "datetime not null",
            'bookly_customer_appointments.created_from'                 => "enum('frontend','backend') not null default 'frontend'",
            'bookly_customer_appointments.customer_id'                  => "int(10) unsigned not null",
            'bookly_customer_appointments.custom_fields'                => "text null default null",
            'bookly_customer_appointments.extras'                       => "text null default null",
            'bookly_customer_appointments.extras_consider_duration'     => "tinyint(1) not null default '1'",
            'bookly_customer_appointments.extras_multiply_nop'          => "tinyint(1) not null default '1'",
            'bookly_customer_appointments.id'                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_customer_appointments.locale'                       => "varchar(8) null default null",
            'bookly_customer_appointments.notes'                        => "text null default null",
            'bookly_customer_appointments.number_of_persons'            => "int(10) unsigned not null default '1'",
            'bookly_customer_appointments.package_id'                   => "int(10) unsigned null default null",
            'bookly_customer_appointments.payment_id'                   => "int(10) unsigned null default null",
            'bookly_customer_appointments.rating'                       => "int(11) null default null",
            'bookly_customer_appointments.rating_comment'               => "text null default null",
            'bookly_customer_appointments.series_id'                    => "int(10) unsigned null default null",
            'bookly_customer_appointments.status'                       => "varchar(255) not null default 'approved'",
            'bookly_customer_appointments.status_changed_at'            => "datetime null default null",
            'bookly_customer_appointments.time_zone'                    => "varchar(255) null default null",
            'bookly_customer_appointments.time_zone_offset'             => "int(11) null default null",
            'bookly_customer_appointments.token'                        => "varchar(255) null default null",
            'bookly_customer_appointments.units'                        => "int(10) unsigned not null default '1'",
            'bookly_customer_appointment_files.customer_appointment_id' => "int(10) unsigned not null",
            'bookly_customer_appointment_files.file_id'                 => "int(10) unsigned not null",
            'bookly_customer_appointment_files.id'                      => "int(10) unsigned not null auto_increment primary key",
            'bookly_customer_groups.appointment_status'                 => "varchar(255) not null default ''",
            'bookly_customer_groups.description'                        => "text not null",
            'bookly_customer_groups.discount'                           => "varchar(100) not null default '0'",
            'bookly_customer_groups.id'                                 => "int(10) unsigned not null auto_increment primary key",
            'bookly_customer_groups.name'                               => "varchar(255) not null",
            'bookly_customer_groups_services.group_id'                  => "int(10) unsigned not null",
            'bookly_customer_groups_services.id'                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_customer_groups_services.service_id'                => "int(10) unsigned not null",
            'bookly_custom_statuses.busy'                               => "tinyint(1) not null default '1'",
            'bookly_custom_statuses.id'                                 => "int(10) unsigned not null auto_increment primary key",
            'bookly_custom_statuses.name'                               => "varchar(255) null default null",
            'bookly_custom_statuses.position'                           => "int(11) not null default '9999'",
            'bookly_custom_statuses.slug'                               => "varchar(255) not null",
            'bookly_files.custom_field_id'                              => "int(11) null default null",
            'bookly_files.id'                                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_files.name'                                         => "text not null",
            'bookly_files.path'                                         => "text not null",
            'bookly_files.slug'                                         => "varchar(32) not null",
            'bookly_holidays.date'                                      => "date not null",
            'bookly_holidays.id'                                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_holidays.parent_id'                                 => "int(10) unsigned null default null",
            'bookly_holidays.repeat_event'                              => "tinyint(1) not null default '0'",
            'bookly_holidays.staff_id'                                  => "int(10) unsigned null default null",
            'bookly_locations.id'                                       => "int(10) unsigned not null auto_increment primary key",
            'bookly_locations.info'                                     => "text null default null",
            'bookly_locations.name'                                     => "varchar(255) null default ''",
            'bookly_locations.position'                                 => "int(11) not null default '9999'",
            'bookly_messages.body'                                      => "text null default null",
            'bookly_messages.created'                                   => "datetime not null",
            'bookly_messages.id'                                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_messages.message_id'                                => "int(10) unsigned not null",
            'bookly_messages.seen'                                      => "tinyint(1) not null default '0'",
            'bookly_messages.subject'                                   => "text null default null",
            'bookly_messages.type'                                      => "varchar(255) not null",
            'bookly_notifications.active'                               => "tinyint(1) not null default '0'",
            'bookly_notifications.attach_ics'                           => "tinyint(1) not null default '0'",
            'bookly_notifications.attach_invoice'                       => "tinyint(1) not null default '0'",
            'bookly_notifications.gateway'                              => "enum('email','sms') not null default 'email'",
            'bookly_notifications.id'                                   => "int(10) unsigned not null auto_increment primary key",
            'bookly_notifications.message'                              => "text null default null",
            'bookly_notifications.name'                                 => "varchar(255) not null default ''",
            'bookly_notifications.settings'                             => "text null default null",
            'bookly_notifications.subject'                              => "varchar(255) not null default ''",
            'bookly_notifications.to_admin'                             => "tinyint(1) not null default '0'",
            'bookly_notifications.to_customer'                          => "tinyint(1) not null default '0'",
            'bookly_notifications.to_staff'                             => "tinyint(1) not null default '0'",
            'bookly_notifications.type'                                 => "varchar(255) not null default ''",
            'bookly_packages.created'                                   => "datetime not null",
            'bookly_packages.customer_id'                               => "int(10) unsigned not null",
            'bookly_packages.id'                                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_packages.internal_note'                             => "text null default null",
            'bookly_packages.location_id'                               => "int(10) unsigned null default null",
            'bookly_packages.service_id'                                => "int(10) unsigned not null",
            'bookly_packages.staff_id'                                  => "int(10) unsigned null default null",
            'bookly_payments.coupon_id'                                 => "int(10) unsigned null default null",
            'bookly_payments.created'                                   => "datetime not null",
            'bookly_payments.details'                                   => "text null default null",
            'bookly_payments.gateway_price_correction'                  => "decimal(10,2) null default '0.00'",
            'bookly_payments.id'                                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_payments.paid'                                      => "decimal(10,2) not null default '0.00'",
            'bookly_payments.paid_type'                                 => "enum('in_full','deposit') not null default 'in_full'",
            'bookly_payments.status'                                    => "enum('pending','completed','rejected') not null default 'completed'",
            'bookly_payments.tax'                                       => "decimal(10,2) not null default '0.00'",
            'bookly_payments.total'                                     => "decimal(10,2) not null default '0.00'",
            'bookly_payments.type'                                      => "enum('local','coupon','paypal','authorize_net','stripe','2checkout','payu_biz','payu_latam','payson','mollie','woocommerce') not null default 'local'",
            'bookly_schedule_item_breaks.end_time'                      => "time null default null",
            'bookly_schedule_item_breaks.id'                            => "int(10) unsigned not null auto_increment primary key",
            'bookly_schedule_item_breaks.staff_schedule_item_id'        => "int(10) unsigned not null",
            'bookly_schedule_item_breaks.start_time'                    => "time null default null",
            'bookly_sent_notifications.created'                         => "datetime not null",
            'bookly_sent_notifications.id'                              => "int(10) unsigned not null auto_increment primary key",
            'bookly_sent_notifications.notification_id'                 => "int(10) unsigned not null",
            'bookly_sent_notifications.ref_id'                          => "int(10) unsigned not null",
            'bookly_series.id'                                          => "int(10) unsigned not null auto_increment primary key",
            'bookly_series.repeat'                                      => "varchar(255) null default null",
            'bookly_series.token'                                       => "varchar(255) not null",
            'bookly_services.appointments_limit'                        => "int(11) null default null",
            'bookly_services.capacity_max'                              => "int(11) not null default '1'",
            'bookly_services.capacity_min'                              => "int(11) not null default '1'",
            'bookly_services.category_id'                               => "int(10) unsigned null default null",
            'bookly_services.collaborative_equal_duration'              => "tinyint(1) not null default '0'",
            'bookly_services.color'                                     => "varchar(255) not null default '#FFFFFF'",
            'bookly_services.deposit'                                   => "varchar(100) not null default '100%'",
            'bookly_services.duration'                                  => "int(11) not null default '900'",
            'bookly_services.end_time_info'                             => "varchar(255) null default ''",
            'bookly_services.id'                                        => "int(10) unsigned not null auto_increment primary key",
            'bookly_services.info'                                      => "text null default null",
            'bookly_services.limit_period'                              => "enum('off','day','week','month','year','upcoming','calendar_day','calendar_week','calendar_month','calendar_year') not null default 'off'",
            'bookly_services.one_booking_per_slot'                      => "tinyint(1) not null default '0'",
            'bookly_services.package_life_time'                         => "int(11) null default null",
            'bookly_services.package_size'                              => "int(11) null default null",
            'bookly_services.package_unassigned'                        => "tinyint(1) not null default '0'",
            'bookly_services.padding_left'                              => "int(11) not null default '0'",
            'bookly_services.padding_right'                             => "int(11) not null default '0'",
            'bookly_services.position'                                  => "int(11) not null default '9999'",
            'bookly_services.price'                                     => "decimal(10,2) not null default '0.00'",
            'bookly_services.recurrence_enabled'                        => "tinyint(1) not null default '1'",
            'bookly_services.recurrence_frequencies'                    => "set('daily','weekly','biweekly','monthly') not null default 'daily,weekly,biweekly,monthly'",
            'bookly_services.slot_length'                               => "varchar(255) not null default 'default'",
            'bookly_services.staff_preference'                          => "enum('order','least_occupied','most_occupied','least_occupied_for_period','most_occupied_for_period','least_expensive','most_expensive') not null default 'most_expensive'",
            'bookly_services.staff_preference_settings'                 => "text null default null",
            'bookly_services.start_time_info'                           => "varchar(255) null default ''",
            'bookly_services.time_requirements'                         => "enum('required','optional','off') not null default 'required'",
            'bookly_services.title'                                     => "varchar(255) null default ''",
            'bookly_services.type'                                      => "enum('simple','collaborative','compound','package') not null default 'simple'",
            'bookly_services.units_max'                                 => "int(10) unsigned not null default '1'",
            'bookly_services.units_min'                                 => "int(10) unsigned not null default '1'",
            'bookly_services.visibility'                                => "enum('public','private','group') not null default 'public'",
            'bookly_service_extras.attachment_id'                       => "int(10) unsigned null default null",
            'bookly_service_extras.duration'                            => "int(11) not null default '0'",
            'bookly_service_extras.id'                                  => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_extras.max_quantity'                        => "int(11) not null default '1'",
            'bookly_service_extras.position'                            => "int(11) not null default '9999'",
            'bookly_service_extras.price'                               => "decimal(10,2) not null default '0.00'",
            'bookly_service_extras.service_id'                          => "int(10) unsigned not null",
            'bookly_service_extras.title'                               => "varchar(255) null default ''",
            'bookly_service_schedule_breaks.end_time'                   => "time null default null",
            'bookly_service_schedule_breaks.id'                         => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_schedule_breaks.service_schedule_day_id'    => "int(10) unsigned not null",
            'bookly_service_schedule_breaks.start_time'                 => "time null default null",
            'bookly_service_schedule_days.day_index'                    => "smallint(6) null default null",
            'bookly_service_schedule_days.end_time'                     => "time null default null",
            'bookly_service_schedule_days.id'                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_schedule_days.service_id'                   => "int(10) unsigned not null",
            'bookly_service_schedule_days.start_time'                   => "time null default null",
            'bookly_service_special_days.date'                          => "date null default null",
            'bookly_service_special_days.end_time'                      => "time null default null",
            'bookly_service_special_days.id'                            => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_special_days.service_id'                    => "int(10) unsigned not null",
            'bookly_service_special_days.start_time'                    => "time null default null",
            'bookly_service_special_days_breaks.end_time'               => "time null default null",
            'bookly_service_special_days_breaks.id'                     => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_special_days_breaks.service_special_day_id' => "int(10) unsigned not null",
            'bookly_service_special_days_breaks.start_time'             => "time null default null",
            'bookly_service_taxes.id'                                   => "int(10) unsigned not null auto_increment primary key",
            'bookly_service_taxes.service_id'                           => "int(10) unsigned not null",
            'bookly_service_taxes.tax_id'                               => "int(10) unsigned not null",
            'bookly_shop.created'                                       => "datetime not null",
            'bookly_shop.demo_url'                                      => "varchar(255) null default null",
            'bookly_shop.description'                                   => "text not null",
            'bookly_shop.highlighted'                                   => "tinyint(1) not null default '0'",
            'bookly_shop.icon'                                          => "varchar(255) not null",
            'bookly_shop.id'                                            => "int(10) unsigned not null auto_increment primary key",
            'bookly_shop.plugin_id'                                     => "int(10) unsigned not null",
            'bookly_shop.price'                                         => "decimal(10,2) not null",
            'bookly_shop.priority'                                      => "int(10) unsigned null default '0'",
            'bookly_shop.published'                                     => "datetime not null",
            'bookly_shop.rating'                                        => "decimal(10,2) not null",
            'bookly_shop.reviews'                                       => "int(10) unsigned not null",
            'bookly_shop.sales'                                         => "int(10) unsigned not null",
            'bookly_shop.seen'                                          => "tinyint(1) not null default '0'",
            'bookly_shop.slug'                                          => "varchar(255) not null",
            'bookly_shop.title'                                         => "varchar(255) not null",
            'bookly_shop.type'                                          => "enum('plugin','bundle') not null default 'plugin'",
            'bookly_shop.url'                                           => "varchar(255) not null",
            'bookly_special_days_breaks.end_time'                       => "time null default null",
            'bookly_special_days_breaks.id'                             => "int(10) unsigned not null auto_increment primary key",
            'bookly_special_days_breaks.staff_special_day_id'           => "int(10) unsigned not null",
            'bookly_special_days_breaks.start_time'                     => "time null default null",
            'bookly_staff.attachment_id'                                => "int(10) unsigned null default null",
            'bookly_staff.category_id'                                  => "int(10) unsigned null default null",
            'bookly_staff.email'                                        => "varchar(255) null default null",
            'bookly_staff.full_name'                                    => "varchar(255) null default null",
            'bookly_staff.google_data'                                  => "text null default null",
            'bookly_staff.id'                                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff.info'                                         => "text null default null",
            'bookly_staff.outlook_data'                                 => "text null default null",
            'bookly_staff.phone'                                        => "varchar(255) null default null",
            'bookly_staff.position'                                     => "int(11) not null default '9999'",
            'bookly_staff.visibility'                                   => "enum('public','private','archive') not null default 'public'",
            'bookly_staff.working_time_limit'                           => "int(10) unsigned null default null",
            'bookly_staff.wp_user_id'                                   => "bigint(20) unsigned null default null",
            'bookly_staff_categories.id'                                => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_categories.name'                              => "varchar(255) not null",
            'bookly_staff_categories.position'                          => "int(11) not null default '9999'",
            'bookly_staff_locations.custom_schedule'                    => "tinyint(1) not null default '0'",
            'bookly_staff_locations.custom_services'                    => "tinyint(1) not null default '0'",
            'bookly_staff_locations.id'                                 => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_locations.location_id'                        => "int(10) unsigned not null",
            'bookly_staff_locations.staff_id'                           => "int(10) unsigned not null",
            'bookly_staff_preference_orders.id'                         => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_preference_orders.position'                   => "int(11) not null default '9999'",
            'bookly_staff_preference_orders.service_id'                 => "int(10) unsigned not null",
            'bookly_staff_preference_orders.staff_id'                   => "int(10) unsigned not null",
            'bookly_staff_schedule_items.day_index'                     => "int(10) unsigned not null",
            'bookly_staff_schedule_items.end_time'                      => "time null default null",
            'bookly_staff_schedule_items.id'                            => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_schedule_items.location_id'                   => "int(10) unsigned null default null",
            'bookly_staff_schedule_items.staff_id'                      => "int(10) unsigned not null",
            'bookly_staff_schedule_items.start_time'                    => "time null default null",
            'bookly_staff_services.capacity_max'                        => "int(11) not null default '1'",
            'bookly_staff_services.capacity_min'                        => "int(11) not null default '1'",
            'bookly_staff_services.deposit'                             => "varchar(100) not null default '100%'",
            'bookly_staff_services.id'                                  => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_services.location_id'                         => "int(10) unsigned null default null",
            'bookly_staff_services.price'                               => "decimal(10,2) not null default '0.00'",
            'bookly_staff_services.service_id'                          => "int(10) unsigned not null",
            'bookly_staff_services.staff_id'                            => "int(10) unsigned not null",
            'bookly_staff_special_days.date'                            => "date null default null",
            'bookly_staff_special_days.end_time'                        => "time null default null",
            'bookly_staff_special_days.id'                              => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_special_days.staff_id'                        => "int(10) unsigned not null",
            'bookly_staff_special_days.start_time'                      => "time null default null",
            'bookly_staff_special_hours.end_time'                       => "time null default null",
            'bookly_staff_special_hours.id'                             => "int(10) unsigned not null auto_increment primary key",
            'bookly_staff_special_hours.location_id'                    => "int(10) unsigned null default null",
            'bookly_staff_special_hours.price'                          => "decimal(10,2) not null default '0.00'",
            'bookly_staff_special_hours.service_id'                     => "int(10) unsigned not null",
            'bookly_staff_special_hours.staff_id'                       => "int(10) unsigned not null",
            'bookly_staff_special_hours.start_time'                     => "time null default null",
            'bookly_stats.created'                                      => "datetime not null",
            'bookly_stats.id'                                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_stats.name'                                         => "varchar(255) not null",
            'bookly_stats.value'                                        => "text null default null",
            'bookly_sub_services.duration'                              => "int(11) null default null",
            'bookly_sub_services.id'                                    => "int(10) unsigned not null auto_increment primary key",
            'bookly_sub_services.position'                              => "int(11) not null default '9999'",
            'bookly_sub_services.service_id'                            => "int(10) unsigned not null",
            'bookly_sub_services.sub_service_id'                        => "int(10) unsigned null default null",
            'bookly_sub_services.type'                                  => "enum('service','spare_time') not null default 'service'",
            'bookly_taxes.id'                                           => "int(10) unsigned not null auto_increment primary key",
            'bookly_taxes.rate'                                         => "decimal(10,3) not null default '0.000'",
            'bookly_taxes.title'                                        => "varchar(255) null default ''",
        );

        $prefix_len = strlen( $wpdb->prefix );
        $key        = substr( $table, $prefix_len ) . '.' . $column;
        if ( isset( $fields[ $key ] ) ) {
            wp_send_json_success( $fields[ $key ] );
        } else {
            wp_send_json_error();
        }
    }

    public static function executeQuery()
    {
        /** @global \wpdb */
        global $wpdb;

        ob_start();
        $result = $wpdb->query( self::parameter( 'query' ) );
        ob_end_clean();

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Query completed successfully' ) );
        } else {
            wp_send_json_error( array( 'message' => $wpdb->last_error ) );
        }
    }

    public static function getConstraintData()
    {
        /** @global \wpdb */
        global $wpdb;

        $table      = self::parameter( 'table' );
        $column     = self::parameter( 'column' );
        $ref_table  = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );
        /** SELECT CONCAT_WS( '.', SUBSTR(kcu.TABLE_NAME,4), kcu.COLUMN_NAME ) AS field
                 , CONCAT_WS( '.', SUBSTR(kcu.REFERENCED_TABLE_NAME,4), kcu.REFERENCED_COLUMN_NAME ) AS ref
                 , rc.UPDATE_RULE
                 , rc.DELETE_RULE
             FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS rc
        LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS kcu ON ( rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME )
            WHERE unique_CONSTRAINT_SCHEMA = SCHEMA()
              AND rc.CONSTRAINT_NAME LIKE 'wp_bookly_%'
         GROUP BY rc.CONSTRAINT_NAME
         */

        $constaints = array (
            'bookly_appointments.location_id'                           => array( 'bookly_locations.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_appointments.service_id'                            => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_appointments.staff_id'                              => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_customers.coupon_id'                         => array( 'bookly_coupons.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_customers.customer_id'                       => array( 'bookly_customers.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_services.coupon_id'                          => array( 'bookly_coupons.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_services.service_id'                         => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_staff.coupon_id'                             => array( 'bookly_coupons.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_coupon_staff.staff_id'                              => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_appointment_files.customer_appointment_id' => array( 'bookly_customer_appointments.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_appointment_files.file_id'                 => array( 'bookly_files.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_appointments.appointment_id'               => array( 'bookly_appointments.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_appointments.customer_id'                  => array( 'bookly_customers.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_appointments.package_id'                   => array( 'bookly_packages.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_customer_appointments.payment_id'                   => array( 'bookly_payments.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_customer_appointments.series_id'                    => array( 'bookly_series.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_groups_services.group_id'                  => array( 'bookly_customer_groups.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customer_groups_services.service_id'                => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_customers.group_id'                                 => array( 'bookly_customer_groups.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_holidays.staff_id'                                  => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'RESTRICT', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_packages.customer_id'                               => array( 'bookly_customers.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_packages.service_id'                                => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_packages.staff_id'                                  => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_payments.coupon_id'                                 => array( 'bookly_coupons.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_schedule_item_breaks.staff_schedule_item_id'        => array( 'bookly_staff_schedule_items.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_sent_notifications.notification_id'                 => array( 'bookly_notifications.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_extras.service_id'                          => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_schedule_breaks.service_schedule_day_id'    => array( 'bookly_service_schedule_days.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_schedule_days.service_id'                   => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_special_days.service_id'                    => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_special_days_breaks.service_special_day_id' => array( 'bookly_service_special_days.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_taxes.service_id'                           => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_service_taxes.tax_id'                               => array( 'bookly_taxes.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_services.category_id'                               => array( 'bookly_categories.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_special_days_breaks.staff_special_day_id'           => array( 'bookly_staff_special_days.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff.category_id'                                  => array( 'bookly_staff_categories.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'SET NULL', ), ),
            'bookly_staff_locations.location_id'                        => array( 'bookly_locations.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_locations.staff_id'                           => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_preference_orders.service_id'                 => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_preference_orders.staff_id'                   => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_schedule_items.location_id'                   => array( 'bookly_locations.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_schedule_items.staff_id'                      => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_services.location_id'                         => array( 'bookly_locations.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_services.service_id'                          => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_services.staff_id'                            => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_special_days.staff_id'                        => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_special_hours.service_id'                     => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_staff_special_hours.staff_id'                       => array( 'bookly_staff.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_sub_services.service_id'                            => array( 'bookly_services.id' => array( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
            'bookly_sub_services.sub_service_id'                        => array( 'bookly_services.id' => array ( 'UPDATE_RULE' => 'CASCADE', 'DELETE_RULE' => 'CASCADE', ), ),
        );

        $prefix_len = strlen( $wpdb->prefix );
        $key        = substr( $table, $prefix_len ) . '.' . $column;
        $ref        = substr( $ref_table, $prefix_len ) . '.' . $ref_column;
        if ( isset( $constaints[ $key ][ $ref ] ) ) {
            wp_send_json_success( $constaints[ $key ][ $ref ] );
        } else {
            wp_send_json_error();
        }
    }

    public static function addConstraint()
    {
        /** @global \wpdb */
        global $wpdb;

        $table  = self::parameter( 'table' );
        $column = self::parameter( 'column' );
        $ref_table = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );

        $sql = sprintf( 'ALTER TABLE `%s` ADD CONSTRAINT FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)', $table, $column, $ref_table, $ref_column );
        $delete_rule = self::parameter( 'delete_rule' );
        switch ( $delete_rule ) {
            case 'RESTRICT':
            case 'CASCADE':
            case 'SET NULL':
            case 'NO ACTIONS':
                $sql .= ' ON DELETE ' . $delete_rule;
                break;
            default:
                wp_send_json_error( array( 'message' => 'Select ON DELETE action' ) );
        }
        $update_rule = self::parameter( 'update_rule' );
        switch ( $update_rule ) {
            case 'RESTRICT':
            case 'CASCADE':
            case 'SET NULL':
            case 'NO ACTIONS':
                $sql .= ' ON UPDATE ' . $update_rule;
                break;
            default:
                wp_send_json_error( array( 'message' => 'Select ON UPDATE action' ) );
        }

        ob_start();
        $result = $wpdb->query( $sql );
        ob_end_clean();

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Constraint created' ) );
        } else {
            wp_send_json_error( array( 'message' => $wpdb->last_error ) );
        }
    }

    public static function fixConsistency()
    {
        /** @global \wpdb */
        global $wpdb;

        $rule   = self::parameter( 'rule' );
        $table  = self::parameter( 'table' );
        $column = self::parameter( 'column' );
        $ref_table  = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );

        switch ( $rule ) {
            case 'CASCADE':
                $sql = sprintf( 'DELETE FROM `%s` WHERE `%s` NOT IN ( SELECT `%s` FROM `%s` )',
                    $table, $column, $ref_column, $ref_table );
                break;
            case 'SET NULL':
                $sql = sprintf( 'UPDATE TABLE `%s` SET `%s` = NULL WHERE `%s` NOT IN ( SELECT `%s` FROM `%s` )',
                    $table, $column, $column, $ref_column, $ref_table );
                break;
            default:
                wp_send_json_success( array( 'message' => 'No manipulation actions were performed' ) );
        }


        ob_start();
        $result = $wpdb->query( $sql );
        ob_end_clean();

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => 'Successful, click Add constraint' ) );
        } else {
            wp_send_json_error( array( 'message' => $wpdb->last_error ) );
        }
    }
}