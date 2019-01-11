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