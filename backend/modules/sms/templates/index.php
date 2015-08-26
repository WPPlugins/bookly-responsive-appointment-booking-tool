<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var AB_SMS $sms */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'SMS Notifications', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div class="ab-wrapper-container">
            <div class="row">
                <div class="col-xs-12">
                    <div
                        class="alert alert-info"><?php _e( 'SMS Notifications (or "Bookly SMS") is a service for notifying your customers via text messages which are sent to mobile phones.<br/>It is necessary to register in order to start using this service.<br/>After registration you will need to configure notification messages and top up your balance in order to start sending SMS.', 'bookly' ) ?></div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-3">
                    <form method="post" class="ab-login-form well">

                        <fieldset>
                            <legend><?php _e( 'Login', 'bookly' ) ?></legend>
                            <div class="form-group">
                                <label for="ab_username"><?php _e( 'Email', 'bookly' ) ?></label>
                                <input id="ab_username" class="form-control" type="text" required="required" value="" name="username"/>
                            </div>
                            <div class="form-group">
                                <label for="ab_password"><?php _e( 'Password', 'bookly' ) ?></label>
                                <input id="ab_password" class="form-control" type="password" required="required" name="password"/>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="form-login" class="btn btn-info"><?php _e( 'Log In', 'bookly' ) ?></button>
                                <a href="#" class="show-register-form"><?php _e( 'Registration', 'bookly' ) ?></a>
                                <a href="#" class="show-forgot-form"><?php _e( 'Forgot password', 'bookly' ) ?></a>
                            </div>
                        </fieldset>

                    </form>

                    <form method="post" class="ab-register-form well" style="display: none;">
                        <fieldset>
                            <legend><?php _e( 'Registration', 'bookly' ) ?></legend>
                            <div class="form-group">
                                <label for="ab_r_username"><?php _e( 'Email', 'bookly' ) ?></label>
                                <input id="ab_r_username" name="username" class="form-control" required="required" value="" type="text"/>
                            </div>
                            <div class="form-group">
                                <label for="ab_r_password"><?php _e( 'Password', 'bookly' ) ?></label>
                                <input id="ab_r_password" name="password" class="form-control" required="required" value="" type="password"/>
                            </div>
                            <div class="form-group">
                                <label for="ab_r_repeat_password"><?php _e( 'Repeat password', 'bookly' ) ?></label>
                                <input id="ab_r_repeat_password" name="password_repeat" class="form-control" required="required" value="" type="password"/>
                            </div>
                            <div class="form-group">
                                <label for="ab_r_tos"><?php _e( 'Accept <a href="javascript:void(0)" data-toggle="modal" data-target="#ab-tos">Terms & Conditions</a>', 'bookly' ) ?></label>
                                <input id="ab_r_tos" name="accept_tos" class="form-control" required="required" value="1" type="checkbox" style="margin:0"/>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="form-registration" class="btn btn-info"><?php _e( 'Register', 'bookly' ) ?></button>
                                <a href="#" class="show-login-form"><?php _e( 'Log In', 'bookly' ) ?></a>
                            </div>
                        </fieldset>
                    </form>

                    <form method="post" class="ab-forgot-form well" style="display: none;">
                        <fieldset>
                            <legend><?php _e( 'Forgot password', 'bookly' ) ?></legend>
                            <div class="form-group">
                                <input name="username" class="form-control" value="" type="text" placeholder="<?php echo esc_attr( __( 'Email', 'bookly' ) ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="code" class="form-control" value="" type="text" placeholder="<?php echo esc_attr( __( 'Enter code from email', 'bookly' ) ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="password" class="form-control" value="" type="password" placeholder="<?php echo esc_attr( __( 'New password', 'bookly' ) ) ?>"/>
                            </div>
                            <div class="form-group hidden">
                                <input name="password_repeat" class="form-control" value="" type="password" placeholder="<?php echo esc_attr( __( 'Repeat new password', 'bookly' ) ) ?>"/>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-info form-forgot-next" data-step="0"><?php _e( 'Next', 'bookly' ) ?></button>
                                <a href="#" class="show-login-form"><?php _e( 'Log In', 'bookly' ) ?></a>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="col-xs-9">
                    <?php include "_price.php" ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="lite_notice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Notice', 'bookly') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('This function is disabled in the lite version of Bookly. If you find the plugin useful for your business please consider buying a licence for the full version. It costs just $46 and for this money you will get many useful functions, lifetime free update and excellent support! More information can be found here', 'bookly'); ?>: <a href="http://booking-wp-plugin.com" target="_blank">http://booking-wp-plugin.com</a>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', 'bookly') ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

