<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<script type="text/ng-template" id="bookly-payment-details-dialog.tpl">
<div class="modal fade" id="bookly-payment-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php esc_attr_e( 'Close', 'bookly' ) ?>"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title h2"><?php _e( 'Payment', 'bookly' ) ?></div>
            </div>
            <div class="modal-body">
                <div class="bookly-loading"></div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCustom( null, 'btn-lg btn-default', __( 'Close', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>
</script>