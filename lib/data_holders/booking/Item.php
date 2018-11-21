<?php
namespace Bookly\Lib\DataHolders\Booking;

use Bookly\Lib;

/**
 * Class Item
 * @package Bookly\Lib\DataHolders\Booking
 */
abstract class Item
{
    const TYPE_SIMPLE        = 1;
    const TYPE_COLLABORATIVE = 2;
    const TYPE_COMPOUND      = 3;
    const TYPE_SERIES        = 4;

    /** @var int */
    protected $type;
    /** @var float */
    protected $tax = 0;

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check if item is simple.
     *
     * @return bool
     */
    public function isSimple()
    {
        return $this->type == self::TYPE_SIMPLE;
    }

    /**
     * Check if item is collaborative.
     *
     * @return bool
     */
    public function isCollaborative()
    {
        return $this->type == self::TYPE_COLLABORATIVE;
    }

    /**
     * Check if item is compound.
     *
     * @return bool
     */
    public function isCompound()
    {
        return $this->type == self::TYPE_COMPOUND;
    }

    /**
     * Check if item is series.
     *
     * @return bool
     */
    public function isSeries()
    {
        return $this->type == self::TYPE_SERIES;
    }

    /**
     * Get appointment.
     *
     * @return Lib\Entities\Appointment
     */
    abstract public function getAppointment();

    /**
     * Get customer appointment.
     *
     * @return Lib\Entities\CustomerAppointment
     */
    abstract public function getCA();

    /**
     * Get deposit.
     *
     * @return string
     */
    abstract public function getDeposit();

    /**
     * Get extras.
     *
     * @return array
     */
    abstract public function getExtras();

    /**
     * Get service.
     *
     * @return Lib\Entities\Service;
     */
    abstract public function getService();


    /**
     * Get service duration.
     *
     * For compound or collaborative services the duration
     * is calculated based on duration of sub services.
     *
     * @return int
     */
    abstract public function getServiceDuration();

    /**
     * Get service price.
     *
     * @return float
     */
    abstract public function getServicePrice();

    /**
     * Get staff.
     *
     * @return Lib\Entities\Staff
     */
    abstract public function getStaff();

    /**
     * Get tax.
     *
     * @return string
     */
    abstract public function getTax();

    /**
     * Get appointment end time taking into account extras duration.
     *
     * For compound or collaborative services the total end
     * is calculated based on ending time of sub services.
     *
     * @return Lib\Slots\DatePoint
     */
    abstract public function getTotalEnd();

    /**
     * Get total price.
     *
     * @return float
     */
    abstract public function getTotalPrice();
}