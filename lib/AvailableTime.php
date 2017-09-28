<?php
namespace BooklyLite\Lib;

/**
 * Class AvailableTime
 * @package BooklyLite\Lib
 */
class AvailableTime
{
    public $params = array(
        'break_on_first_fetched_day' => false,  // find first available day only
        'exclude_staff_with_day_off' => false,  // exclude staff whose first searched day is off
    );

    private $last_fetched_slot = null;

    private $selected_date = null;

    private $has_more_slots = false;

    private $slots = array();

    /** @var UserBookingData */
    public $userData;
    /** @var \DateInterval */
    public $one_day;
    public $show_calendar;
    public $show_day_per_column;
    public $show_blocked_slots;
    public $show_cart;
    public $client_diff;
    public $current_timestamp;
    /** @var \DateTime */
    public $start_date;
    /** @var \DateTime */
    public $max_date;
    public $req_timestamp;
    public $staff_data = array();

    /**
     * Constructor.
     *
     * @param UserBookingData $userData
     */
    public function __construct( UserBookingData $userData )
    {
        $this->userData            = $userData;
        $this->one_day             = new \DateInterval( 'P1D' );
        $this->show_calendar       = Config::showCalendar();
        $this->show_day_per_column = Config::showDayPerColumn();
        $this->show_blocked_slots  = Config::showBlockedTimeSlots();
        $this->show_cart           = Config::showStepCart();
        $this->client_diff         = Config::useClientTimeZone()
            ? get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $userData->get( 'time_zone_offset' ) * 60
            : 0;
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
     * Load and init.
     *
     * @param array $params
     */
    public function load( array $params = array() )
    {
        // Override default params.
        $this->params = array_merge( $this->params, $params );

        /** @var ChainItem $chain_item */
        /** @var Slots $next_slots */
        $next_slots = null;
        foreach ( array_reverse( $this->userData->chain->getItems() ) as $chain_item ) {
            $extras_duration = apply_filters( 'bookly_service_extras_get_total_duration', 0, $chain_item->get( 'extras' ) );
            for ( $q = 0; $q < $chain_item->get( 'quantity' ); ++ $q ) {
                $sub_services = $chain_item->getSubServices();
                $last_key     = count( $sub_services ) - 1;
                foreach ( array_reverse( $sub_services ) as $key => $sub_service ) {
                    $slots = new Slots( $this, $sub_service, $chain_item->get( 'number_of_persons' ), $key == $last_key ? $extras_duration : 0 );
                    if ( $next_slots !== null ) {
                        $slots->next_slots = $next_slots;
                    }
                    $next_slots = $slots;
                }
            }
        }

        $slots->load();

        $this->slots = $slots->data;
        $this->has_more_slots = $slots->has_more_slots;
    }

    /**
     * Determine requested timestamp and start and max date.
     */
    private function _prepareDates()
    {
        $this->current_timestamp = (int) current_time( 'timestamp' ) + Config::getMinimumTimePriorBooking();

        if ( $this->last_fetched_slot ) {
            $this->start_date    = date_create( '@' . $this->last_fetched_slot[0][2] )->setTime( 0, 0, 0 );
            $this->req_timestamp = $this->start_date->getTimestamp();
            /** The last_fetched_slot is always in WP time zone @see \BooklyLite\Frontend\Modules\Booking\Controller::executeRenderNextTime() .
              * We increase it by 1 day to get the date to start with. */
            $this->start_date->add( $this->one_day );
        } else {
            $this->start_date = new \DateTime( $this->selected_date ?: $this->userData->get( 'date_from' ) );
            if ( $this->show_calendar ) {
                // Get slots for selected month.
                $this->start_date->modify( 'first day of this month' );
            }
            $this->req_timestamp = $this->start_date->getTimestamp();
            if ( Config::useClientTimeZone() ) {
                // The userData::date_from is in client's time zone so we need to check the previous day too
                // because some available slots can be found in the previous day due to time zone offset.
                $this->start_date->sub( $this->one_day );
            }
        }

        $this->max_date = date_create(
            '@' . ( (int) current_time( 'timestamp' ) + Config::getMaximumAvailableDaysForBooking() * DAY_IN_SECONDS )
        )->setTime( 0, 0 );
    }

    /**
     * Prepare data for staff.
     */
    private function _prepareStaffData()
    {
        // Prepare staff ids for each service.
        $ss_ids = array();
        foreach ( $this->userData->chain->getItems() as $chain_item ) {
            $sub_services = $chain_item->getSubServices();
            foreach ( $sub_services as $sub_service ) {
                $staff_ids  = $chain_item->getStaffIdsForSubService( $sub_service );
                $service_id = $sub_service->get( 'id' );
                if ( ! isset ( $ss_ids[ $service_id ] ) ) {
                    $ss_ids[ $service_id ] = array();
                }
                $ss_ids[ $service_id ] = array_unique( array_merge( $ss_ids[ $service_id ], $staff_ids ) );
            }
        }

        // Load service price and capacity for each staff member.
        $where = array();
        foreach ( $ss_ids as $service_id => $staff_ids ) {
            $where[] = sprintf(
                'service_id = %d AND staff_id IN (%s)',
                $service_id,
                empty ( $staff_ids ) ? 'NULL' : implode( ',', $staff_ids )
            );
        }
        $staff_services = Entities\StaffService::query()
            ->select( 'service_id, staff_id, price, capacity' )
            ->whereRaw( implode( ' OR ', $where ), array() )
            ->fetchArray();
        foreach ( $staff_services as $staff_service ) {
            $staff_id = 1;
            if ( ! isset ( $this->staff_data[ $staff_id ] ) ) {
                // Create initial data structure.
                $this->staff_data[ $staff_id ] = array(
                    'services'      => array(),
                    'holidays'      => array(),
                    'bookings'      => array(),
                    'working_hours' => array(),
                    'special_days'  => array(),
                );
            }
            $this->staff_data[ $staff_id ]['services'][ $staff_service['service_id'] ] = array(
                'price'    => $staff_service['price'],
                'capacity' => $staff_service['capacity'],
            );
        }

        // Load holidays.
        $holidays = Entities\Holiday::query( 'h' )
            ->select( 'IF(h.repeat_event, DATE_FORMAT(h.date, \'%%m-%%d\'), h.date) as date, h.staff_id' )
            ->where( 'h.staff_id', 1 )
            ->whereRaw( 'h.repeat_event = 1 OR h.date >= %s', array( $this->start_date->format( 'Y-m-d H:i:s' ) ) )
            ->fetchArray();
        foreach ( $holidays as $holiday ) {
            $this->staff_data[ $holiday['staff_id'] ]['holidays'][ $holiday['date'] ] = 1;
        }

        // Load working schedule.
        $working_schedule = Entities\StaffScheduleItem::query( 'ssi' )
            ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
            ->leftJoin( 'ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
            ->where( 'ssi.staff_id', 1 )
            ->whereNot( 'ssi.start_time', null )
            ->fetchArray();

        foreach ( $working_schedule as $item ) {
            if ( ! isset ( $this->staff_data[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] ) ) {
                $this->staff_data[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] = array(
                    'start_time' => Utils\DateTime::timeToSeconds( $item['start_time'] ),
                    'end_time'   => Utils\DateTime::timeToSeconds( $item['end_time'] ),
                    'breaks'     => array(),
                );
            }
            if ( $item['break_start'] ) {
                $this->staff_data[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ]['breaks'][] = array(
                    'start' => Utils\DateTime::timeToSeconds( $item['break_start'] ),
                    'end'   => Utils\DateTime::timeToSeconds( $item['break_end'] ),
                );
            }
        }

        // Load special days.
        $special_days = apply_filters( 'bookly_special_days_get_data_for_available_time', array(), array_keys( $this->staff_data ), $this->start_date, $this->max_date );

        foreach ( $special_days as $day ) {
            if ( ! isset ( $this->staff_data[ $day['staff_id'] ]['special_days'][ $day['date'] ] ) ) {
                $this->staff_data[ $day['staff_id'] ]['special_days'][ $day['date'] ] = array(
                    'start_time' => Utils\DateTime::timeToSeconds( $day['start_time'] ),
                    'end_time'   => Utils\DateTime::timeToSeconds( $day['end_time'] ),
                    'breaks'     => array(),
                );
            }
            if ( $day['break_start'] ) {
                $this->staff_data[ $day['staff_id'] ]['special_days'][ $day['date'] ]['breaks'][] = array(
                    'start' => Utils\DateTime::timeToSeconds( $day['break_start'] ),
                    'end'   => Utils\DateTime::timeToSeconds( $day['break_end'] ),
                );
            }
        }

        // Load bookings.
        $bookings = Entities\CustomerAppointment::query( 'ca' )
            ->select( '`a`.`id`,
                `a`.`staff_id`,
                `a`.`service_id`,
                `a`.`start_date`,
                DATE_ADD(`a`.`end_date`, INTERVAL `a`.`extras_duration` SECOND) AS `end_date`,
                `ca`.`extras`,
                COALESCE(`s`.`padding_left`,0) AS `padding_left`,
                COALESCE(`s`.`padding_right`,0) AS `padding_right`,
                SUM(`ca`.`number_of_persons`) AS `number_of_bookings`'
            )
            ->leftJoin( 'Appointment', 'a', '`a`.`id` = `ca`.`appointment_id`' )
            ->leftJoin( 'StaffService', 'ss', '`ss`.`staff_id` = `a`.`staff_id` AND `ss`.`service_id` = `a`.`service_id`' )
            ->leftJoin( 'Service', 's', '`s`.`id` = `a`.`service_id`' )
            ->whereNot( 'ca.status', Entities\CustomerAppointment::STATUS_CANCELLED )
            ->whereNot( 'ca.status', Entities\CustomerAppointment::STATUS_REJECTED )
            ->where( 'a.staff_id', 1 )
            ->whereGte( 'a.start_date', $this->start_date->format( 'Y-m-d' ) )
            ->groupBy( 'a.id' )
            ->fetchArray();
        foreach ( $bookings as $booking ) {
            $this->staff_data[ 1 ]['bookings'][] = array(
                'from_google'        => false,
                'service_id'         => $booking['service_id'],
                'start_time'         => strtotime( $booking['start_date'] ),
                'end_time'           => strtotime( $booking['end_date'] ),
                'padding_left'       => 0,
                'padding_right'      => 0,
                'number_of_bookings' => 1,
                'extras'             => $booking['extras'],
            );
        }

        // Handle cart bookings.
        if ( $this->show_cart ) {
            $this->handleCartBookings();
        }
    }

    /**
     * Add cart items to staff bookings arrays.
     */
    public function handleCartBookings()
    {
        foreach ( $this->userData->cart->getItems() as $cart_key => $cart_item ) {
            if ( ! in_array( $cart_key, $this->userData->get( 'edit_cart_keys' ) ) ) {
                $extras_duration = apply_filters( 'bookly_service_extras_get_total_duration', 0, $cart_item->get( 'extras' ) );
                foreach ( $cart_item->get( 'slots' ) as $slot ) {
                    list ( $service_id, $staff_id, $timestamp ) = $slot;
                    $service = Entities\Service::find( $service_id );
                    $s_time  = $timestamp;
                    $e_time  = $s_time + $service->get( 'duration' ) + $extras_duration;
                    $extras_duration = 0;
                    $booking_exists = false;
                    foreach ( $this->staff_data[ $staff_id ]['bookings'] as &$booking ) {
                        // If such booking exists increase number_of_bookings.
                        if ( $booking['from_google'] == false
                            && $booking['service_id'] == $service_id
                            && $booking['start_time'] == $s_time
                            && $booking['end_time'] >= $e_time
                        ) {
                            $booking['number_of_bookings'] += $cart_item->get( 'number_of_persons' );
                            $booking_exists = true;
                            break;
                        }
                    }
                    if ( ! $booking_exists ) {
                        // Add cart item to staff bookings array.
                        $this->staff_data[ $staff_id ]['bookings'][] = array(
                            'from_google'        => false,
                            'service_id'         => $service_id,
                            'start_time'         => $s_time,
                            'end_time'           => $e_time,
                            'padding_left'       => $service->get( 'padding_left' ),
                            'padding_right'      => $service->get( 'padding_right' ),
                            'number_of_bookings' => $cart_item->get( 'number_of_persons' ),
                            'extras'             => $cart_item->isFirstSubService( $service_id ) ? json_encode( $cart_item->get( 'extras' ) ) : '[]',
                        );
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
        $result = array();
        $date = new \DateTime( $this->selected_date ?: $this->userData->get( 'date_from' ) );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int) $date->format( 'Y' );
        $n = (int) $date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int) $date->format( 'j' ) );
            }
            $date->add( $this->one_day );
        }

        return $result;
    }

    public function setLastFetchedSlot( $last_fetched_slot )
    {
        $slots = json_decode( $last_fetched_slot, true );
        $this->last_fetched_slot = array( $slots[0] );
    }

    public function setSelectedDate( $selected_date )
    {
        $this->selected_date = $selected_date;
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

        return $this->userData->get( 'date_from' );
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
     * Whether the first service in chain is all day service.
     *
     * @return bool
     */
    public function isAllDayService()
    {
        $chain_items  = $this->userData->chain->getItems();
        $sub_services = $chain_items[0]->getSubServices();

        return $sub_services[0]->get( 'duration' ) == DAY_IN_SECONDS;
    }
}