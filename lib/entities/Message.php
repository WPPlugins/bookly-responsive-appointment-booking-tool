<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Message
 * @package Bookly\Lib\Entities
 */
class Message extends Lib\Base\Entity
{
    /** @var  int */
    protected $message_id;
    /** @var  string */
    protected $type;
    /** @var  string */
    protected $subject;
    /** @var  string */
    protected $body;
    /** @var  int */
    protected $seen = 0;
    /** @var  string */
    protected $created;

    protected static $table = 'bookly_messages';

    protected static $schema = array(
        'id'         => array( 'format' => '%d' ),
        'message_id' => array( 'format' => '%d' ),
        'type'       => array( 'format' => '%s' ),
        'subject'    => array( 'format' => '%s' ),
        'body'       => array( 'format' => '%s' ),
        'seen'       => array( 'format' => '%d' ),
        'created'    => array( 'format' => '%s' ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets message_id
     *
     * @return int
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * Sets message_id
     *
     * @param int $message_id
     * @return $this
     */
    public function setMessageId( $message_id )
    {
        $this->message_id = $message_id;

        return $this;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type
     *
     * @param string $type
     * @return $this
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject( $subject )
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Gets body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets body
     *
     * @param string $body
     * @return $this
     */
    public function setBody( $body )
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets seen
     *
     * @return int
     */
    public function getSeen()
    {
        return $this->seen;
    }

    /**
     * Sets seen
     *
     * @param int $seen
     * @return $this
     */
    public function setSeen( $seen )
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * Gets created
     *
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Sets created
     *
     * @param string $created
     * @return $this
     */
    public function setCreated( $created )
    {
        $this->created = $created;

        return $this;
    }

}