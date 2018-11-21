<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div id="bookly-lite-rebranding-notice" class="alert alert-info bookly-tbs-body bookly-flexbox" data-action="bookly_dismiss_lite_rebranding_notice">
        <div class="bookly-flex-row">
            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
            <div class="bookly-flex-cell">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php printf( __( '<b>Bookly Lite rebrands into Bookly with more features available.</b><br/><br/>We have changed the architecture of Bookly Lite and Bookly to optimize the development of both plugin versions and add more features to the new free Bookly. To learn more about the major Bookly update, check our <a href="%s" target="_blank">blog post</a>.', 'bookly' ), 'https://www.booking-wp-plugin.com/bookly-major-update/?utm_source=bookly_admin&utm_medium=pro_not_active&utm_campaign=notification' ) ?>
            </div>
        </div>
    </div>
</div>