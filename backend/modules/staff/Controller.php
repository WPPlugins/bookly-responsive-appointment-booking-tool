<?php
namespace BooklyLite\Backend\Modules\Staff;

use BooklyLite\Lib;
use BooklyLite\Backend\Modules\Staff\Forms\Widgets\TimeChoice;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Staff
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-staff';

    protected function getPermissions()
    {
        return get_option( 'bookly_gen_allow_staff_edit_profile' ) ? array( '_this' => 'user' ) : array();
    }

    public function index()
    {
        wp_enqueue_media();
        $this->enqueueStyles( array(
            'frontend' => array_merge(
                array( 'css/ladda.min.css', ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' )
            ),
            'backend'  => array( 'bootstrap/css/bootstrap-theme.min.css', 'css/jquery-ui-theme/jquery-ui.min.css' )
        ) );

        $this->enqueueScripts( array(
            'backend'  => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/jCal.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
                'js/range_tools.js' => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/spin.min.js'  => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'module' => array(
                'js/staff-details.js'  => array( 'bookly-alert.js', ),
                'js/staff-services.js' => array( 'bookly-staff-details.js' ),
                'js/staff-schedule.js' => array( 'bookly-staff-services.js' ),
                'js/staff-days-off.js' => array( 'bookly-staff-schedule.js' ),
                'js/staff.js'          => array( 'jquery-ui-sortable', 'jquery-ui-datepicker', 'bookly-range_tools.js', 'bookly-staff-days-off.js' ),
            ),
        ) );

        wp_localize_script( 'bookly-staff.js', 'BooklyL10n', array(
            'are_you_sure'      => __( 'Are you sure?', 'bookly' ),
            'saved'             => __( 'Settings saved.', 'bookly' ),
            'capacity_error'    => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'selector'          => array( 'all_selected' => __( 'All locations', 'bookly' ), 'nothing_selected' => __( 'No locations selected', 'bookly' ), ),
            'intlTelInput'      => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
            'csrf_token'        => Lib\Utils\Common::getCsrfToken(),
            'limitations'       => __( '<b class="h4">This function is not available in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        // Allow add-ons to enqueue their assets.
        Lib\Proxy\Shared::enqueueAssetsForStaffProfile();

        $active_staff_id = 1;
        $staff_members = Lib\Entities\Staff::query()->where( 'id', $active_staff_id )->fetchArray();

        // Check if this request is the request after google auth, set the token-data to the staff.
        if ( $this->hasParameter( 'code' ) ) {
            $google = new Lib\Google();
            $success_auth = $google->authCodeHandler( $this->getParameter( 'code' ) );

            if ( $success_auth ) {
                $staff = new Lib\Entities\Staff();
                $staff->load( 1 );
                $staff->setGoogleData( $google->getAccessToken() );
                $staff->save();

                exit ( '<script>location.href="' . Lib\Google::generateRedirectURI() . '&staff_id=1";</script>' );
            } else {
                Lib\Session::set( 'staff_google_auth_error', json_encode( $google->getErrors() ) );
            }
        }

        if ( $this->hasParameter( 'google_logout' ) ) {
            $google = new Lib\Google();
            $google->loadByStaffId( $active_staff_id );
            $google->logout();
        }
        $form = new Forms\StaffMemberEdit();
        $users_for_staff = $form->getUsersForStaff();

        $this->render( 'index', compact( 'staff_members', 'users_for_staff', 'active_staff_id' ) );
    }

    public function executeCreateStaff()
    {
        $staff = new Lib\Entities\Staff();
        if ( $staff ) {
            wp_send_json_success( array( 'html' => $this->render( '_list_item', array( 'staff' => $staff->getFields() ), false ) ) );
        }
    }

    public function executeUpdateStaffPosition()
    {
        $staff_sorts = $this->getParameter( 'position' );
        foreach ( $staff_sorts as $position => $staff_id ) {
            $staff_sort = new Lib\Entities\Staff();
            $staff_sort->load( $staff_id );
            $staff_sort->setPosition( $position );
            $staff_sort->save();
        }
    }

    public function executeGetStaffServices()
    {
        $form = new Forms\StaffServices();
        $staff_id   = 1;
        $form->load( $staff_id );
        $categories = $form->getCategories();
        $services_data = $form->getServicesData();
        $uncategorized_services = $form->getUncategorizedServices();

        $html = $this->render( 'services', compact( 'categories', 'services_data', 'uncategorized_services', 'staff_id' ), false );
        wp_send_json_success( compact( 'html' ) );
    }

    public function executeGetStaffSchedule()
    {
        $staff_id = 1;
        $staff    = new Lib\Entities\Staff();
        $staff->load( $staff_id );
        $schedule_items = $staff->getScheduleItems();
        $html = $this->render( 'schedule', compact( 'schedule_items', 'staff_id' ), false );
        wp_send_json_success( compact( 'html' ) );
    }

    public function executeStaffScheduleUpdate()
    {
        $form = new Forms\StaffSchedule();
        $form->bind( $this->getPostParameters() );
        $form->save();
        wp_send_json_success();
    }

    /**
     *
     * @throws \Exception
     */
    public function executeResetBreaks()
    {
        $breaks      = $this->getParameter( 'breaks' );
        $html_breaks = array();

        // Remove all breaks for staff member.
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->removeBreaksByStaffId( 1 );

        // Restore previous breaks.
        if ( isset( $breaks['breaks'] ) && is_array( $breaks['breaks'] ) ) {
            foreach ( $breaks['breaks'] as $day ) {
                $schedule_item_break = new Lib\Entities\ScheduleItemBreak();
                $schedule_item_break->setFields( $day );
                $schedule_item_break->save();
            }
        }

        $staff = new Lib\Entities\Staff();
        $staff->load( 1 );

        // Make array with breaks (html) for each day.
        foreach ( $staff->getScheduleItems() as $item ) {
            /** @var Lib\Entities\StaffScheduleItem $item */
            $html_breaks[ $item->getId() ] = $this->render( '_breaks', array(
                'day_is_not_available' => null === $item->getStartTime(),
                'item'                 => $item,
                'break_start'          => new TimeChoice( array( 'use_empty' => false, 'type' => 'break_from' ) ),
                'break_end'            => new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) ),
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

        if ( Lib\Utils\DateTime::timeToSeconds( $start_time ) >= Lib\Utils\DateTime::timeToSeconds( $end_time ) ) {
            wp_send_json_error( array( 'message' => __( 'The start time must be less than the end one', 'bookly' ), ) );
        }

        $res_schedule = new Lib\Entities\StaffScheduleItem();
        $res_schedule->load( $this->getParameter( 'staff_schedule_item_id' ) );

        $break_id = $this->getParameter( 'break_id', 0 );

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
            $form->bind( $this->getPostParameters() );

            $res_schedule_break = $form->save();
            if ( $res_schedule_break ) {
                $breakStart = new TimeChoice( array( 'use_empty' => false, 'type' => 'break_from' ) );
                $breakEnd   = new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) );
                wp_send_json( array(
                    'success'      => true,
                    'item_content' => $this->render( '_break', array(
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

    public function executeDeleteStaffScheduleBreak()
    {
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->setId( $this->getParameter( 'id', 0 ) );
        $break->delete();

        wp_send_json_success();
    }

    public function executeStaffServicesUpdate()
    {
        $form = new Forms\StaffServices();
        $form->bind( $this->getPostParameters() );
        $form->save();
        wp_send_json_success();
    }

    public function executeEditStaff()
    {
        $alert   = array( 'error' => array() );
        $staff   = new Lib\Entities\Staff();
        $staff->load( 1 );

        if ( $gc_errors = Lib\Session::get( 'staff_google_auth_error' ) ) {
            foreach ( (array) json_decode( $gc_errors, true ) as $error ) {
                $alert['error'][] = $error;
            }
            Lib\Session::destroy( 'staff_google_auth_error' );
        }

        $google_calendars = array();
        $authUrl = null;
        $form  = new Forms\StaffMemberEdit();
        if ( $staff->getGoogleData() == '' ) {
            if ( get_option( 'bookly_gc_client_id' ) == '' ) {
                $authUrl = false;
            } else {
                $google  = new Lib\Google();
                $authUrl = $google->createAuthUrl( $this->getParameter( 'id' ) );
            }
        } else {
            $google = new Lib\Google();
            if ( $google->loadByStaff( $staff ) ) {
                $google_calendars = $google->getCalendarList();
            } else {
                foreach ( $google->getErrors() as $error ) {
                    $alert['error'][] = $error;
                }
            }
        }

        $users_for_staff = Lib\Utils\Common::isCurrentUserAdmin() ? $form->getUsersForStaff( $staff->getId() ) : array();

        wp_send_json_success( array(
            'html'   => array(
                'edit'    => $this->render( 'edit', compact( 'staff' ), false ),
                'details' => $this->render( '_details', compact( 'staff', 'authUrl', 'google_calendars', 'users_for_staff' ), false )
            ),
            'alert' => $alert,
        ) );
    }


    /**
     * Update staff from POST request.
     */
    public function executeUpdateStaff()
    {
        if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
            // Check permissions to prevent one staff member from updating profile of another staff member.
            do {
                if ( get_option( 'bookly_gen_allow_staff_edit_profile' ) ) {
                    $staff = new Lib\Entities\Staff();
                    $staff->load( $this->getParameter( 'id' ) );
                    if ( $staff->getWpUserId() == get_current_user_id() ) {
                        unset ( $_POST['wp_user_id'] );
                        break;
                    }
                }
                do_action( 'admin_page_access_denied' );
                wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
            } while ( 0 );
        }
        $form  = new Forms\StaffMemberEdit();

        $form->bind( $this->getPostParameters(), $_FILES );
        $employee = $form->save();

        Lib\Proxy\Shared::updateStaff( $this->getPostParameters() );

        if ( $employee === false && array_key_exists( 'google_calendar_error', $form->getErrors() ) ) {
            $errors = $form->getErrors();
            wp_send_json_error( array( 'error' => $errors['google_calendar_error'] ) );
        } else {
            $wp_users = array();
            if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
                $form     = new Forms\StaffMember();
                $wp_users = $form->getUsersForStaff();
            }

            wp_send_json_success( compact( 'wp_users' ) );
        }
    }

    public function executeDeleteStaff()
    {
        $wp_users = array();

        if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
            $form = new Forms\StaffMember();
            $wp_users = $form->getUsersForStaff();
        }

        wp_send_json_success( compact( 'wp_users' ) );
    }

    public function executeDeleteStaffAvatar()
    {
        $staff = new Lib\Entities\Staff();
        $staff->load( 1 );
        $staff->setAttachmentId( null );
        $staff->save();

        wp_send_json_success();
    }

    public function executeStaffHolidays()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $staff_id           = 1;
        $holidays           = $this->getHolidays( $staff_id );
        $loading_img        = plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' );
        $start_of_week      = (int) get_option( 'start_of_week' );
        $days               = array_values( $wp_locale->weekday_abbrev );
        $months             = array_values( $wp_locale->month );
        $close              = __( 'Close', 'bookly' );
        $repeat             = __( 'Repeat every year', 'bookly' );
        $we_are_not_working = __( 'We are not working on this day', 'bookly' );
        $html               = $this->render( 'holidays', array(), false );
        wp_send_json_success( compact( 'html', 'holidays', 'days', 'months', 'start_of_week', 'loading_img', 'we_are_not_working', 'repeat', 'close' ) );
    }

    public function executeStaffHolidaysUpdate()
    {
        global $wpdb;

        $id       = $this->getParameter( 'id' );
        $holiday  = $this->getParameter( 'holiday' ) == 'true';
        $repeat   = $this->getParameter( 'repeat' ) == 'true';
        $day      = $this->getParameter( 'day', false );
        $staff_id = 1;
        if ( $staff_id ) {
            // Update or delete the event.
            if ( $id ) {
                if ( $holiday ) {
                    $wpdb->update( Lib\Entities\Holiday::getTableName(), array( 'repeat_event' => (int) $repeat ), array( 'id' => $id ), array( '%d' ) );
                } else {
                    Lib\Entities\Holiday::query()->delete()->where( 'id', $id )->execute();
                }
                // Add the new event.
            } elseif ( $holiday && $day ) {
                $wpdb->insert( Lib\Entities\Holiday::getTableName(), array( 'date' => $day, 'repeat_event' => (int) $repeat, 'staff_id' => 1 ), array( '%s', '%d', '%d' ) );
            }
            // And return refreshed events.
            echo json_encode( $this->getHolidays( $staff_id ) );
        }
        exit;
    }

    // Protected methods.

    protected function getHolidays( $staff_id )
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
    protected function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
                $staff = new Lib\Entities\Staff();

                switch ( $action ) {
                    case 'executeEditStaff':
                    case 'executeDeleteStaffAvatar':
                    case 'executeStaffSchedule':
                    case 'executeStaffHolidays':
                    case 'executeUpdateStaff':
                    case 'executeGetStaffDetails':
                        $staff->load( $this->getParameter( 'id' ) );
                        break;
                    case 'executeGetStaffServices':
                    case 'executeGetStaffSchedule':
                    case 'executeStaffServicesUpdate':
                    case 'executeStaffHolidaysUpdate':
                        $staff->load( $this->getParameter( 'staff_id' ) );
                        break;
                    case 'executeStaffScheduleHandleBreak':
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( $this->getParameter( 'staff_schedule_item_id' ) );
                        $staff->load( $res_schedule->getStaffId() );
                        break;
                    case 'executeDeleteStaffScheduleBreak':
                        $break = new Lib\Entities\ScheduleItemBreak();
                        $break->load( $this->getParameter( 'id' ) );
                        $res_schedule = new Lib\Entities\StaffScheduleItem();
                        $res_schedule->load( $break->getStaffScheduleItemId() );
                        $staff->load( $res_schedule->getStaffId() );
                        break;
                    case 'executeStaffScheduleUpdate':
                        if ( $this->hasParameter( 'days' ) ) {
                            foreach ( $this->getParameter( 'days' ) as $id => $day_index ) {
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