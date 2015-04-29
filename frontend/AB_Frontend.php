<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include 'modules/booking/AB_BookingController.php';

class AB_Frontend {

    public function __construct() {
        add_action( 'wp_loaded', array( $this, 'init' ) );
        add_action( 'wp_head', array( $this, 'includeCSS' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'includeJS' ) );
        $this->bookingController = new AB_BookingController();
        add_shortcode( 'ap-booking', array( $this->bookingController, 'renderShortCode') );
    }

    /**
     * Generate StyleSheets from Appearance Options
     */
    public function includeCSS() {
        $booking_form_color = get_option( 'ab_appearance_color' ) . '!important';
        $checkbox_img = plugins_url( 'resources/images/checkbox.png', __FILE__ ) ;
        echo '<style type="text/css">
            /* Service */
            .ab-formGroup .ab-label-error {color: '. $booking_form_color .';}
            label.ab-category-title {color: '. $booking_form_color .';}
            .ab-next-step, .ab-mobile-next-step, .ab-mobile-prev-step, li.ab-step-tabs.active div,
                .pickadate__calendar, .ab-first-step .ab-week-days li label {background: '. $booking_form_color .';}
            li.ab-step-tabs.active a {color: '. $booking_form_color .';}
            div.ab-select-service-error {color: '. $booking_form_color .';}
            div.ab-select-time-error {color: '. $booking_form_color .';}
            .ab-select-wrap.ab-service-error .select-list {border: 2px solid '. $booking_form_color .';}
            .pickadate__header {border-bottom: 1px solid '. $booking_form_color .';}
            .pickadate__nav--next, .pickadate__nav--prev {color: '. $booking_form_color .';}
            .pickadate__nav--next:before {border-left:  6px solid '. $booking_form_color .';}
            .pickadate__nav--prev:before {border-right: 6px solid '. $booking_form_color .';}
            .pickadate__day:hover {color: '. $booking_form_color .';}
            .pickadate__day--selected:hover {color: '. $booking_form_color .';}
            .pickadate__day--selected {color: '. $booking_form_color .';}
            .pickadate__button--clear {color: '. $booking_form_color .';}
            .pickadate__button--today {color: '. $booking_form_color .';}
            .ab-first-step .ab-week-days li label.active {background: '. get_option( "ab_appearance_color" ) .' url(' .$checkbox_img. ') 0 0 no-repeat!important;}

            /* Time */
            .ab-columnizer .ab-available-day {
                background: '. $booking_form_color .';
                border: 1px solid '. $booking_form_color .';
            }
            .ab-columnizer .ab-available-hour:hover {
                border: 2px solid '. $booking_form_color .';
                color: '. $booking_form_color .';
             }
            .ab-columnizer .ab-available-hour:hover .ab-hour-icon {
                background: none;
                border: 2px solid '. $booking_form_color .';
                color: '. $booking_form_color .';
            }
            .ab-columnizer .ab-available-hour:hover .ab-hour-icon span {background: '. $booking_form_color .';}
            .ab-time-next {background: '. $booking_form_color .';}
            .ab-time-prev {background: '. $booking_form_color .';}
            .ab-to-first-step {background: '. $booking_form_color .';}

            /* Details */
            label.ab-formLabel {color: '. $booking_form_color .';}
            a.ab-to-second-step {background: '. $booking_form_color .';}
            a.ab-to-fourth-step {background: '. $booking_form_color .';}
            div.ab-error {color: '. $booking_form_color .';}
            input.ab-details-error {border: 2px solid '. $booking_form_color .';}
            .ab-to-second-step, .ab-to-fourth-step {background: '. $booking_form_color .';}

            /* Payment */
            .btn-apply-coupon {background: '. $booking_form_color .';}
            .ab-to-third-step {background: '. $booking_form_color .';}
            .ab-final-step {background: '. $booking_form_color .';}
        </style>';
    }

    public function includeJS() {
        wp_enqueue_style( 'ab-reset', plugins_url( 'resources/css/ab-reset.css', __FILE__ ) );
        wp_enqueue_style( 'ab-adda-themeless', plugins_url( 'resources/css/ladda-themeless.min.css', __FILE__ ) );
        wp_enqueue_style( 'ab-ladda-min', plugins_url( 'resources/css/ladda.min.css',   __FILE__ ) );
        wp_enqueue_style( 'ab-frontend-style', plugins_url( 'resources/css/ab_frontend_style.css',   __FILE__ ) );
        wp_enqueue_style( 'ab-pickadate', plugins_url( 'resources/css/pickadate.classic.css', __FILE__ ) );
        wp_enqueue_style( 'ab-columnizer', plugins_url( 'resources/css/ab-columnizer.css', __FILE__ ) );
        wp_enqueue_script( 'ab-spin', plugins_url( 'resources/js/spin.min.js', __FILE__ ) );
        wp_enqueue_script( 'ab-ladda', plugins_url( 'resources/js/ladda.min.js', __FILE__ ) );
        wp_enqueue_script( 'appointment-booking', plugins_url( 'resources/js/appointment_booking.js', __FILE__ ), array( 'jquery', 'underscore', 'backbone' ) );
        wp_enqueue_script( 'ab-pickadate', plugins_url( 'resources/js/pickadate.legacy.min.js', __FILE__ ) );
        wp_localize_script( 'ab-pickadate', 'BooklyL10n', array(
            'today'        => __( 'Today', 'ab' ),
            'month'        => array(
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
            'day'            => array(
                'Sun'        => __( 'Sun', 'ab' ),
                'Mon'        => __( 'Mon', 'ab' ),
                'Tue'        => __( 'Tue', 'ab' ),
                'Wed'        => __( 'Wed', 'ab' ),
                'Thu'        => __( 'Thu', 'ab' ),
                'Fri'        => __( 'Fri', 'ab' ),
                'Sat'        => __( 'Sat', 'ab' )
            )
        ) );
        wp_enqueue_script( 'ab-hammer', plugins_url( 'resources/js/jquery.hammer.min.js', __FILE__ ) );
        wp_enqueue_script( 'ab-cookie', plugins_url( 'resources/js/jquery.ck.js', __FILE__ ) );
        // Android animation
        if ( stripos( strtolower( $_SERVER[ 'HTTP_USER_AGENT' ] ), 'android' ) !== false ) {
            wp_enqueue_script( 'ab-jquery-animate-enhanced', plugins_url( 'resources/js/jquery.animate-enhanced.min.js', __FILE__ ) );
        }
    }

    public function init() {
        if ( !session_id() ) {
            @session_start();
        }

        // PayPal Express Checkout
        if ( isset( $_POST['action'] ) ) {
            switch ( $_POST['action'] ) {
                case 'ab_paypal_checkout':
                    $this->paypalController->paypalExpressCheckout();
                    break;
            }
        }
    }
}