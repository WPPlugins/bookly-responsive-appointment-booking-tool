<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Support\Lib\Urls;
use Bookly\Lib\Utils\Common;
?>
<div id="bookly-tbs" class="wrap bookly-js-nps-notice">
    <div id="bookly-nps-notice" class="alert alert-info bookly-tbs-body bookly-flexbox">
        <div class="bookly-flex-row">
            <div class="bookly-flex-cell" style="width:39px"><i class="alert-icon"></i></div>
            <div class="bookly-flex-cell">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <div id="bookly-nps-quiz">
                    <label><?php esc_html_e( 'How likely is it that you would recommend Bookly to a friend or colleague?', 'bookly' ) ?></label>
                    <select id="bookly-nps-stars" class="hidden">
                        <option value=""></option>
                        <?php for ( $i = 1; $i <= 10; ++ $i ): ?>
                            <option value="<?php echo $i ?>"><?php echo $i ?></option>
                        <?php endfor ?>
                    </select>
                </div>
                <div id="bookly-nps-form" class="bookly-margin-top-lg" style="max-width:400px;display:none;">
                    <div class="form-group">
                        <label for="bookly-nps-msg" class="control-label"><?php esc_html_e( 'What do you think should be improved?', 'bookly' ) ?></label>
                        <textarea id="bookly-nps-msg" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="bookly-nps-email" class="control-label"><?php esc_html_e( 'Please enter your email (optional)', 'bookly' ) ?></label>
                        <input type="text" id="bookly-nps-email" class="form-control" value="<?php echo esc_attr( $current_user->user_email ) ?>" />
                    </div>
                    <?php Buttons::renderCustom( 'bookly-nps-btn', 'btn-success', __( 'Send', 'bookly' ) ) ?>
                </div>
                <div id="bookly-nps-thanks" style="display:none;">
                    <?php printf(
                            __( 'Please leave your feedback <a href="%s" target="_blank">here</a>.', 'bookly' ),
                            Common::prepareUrlReferrers( Urls::REVIEWS_PAGE, 'nps' )
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>