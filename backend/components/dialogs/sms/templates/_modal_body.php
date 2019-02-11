<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Container;
?>
<div class="bookly-js-loading" style="height: 200px;"></div>
<div class="bookly-js-loading">
    <?php Container::renderHeader( __( 'Notification settings', 'bookly' ), 'bookly-js-settings-container' ) ?>
    <input type="hidden" name="notification[id]" value="0">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="notification_name"><?php esc_attr_e( 'Name', 'bookly' ) ?></label>
                <p class="help-block"><?php esc_html_e( 'Enter notification name which will be displayed in the list.', 'bookly' ) ?></p>
                <input type="text" class="form-control" id="notification_name" name="notification[name]" value=""/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group"><label><?php esc_html_e( 'State', 'bookly' ) ?></label>
                <p class="help-block"><?php esc_html_e( 'Choose whether notification is enabled and sending messages or it is disabled and no messages are sent until you activate the notification.', 'bookly' ) ?></p>
                <div class="radio"><label><input type="radio" name="notification[active]" value="1" checked="checked"> <?php esc_html_e( 'Enabled', 'bookly' ) ?></label></div>
                <div class="radio"><label><input type="radio" name="notification[active]" value="0"> <?php esc_html_e( 'Disabled', 'bookly' ) ?></label></div>
            </div>
        </div>
    </div>

    <?php $self::renderTemplate( '_types' ) ?>
    <?php static::renderTemplate( '_settings' ) ?>

    <div class="row bookly-js-recipient-container">
        <div class="col-md-12">
            <div class="form-group">
                <label><?php esc_attr_e( 'Recipients', 'bookly' ) ?></label>
                <p class="help-block"><?php esc_html_e( 'Choose who will receive this notification.', 'bookly' ) ?></p>
                <input type="hidden" name="notification[to_customer]" value="0">
                <div class="checkbox"><label><input type="checkbox" name="notification[to_customer]" value="1"> <?php esc_attr_e( 'Client', 'bookly' ) ?></label></div>
                <input type="hidden" name="notification[to_staff]" value="0">
                <div class="checkbox"><label><input type="checkbox" name="notification[to_staff]" value="1"> <?php esc_attr_e( 'Staff', 'bookly' ) ?></label></div>
                <input type="hidden" name="notification[to_admin]" value="0">
                <div class="checkbox"><label><input type="checkbox" name="notification[to_admin]" value="1"> <?php esc_attr_e( 'Administrators', 'bookly' ) ?></label></div>
            </div>
        </div>
    </div>

    <?php Container::renderFooter() ?>
    <?php Container::renderHeader( '', 'bookly-js-message-container' ) ?>

    <?php $self::renderTemplate( '_subject' ) ?>
    <?php $self::renderTemplate( '_editor' ) ?>
    <?php $self::renderTemplate( '_codes' ) ?>
    <?php Container::renderFooter() ?>
</div>