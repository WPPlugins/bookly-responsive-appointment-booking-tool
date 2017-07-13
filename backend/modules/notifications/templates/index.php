<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $bookly_email_sender_name  = get_option( 'bookly_email_sender_name' ) == '' ?
        get_option( 'blogname' )    : get_option( 'bookly_email_sender_name' );
    $bookly_email_sender = get_option( 'bookly_email_sender' ) == '' ?
        get_option( 'admin_email' ) : get_option( 'bookly_email_sender' );
    $collapse_id = 0;
    $form_data = $form->getData();
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body" ng-app="notifications">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Email Notifications', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <form method="post" action="">
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
                                <input id="sender_email" name="bookly_email_sender" class="form-control ab-sender" type="text" value="<?php echo esc_attr( $bookly_email_sender ) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_email_send_as', __( 'Send emails as', 'bookly' ), __( 'HTML allows formatting, colors, fonts, positioning, etc. With Text you must use Text mode of rich-text editors below. On some servers only text emails are sent successfully.', 'bookly' ),
                                array( array( 'html', __( 'HTML', 'bookly' ) ), array( 'text', __( 'Text', 'bookly' ) ) )
                            ) ?>
                        </div>
                        <div class="col-md-6">
                            <?php \BooklyLite\Lib\Utils\Common::optionToggle( 'bookly_email_reply_to_customers', __( 'Reply directly to customers', 'bookly' ), __( 'If this option is enabled then the email address of the customer is used as a sender email address for notifications sent to staff members and administrators.', 'bookly' ) ) ?>
                        </div>
                    </div>
                    <?php if ( $form->types['combined'] || \BooklyLite\Lib\Utils\Common::isPluginActive( 'bookly-addon-recurring-appointments/main.php' ) ) : ?>
                        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Single', 'bookly' ) ?></h4>
                    <?php endif ?>
                    <div class="panel-group bookly-margin-vertical-xlg" id="single">
                        <?php foreach ( $form->types['single'] as $type ) : ?>
                            <div class="panel panel-default bookly-js-collapse">
                                <div class="panel-heading" role="tab">
                                    <div class="checkbox bookly-margin-remove">
                                        <label>
                                            <input name="<?php echo $type ?>[active]" value="0" type="checkbox" checked="checked" class="hidden">
                                            <input id="<?php echo $type ?>_active" name="<?php echo $type ?>[active]" value="1" type="checkbox" <?php checked( $form_data[ $type ]['active'] ) ?>>
                                            <a href="#collapse_<?php echo ++ $collapse_id ?>" class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#single">
                                                <?php echo $form_data[ $type ]['name'] ?>
                                            </a>
                                        </label>
                                    </div>
                                </div>
                                <div id="collapse_<?php echo $collapse_id ?>" class="panel-collapse collapse">
                                    <div class="panel-body">

                                        <?php $form->renderSendingTime( $type ) ?>
                                        <?php $form->renderSubject( $type ) ?>
                                        <?php $form->renderEditor( $type ) ?>
                                        <?php $form->renderCopy( $type ) ?>

                                        <div class="form-group">
                                            <label><?php _e( 'Codes', 'bookly' ) ?></label>
                                            <?php switch ( $type ) :
                                                case 'staff_agenda':       include '_codes_staff_agenda.php';       break;
                                                case 'client_new_wp_user': include '_codes_client_new_wp_user.php'; break;
                                                default:                   include '_codes.php';
                                            endswitch ?>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <?php if ( $form->types['combined'] ) : ?>
                        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Combined', 'bookly' ) ?></h4>
                        <div class="panel-group bookly-margin-vertical-xlg" id="combined">
                            <?php foreach ( $form->types['combined'] as $type ) : ?>
                                <div class="panel panel-default bookly-js-collapse">
                                    <div class="panel-heading" role="tab">
                                        <div class="checkbox bookly-margin-remove">
                                            <label>
                                                <input name="<?php echo $type ?>[active]" value="0" type="checkbox" checked="checked" class="hidden">
                                                <input id="<?php echo $type ?>_active" name="<?php echo $type ?>[active]" value="1" type="checkbox" <?php checked( $form_data[ $type ]['active'] ) ?>>
                                                <a href="#collapse_<?php echo ++ $collapse_id ?>" class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#combined">
                                                    <?php echo $form_data[ $type ]['name'] ?>
                                                </a>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="collapse_<?php echo $collapse_id ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <?php $form->renderSubject( $type ) ?>
                                            <?php $form->renderEditor( $type ) ?>
                                            <?php $form->renderCopy( $type ) ?>

                                            <div class="form-group">
                                                <label><?php _e( 'Codes', 'bookly' ) ?></label>
                                                <?php include '_codes_cart.php' ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>

                    <?php do_action( 'bookly_render_email_notifications', $form ) ?>

                    <div class="alert alert-info">
                        <?php if ( is_multisite() ) : ?>
                            <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                        <?php else : ?>
                            <p><?php _e( 'To send scheduled notifications please execute the following script hourly with your cron:', 'bookly' ) ?></p><br />
                            <code class="bookly-text-wrap">php -f <?php echo $cron_path ?></code>
                        <?php endif ?>
                    </div>
                </div>

                <div class="panel-footer">
                    <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
                    <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>

                    <div class="pull-left">
                        <button type="button" class="btn btn-default ab-test-email-notifications btn-lg">
                            <?php _e( 'Test Email Notifications', 'bookly' ) ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <?php include '_test_email_notifications_modal.php' ?>
    </div>
</div>