<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<div class="modal fade bookly-js-delete-cascade-confirm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title h4"><?php _e( 'Are you sure?', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <p><?php _e( 'You are going to delete item which is involved in upcoming appointments. All related appointments will be deleted. Please double check and edit appointments before this item deletion if needed.', 'bookly' ) ?></p>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCustom( null, 'btn-lg btn-danger bookly-js-delete', __( 'Delete', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-success bookly-js-edit', __( 'Edit appointments', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>