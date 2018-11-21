<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class SentNotification
 * @package Bookly\Lib\Entities
 */
class SentNotification extends Lib\Base\Entity
{
    /** @var  int */
    protected $ref_id;
    /** @var  int */
    protected $notification_id;
    /** @var  string */
    protected $created;

    protected static $table = 'bookly_sent_notifications';

    protected static $schema = array(
        'id'              => array( 'format' => '%d' ),
        'ref_id'          => array( 'format' => '%d' ),
        'notification_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Notification' ) ),
        'created'         => array( 'format' => '%s' ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets ref_id
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * Sets ref_id
     *
     * @param int $ref_id
     * @return $this
     */
    public function setRefId( $ref_id )
    {
        $this->ref_id = $ref_id;

        return $this;
    }

    /**
     * Gets notification id
     *
     * @return int
     */
    public function getNotificationId()
    {
        return $this->notification_id;
    }

    /**
     * Sets notification id
     *
     * @param int $notification_id
     * @return $this
     */
    public function setNotificationId( $notification_id )
    {
        $this->notification_id = $notification_id;

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