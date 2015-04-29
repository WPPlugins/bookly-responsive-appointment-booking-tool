<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class AB_NotificationsController extends AB_Controller {

    public function index() {
        $path = dirname( __DIR__ );
        wp_enqueue_style( 'ab-style', plugins_url( 'resources/css/ab_style.css', $path ) );
        wp_enqueue_style( 'ab-bootstrap', plugins_url( 'resources/bootstrap/css/bootstrap.min.css', $path ) );
        wp_enqueue_script( 'ab-bootstrap', plugins_url( 'resources/bootstrap/js/bootstrap.min.js', $path ), array( 'jquery' ) );

	    $this->notifications = array(
		    'client_info' => array(
			    'name'    => __( 'Notification to Customer about Appointment details', 'ab' ),
			    'subject' => __( 'Your appointment information', 'ab' ),
			    'message' => wpautop( __("Dear [[CLIENT_NAME]].\n\nThis is confirmation that you have booked [[SERVICE_NAME]].\n\nWe are waiting you at [[COMPANY_ADDRESS]] on [[APPOINTMENT_DATE]] at [[APPOINTMENT_TIME]].\n\nThank you for choosing our company.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
		    ),
		    'provider_info' => array(
			    'name'    => __( 'Notification to Staff Member about Appointment details', 'ab' ),
			    'subject' => __( 'New booking information', 'ab' ),
			    'message' => wpautop( __( "Hello.\n\nYou have new booking.\n\nService: [[SERVICE_NAME]]\nDate: [[APPOINTMENT_DATE]]\nTime: [[APPOINTMENT_TIME]]\nClient name: [[CLIENT_NAME]]\nClient phone: [[CLIENT_PHONE]]\nClient email: [[CLIENT_EMAIL]]\nClient notes: [[CLIENT_NOTES]]", 'ab' ) ),
		    ),
		    'evening_next_day' => array(
			    'name'    => __( 'Evening reminder to Customer about next day Appointment', 'ab' ),
			    'subject' => __( 'Your appointment at [[COMPANY_NAME]]', 'ab' ),
			    'message' => wpautop( __( "Dear [[CLIENT_NAME]].\n\nWe would like to remind you that you have booked [[SERVICE_NAME]] tomorrow on [[APPOINTMENT_TIME]]. We are waiting you at [[COMPANY_ADDRESS]].\n\nThank you for choosing our company.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
		    ),
		    'evening_after' => array(
			    'name'    => __( 'Follow-up message on the day after Appointment', 'ab' ),
			    'subject' => __( 'Your visit to [[COMPANY_NAME]]', 'ab' ),
			    'message' => wpautop( __( "Dear [[CLIENT_NAME]].\n\nThank you for choosing [[COMPANY_NAME]]. We hope you were satisfied with your [[SERVICE_NAME]].\n\nThank you and we look forward to seeing you again soon.\n\n[[COMPANY_NAME]]\n[[COMPANY_PHONE]]\n[[COMPANY_WEBSITE]]", 'ab' ) ),
		    ),
		    'event_next_day' => array(
			    'name'    => __( 'Evening notification with the next day agenda to Staff Member', 'ab' ),
			    'subject' => __( 'Your agenda for [[TOMORROW_DATE]]', 'ab' ),
			    'message' => wpautop( __( "Hello.\n\nYour agenda for tomorrow is:\n\n[[NEXT_DAY_AGENDA]]", 'ab' ) ),
		    )
	    );

      $this->render( 'index' );
    }

    // Protected methods.

    /**
     * Override parent method to add 'wp_ajax_ab_' prefix
     * so current 'execute*' methods look nicer.
     */
    protected function registerWpActions( $prefix = '' ) {
        parent::registerWpActions( 'wp_ajax_ab_' );
    }
}