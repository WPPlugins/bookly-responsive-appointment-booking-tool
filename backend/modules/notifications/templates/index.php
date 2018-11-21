<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Notifications\Proxy;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;

$bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
    get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );
$bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
    get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );

/** @var Bookly\Backend\Modules\Notifications\Forms\Notifications $form */
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body" ng-app="notifications">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Email Notifications', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <form method="post" action="<?php echo Common::escAdminUrl( $self::pageSlug() ) ?>">
            <div class="panel panel-default bookly-main" ng-controller="emailNotifications">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sender_name"><?php _e( 'Sender name', 'bookly' ) ?></label>
                                <input id="sender_name" name="bookly_email_sender_name" class="form-control" type="text" value="<?php echo esc_attr( $bookly_email_sender_name ) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sender_email"><?php _e( 'Sender email', 'bookly' ) ?></label>
                                <input id="sender_email" name="bookly_email_sender" class="form-control bookly-sender" type="text" value="<?php echo esc_attr( $bookly_email_sender ) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php Selects::renderSingle( 'bookly_email_send_as', __( 'Send emails as', 'bookly' ), __( 'HTML allows formatting, colors, fonts, positioning, etc. With Text you must use Text mode of rich-text editors below. On some servers only text emails are sent successfully.', 'bookly' ),
                                array( array( 'html', __( 'HTML', 'bookly' ) ), array( 'text', __( 'Text', 'bookly' ) ) )
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?php Selects::renderSingle( 'bookly_email_reply_to_customers', __( 'Reply directly to customers', 'bookly' ), __( 'If this option is enabled then the email address of the customer is used as a sender email address for notifications sent to staff members and administrators.', 'bookly' ) ) ?>
                        </div>
                    </div>

                    <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Single', 'bookly' ) ?></h4>

                    <div class="panel-group bookly-margin-vertical-xlg" id="bookly-js-single-notifications">
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
                                        <?php $form->renderSubject( $id ) ?>
                                        <?php $form->renderEditor( $id ) ?>
                                        <?php $form->renderAttachIcs( $notification ) ?>
                                        <?php $form->renderAttachInvoice( $notification ) ?>
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

                    <?php if ( Config::proActive() && $form->types['combined'] ) : ?>

                        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Combined', 'bookly' ) ?></h4>

                        <div class="panel-group bookly-margin-vertical-xlg" id="bookly-js-combined-notifications">
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

                                            <?php $form->renderSubject( $id ) ?>
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

                    <?php Proxy\Shared::renderEmailNotifications( $form ) ?>

                    <?php Proxy\Pro::renderCustomEmailNotifications( $form ) ?>

                    <div id="bookly-js-tinymce-wrap" class="collapse">
                        <div id="bookly-js-tinymce-container">
                            <?php
                            $settings = array(
                                'media_buttons' => false,
                                'editor_height' => 384,
                                'tinymce'       => array(
                                    'theme_advanced_buttons1' => 'formatselect,|,bold,italic,underline,|,'.
                                                                 'bullist,blockquote,|,justifyleft,justifycenter'.
                                                                 ',justifyright,justifyfull,|,link,unlink,|'.
                                                                 ',spellchecker,wp_fullscreen,wp_adv'
                                )
                            );
                            wp_editor( '', 'bookly-js-tinymce-area', $settings );
                            ?>
                        </div>
                    </div>

                    <?php if ( Config::proActive() ) : ?>
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-10">
                                <?php if ( is_multisite() ) : ?>
                                    <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), Common::prepareUrlReferrers( 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', 'cron_setup' ), network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                                <?php else : ?>
                                    <p><?php _e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'bookly' ) ?></p><br/>
                                    <code class="bookly-text-wrap">wget -q -O - <?php echo site_url( 'wp-cron.php' ) ?></code>
                                <?php endif ?>
                            </div>
                            <div class="col-md-2">
                                <?php Selects::renderSingle( 'bookly_ntf_processing_interval', __( 'Notification period', 'bookly' ), __( 'Set period of time when system will attempt to deliver notification to user. Notification will be discarded after period expiration.', 'bookly' ), $bookly_ntf_processing_interval_values ) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif ?>
                </div>

                <div class="panel-footer">
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::renderSubmit() ?>
                    <?php Buttons::renderReset() ?>

                    <div class="pull-left">
                        <button type="button" class="btn btn-default bookly-test-email-notifications btn-lg" ng-click="showTestEmailNotificationDialog(); $event.stopPropagation();">
                            <?php _e( 'Test Email Notifications', 'bookly' ) ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <?php include '_test_email_notifications_modal.php' ?>
    </div>
</div>