<?php
namespace BooklyLite\Backend\Modules\Appearance;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Appearance
 */
class Controller extends Lib\Base\Controller
{
    /**
     *  Default Action
     */
    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array_merge(
                ( get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'css/intlTelInput.css' ) ),
                array(
                    'css/ladda.min.css',
                    'css/picker.classic.css',
                    'css/picker.classic.date.css',
                    'css/bookly-main.css',
                )
            ),
            'backend' => array(
                'bootstrap/css/bootstrap-theme.min.css',
                'css/bootstrap-editable.css',
            ),
            'wp' => array( 'wp-color-picker' ),
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'js/bootstrap-editable.min.js'  => array( 'jquery' ),
                'js/alert.js' => array( 'jquery' ),
            ),
            'frontend' => array_merge(
                array(
                    'js/picker.js' => array( 'jquery' ),
                    'js/picker.date.js' => array( 'jquery' ),
                    'js/spin.min.js'    => array( 'jquery' ),
                    'js/ladda.min.js'   => array( 'jquery' ),
                ),
                get_option( 'ab_settings_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'wp'     => array( 'wp-color-picker' ),
            'module' => array( 'js/appearance.js' => array( 'jquery' ) )
        ) );

        wp_localize_script( 'ab-picker.date.js', 'BooklyL10n', array(
            'today'         => __( 'Today', 'bookly' ),
            'months'        => array_values( $wp_locale->month ),
            'days'          => array_values( $wp_locale->weekday_abbrev ),
            'nextMonth'     => __( 'Next month', 'bookly' ),
            'prevMonth'     => __( 'Previous month', 'bookly' ),
            'date_format'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_PICKADATE ),
            'start_of_week' => (int) get_option( 'start_of_week' ),
            'saved'         => __( 'Settings saved.', 'bookly' ),
            'intlTelInput'  => array(
                'enabled' => ( get_option( 'ab_settings_phone_default_country' ) != 'disabled' ),
                'utils'   => plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'ab_settings_phone_default_country' ),
            )
        ) );

        // Initialize steps (tabs).
        $this->steps = array(
            1 => get_option( 'ab_appearance_text_step_service' ),
            get_option( 'ab_appearance_text_step_extras' ),
            get_option( 'ab_appearance_text_step_time' ),
            get_option( 'ab_appearance_text_step_cart' ),
            get_option( 'ab_appearance_text_step_details' ),
            get_option( 'ab_appearance_text_step_payment' ),
            get_option( 'ab_appearance_text_step_done' )
        );

        // Render general layout.
        $this->render( 'index' );
    }

    /**
     *  Update options
     */
    public function executeUpdateAppearanceOptions()
    {
        if ( $this->hasParameter( 'options' ) ) {
            $get_option = $this->getParameter( 'options' );
            $options = array(
                // Info text.
                'ab_appearance_text_info_cart_step'          => $get_option['text_info_cart_step'],
                'ab_appearance_text_info_complete_step'      => $get_option['text_info_complete_step'],
                'ab_appearance_text_info_coupon'             => $get_option['text_info_coupon'],
                'ab_appearance_text_info_details_step'       => $get_option['text_info_details_step'],
                'ab_appearance_text_info_details_step_guest' => $get_option['text_info_details_step_guest'],
                'ab_appearance_text_info_payment_step'       => $get_option['text_info_payment_step'],
                'ab_appearance_text_info_service_step'       => $get_option['text_info_service_step'],
                'ab_appearance_text_info_time_step'          => $get_option['text_info_time_step'],
                // Color.
                'ab_appearance_color'                        => $get_option['color'],
                // Step, label and option texts.
                'ab_appearance_text_button_apply'            => $get_option['text_button_apply'],
                'ab_appearance_text_button_back'             => $get_option['text_button_back'],
                'ab_appearance_text_button_book_more'        => $get_option['text_button_book_more'],
                'ab_appearance_text_button_next'             => $get_option['text_button_next'],
                'ab_appearance_text_label_category'          => $get_option['text_label_category'],
                'ab_appearance_text_label_ccard_code'        => $get_option['text_label_ccard_code'],
                'ab_appearance_text_label_ccard_expire'      => $get_option['text_label_ccard_expire'],
                'ab_appearance_text_label_ccard_number'      => $get_option['text_label_ccard_number'],
                'ab_appearance_text_label_coupon'            => $get_option['text_label_coupon'],
                'ab_appearance_text_label_email'             => $get_option['text_label_email'],
                'ab_appearance_text_label_employee'          => $get_option['text_label_employee'],
                'ab_appearance_text_label_finish_by'         => $get_option['text_label_finish_by'],
                'ab_appearance_text_label_name'              => $get_option['text_label_name'],
                'ab_appearance_text_label_number_of_persons' => $get_option['text_label_number_of_persons'],
                'ab_appearance_text_label_pay_ccard'         => $get_option['text_label_pay_ccard'],
                'ab_appearance_text_label_pay_locally'       => $get_option['text_label_pay_locally'],
                'ab_appearance_text_label_pay_mollie'        => $get_option['text_label_pay_mollie'],
                'ab_appearance_text_label_pay_paypal'        => $get_option['text_label_pay_paypal'],
                'ab_appearance_text_label_phone'             => $get_option['text_label_phone'],
                'ab_appearance_text_label_select_date'       => $get_option['text_label_select_date'],
                'ab_appearance_text_label_service'           => $get_option['text_label_service'],
                'ab_appearance_text_label_start_from'        => $get_option['text_label_start_from'],
                'ab_appearance_text_option_category'         => $get_option['text_option_category'],
                'ab_appearance_text_option_employee'         => $get_option['text_option_employee'],
                'ab_appearance_text_option_service'          => $get_option['text_option_service'],
                'ab_appearance_text_step_cart'               => $get_option['text_step_cart'],
                'ab_appearance_text_step_details'            => $get_option['text_step_details'],
                'ab_appearance_text_step_done'               => $get_option['text_step_done'],
                'ab_appearance_text_step_payment'            => $get_option['text_step_payment'],
                'ab_appearance_text_step_service'            => $get_option['text_step_service'],
                'ab_appearance_text_step_time'               => $get_option['text_step_time'],
                // Validator errors.
                'ab_appearance_text_required_email'          => $get_option['text_required_email'],
                'ab_appearance_text_required_employee'       => $get_option['text_required_employee'],
                'ab_appearance_text_required_name'           => $get_option['text_required_name'],
                'ab_appearance_text_required_phone'          => $get_option['text_required_phone'],
                'ab_appearance_text_required_service'        => $get_option['text_required_service'],
                // Checkboxes.
                'ab_appearance_required_employee'            => $get_option['required_employee'],
                'ab_appearance_show_blocked_timeslots'       => $get_option['blocked_timeslots'],
                'ab_appearance_show_calendar'                => $get_option['show_calendar'],
                'ab_appearance_show_day_one_column'          => $get_option['day_one_column'],
                'ab_appearance_show_progress_tracker'        => $get_option['progress_tracker'],
                'ab_appearance_staff_name_with_price'        => $get_option['staff_name_with_price'],
            );

            if ( Lib\Config::extrasEnabled() ) {
                $options['ab_appearance_text_info_extras_step'] = $get_option['text_info_extras_step'];
                $options['ab_appearance_text_step_extras']      = $get_option['text_step_extras'];
            }
            if ( Lib\Config::locationsEnabled() ) {
                $options['ab_appearance_required_location']     = $get_option['required_location'];
                $options['ab_appearance_text_label_location']   = $get_option['text_label_location'];
                $options['ab_appearance_text_option_location']  = $get_option['text_option_location'];
                $options['ab_appearance_text_required_location'] = $get_option['text_required_location'];
            }
            if ( Lib\Config::multiplyAppointmentsEnabled() ) {
                $options['ab_appearance_text_label_multiply'] = $get_option['text_label_multiply'];
            }

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
     * Ajax request to dismiss appearance notice for current user.
     */
    public function executeDismissAppearanceNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_appearance_notice', 1 );
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