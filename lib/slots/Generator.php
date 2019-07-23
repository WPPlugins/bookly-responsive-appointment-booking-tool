<?php
namespace Bookly\Lib\Slots;

use Bookly\Lib\Proxy\Locations as LocationsProxy;
use Bookly\Lib\Proxy\Pro as ProProxy;

/**
 * Class Generator
 * @package Bookly\Lib\Slots
 */
class Generator implements \Iterator
{
    const CONNECTION_CONSECUTIVE = 1;
    const CONNECTION_PARALLEL    = 2;

    /** @var Staff[] */
    protected $staff_members;
    /** @var Schedule[] */
    protected $staff_schedule;
    /** @var int */
    protected $slot_length;
    /** @var DatePoint */
    protected $dp;
    /** @var int */
    protected $location_id;
    /** @var int */
    protected $srv_id;
    /** @var int */
    protected $srv_duration;
    /** @var int */
    protected $srv_duration_days;
    /** @var int */
    protected $srv_padding_left;
    /** @var int */
    protected $srv_padding_right;
    /** @var int */
    protected $nop;
    /** @var int */
    protected $extras_duration;
    /** @var Range  Requested time range */
    protected $time_limit;
    /** @var int */
    protected $spare_time;
    /** @var bool */
    protected $waiting_list_enabled;
    /** @var static */
    protected $next_generator;
    /** @var int */
    protected $next_connection;
    /** @var RangeCollection */
    protected $next_slots;
    /** @var RangeCollection[] */
    protected $past_slots;

    /**
     * Constructor.
     *
     * @param Staff[] $staff_members  Array of Staff objects indexed by staff ID
     * @param Schedule|null $service_schedule
     * @param int $slot_length
     * @param int $location_id
     * @param int $service_id
     * @param int $service_duration
     * @param int $service_padding_left
     * @param int $service_padding_right
     * @param int $nop  Number of persons
     * @param int $extras_duration
     * @param DatePoint $start_dp
     * @param string $time_from  Limit results by start time
     * @param string $time_to  Limit results by end time
     * @param int $spare_time  Spare time next to service
     * @param bool $waiting_list_enabled
     * @param self|null $next_generator
     * @param int $next_connection
     */
    public function __construct(
        array $staff_members,
        $service_schedule,
        $slot_length,
        $location_id,
        $service_id,
        $service_duration,
        $service_padding_left,
        $service_padding_right,
        $nop,
        $extras_duration,
        DatePoint $start_dp,
        $time_from,
        $time_to,
        $spare_time,
        $waiting_list_enabled,
        $next_generator,
        $next_connection
    )
    {
        $this->staff_members        = array();
        $this->staff_schedule       = array();
        $this->dp                   = $start_dp->modify( 'today' );
        $this->location_id          = (int) $location_id;
        $this->srv_id               = (int) $service_id ?: null;
        $this->srv_duration         = (int) min( $service_duration, DAY_IN_SECONDS );
        $this->srv_duration_days    = (int) ( $service_duration / DAY_IN_SECONDS );
        $this->srv_padding_left     = (int) $service_padding_left;
        $this->srv_padding_right    = (int) $service_padding_right;
        $this->slot_length          = (int) ( $this->srv_duration_days ? DAY_IN_SECONDS : min( $slot_length, DAY_IN_SECONDS ) );
        $this->nop                  = (int) $nop;
        $this->extras_duration      = (int) ( $this->srv_duration_days < 1 ? $extras_duration : 0 );
        $this->time_limit           = Range::fromTimes( $time_from, $time_to );
        $this->spare_time           = (int) $spare_time;
        $this->waiting_list_enabled = (bool) $waiting_list_enabled;
        $this->next_generator       = $next_generator;
        $this->next_connection      = $next_connection;

        // Pick only those staff members who provides the service
        // and who can serve the requested number of persons.
        foreach ( $staff_members as $staff_id => $staff ) {
            // Check that staff provides the service.
            $location_id = LocationsProxy::servicesPerLocationAllowed() ? $this->location_id : 0;
            if ( $staff->providesService( $this->srv_id, $location_id ) ) {
                // Check that requested number of persons meets service capacity.
                $service = $staff->getService( $this->srv_id, $this->location_id );
                if ( $service->capacityMax() >= $this->nop && $service->capacityMin() <= $this->nop ) {
                    $this->staff_members[ $staff_id ] = $staff;
                    // Prepare staff schedule.
                    $schedule = $staff->getSchedule( $location_id );
                    if ( $service_schedule ) {
                        $schedule = $schedule->intersect( $service_schedule );
                    }
                    $this->staff_schedule[ $staff_id ] = $schedule;
                }
            }
        }

        // Init next generator.
        if ( $this->next_generator ) {
            $this->next_slots = new RangeCollection();
            $this->next_generator->rewind();
        }
    }

