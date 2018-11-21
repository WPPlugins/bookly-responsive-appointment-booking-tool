<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<div class="modal fade bookly-js-unsaved-changes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title h4"><?php _e( 'Are you sure?', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <p><?php _e( 'All unsaved changes will be lost.', 'bookly' ) ?></p>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCustom( null, 'btn-lg btn-success bookly-js-save-changes', __( 'Save', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-danger bookly-js-ignore-changes', __( 'Don\'t save', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>