<?php

/**
 * Database entity.
 */
abstract class AB_Entity {

    // Protected properties.

    /**
     * Reference to global database object.
     * @var wpdb
     */
    protected $wpdb;

    /**
     * Name of table in database.
     * Must be defined in child class.
     * @var string
     */
    protected $table_name = null;

    /**
     * Schema of entity fields in database.
     * Must be defined in child class as
     * array(
     *     '[FIELD_NAME]' => array(
     *         'format'  => '[FORMAT]',
     *         'default' => '[DEFAULT_VALUE]',
     *     )
     * )
     * @var array
     */
    protected $schema = null;

    /**
     * Flag that shows whether entity data was loaded or not.
     * @var boolean
     */
    protected $loaded = false;

    // Private properties.

    /**
     * Values of fields.
     * @var array
     */
    private $values = array();

    /**
     * Formats of fields.
     * @var array
     */
    private $formats = array();

    /**
     * Array of flags that indicate whether static properties
     * were checked for particular child class
     * to contain all required options.
     * @staticvar array
     */
    private static $checked_child_classes = array();


    // Public methods.

    /**
     * Constructor.
     */
    public function __construct() {
      /** @var WPDB $wpdb */
      global $wpdb;

      // Reference to global database object.
      $this->wpdb = $wpdb;

      // Get name of child class.
      $class_name = get_class( $this );

      if ( !in_array( $class_name, self::$checked_child_classes ) ) {
          // Check whether table name is defined in the child class.
          if ( $this->table_name === null ) {
              throw new Exception( sprintf( 'Table name must be defined for entity %s', $class_name ) );
          }
          // Check whether schema is defined in the child class.
          if ( $this->schema === null ) {
              throw new Exception( sprintf( 'Schema must be defined for entity %s', $class_name ) );
          }
          // Ensure that schema contains field `id`.
          if ( !array_key_exists( 'id', $this->schema ) ) {
              throw new Exception( sprintf( 'Schema must contain field `id` for entity %s', $class_name ) );
          }
          // Ensure that schema contains all required options.
          // Set default options otherwise.
          array_walk( $this->schema, array( $this, '_ensureSchemaDefaultOptions' ) );
          // Indicate that static properties are now checked for this class.
          self::$checked_child_classes[] = $class_name;
      }

      // Initialize $values and $formats.
      foreach ( $this->schema as $field_name => $options ) {
          @$this->values[ $field_name ]  = $options[ 'default' ];
          @$this->formats[ $field_name ] = $options[ 'format' ];
      }
    }

    /**
     * Set value to field.
     *
     * @param string $field
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function set( $field, $value ) {
        if ( !array_key_exists( $field, $this->values ) ) {
            throw new InvalidArgumentException( sprintf( 'Trying to set unknown field "%s" for entity "%s"', $field, get_class( $this ) ) );
        }

        $this->values[ $field ] = $value;
    }

    /**
     * Get value of field.
     *
     * @param string $field
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get( $field ) {
        if ( !array_key_exists( $field, $this->values ) ) {
            throw new InvalidArgumentException( sprintf( 'Trying to get unknown field "%s" for entity "%s"', $field, get_class( $this ) ) );
        }

        return $this->values[ $field ];
    }

    /**
     * Magic set method.
     *
     * @param string $field
     * @param mixed $value
     */
    public function __set( $field, $value ) {
        $this->set( $field, $value );
    }

    /**
     * Magic get method.
     *
     * @param string $field
     * @return mixed
     */
    public function __get( $field ) {
        return $this->get( $field );
    }

    /**
     * Load entity from database.
     *
     * @param integer $id
     * @return boolean
     */
    public function load( $id ) {
        $row = $this->wpdb->get_row( sprintf( 'SELECT * FROM %s WHERE id = %d', $this->table_name, $id ) );

        if ( $row ) {
            $this->setData( $row );
            $this->loaded = true;
        } else {
            $this->loaded = false;
        }

        return $this->loaded;
    }

    /**
     * Set values to fields.
     * The method can be used to update only some fields.
     *
     * @param array|object $data
     */
    public function setData( $data ) {
        if ( is_array( $data ) || $data instanceof stdClass ) {
            foreach ( $data as $field => $value ) {
                if ( array_key_exists( $field, $this->values ) ) {
                    $this->values[ $field ] = $value;
                }
            }
        }
    }

    /**
     * Get values of fields as array.
     *
     * @return array
     */
    public function getData() {
        return $this->values;
    }

    /**
     * Save entity to database.
     *
     * @return int|false
     */
    public function save() {
        // Prepare query data.
        $set    = array();
        $values = array();
        foreach ( $this->values as $field => $value )
        {
            if ( $field == 'id' ) {
                continue;
            }
            if ( $value === null ) {
                $set[] = sprintf( '`%s` = NULL', $field );
            } else {
                $set[] = sprintf( '`%s` = %s', $field, $this->formats[ $field ] );
                $values[] = $value;
            }
        }
        // Run query.
        if ( $this->values[ 'id' ] ) {
            $res = $this->wpdb->query( $this->wpdb->prepare(
                sprintf(
                    'UPDATE `%s` SET %s WHERE `id` = %d',
                    $this->table_name,
                    implode( ', ', $set ),
                    $this->values[ 'id' ]
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
                $this->values[ 'id' ] = $this->wpdb->insert_id;
            }
        }

        return $res;
    }

    /**
     * Delete entity from database.
     *
     * @return int|false
     */
    public function delete() {
        if ( $this->values[ 'id' ] ) {
            return $this->wpdb->delete( $this->table_name, array( 'id' => $this->values[ 'id' ] ), array( '%d' ) );
        }

        return false;
    }


    // Private methods.

    /**
     * Private function that makes sure that schema
     * contains all required options.
     *
     * @param array &$options
     */
    private function _ensureSchemaDefaultOptions( &$options ) {
        if ( !array_key_exists( 'format', $options ) ) {
            $options[ 'format' ] = '%s';
        }
        if ( !array_key_exists( 'default', $options ) ) {
            $options[ 'default' ] = null;
        }
    }
}
