<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;
/** @var Bookly\Lib\Entities\Staff $staff */
?>
<form class="bookly-js-staff-details">
    <div class="bookly-flexbox bookly-margin-bottom-lg">
        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
            <div id="bookly-js-staff-avatar" class="bookly-thumb bookly-thumb-lg bookly-margin-right-lg">
                <div class="bookly-flex-cell" style="width: 100%">
                    <div class="form-group">
                        <?php $img = wp_get_attachment_image_src( $staff->getAttachmentId(), 'thumbnail' ) ?>

                        <div class="bookly-js-image bookly-thumb bookly-thumb-lg bookly-margin-right-lg"
                            <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;"' : '' ?>
                        >
                            <a class="dashicons dashicons-trash text-danger bookly-thumb-delete"
                               href="javascript:void(0)"
                               title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"
                               <?php if ( ! $img ) : ?>style="display: none;"<?php endif ?>>
                            </a>
                            <div class="bookly-thumb-edit">
                                <div class="bookly-pretty">
                                    <label class="bookly-pretty-indicator bookly-thumb-edit-btn">
                                        <?php esc_html_e( 'Image', 'bookly' ) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="font-size: 27px;margin-left: -10px;"><?php Proxy\Ratings::renderStaffServiceRating( $staff->getId(), null, 'left' ) ?></div>
        <div class="form-group" style="margin-top: 20px; margin-bottom: 0px">
            <label for="bookly-full-name"><?php esc_html_e( 'Full name', 'bookly' ) ?></label>
            <input type="text" class="form-control" id="bookly-full-name" name="full_name" value="<?php echo esc_attr( $staff->getFullName() ) ?>"/>
        </div>
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
        <div class="col-sm-12">
            <div class="form-group">
                <label for="bookly-email"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                <input class="form-control" id="bookly-email" name="email"
                       value="<?php echo esc_attr( $staff->getEmail() ) ?>"
                       type="text"/>
            </div>
        </div>
        <div class="col-sm-12">
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

    <div class="form-group" id="bookly-visibility" data-default="<?php echo esc_attr( $staff->getVisibility() ) ?>">
        <label><?php esc_html_e( 'Visibility', 'bookly' ) ?></label>
        <p class="help-block"><?php esc_html_e( 'To make staff member invisible to your customers set the visibility to "Private".', 'bookly' ) ?></p>
        <div class="radio"><label><input type="radio" name="visibility" value="public" <?php checked( $staff->getVisibility(), 'public' ) ?>><?php esc_html_e( 'Public', 'bookly' ) ?></label></div>
        <div class="radio"><label><input type="radio" name="visibility" value="private" <?php checked( $staff->getVisibility(), 'private' ) ?>><?php esc_html_e( 'Private', 'bookly' ) ?></label></div>
        <?php if ( Config::proActive() || $staff->getVisibility() === 'archive' ) : ?>
            <div class="radio"><label><input type="radio" name="visibility" value="archive" <?php checked( $staff->getVisibility(), 'archive' ) ?>><?php esc_html_e( 'Archive', 'bookly' ) ?></label></div>
        <?php endif ?>
    </div>

    <?php Proxy\Pro::renderStaffDetails( $staff ) ?>
    <?php Proxy\Shared::renderStaffForm( $staff ) ?>
    <?php if ( $staff->getId() ) : ?>
    <?php Proxy\Pro::renderGoogleCalendarSettings( $tpl_data ) ?>
    <?php Proxy\OutlookCalendar::renderCalendarSettings( $tpl_data ) ?>
    <?php endif ?>
    <input type="hidden" name="id" value="<?php echo $staff->getId() ?>">
    <input type="hidden" name="attachment_id" value="<?php echo $staff->getAttachmentId() ?>">
    <?php Inputs::renderCsrf() ?>

    <div class="panel-footer">
        <?php if ( Common::isCurrentUserAdmin() ) : ?>
            <?php Buttons::renderDelete( 'bookly-staff-delete', 'btn-lg pull-left' ) ?>
            <?php Buttons::renderCustom( null, 'btn-lg btn-danger ladda-button bookly-js-staff-archive pull-left', esc_html__( 'Archive', 'bookly' ), !Config::proActive() || $staff->getVisibility() == 'archive' ? array( 'style' => 'display:none;' ) : array(), '<i class="fa fa-archive"></i> {caption}' ) ?>
        <?php endif ?>
        <?php Buttons::renderCustom( 'bookly-details-save', 'btn-lg btn-success bookly-js-save', esc_html__( 'Save', 'bookly' ) ) ?>
    </div>
</form>