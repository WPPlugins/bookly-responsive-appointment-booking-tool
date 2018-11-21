<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Support;
/** @var \Bookly\Lib\SMS $sms */
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php esc_html_e( 'SMS Notifications', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <?php if ( $is_logged_in ) : ?>
                    <div class="row">
                        <div class="col-xs-6 col-sm-8 col-md-9 row">
                            <div class="col-sm-7">
                                <div class="bookly-page-title h4"><?php esc_html_e( 'Your balance', 'bookly' ) ?>: <b>$<?php echo $sms->getBalance() ?></b></div>
                                <div class="checkbox" style="padding-left: 17px">
                                    <label>
                                        <img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ) ?>" style="display: none; margin: 0 6px 0 -26px">
                                        <input type="checkbox" name="bookly_sms_notify_low_balance" class="bookly-admin-notify" value="1" <?php checked( get_option( 'bookly_sms_notify_low_balance' ) ) ?> />
                                        <?php esc_html_e( 'Send email notification to administrators at low balance', 'bookly' ) ?>
                                    </label>
                                </div>
                                <div class="checkbox" style="padding-left: 17px">
                                    <label>
                                        <img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ) ?>" style="display: none; margin: 0 6px 0 -26px">
                                        <input type="checkbox" name="bookly_sms_notify_weekly_summary" class="bookly-admin-notify" value="1" <?php checked( get_option( 'bookly_sms_notify_weekly_summary' ) ) ?> />
                                        <?php esc_html_e( 'Send weekly summary to administrators', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="bookly-page-title h4"><?php esc_html_e( 'Sender ID', 'bookly' ) ?>: <b class="bookly-js-sender-id"><?php echo $sms->getSenderId() ?></b> <small><a id="bookly-open-tab-sender-id" href="#"><?php esc_html_e( 'Change', 'bookly' ) ?></a></small>
                                    <?php if ( $sms->getSenderIdApprovalDate() ) : ?>
                                    <span class="help-block bookly-js-sender-id-approval-date"><?php esc_html_e( 'Approved at', 'bookly' ) ?>: <strong><?php echo \Bookly\Lib\Utils\DateTime::formatDate( $sms->getSenderIdApprovalDate() ) ?></strong></span>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-4 col-md-3">
                            <form method="post" class="btn-group pull-right">
                                <a class="btn btn-success" data-toggle="modal" href="#modal_change_password">
                                    <i class="dashicons dashicons-admin-users"></i>
                                    <?php echo $sms->getUserName() ?>
                                </a>
                                <button class="btn btn-default" type="submit" name="form-logout"><?php esc_html_e( 'Log out', 'bookly' ) ?></button>
                            </form>
                        </div>
                    </div>

                    <?php $self::renderTemplate( '_invoice', array( 'invoice' => $sms->getInvoiceData() ) ) ?>
                    <ul class="bookly-nav bookly-nav-tabs" id="sms_tabs">
                        <li class="bookly-nav-item active" data-toggle="tab" data-target="#notifications"><?php esc_html_e( 'Notifications', 'bookly' ) ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#add_money"><?php esc_html_e( 'Add money', 'bookly' ) ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#auto_recharge"><?php esc_html_e( 'Auto-Recharge', 'bookly' ) ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#purchases"><?php esc_html_e( 'Purchases', 'bookly' ) ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#sms_details"><?php esc_html_e( 'SMS Details', 'bookly' ); if ( $undelivered_count ): ?> <span class="badge bookly-bg-brand-danger"><?php echo $undelivered_count ?></span><?php endif ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#price_list"><?php esc_html_e( 'Price list', 'bookly' ) ?></li>
                        <li class="bookly-nav-item" data-toggle="tab" data-target="#sender_id"><?php esc_html_e( 'Sender ID', 'bookly' ) ?></li>
                    </ul>
                <?php endif ?>

                <?php if ( $is_logged_in ) : ?>
                    <div class="tab-content bookly-margin-top-lg">
                        <div class="tab-pane active" id="notifications"><?php include '_notifications.php' ?></div>
                        <div class="tab-pane" id="add_money"><?php include '_buttons.php' ?></div>
                        <div class="tab-pane" id="auto_recharge"><?php include '_auto_recharge.php' ?></div>
                        <div class="tab-pane" id="purchases"><?php include '_purchases.php' ?></div>
                        <div class="tab-pane" id="sms_details"><?php include '_sms_details.php' ?></div>
                        <div class="tab-pane" id="price_list"><?php include '_price.php' ?></div>
                        <div class="tab-pane" id="sender_id"><?php include '_sender_id.php' ?></div>
                    </div>
                <?php else : ?>
                    <div class="alert alert-info">
                        <p><?php esc_html_e( 'SMS Notifications (or "Bookly SMS") is a service for notifying your customers via text messages which are sent to mobile phones.', 'bookly' ) ?></p>
                        <p><?php esc_html_e( 'It is necessary to register in order to start using this service.', 'bookly' ) ?></p>
                        <p><?php esc_html_e( 'After registration you will need to configure notification messages and top up your balance in order to start sending SMS.', 'bookly' ) ?></p>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="well">
                                <div class="bookly-confirm-form"<?php if ( $email_confirm_required === false ) : ?> style="display: none;"<?php endif ?>>
                                    <legend><?php esc_html_e( 'Confirm your email', 'bookly' ) ?></legend>
                                    <p><?php esc_html_e( 'Thank you for registration.', 'bookly' ) ?></p>
                                    <p><?php printf( esc_html__( 'Confirmation is sent to %s.', 'bookly' ), $email_confirm_required ) ?></p>
                                    <p><?php esc_html_e( 'Once you confirm your e-mail address, you will get an access to Bookly SMS service.', 'bookly' ) ?></p>
                                    <a href="#" class="bookly-show-login-form"><?php esc_html_e( 'Back', 'bookly' ) ?></a>
                                </div>
                                <form method="post" class="bookly-login-form" action="<?php echo esc_url( remove_query_arg( array( 'paypal_result', 'auto-recharge', 'tab', ) ) ) ?>"<?php if ( $email_confirm_required !== false || $show_registration_form === true ) : ?> style="display: none;"<?php endif ?>>
                                    <fieldset>
                                        <legend><?php esc_html_e( 'Login', 'bookly' ) ?></legend>
                                        <div class="form-group">
                                            <label for="bookly-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                            <input id="bookly-username" class="form-control" type="text" required="required" value="" name="username">
                                        </div>
                                        <div class="form-group">
                                            <label for="bookly-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                            <input id="bookly-password" class="form-control" type="password" required="required" name="password">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="form-login" class="btn btn-success pull-right"><?php esc_html_e( 'Log In', 'bookly' ) ?></button>
                                            <a href="#" class="show-register-form"><?php esc_html_e( 'Registration', 'bookly' ) ?></a><br>
                                            <a href="#" class="show-forgot-form"><?php esc_html_e( 'Forgot password', 'bookly' ) ?></a>
                                        </div>
                                    </fieldset>
                                </form>

                                <form method="post" class="bookly-register-form"<?php if ( $show_registration_form !== true ) : ?> style="display: none;"<?php endif; ?>>
                                    <fieldset>
                                        <legend><?php esc_html_e( 'Registration', 'bookly' ) ?></legend>
                                        <div class="form-group">
                                            <label for="bookly-r-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                            <input id="bookly-r-username" name="username" class="form-control" required="required" value="" type="text">
                                        </div>
                                        <div class="form-group">
                                            <label for="bookly-r-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                            <input id="bookly-r-password" name="password" class="form-control" required="required" value="" type="password">
                                        </div>
                                        <div class="form-group">
                                            <label for="bookly-r-repeat-password"><?php esc_html_e( 'Repeat password', 'bookly' ) ?></label>
                                            <input id="bookly-r-repeat-password" name="password_repeat" class="form-control" required="required" value="" type="password">
                                        </div>
                                        <div class="form-group">
                                            <div class="checkbox">
                                                <label for="bookly-r-tos">
                                                    <input id="bookly-r-tos" name="accept_tos" required="required" value="1" type="checkbox">
                                                    <?php printf( __( 'I accept <a href="%1$s" target="_blank">Service Terms</a> and <a href="%2$s" target="_blank">Privacy Policy</a>', 'bookly' ), 'https://www.booking-wp-plugin.com/terms/', 'https://www.booking-wp-plugin.com/privacy/' ) ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" name="form-registration" class="btn btn-success pull-right"><?php esc_html_e( 'Register', 'bookly' ) ?></button>
                                            <a href="#" class="bookly-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                                        </div>
                                    </fieldset>
                                </form>

                                <form method="post" class="bookly-forgot-form" style="display: none;">
                                    <fieldset>
                                        <legend><?php esc_html_e( 'Forgot password', 'bookly' ) ?></legend>
                                        <div class="form-group">
                                            <input name="username" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Email', 'bookly' ) ?>" />
                                        </div>
                                        <div class="form-group hidden">
                                            <input name="code" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Enter code from email', 'bookly' ) ?>" />
                                        </div>
                                        <div class="form-group hidden">
                                            <input name="password" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>" />
                                        </div>
                                        <div class="form-group hidden">
                                            <input name="password_repeat" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>" />
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-success pull-right form-forgot-next" data-step="0"><?php esc_html_e( 'Next', 'bookly' ) ?></button>
                                            <a href="#" class="bookly-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <?php include '_price.php' ?>
                        </div>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <?php if ( $is_logged_in ) : ?>
            <div class="modal fade" id="modal_change_password" tabindex=-1 role="dialog">
                <form id="form-change-password">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <div class="modal-title h2"><?php esc_html_e( 'Change password', 'bookly' ) ?></div>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="old_password"><?php esc_html_e( 'Old password', 'bookly' ) ?></label>
                                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="<?php esc_attr_e( 'Old password', 'bookly' ) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="new_password"><?php esc_html_e( 'New password', 'bookly' ) ?></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="new_password_repeat"><?php esc_html_e( 'Repeat new password', 'bookly' ) ?></label>
                                    <input type="password" class="form-control" id="new_password_repeat" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <?php Inputs::renderCsrf() ?>
                                <?php Buttons::renderSubmit( 'ajax-send-change-password', 'btn-sm' ) ?>
                            </div>
                            <input type="hidden" name="action" value="bookly_change_password">
                        </div>
                    </div>
                </form>
            </div>
        <?php endif ?>
    </div>
</div>