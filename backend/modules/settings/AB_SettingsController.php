<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'forms/AB_CompanyForm.php';
include 'forms/AB_PaymentsForm.php';
include 'forms/AB_BusinessHoursForm.php';

/**
 * Class AB_SettingsController
 */
class AB_SettingsController extends AB_Controller {

	public function index() {
		// save the settings
		if ( count( $this->getPost() ) ) {
			// Payments form
			if ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_payments' ) {
				$this->form = new AB_PaymentsForm();
				$this->message_p = __( 'Settings saved.', 'ab' );

				// Business hours form
			} elseif ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_hours' ) {
				$this->form = new AB_BusinessHoursForm();
				$this->message_h = __( 'Settings saved.', 'ab' );
			}
				// Purchase Code Form
			elseif ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_purchase_code' ) {
				update_option( 'ab_envato_purchase_code',  esc_html( $this->getParameter( 'ab_envato_purchase_code' ) ) );
				$this->message_pc = __( 'Settings saved.', 'ab' );
			} elseif ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_general' ) {
                $ab_settings_time_slot_length = $this->getParameter( 'ab_settings_time_slot_length' );
                if ( in_array( $ab_settings_time_slot_length, array( 10, 15, 20, 30, 60 ) ) ) {
                    update_option( 'ab_settings_time_slot_length',  $ab_settings_time_slot_length );
                }
                update_option( 'ab_settings_no_current_day_appointments', (int)$this->getParameter( 'ab_settings_no_current_day_appointments' ) );
                update_option( 'ab_settings_use_client_time_zone', (int)$this->getParameter( 'ab_settings_use_client_time_zone' ) );
                update_option( 'ab_settings_cancel_page_url', $this->getParameter( 'ab_settings_cancel_page_url' ) );
                $this->message_g = __( 'Settings saved.', 'ab' );
            }
				// Holidays form
			elseif ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] == '_holidays' ) {
				// Company form
			} else {
				$this->form = new AB_CompanyForm();
				$this->message_c = __( 'Settings saved.', 'ab' );
			}
			if ( isset( $_GET[ 'type' ] ) && $_GET[ 'type' ] != '_purchase_code' && $_GET[ 'type' ] != '_holidays'
			     && $_GET[ 'type' ] != '_import' && $_GET[ 'type' ] != '_general' ) {
				$this->form->bind( $this->getPost(), $_FILES );
				$this->form->save();
			}
		}

		// get holidays
		$this->holidays = $this->getHolidays();

		$this->render( 'index' );
	} // index

	/**
	 * Ajax request for Holidays calendar
	 */
	public function executeSettingsHoliday() {
		$id       = $this->getParameter( 'id', false );
		$holiday  = $this->getParameter( 'holiday' ) == 'true';
		$repeat   = $this->getParameter( 'repeat' ) == 'true';
		$day      = $this->getParameter( 'day', false );

		// update or delete the event
		if ( $id ) {
			if ( $holiday ) {
				$this->getWpdb()->update( 'ab_holiday', array('repeat_event' => intval( $repeat ) ), array( 'id' => $id ), array( '%d' ) );
				$this->getWpdb()->update( 'ab_holiday', array( 'repeat_event' => intval( $repeat ) ), array( 'parent_id' => $id ), array( '%d' )  );
			} else {
				$this->getWpdb()->delete( 'ab_holiday', array( 'id' => $id ), array( '%d' ) );
				$this->getWpdb()->delete( 'ab_holiday', array( 'parent_id' => $id ), array( '%d' ) );
			}
			// add the new event
		} elseif ( $holiday && $day ) {
			$day = new DateTime( $day );
			$this->getWpdb()->insert( 'ab_holiday', array( 'holiday' => $day->format( 'Y-m-d H:i:s' ), 'repeat_event' => intval( $repeat ) ), array( '%s', '%d' ) );
			$parent_id = $this->getWpdb()->insert_id;
			$staff = $this->getWpdb()->get_results( 'SELECT id FROM ab_staff' );
			foreach ( $staff as $employee ) {
				$this->getWpdb()->insert( 'ab_holiday',
					array(
						'holiday' => date( 'Y-m-d H:i:s', $day->format( 'U' ) ),
						'repeat_event' => intval( $repeat ),
						'staff_id' => $employee->id,
						'parent_id' => $parent_id
					),
					array( '%s', '%d', '%d' )
				);
			}
		}

		// and return refreshed events
		echo $this->getHolidays();
		exit;
	}

	protected function getHolidays() {
		$collection = $this->getWpdb()->get_results( "SELECT * FROM ab_holiday WHERE staff_id IS NULL" );
		$holidays = array();
		if ( count( $collection ) ) {
			foreach ( $collection as $holiday ) {
				$holidays[ $holiday->id ] = array(
					'm'     => intval( date( 'm', strtotime( $holiday->holiday ) ) ),
					'd'     => intval( date( 'd', strtotime( $holiday->holiday ) ) ),
					'title' => $holiday->title,
				);
				// if not repeated holiday, add the year
				if ( ! $holiday->repeat_event ) {
					$holidays[ $holiday->id ][ 'y' ] = intval( date( 'Y', strtotime( $holiday->holiday ) ) );
				}
			}
		}

		return json_encode( (object) $holidays );
	}

    /**
     * Show admin notice about purchase code and license.
     */
    public function showAdminNotice() {
        global $current_user;

        if ( !get_user_meta( $current_user->ID, 'ab_dismiss_admin_notice', true ) &&
            get_option( 'ab_envato_purchase_code' ) == '' &&
            time() > get_option( 'ab_installation_time' ) + 7*24*60*60
        ) {
            $this->render( 'admin_notice' );
        }
    }

    /**
     * Ajax request to dismiss admin notice for current user.
     */
    public function executeDismissAdminNotice() {
        global $current_user;

        update_user_meta( $current_user->ID, 'ab_dismiss_admin_notice', 1 );
    }

	/**
	 * Override parent method to add 'wp_ajax_ab_' prefix
	 * so current 'execute*' methods look nicer.
	 */
	protected function registerWpActions( $prefix = '' ) {
		parent::registerWpActions( 'wp_ajax_ab_' );
	}
}