<?php
namespace BooklyLite\Lib\Slots;

use BooklyLite\Lib;
use BooklyLite\Lib\Utils\DateTime;

/**
 * Class Finder
 * @package BooklyLite\Lib\Slots
 */
class Finder
{
    // Input parameters.
    /** @var Lib\UserBookingData */
    protected $userData;
    /** @var array|null */
    protected $last_fetched_slot = null;
    /** @var string|null */
    protected $selected_date = null;

    // Configuration.
    /** @var int */
    protected $slot_length;
    /** @var bool */
    protected $srv_duration_as_slot_length;
    /** @var bool */
    protected $show_calendar;
    /** @var bool */
    protected $show_blocked_slots;
    /** @var callable */
    protected $callback_group;
    /** @var callable */
    protected $callback_stop;

    // Data for generator.
    /** @var Staff[] */
    protected $staff = array();
    /** @var Schedule[] */
    protected $service_schedule = array();

    // Dates in WP time zone.
    /** @var DatePoint */
    public $start_dp;
    /** @var DatePoint */
    public $end_dp;

    // Dates in client time zone.
    /** @var DatePoint */
    public $client_start_dp;
    /** @var DatePoint */
    public $client_end_dp;

    // Result.
    /** @var array */
    protected $slots;
    /** @var bool */
    protected $has_more_slots;
    /** @var int */
    protected $srv_duration_days;

    /**
     * Constructor.
     *
     * @param Lib\UserBookingData $userData
     * @param callable $callback_group
     * @param callable $callback_stop
     */
    public function __construct( Lib\UserBookingData $userData, $callback_group = null, $callback_stop = null )
    {
        $this->userData                    = $userData;
        $this->slot_length                 = Lib\Config::getTimeSlotLength();
        $this->srv_duration_as_slot_length = Lib\Config::useServiceDurationAsSlotLength();
        $this->show_calendar               = Lib\Config::showCalendar();
        $this->show_blocked_slots          = Lib\Config::showBlockedTimeSlots();

        // Prepare group callback.
        if ( is_callable( $callback_group ) ) {
            $this->callback_group = $callback_group;
        } else {
            $this->callback_group = array( $this, '_groupDefault' );
        }

        // Prepare stop callback.
        if ( is_callable( $callback_stop ) ) {
            $this->callback_stop = $callback_stop;
        } else {
            if ( $this->show_calendar ) {
                $this->callback_stop = array( $this, '_stopCalendar' );
            } else {
                if ( Lib\Config::showDayPerColumn() ) {
                    $this->callback_stop = array( $this, '_stopDayPerColumn' );
                } else {
                    $this->callback_stop = array( $this, '_stopDefault' );
                }
            }
        }
    }

    /**
     * Prepare dates and staff data.
     *
     * @return $this
     */
    public function prepare()
    {
        $this->_prepareDates();
        $this->_prepareStaffData();

        return $this;
    }

    /**
     * Init slots generator.
     *
     * @return Generator
     */
    private function _generate()
    {
        $generator = null;

        /** @var Lib\ChainItem $chain_item */
        foreach ( array_reverse( $this->userData->chain->getItems() ) as $chain_item ) {
            $compound_service_id = $chain_item->getService()->isCompound() ? $chain_item->getService()->getId() : null;
            $extras_duration = (int) Lib\Proxy\ServiceExtras::getTotalDuration( $chain_item->getExtras() );
            for ( $q = 0; $q < $chain_item->getQuantity(); ++ $q ) {
                $sub_services = $chain_item->getSubServicesWithSpareTime();
                $last_key     = count( $sub_services ) - 1;
                $spare_time   = 0;
                foreach ( array_reverse( $sub_services ) as $key => $sub_service ) {
                    if ( $sub_service instanceof Lib\Entities\Service ) {
                        $generator = new Generator(
                            array_intersect_key( $this->staff, array_flip( $chain_item->getStaffIdsForSubService( $sub_service ) ) ),
                            isset ( $this->service_schedule[ $compound_service_id ][ $sub_service->getId() ] )
                                ? $this->service_schedule[ $compound_service_id ][ $sub_service->getId() ]
                                : null,
                            $this->srv_duration_as_slot_length ? $sub_service->getDuration() : $this->slot_length,
                            $chain_item->getLocationId(),
                            $sub_service->getId(),
                            $sub_service->getDuration(),
                            $sub_service->getPaddingLeft(),
                            $sub_service->getPaddingRight(),
                            $chain_item->getNumberOfPersons(),
                            $key == $last_key ? $extras_duration : 0,
                            $this->start_dp,
                            $this->userData->getTimeFrom(),
                            $this->userData->getTimeTo(),
                            $spare_time,
                            $generator
                        );
                        $spare_time = 0;
                    } else {
                        /** @var Lib\Entities\SubService $sub_service */
                        $spare_time += $sub_service->getDuration();
                    }
                }
            }
        }

        $this->srv_duration_days = $generator->serviceDurationInDays();

        return $generator;
    }

