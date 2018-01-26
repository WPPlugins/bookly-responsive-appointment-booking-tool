<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \BooklyLite\Lib\Entities\Staff $staff */
?>
<form>
    <div class="form-group">
        <label for="bookly-full-name"><?php _e( 'Full name', 'bookly' ) ?></label>
        <input type="text" class="form-control" id="bookly-full-name" name="full_name" value="<?php echo esc_attr( $staff->getFullName() ) ?>"/>
    </div>
    <?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
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
    <?php BooklyLite\Lib\Proxy\Shared::renderStaffForm( $staff ) ?>

    <div class="form-group">
        <h3><?php _e( 'Google Calendar integration', 'bookly' ) ?></h3>
        <p class="help-block">
            <?php _e( 'Synchronize staff member appointments with Google Calendar.', 'bookly' ) ?>
        </p>
        <p>
            <?php if ( isset( $authUrl ) ) : ?>
                <?php if ( $authUrl ) : ?>
                    <a href="<?php echo $authUrl ?>"><?php _e( 'Connect', 'bookly' ) ?></a>
                <?php else : ?>
                    <?php printf( __( 'Please configure Google Calendar <a href="%s">settings</a> first', 'bookly' ), \BooklyLite\Lib\Utils\Common::escAdminUrl( \BooklyLite\Backend\Modules\Settings\Controller::page_slug, array( 'tab' => 'google_calendar' ) ) ) ?>
                <?php endif ?>
            <?php else : ?>
                <?php _e( 'Connected', 'bookly' ) ?> (<a href="<?php echo \BooklyLite\Lib\Utils\Common::escAdminUrl( \BooklyLite\Backend\Modules\Staff\Controller::page_slug, array( 'google_logout' => $staff->getId() ) ) ?>"><?php _e( 'disconnect', 'bookly' ) ?></a>)
            <?php endif ?>
        </p>
    </div>
    <?php if ( ! isset( $authUrl ) ) : ?>
        <div class="form-group">
            <label for="bookly-calendar-id"><?php _e( 'Calendar', 'bookly' ) ?></label>
            <select class="form-control" name="google_calendar_id" id="bookly-calendar-id">
                <?php foreach ( $google_calendars as $id => $calendar ) : ?>
                    <option
                        <?php selected( $staff->getGoogleCalendarId() == $id || $staff->getGoogleCalendarId() == '' && $calendar['primary'] ) ?>
                            value="<?php echo esc_attr( $id ) ?>">
                        <?php echo esc_html( $calendar['summary'] ) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>
    <?php endif ?>

    <input type="hidden" name="id" value="<?php echo $staff->getId() ?>">
    <input type="hidden" name="attachment_id" value="<?php echo $staff->getAttachmentId() ?>">
    <?php \BooklyLite\Lib\Utils\Common::csrf() ?>

    <div class="panel-footer">
        <?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
            <?php \BooklyLite\Lib\Utils\Common::deleteButton( 'bookly-staff-delete', 'btn-lg pull-left' ) ?>
        <?php endif ?>
        <?php \BooklyLite\Lib\Utils\Common::customButton( 'bookly-details-save', 'btn-lg btn-success', __( 'Save', 'bookly' ) ) ?>
        <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
    </div>
</form>