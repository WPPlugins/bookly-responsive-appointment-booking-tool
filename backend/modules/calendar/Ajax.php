<?php
namespace Bookly\Backend\Modules\Calendar;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Calendar
 */
class Ajax extends Page
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'user' );
    }

    /**
     * Get data for FullCalendar.
     */
    public static function getStaffAppointments()
    {
        if ( Lib\Config::proActive() ) {
            $staff_members = Lib\Utils\Common::isCurrentUserSupervisor()
                ? Lib\Entities\Staff::query()->sortBy( 'position' )->whereNot( 'visibility', 'archive' )->find()
                : Lib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->whereNot( 'visibility', 'archive' )->find();
        } else {
            $staff_members = array( Lib\Entities\Staff::query()->findOne() );
        }
        $result        = array();
        $one_day       = new \DateInterval( 'P1D' );
        $start_date    = new \DateTime( self::parameter( 'start' ) );
        $end_date      = new \DateTime( self::parameter( 'end' ) );
        // FullCalendar sends end date as 1 day further.
        $end_date->sub( $one_day );

        if ( Lib\Utils\Common::isCurrentUserSupervisor() ) {
            $staff_ids = explode( ',', self::parameter( 'staff_ids' ) );
            foreach ( $staff_members as $id => $staff ) {
                if ( ! in_array( $staff->getId(), $staff_ids ) ) {
                    unset( $staff_members[ $id ] );
                }
            }
        } else {
            $staff_ids = array( $staff_members[0]->getId() );
        }
        // Load special days.
        $special_days = array();
        foreach ( (array) Lib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) as $day ) {
            $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
        }

        if ( ! Lib\Config::locationsActive() || self::parameter( 'location_ids' ) ) {
            foreach ( $staff_members as $staff ) {
                /** @var Lib\Entities\Staff $staff */
                $result = array_merge( $result, self::_getAppointmentsForFC( $staff->getId(), $start_date, $end_date ) );

                // Schedule.
                $items = $staff->getScheduleItems();
                $day   = clone $start_date;
                // Find previous day end time.
                $last_end = clone $day;
                $last_end->sub( $one_day );
                $w        = (int) $day->format( 'w' );
                $end_time = $items[ $w > 0 ? $w : 7 ]->getEndTime();
                if ( $end_time !== null ) {
                    $end_time = explode( ':', $end_time );
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    $last_end->setTime( 24, 0 );
                }
                // Do the loop.
                while ( $day <= $end_date ) {
                    $start = $last_end->format( 'Y-m-d H:i:s' );
                    // Check if $day is Special Day for current staff.
                    if ( isset( $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ] ) ) {
                        $sp_days = $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ];
                        $end     = $sp_days[0]['date'] . ' ' . $sp_days[0]['start_time'];
                        if ( $start < $end ) {
                            $result[] = array(
                                'start'     => $start,
                                'end'       => $end,
                                'rendering' => 'background',
                                'staffId'   => $staff->getId(),
                            );
                        }
                        // Breaks.
                        foreach ( $sp_days as $sp_day ) {
                            $break_start = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + Lib\Utils\DateTime::timeToSeconds( $sp_day['break_start'] )
                            );
                            $break_end   = date(
                                'Y-m-d H:i:s',
                                strtotime( $sp_day['date'] ) + Lib\Utils\DateTime::timeToSeconds( $sp_day['break_end'] )
                            );
                            $result[]    = array(
                                'start'     => $break_start,
                                'end'       => $break_end,
                                'rendering' => 'background',
                                'staffId'   => $staff->getId(),
                            );
                        }
                        $end_time = explode( ':', $sp_days[0]['end_time'] );
                        $last_end = clone $day;
                        $last_end->setTime( $end_time[0], $end_time[1] );
                    } else {
                        /** @var Lib\Entities\StaffScheduleItem $item */
                        $item = $items[ (int) $day->format( 'w' ) + 1 ];
                        if ( $item->getStartTime() && ! $staff->isOnHoliday( $day ) ) {
                            $end = $day->format( 'Y-m-d ' . $item->getStartTime() );
                            if ( $start < $end ) {
                                $result[] = array(
                                    'start'     => $start,
                                    'end'       => $end,
                                    'rendering' => 'background',
                                    'staffId'   => $staff->getId(),
                                );
                            }
                            $last_end = clone $day;
                            $end_time = explode( ':', $item->getEndTime() );
                            $last_end->setTime( $end_time[0], $end_time[1] );

                            // Breaks.
                            foreach ( $item->getBreaksList() as $break ) {
                                $break_start = date(
                                    'Y-m-d H:i:s',
                                    $day->getTimestamp() + Lib\Utils\DateTime::timeToSeconds( $break['start_time'] )
                                );
                                $break_end   = date(
                                    'Y-m-d H:i:s',
                                    $day->getTimestamp() + Lib\Utils\DateTime::timeToSeconds( $break['end_time'] )
                                );
                                $result[]    = array(
                                    'start'     => $break_start,
                                    'end'       => $break_end,
                                    'rendering' => 'background',
                                    'staffId'   => $staff->getId(),
                                );
                            }
                        } else {
                            $result[] = array(
                                'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                                'end'       => $day->format( 'Y-m-d 24:00:00' ),
                                'rendering' => 'background',
                                'staffId'   => $staff->getId(),
                            );
                            $last_end = clone $day;
                            $last_end->setTime( 24, 0 );
                        }
                    }

                    $day->add( $one_day );
                }

                if ( $last_end->format( 'H' ) != 24 ) {
                    $result[] = array(
                        'start'     => $last_end->format( 'Y-m-d H:i:s' ),
                        'end'       => $last_end->format( 'Y-m-d 24:00:00' ),
                        'rendering' => 'background',
                        'staffId'   => $staff->getId(),
                    );
                }
            }
        }

        wp_send_json( $result );
    }

    /**
     * Get appointments for FullCalendar.
     *
     * @param integer $staff_id
     * @param \DateTime $start_date
     * @param \DateTime $end_date
     * @return array
     */
    private static function _getAppointmentsForFC( $staff_id, \DateTime $start_date, \DateTime $end_date )
    {
        $query = Lib\Entities\Appointment::query( 'a' )
            ->where( 'st.id', $staff_id )
            ->whereBetween( 'DATE(a.start_date)', $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );

        Proxy\Shared::prepareAppointmentsQueryForFC( $query, $staff_id, $start_date, $end_date );

        return self::buildAppointmentsForFC( $staff_id, $query );
    }
}