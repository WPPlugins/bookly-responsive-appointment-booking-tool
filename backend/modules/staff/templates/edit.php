<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var AB_Staff $staff
 * @var string $authUrl
 * @var array $staff_errors
 */
?>
<div id="ab-edit-staff">
    <?php AB_Utils::notice( __( 'Settings saved.', 'bookly' ), 'notice-success', isset ( $updated ) ) ?>
    <?php AB_Utils::notice( $staff_errors, 'notice-error' ) ?>

    <div class="ab-nav-head" style="">
        <h2 class="pull-left"><?php echo $staff->get( 'full_name' ) ?></h2>
    </div>
    <div class="tabbable">
        <ul class="nav nav-tabs ab-nav-tabs">
            <li class="active"><a id="ab-staff-details-tab" href="#tab1" data-toggle="tab"><?php _e( 'Details', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-services-tab" href="#services" data-toggle="tab"><?php _e( 'Services', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-schedule-tab" href="#schedule" data-toggle="tab"><?php _e( 'Schedule', 'bookly' ) ?></a></li>
            <li><a id="ab-staff-holidays-tab" href="#dayoff" data-toggle="tab"><?php _e( 'Days off', 'bookly' ) ?></a></li>
        </ul>
        <div class="tab-content">
            <div style="display: none;" class="loading-indicator">
                <span class="ab-loader"></span>
            </div>
            <div class="tab-pane active" id="tab1">
                <div id="ab-staff-details-container" class="ab-staff-tab-content">
                    <form class="ab-staff-form form-horizontal" action="" name="ab_staff" method="POST" enctype="multipart/form-data">
                        <?php if ( AB_Utils::isCurrentUserAdmin() ): ?>
                            <div class="form-group">
                                <div class="col-sm-11 col-xs-10">
                                    <label for="ab-staff-wpuser"><?php _e( 'User', 'bookly' ) ?></label>
                                    <select class="form-control" name="wp_user_id" id="ab-staff-wpuser">
                                        <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
                                        <?php foreach ( $form->getUsersForStaff( $staff->id ) as $user ) : ?>
                                            <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected( $user->ID, $staff->get( 'wp_user_id' ) ) ?>><?php echo $user->display_name ?></option>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-sm-1 col-xs-2">
                                    <img
                                        src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                                        alt=""
                                        style="margin: 28px 0 0 -20px;"
                                        class="ab-popover-ext"
                                        data-ext_id="ab-staff-popover-ext"
                                        />
                                </div>
                            </div>
                        <?php endif ?>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label class="control-label" for="ab-staff-avatar"><?php _e( 'Photo', 'bookly' ) ?></label>
                                <div id="ab-staff-avatar-image">
                                    <?php if ( $staff->get( 'avatar_url' ) ) : ?>
                                        <img src="<?php echo $staff->get( 'avatar_url' ) ?>" alt="<?php _e( 'Avatar', 'bookly' ) ?>"/>
                                        <a id="ab-delete-avatar" href="javascript:void(0)"><?php _e( 'Delete current photo', 'bookly' ) ?></a>
                                    <?php endif ?>
                                </div>
                                <input id="ab-staff-avatar" name="avatar" type="file"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-11 col-xs-10">
                                <label for="ab-staff-full-name"><?php _e( 'Full name', 'bookly' ) ?></label>
                                <input class="form-control" id="ab-staff-full-name" name="full_name" value="<?php echo esc_attr( $staff->get( 'full_name' ) ) ?>" type="text"/>
                            </div>
                            <div class="col-sm-1 col-xs-2">
                                <span style="position: relative;top: 28px;left: -20px;" class=" ab-red"> *</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label for="ab-staff-email"><?php _e( 'Email', 'bookly' ) ?></label>
                                <input class="form-control" id="ab-staff-email" name="email" value="<?php echo esc_attr( $staff->get( 'email' ) ) ?>" type="text"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label for="ab-staff-phone"><?php _e( 'Phone', 'bookly' ) ?></label>
                                <div style="clear: both"></div>
                                <input class="form-control" id="ab-staff-phone" name="phone" value="<?php echo esc_attr( $staff->get( 'phone' ) ) ?>" type="text" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <h4 class="pull-left"><?php _e( 'Google Calendar integration', 'bookly' ) ?></h4>
                                <div style="margin: 5px;display: inline-block;">
                                    <?php AB_Utils::popover( __( 'Synchronize the data of the staff member bookings with Google Calendar.', 'bookly' ) ) ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <label>
                                    <?php if ( isset( $authUrl ) ): ?>
                                        <?php if ( $authUrl ): ?>
                                            <a href="<?php echo $authUrl ?>"><?php _e( 'Connect', 'bookly' ) ?></a>
                                        <?php else: ?>
                                            <?php _e( 'Please configure Google Calendar <a href="?page=ab-settings&type=_google_calendar">settings</a> first', 'bookly' ) ?>
                                        <?php endif ?>
                                    <?php else: ?>
                                        <?php _e( 'Connected', 'bookly' ) ?> (<a href="<?php echo AB_Utils::escAdminUrl( AB_StaffController::page_slug, array( 'google_logout' => $staff->get( 'id' ) ) ) ?>" ><?php _e( 'disconnect', 'bookly' ) ?></a>)
                                    <?php endif ?>
                                </label>
                            </div>
                        </div>
                        <?php if ( ! isset( $authUrl ) ): ?>
                            <div class="form-group">
                                <div class="col-sm-11 col-xs-10">
                                    <label for="ab-calendar-id"><?php _e( 'Calendar ID', 'bookly' ) ?></label>
                                    <input class="form-control" id="ab-calendar-id" <?php disabled( isset( $authUrl ) ) ?> name="google_calendar_id" value="<?php echo esc_attr( $staff->get( 'google_calendar_id' ) ) ?>" type="text"/>
                                </div>
                                <div class="col-sm-1 col-xs-2">
                                    <img
                                        src="<?php echo plugins_url( 'backend/resources/images/help.png', AB_PATH . '/main.php' ) ?>"
                                        alt=""
                                        style="vertical-align: -30px; margin: 0;"
                                        class="ab-popover-ext"
                                        data-ext_id="ab-staff-calendar-id-popover-ext"
                                        />
                                </div>
                            </div>
                        <?php endif ?>
                        <div class="form-group">
                            <div class="col-xs-11">
                                <?php AB_Utils::submitButton() ?>
                                <?php AB_Utils::resetButton() ?>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $staff->get( 'id' ) ?>"/>
                        <input type="hidden" name="action" value="ab_update_staff"/>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="services">
                <div id="ab-staff-services-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="schedule">
                <div id="ab-staff-schedule-container" class="ab-staff-tab-content" style="display: none"></div>
            </div>
            <div class="tab-pane" id="dayoff">
                <div id="ab-staff-holidays-container" class="ab-staff-tab-content" style="display: none"></div>
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
