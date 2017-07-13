<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var BooklyLite\Backend\Modules\Notifications\Forms\Notifications $form */
    $collapse_id = 0;
    $form_data = $form->getData();
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
    <?php if ( $form->types['combined'] || \BooklyLite\Lib\Utils\Common::isPluginActive( 'bookly-addon-recurring-appointments/main.php' ) ) : ?>
        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Single', 'bookly' ) ?></h4>
    <?php endif ?>
    <div class="panel-group bookly-margin-vertical-xlg" id="accordion" role="tablist" aria-multiselectable="true">
        <?php foreach ( $form->types['single'] as $type ) : ?>
            <div class="panel panel-default bookly-js-collapse">
                <div class="panel-heading" role="tab">
                    <div class="checkbox bookly-margin-remove">
                        <label>
                            <input name="<?php echo $type ?>[active]" value="0" type="checkbox" checked="checked" class="hidden"/>
                            <input id="<?php echo $type ?>_active" name="<?php echo $type ?>[active]" value="1" type="checkbox" <?php checked( $form_data[ $type ]['active'] ) ?> />
                            <a class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo ++ $collapse_id ?>">
                                <?php echo $form_data[ $type ]['name'] ?>
                            </a>
                        </label>
                    </div>
                </div>
                <div id="collapse_<?php echo $collapse_id ?>" class="panel-collapse collapse">
                    <div class="panel-body">

                        <?php $form->renderSendingTime( $type ) ?>
                        <?php $form->renderEditor( $type ) ?>
                        <?php $form->renderCopy( $type ) ?>

                        <div class="form-group">
                            <label><?php _e( 'Codes', 'bookly' ) ?></label>
                            <?php switch ( $type ) :
                                case 'staff_agenda':        include '_codes_staff_agenda.php';          break;
                                case 'client_new_wp_user':  include '_codes_client_new_wp_user.php';    break;
                                default:                    include '_codes.php';
                            endswitch ?>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach ?>
    </div>

    <?php if ( $form->types['combined'] ) : ?>
        <h4 class="bookly-block-head bookly-color-gray"><?php _e( 'Combined', 'bookly' ) ?></h4>
        <div class="panel-group bookly-margin-vertical-xlg" id="accordion" role="tablist" aria-multiselectable="true">
            <?php foreach ( $form->types['combined'] as $type ) : ?>
                <div class="panel panel-default bookly-js-collapse">
                    <div class="panel-heading" role="tab">
                        <div class="checkbox bookly-margin-remove">
                            <label>
                                <input name="<?php echo $type ?>[active]" value="0" type="checkbox" class="hidden" checked>
                                <input id="<?php echo $type ?>_active" name="<?php echo $type ?>[active]" value="1" type="checkbox" <?php checked( $form_data[ $type ]['active'] ) ?>>
                                <a class="collapsed panel-title" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?php echo ++ $collapse_id ?>">
                                    <?php echo $form_data[ $type ]['name'] ?>
                                </a>
                            </label>
                        </div>
                    </div>
                    <div id="collapse_<?php echo $collapse_id ?>" class="panel-collapse collapse">
                        <div class="panel-body">
                            <?php $form->renderSendingTime( $type ) ?>
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

    <?php do_action( 'bookly_render_sms_notifications', $form ) ?>

    <div class="alert alert-info">
        <?php if ( is_multisite() ) : ?>
            <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
        <?php else : ?>
            <p><?php _e( 'To send scheduled notifications please execute the following script hourly with your cron:', 'bookly' ) ?></p><br />
            <code class="bookly-text-wrap">php -f <?php echo $cron_path ?></code>
        <?php endif ?>
    </div>

    <div class="panel-footer">
        <?php \BooklyLite\Lib\Utils\Common::submitButton( 'js-submit-notifications' ) ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>