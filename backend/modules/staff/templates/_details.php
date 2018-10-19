<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib\Utils\Common;
/** @var Bookly\Lib\Entities\Staff $staff */
?>
<form class="bookly-js-staff-details">
    <div class="form-group">
        <label for="bookly-full-name"><?php _e( 'Full name', 'bookly' ) ?></label>
        <input type="text" class="form-control" id="bookly-full-name" name="full_name" value="<?php echo esc_attr( $staff->getFullName() ) ?>"/>
    </div>
    <?php if ( Common::isCurrentUserAdmin() ) : ?>
        <div class="form-group">
            <label for="bookly-wp-user"><?php _e( 'User', 'bookly' ) ?></label>

            <p class="help-block">
                <?php _e( 'If this staff member requires separate login to access personal calendar, a regular WP user needs to be created for this purpose.', 'bookly' ) ?>
                <?php _e( 'User with "Administrator" role will have access to calendars and settings of all staff members, user with another role will have access only to personal calendar and settings.', 'bookly' ) ?>
                <?php _e( 'If you leave this field blank, this staff member will not be able to access personal calendar using WP backend.', 'bookly' ) ?>
            </p>

            <select class="form-control" name="wp_user_id" id="bookly-wp-user">
                <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
                <?php foreach ( $users_for_staff as $user ) : ?>
                    <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected( $user->ID, $staff->getWpUserId() ) ?>><?php echo $user->display_name ?></option>
                <?php endforeach ?>
            </select>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bookly-email"><?php _e( 'Email', 'bookly' ) ?></label>
                <input class="form-control" id="bookly-email" name="email"
                       value="<?php echo esc_attr( $staff->getEmail() ) ?>"
                       type="text"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bookly-phone"><?php _e( 'Phone', 'bookly' ) ?></label>
                <input class="form-control" id="bookly-phone"
                       value="<?php echo esc_attr( $staff->getPhone() ) ?>"
                       type="text"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="bookly-info"><?php _e( 'Info', 'bookly' ) ?></label>
        <p class="help-block">
            <?php printf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{staff_info}' ) ?>
        </p>
        <textarea id="bookly-info" name="info" rows="3" class="form-control"><?php echo esc_textarea( $staff->getInfo() ) ?></textarea>
    </div>

    <div class="form-group">
        <label for="bookly-visibility"><?php _e( 'Visibility', 'bookly' ) ?></label>
        <p class="help-block">
            <?php _e( 'To make staff member invisible to your customers set the visibility to "Private".', 'bookly' ) ?>
        </p>
        <select name="visibility" class="form-control" id="bookly-visibility">
            <option value="public" <?php selected( $staff->getVisibility(), 'public' ) ?>><?php _e( 'Public', 'bookly' ) ?></option>
            <option value="private" <?php selected( $staff->getVisibility(), 'private' ) ?>><?php _e( 'Private', 'bookly' ) ?></option>
        </select>
    </div>

    <?php Proxy\Shared::renderStaffForm( $staff ) ?>
    <?php Proxy\Pro::renderGoogleCalendarSettings( $tpl_data ) ?>

    <input type="hidden" name="id" value="<?php echo $staff->getId() ?>">
    <input type="hidden" name="attachment_id" value="<?php echo $staff->getAttachmentId() ?>">
    <?php Inputs::renderCsrf() ?>

    <div class="panel-footer">
        <?php if ( Common::isCurrentUserAdmin() ) : ?>
            <?php Buttons::renderDelete( 'bookly-staff-delete', 'btn-lg pull-left' ) ?>
        <?php endif ?>
        <?php Buttons::renderSubmit( 'bookly-details-save' ) ?>
        <?php Buttons::renderReset() ?>
    </div>
</form>