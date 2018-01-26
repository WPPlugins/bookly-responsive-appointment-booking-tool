<?php
namespace BooklyLite\Lib\DataHolders\Notification;

use BooklyLite\Lib\Entities\CustomerAppointment;
use BooklyLite\Lib\Entities\Notification;

/**
 * Class Settings
 * @package BooklyLite\Lib\DataHolders\Notification
 */
class Settings
{
    const SET_AFTER_EVENT                       = 'after_event';
    const SET_EXISTING_EVENT_WITH_DATE          = 'existing_event_with_date';
    const SET_EXISTING_EVENT_WITH_DATE_AND_TIME = 'existing_event_with_date_and_time';
    const SET_EXISTING_EVENT_WITH_DATE_BEFORE   = 'existing_event_with_date_before';

    /** @var array */
    protected $settings;
    /** @var  int */
    protected $offset_hours = 0;
    /** @var  int */
    protected $at_hour;
    /** @var  string  @see CustomerAppointment::STATUS_* or any */
    protected $status = 'any';
    /** @var  bool */
    protected $instant = 0;

    /**
     * Condition constructor.
     *
     * @param Notification $notification
     */
    public function __construct( Notification $notification )
    {
        $this->settings = (array) json_decode( $notification->getSettings(), true );
        $this->prepare( $notification->getType() );
    }

    /**
     * @param string $type
     */
    private function prepare( $type )
    {
        switch ( $type ) {
            case Notification::TYPE_APPOINTMENT_START_TIME:
            case Notification::TYPE_LAST_CUSTOMER_APPOINTMENT:
                $set  = Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME;
                if ( isset( $this->settings[ $set ] ) ) {
                    $this->status = $this->settings[ $set ]['status'];
                    // selected radio
                    $option = $this->settings[ $set ]['option'];
                    if ( $option == 1 ) {
                        // offset_hours [ 1h .. 30d ] & perform [ after | before ]
                        $this->offset_hours = $this->settings[ $set ]['offset_hours'];
                        if ( $this->settings[ $set ]['perform'] == 'before' ) {
                            $this->offset_hours *= - 1;
                        }
                    } elseif ( $option == 2 ) {
                        // at_hour [ 00:00 .. 23:00 ] & offset_bidirectional_hours [ -30d .. 30d ]
                        $this->at_hour      = $this->settings[ $set ]['at_hour'];
                        $this->offset_hours = $this->settings[ $set ]['offset_bidirectional_hours'];
                    }
                }
                break;
            case Notification::TYPE_STAFF_DAY_AGENDA:
                $set = Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE;
                if ( isset( $this->settings[ $set ] ) ) {
                    // AGENDA    at_hour [ 00:00 .. 23:00 ] & offset_bidirectional_hours [ -30d .. 0 ]
                    $this->at_hour      = $this->settings[ $set ]['at_hour'];
                    $this->offset_hours = $this->settings[ $set ]['offset_bidirectional_hours'];
                }
                break;
            case Notification::TYPE_CUSTOMER_BIRTHDAY:
                $set = Settings::SET_EXISTING_EVENT_WITH_DATE;
                if ( isset( $this->settings[ $set ] ) ) {
                    // AGENDA    at_hour [ 00:00 .. 23:00 ] & offset_bidirectional_hours [ -30d .. 30d ]
                    $this->at_hour      = $this->settings[ $set ]['at_hour'];
                    $this->offset_hours = $this->settings[ $set ]['offset_bidirectional_hours'];
                }
                break;
            case Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED:
            case Notification::TYPE_CUSTOMER_APPOINTMENT_CREATED:
                $set  = Settings::SET_AFTER_EVENT;
                if ( isset( $this->settings[ $set ] ) ) {
                    $this->status = $this->settings[ $set ]['status'];
                    // selected radio
                    $option = $this->settings[ $set ]['option'];
                    if ( $option == 1 ) {
                        // Instantly
                        $this->instant = 1;
                    } elseif ( $option == 2 ) {
                        // offset_hours [ 1h .. 30d ] after
                        $this->offset_hours = $this->settings[ $set ]['offset_hours'];
                    } elseif ( $option == 3 ) {
                        // at_hour [ 00:00 .. 23:00 ] & offset_bidirectional_hours [ 0 .. 30d ]
                        $this->at_hour      = $this->settings[ $set ]['at_hour'];
                        $this->offset_hours = $this->settings[ $set ]['offset_bidirectional_hours'];
                    }
                }
                break;
        }
    }

    /**
     * @return int
     */
    public function getOffsetHours()
    {
        return (int) $this->offset_hours;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets at_hour
     *
     * @return int
     */
    public function getSendAtHour()
    {
        return (int) $this->at_hour;
    }

    /**
     * Gets at_hour
     *
     * @return int|null
     */
    public function getAtHour()
    {
        return $this->at_hour;
    }

    /**
     * Gets instant
     *
     * @return bool
     */
    public function getInstant()
    {
        return $this->instant;
    }

    /**
     * Default Custom notification settings
     * @return array
     */
    public static function getDefault()
    {
        return array(
            // Notification::TYPE_APPOINTMENT_START_TIME
            // Notification::TYPE_LAST_CUSTOMER_APPOINTMENT
            Settings::SET_EXISTING_EVENT_WITH_DATE_AND_TIME => array(
                'status'       => 'any',
                'option'       => 1,
                'offset_hours' => 24,   'prepend' => 'before',
                'at_hour'      => 9,    'offset_bidirectional_hours' => '0'
            ),
            // Notification::TYPE_CUSTOMER_BIRTHDAY
            Settings::SET_EXISTING_EVENT_WITH_DATE          => array(
                'at_hour'      => 9,
                'offset_bidirectional_hours' => '0',
            ),
            // Notification::TYPE_CUSTOMER_APPOINTMENT_STATUS_CHANGED
            Settings::SET_AFTER_EVENT                       => array(
                'status'       => CustomerAppointment::STATUS_APPROVED,
                'option'       => 1,
                'offset_hours' => 1,
                'at_hour'      => 9,    'offset_bidirectional_hours' => '0'
            ),
            // Notification::TYPE_STAFF_DAY_AGENDA
            Settings::SET_EXISTING_EVENT_WITH_DATE_BEFORE   => array(
                'at_hour'      => 9,
                'offset_bidirectional_hours' => -24,
            )
        );
    }

}