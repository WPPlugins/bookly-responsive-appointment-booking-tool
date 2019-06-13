<?php
namespace Bookly\Lib\Base;

/**
 * Class Schema
 * @package Bookly\Lib\Base
 */
abstract class Schema
{
    /**
     * Drop foreign keys.
     *
     * @param array $tables
     */
    protected function dropForeignKeys( array $tables )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $query_foreign_keys = sprintf(
            'SELECT table_name, constraint_name FROM information_schema.key_column_usage
                WHERE REFERENCED_TABLE_SCHEMA = SCHEMA() AND REFERENCED_TABLE_NAME IN (%s)',
            implode( ', ', array_fill( 0, count( $tables ), '%s' ) )
        );

        $schema = $wpdb->get_results( $wpdb->prepare( $query_foreign_keys, $tables ) );
        foreach ( $schema as $foreign_key )
        {
            $wpdb->query( "ALTER TABLE `$foreign_key->table_name` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
    }

    /**
     * Drop tables.
     *
     * @param array $tables
     */
    protected function drop( array $tables )
    {
        global $wpdb;

        $this->dropForeignKeys( $tables );

        $wpdb->query( 'DROP TABLE IF EXISTS `' . implode( '`, `', $tables ) . '` CASCADE;' );
    }

    /**
     * Drop table columns.
     *
     * @param $table
     * @param array $columns
     */
    protected function dropTableColumns( $table, array $columns )
    {
        global $wpdb;

        $this->dropTableForeignKeys( $table, $columns );

        foreach ( $columns as $column ) {
            $wpdb->query( "ALTER TABLE `$table` DROP COLUMN `$column`" );
        }
    }

    /**
     * Drop table foreign keys.
     *
     * @param $table
     * @param array $columns
     */
    protected function dropTableForeignKeys( $table, array $columns )
    {
        global $wpdb;

        $get_foreign_keys = sprintf(
            'SELECT constraint_name FROM information_schema.key_column_usage
                WHERE TABLE_SCHEMA = SCHEMA() AND table_name = "%s" AND column_name IN (%s) AND REFERENCED_TABLE_NAME IS NOT NULL',
            $table,
            implode( ', ', array_fill( 0, count( $columns ), '%s' ) )
        );
        $constraints = $wpdb->get_results( $wpdb->prepare( $get_foreign_keys, $columns ) );
        foreach ( $constraints as $foreign_key ) {
            $wpdb->query( "ALTER TABLE `$table` DROP FOREIGN KEY `$foreign_key->constraint_name`" );
        }
    }

    /**
     * Get list of permitted values.
     *
     * @param $table
     * @param $column_name
     * @return string   Like 'value1','value2'
     */
    protected function getEnumString( $table, $column_name )
    {
        global $wpdb;

        $get_enum = $wpdb->prepare(
            'SELECT SUBSTRING(COLUMN_TYPE,5) FROM information_schema.COLUMNS
                WHERE TABLE_NAME = %s AND COLUMN_NAME = %s AND DATA_TYPE = "enum" AND TABLE_SCHEMA = SCHEMA()',
            $table,
            $column_name
        );

        return trim ( $wpdb->get_var( $get_enum ), '()' );
    }

    /**
     * Count of affected rows
     *
     * @return int|null
     */
    public static function getAffectedRows()
    {
        global $wpdb;

        return $wpdb->rows_affected;
    }

    /**
     * Get table name.
     *
     * @param string $table
     * @return string
     */
    protected function getTableName( $table )
    {
        global $wpdb;

        return $wpdb->prefix . $table;
    }

    /**
     * Check table exists
     *
     * @param $table
     *
     * @return bool
     */
    protected function tableExists( $table )
    {
        global $wpdb;

        return (bool) $wpdb->query( $wpdb->prepare(
            'SELECT 1 FROM `information_schema`.`tables` WHERE TABLE_NAME = %s AND TABLE_SCHEMA = SCHEMA() LIMIT 1',
            $this->getTableName( $table )
        ) );
    }

    /**
     * Check exists column in table
     *
     * @param string $table
     * @param string $column_name
     * @return bool
     */
    protected function existsColumn( $table, $column_name )
    {
        global $wpdb;

        return (bool) $wpdb->query( $wpdb->prepare( 'SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_NAME = %s AND COLUMN_NAME = %s AND TABLE_SCHEMA = SCHEMA() LIMIT 1',
            $this->getTableName( $table ),
            $column_name
        ) );
    }
}