<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Database entity.
 */
abstract class AB_Entity {

    /**
     * Reference to global database object.
     * @var wpdb
     */
    protected $wpdb;

    /**
     * Name of table in database without WordPress prefix.
     * Must be defined in the child class.
     * @static
     * @var string
     */
    protected static $table = null;

    /**
     * Schema of entity fields in database.
     * Must be defined in the child class as
     * array(
     *     '[FIELD_NAME]' => array(
     *         'format'  => '[FORMAT]',
     *         'default' => '[DEFAULT_VALUE]',
     *     )
     * )
     * @static
     * @var array
     */
    protected static $schema = null;

    // Private properties.

    /**
     * Name of table in database with WordPress prefix.
     * @var string
     */
    private $table_name = null;

    /**
     * Values of fields.
     * @var array
     */
    private $fields = array();

    /**
     * Values loaded from the database.
     * @var boolean
     */
    private $loaded_values = null;


    // Public methods.

    /**
     * Constructor
     *
     * @param array $fields
     */
    public function __construct( $fields = array() )
    {
        /** @var WPDB $wpdb */
        global $wpdb;

        // Reference to global database object.
        $this->wpdb = $wpdb;

        $this->table_name = self::getTableName();

        // Initialize field values.
        foreach ( static::$schema as $field_name => $options ) {
            $this->fields[ $field_name ]  = array_key_exists( 'default', $options ) ? $options[ 'default' ] : null;
        }

        $this->setFields( $fields );
    }

    /**
     * Set value to field.
     *
     * @param string $field
     * @param mixed $value
     */
    public function set( $field, $value )
    {
        $this->fields[ $field ] = $value;
    }

    /**
     * Get value of field.
     *
     * @param string $field
     * @return mixed
     */
    public function get( $field )
    {
        return $this->fields[ $field ];
    }

    /**
     * Magic set method.
     *
     * @param string $field
     * @param mixed $value
     */
    public function __set( $field, $value )
    {
        $this->set( $field, $value );
    }

    /**
     * Magic get method.
     *
     * @param string $field
     * @return mixed
     */
    public function __get( $field )
    {
        return $this->get( $field );
    }

    /**
     * Load entity from database by ID.
     *
     * @param integer $id
     * @return boolean
     */
    public function load( $id )
    {
        return $this->loadBy( array( 'id' => $id ) );
    }

    /**
     * Load entity from database by field values.
     *
     * @param array $fields
     * @return bool
     */
    public function loadBy( array $fields )
    {
        // Prepare WHERE clause.
        $where = array();
        $values = array();
        foreach ( $fields as $field => $value ) {
            if ( $value === null ) {
                $where[] = sprintf( '`%s` IS NULL', $field );
            } else {
                $where[] = sprintf( '`%s` = %s', $field, static::$schema[ $field ]['format'] );
                $values[] = $value;
            }
        }

        $query = sprintf(
            'SELECT * FROM `%s` WHERE %s LIMIT 1',
            $this->table_name,
            implode( ' AND ', $where )
        );

        $row = $this->wpdb->get_row(
            empty ( $values ) ? $query : $this->wpdb->prepare( $query, $values )
        );

        if ( $row ) {
            $this->setFields( $row );
            $this->loaded_values = $this->fields;
        } else {
            $this->loaded_values = null;
        }

        return $this->isLoaded();
    }

    /**
     * Check whether the entity was loaded from the database or not.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded_values !== null;
    }

    /**
     * Set values to fields.
     * The method can be used to update only some fields.
     *
     * @param array|object $data
     * @param bool $overwrite_loaded_values
     */
    public function setFields( $data, $overwrite_loaded_values = false )
    {
        if ( is_array( $data ) || $data instanceof stdClass ) {
            foreach ( $data as $field => $value ) {
                if ( array_key_exists( $field, $this->fields ) ) {
                    $this->fields[ $field ] = $value;
                }
            }

            // This parameter is used by AB_Query.
            if ( $overwrite_loaded_values ) {
                $this->loaded_values = $this->fields;
            }
        }
    }

    /**
     * Get values of fields as array.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get modified fields with initial values.
     *
     * @return array
     */
    public function getModified()
    {
        return array_diff_assoc( $this->loaded_values ?: array(), $this->fields );
    }

    /**
     * Save entity to database.
     *
     * @return int|false
     */
    public function save()
    {
        // Prepare query data.
        $set    = array();
        $values = array();
        foreach ( $this->fields as $field => $value ) {
            if ( $field == 'id' ) {
                continue;
            }
            if ( $value === null ) {
                $set[] = sprintf( '`%s` = NULL', $field );
            } else {
                $set[] = sprintf( '`%s` = %s', $field, static::$schema[ $field ]['format'] );
                $values[] = $value;
            }
        }
        // Run query.
        if ( $this->fields[ 'id' ] ) {
            $res = $this->wpdb->query( $this->wpdb->prepare(
                sprintf(
                    'UPDATE `%s` SET %s WHERE `id` = %d',
                    $this->table_name,
                    implode( ', ', $set ),
                    $this->fields[ 'id' ]
                ),
                $values
            ) );
        } else {
            $res = $this->wpdb->query( $this->wpdb->prepare(
                sprintf(
                    'INSERT INTO `%s` SET %s',
                    $this->table_name,
                    implode( ', ', $set )
                ),
                $values
            ) );
            if ( $res ) {
                $this->fields[ 'id' ] = $this->wpdb->insert_id;
            }
        }

        if ( $res ) {
            // Update loaded values.
            $this->loaded_values = $this->fields;
        }

        return $res;
    }

    /**
     * Delete entity from database.
     *
     * @return int|false
     */
    public function delete()
    {
        if ( $this->fields[ 'id' ] ) {
            return $this->wpdb->delete( $this->table_name, array( 'id' => $this->fields[ 'id' ] ), array( '%d' ) );
        }

        return false;
    }

    /**
     * Get table name.
     *
     * @static
     * @return string
     */
    public static function getTableName()
    {
        global $wpdb;

        return $wpdb->prefix . static::$table;
    }

    /**
     * Get schema.
     *
     * @static
     * @return array
     */
    public static function getSchema()
    {
        return static::$schema;
    }

    /**
     * Create query for this entity.
     *
     * @param $alias
     *
     * @return AB_Query
     */
    public static function query( $alias = 'r' )
    {
        return new AB_Query( get_called_class(), $alias );
    }

}