<?php
namespace Bookly\Backend\Modules\Staff;

use Bookly\Lib;
use Bookly\Backend\Modules\Staff\Forms\Widgets\TimeChoice;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Staff
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        $permissions = get_option( 'bookly_gen_allow_staff_edit_profile' ) ? array( '_default' => 'user' ) : array();
        if ( Lib\Config::staffCabinetActive() ) {
            $permissions = array( '_default' => 'user' );
        }

        return $permissions;
    }

    /**
     * Create staff.
     */
    public static function createStaff()
    {
        if ( ! Lib\Config::proActive() && Lib\Entities\Staff::query()->count() >= 1 ) {
            wp_send_json_error();
        }
        $form = new Forms\StaffMemberNew();
        $form->bind( self::postParameters() );

        if ( $staff = $form->save() ) {
            wp_send_json_success( array( 'id' => $staff->getId(), 'name' => $staff->getFullName(), 'category' => $staff->getCategoryId() ) );
        }
    }

    /**
     * Update staff position.
     */
    public static function updateStaffPosition()
    {
        $data = self::parameter( 'data' );
        if ( isset( $data['staff'] ) ) {
            foreach ( $data['staff'] as $position => $staff_data ) {
                $staff = Lib\Entities\Staff::find( $staff_data['staff_id'] );
                $staff
                    ->setPosition( $position )
                    ->setCategoryId( $staff_data['category_id'] !== '' ? $staff_data['category_id'] : null )
                    ->save();
            }
        }
        if ( isset( $data['categories'] ) ) {
            Proxy\Pro::updateCategoriesPositions( $data['categories'] );
        }
        wp_send_json_success();
    }

    public static function updateStaffCategoriesFilter()
    {
        $category_id = self::parameter( 'category_id' ) ?: 0;
        $collapsed   = self::parameter( 'collapsed' );
        $filter      = (array) get_user_meta( get_current_user_id(), 'bookly_filter_staff_categories', true );
        if ( $collapsed ) {
            $filter[$category_id] = true;
            update_user_meta( get_current_user_id(), 'bookly_filter_staff_categories', $filter );
        } else {
            $filter[$category_id] = false;
            update_user_meta( get_current_user_id(), 'bookly_filter_staff_categories', $filter );
        }

        wp_send_json_success();
    }

    /**
     * Get staff services.
     */
    public static function getStaffServices()
    {
        $form        = new Forms\StaffServices();
        $staff_id    = self::parameter( 'staff_id' );
        $location_id = self::parameter( 'location_id' );

        $form->load( $staff_id, $location_id );
        $services_data = $form->getServicesData();

        $html = self::renderTemplate( 'services', compact( 'form', 'services_data', 'staff_id', 'location_id' ), false );
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Get staff schedule.
     */
    public static function getStaffSchedule()
    {
        $staff_id    = self::parameter( 'staff_id' );
        $location_id = self::parameter( 'location_id' );
        $staff       = new Lib\Entities\Staff();
        $staff->load( $staff_id );
        $schedule_items = $staff->getScheduleItems( $location_id );
        $html           = self::renderTemplate( 'schedule', compact( 'schedule_items', 'staff_id', 'location_id' ), false );
        $schedule       = (array) Proxy\Locations::getStaffSchedule( $staff_id, $location_id );
        wp_send_json_success( compact( 'html', 'schedule' ) );
    }

    /**
     * Update staff schedule.
     */
    public static function staffScheduleUpdate()
    {
        $form = new Forms\StaffSchedule();
        $form->bind( self::postParameters() );
        $form->save();

        Proxy\Shared::updateStaffSchedule( self::postParameters() );

        wp_send_json_success();
    }

    /**
     * Reset breaks.
     */
    public static function resetBreaks()
    {
        $breaks = self::parameter( 'breaks' );

        if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if ( self::parameter( 'staff_cabinet' ) && Lib\Config::staffCabinetActive() ) {
                    $allow = true;
                } else {
                    $allow = get_option( 'bookly_gen_allow_staff_edit_profile' );
                }
                if ( $allow ) {
                    $breaks = self::parameter( 'breaks' );
                    $staff = new Lib\Entities\Staff();
                    $staff->load( $breaks['staff_id'] );
                    if ( $staff->getWpUserId() == get_current_user_id() ) {
                        break;
                    }
                }
                do_action( 'admin_page_access_denied' );
                wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
            } while ( 0 );
        }

        $html_breaks = array();

        // Remove all breaks for staff member.
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->removeBreaksByStaffId( $breaks['staff_id'] );

        // Restore previous breaks.
        if ( isset( $breaks['breaks'] ) && is_array( $breaks['breaks'] ) ) {
            foreach ( $breaks['breaks'] as $day ) {
                $schedule_item_break = new Lib\Entities\ScheduleItemBreak();
                $schedule_item_break->setFields( $day );
                $schedule_item_break->save();
            }
        }

        $staff = new Lib\Entities\Staff();
        $staff->load( $breaks['staff_id'] );

        // Make array with breaks (html) for each day.
        foreach ( $staff->getScheduleItems() as $item ) {
            /** @var Lib\Entities\StaffScheduleItem $item */
            $html_breaks[ $item->getId() ] = self::renderTemplate( '_breaks', array(
                'day_is_not_available' => null === $item->getStartTime(),
                'item'                 => $item,
                'break_start'          => new TimeChoice( array( 'use_empty' => false, 'type' => 'break_from' ) ),
                'break_end'            => new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) ),
            ), false );
        }

        wp_send_json( $html_breaks );
    }

    /**
     * Handle staff schedule break.
     */
    public static function staffScheduleHandleBreak()
    {
        $start_time    = self::parameter( 'start_time' );
        $end_time      = self::parameter( 'end_time' );
        $working_start = self::parameter( 'working_start' );
        $working_end   = self::parameter( 'working_end' );

        if ( Lib\Utils\DateTime::timeToSeconds( $start_time ) >= Lib\Utils\DateTime::timeToSeconds( $end_time ) ) {
            wp_send_json_error( array( 'message' => __( 'The start time must be less than the end one', 'bookly' ), ) );
        }

        $res_schedule = new Lib\Entities\StaffScheduleItem();
        $res_schedule->load( self::parameter( 'staff_schedule_item_id' ) );

        $break_id = self::parameter( 'break_id', 0 );

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if ( ! $in_working_time || ! $res_schedule->isBreakIntervalAvailable( $start_time, $end_time, $break_id ) ) {
            wp_send_json_error( array( 'message' => __( 'The requested interval is not available', 'bookly' ), ) );
        }

        $formatted_start    = Lib\Utils\DateTime::formatTime( Lib\Utils\DateTime::timeToSeconds( $start_time ) );
        $formatted_end      = Lib\Utils\DateTime::formatTime( Lib\Utils\DateTime::timeToSeconds( $end_time ) );
        $formatted_interval = $formatted_start . ' - ' . $formatted_end;

        if ( $break_id ) {
            $break = new Lib\Entities\ScheduleItemBreak();
            $break->load( $break_id );
            $break->setStartTime( $start_time )
                ->setEndTime( $end_time )
                ->save();

            wp_send_json_success( array( 'interval' => $formatted_interval, ) );
        } else {
            $form = new Forms\StaffScheduleItemBreak();
            $form->bind( self::postParameters() );

            $res_schedule_break = $form->save();
            if ( $res_schedule_break ) {
                $breakStart = new TimeChoice( array( 'use_empty' => false, 'type' => 'break_from' ) );
                $breakEnd   = new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) );
                wp_send_json( array(
                    'success'      => true,
                    'item_content' => self::renderTemplate( '_break', array(
                        'staff_schedule_item_break_id' => $res_schedule_break->getId(),
                        'formatted_interval'           => $formatted_interval,
                        'break_start_choices'          => $breakStart->render( '', $start_time, array( 'class' => 'break-start form-control' ) ),
                        'break_end_choices'            => $breakEnd->render( '', $end_time, array( 'class' => 'break-end form-control' ) ),
                    ), false ),
                ) );
            } else {
                wp_send_json_error( array( 'message' => __( 'Error adding the break interval', 'bookly' ), ) );
            }
        }
    }

    /**
     * Delete staff schedule break.
     */
    public static function deleteStaffScheduleBreak()
    {
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->setId( self::parameter( 'id', 0 ) );
        $break->delete();

        wp_send_json_success();
    }

    /**
     * Update staff services.
     */
    public static function staffServicesUpdate()
    {
        $form = new Forms\StaffServices();
        $form->bind( self::postParameters() );
        $form->save();

        Proxy\Shared::updateStaffServices( self::postParameters() );

        wp_send_json_success();
    }

    /**
     * Edit staff.
     */
    public static function editStaff()
    {
        $form  = new Forms\StaffMember();
        $staff = new Lib\Entities\Staff();
        $staff->load( self::parameter( 'id' ) );

        $data = Proxy\Shared::editStaff(
            array( 'alert' => array( 'error' => array() ), 'tpl' => array() ),
            $staff
        );
        $tpl_data = $data['tpl'];

        $users_for_staff = Lib\Utils\Common::isCurrentUserAdmin() ? $form->getUsersForStaff( $staff->getId() ) : array();

        wp_send_json_success( array(
            'html'  => array(
                'edit'    => self::renderTemplate( 'edit', compact( 'staff' ), false ),
                'details' => self::renderTemplate(
                    '_details',
                    compact( 'staff', 'users_for_staff', 'tpl_data' ),
                    false
                ),
            ),
            'alert' => $data['alert'],
        ) );
    }

    /**
     * Update staff from POST request.
     */
    public static function updateStaff()
    {
        if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if ( self::parameter( 'staff_cabinet' ) && Lib\Config::staffCabinetActive() ) {
                    $allow = true;
                } else {
                    $allow = get_option( 'bookly_gen_allow_staff_edit_profile' );
                }
                if ( $allow ) {
                    $staff = Lib\Entities\Staff::find( self::parameter( 'id' ) );
                    if ( $staff->getWpUserId() == get_current_user_id() ) {
                        unset ( $_POST['wp_user_id'] );
                        break;
                    }
                }
                do_action( 'admin_page_access_denied' );
                wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
            } while ( 0 );
        }

        $params = self::postParameters();
        if ( ! $params['category_id'] ) {
            $params['category_id'] = null;
        }
        if ( ! $params['working_time_limit'] ) {
            $params['working_time_limit'] = null;
        }
        $form = new Forms\StaffMemberEdit();
        $form->bind( $params, $_FILES );

        Proxy\Shared::preUpdateStaff( $form->getObject(), $params );
        Proxy\Shared::updateStaff( $form->save(), $params );

        $wp_users = array();
        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $form     = new Forms\StaffMember();
            $wp_users = $form->getUsersForStaff();
        }

        wp_send_json_success( compact( 'wp_users' ) );
    }

    /**
     * 'Safely' remove staff (report if there are future appointments)
     */
    public static function deleteStaff()
    {
        $wp_users = array();

        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $staff_id = self::parameter( 'id' );

            if ( self::parameter( 'force_delete', false ) ) {
                if ( $staff = Lib\Entities\Staff::find( $staff_id ) ) {
                    $staff->delete();
                }

                $form = new Forms\StaffMember();
                $wp_users = $form->getUsersForStaff();
            } else {
                $appointment = Lib\Entities\Appointment::query( 'a' )
                    ->select( 'MAX(a.start_date) AS start_date' )
                    ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                    ->where( 'a.staff_id', $staff_id )
                    ->whereGt( 'a.start_date', current_time( 'mysql' ) )
                    ->whereIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                        Lib\Entities\CustomerAppointment::STATUS_PENDING,
                        Lib\Entities\CustomerAppointment::STATUS_APPROVED,
                    ) ) )
                    ->fetchRow();
                $filter_url = '';
                if ( $appointment['start_date'] ) {
                    $last_month = date_create( $appointment['start_date'] )->modify( 'last day of' )->format( 'Y-m-d' );
                    $action     = 'show_modal';
                    $filter_url = sprintf( '%s#staff=%d&appointment-date=%s-%s',
                        Lib\Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Appointments\Ajax::pageSlug() ),
                        $staff_id,
                        date_create( current_time( 'mysql' ) )->format( 'Y-m-d' ),
                        $last_month );
                    wp_send_json_error( compact( 'action', 'filter_url' ) );
                }
                $filter_url = Proxy\Shared::getAffectedAppointmentsFilter( $filter_url, $staff_id );
                if ( $filter_url ) {
                    $action     = 'show_modal';
                    wp_send_json_error( compact( 'action', 'filter_url' ) );
                } else {
                    $action = 'confirm';
                    wp_send_json_error( compact( 'action' ) );
                }
            }
        }

        wp_send_json_success( compact( 'wp_users' ) );
    }

    /**
     * Delete staff avatar.
     */
    public static function deleteStaffAvatar()
    {
        $staff = new Lib\Entities\Staff();
        $staff->load( self::parameter( 'id' ) );
        $staff->setAttachmentId( null );
        $staff->save();

        wp_send_json_success();
    }

    /**
     * Get staff holidays.
     */
    public static function staffHolidays()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $staff_id           = self::parameter( 'id', 0 );
        $holidays           = self::_getHolidays( $staff_id );
        $loading_img        = plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' );
        $start_of_week      = (int) get_option( 'start_of_week' );
        $days               = array_values( $wp_locale->weekday_abbrev );
        $months             = array_values( $wp_locale->month );
        $close              = __( 'Close', 'bookly' );
        $repeat             = __( 'Repeat every year', 'bookly' );
        $we_are_not_working = __( 'We are not working on this day', 'bookly' );
        $html               = self::renderTemplate( 'holidays', array(), false );
        wp_send_json_success( compact( 'html', 'holidays', 'days', 'months', 'start_of_week', 'loading_img', 'we_are_not_working', 'repeat', 'close' ) );
    }

    /**
     * Update staff holidays.
     */
    public static function staffHolidaysUpdate()
    {
        $id       = self::parameter( 'id' );
        $holiday  = self::parameter( 'holiday' ) == 'true';
        $repeat   = self::parameter( 'repeat' ) == 'true';
        $day      = self::parameter( 'day', false );
        $staff_id = self::parameter( 'staff_id' );
        if ( $staff_id ) {
            // Update or delete the event.
            if ( $id ) {
                if ( $holiday ) {
                    Lib\Entities\Holiday::query()
                        ->update()
                        ->set( 'repeat_event', (int) $repeat )
                        ->where( 'id', $id )
                        ->execute();
                } else {
                    Lib\Entities\Holiday::query()
                        ->delete()
                        ->where( 'id', $id )
                        ->execute();
                }
                // Add the new event.
            } elseif ( $holiday && $day ) {
                $holiday = new Lib\Entities\Holiday();
                $holiday
                    ->setDate( $day )
                    ->setRepeatEvent( (int) $repeat )
                    ->setStaffId( $staff_id )
                    ->save();
            }
            // And return refreshed events.
            echo json_encode( self::_getHolidays( $staff_id ) );
        }
        exit;
    }

    /**
     * Get holidays.
     *
     * @param int $staff_id
     * @return array
     */
    private static function _getHolidays( $staff_id )
    {
        $collection = Lib\Entities\Holiday::query( 'h' )->where( 'h.staff_id', $staff_id )->fetchArray();
        $holidays = array();
        foreach ( $collection as $holiday ) {
            list ( $Y, $m, $d ) = explode( '-', $holiday['date'] );
            $holidays[ $holiday['id'] ] = array(
                'm' => (int) $m,
                'd' => (int) $d,
            );
            // if not repeated holiday, add the year
            if ( ! $holiday['repeat_event'] ) {
                $holidays[ $holiday['id'] ]['y'] = (int) $Y;
            }
        }

        return $holidays;
    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
                $staff = new Lib\Entities\Staff();

                switch ( $action ) {
                    case 'editStaff':
                    case 'deleteStaffAvatar':
                    case 'staffSchedule':
                    case 'staffHolidays':
                    case 'updateStaff':
                    case 'getStaffDetails':
                        $staff->load( self::parameter( 'id' ) );
                        break;
                    case 'getStaffServices':
                    case 'getStaffSchedule':
                    case 'staffServicesUpdate':
                    case 'staffHolidaysUpdate':
                        $staff->load( self::parameter( 'staff_id' ) );
                        break;
                    case 'staffScheduleHandleBreak':
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( self::parameter( 'staff_schedule_item_id' ) );
                        $staff->load( $res_schedule->getStaffId() );
                        break;
                    case 'deleteStaffScheduleBreak':
                        $break = new Lib\Entities\ScheduleItemBreak();
                        $break->load( self::parameter( 'id' ) );
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( $break->getStaffScheduleItemId() );
                        $staff->load( $res_schedule->getStaffId() );
                        break;
                    case 'staffScheduleUpdate':
                        if ( self::hasParameter( 'days' ) ) {
                            foreach ( self::parameter( 'days' ) as $id => $day_index ) {
                                $res_schedule = new Lib\Entities\StaffScheduleItem();
                                $res_schedule->load( $id );
                                $staff = new Lib\Entities\Staff();
                                $staff->load( $res_schedule->getStaffId() );
                                if ( $staff->getWpUserId() != get_current_user_id() ) {
                                    return false;
                                }
                            }
                        }
                        break;
                    case 'resetBreaks':
                        $parameter = self::parameter( 'breaks' );
                        if ( $parameter && isset( $parameter['staff_id'] ) ) {
                            $staff->load( $parameter['staff_id'] );
                        }
                        break;
                    default:
                        return false;
                }

                return $staff->getWpUserId() == get_current_user_id();
            }

            return true;
        }

        return false;
    }
}