    /**
     * Load and init.
     *
     * @param callable $callback_break
     */
    public function load( $callback_break = null )
    {
        $this->slots = array();
        $this->has_more_slots = false;

        // Prepare break callback.
        if ( ! is_callable( $callback_break ) ) {
            $callback_break = array( $this, '_breakDefault' );
        }

        // Do search.
        $slots_count = 0;
        $do_break    = false;
        $weekdays    = $this->userData->getDays();
        $generator   = $this->_generate();
        foreach ( $generator as $slots ) {
            // Workaround for PHP < 5.5.
            $dp = $generator->key();
            // For empty slots check client end date here.
            if ( call_user_func( $callback_break, $dp, $this->srv_duration_days, $slots_count ) ) {
                break;
            }
            foreach ( $slots->all() as $slot ) {
                if ( $do_break ) {
                    // Flag there are more slots.
                    $this->has_more_slots = true;
                    break 2;
                }
                /** @var DatePoint $client_dp */
                $client_dp = $slot->start()->toClientTz();
                if ( $client_dp->lt( $this->client_start_dp ) ) {
                    // Skip slots earlier than requested time.
                    continue;
                }
                if ( ! in_array( (int) $client_dp->format( 'w' ) + 1, $weekdays ) ) {
                    // Skip slots outside of requested weekdays.
                    continue;
                }

                // Decide how to group slots.
                $group = call_user_func( $this->callback_group, $client_dp );

                // Decide when to stop.
                if ( ! isset ( $this->slots[ $group ] ) ) {
                    switch ( call_user_func( $this->callback_stop, $client_dp, count( $this->slots ), $slots_count ) ) {
                        case 0:  // Continue search.
                            break;
                        case 1:  // Immediate stop.
                            break 3;
                        case 2:  // Check whether there are more slots and then stop.
                            $do_break = true;
                            continue 2;
                    }
                }

                if ( $slot->notFullyBooked() || $this->show_blocked_slots ) {
                    // Add slot to result.
                    $this->slots[ $group ][] = $slot;

                    ++ $slots_count;
                }
            }

        }
    }

    /**
     * Callback for making decision whether to break generator loop.
     *
     * @param DatePoint $dp
     * @param int $srv_duration_days
     * @param int $slots_count
     * @return bool
     */
    private function _breakDefault( DatePoint $dp, $srv_duration_days, $slots_count )
    {
        return $dp->modify( - ( $srv_duration_days > 1 ? $srv_duration_days - 1 : 0 ) . ' days' )->gte( $this->client_end_dp );
    }

    /**
     * Callback for computing slot's group.
     *
     * @param DatePoint $client_dp
     * @return string
     */
    private function _groupDefault( DatePoint $client_dp )
    {
        return $client_dp
            ->modify( $this->srv_duration_days && ! $this->show_calendar ? 'first day of this month' : null )
            ->format('Y-m-d' );
    }

    /**
     * Callback for making decision whether to stop when calendar is enabled.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return bool
     */
    private function _stopCalendar( DatePoint $client_dp, $groups_count, $slots_count  )
    {
        return (int) $client_dp->gte( $this->client_end_dp );
    }

    /**
     * Callback for making decision whether to stop when days are displayed in one column.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return int
     */
    private function _stopDayPerColumn( DatePoint $client_dp, $groups_count, $slots_count )
    {
        // Stop when groups count has reached 10.
        return $groups_count >= 10 ? 2 : 0;
    }

    /**
     * Callback for making decision whether to stop for default mode.
     *
     * @param DatePoint $client_dp
     * @param int $groups_count
     * @param int $slots_count
     * @return int
     */
    private function _stopDefault( DatePoint $client_dp, $groups_count, $slots_count )
    {
        return $slots_count >= 100 ? 2 : 0;
    }

