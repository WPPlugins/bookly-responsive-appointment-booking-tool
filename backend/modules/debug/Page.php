<?php
namespace Bookly\Backend\Modules\Debug;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Debug
 */
class Page extends Lib\Base\Ajax
{
    const TABLE_STATUS_OK      = 1;
    const TABLE_STATUS_ERROR   = 0;
    const TABLE_STATUS_WARNING = 2;

    /**
     * Render page.
     */
    public static function render()
    {
        /** @var \wpdb $wpdb*/
        global $wpdb;

        self::enqueueStyles( array(
            'frontend' => array( 'css/ladda.min.css', ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'module'   => array( 'css/style.css' ),
        ) );

        self::enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/spin.min.js'  => array( 'jquery', ),
                'js/ladda.min.js' => array( 'jquery', ),
            ),
            'module'  => array( 'js/debug.js' => array( 'jquery' ) ),
        ) );

        $debug = array();
        /** @var Lib\Base\Plugin $plugin */
        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin ) {
            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $tableName = $entity_class::getTableName();
                $debug[ $tableName ] = array(
                    'fields'      => null,
                    'constraints' => null,
                    'status'      => null,
                );
                if ( self::_tableExists( $tableName ) ) {
                    $tableStructure     = self::_getTableStructure( $tableName );
                    $tableConstraints   = self::_getTableConstraints( $tableName );
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
        }

        wp_localize_script( 'bookly-debug.js', 'BooklyL10n', array(
            'csrfToken'      => Lib\Utils\Common::getCsrfToken(),
            'charsetCollate' => $wpdb->has_cap( 'collation' )
                ? $wpdb->get_charset_collate()
                : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci'
        ) );

        ksort( $debug );
        $import_status = self::parameter( 'status' );
        self::renderTemplate( 'index', compact( 'debug', 'import_status' ) );
    }

    /**
     * Get table structure
     *
     * @param string $tableName
     * @return array
     */
    protected static function _getTableStructure( $tableName )
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
    protected static function _getTableConstraints( $tableName )
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
    protected static function _tableExists( $tableName )
    {
        global $wpdb;

        return $wpdb->query( 'SHOW TABLES LIKE "' . $tableName . '"' );
    }
}