<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_StaffController
 *
 * @property $form
 * @property $collection
 * @property $services
 * @property $staff_id
 * @property AB_Staff $staff
 */
class AB_StaffController extends AB_Controller {

    const page_slug = 'ab-system-staff';

    protected function getPermissions()
    {
        return get_option( 'ab_settings_allow_staff_members_edit_profile' ) ? array( '_this' => 'user' ) : array();
    }

    public function index()
    {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array(
                'css/intlTelInput.css',
                'css/ladda.min.css',
            ),
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/jCal.css',
            ),
            'module' => array(
                'css/staff.css'
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/ab_popup.js' => array( 'jquery' ),
                'js/jCal.js' => array( 'jquery' ),
            ),
            'module' => array(
                'js/staff.js' => array( 'jquery-ui-sortable', 'jquery' ),
            ),
            'frontend' => array(
                'js/intlTelInput.min.js' => array( 'jquery' ),
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
            )
        ) );

        wp_localize_script( 'ab-staff.js', 'BooklyL10n',  array(
            'are_you_sure'       => __( 'Are you sure?', 'bookly' ),
            'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            'repeat'             => __( 'Repeat every year', 'bookly' ),
            'months'             => array_values( $wp_locale->month ),
            'days'               => array_values( $wp_locale->weekday_abbrev ),
            'country'            => get_option( 'ab_settings_phone_default_country' ),
            'intlTelInput_utils' => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
        ) );

        $this->form = new AB_StaffMemberNewForm();

        $this->staff_members  =AB_Staff::query()->sortBy( 'position' )->limit(1)->fetchArray();

        $this->active_staff_id = 1;

        $this->render( 'list' );
    }

    public function executeCreateStaff()
    {
        $this->form = new AB_StaffMemberNewForm();
        $this->form->bind( $this->getPostParameters() );

        $staff = $this->form->save();
        if ( $staff ) {
            $this->render( 'list_item', array( 'staff' => $staff ) );
            // Register string for translate in WPML.
            do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $staff->get( 'id' ), $staff->get( 'full_name' ) );
        }
        exit;
    }

    public function executeUpdateStaffPosition()
    {
        $staff_sorts = $this->getParameter( 'position' );
        foreach ( $staff_sorts as $position => $staff_id ) {
            $staff_sort = new AB_Staff();
            $staff_sort->load( $staff_id );
            $staff_sort->set( 'position', $position );
            $staff_sort->save();
        }
    }

    public function executeStaffServices()
    {
        $this->form = new AB_StaffServicesForm();
        $this->form->load( 1 );
        $this->staff_id = 1;
        $this->render( 'services' );
        exit;
    }

    public function executeStaffSchedule()
    {
        $staff = new AB_Staff();
        $staff->load( 1 );
        $this->schedule_items = $staff->getScheduleItems();
        $this->staff_id       = 1;
        $this->render( 'schedule' );
        exit;
    }

    public function executeStaffScheduleUpdate()
    {
        $this->form = new AB_StaffScheduleForm();
        $this->form->bind( $this->getPostParameters() );
        $this->form->save();

        wp_send_json_success();
    }

    /**
     *
     * @throws Exception
     */
    public function executeResetBreaks()
    {
        $breaks = $this->getParameter( 'breaks' );

        // Remove all breaks for staff member.
        $break = new AB_ScheduleItemBreak();
        $break->removeBreaksByStaffId( 1 );
        $html_breaks = array();

        // Restore previous breaks.
        if ( isset( $breaks['breaks'] ) && is_array( $breaks['breaks'] ) ) {
            foreach ($breaks['breaks'] as $day) {
                $schedule_item_break = new AB_ScheduleItemBreak();
                $schedule_item_break->setFields($day);
                $schedule_item_break->save();
            }
        }

        $staff = new AB_Staff();
        $staff->load( 1 );

        // Make array with breaks (html) for each day.
        foreach ( $staff->getScheduleItems() as $item ) {
            /** @var AB_StaffScheduleItem $item */
            $html_breaks[ $item->get( 'id' )] = $this->render( '_breaks', array(
                'day_is_not_available' => null === $item->get( 'start_time' ),
                'item'                 => $item,
            ), false );
        }

        wp_send_json( $html_breaks );
    }

    public function executeStaffScheduleHandleBreak()
    {
        $start_time    = $this->getParameter( 'start_time' );
        $end_time      = $this->getParameter( 'end_time' );
        $working_start = $this->getParameter( 'working_start' );
        $working_end   = $this->getParameter( 'working_end' );

        if ( AB_DateTimeUtils::timeToSeconds( $start_time ) >= AB_DateTimeUtils::timeToSeconds( $end_time ) ) {
            wp_send_json( array(
                'success'   => false,
                'error_msg' => __( 'The start time must be less than the end one', 'bookly' ),
            ) );
        }

        $staffScheduleItem = new AB_StaffScheduleItem();
        $staffScheduleItem->load( $this->getParameter( 'staff_schedule_item_id' ) );

        $bound = array( $staffScheduleItem->get( 'start_time' ), $staffScheduleItem->get( 'end_time' ) );
        $break_id = $this->getParameter( 'break_id', 0 );

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if ( !$in_working_time || ! $staffScheduleItem->isBreakIntervalAvailable( $start_time, $end_time, $break_id ) ) {
            wp_send_json( array(
                'success'   => false,
                'error_msg' => __( 'The requested interval is not available', 'bookly' ),
            ) );
        }

        $formatted_start    = AB_DateTimeUtils::formatTime( AB_DateTimeUtils::timeToSeconds( $start_time ) );
        $formatted_end      = AB_DateTimeUtils::formatTime( AB_DateTimeUtils::timeToSeconds( $end_time ) );
        $formatted_interval = $formatted_start . ' - ' . $formatted_end;

        if ( $break_id ) {
            $break = new AB_ScheduleItemBreak();
            $break->load( $break_id );
            $break->set( 'start_time', $start_time );
            $break->set( 'end_time', $end_time );
            $break->save();

            wp_send_json( array(
                'success'      => true,
                'new_interval' => $formatted_interval,
            ) );
        } else {
            $this->form = new AB_StaffScheduleItemBreakForm();
            $this->form->bind( $this->getPostParameters() );

            $staffScheduleItemBreak = $this->form->save();
            if ( $staffScheduleItemBreak ) {
                $breakStart = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'from',  'bound' => $bound ) );
                $break_start_choices = $breakStart->render(
                    '',
                    $start_time,
                    array( 'class'              => 'break-start form-control',
                           'data-default_value' => AB_StaffScheduleItem::WORKING_START_TIME
                    )
                );
                $breakEnd = new AB_TimeChoiceWidget( array( 'use_empty' => false, 'type' => 'bound',  'bound' => $bound  ) );
                $break_end_choices = $breakEnd->render(
                    '',
                    $end_time,
                    array( 'class'              => 'break-end form-control',
                           'data-default_value' => date( 'H:i:s', strtotime( AB_StaffScheduleItem::WORKING_START_TIME . ' + 1 hour' ) )
                    )
                );
                wp_send_json( array(
                    'success'      => true,
                    'item_content' => $this->render( '_break', array(
                        'staff_schedule_item_break_id'  => $staffScheduleItemBreak->get( 'id' ),
                        'formatted_interval'            => $formatted_interval,
                        'break_start_choices'           => $break_start_choices,
                        'break_end_choices'             => $break_end_choices,
                    ), false),
                ) );
            } else {
                wp_send_json( array(
                    'success'   => false,
                    'error_msg' => __( 'Error adding the break interval', 'bookly' ),
                ) );
            }
        }
    }

    public function executeDeleteStaffScheduleBreak()
    {
        $break = new AB_ScheduleItemBreak();
        $break->load( 1 );
        $break->delete();

        wp_send_json_success();
    }

    public function executeStaffServicesUpdate()
    {
        $this->form = new AB_StaffServicesForm();
        $this->form->bind( $this->getPostParameters() );
        $this->form->save();

        wp_send_json_success();
    }

    public function executeEditStaff()
    {
        $this->form = new AB_StaffMemberEditForm();
        $this->staff = new AB_Staff();
        $this->staff->load( 1 );
        $staff_errors = array();

        $this->authUrl = false;
        // Register string for translate in WPML.
        do_action( 'wpml_register_single_string', 'bookly', 'staff_' . $this->staff->get( 'id' ), $this->staff->get( 'full_name' ) );

        $this->render( 'edit', array( 'staff_errors' => $staff_errors ) );
        exit;
    }

    /**
     * Update staff from POST request.
     * @see AB_Backend.php
     */
    public function updateStaff()
    {
        if ( ! AB_Utils::isCurrentUserAdmin() ) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if ( get_option( 'ab_settings_allow_staff_members_edit_profile' ) ) {
                    $staff = new AB_Staff();
                    $staff->load( 1 );
                    if ( $staff->get( 'wp_user_id' ) == get_current_user_id() ) {
                        unset ( $_POST['wp_user_id'] );
                        break;
                    }
                }
                do_action( 'admin_page_access_denied' );
                wp_die( __( 'Bookly: You do not have sufficient permissions to access this page.', 'bookly' ) );
            } while ( 0 );
        }

        $form = new AB_StaffMemberEditForm();
        $form->bind( $this->getPostParameters(), $_FILES );
        $result = $form->save();

        // Set staff id to load the form for.
        $this->active_staff_id = 1;

        if ( $result === false && array_key_exists( 'google_calendar', $form->getErrors() ) ) {
            $errors = $form->getErrors();
            $_SESSION['google_calendar_error'] = $errors['google_calendar'];
        } else {
            $_SESSION['bookly_updated'] = true;
        }
    }

    public function executeDeleteStaff()
    {
        $staff = new AB_Staff();
        $staff->load( $this->getParameter( 'id' ) );
        $staff->delete();
        $form = new AB_StaffMemberForm();
        wp_send_json( $form->getUsersForStaff() );
    }

    public function executeDeleteStaffAvatar()
    {
        $staff = new AB_Staff();
        $staff->load( 1 );
        if ( file_exists( $staff->get( 'avatar_path' ) ) ) {
            unlink( $staff->get( 'avatar_path' ) );
        }
        $staff->set( 'avatar_url', '' );
        $staff->set( 'avatar_path', '' );
        $staff->save();
        wp_send_json_success();
    }

    public function executeStaffHolidays()
    {
        $this->id = $this->getParameter( 'id', 0 );
        $this->holidays = $this->getHolidays( $this->id );
        $this->render( 'holidays' );
        exit;
    }

    public function executeStaffHolidaysUpdate()
    {
        $id         = 1;
        $holiday    = $this->getParameter( 'holiday' ) == 'true';
        $repeat     = $this->getParameter( 'repeat' ) == 'true';
        $day        = $this->getParameter( 'day', false );
        $staff_id   = $this->getParameter( 'staff_id' );

        if ( $staff_id ) {
            // Update or delete the event.
            if ( $id ) {
                if ( $holiday ) {
                    $this->getWpdb()->update( AB_Holiday::getTableName(), array( 'repeat_event' => intval( $repeat ) ), array( 'id' => $id ), array( '%d' ) );
                } else {
                    AB_Holiday::query()->delete()->where( 'id', $id )->execute();
                }
                // Add the new event.
            } elseif ( $holiday && $day ) {
                $this->getWpdb()->insert( AB_Holiday::getTableName(), array( 'date' => $day, 'repeat_event' => intval( $repeat ), 'staff_id' => $staff_id ), array( '%s', '%d', '%d' ) );
            }

            // And return refreshed events.
            echo $this->getHolidays( $staff_id );
        }
        exit;
    }

    // Protected methods.

    protected function getHolidays( $id )
    {
        $collection = AB_Holiday::query( 'h' )->where( 'h.staff_id', 1 )->fetchArray();
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[$holiday['id']] = array(
                    'm' => intval( date( 'm', strtotime( $holiday['date'] ) ) ),
                    'd' => intval( date( 'd', strtotime( $holiday['date'] ) ) ),
                    'title' => $holiday['title'],
                );
                // if not repeated holiday, add the year
                if ( ! $holiday['repeat_event'] ) {
                    $holidays[ $holiday['id'] ]['y'] = intval( date( 'Y', strtotime( $holiday['date'] ) ) );
                }
            }
        }

        return json_encode( $holidays );
    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {

            if ( ! AB_Utils::isCurrentUserAdmin() ) {
                $staff = new AB_Staff();

                switch ( $action ) {
                    case 'executeEditStaff':
                    case 'executeDeleteStaffAvatar':
                    case 'executeStaffServices':
                    case 'executeStaffSchedule':
                    case 'executeStaffHolidays':
                        $staff->load( 1 );
                        break;
                    case 'executeStaffServicesUpdate':
                    case 'executeStaffHolidaysUpdate':
                        $staff->load( $this->getParameter( 'staff_id' ) );
                        break;
                    case 'executeStaffScheduleHandleBreak':
                        $staffScheduleItem = new AB_StaffScheduleItem();
                        $staffScheduleItem->load( $this->getParameter( 'staff_schedule_item_id' ) );
                        $staff->load( $staffScheduleItem->get( 'staff_id' ) );
                        break;
                    case 'executeDeleteStaffScheduleBreak':
                        $break = new AB_ScheduleItemBreak();
                        $break->load( 1 );
                        $staffScheduleItem = new AB_StaffScheduleItem();
                        $staffScheduleItem->load( $break->get( 'staff_schedule_item_id' ) );
                        $staff->load( $staffScheduleItem->get( 'staff_id' ) );
                        break;
                    case 'executeStaffScheduleUpdate':
                        if ( $this->hasParameter( 'days' ) ) {
                            foreach ( $this->getParameter( 'days' ) as $id => $day_index ) {
                                $staffScheduleItem = new AB_StaffScheduleItem();
                                $staffScheduleItem->load( $id );
                                $staff = new AB_Staff();
                                $staff->load( $staffScheduleItem->get( 'staff_id' ) );
                                if ( $staff->get( 'wp_user_id' ) != get_current_user_id() ) {
                                    return false;
                                }
                            }
                        }
                        break;
                    default:
                        return false;
                }

                return $staff->get( 'wp_user_id' ) == get_current_user_id();
            }

            return true;
        }

        return false;
    }

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

}