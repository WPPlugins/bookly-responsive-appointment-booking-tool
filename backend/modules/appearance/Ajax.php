<?php
namespace Bookly\Backend\Modules\Appearance;

use Bookly\Lib;
use Bookly\Backend\Modules\Appearance\Proxy;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Appearance
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     *  Update options.
     */
    public static function updateAppearanceOptions()
    {
        $options = self::parameter( 'options', array() );

        // Make sure that we save only allowed options.
        $options_to_save = array_intersect_key( $options, array_flip( array(
            // Info text.
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
            'bookly_l10n_label_category',
            'bookly_l10n_label_ccard_code',
            'bookly_l10n_label_ccard_expire',
            'bookly_l10n_label_ccard_number',
            'bookly_l10n_label_email',
            'bookly_l10n_label_email_confirm',
            'bookly_l10n_label_employee',
            'bookly_l10n_label_service_duration',
            'bookly_l10n_label_finish_by',
            'bookly_l10n_label_name',
            'bookly_l10n_label_first_name',
            'bookly_l10n_label_last_name',
            'bookly_l10n_label_notes',
            'bookly_l10n_label_number_of_persons',
            'bookly_l10n_label_pay_ccard',
            'bookly_l10n_label_pay_locally',
            'bookly_l10n_label_phone',
            'bookly_l10n_label_select_date',
            'bookly_l10n_label_service',
            'bookly_l10n_label_start_from',
            'bookly_l10n_option_category',
            'bookly_l10n_option_employee',
            'bookly_l10n_option_service',
            'bookly_l10n_option_day',
            'bookly_l10n_option_month',
            'bookly_l10n_option_year',
            'bookly_l10n_step_service',
            'bookly_l10n_step_service_mobile_button_next',
            'bookly_l10n_step_service_button_next',
            'bookly_l10n_step_time',
            'bookly_l10n_step_time_slot_not_available',
            'bookly_l10n_step_details',
            'bookly_l10n_step_details_button_next',
            'bookly_l10n_step_details_button_login',
            'bookly_l10n_step_payment',
            'bookly_l10n_step_payment_button_next',
            'bookly_l10n_step_done',
            // Validator errors.
            'bookly_l10n_required_email',
            'bookly_l10n_email_confirm_not_match',
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
            'bookly_app_show_time_zone_switcher',
            'bookly_app_show_login_button',
            'bookly_app_show_facebook_login_button',
            'bookly_app_show_notes',
            'bookly_app_show_progress_tracker',
            'bookly_app_align_buttons_left',
            'bookly_app_staff_name_with_price',
            'bookly_cst_required_details',
            'bookly_app_service_duration_with_price',
            'bookly_cst_first_last_name',
            'bookly_app_show_email_confirm',
        ) ) );

        // Allow add-ons to add their options.
        $options_to_save = Proxy\Shared::prepareOptions( $options_to_save, $options );

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
    public static function dismissAppearanceNotice()
    {
        update_user_meta( get_current_user_id(), Lib\Plugin::getPrefix() . 'dismiss_appearance_notice', 1 );
    }

    /**
     * Process ajax request to save custom css
     */
    public static function saveCustomCss()
    {
        update_option( 'bookly_app_custom_styles', self::parameter( 'custom_css' ) );

        wp_send_json_success( array( 'message' => __( 'Your custom CSS was saved. Please refresh the page to see your changes.', 'bookly') ) );
    }
}