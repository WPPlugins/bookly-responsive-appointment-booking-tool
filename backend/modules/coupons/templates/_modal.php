<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="modal fade" id="bookly-coupon-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="bookly-new-coupon-title"><?php _e( 'New coupon', 'bookly' ) ?></h4>
                    <h4 class="modal-title" id="bookly-edit-coupon-title"><?php _e( 'Edit coupon', 'bookly' ) ?></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class=form-group>
                                <label for="bookly-coupon-code"><?php _e( 'Code', 'bookly' ) ?></label>
                                <input type="text" id="bookly-coupon-code" class="form-control" name="code" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class=form-group>
                                <label for="bookly-coupon-discount"><?php _e( 'Discount (%)', 'bookly' ) ?></label>
                                <input type="number" id="bookly-coupon-discount" class="form-control" name="discount" />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class=form-group>
                                <label for="bookly-coupon-deduction"><?php _e( 'Deduction', 'bookly' ) ?></label>
                                <input type="text" id="bookly-coupon-deduction" class="form-control" name="deduction" />
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class=form-group>
                                <label for="bookly-coupon-usage-limit"><?php _e( 'Usage limit', 'bookly' ) ?></label>
                                <input type="number" id="bookly-coupon-usage-limit" class="form-control" name="usage_limit" min="0" step="1" />
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="btn-group">
                                <button class="btn btn-default dropdown-toggle bookly-flexbox" data-toggle="dropdown">
                                    <div class="bookly-flex-cell"><i class="glyphicon glyphicon-tag bookly-margin-right-md"></i></div>
                                    <div class="bookly-flex-cell text-left"><span id="bookly-entity-counter"></span></div>
                                    <div class="bookly-flex-cell">
                                        <div class="bookly-margin-left-md"><span class="caret"></span></div>
                                    </div>
                                </button>
                                <ul class="dropdown-menu bookly-entity-selector">
                                    <li>
                                        <a class="checkbox" href="javascript:void(0)">
                                            <label><input type="checkbox" id="bookly-check-all-entities"/><?php _e( 'All Services', 'bookly' ) ?></label>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php \BooklyLite\Lib\Utils\Common::submitButton( 'bookly-coupon-save' ) ?>
                    <button class="btn btn-lg btn-default" data-dismiss="modal">
                        <?php _e( 'Cancel', 'bookly' ) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>