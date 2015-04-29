<?php

abstract class AB_Form {
  
    // Protected properties.

    /**
     * Class name of entity.
     * Must be defined in child form class.
     * @staticvar string 
     */
    protected static $entity_class = null;

    /**
     * Entity object.
     * @var object 
     */
    protected $object = null;

    /**
     * Fields of form.
     * @var array
     */
    protected $fields = array( 'id' );

    /**
     * Values of form.
     * @var array
     */
    protected $data = array();

    // Private properties.

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
        // Get name of child class.
        $class_name = get_class( $this );

        if ( !in_array( $class_name, self::$checked_child_classes ) ) {
            // Check whether entity class is defined in the child class.
            if ( self::$entity_class === null ) {
                throw new Exception( sprintf( 'Entity class must be defined for form %s', $class_name ) );
            }
            // Indicate that static properties are now checked for this class.
            self::$checked_child_classes[] = $class_name;
        }

        // Create object of entity class.
        $this->object = new self::$entity_class();

        // Run configuration of child form.
        $this->configure();
    }

    /**
     * Configure the form in child class.
     */
    public function configure() {
        // Place configuration code here, like $this->setFields(...)
    }

    /**
     * Set fields.
     *
     * @param array $fields
     */
    public function setFields( array $fields ) {
        $this->fields = array_merge( array( 'id' ), $fields );
    }

    /**
     * Bind values to form.
     *
     * @param array $post
     * @param array $files
     */
    public function bind( array $post, array $files = array() ) {
        foreach ( $this->fields as $field ) {
            if ( array_key_exists( $field, $post ) ) {
                $this->data[ $field ] = $post[ $field ];
            }
        }
        // If we are going to update the object
        // load it from the database first.
        if ( !$this->isNew() ) {
            $this->object->load( $this->data[ 'id' ] );
        }
    }

    /**
     * Determine whether we update the object or create it.
     *
     * @return boolean Create - true, Update - false
     */
    public function isNew() {
        return !(array_key_exists( 'id', $this->data ) && $this->data[ 'id' ]);
    }

    /**
     * Save data to database.
     *
     * @return object Entity
     */
    public function save() {
        foreach ( $this->object->getData() as $field => $value ) {
            if ( array_key_exists( $field, $this->data ) ) {
                $this->object->set( $field, $this->data[ $field ] );
            }
        }

        $this->object->save();

        return $this->object;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get entity object.
     *
     * @return object
     */
    public function getObject() {
        return $this->object;
    }
}