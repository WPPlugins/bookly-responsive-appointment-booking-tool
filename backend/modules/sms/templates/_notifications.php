<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Entities\Notification;
use BooklyLite\Lib\Proxy;
use BooklyLite\Lib\Utils\Common;
/** @var BooklyLite\Backend\Modules\Notifications\Forms\Notifications $form */
?>
<form action="<?php echo esc_url( remove_query_arg( array( 'paypal_result', 'auto-recharge', 'tab' ) ) ) ?>" method="post">
    <input type="hidden" name="form-notifications">
    <div class="form-inline bookly-margin-bottom-xlg">
        <div class="form-group">
            <label for="admin_phone">
                <?php _e( 'Administrator phone', 'bookly' ) ?>
            </label>
            <p class="help-block"><?php _e( 'Enter a phone number in international format. E.g. for the United States a valid phone number would be +17327572923.', 'bookly' ) ?></p>
            <div>
                <input class="form-control" id="admin_phone" name="bookly_sms_administrator_phone" type="text" value="<?php form_option( 'bookly_sms_administrator_phone' ) ?>">
                <button class="btn btn-success" id="send_test_sms"><?php _e( 'Send test SMS', 'bookly' ) ?></button>
            </div>
        </div>
    </div>

    <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Single', 'bookly' ) ?></h4>

    <div class="panel-group bookly-margin-vertical-xlg" id="bookly-js-single-notifications" role="tablist" aria-multiselectable="true">
        <?php foreach ( $form->getNotifications( 'single' ) as $notification ) :
            $id = $notification['id'];
            ?>
            <div class="panel panel-default bookly-js-collapse">
                <div class="panel-heading" role="tab">
                    <div class="checkbox bookly-margin-remove">
                        <label>
                            <input name="notification[<?php echo $id ?>][active]" value="0" type="checkbox" checked="checked" class="hidden">
                            <input id="<?php echo $id ?>_active" name="notification[<?php echo $id ?>][active]" value="1" type="checkbox" <?php checked( $notification['active'] ) ?>>
                            <a href="#collapse_<?php echo $id ?>" class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#bookly-js-single-notifications">
                                <?php echo Notification::getName( $notification['type'] ) ?>
                            </a>
                        </label>
                    </div>
                </div>
                <div id="collapse_<?php echo $id ?>" class="panel-collapse collapse">
                    <div class="panel-body">

                        <?php $form->renderSendingTime( $notification ) ?>
                        <?php $form->renderEditor( $id ) ?>
                        <?php $form->renderCopy( $notification ) ?>

                        <div class="form-group">
                            <label><?php _e( 'Codes', 'bookly' ) ?></label>
                            <?php $form->renderCodes( $notification['type'] ) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <?php if ( $form->types['combined'] ) : ?>
        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Combined', 'bookly' ) ?></h4>
        <div class="panel-group bookly-margin-vertical-xlg" id="bookly-js-combined-notifications" role="tablist" aria-multiselectable="true">
            <?php foreach ( $form->getNotifications( 'combined' ) as $notification ) :
                $id = $notification['id'];
                ?>
                <div class="panel panel-default bookly-js-collapse">
                    <div class="panel-heading" role="tab">
                        <div class="checkbox bookly-margin-remove">
                            <label>
                                <input name="notification[<?php echo $id ?>][active]" value="0" type="checkbox" checked="checked" class="hidden">
                                <input id="<?php echo $id ?>_active" name="notification[<?php echo $id ?>][active]" value="1" type="checkbox" <?php checked( $notification['active'] ) ?>>
                                <a href="#collapse_<?php echo $id ?>" class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#bookly-js-combined-notifications">
                                    <?php echo Notification::getName( $notification['type'] ) ?>
                                </a>
                            </label>
                        </div>
                    </div>
                    <div id="collapse_<?php echo $id ?>" class="panel-collapse collapse">
                        <div class="panel-body">

                            <?php $form->renderEditor( $id ) ?>
                            <?php $form->renderCopy( $notification ) ?>

                            <div class="form-group">
                                <label><?php _e( 'Codes', 'bookly' ) ?></label>
                                <?php $form->renderCodes( $notification['type'] ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <?php Proxy\Shared::renderSmsNotifications( $form ) ?>

    <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Custom', 'bookly' ) ?></h4>
    <div class="panel-group bookly-margin-vertical-xlg" id="bookly-js-custom-notifications">
        <?php foreach ( $form->getNotifications( 'custom' ) as $notification ) :
            $this->render( '_custom_notification', compact( 'form', 'notification', 'statuses' ) );
        endforeach ?>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <button id="bookly-js-new-notification" type="button" class="btn btn-xlg btn-block btn-success-outline">
                    <span class="ladda-label"><i class="dashicons dashicons-plus-alt"></i>
                        <?php _e( 'New Notification', 'bookly' ) ?>
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-10">
                <?php if ( is_multisite() ) : ?>
                    <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                <?php else : ?>
                    <p><?php _e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'bookly' ) ?></p><br/>
                    <code class="bookly-text-wrap">wget -q -O - <?php echo $cron_uri ?></code>
                <?php endif ?>
            </div>
            <div class="col-md-2">
                <?php Common::optionToggle( 'bookly_ntf_processing_interval', __( 'Notification period', 'bookly' ), __( 'Set period of time when system will attempt to deliver notification to user. Notification will be discarded after period expiration.', 'bookly' ), $bookly_ntf_processing_interval_values ) ?>
            </div>
        </div>
    </div>

    <div class="panel-footer">
        <?php Common::submitButton( 'bookly-js-submit-notifications' ) ?>
        <?php Common::resetButton() ?>
    </div>
</form>