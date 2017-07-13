<?php
/**
 * Template for delete appointment dialog
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div id="bookly-delete-dialog" class="modal fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <div class="modal-title h2"><?php _e( 'Delete', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label>
                        <input id="bookly-delete-notify" type="checkbox" />
                        <?php _e( 'Send notifications', 'bookly' ) ?>
                    </label>
                </div>
                <div class="form-group" style="display: none;" id="bookly-delete-reason-cover">
                    <input class="form-control" type="text" id="bookly-delete-reason" placeholder="<?php _e( 'Cancellation reason (optional)', 'bookly' ) ?>" />
                </div>
            </div>
            <div class="modal-footer">
                <?php \BooklyLite\Lib\Utils\Common::deleteButton(); ?>
                <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'btn-default', __( 'Cancel', 'bookly' ), array( 'ng-click' => 'closeDialog()', 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>
