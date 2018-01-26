<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Proxy;
use BooklyLite\Lib\Utils\Common;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'url' ) ) ?>">
    <?php
        Common::optionText( 'bookly_url_approve_page_url', __( 'Approve appointment URL (success)', 'bookly' ), __( 'Set the URL of a page that is shown to staff after they successfully approved the appointment.', 'bookly' ) );
        Common::optionText( 'bookly_url_approve_denied_page_url', __( 'Approve appointment URL (denied)', 'bookly' ), __( 'Set the URL of a page that is shown to staff when the approval of appointment cannot be done (due to capacity, changed status, etc.).', 'bookly' ) );
        Common::optionText( 'bookly_url_cancel_page_url', __( 'Cancel appointment URL (success)', 'bookly' ), __( 'Set the URL of a page that is shown to clients after they successfully cancelled their appointment.', 'bookly' ) );
        Common::optionText( 'bookly_url_cancel_denied_page_url', __( 'Cancel appointment URL (denied)', 'bookly' ), __( 'Set the URL of a page that is shown to clients when the cancellation of appointment is not available anymore.', 'bookly' ) );
        Common::optionText( 'bookly_url_cancel_confirm_page_url', __( 'Appointment cancellation confirmation URL', 'bookly' ), __( 'Set the URL of an appointment cancellation confirmation page that is shown to clients when they press cancellation link.', 'bookly' ) );
        Common::optionText( 'bookly_url_reject_page_url', __( 'Reject appointment URL (success)', 'bookly' ), __( 'Set the URL of a page that is shown to staff after they successfully rejected the appointment.', 'bookly' ) );
        Common::optionText( 'bookly_url_reject_denied_page_url', __( 'Reject appointment URL (denied)', 'bookly' ), __( 'Set the URL of a page that is shown to staff when the rejection of appointment cannot be done (due to changed status, etc.).', 'bookly' ) );
    ?>
    <div class="form-group">
        <label for="bookly_settings_final_step_url_mode"><?php _e( 'Final step URL', 'bookly' ) ?></label>
        <p class="help-block"><?php _e( 'Set the URL of a page that the user will be forwarded to after successful booking. If disabled then the default Done step is displayed.', 'bookly' ) ?></p>
        <select class="form-control" id="bookly_settings_final_step_url_mode">
            <?php foreach ( array( __( 'Disabled', 'bookly' ) => 0, __( 'Enabled', 'bookly' ) => 1 ) as $text => $mode ) : ?>
                <option value="<?php echo esc_attr( $mode ) ?>" <?php selected( get_option( 'bookly_url_final_step_url' ), $mode ) ?> ><?php echo $text ?></option>
            <?php endforeach ?>
        </select>
        <input class="form-control"
               style="margin-top: 5px; <?php echo get_option( 'bookly_url_final_step_url' ) == '' ? 'display: none' : '' ?>"
               type="text" name="bookly_url_final_step_url"
               value="<?php form_option( 'bookly_url_final_step_url' ) ?>"
               placeholder="<?php esc_attr_e( 'Enter a URL', 'bookly' ) ?>"/>
    </div>
    <?php Proxy\Shared::renderUrlSettings() ?>
    <div class="panel-footer">
        <?php Common::csrf() ?>
        <?php Common::submitButton() ?>
        <?php Common::resetButton() ?>
    </div>
</form>