<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap bookly-js-subscribe-notice">
    <div id="bookly-subscribe-notice" class="alert alert-info bookly-tbs-body bookly-flexbox">
        <div class="bookly-flex-row">
            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
            <div class="bookly-flex-cell">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <label for="bookly-subscribe-email"><?php _e( 'Subscribe to monthly emails about Bookly improvements and new releases.', 'bookly' ) ?></label>
                <div class="input-group input-group-sm" style="max-width: 400px">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                    <input type="text" id="bookly-subscribe-email" class="form-control" />
                    <span class="input-group-btn">
                        <button type="button" id="bookly-subscribe-btn" class="btn btn-info ladda-button" data-spinner-size="30" data-style="zoom-in">
                            <span class="ladda-label"><?php _e( 'Send', 'bookly' ) ?></span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>