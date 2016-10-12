<?php
namespace BooklyLite\Backend\Modules\Debug;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Debug
 */
class Controller extends Lib\Base\Controller
{

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
        /** @var Lib\Base\Entity $entity */
        foreach ( self::getEntities() as $entity ) {
            $tableName = $entity::getTableName();
            $debug[ $tableName ] = array(
                'fields'      => null,
                'constraints' => null,
                'status'      => null,
            );
            if ( self::tableExists( $tableName ) ) {
                $tableStructure     = self::getTableStructure( $tableName );
                $tableConstraints   = self::getTableConstraints( $tableName );
                $entitySchema       = $entity::getSchema();
                $entityConstraints  = $entity::getConstraints();
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
        $this->render( 'index', compact( 'debug', 'sql' ) );
    }

    /**
     * Get entity class names.
     *
     * @return array
     */
    public function getEntities()
    {
        $result = array();
        foreach ( scandir( Lib\Plugin::getDirectory() . '/lib/entities' ) as $filename ) {
            if ( $filename == '.' || $filename == '..' ) {
                continue;
            }
            $result[] = '\\BooklyLite\\Lib\\Entities\\' . basename( $filename, '.php' );
        }

        return apply_filters( 'bookly_tables', $result );
    }

    /**
     * Get table structure
     *
     * @param string $tableName
     * @return array
     */
    public static function getTableStructure( $tableName )
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
    public static function getTableConstraints( $tableName )
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
    public static function tableExists( $tableName )
    {
        global $wpdb;

        return $wpdb->query( 'SHOW TABLES LIKE "' . $tableName . '"' );
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}