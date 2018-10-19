<?php
namespace Bookly\Lib\Slots;

/**
 * Class RangeData
 * @package Bookly\Lib\Slots
 */
class RangeData
{
    /** @var int */
    protected $service_id;
    /** @var int */
    protected $staff_id;
    /** @var int */
    protected $location_id;
    /** @var int */
    protected $state;
    /** @var int */
    protected $on_waiting_list;
    /** @var int */
    protected $capacity;
    /** @var int */
    protected $nop;
    /** @var Range */
    protected $next_slot;

    /**
     * Constructor.
     *
     * @param int $service_id
     * @param int $staff_id
     * @param int $location_id
     * @param int $state
     * @param int $on_waiting_list
     * @param int $capacity
     * @param int $nop
     * @param Range|null $next_slot
     */
    public function __construct( $service_id, $staff_id, $location_id = 0, $state = Range::AVAILABLE, $on_waiting_list = 0, $next_slot = null, $capacity = 1, $nop = 0 )
    {
        $this->service_id      = $service_id;
        $this->staff_id        = $staff_id;
        $this->location_id     = $location_id;
        $this->state           = $state;
        $this->on_waiting_list = $on_waiting_list;
        $this->next_slot       = $next_slot;
        $this->capacity        = $capacity;
        $this->nop             = $nop;
    }

    /**
     * Get service ID.
     *
     * @return int
     */
    public function serviceId()
    {
        return $this->service_id;
    }

    /**
     * Get staff ID.
     *
     * @return int
     */
    public function staffId()
    {
        return $this->staff_id;
    }

    /**
     * Get location ID.
     *
     * @return int
     */
    public function locationId()
    {
        return $this->location_id;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function state()
    {
        return $this->state;
    }

    /**
     * Get capacity.
     *
     * @return int
     */
    public function capacity()
    {
        return $this->capacity;
    }

    /**
     * Get nop.
     *
     * @return int
     */
    public function nop()
    {
        return $this->nop;
    }

    /**
     * Get number of persons on waiting list.
     *
     * @return int
     */
    public function onWaitingList()
    {
        return $this->on_waiting_list;
    }

    /**
     * Get next slot.
     *
     * @return Range
     */
    public function nextSlot()
    {
        return $this->next_slot;
    }

    /**
     * Check whether next slot is set.
     *
     * @return bool
     */
    public function hasNextSlot()
    {
        return $this->next_slot != null;
    }

    /**
     * Create a copy of the data with new staff ID.
     *
     * @param int $new_staff_id
     * @return static
     */
    public function replaceStaffId( $new_staff_id )
    {
        return new static( $this->service_id, $new_staff_id, $this->location_id, $this->state, $this->on_waiting_list, $this->next_slot, $this->capacity, $this->nop );
    }

    /**
     * Create a copy of the data with new state.
     *
     * @param int $new_state
     * @return static
     */
    public function replaceState( $new_state )
    {
        return new static( $this->service_id, $this->staff_id, $this->location_id, $new_state, $this->on_waiting_list, $this->next_slot, $this->capacity, $this->nop );
    }

    /**
     * Create a copy of the data with new on waiting list number.
     *
     * @param int $new_on_waiting_list
     * @return static
     */
    public function replaceOnWaitingList( $new_on_waiting_list )
    {
        return new static( $this->service_id, $this->staff_id, $this->location_id, $this->state, $new_on_waiting_list, $this->next_slot, $this->capacity, $this->nop );
    }

    /**
     * Create a copy of the data with new next slot.
     *
     * @param Range|null $new_next_slot
     * @return static
     */
    public function replaceNextSlot( $new_next_slot )
    {
        return new static( $this->service_id, $this->staff_id, $this->location_id, $this->state, $this->on_waiting_list, $new_next_slot, $this->capacity, $this->nop );
    }

    public function replaceNop( $new_nop )
    {
        return new static( $this->service_id, $this->staff_id, $this->location_id, $this->state, $this->on_waiting_list, $this->next_slot, $this->capacity, $new_nop );
    }

    public function replaceCapacity( $new_capacity )
    {
        return new static( $this->service_id, $this->staff_id, $this->location_id, $this->state, $this->on_waiting_list, $this->next_slot, $new_capacity, $this->nop );
    }
}