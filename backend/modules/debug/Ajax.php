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
}