    /**
     * @inheritdoc
     * @return RangeCollection
     */
    public function current()
    {
        $result = new RangeCollection();

        // Loop through all staff members.
        foreach ( $this->staff_members as $staff_id => $staff ) {
            $schedule = $this->staff_schedule[ $staff_id ];
            // Check that staff is not off.
            if ( ! $schedule->isDayOff( $this->dp ) ) {
                // Create ranges from staff schedule.
                $ranges = $this->srv_duration_days
                    ? $schedule->getAllDayRange( $this->dp, $this->srv_id, $staff_id, $this->location_id )
                    : $schedule->getRanges( $this->dp, $this->srv_id, $staff_id, $this->location_id, $this->time_limit );

                // Create booked ranges from staff bookings.
                $ranges = $this->_mapStaffBookings( $ranges, $staff );

                // Remove ranges with hours limit
                $ranges = ProProxy::prepareGeneratorRanges( $ranges, $staff, $this->srv_duration + $this->extras_duration );

                // Find slots.
                $max_capacity = $staff->getService( $this->srv_id, $this->location_id )->capacityMax();
                foreach ( $ranges->all() as $range ) {
                    $range = $range->replaceCapacity( $max_capacity );
                    // With available ranges we need to adjust their length.
                    if ( $range->state() == Range::AVAILABLE ) {
                        // Shorten range by service and extras duration.
                        $range = $range->transform( null, - $this->srv_duration - $this->extras_duration );
                        if ( ! $range->valid() ) {
                            // If range is not valid skip it.
                            continue;
                        }
                        // Enlarge range by slot length.
                        $range = $range->transform( null, $this->slot_length );
                    }
                    // Split range into slots.
                    foreach ( $range->split( $this->slot_length )->all() as $slot ) {
                        if ( $slot->length() < $this->slot_length ) {
                            // Skip slots with not enough length.
                            continue;
                        }
                        $timestamp = $this->srv_duration_days > 1 ?
                            $slot->start()->modify( '-' . ( $this->srv_duration_days - 1 ) . ' day' )->value()->getTimestamp() :
                            $slot->start()->value()->getTimestamp();
                        $ex_slot   = null;
                        // Decide whether to add slot or skip it.
                        if ( $result->has( $timestamp ) ) {
                            // If result already has this timestamp...
                            if ( $slot->fullyBooked() ) {
                                // Skip the slot if it is fully booked.
                                continue;
                            } else {
                                $ex_slot = $result->get( $timestamp );
                                if ( $ex_slot->notFullyBooked() && $slot->waitingListStarted() && $ex_slot->noWaitingListStarted() ) {
                                    // Skip the slot if it has waiting list started but the existing one does not.
                                    continue;
                                }
                            }
                        }
                        // For consecutive bookings try to find a next slot.
                        if ( $this->next_generator && ! $slot->nextSlot() && $slot->notFullyBooked() ) {
                            if ( ( $slot = $this->_tryFindNextSlot( $slot ) ) == false ) {
                                // Skip it if no next slot was found.
                                continue;
                            }
                        }
                        // For multi-day services try to find available day in the past.
                        if ( $this->srv_duration_days > 1 && $slot->state() == Range::AVAILABLE ) {
                            if ( ( $slot = $this->_tryFindPastSlot( $slot ) ) == false ) {
                                // Skip it if no past slot was found.
                                continue;
                            }
                        }
                        // Decide which slot to add.
                        if ( $ex_slot && $ex_slot->notFullyBooked() && ( $slot->waitingListStarted() || $ex_slot->noWaitingListStarted() ) ) {
                            $slot = $this->_findPreferableSlot( $slot, $ex_slot );
                        }
                        // Add slot to result.
                        $result->put( $timestamp, $slot );
                    }
                }
            }
        }

        return $result->ksort();
    }