    /**
     * Find start and end dates.
     */
    private function _prepareDates()
    {
        // Initial constraints in WP time zone.
        $now       = DatePoint::now();
        $min_start = $now->modify( Lib\Config::getMinimumTimePriorBooking() );
        $max_end   = $now->modify( Lib\Config::getMaximumAvailableDaysForBooking() . ' days midnight' );

        // Find start date.
        if ( $this->last_fetched_slot ) {
            // Set start date to the next day after last fetched slot.
            $this->client_start_dp = DatePoint::fromStr( $this->last_fetched_slot[0][2] )->toClientTz()->modify( 'tomorrow' );
        } else {
            // Requested date.
            $this->client_start_dp = DatePoint::fromStrInClientTz( $this->selected_date ?: $this->userData->getDateFrom() );
            if ( $this->show_calendar ) {
                $this->client_start_dp = $this->client_start_dp->modify( 'first day of this month midnight' );
            }
            if ( $this->client_start_dp->lt( $min_start ) ) {
                $this->client_start_dp = $min_start->toClientTz();
            }
        }

        // Find end date.
        $this->client_end_dp = $max_end->toClientTz();
        if ( $this->show_calendar ) {
            $client_next_month = $this->client_start_dp->modify( 'first day of next month midnight' );
            if ( $this->client_end_dp->gt( $client_next_month ) ) {
                $this->client_end_dp = $client_next_month;
            }
        }

        // Start and end dates in WP time zone.
        $this->start_dp = $this->client_start_dp->toWpTz();
        $this->end_dp   = $max_end;
    }

