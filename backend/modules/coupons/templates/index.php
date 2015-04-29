<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title"><?php _e('Coupons', 'ab') ?></div>
<div style="min-width: 800px;">
    <div class="ab-right-content" style="border: 0" id="ab_coupons_wrapper">
        <div class="no-result"><?php _e( 'No coupons found','ab' ) ?></div>
        <div class="list-wrapper">
            <div id="ab-coupons-list">
            </div>
            <div class="list-actions">
                <a class="add-coupon btn btn-info" href="#"><?php _e('Add Coupon','ab') ?></a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'ab') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $38 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'ab'); ?>: <a href="http://bookly.ladela.com" target="_blank">http://bookly.ladela.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'ab') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