    /**
     * Create fully/partially booked ranges from staff bookings.
     *
     * @param RangeCollection $ranges
     * @param Staff $staff
     * @return RangeCollection
     */
    private function _mapStaffBookings( RangeCollection $ranges, $staff )
    {
        if ( $ranges->isNotEmpty() ) {
            $max_capacity = $staff->getService( $this->srv_id, $this->location_id )->capacityMax();
            foreach ( $staff->getBookings() as $booking ) {
                // Take in account booking and service padding.
                $range_to_remove = $booking->rangeWithPadding()->transform( - $this->srv_padding_right, $this->srv_padding_left );
                // Remove booking from ranges.
                $new_ranges = new RangeCollection();
                $removed    = new RangeCollection();
                foreach ( $ranges->all() as $r ) {
                    if ( $r->overlaps( $range_to_remove ) ) {
                        // Make sure that removed range will have length of a multiple of slot length.
                        $extra_left  = $range_to_remove->start()->diff( $r->start() ) % $this->slot_length;
                        $extra_right = $range_to_remove->end()->diff( $r->start() ) % $this->slot_length;
                        $remove      = $range_to_remove->transform(
                            $extra_left
                                ? - $extra_left
                                : null,
                            $extra_right
                                ? $this->slot_length - $extra_right
                                : null
                        );
                        $new_ranges = $new_ranges->merge( $r->subtract( $remove, $removed_range ) );
                        /** @var Range $removed_range */
                        if ( $removed_range ) {
                            $removed->push( $removed_range->replaceNop( $booking->nop() ) );
                        }
                    } else {
                        $new_ranges->push( $r );
                    }
                }
                $ranges = $new_ranges;
                // If some ranges were removed add them back with appropriate state.
                if ( $removed->isNotEmpty() ) {
                    $data = $removed->get( 0 )->data()->replaceState( Range::FULLY_BOOKED );
                    // Handle waiting list.
                    if ( $this->waiting_list_enabled && $booking->serviceId() == $this->srv_id && $booking->range()->length() - $booking->extrasDuration() == ( $this->srv_duration_days > 1 ? $this->srv_duration_days * DAY_IN_SECONDS : $this->srv_duration ) ) {
                        if ( $booking->onWaitingList() ) {
                            $data = $data->replaceOnWaitingList( $booking->onWaitingList() );
                        }
                        $booking_range = $booking->range();
                        foreach ( $removed->all() as $range ) {
                            // Find range which contains booking start point.
                            if ( $range->contains( $booking_range->start() ) ) {
                                // Create partially booked range and add it to collection.
                                $ranges->push( $booking_range->resize( $this->slot_length )->replaceData(
                                    $data->replaceState( Range::WAITING_LIST_STARTED )
                                ) );
                                break;
                            }
                        }
                    }
                    foreach ( $removed->all() as $range ) {
                        $ranges->push( $range->replaceData( $data ) );
                    }
                    // Handle partially booked appointments (when number of persons is less than max capacity).
                    if (
                        ! $booking->oneBookingPerSlot() &&
                        ( ! $booking->locationId() || ! $this->location_id || $booking->locationId() == $this->location_id ) &&
                        $booking->serviceId() == $this->srv_id &&
                        $booking->nop() <= $max_capacity - $this->nop &&
                        $booking->range()->length() - $booking->extrasDuration() == ( $this->srv_duration_days > 1 ? $this->srv_duration_days * DAY_IN_SECONDS : $this->srv_duration ) &&
                        $booking->extrasDuration() >= $this->extras_duration
                    ) {
                        $booking_range = $booking->range();
                        foreach ( $removed->all() as $range ) {
                            // Find range which contains booking start point.
                            if ( $range->contains( $booking_range->start() ) ) {
                                $data = $data->replaceState( Range::PARTIALLY_BOOKED );
                                // Create partially booked range and add it to collection.
                                $ranges->push( $booking_range->resize( $this->slot_length )->replaceData( $data ) );
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $ranges;
    }

    /**
     * Try to find next slot for consecutive bookings.
     *
     * @param Range $slot
     * @return Range|false
     */
    private function _tryFindNextSlot( Range $slot )
    {
        if ( $this->next_connection == self::CONNECTION_CONSECUTIVE ) {
            $next_start = $slot->start()->modify( $this->srv_duration + $this->extras_duration + $this->spare_time );
            $padding = $this->srv_padding_right + $this->next_generator->srv_padding_left;
            // There are 2 possible options:
            // 1. next service is done by another staff, then do not take into account padding
            // 2. next service is done by the same staff, then count padding
            $next_slot = $this->_findNextSlot( $next_start );
            if (
                $next_slot == false ||
                $next_slot->fullyBooked() ||
                $padding != 0 && $next_slot->staffId() == $slot->staffId()
            ) {
                $next_slot = $this->_findNextSlot( $next_start->modify( $padding ) );
                if ( $next_slot && (
                    $next_slot->fullyBooked() ||
                    $next_slot->staffId() != $slot->staffId()
                ) ) {
                    $next_slot = false;
                }
            }
        } else {
            $next_slot = $this->_findNextSlot( $slot->start() );
            if ( $next_slot ) {
                if ( $next_slot->fullyBooked() ) {
                    $next_slot = false;
                } else {
                    while ( in_array( $slot->staffId(), $next_slot->allStaffIds() ) ) {
                        if ( $next_slot->hasAltSlot() ) {
                            // Try alternative slot.
                            $next_slot = $next_slot->altSlot();
                        } else {
                            $next_slot = false;
                            break;
                        }
                    }
                }
            }
        }

        if ( $next_slot ) {
            // Connect slots with each other.
            $slot = $slot->replaceNextSlot( $next_slot );
        } else {
            // If no next slot was found then return false.
            return false;
        }

        return $slot;
    }

    /**
     * Try to find a valid slot in the past for multi-day services.
     *
     * @param Range $slot
     * @return Range|bool
     */
    private function _tryFindPastSlot( Range $slot )
    {
        $timestamp = $slot->start()->value()->getTimestamp();
        if ( ! isset( $this->past_slots[ $slot->staffId() ] ) ) {
            $this->past_slots[ $slot->staffId() ] = new RangeCollection();
        }
        // Store slot for further reference.
        // @todo In theory we can hold just $this->srv_duration_days slots in the past.
        $this->past_slots[ $slot->staffId() ]->put( $timestamp, $slot );
        // Check if there are enough valid days for service duration in the past.
        $day = $slot->start();
        for ( $d = 1; $d < $this->srv_duration_days; ++ $d ) {
            $day       = $day->modify( '-1 day' );
            $timestamp = $day->value()->getTimestamp();
            if ( ! $this->past_slots[ $slot->staffId() ]->has( $timestamp ) ) {
                return false;
            }
        }
        // Replace slot with one from the day when service starts.
        $slot = $this->past_slots[ $slot->staffId() ]->get( $timestamp )->replaceNextSlot( $slot->nextSlot() );

        return $slot;
    }

    /**
     * Find next slot for consecutive bookings.
     *
     * @param IPoint $start
     * @return Range|false
     */
    private function _findNextSlot( IPoint $start )
    {
        while (
            $this->next_generator->valid() &&
            // Do search only while next generator is producing slots earlier than the requested point.
            $start->modify( $this->next_generator->srv_duration_days . ' days' )->gt( $this->next_generator->key() )
        ) {
            $this->next_slots = $this->next_slots->union( $this->next_generator->current() );
            $this->next_generator->next();
        }

        return $this->next_slots->get( $start->value()->getTimestamp() );
    }

    /**
     * Find more preferable slot and store the other one as alternative.
     *
     * @param Range $slot
     * @param Range $ex_slot
     * @return Range
     */
    private function _findPreferableSlot( $slot, $ex_slot )
    {
        // Find which staff is more preferable.
        $staff    = $this->staff_members[ $slot->staffId() ];
        $ex_staff = $this->staff_members[ $ex_slot->staffId() ];
        if ( $staff->morePreferableThan( $ex_staff, $slot ) ) {
            $slot = $slot->replaceAltSlot( $ex_slot );
        } else {
            if ( $ex_slot->hasAltSlot() ) {
                $slot = $this->_findPreferableSlot( $slot, $ex_slot->altSlot() );
            }
            $slot = $ex_slot->replaceAltSlot( $slot );
        }

        return $slot;
    }

    /**
     * Get service duration in days.
     *
     * @return int
     */
    public function serviceDurationInDays()
    {
        return $this->srv_duration_days;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        // Start one day earlier to cover night shifts.
        $this->dp = $this->dp->modify( '-1 day' );
    }

    /**
     * @inheritdoc
     * @return DatePoint
     */
    public function key()
    {
        return $this->dp;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->dp = $this->dp->modify( '+1 day' );
    }

    /**
     * Infinite search.
     *
     * @return bool
     */
    public function valid()
    {
        return true;
    }
}