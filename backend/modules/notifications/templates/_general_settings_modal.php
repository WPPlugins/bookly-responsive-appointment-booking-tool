<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Lib\Utils;

foreach ( range( 1, 23 ) as $hours ) {
    $bookly_ntf_processing_interval_values[] = array( $hours, Utils\DateTime::secondsToInterval( $hours * HOUR_IN_SECONDS ) );
}
?>
<form id="bookly-js-general-settings-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <div class="modal-title h2"><?php esc_html_e( 'General settings', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <?php self::renderTemplate( '_common_settings', array( 'tail' => '_gen' ) ) ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php Selects::renderSingle( 'bookly_ntf_processing_interval', __( 'Scheduled notifications retry period', 'bookly' ), __( 'Set period of time when system will attempt to deliver notification to user. Notification will be discarded after period expiration.', 'bookly' ), $bookly_ntf_processing_interval_values ) ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::renderCustom( null, 'bookly-js-save btn-lg btn-success', __( 'Save settings', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', __( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>