<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div id="bookly-collect-stats-notice" class="alert alert-info bookly-tbs-body bookly-flexbox" data-action="bookly_dismiss_collect<?php if ( $enabled ): ?>ing<?php endif ?>_stats_notice">
        <div class="bookly-flex-row">
            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
            <div class="bookly-flex-cell">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php if ( $enabled ): ?>
                    <?php esc_html_e( 'To help us improve Bookly, the plugin anonymously collects usage information. You can opt out of sharing the information in Settings > General.', 'bookly' ) ?>
                    <div class="bookly-margin-top-md">
                        <button type="button" class="btn btn-primary" data-dismiss="alert"><?php esc_html_e( 'Close', 'bookly' ) ?></button>
                    </div>
                <?php else: ?>
                    <?php esc_html_e( 'Let the plugin anonymously collect usage information to help Bookly team improve the product.', 'bookly' ) ?>
                    <div class="bookly-margin-top-md">
                        <button type="button" class="btn btn-default" data-dismiss="alert"><?php esc_html_e( 'Disagree', 'bookly' ) ?></button>
                        <button type="button" class="btn btn-success" id="bookly-enable-collecting-stats-btn" data-dismiss="alert"><?php esc_html_e( 'Agree', 'bookly' ) ?></button>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>