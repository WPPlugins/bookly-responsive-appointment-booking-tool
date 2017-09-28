<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
    <div class="form-group">
        <button id="ab-newstaff-member" type="button" class="btn btn-xlg btn-block btn-success-outline">
            <i class="dashicons dashicons-plus-alt"></i>
            <?php _e( 'New Staff Member', 'bookly' ) ?>
        </button>
    </div>
<?php endif ?>

<div id="bookly-new-staff" style="display: none;">
    <div class="form-group bookly-margin-bottom-md">
        <label for="bookly-new-staff-wpuser"><?php _e( 'User', 'bookly' ) ?></label>
        <p class="help-block">
            <?php _e( 'If this staff member requires separate login to access personal calendar, a regular WP user needs to be created for this purpose.', 'bookly' ) ?>
            <?php _e( 'User with "Administrator" role will have access to calendars and settings of all staff members, user with some other role will have access only to personal calendar and settings.', 'bookly' ) ?>
            <?php _e( 'If you will leave this field blank, this staff member will not be able to access personal calendar using WP backend.', 'bookly' ) ?>
        </p>
        <select class="form-control" name="ab_newstaff_wpuser" id="bookly-new-staff-wpuser">
            <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
            <?php foreach ( $users_for_staff as $user ) : ?>
                <option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="form-group bookly-margin-bottom-md">
        <div class="form-field form-required">
            <label for="bookly-new-staff-fullname"><?php _e( 'Full name', 'bookly' ) ?></label>
            <input class="form-control" id="bookly-new-staff-fullname" name="ab_newstaff_fullname" type="text">
        </div>
    </div>

    <hr>
    <div class="text-right">
        <?php \BooklyLite\Lib\Utils\Common::submitButton( null, 'bookly-js-save-form' ) ?>
        <?php \BooklyLite\Lib\Utils\Common::customButton( null, 'bookly-popover-close btn-lg btn-default', __( 'Close', 'bookly' ) ) ?>
    </div>
</div>




