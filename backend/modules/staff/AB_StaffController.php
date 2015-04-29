<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_StaffMemberNewForm.php';
include 'forms/AB_StaffMemberEditForm.php';
include 'forms/AB_StaffServicesForm.php';
include 'forms/AB_StaffScheduleForm.php';
include 'forms/AB_StaffScheduleItemBreakForm.php';
include 'forms/widget/AB_TimeChoiceWidget.php';

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

    public function renderStaffMembers() {
        $path = dirname( __DIR__ );
        wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', $path ) );
        wp_enqueue_style( 'ab-staff', plugins_url( 'resources/css/staff.css', __FILE__ ) );
        wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', $path ) );
        wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', $path ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-popup', plugins_url( 'resources/js/ab_popup.js', $path ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-system-staff', plugins_url( 'resources/js/staff.js', __FILE__ ), array( 'jquery' ) );
        wp_enqueue_script( 'ab-jCal', plugins_url( 'resources/js/jCal.js', $path ), array( 'jquery' ) );
        wp_enqueue_style( 'ab-jCal', plugins_url( 'resources/css/jCal.css', $path ) );
        wp_localize_script( 'ab-jCal', 'BooklyL10n',  array(
            'we_are_not_working' => __( 'We are not working on this day', 'ab' ),
            'repeat'             => __( 'Repeat every year', 'ab' ),
            'month'              => array(
                'January'    => __( 'January', 'ab' ),
                'February'   => __( 'February', 'ab' ),
                'March'      => __( 'March', 'ab' ),
                'April'      => __( 'April', 'ab' ),
                'May'        => __( 'May', 'ab' ),
                'June'       => __( 'June', 'ab' ),
                'July'       => __( 'July', 'ab' ),
                'August'     => __( 'August', 'ab' ),
                'September'  => __( 'September', 'ab' ),
                'October'    => __( 'October', 'ab' ),
                'November'   => __( 'November', 'ab' ),
                'December'   => __( 'December', 'ab' )
            ),
            'day'                => array(
                'Mon'        => __( 'Mon', 'ab' ),
                'Tue'        => __( 'Tue', 'ab' ),
                'Wed'        => __( 'Wed', 'ab' ),
                'Thu'        => __( 'Thu', 'ab' ),
                'Fri'        => __( 'Fri', 'ab' ),
                'Sat'        => __( 'Sat', 'ab' ),
                'Sun'        => __( 'Sun', 'ab' )
            )
        ) );

        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style("wp-jquery-ui-dialog");

        $this->form = new AB_StaffMemberNewForm();
        $this->collection = $this->getWpdb()->get_results( "SELECT * FROM ab_staff LIMIT 1" );
        if ( !isset ( $this->active_staff_id ) ) {
            if ( $this->hasParameter( 'staff_id' ) ) {
                $this->active_staff_id = $this->getParameter( 'staff_id' );
            }
            else {
                $this->active_staff_id = $this->collection ? $this->collection[0]->id : 0;
            }
        }

        $this->render( 'list' );
    }

    public function executeStaffServices() {
        $this->form = new AB_StaffServicesForm();
        $this->form->load( 1 );
        $this->staff_id = 1;
        $this->render( 'services' );
        exit;
    }

    public function executeStaffSchedule() {
        $staff = new AB_Staff();
        $staff->load( 1 );
        $this->schedule_list = $staff->getScheduleList();
        $this->staff_id      = 1;
        $this->render( 'schedule' );
        exit;
    }

    public function executeStaffScheduleUpdate() {
        $this->form = new AB_StaffScheduleForm();
        $this->form->bind( $this->getPostParameters() );
        $this->form->save();
        exit;
    }

    /**
     *
     * @throws Exception
     */
    public function executeResetBreaks() {
        global $wpdb;
        $breaks = $this->getParameter( 'breaks' );

        // remove all breaks for staff member
        $break = new AB_ScheduleItemBreak();
        $break->removeBreaksByStaffId( $breaks[ 'staff_id' ] );
        $html_breaks = array();

        // restore previous breaks
        if (isset($breaks['breaks']) && is_array($breaks['breaks'])) {
            $query = "INSERT INTO ab_schedule_item_break (staff_schedule_item_id, start_time, end_time) VALUES ";

            foreach ($breaks['breaks'] as $day) {
                $query .= "($day[staff_schedule_item_id], '$day[start]', '$day[end]'), ";
            }
            $query = rtrim($query, ", ");
            $wpdb->get_results($query);
        }

        $staff = new AB_Staff();
        $staff->load( $breaks['staff_id'] );

        // make array with breaks (html) for each day
        foreach ($staff->getScheduleList() as $list_item) {
            $html_breaks[$list_item->id] = $this->render("_breaks", array(
                'day_is_not_available' => null === $list_item->start_time,
                'list_item' => $list_item,
                'time_format' => get_option( 'time_format' ),
            ), false);
        }

        echo json_encode($html_breaks);
        exit();
    }

    public function executeStaffScheduleHandleBreak() {
        $start_time    = $this->getParameter( 'start_time' );
        $end_time      = $this->getParameter( 'end_time' );
        $working_start = $this->getParameter( 'working_start' );
        $working_end   = $this->getParameter( 'working_end' );

        if ( strtotime( date( 'Y-m-d ' . $start_time ) ) >= strtotime( date( 'Y-m-d ' . $end_time ) ) ) {
            echo json_encode( array(
                'success'   => false,
                'error_msg' => __( 'The start time must be less than the end one', 'ab'),
            ) );
            exit;
        }

        $staffScheduleItem = new AB_StaffScheduleItem();
        $staffScheduleItem->load( $this->getParameter( 'staff_schedule_item_id' ) );

        $break_id = $this->getParameter( 'break_id', 0 );

        $in_working_time = $working_start <= $start_time && $start_time <= $working_end
            && $working_start <= $end_time && $end_time <= $working_end;
        if ( !$in_working_time || ! $staffScheduleItem->isBreakIntervalAvailable( $start_time, $end_time, $break_id ) ) {
            echo json_encode( array(
                'success'   => false,
                'error_msg' => __( 'The requested interval is not available', 'ab'),
            ) );
            exit;
        }

        $time_format              = get_option( 'time_format' );
        $formatted_interval_start = date_i18n( $time_format, strtotime( $start_time ) );
        $formatted_interval_end   = date_i18n( $time_format, strtotime( $end_time ) );
        $formatted_interval       = $formatted_interval_start . ' - ' . $formatted_interval_end;

        if ( $break_id ) {
            $break = new AB_ScheduleItemBreak();
            $break->load( $break_id );
            $break->set( 'start_time', $start_time );
            $break->set( 'end_time', $end_time );
            $break->save();

            echo json_encode( array(
                'success'      => true,
                'new_interval' => $formatted_interval,
            ) );
        } else {
            $this->form = new AB_StaffScheduleItemBreakForm();
            $this->form->bind( $this->getPostParameters() );

            $staffScheduleItemBreak = $this->form->save();
            if ( $staffScheduleItemBreak ) {
                $breakStart = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                $break_start_choices = $breakStart->render(
                    '',
                    $start_time,
                    array(
                        'class'              => 'break-start',
                        'data-default_value' => AB_StaffScheduleItem::WORKING_START_TIME
                    )
                );
                $breakEnd = new AB_TimeChoiceWidget( array( 'use_empty' => false ) );
                $break_end_choices = $breakEnd->render(
                    '',
                    $end_time,
                    array(
                        'class'              => 'break-end',
                        'data-default_value' => date( 'H:i:s', strtotime( AB_StaffScheduleItem::WORKING_START_TIME . ' + 1 hour' ) )
                    )
                );
                echo json_encode(array(
                    'success'      => true,
                    'item_content' => '<div class="break-interval-wrapper" data-break_id="' . $staffScheduleItemBreak->get( 'id' ) . '">
                                          <div class="ab-popup-wrapper hide-on-non-working-day">
                                             <a class="ab-popup-trigger break-interval" href="javascript:void(0)">' . $formatted_interval . '</a>
                                             <div class="ab-popup" style="display: none">
                                                 <div class="ab-arrow"></div>
                                                 <div class="error" style="display: none"></div>
                                                 <div class="ab-content">
                                                     <table cellspacing="0" cellpadding="0">
                                                         <tr>
                                                             <td>' . $break_start_choices . ' <span class="hide-on-non-working-day">' . __( 'to', 'ab') . '</span> ' . $break_end_choices . '</td>
                                                         </tr>
                                                         <tr>
                                                             <td>
                                                                 <a class="btn btn-info ab-popup-save ab-save-break">' . __('Save break','ab') . '</a>
                                                                 <a class="ab-popup-close" href="#">' . __('Cancel', 'ab') . '</a>
                                                             </td>
                                                         </tr>
                                                     </table>
                                                     <a class="ab-popup-close ab-popup-close-icon" href="javascript:void(0)"></a>
                                                  </div>
                                              </div>
                                          </div>
                                          <img class="delete-break" src="' . plugins_url( 'backend/resources/images/delete_cross.png', AB_PATH . '/main.php' ) . '" />
                                       </div>'
                ) );
            } else {
                echo json_encode( array(
                    'success'   => false,
                    'error_msg' => __( 'Error adding the break interval', 'ab'),
                ) );
            }
        }

        exit;
    }

    public function executeDeleteStaffScheduleBreak() {
        $break = new AB_ScheduleItemBreak();
        $break->load( 1 );
        $break->delete();
        exit;
    }

    public function executeStaffServicesUpdate() {
        $this->form = new AB_StaffServicesForm();
        $this->form->bind( $this->getPostParameters() );
        $this->form->save();
        exit;
    }

    public function executeEditStaff() {
        $this->form = new AB_StaffMemberEditForm();
        $this->staff = new AB_Staff();
        $this->staff->load( 1 );
        $staff_errors = array();

        if ( isset( $_SESSION['was_update'] ) ) {
            unset($_SESSION['was_update']);
            $this->update = true;
        }

        $this->render('edit', array(
            'staff_errors' => $staff_errors
        ));
        exit;
    }

    public function updateStaff() {
        $form = new AB_StaffMemberEditForm();
        $form->bind( $this->getPostParameters(), $_FILES );
        $result = $form->save();

        $this->active_staff_id = 1;

        if ($result === false){

        }else{
            $_SESSION['was_update'] = true;
        }
    }

    public function executeDeleteStaffAvatar() {
        $staff = new AB_Staff();
        $staff->load( 1 );
        unlink( $staff->get( 'avatar_path' ) );
        $staff->set( 'avatar_url', '' );
        $staff->set( 'avatar_path', '' );
        $staff->save();
        exit;
    }

    public function executeStaffHolidays() {
        $this->id = $this->getParameter( 'id', 0 );
        $this->holidays = $this->getHolidays( $this->id );
        $this->render('holidays');
        exit;
    }

    public function executeStaffHolidaysUpdate() {
        $id         = $this->getParameter( 'id' );
        $holiday    = $this->getParameter( 'holiday' ) == 'true';
        $repeat     = $this->getParameter( 'repeat' ) == 'true';
        $day        = $this->getParameter( 'day', false );
        $staff_id   = $this->getParameter( 'staff_id' );

        if ( $staff_id ) {
            // update or delete the event
            if ( $id ) {
                if ( $holiday ) {
                    $this->getWpdb()->update( 'ab_holiday', array( 'repeat_event' => intval( $repeat ) ), array( 'id' => $id ), array( '%d' ) );
                } else {
                    $this->getWpdb()->delete( 'ab_holiday', array( 'id' => $id ), array( '%d' ) );
                }
                // add the new event
            } else if ( $holiday && $day ) {
                $day = new DateTime($day);
                $this->getWpdb()->insert( 'ab_holiday', array( 'holiday' => date( 'Y-m-d H:i:s', $day->format( 'U' ) ), 'repeat_event' => intval( $repeat ), 'staff_id' => $staff_id ), array( '%s', '%d', '%d' ) );
            }

            // and return refreshed events
            echo $this->getHolidays($staff_id);
        }
        exit;
    }



    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }

    protected function getHolidays($id) {
        $collection = $this->getWpdb()->get_results( $this->getWpdb()->prepare( "SELECT * FROM ab_holiday WHERE staff_id = %d",  $id ) );
        $holidays = array();
        if ( count( $collection ) ) {
            foreach ( $collection as $holiday ) {
                $holidays[$holiday->id] = array(
                    'm'     => intval(date('m', strtotime($holiday->holiday))),
                    'd'     => intval(date('d', strtotime($holiday->holiday))),
                    'title' => $holiday->title,
                );
                // if not repeated holiday, add the year
                if ( ! $holiday->repeat_event ) {
                    $holidays[$holiday->id]['y'] = intval(date('Y', strtotime($holiday->holiday)));
                }
            }
        }

        return json_encode( (object) $holidays );
    }
}