<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Entities\Notification;
use BooklyLite\Lib\Utils\Common;
use BooklyLite\Lib\Proxy;
use BooklyLite\Backend\Modules\Support;

$bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
    get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );
$bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
    get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );

/** @var BooklyLite\Backend\Modules\Notifications\Forms\Notifications $form */
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body" ng-app="notifications">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Email Notifications', 'bookly' ) ?>
            </div>
            <?php Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <form method="post" action="<?php echo Common::escAdminUrl( $this::page_slug ) ?>">
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
                            <?php Common::optionToggle( 'bookly_email_send_as', __( 'Send emails as', 'bookly' ), __( 'HTML allows formatting, colors, fonts, positioning, etc. With Text you must use Text mode of rich-text editors below. On some servers only text emails are sent successfully.', 'bookly' ),
                                array( array( 'html', __( 'HTML', 'bookly' ) ), array( 'text', __( 'Text', 'bookly' ) ) )
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?php Common::optionToggle( 'bookly_email_reply_to_customers', __( 'Reply directly to customers', 'bookly' ), __( 'If this option is enabled then the email address of the customer is used as a sender email address for notifications sent to staff members and administrators.', 'bookly' ) ) ?>
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
                </div>

                <div class="panel-footer">
                    <?php Common::csrf() ?>
                    <?php Common::submitButton() ?>
                    <?php Common::resetButton() ?>

                    <div class="pull-left">
                        <button type="button" class="btn btn-default bookly-test-email-notifications btn-lg">
                            <?php _e( 'Test Email Notifications', 'bookly' ) ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <?php include '_test_email_notifications_modal.php' ?>

        <div class="modal fade" tabindex="-1" role="dialog" id="bookly-js-continue-confirm">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="modal-title h4"><?php _e( 'Are you sure?', 'bookly' ) ?></div>
                    </div>
                    <div class="modal-body">
                        <p><?php _e( 'When creating a new notification, the page will be reloaded, and all unsaved changes will be lost.', 'bookly' ) ?></p>
                    </div>
                    <div class="modal-footer">
                        <?php Common::customButton( null, 'btn-lg btn-success bookly-js-save', __( 'Save changes', 'bookly' ) ) ?>
                        <?php Common::customButton( null, 'btn-lg btn-danger bookly-js-continue', __( 'Continue without saving', 'bookly' ) ) ?>
                        <?php Common::customButton( null, 'btn-lg btn-default', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>