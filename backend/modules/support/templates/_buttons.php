<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use \BooklyLite\Lib\Utils;
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
        <li><a href="<?php echo $messages_link ?>"><?php _e( 'Show all notifications', 'bookly' ) ?></a></li>
        <?php if ( $messages_new ) : ?>
        <li><a href="#" id="bookly-js-mark-read-all-messages"><?php _e( 'Mark all notifications as read', 'bookly' ) ?></a></li>
        <?php endif ?>
    </ul>
</span>
<a href="<?php echo esc_url( $doc_link ) ?>" target="_blank" id="bookly-help-btn" class="btn btn-default-outline">
    <i class="bookly-icon bookly-icon-help"></i><?php _e( 'Documentation', 'bookly' ) ?>
</a>
<a href="#bookly-support-modal" id="bookly-contact-us-btn" class="btn btn-default-outline"
   data-processed="false"
   data-toggle="modal"
    <?php if ( $show_contact_us_notice ) : ?>
        data-trigger="manual" data-placement="bottom" data-html="1"
        data-content="<?php echo esc_attr( '<button type="button" class="close pull-right bookly-margin-left-sm"><span>&times;</span></button>' . __( 'Need help? Contact us here.', 'bookly' ) ) ?>"
    <?php endif ?>
>
    <i class="bookly-icon bookly-icon-contact-us"></i><?php _e( 'Contact Us', 'bookly' ) ?>
</a>
<a href="<?php echo $this::BOOKLY_WORDPRESS_URL ?>" id="bookly-feedback-btn" target="_blank" class="btn btn-default-outline"
   data-toggle="modal"
    <?php if ( $show_feedback_notice ) : ?>
        data-trigger="manual" data-placement="bottom" data-html="1"
        data-content="<?php echo esc_attr( '<button type="button" class="close pull-right bookly-margin-left-sm"><span>&times;</span></button><div class="pull-left">' . __( 'We care about your experience using Bookly!<br/>Leave a review and tell others what you think.', 'bookly' ) . '</div>' ) ?>"
    <?php endif ?>
>
    <i class="bookly-icon bookly-icon-feedback"></i><?php _e( 'Feedback', 'bookly' ) ?>
</a>

<div id="bookly-support-modal" class="modal fade text-left" tabindex=-1>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><?php _e( 'Leave us a message', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bookly-support-name" class="control-label"><?php _e( 'Your name', 'bookly' ) ?></label>
                    <input type="text" id="bookly-support-name" class="form-control" value="<?php echo esc_attr( $current_user->user_firstname . ' ' . $current_user->user_lastname ) ?>" />
                </div>
                <div class="form-group">
                    <label for="bookly-support-email" class="control-label"><?php _e( 'Email address', 'bookly' ) ?> <span class="bookly-color-brand-danger">*</span></label>
                    <input type="text" id="bookly-support-email" class="form-control" value="<?php echo esc_attr( $current_user->user_email ) ?>" />
                </div>
                <div class="form-group">
                    <label for="bookly-support-msg" class="control-label"><?php _e( 'How can we help you?', 'bookly' ) ?> <span class="bookly-color-brand-danger">*</span></label>
                    <textarea id="bookly-support-msg" class="form-control" rows="10"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <?php Utils\Common::csrf() ?>
                <?php Utils\Common::customButton( 'bookly-support-send', 'btn-success btn-lg', __( 'Send', 'bookly' ) ) ?>
                <?php Utils\Common::customButton( null, 'btn-default btn-lg', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>