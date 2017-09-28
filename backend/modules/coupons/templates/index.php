<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Coupons', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="form-inline bookly-margin-bottom-lg text-right">
                    <div class="form-group">
                        <button type="button"
                                id="bookly-add"
                                class="btn btn-success">
                            <i class="glyphicon glyphicon-plus"></i> <?php _e( 'Add Coupon', 'bookly' ) ?>
                        </button>
                    </div>
                </div>

                <table class="table table-striped" id="bookly-coupons-list" width="100%">
                    <thead>
                        <tr>
                            <th><?php _e( 'Code', 'bookly' ) ?></th>
                            <th><?php _e( 'Discount (%)', 'bookly' ) ?></th>
                            <th><?php _e( 'Deduction', 'bookly' ) ?></th>
                            <th><?php _e( 'Services', 'bookly' ) ?></th>
                            <th><?php _e( 'Usage limit', 'bookly' ) ?></th>
                            <th><?php _e( 'Number of times used', 'bookly' ) ?></th>
                            <th></th>
                            <th width="16"><input type="checkbox" id="bookly-check-all"></th>
                        </tr>
                    </thead>
                </table>

                <div class="text-right bookly-margin-top-lg">
                    <?php \BooklyLite\Lib\Utils\Common::deleteButton() ?>
                </div>
            </div>
        </div>
    </div>
    <?php include '_modal.php' ?>
</div>