    /**
     * Prepare data for staff.
     */
    private function _prepareStaffData()
    {
        // Prepare staff IDs for each service.
        $staff_ids = array();
        foreach ( $this->userData->chain->getItems() as $chain_item ) {
            $compound_service_id = $chain_item->getService()->isCompound()
                ? $chain_item->getService()->getId()
                : null;
            $sub_services = $chain_item->getSubServices();
            foreach ( $sub_services as $sub_service ) {
                $_staff_ids = $chain_item->getStaffIdsForSubService( $sub_service );
                $service_id = $sub_service->getId();
                if ( ! isset ( $staff_ids[ $service_id ] ) ) {
                    $staff_ids[ $service_id ] = array();
                }
                $staff_ids[ $service_id ] = array_unique( array_merge( $staff_ids[ $service_id ], $_staff_ids ) );
                // Service schedule.
                if ( Lib\Config::serviceScheduleEnabled() ) {
                    $this->_prepareServiceSchedule( $compound_service_id, $service_id );
                }
            }
        }

        // Service price, capacity and preference rule for each staff member.
        $where = array();
        foreach ( $staff_ids as $service_id => $_staff_ids ) {
            $where[] = sprintf(
                'ss.service_id = %d AND ss.staff_id IN (%s)',
                $service_id,
                empty ( $_staff_ids ) ? 'NULL' : implode( ',', $_staff_ids )
            );
        }
        $rows = Lib\Entities\StaffService::query( 'ss' )
            ->select( 'ss.*, s.staff_preference, spo.position' )
            ->leftJoin( 'Service', 's', 's.id = ss.service_id' )
            ->leftJoin( 'StaffPreferenceOrder', 'spo', 'spo.service_id = ss.service_id AND spo.staff_id = ss.staff_id' )
            ->whereRaw( implode( ' OR ', $where ), array() )
            ->fetchArray();
        foreach ( $rows as $row ) {
            $staff_id = $row['staff_id'];
            if ( ! isset ( $this->staff[ $staff_id ] ) ) {
                $this->staff[ $staff_id ] = new Staff();
            }
            $this->staff[ $staff_id ]->addService(
                $row['service_id'],
                $row['price'],
                $row['capacity_min'],
                $row['capacity_max'],
                $row['staff_preference'],
                $row['position']
            );
        }

        // Holidays.
        $holidays = Lib\Entities\Holiday::query( 'h' )
            ->select( 'IF(h.repeat_event, DATE_FORMAT(h.date, \'%%m-%%d\'), h.date) as date, h.staff_id' )
            ->whereIn( 'h.staff_id', array( 1 ) )
            ->whereRaw( 'h.repeat_event = 1 OR h.date >= %s', array( $this->start_dp->format( 'Y-m-d' ) ) )
            ->fetchArray();
        foreach ( $holidays as $holiday ) {
            $this->staff[ $holiday['staff_id'] ]->getSchedule()->addHoliday( $holiday['date'] );
        }

        // Working schedule.
        $working_schedule = Lib\Entities\StaffScheduleItem::query( 'ssi' )
            ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
            ->leftJoin( 'ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
            ->whereIn( 'ssi.staff_id', array( 1 ) )
            ->whereNot( 'ssi.start_time', null )
            ->fetchArray();
        foreach ( $working_schedule as $item ) {
            $weekday  = $item['day_index'] - 1;
            $schedule = $this->staff[ $item['staff_id'] ]->getSchedule();
            if ( ! $schedule->hasDay( $weekday ) ) {
                $schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
            }
            if ( $item['break_start'] ) {
                $schedule->addBreak( $item['day_index'] - 1, $item['break_start'], $item['break_end'] );
            }
        }

        // Special days.
        $special_days = (array) Lib\Proxy\SpecialDays::getSchedule( array( 1 ), $this->start_dp->value(), $this->end_dp->value() );
        foreach ( $special_days as $day ) {
            $schedule = $this->staff[ $day['staff_id'] ]->getSchedule();
            if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
                $schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
            }
            if ( $day['break_start'] ) {
                $schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
            }
        }

        // Prepare padding_left for first service.
        $chain         = $this->userData->chain->getItems();
        $first_item    = $chain[0];
        $services      = $first_item->getSubServices();
        $first_service = $services[0];
        $padding_left  = $first_service->getPaddingLeft();

        // Take into account the statuses.
        $statuses = array(
            Lib\Entities\CustomerAppointment::STATUS_PENDING,
            Lib\Entities\CustomerAppointment::STATUS_APPROVED,
        );
        if ( Lib\Config::waitingListActive() ) {
            $statuses[] = Lib\Entities\CustomerAppointment::STATUS_WAITLISTED;
        }

        // Bookings.
        $bookings = Lib\Entities\Appointment::query( 'a' )
            ->select( '`a`.`id`,
                `a`.`location_id`,
                `a`.`staff_id`,
                `a`.`service_id`,
                `a`.`start_date`,
                DATE_ADD(`a`.`end_date`, INTERVAL `a`.`extras_duration` SECOND) AS `end_date`,
                `a`.`extras_duration`,
                0 AS `padding_left`,
                0 AS `padding_right`,
                SUM(`ca`.`number_of_persons`) AS `number_of_bookings`'
            )
            ->leftJoin( 'CustomerAppointment', 'ca', '`ca`.`appointment_id` = `a`.`id`' )
            ->leftJoin( 'Service', 's', '`s`.`id` = `a`.`service_id`' )
            ->whereIn( 'a.staff_id', array( 1 ) )
            ->whereRaw( sprintf( 'ca.status IN ("%s") OR ca.status IS NULL', implode('","', $statuses ) ), array() )
            ->whereRaw( 'DATE_ADD( `a`.`end_date`, INTERVAL (COALESCE(`s`.`padding_right`,0) + %d) SECOND) >= %s', array( $padding_left, $this->start_dp->format( 'Y-m-d' ) ) )
            ->groupBy( 'a.id' )
            ->fetchArray();
        foreach ( $bookings as $booking ) {
            $this->staff[ $booking['staff_id'] ]->addBooking( new Booking(
                $booking['location_id'],
                $booking['service_id'],
                $booking['number_of_bookings'],
                $booking['start_date'],
                $booking['end_date'],
                $booking['padding_left'],
                $booking['padding_right'],
                $booking['extras_duration'],
                false
            ) );
        }

        // Cart bookings.
        $this->handleCartBookings();

        // Google Calendar events.
        if ( get_option( 'bookly_gc_two_way_sync' ) ) {
            $query = Lib\Entities\Staff::query( 's' )
                ->whereIn( 's.id', array( 1 ) )
                ->whereNot( 'google_data', null );
            foreach ( $query->find() as $staff ) {
                $google = new Lib\Google();
                if ( $google->loadByStaff( $staff ) ) {
                    foreach ( $google->getCalendarEvents( $this->start_dp->value() ) ?: array() as $booking ) {
                        $this->staff[ $staff->getId() ]->addBooking( $booking );
                    }
                }
            }
        }
    }

