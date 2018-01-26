<?php
/**
 * Template to show notice about "we'r starting to collect statistics about usage"
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Utils\Common;
?>
<div id="bookly-tbs" class="wrap">
    <div id="bookly-collect-stats-notice" class="alert alert-info bookly-tbs-body bookly-flexbox">
        <div class="bookly-flex-row">
            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
            <div class="bookly-flex-cell">
                <button type="button" class="close bookly-js-disallow-stats" data-dismiss="alert"></button>
                <?php _e( 'Dear customer,', 'bookly' ) ?>
                <br>
                <?php _e( 'Bookly needs your permission to collect anonymous plugin usage stats so we could constantly improve the plugin. You can always change permissions in Bookly settings.', 'bookly' ); ?>
                <br>
                <br>
                <?php Common::customButton( null, 'btn-success bookly-js-allow-stats', __( 'Allow (OK)', 'bookly' ) ) ?>
                <?php Common::customButton( null, 'btn-default bookly-js-disallow-stats', __( 'Don’t allow', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>
