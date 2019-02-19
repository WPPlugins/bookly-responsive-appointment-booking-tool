<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;
/** @var Bookly\Lib\Entities\Staff $staff */
?>
<form class="bookly-js-staff-details">
    <div class="form-group">
        <label for="bookly-full-name"><?php esc_html_e( 'Full name', 'bookly' ) ?></label>
        <input type="text" class="form-control" id="bookly-full-name" name="full_name" value="<?php echo esc_attr( $staff->getFullName() ) ?>"/>
    </div>
    <?php if ( Common::isCurrentUserAdmin() ) : ?>
        <div class="form-group">
            <label for="bookly-wp-user"><?php esc_html_e( 'User', 'bookly' ) ?></label>

            <p class="help-block">
                <?php esc_html_e( 'If this staff member requires separate login to access personal calendar, a regular WP user needs to be created for this purpose.', 'bookly' ) ?>
                <?php esc_html_e( 'User with "Administrator" role will have access to calendars and settings of all staff members, user with another role will have access only to personal calendar and settings.', 'bookly' ) ?>
                <?php esc_html_e( 'If you leave this field blank, this staff member will not be able to access personal calendar using WP backend.', 'bookly' ) ?>
            </p>

            <select class="form-control" name="wp_user_id" id="bookly-wp-user">
                <option value=""><?php esc_html_e( 'Select from WP users', 'bookly' ) ?></option>
                <?php foreach ( $users_for_staff as $user ) : ?>
                    <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected( $user->ID, $staff->getWpUserId() ) ?>><?php echo $user->display_name ?></option>
                <?php endforeach ?>
            </select>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bookly-email"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                <input class="form-control" id="bookly-email" name="email"
                       value="<?php echo esc_attr( $staff->getEmail() ) ?>"
                       type="text"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="bookly-phone"><?php esc_html_e( 'Phone', 'bookly' ) ?></label>
                <input class="form-control" id="bookly-phone"
                       value="<?php echo esc_attr( $staff->getPhone() ) ?>"
                       type="text"/>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="bookly-info"><?php esc_html_e( 'Info', 'bookly' ) ?></label>
        <p class="help-block">
            <?php printf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{staff_info}' ) ?>
        </p>
        <textarea id="bookly-info" name="info" rows="3" class="form-control"><?php echo esc_textarea( $staff->getInfo() ) ?></textarea>
    </div>

    <div class="form-group">
        <label for="bookly-visibility"><?php esc_html_e( 'Visibility', 'bookly' ) ?></label>
        <p class="help-block">
            <?php esc_html_e( 'To make staff member invisible to your customers set the visibility to "Private".', 'bookly' ) ?>
        </p>
        <select name="visibility" class="form-control" id="bookly-visibility" data-default="<?php echo $staff->getVisibility() ?>">
            <option value="public" <?php selected( $staff->getVisibility(), 'public' ) ?>><?php esc_html_e( 'Public', 'bookly' ) ?></option>
            <option value="private" <?php selected( $staff->getVisibility(), 'private' ) ?>><?php esc_html_e( 'Private', 'bookly' ) ?></option>
            <?php if ( Config::proActive() || $staff->getVisibility() == 'archive' ) : ?>
                <option value="archive" <?php selected( $staff->getVisibility(), 'archive' ) ?>><?php esc_html_e( 'Archive', 'bookly' ) ?></option>
            <?php endif ?>
        </select>
    </div>

    <?php Proxy\Pro::renderStaffDetails( $staff ) ?>
    <?php Proxy\Shared::renderStaffForm( $staff ) ?>
    <?php Proxy\Pro::renderGoogleCalendarSettings( $tpl_data ) ?>
    <?php Proxy\OutlookCalendar::renderCalendarSettings( $tpl_data ) ?>

    <input type="hidden" name="id" value="<?php echo $staff->getId() ?>">
    <input type="hidden" name="attachment_id" value="<?php echo $staff->getAttachmentId() ?>">
    <?php Inputs::renderCsrf() ?>

    <div class="panel-footer">
        <?php if ( Common::isCurrentUserAdmin() ) : ?>
            <?php Buttons::renderDelete( 'bookly-staff-delete', 'btn-lg pull-left' ) ?>
            <?php Buttons::renderCustom( null, 'btn-lg btn-danger ladda-button bookly-js-staff-archive pull-left', esc_html__( 'Archive', 'bookly' ), !Config::proActive() || $staff->getVisibility() == 'archive' ? array( 'style' => 'display:none;' ) : array(), '<i class="fa fa-archive"></i> {caption}' ) ?>
        <?php endif ?>
        <?php Buttons::renderCustom( 'bookly-details-save', 'btn-lg btn-success', esc_html__( 'Save', 'bookly' ) ) ?>
        <?php Buttons::renderReset() ?>
    </div>
</form>