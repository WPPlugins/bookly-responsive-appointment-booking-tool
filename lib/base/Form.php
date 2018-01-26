<?php
namespace BooklyLite\Lib\Base;

/**
 * Class Form
 * @package BooklyLite\Lib\Base
 */
abstract class Form
{
    // Protected properties.

    /**
     * Class name of entity.
     * Must be defined in child form class.
     * @staticvar string
     */
    protected static $entity_class = null;

    /**
     * @staticvar string
     */
    protected static $namespace = '\BooklyLite\Lib\Entities';

    /**
     * Entity object.
     * @var Entity
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


    // Public methods.

    /**
     * Form constructor.
     */
    public function __construct()
    {
        // Create object of entity class.
        $entity_class = static::$namespace . '\\' . static::$entity_class;
        $this->object = new $entity_class();

        // Run configuration of child form.
        $this->configure();
    }

    /**
     * Configure the form in child class.
     */
    public function configure()
    {
        // Place configuration code here, like $this->setFields(...)
    }

    /**
     * Set fields.
     *
     * @param array $fields
     */
    public function setFields( array $fields )
    {
        $this->fields = array_merge( array( 'id' ), $fields );
    }

    /**
     * Bind values to form.
     *
     * @param array $_post
     * @param array $files
     */
    public function bind( array $_post, array $files = array() )
    {
        foreach ( $this->fields as $field ) {
            if ( array_key_exists( $field, $_post ) ) {
                $this->data[ $field ] = $_post[ $field ];
            }
        }
        // If we are going to update the object
        // load it from the database first.
        if ( ! $this->isNew() ) {
            $this->object->load( $this->data['id'] );
        }
    }

    /**
     * Determine whether we update the object or create it.
     *
     * @return boolean Create - true, Update - false
     */
    public function isNew()
    {
        return ! ( array_key_exists( 'id', $this->data ) && $this->data['id'] );
    }

    /**
     * Save data to database.
     *
     * @return Entity
     */
    public function save()
    {
        $this->object
            ->setFields( $this->data )
            ->save();

        return $this->object;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get entity object.
     *
     * @return Entity
     */
    public function getObject()
    {
        return $this->object;
    }

}