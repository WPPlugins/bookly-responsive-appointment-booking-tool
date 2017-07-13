<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap bookly-js-subscribe-notice">
    <div id="bookly-subscribe-notice" class="alert alert-info bookly-tbs-body">
        <button type="button" class="close" data-dismiss="alert"></button>
        <i class="alert-icon"></i>
        <div class="alert-title">
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