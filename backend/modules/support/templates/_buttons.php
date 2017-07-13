<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<a href="<?php echo esc_url( $doc_link ) ?>" target="_blank" id="bookly-help-btn" class="btn btn-default-outline">
    <i class="bookly-icon bookly-icon-help"></i><?php _e( 'Documentation', 'bookly' ) ?>
</a>
<a href="#bookly-support-modal" id="bookly-contact-us-btn" class="btn btn-default-outline"
   data-processed="false"
   data-toggle="modal"
    <?php if ( $show_contact_us_notice ): ?>
        data-trigger="manual" data-placement="bottom" data-html="1"
        data-content="<?php echo esc_attr( '<button type="button" class="close pull-right bookly-margin-left-sm"><span>&times;</span></button>' . __( 'Need help? Contact us here.', 'bookly' ) ) ?>"
    <?php endif ?>
>
    <i class="bookly-icon bookly-icon-contact-us"></i><?php _e( 'Contact Us', 'bookly' ) ?>
</a>
<a href="https://wordpress.org/support/plugin/bookly-responsive-appointment-booking-tool/reviews/" id="bookly-feedback-btn" target="_blank" class="btn btn-default-outline"
   data-toggle="modal"
    <?php if ( $show_feedback_notice ): ?>
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
                <?php \BooklyLite\Lib\Utils\Common::customButton( 'bookly-support-send', 'btn-success btn-lg', __( 'Send', 'bookly' ) ) ?>
                <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'btn-default btn-lg', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
            </div>
        </div>
    </div>
</div>