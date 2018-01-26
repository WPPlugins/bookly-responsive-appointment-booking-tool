<?php
namespace BooklyLite\Backend\Modules\Appearance;

use BooklyLite\Lib;
use BooklyLite\Backend\Modules\Appearance\Lib\Helper;

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
            'backend' => array( 'bootstrap/css/bootstrap-theme.min.css', ),
            'wp'      => array( 'wp-color-picker', ),
            'module'  => array( 'css/bootstrap-editable.css', )
        ) );

        $this->enqueueScripts( array(
            'backend' => array(
                'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
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
            'module' => array(
                'js/bootstrap-editable.min.js'    => array( 'bookly-bootstrap.min.js' ),
                'js/bootstrap-editable.bookly.js' => array( 'bookly-bootstrap-editable.min.js' ),
                'js/appearance.js'                => array( 'bookly-bootstrap-editable.bookly.js' )
            )
        ) );

        wp_localize_script( 'bookly-picker.date.js', 'BooklyL10n', array(
            'csrf_token'    => Lib\Utils\Common::getCsrfToken(),
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
        $steps = array(
            1 => get_option( 'bookly_l10n_step_service' ),
            get_option( 'bookly_l10n_step_extras' ),
            get_option( 'bookly_l10n_step_time' ),
            get_option( 'bookly_l10n_step_repeat' ),
            get_option( 'bookly_l10n_step_cart' ),
            get_option( 'bookly_l10n_step_details' ),
            get_option( 'bookly_l10n_step_payment' ),
            get_option( 'bookly_l10n_step_done' )
        );

        $custom_css = get_option( 'bookly_app_custom_styles' );

        // Shortcut to helper class.
        $editable = new Helper();

        // Render general layout.
        $this->render( 'index', compact( 'steps', 'custom_css', 'editable' ) );
    }

    /**
     *  Update options
     */
    public function executeUpdateAppearanceOptions()
    {
        $options = $this->getParameter( 'options', array() );

        // Make sure that we save only allowed options.
        $options_to_save = array_intersect_key( $options, array_flip( array(
            // Info text.
            'bookly_l10n_info_cart_step',
            'bookly_l10n_info_complete_step',
            'bookly_l10n_info_complete_step_limit_error',
            'bookly_l10n_info_complete_step_processing',
            'bookly_l10n_info_details_step',
            'bookly_l10n_info_details_step_guest',
            'bookly_l10n_info_payment_step_single_app',
            'bookly_l10n_info_payment_step_several_apps',
            'bookly_l10n_info_service_step',
            'bookly_l10n_info_time_step',
            // Step, label and option texts.
            'bookly_l10n_button_apply',
            'bookly_l10n_button_back',
            'bookly_l10n_button_book_more',
            'bookly_l10n_label_category',
            'bookly_l10n_label_ccard_code',
            'bookly_l10n_label_ccard_expire',
            'bookly_l10n_label_ccard_number',
            'bookly_l10n_label_email',
            'bookly_l10n_label_employee',
            'bookly_l10n_label_finish_by',
            'bookly_l10n_label_name',
            'bookly_l10n_label_first_name',
            'bookly_l10n_label_last_name',
            'bookly_l10n_label_notes',
            'bookly_l10n_label_number_of_persons',
            'bookly_l10n_label_pay_ccard',
            'bookly_l10n_label_pay_locally',
            'bookly_l10n_label_pay_paypal',
            'bookly_l10n_label_phone',
            'bookly_l10n_label_select_date',
            'bookly_l10n_label_service',
            'bookly_l10n_label_start_from',
            'bookly_l10n_option_category',
            'bookly_l10n_option_employee',
            'bookly_l10n_option_service',
            'bookly_l10n_step_service',
            'bookly_l10n_step_service_mobile_button_next',
            'bookly_l10n_step_service_button_next',
            'bookly_l10n_step_time',
            'bookly_l10n_step_time_slot_not_available',
            'bookly_l10n_step_cart',
            'bookly_l10n_step_cart_slot_not_available',
            'bookly_l10n_step_cart_button_next',
            'bookly_l10n_step_details',
            'bookly_l10n_step_details_button_next',
            'bookly_l10n_step_details_button_login',
            'bookly_l10n_step_payment',
            'bookly_l10n_step_payment_button_next',
            'bookly_l10n_step_done',
            // Validator errors.
            'bookly_l10n_required_email',
            'bookly_l10n_required_employee',
            'bookly_l10n_required_name',
            'bookly_l10n_required_first_name',
            'bookly_l10n_required_last_name',
            'bookly_l10n_required_phone',
            'bookly_l10n_required_service',
            // Color.
            'bookly_app_color',
            // Checkboxes.
            'bookly_app_required_employee',
            'bookly_app_service_name_with_duration',
            'bookly_app_show_blocked_timeslots',
            'bookly_app_show_calendar',
            'bookly_app_show_day_one_column',
            'bookly_app_show_login_button',
            'bookly_app_show_notes',
            'bookly_app_show_progress_tracker',
            'bookly_app_staff_name_with_price',
            'bookly_cst_required_phone',
            'bookly_cst_first_last_name',
        ) ) );

        // Allow add-ons to add their options.
        $options_to_save = Lib\Proxy\Shared::prepareAppearanceOptions( $options_to_save, $options );

        // Save options.
        foreach ( $options_to_save as $option_name => $option_value ) {
            update_option( $option_name, $option_value );
            // Register string for translate in WPML.
            if ( strpos( $option_name, 'bookly_l10n_' ) === 0 ) {
                do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
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
     * Process ajax request to save custom css
     */
    public function executeSaveCustomCss()
    {
        update_option( 'bookly_app_custom_styles', $this->getParameter( 'custom_css' ) );

        wp_send_json_success( array( 'message' => __( 'Your custom CSS was saved. Please refresh the page to see your changes.', 'bookly') ) );
    }
}