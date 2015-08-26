<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="ab_coupons_wrapper" class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Coupons', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class="list-wrapper">
            <div id="ab-coupons-list">
                <?php include '_list.php' ?>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="list-actions">
            <a class="add-coupon btn btn-info" href="#"><?php _e( 'Add Coupon', 'bookly' ) ?></a>
            <a class="delete btn btn-info" href="#"><?php _e( 'Delete', 'bookly' ) ?></a>
        </div>
    </div>
</div>
<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'bookly') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $46 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'bookly'); ?>: <a href="http://booking-wp-plugin.com" target="_blank">http://booking-wp-plugin.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'bookly') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->