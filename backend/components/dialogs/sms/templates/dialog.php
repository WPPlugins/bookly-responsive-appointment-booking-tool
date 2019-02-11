<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<form id="bookly-js-notification-modal" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                <div class="modal-title h2"></div>
            </div>
            <div class="modal-body">
                <?php static::renderTemplate( '_modal_body', compact( 'self' ) ) ?>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::renderCustom( null, 'bookly-js-save btn-lg btn-success', esc_html__( 'Save notification', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', esc_html__( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</form>