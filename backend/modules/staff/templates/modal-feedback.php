<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php \BooklyLite\Lib\Utils\Common::helpButton() ?>

<div id="feedback" class="modal fade text-left" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-sm">
        <form enctype="multipart/form-data" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php _e( 'Leave as a message', 'bookly' ) ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for=""><?php _e( 'Name', 'bookly' ) ?> <span class="bookly-color-brand-danger">*</span></label>
                        <input type="text" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-lg btn-default bookly-margin-right-md"
                            data-dismiss="modal"><?php _e('Close', 'bookly') ?></button>
                    <button type="submit" class="btn btn-lg btn-success">
                        <?php _e( 'Send', 'bookly' ) ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>




