<?php
namespace BooklyLite\Lib\Slots;

/**
 * Class RangeData
 * @package BooklyLite\Lib\Slots
 */
class RangeData
{
    /** @var int */
    protected $service_id;
    /** @var int */
    protected $staff_id;
    /** @var int */
    protected $state;
    /** @var Range */
    protected $next_slot;

    /**
     * Constructor.
     *
     * @param int $service_id
     * @param int $staff_id
     * @param int $state
     * @param Range|null $next_slot
     */
    public function __construct( $service_id, $staff_id, $state = Range::AVAILABLE, $next_slot = null )
    {
        $this->service_id = $service_id;
        $this->staff_id   = $staff_id;
        $this->state      = $state;
        $this->next_slot  = $next_slot;
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
     * Get state.
     *
     * @return int
     */
    public function state()
    {
        return $this->state;
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
        return new static( $this->service_id, $new_staff_id, $this->state, $this->next_slot );
    }

    /**
     * Create a copy of the data with new state.
     *
     * @param int $new_state
     * @return static
     */
    public function replaceState( $new_state )
    {
        return new static( $this->service_id, $this->staff_id, $new_state, $this->next_slot );
    }

    /**
     * Create a copy of the data with new next slot.
     *
     * @param Range|null $new_next_slot
     * @return static
     */
    public function replaceNextSlot( $new_next_slot )
    {
        return new static( $this->service_id, $this->staff_id, $this->state, $new_next_slot );
    }
}