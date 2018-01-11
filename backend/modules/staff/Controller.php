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
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

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
            'module' => array( 'js/staff.js' => array( 'jquery-ui-sortable', 'jquery', 'jquery-ui-datepicker', 'bookly-range_tools.js' ) ),
        ) );

        wp_localize_script( 'bookly-staff.js', 'BooklyL10n', array(
            'are_you_sure' => __( 'Are you sure?', 'bookly' ),
            'close'        => __( 'Close', 'bookly' ),
            'days'         => array_values( $wp_locale->weekday_abbrev ),
            'months'       => array_values( $wp_locale->month ),
            'repeat'       => __( 'Repeat every year', 'bookly' ),
            'saved'        => __( 'Settings saved.', 'bookly' ),
            'selector'     => array( 'all_selected' => __( 'All locations', 'bookly' ), 'nothing_selected' => __( 'No locations selected', 'bookly' ), ),
            'intlTelInput' => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            ),
            'we_are_not_working' => __( 'We are not working on this day', 'bookly' ),
            'limitations' => __( '<b class="h4">This function is disabled in the Lite version of Bookly.</b><br><br>To get access to all Bookly features, lifetime free updates and 24/7 support, please upgrade to the Standard version of Bookly.<br>For more information visit', 'bookly' ) . ' <a href="http://booking-wp-plugin.com" target="_blank" class="alert-link">http://booking-wp-plugin.com</a>',
        ) );

        // Allow add-ons to enqueue their assets.
        do_action( 'bookly_enqueue_assets_for_staff_profile' );

        $active_staff_id = 1;
        $staff_members = Lib\Entities\Staff::query()->where( 'id', $active_staff_id )->fetchArray();

        // Check if this request is the request after google auth, set the token-data to the staff.
        if ( $this->hasParameter( 'code' ) ) {
            $google = new Lib\Google();
            $success_auth = $google->authCodeHandler( $this->getParameter( 'code' ) );

            if ( $success_auth ) {
                $staff = new Lib\Entities\Staff();
                $staff->load( 1 );
                $staff->set( 'google_data', null );
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
            $this->render( '_list_item', array( 'staff' => $staff->getFields() ) );
        }
        exit;
    }

    public function executeUpdateStaffPosition()
    {
        $staff_sorts = $this->getParameter( 'position' );
        foreach ( $staff_sorts as $position => $staff_id ) {
            $staff_sort = new Lib\Entities\Staff();
            $staff_sort->load( $staff_id );
            $staff_sort->set( 'position', $position );
            $staff_sort->save();
        }
    }

    public function executeStaffServices()
    {
        $form = new Forms\StaffServices();
        $staff_id   = 1;
        $form->load( $staff_id );
        $categories = $form->getCategories();
        $services_data = $form->getServicesData();
        $uncategorized_services = $form->getUncategorizedServices();

        $this->render( 'services', compact( 'categories', 'services_data', 'uncategorized_services', 'staff_id' ) );
        exit;
    }

    public function executeStaffSchedule()
    {
        $staff_id = 1;
        $staff = new Lib\Entities\Staff();
        $staff->load( $staff_id );
        $schedule_items = $staff->getScheduleItems();
        $this->render( 'schedule', compact( 'schedule_items', 'staff_id' ) );
        exit;
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
        $breaks = $this->getParameter( 'breaks' );

        // Remove all breaks for staff member.
        $break = new Lib\Entities\ScheduleItemBreak();
        $break->removeBreaksByStaffId( 1 );
        $html_breaks = array();

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
            $html_breaks[ $item->get( 'id' ) ] = $this->render( '_breaks', array(
                'day_is_not_available' => null === $item->get( 'start_time' ),
                'item'        => $item,
                'break_start' => new TimeChoice( array( 'use_empty' => false, 'type' => 'from' ) ),
                'break_end'   => new TimeChoice( array( 'use_empty' => false, 'type' => 'to' ) ),
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

        $staffScheduleItem = new Lib\Entities\StaffScheduleItem();
        $staffScheduleItem->load( $this->getParameter( 'staff_schedule_item_id' ) );

        $bound = array( $staffScheduleItem->get( 'start_time' ), $staffScheduleItem->get( 'end_time' ) );
        $break_id = $this->getParameter( 'break_id', 0 );

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if ( !$in_working_time || ! $staffScheduleItem->isBreakIntervalAvailable( $start_time, $end_time, $break_id ) ) {
            wp_send_json_error( array( 'message' => __( 'The requested interval is not available', 'bookly' ), ) );
        }

        $formatted_start    = Lib\Utils\DateTime::formatTime( Lib\Utils\DateTime::timeToSeconds( $start_time ) );
        $formatted_end      = Lib\Utils\DateTime::formatTime( Lib\Utils\DateTime::timeToSeconds( $end_time ) );
        $formatted_interval = $formatted_start . ' - ' . $formatted_end;

        if ( $break_id ) {
            $break = new Lib\Entities\ScheduleItemBreak();
            $break->load( $break_id );
            $break->set( 'start_time', $start_time );
            $break->set( 'end_time', $end_time );
            $break->save();

            wp_send_json_success( array( 'interval' => $formatted_interval, ) );
        } else {
            $form = new Forms\StaffScheduleItemBreak();
            $form->bind( $this->getPostParameters() );

            $staffScheduleItemBreak = $form->save();
            if ( $staffScheduleItemBreak ) {
                $breakStart = new TimeChoice( array( 'use_empty' => false, 'type' => 'from', 'bound' => $bound ) );
                $breakEnd   = new TimeChoice( array( 'use_empty' => false, 'type' => 'bound', 'bound' => $bound ) );
                wp_send_json( array(
                    'success'      => true,
                    'item_content' => $this->render( '_break', array(
                        'staff_schedule_item_break_id' => $staffScheduleItemBreak->get( 'id' ),
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
        $break->set( 'id', $this->getParameter( 'id', 0 ) );
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
        $calendar_list = array();
        $authUrl = null;
        $alert   = array( 'error' => array() );
        $form    = new Forms\StaffMemberEdit();
        $staff   = new Lib\Entities\Staff();
        $staff->load( 1 );

        if ( $staff->get( 'google_data' ) == '' ) {
            if ( get_option( 'bookly_gc_client_id' ) == '' ) {
                $authUrl = false;
            } else {
                $google  = new Lib\Google();
                $authUrl = $google->createAuthUrl( $this->getParameter( 'id' ) );
            }
        } else {
            $google = new Lib\Google();
            if ( $google->loadByStaff( $staff ) ) {
                $calendar_list = $google->getCalendarList();
            } else {
                foreach ( $google->getErrors() as $error ) {
                    $alert['error'][] = $error;
                }
            }
        }

        if ( $gc_errors = Lib\Session::get( 'staff_google_auth_error' ) ) {
            foreach ( (array) json_decode( $gc_errors, true ) as $error ) {
                $alert['error'][] = $error;
            }
            Lib\Session::destroy( 'staff_google_auth_error' );
        }

        $users_for_staff = Lib\Utils\Common::isCurrentUserAdmin() ? $form->getUsersForStaff( $staff->get( 'id' ) ) : array();

        wp_send_json_success( array(
            'html'  => $this->render( 'edit', compact( 'staff', 'users_for_staff', 'authUrl', 'calendar_list' ), false ),
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
                    if ( $staff->get( 'wp_user_id' ) == get_current_user_id() ) {
                        unset ( $_POST['wp_user_id'] );
                        break;
                    }
                }
                do_action( 'admin_page_access_denied' );
                wp_die( 'Bookly: ' . __( 'You do not have sufficient permissions to access this page.' ) );
            } while ( 0 );
        }
        $form = new Forms\StaffMemberEdit();
        $form->bind( $this->getPostParameters(), $_FILES );
        $employee = $form->save();

        do_action( 'bookly_staff_update', $this->getPostParameters() );

        if ( $employee === false && array_key_exists( 'google_calendar_error', $form->getErrors() ) ) {
            $errors = $form->getErrors();
            wp_send_json_error( array( 'error' => $errors['google_calendar_error'] ) );
        } else {
            $wp_users = array();
            if ( Lib\Utils\Common::isCurrentUserAdmin() ) {
                $form = new Forms\StaffMember();
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
        $staff->set( 'attachment_id', null );
        $staff->save();
        wp_send_json_success();
    }

    public function executeStaffHolidays()
    {
        $staff_id = $this->getParameter( 'id', 0 );
        $holidays = $this->getHolidays( $staff_id );
        $this->render( 'holidays', compact ( 'holidays', 'staff_id' ) );
        exit;
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
                $wpdb->insert( Lib\Entities\Holiday::getTableName(), array( 'date' => $day, 'repeat_event' => (int) $repeat, 'staff_id' => $staff_id ), array( '%s', '%d', '%d' ) );
            }

            // And return refreshed events.
            echo $this->getHolidays( $staff_id );
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

            if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) {
                $staff = new Lib\Entities\Staff();

                switch ( $action ) {
                    case 'executeEditStaff':
                    case 'executeDeleteStaffAvatar':
                    case 'executeStaffServices':
                    case 'executeStaffSchedule':
                    case 'executeStaffHolidays':
                    case 'executeUpdateStaff':
                        $staff->load( $this->getParameter( 'id' ) );
                        break;
                    case 'executeStaffServicesUpdate':
                    case 'executeStaffHolidaysUpdate':
                        $staff->load( $this->getParameter( 'staff_id' ) );
                        break;
                    case 'executeStaffScheduleHandleBreak':
                        $staffScheduleItem = new Lib\Entities\StaffScheduleItem();
                        $staffScheduleItem->load( $this->getParameter( 'staff_schedule_item_id' ) );
                        $staff->load( $staffScheduleItem->get( 'staff_id' ) );
                        break;
                    case 'executeDeleteStaffScheduleBreak':
                        $break = new Lib\Entities\ScheduleItemBreak();
                        $break->load( $this->getParameter( 'id' ) );
                        $staffScheduleItem = new Lib\Entities\StaffScheduleItem();
                        $staffScheduleItem->load( $break->get( 'staff_schedule_item_id' ) );
                        $staff->load( $staffScheduleItem->get( 'staff_id' ) );
                        break;
                    case 'executeStaffScheduleUpdate':
                        if ( $this->hasParameter( 'days' ) ) {
                            foreach ( $this->getParameter( 'days' ) as $id => $day_index ) {
                                $staffScheduleItem = new Lib\Entities\StaffScheduleItem();
                                $staffScheduleItem->load( $id );
                                $staff = new Lib\Entities\Staff();
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
     * Override parent method to add 'wp_ajax_bookly_' prefix
     * so current 'execute*' methods look nicer.
     *
     * @param string $prefix
     */
    protected function registerWpActions( $prefix = '' )
    {
        parent::registerWpActions( 'wp_ajax_bookly_' );
    }

}