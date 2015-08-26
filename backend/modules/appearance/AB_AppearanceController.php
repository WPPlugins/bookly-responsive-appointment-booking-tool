<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class AB_AppearanceController
 */
class AB_AppearanceController extends AB_Controller {

    /**
     *  Default Action
     */
    public function index()
    {
        /** @var WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array(
                'css/intlTelInput.css',
                'css/ladda.min.css',
                'css/bookly-main.css',
                'css/ab-columnizer.css',
                'css/picker.classic.css',
                'css/picker.classic.date.css',
                'css/ab-picker.css',
            ),
            'backend' => array(
                'css/bookly.main-backend.css',
                'bootstrap/css/bootstrap.min.css',
                'css/bootstrap-editable.css',
            ),
            'wp' => array(
                'wp-color-picker',
            ),
            'module' => array(
                'css/appearance.css',
            )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/bootstrap-editable.min.js' => array( 'jquery' ),
            ),
            'frontend' => array(
                'js/picker.js' => array( 'jquery' ),
                'js/picker.date.js' => array( 'jquery' ),
                'js/spin.min.js' => array( 'jquery' ),
                'js/ladda.min.js' => array( 'jquery' ),
                'js/intlTelInput.min.js' => array( 'ab-picker.date.js' )
            ),
            'wp' => array(
                'wp-color-picker',
            ),
            'module' => array(
                'js/appearance.js' => array( 'jquery' ),
            )
        ) );

        wp_localize_script( 'ab-picker.date.js', 'BooklyL10n', array(
            'today'       => __( 'Today', 'bookly' ),
            'months'      => array_values( $wp_locale->month ),
            'days'        => array_values( $wp_locale->weekday_abbrev ),
            'nextMonth'   => __( 'Next month', 'bookly' ),
            'prevMonth'   => __( 'Previous month', 'bookly' ),
            'date_format' => AB_DateTimeUtils::convertFormat( 'date', AB_DateTimeUtils::FORMAT_PICKADATE ),
            'start_of_week'      => (int) get_option( 'start_of_week' ),
            'intlTelInput_utils' => plugins_url( 'intlTelInput.utils.js', AB_PATH . '/frontend/resources/js/intlTelInput.utils.js' ),
        ) );

        // Initialize steps (tabs).
        $this->steps = array(
            1 => get_option( 'ab_appearance_text_step_service' ),
            2 => get_option( 'ab_appearance_text_step_time' ),
            3 => get_option( 'ab_appearance_text_step_details' ),
            4 => get_option( 'ab_appearance_text_step_payment' ),
            5 => get_option( 'ab_appearance_text_step_done' )
        );

        // Render general layout.
        $this->render( 'index' );
    } // index

    /**
     *  Update options
     */
    public function executeUpdateAppearanceOptions()
    {
        if ( $this->hasParameter( 'options' ) ) {
            $get_option = $this->getParameter( 'options' );
            $options = array(
                // Info text.
                'ab_appearance_text_info_first_step'         => $get_option['text_info_first_step'],
                'ab_appearance_text_info_second_step'        => $get_option['text_info_second_step'],
                'ab_appearance_text_info_third_step'         => $get_option['text_info_third_step'],
                'ab_appearance_text_info_third_step_guest'   => $get_option['text_info_third_step_guest'],
                'ab_appearance_text_info_fourth_step'        => $get_option['text_info_fourth_step'],
                'ab_appearance_text_info_fifth_step'         => $get_option['text_info_fifth_step'],
                'ab_appearance_text_info_coupon'             => $get_option['text_info_coupon'],
                // Color.
                'ab_appearance_color'                        => $get_option['color'],
                // Step, label and option texts.
                'ab_appearance_text_step_service'            => $get_option['text_step_service'],
                'ab_appearance_text_step_time'               => $get_option['text_step_time'],
                'ab_appearance_text_step_details'            => $get_option['text_step_details'],
                'ab_appearance_text_step_payment'            => $get_option['text_step_payment'],
                'ab_appearance_text_step_done'               => $get_option['text_step_done'],
                'ab_appearance_text_label_category'          => $get_option['text_label_category'],
                'ab_appearance_text_label_service'           => $get_option['text_label_service'],
                'ab_appearance_text_label_number_of_persons' => $get_option['text_label_number_of_persons'],
                'ab_appearance_text_label_employee'          => $get_option['text_label_employee'],
                'ab_appearance_text_label_select_date'       => $get_option['text_label_select_date'],
                'ab_appearance_text_label_start_from'        => $get_option['text_label_start_from'],
                'ab_appearance_text_label_finish_by'         => $get_option['text_label_finish_by'],
                'ab_appearance_text_label_name'              => $get_option['text_label_name'],
                'ab_appearance_text_label_phone'             => $get_option['text_label_phone'],
                'ab_appearance_text_label_email'             => $get_option['text_label_email'],
                'ab_appearance_text_option_service'          => $get_option['text_option_service'],
                'ab_appearance_text_option_category'         => $get_option['text_option_category'],
                'ab_appearance_text_option_employee'         => $get_option['text_option_employee'],
                'ab_appearance_text_label_coupon'            => $get_option['text_label_coupon'],
                'ab_appearance_text_label_pay_locally'       => $get_option['text_label_pay_locally'],
                // Checkboxes.
                'ab_appearance_show_progress_tracker'        => $get_option['progress_tracker'],
                'ab_appearance_show_blocked_timeslots'       => $get_option['blocked_timeslots'],
                'ab_appearance_show_day_one_column'          => $get_option['day_one_column'],
                'ab_appearance_show_calendar'                => $get_option['show_calendar'],
            );

            // Save options.
            foreach ( $options as $option_name => $option_value ) {
                update_option( $option_name, $option_value );
                // Register string for translate in WPML.
                if ( strpos( $option_name, 'ab_appearance_text_' ) === 0 ) {
                    do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
                }
            }
        }
        exit;
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
