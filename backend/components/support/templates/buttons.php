<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Support\Lib\Urls;
use Bookly\Lib\Utils;
?>
<style type="text/css">
    #bookly-tbs .page-header > .popover {
        z-index: 1039;
    }
</style>
<span class="dropdown">
    <button type="button" class="btn btn-default-outline dropdown-toggle ladda-button" data-toggle="dropdown" id="bookly-bell" aria-haspopup="true" aria-expanded="true" data-spinner-size="40" data-style="zoom-in" data-spinner-color="#DDDDDD"><span class="ladda-label"><i class="bookly-icon glyphicon glyphicon-bell"></i></span></button>
    <?php if ( $messages_new ) : ?>
    <span class="badge bookly-js-new-messages-count"><?php echo $messages_new ?></span>
    <?php endif ?>
    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="bookly-bell">
        <?php foreach ( $messages as $message ) : ?>
            <li><a href="<?php echo $messages_link ?>"><?php echo Utils\DateTime::formatDate( $message['created'] ) . ': ';
                    if ( $message['seen'] ) :
                        echo esc_html( $message['subject'] );
                    else:
                        echo '<b>' . esc_html( $message['subject'] ) . '</b>';
                    endif ?>
                </a></li>
        <?php endforeach ?>
        <li role="separator" class="divider"></li>
        <li><a href="<?php echo $messages_link ?>"><?php esc_html_e( 'Show all notifications', 'bookly' ) ?></a></li>
        <?php if ( $messages_new ) : ?>
        <li><a href="#" id="bookly-js-mark-read-all-messages"><?php esc_html_e( 'Mark all notifications as read', 'bookly' ) ?></a></li>
        <?php endif ?>
    </ul>
</span>
<a href="<?php echo esc_url( $doc_link ) ?>" target="_blank" id="bookly-help-btn" class="btn btn-default-outline">
    <i class="bookly-icon bookly-icon-help"></i><?php esc_html_e( 'Documentation', 'bookly' ) ?>
</a>

<a href="#bookly-support-modal" id="bookly-contact-us-btn" class="btn btn-default-outline"
   data-toggle="modal"
    <?php if ( $show_contact_us_notice ) : ?>
        data-processed="false"
        data-trigger="manual" data-placement="bottom" data-html="1"
        data-content="<?php echo esc_attr( '<button type="button" class="close pull-right bookly-margin-left-sm"><span>&times;</span></button>' . esc_html__( 'Need help? Contact us here.', 'bookly' ) ) ?>"
    <?php endif ?>
>
    <i class="bookly-icon bookly-icon-contact-us"></i><?php esc_html_e( 'Contact Us', 'bookly' ) ?>
</a>

<?php if ( get_user_meta( get_current_user_id(), 'bookly_feature_requests_rules_hide', true ) ) : ?>
    <a href="<?php echo Utils\Common::prepareUrlReferrers( Urls::FEATURES_REQUEST_PAGE, 'notification_bar' ) ?>" target="_blank" class="btn btn-default-outline bookly-margin-left-sm">
        <i class="bookly-icon"><i class="fas fa-lightbulb"></i></i><?php esc_html_e( 'Feature requests', 'bookly' ) ?>
    </a>
<?php else : ?>
    <div id="bookly-feature-requests-modal" class="modal fade text-left" tabindex=-1>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><?php esc_html_e( 'Feature requests', 'bookly' ) ?></h4>
                </div>
                <div class="modal-body">
                    <p>
                        <?php esc_html_e( 'In the Feature Requests section of our Community, you can make suggestions about what you\'d like to see in our future releases.', 'bookly' ) ?>
                    </p>
                    <p>
                        <?php esc_html_e( 'Before you post, please check if the same suggestion has already been made. If so, vote for ideas you like and add a comment with the details about your situation.', 'bookly' ) ?>
                    </p>
                    <p>
                        <?php esc_html_e( 'It\'s much easier for us to address a suggestion if we clearly understand the context of the issue, the problem, and why it matters to you. When commenting or posting, please consider these questions so we can get a better idea of the problem you\'re facing:', 'bookly' ) ?>
                    <div>&#9679; <?php esc_html_e( 'What is the issue you\'re struggling with?', 'bookly' ) ?></div>
                    <div>&#9679; <?php esc_html_e( 'Where in your workflow do you encounter this issue?', 'bookly' ) ?></div>
                    <div>&#9679; <?php esc_html_e( 'Is this something that impacts just you, your whole team, or your customers?', 'bookly' ) ?></div>
                    </p>
                    <div class="form-inline">
                        <input type="checkbox" id="bookly-js-dont-show-again" /> <label class="bookly-checkbox-text" for="bookly-js-dont-show-again"><?php esc_html_e( 'don\'t show this notification again', 'bookly' ) ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::renderCustom( null, 'bookly-js-proceed-requests btn-success btn-lg', esc_html__( 'Proceed to Feature requests', 'bookly' ) ) ?>
                    <?php Buttons::renderCustom( null, 'btn-default btn-lg', esc_html__( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                </div>
            </div>
        </div>
    </div>
    <button data-action="feature-request" class="btn btn-default-outline bookly-margin-left-sm">
        <i class="bookly-icon"><i class="fas fa-lightbulb"></i></i><?php esc_html_e( 'Feature requests', 'bookly' ) ?>
    </button>
<?php endif ?>

<a href="<?php echo Utils\Common::prepareUrlReferrers( Urls::REVIEWS_PAGE, 'feedback' ) ?>" id="bookly-feedback-btn" target="_blank" class="btn btn-default-outline"
    <?php if ( $show_feedback_notice ) : ?>
        data-trigger="manual" data-placement="bottom" data-html="1"
        data-content="<?php echo esc_attr( '<button type="button" class="close pull-right bookly-margin-left-sm"><span>&times;</span></button><div class="pull-left">' . esc_html__( 'We care about your experience of using Bookly!<br/>Leave a review and tell others what you think.', 'bookly' ) . '</div>' ) ?>"
    <?php endif ?>
>
    <i class="bookly-icon bookly-icon-feedback"></i><?php esc_html_e( 'Feedback', 'bookly' ) ?>
</a>

<div id="bookly-support-modal" class="modal fade text-left" tabindex=-1>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php esc_html_e( 'Leave us a message', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bookly-support-name" class="control-label"><?php esc_html_e( 'Your name', 'bookly' ) ?></label>
                    <input type="text" id="bookly-support-name" class="form-control" value="<?php echo esc_attr( $current_user->user_firstname . ' ' . $current_user->user_lastname ) ?>" />
                </div>
                <div class="form-group">
                    <label for="bookly-support-email" class="control-label"><?php esc_html_e( 'Email address', 'bookly' ) ?> <span class="bookly-color-brand-danger">*</span></label>
                    <input type="text" id="bookly-support-email" class="form-control" value="<?php echo esc_attr( $current_user->user_email ) ?>" />
                </div>
                <div class="form-group">
                    <label for="bookly-support-msg" class="control-label"><?php esc_html_e( 'How can we help you?', 'bookly' ) ?> <span class="bookly-color-brand-danger">*</span></label>
                    <textarea id="bookly-support-msg" class="form-control" rows="10"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <?php Inputs::renderCsrf() ?>
                <?php Buttons::renderCustom( 'bookly-support-send', 'btn-success btn-lg', esc_html__( 'Send', 'bookly' ) ) ?>
                <?php Buttons::renderCustom( null, 'btn-default btn-lg', esc_html__( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>