    /**
     * Prepare service schedule.
     *
     * @param int $compound_service_id
     * @param int $service_id
     */
    private function _prepareServiceSchedule( $compound_service_id, $service_id )
    {
        if ( ! isset ( $this->service_schedule[ $compound_service_id ][ $service_id ] ) ) {
            $schedule = new Schedule();
            // Working schedule.
            $working_schedule = (array) Lib\Proxy\ServiceSchedule::getSchedule( $compound_service_id ?: $service_id );
            foreach ( $working_schedule as $item ) {
                $weekday = $item['day_index'] - 1;
                if ( ! $schedule->hasDay( $weekday ) ) {
                    $schedule->addDay( $weekday, $item['start_time'], $item['end_time'] );
                }
                if ( $item['break_start'] ) {
                    $schedule->addBreak( $weekday, $item['break_start'], $item['break_end'] );
                }
            }
            // Service special days.
            $special_days = (array) Lib\Proxy\SpecialDays::getServiceSchedule(
                $compound_service_id ?: $service_id,
                $this->start_dp->value(),
                $this->end_dp->value()
            );
            foreach ( $special_days as $day ) {
                if ( ! $schedule->hasSpecialDay( $day['date'] ) ) {
                    $schedule->addSpecialDay( $day['date'], $day['start_time'], $day['end_time'] );
                }
                if ( $day['break_start'] ) {
                    $schedule->addSpecialBreak( $day['date'], $day['break_start'], $day['break_end'] );
                }
            }
            // Add schedule to array.
            $this->service_schedule[ $compound_service_id ][ $service_id ] = $schedule;
        }
    }

    /**
     * Add cart items to staff bookings arrays.
     */
    public function handleCartBookings()
    {
        foreach ( $this->userData->cart->getItems() as $cart_key => $cart_item ) {
            if ( ! in_array( $cart_key, $this->userData->getEditCartKeys() ) ) {
                $extras_duration = (int) Lib\Proxy\ServiceExtras::getTotalDuration( $cart_item->getExtras() );
                foreach ( $cart_item->getSlots() as $slot ) {
                    list ( $service_id, $staff_id, $datetime ) = $slot;
                    if ( isset ( $this->staff[ $staff_id ] ) ) {
                        $service = Lib\Entities\Service::find( $service_id );
                        $range   = Range::fromDates( $datetime, $datetime );
                        $range   = $range->resize( $service->getDuration() + $extras_duration );
                        $extras_duration = 0;
                        $booking_exists = false;
                        foreach ( $this->staff[ $staff_id ]->getBookings() as $booking ) {
                            // If such booking exists increase number_of_bookings.
                            if ( $booking->isFromGoogle() == false
                                && $booking->getServiceId() == $service_id
                                && $booking->getRange()->wraps( $range )
                            ) {
                                $booking->incNop( $cart_item->getNumberOfPersons() );
                                $booking_exists = true;
                                break;
                            }
                        }
                        if ( ! $booking_exists ) {
                            // Add cart item to staff bookings array.
                            $this->staff[ $staff_id ]->addBooking( new Booking(
                                $cart_item->getLocationId(),
                                $service_id,
                                $cart_item->getNumberOfPersons(),
                                $range->start()->format( 'Y-m-d H:i:s' ),
                                $range->end()->format( 'Y-m-d H:i:s' ),
                                $service->getPaddingLeft(),
                                $service->getPaddingRight(),
                                $extras_duration,
                                false
                            ) );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get disabled days in Pickadate format.
     *
     * @return array
     */
    public function getDisabledDaysForPickadate()
    {
        $one_day = new \DateInterval( 'P1D' );
        $result = array();
        $date = new \DateTime( $this->selected_date ?: $this->userData->getDateFrom() );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int) $date->format( 'Y' );
        $n = (int) $date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int) $date->format( 'j' ) );
            }
            $date->add( $one_day );
        }

        return $result;
    }

    /**
     * Set last fetched slot.
     *
     * @param string $last_fetched_slot
     * @return $this
     */
    public function setLastFetchedSlot( $last_fetched_slot )
    {
        $slots = json_decode( $last_fetched_slot, true );
        $this->last_fetched_slot = array( $slots[0] );

        return $this;
    }

    /**
     * Set selected date.
     *
     * @param string $selected_date
     * @return $this
     */
    public function setSelectedDate( $selected_date )
    {
        $this->selected_date = $selected_date;

        return $this;
    }

    public function getSelectedDateForPickadate()
    {
        if ( $this->selected_date ) {
            foreach ( $this->slots as $group => $slots ) {
                if ( $group >= $this->selected_date ) {
                    return $group;
                }
            }

            if ( empty( $this->slots ) ) {
                return $this->selected_date;
            } else {
                reset( $this->slots );
                return key( $this->slots );
            }
        }

        if ( ! empty ( $this->slots ) ) {
            reset( $this->slots );
            return key( $this->slots );
        }

        return $this->userData->getDateFrom();
    }

    /**
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return bool
     */
    public function hasMoreSlots()
    {
        return $this->has_more_slots;
    }

    /**
     * Whether the first service in chain has duration in days.
     *
     * @return bool
     */
    public function isServiceDurationInDays()
    {
        return $this->srv_duration_days >= 1;
    }
}