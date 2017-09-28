<?php
namespace BooklyLite\Backend\Modules\Debug;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Debug
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-debug';

    const TABLE_STATUS_OK      = 1;
    const TABLE_STATUS_ERROR   = 0;
    const TABLE_STATUS_WARNING = 2;

    /**
     * Default action
     */
    public function index()
    {
        $this->enqueueStyles( array(
            'backend' => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'module'  => array( 'css/style.css' ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array( 'bootstrap/js/bootstrap.min.js' => array( 'jquery' ) ),
            'module'  => array( 'js/debug.js' => array( 'jquery' ) ),
        ) );

        $debug = array();
        foreach ( Lib\Plugin::getEntityClasses() as $entity_class ) {
            $tableName = $entity_class::getTableName();
            $debug[ $tableName ] = array(
                'fields'      => null,
                'constraints' => null,
                'status'      => null,
            );
            if ( $this->_tableExists( $tableName ) ) {
                $tableStructure     = $this->_getTableStructure( $tableName );
                $tableConstraints   = $this->_getTableConstraints( $tableName );
                $entitySchema       = $entity_class::getSchema();
                $entityConstraints  = $entity_class::getConstraints();
                $debug[ $tableName ]['status'] = self::TABLE_STATUS_OK;
                $debug[ $tableName ]['fields'] = array();

                // Comparing model schema with real DB schema
                foreach ( $entitySchema as $field => $data ) {
                    if ( in_array( $field, $tableStructure ) ) {
                        $debug[ $tableName ]['fields'][ $field ] = 1;
                    } else {
                        $debug[ $tableName ]['fields'][ $field ] = 0;
                        $debug[ $tableName ]['status'] = self::TABLE_STATUS_WARNING;
                    }
                }

                // Comparing model constraints with real DB constraints
                foreach ( $entityConstraints as $constraint ) {
                    $key = $constraint['column_name'] . $constraint['referenced_table_name'] . $constraint['referenced_column_name'];
                    $debug[ $tableName ]['constraints'][ $key ] = $constraint;
                    if ( array_key_exists ( $key, $tableConstraints ) ) {
                        $debug[ $tableName ]['constraints'][ $key ]['status'] = 1;
                    } else {
                        $debug[ $tableName ]['constraints'][ $key ]['status'] = 0;
                        $debug[ $tableName ]['status'] = self::TABLE_STATUS_WARNING;
                    }
                }

            } else {
                $debug[ $tableName ]['status'] = self::TABLE_STATUS_ERROR;
            }
        }

        $import_status = $this->getParameter( 'status' );
        $this->render( 'index', compact( 'debug', 'import_status' ) );
    }

    /**
     * Export database data.
     */
    public function executeExportData()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $result = array();

        $installer_class =  Lib\Plugin::getRootNamespace() . '\Lib\Installer';
        /** @var Lib\Base\Installer $installer */
        $installer = new $installer_class();

        foreach (  Lib\Plugin::getEntityClasses() as $entity_class ) {
            $table_name = $entity_class::getTableName();
            $result['entities'][ $entity_class ] = array(
                'fields' => $this->_getTableStructure( $table_name ),
                'values' => $wpdb->get_results( 'SELECT * FROM ' . $table_name, ARRAY_N )
            );
        }
        $plugin_prefix   =  Lib\Plugin::getPrefix();
        $options_postfix = array( 'data_loaded', 'grace_start', 'db_version', 'installation_time' );
        if ( $plugin_prefix != 'bookly_' ) {
            $options_postfix[] = 'enabled';
        }
        foreach ( $options_postfix as $option ) {
            $option_name = $plugin_prefix . $option;
            $result['options'][ $option_name ] = get_option( $option_name );
        }

        $result['options'][  Lib\Plugin::getPurchaseCodeOption() ] =  Lib\Plugin::getPurchaseCode();
        foreach ( $installer->getOptions() as $option_name => $option_value ) {
            $result['options'][ $option_name ] = get_option( $option_name );
        }

        header( 'Content-type: application/json' );
        header( 'Content-Disposition: attachment; filename=bookly_db_export_' . date( 'YmdHis' ) . '.json' );
        echo json_encode( $result );

        exit ( 0 );
    }

    /**
     * Import database data.
     */
    public function executeImportData()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if ( $file = $_FILES['import']['name'] ) {
            $json = file_get_contents( $_FILES['import']['tmp_name'] );
            if ( $json !== false ) {
                $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

                $data = json_decode( $json, true );

                $installer_class = Lib\Plugin::getRootNamespace() . '\Lib\Installer';
                /** @var Lib\Base\Installer $installer */
                $installer = new $installer_class();

                // Drop all data and options.
                $installer->removeData();
                $installer->dropTables();
                $installer->createTables();

                // Insert tables data.
                foreach ( Lib\Plugin::getEntityClasses() as $entity_class ) {
                    if ( isset ( $data['entities'][ $entity_class ]['values'][0] ) ) {
                        $table_name   = $entity_class::getTableName();
                        $query        = sprintf(
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

                $plugin_prefix   = Lib\Plugin::getPrefix();
                $options_postfix = array( 'data_loaded', 'grace_start', 'db_version' );
                if ( $plugin_prefix != 'bookly_' ) {
                    $options_postfix[] = 'enabled';
                }
                foreach ( $options_postfix as $option ) {
                    $option_name = $plugin_prefix . $option;
                    add_option( $option_name, $data['options'][ $option_name ] );
                }
            }

            header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug&status=imported' ) );
        }

        header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug' ) );

        exit ( 0 );
    }

    /**
     * Get table structure
     *
     * @param string $tableName
     * @return array
     */
    private function _getTableStructure( $tableName )
    {
        global $wpdb;

        $tableStructure = array();
        $results = $wpdb->get_results( 'DESCRIBE `' . $tableName . '`;' );
        if ( $results ) {
            foreach ( $results as $row ) {
                $tableStructure[] = $row->Field;
            }
        }

        return $tableStructure;
    }

    /**
     * Get table constraints
     *
     * @param string $tableName
     * @return array
     */
    private function _getTableConstraints( $tableName )
    {
        global $wpdb;

        $tableConstraints = array();
        $results = $wpdb->get_results(
            'SELECT
                 COLUMN_NAME,
                 CONSTRAINT_NAME,
                 REFERENCED_COLUMN_NAME,
                 REFERENCED_TABLE_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE
              TABLE_NAME = "' . $tableName . '"
              AND CONSTRAINT_SCHEMA = SCHEMA()
              AND CONSTRAINT_NAME <> "PRIMARY";'
        );
        if ( $results ) {
            foreach ( $results as $row ) {
                $constraint = array(
                    'column_name'            => $row->COLUMN_NAME,
                    'referenced_table_name'  => $row->REFERENCED_COLUMN_NAME,
                    'referenced_column_name' => $row->REFERENCED_TABLE_NAME,
                );
                $key = $row->COLUMN_NAME . $row->REFERENCED_TABLE_NAME . $row->REFERENCED_COLUMN_NAME;
                $tableConstraints[ $key ] = $constraint;
            }
        }

        return $tableConstraints;
    }

    /**
     * Verifying if table exists
     *
     * @param string $tableName
     * @return int
     */
    private function _tableExists( $tableName )
    {
        global $wpdb;

        return $wpdb->query( 'SHOW TABLES LIKE "' . $tableName . '"' );
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