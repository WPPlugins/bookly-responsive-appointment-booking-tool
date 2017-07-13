<?php
namespace BooklyLite\Backend\Modules\Appearance;

use BooklyLite\Lib;

/**
 * Class Controller
 * @package BooklyLite\Backend\Modules\Appearance
 */
class Controller extends Lib\Base\Controller
{
    const page_slug = 'bookly-appearance';

    /**
     *  Default Action
     */
    public function index()
    {
        /** @var \WP_Locale $wp_locale */
        global $wp_locale;

        $this->enqueueStyles( array(
            'frontend' => array_merge(
                ( get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
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
                get_option( 'bookly_cst_phone_default_country' ) == 'disabled'
                    ? array()
                    : array( 'js/intlTelInput.min.js' => array( 'jquery' ) )
            ),
            'wp'     => array( 'wp-color-picker' ),
            'module' => array( 'js/appearance.js' => array( 'jquery' ) )
        ) );

        wp_localize_script( 'bookly-picker.date.js', 'BooklyL10n', array(
            'today'         => __( 'Today', 'bookly' ),
            'months'        => array_values( $wp_locale->month ),
            'days'          => array_values( $wp_locale->weekday_abbrev ),
            'nextMonth'     => __( 'Next month', 'bookly' ),
            'prevMonth'     => __( 'Previous month', 'bookly' ),
            'date_format'   => Lib\Utils\DateTime::convertFormat( 'date', Lib\Utils\DateTime::FORMAT_PICKADATE ),
            'start_of_week' => (int) get_option( 'start_of_week' ),
            'saved'         => __( 'Settings saved.', 'bookly' ),
            'intlTelInput'  => array(
                'enabled' => get_option( 'bookly_cst_phone_default_country' ) != 'disabled',
                'utils'   => plugins_url( 'intlTelInput.utils.js', Lib\Plugin::getDirectory() . '/frontend/resources/js/intlTelInput.utils.js' ),
                'country' => get_option( 'bookly_cst_phone_default_country' ),
            )
        ) );

        // Initialize steps (tabs).
        $this->steps = array(
            1 => get_option( 'bookly_l10n_step_service' ),
            get_option( 'bookly_l10n_step_extras' ),
            get_option( 'bookly_l10n_step_time' ),
            get_option( 'bookly_l10n_step_repeat' ),
            get_option( 'bookly_l10n_step_cart' ),
            get_option( 'bookly_l10n_step_details' ),
            get_option( 'bookly_l10n_step_payment' ),
            get_option( 'bookly_l10n_step_done' )
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
                'bookly_l10n_info_cart_step'          => $get_option['text_info_cart_step'],
                'bookly_l10n_info_complete_step'      => $get_option['text_info_complete_step'],
                'bookly_l10n_info_coupon'             => $get_option['text_info_coupon'],
                'bookly_l10n_info_details_step'       => $get_option['text_info_details_step'],
                'bookly_l10n_info_details_step_guest' => $get_option['text_info_details_step_guest'],
                'bookly_l10n_info_payment_step'       => $get_option['text_info_payment_step'],
                'bookly_l10n_info_service_step'       => $get_option['text_info_service_step'],
                'bookly_l10n_info_time_step'          => $get_option['text_info_time_step'],
                // Step, label and option texts.
                'bookly_l10n_button_apply'            => $get_option['text_button_apply'],
                'bookly_l10n_button_back'             => $get_option['text_button_back'],
                'bookly_l10n_button_book_more'        => $get_option['text_button_book_more'],
                'bookly_l10n_button_next'             => $get_option['text_button_next'],
                'bookly_l10n_label_category'          => $get_option['text_label_category'],
                'bookly_l10n_label_ccard_code'        => $get_option['text_label_ccard_code'],
                'bookly_l10n_label_ccard_expire'      => $get_option['text_label_ccard_expire'],
                'bookly_l10n_label_ccard_number'      => $get_option['text_label_ccard_number'],
                'bookly_l10n_label_coupon'            => $get_option['text_label_coupon'],
                'bookly_l10n_label_email'             => $get_option['text_label_email'],
                'bookly_l10n_label_employee'          => $get_option['text_label_employee'],
                'bookly_l10n_label_finish_by'         => $get_option['text_label_finish_by'],
                'bookly_l10n_label_name'              => $get_option['text_label_name'],
                'bookly_l10n_label_number_of_persons' => $get_option['text_label_number_of_persons'],
                'bookly_l10n_label_pay_ccard'         => $get_option['text_label_pay_ccard'],
                'bookly_l10n_label_pay_locally'       => $get_option['text_label_pay_locally'],
                'bookly_l10n_label_pay_mollie'        => $get_option['text_label_pay_mollie'],
                'bookly_l10n_label_pay_paypal'        => $get_option['text_label_pay_paypal'],
                'bookly_l10n_label_phone'             => $get_option['text_label_phone'],
                'bookly_l10n_label_select_date'       => $get_option['text_label_select_date'],
                'bookly_l10n_label_service'           => $get_option['text_label_service'],
                'bookly_l10n_label_start_from'        => $get_option['text_label_start_from'],
                'bookly_l10n_option_category'         => $get_option['text_option_category'],
                'bookly_l10n_option_employee'         => $get_option['text_option_employee'],
                'bookly_l10n_option_service'          => $get_option['text_option_service'],
                'bookly_l10n_step_cart'               => $get_option['text_step_cart'],
                'bookly_l10n_step_details'            => $get_option['text_step_details'],
                'bookly_l10n_step_done'               => $get_option['text_step_done'],
                'bookly_l10n_step_payment'            => $get_option['text_step_payment'],
                'bookly_l10n_step_service'            => $get_option['text_step_service'],
                'bookly_l10n_step_time'               => $get_option['text_step_time'],
                // Validator errors.
                'bookly_l10n_required_email'          => $get_option['text_required_email'],
                'bookly_l10n_required_employee'       => $get_option['text_required_employee'],
                'bookly_l10n_required_name'           => $get_option['text_required_name'],
                'bookly_l10n_required_phone'          => $get_option['text_required_phone'],
                'bookly_l10n_required_service'        => $get_option['text_required_service'],
                // Color.
                'bookly_app_color'                    => $get_option['color'],
                // Checkboxes.
                'bookly_app_required_employee'        => $get_option['required_employee'],
                'bookly_app_show_blocked_timeslots'   => $get_option['blocked_timeslots'],
                'bookly_app_show_calendar'            => $get_option['show_calendar'],
                'bookly_app_show_day_one_column'      => $get_option['day_one_column'],
                'bookly_app_show_progress_tracker'    => $get_option['progress_tracker'],
                'bookly_app_staff_name_with_price'    => $get_option['staff_name_with_price'],
            );

            $options = apply_filters( 'bookly_prepare_appearance_settings', $options, $get_option );

            // Save options.
            foreach ( $options as $option_name => $option_value ) {
                update_option( $option_name, $option_value );
                // Register string for translate in WPML.
                if ( strpos( $option_name, 'bookly_l10n_' ) === 0 ) {
                    do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
                }
            }
        }

        wp_send_json_success();
    }

    /**
     * Ajax request to dismiss appearance notice for current user.
     */
    public function executeDismissAppearanceNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_appearance_notice', 1 );
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