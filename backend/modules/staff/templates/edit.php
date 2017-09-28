<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /** @var \BooklyLite\Lib\Entities\Staff $staff */
?>
<div class="panel panel-default bookly-main">
    <div class="panel-body">
        <div class="bookly-flexbox bookly-margin-bottom-md">
            <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                <div id="bookly-js-staff-avatar" class="bookly-thumb bookly-thumb-lg bookly-margin-right-lg">
                    <div class="bookly-flex-cell" style="width: 100%">
                        <div class="form-group">
                            <?php $img = wp_get_attachment_image_src( $staff->get( 'attachment_id' ), 'thumbnail' ) ?>

                            <div class="bookly-js-image bookly-thumb bookly-thumb-lg bookly-margin-right-lg"
                                <?php echo $img ? 'style="background-image: url(' . $img[0] . '); background-size: cover;"' : ''  ?>
                            >
                                <a class="dashicons dashicons-trash text-danger bookly-thumb-delete"
                                   href="javascript:void(0)"
                                   title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"
                                   <?php if ( !$img ) : ?>style="display: none;"<?php endif ?>>
                                </a>
                                <div class="bookly-thumb-edit">
                                    <div class="bookly-pretty">
                                        <label class="bookly-pretty-indicator bookly-thumb-edit-btn">
                                            <?php _e( 'Image', 'bookly' ) ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bookly-flex-cell bookly-vertical-top"><h1 class="bookly-js-staff-name-<?php echo $staff->get( 'id' ) ?>"><?php echo $staff->get( 'full_name' ) ?></h1></div>
        </div>

        <ul class="nav nav-tabs nav-justified bookly-nav-justified">
            <li class="active">
                <a id="bookly-details-tab" href="#details" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-info"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Details', 'bookly' ) ?></span>
                </a>
            </li>
            <li>
                <a id="bookly-services-tab" href="#services" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-checklist"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Services', 'bookly' ) ?></span>
                </a>
            </li>
            <li>
                <a id="bookly-schedule-tab" href="#schedule" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-schedule"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Schedule', 'bookly' ) ?></span>
                </a>
            </li>
            <?php do_action( 'bookly_special_days_render_tab' ) ?>
            <li>
                <a id="bookly-holidays-tab" href="#daysoff" data-toggle="tab">
                    <i class="bookly-icon bookly-icon-daysoff"></i>
                    <span class="bookly-nav-tabs-title"><?php _e( 'Days off', 'bookly' ) ?></span>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div style="display: none;" class="bookly-loading"></div>

            <div class="tab-pane active" id="details">
                <div id="bookly-details-container">
                    <form>
                        <div class="form-group">
                            <label for="bookly-full-name"><?php _e( 'Full name', 'bookly' ) ?></label>
                            <input type="text" class="form-control" id="bookly-full-name" name="full_name" value="<?php echo esc_attr( $staff->get( 'full_name' ) ) ?>"  />
                        </div>
                        <?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                            <div class="form-group">
                                <label for="bookly-wp-user"><?php _e( 'User', 'bookly' ) ?></label>

                                <p class="help-block">
                                    <?php _e( 'If this staff member requires separate login to access personal calendar, a regular WP user needs to be created for this purpose.', 'bookly' ) ?>
                                    <?php _e( 'User with "Administrator" role will have access to calendars and settings of all staff members, user with some other role will have access only to personal calendar and settings.', 'bookly' ) ?>
                                    <?php _e( 'If you will leave this field blank, this staff member will not be able to access personal calendar using WP backend.', 'bookly' ) ?>
                                </p>

                                <select class="form-control" name="wp_user_id" id="bookly-wp-user">
                                    <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
                                    <?php foreach ( $users_for_staff as $user ) : ?>
                                        <option value="<?php echo $user->ID ?>" data-email="<?php echo $user->user_email ?>" <?php selected( $user->ID, $staff->get( 'wp_user_id' ) ) ?>><?php echo $user->display_name ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        <?php endif ?>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="bookly-email"><?php _e( 'Email', 'bookly' ) ?></label>
                                    <input class="form-control" id="bookly-email" name="email"
                                           value="<?php echo esc_attr( $staff->get( 'email' ) ) ?>"
                                           type="text" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="bookly-phone"><?php _e( 'Phone', 'bookly' ) ?></label>
                                    <input class="form-control" id="bookly-phone"
                                           value="<?php echo esc_attr( $staff->get( 'phone' ) ) ?>"
                                           type="text" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bookly-info"><?php _e( 'Info', 'bookly' ) ?></label>
                            <p class="help-block">
                                <?php printf( __( 'This text can be inserted into notifications with %s code.', 'bookly' ), '{staff_info}' ) ?>
                            </p>
                            <textarea id="bookly-info" name="info" rows="3" class="form-control"><?php echo esc_textarea( $staff->get( 'info' ) ) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="bookly-visibility"><?php _e( 'Visibility', 'bookly' ) ?></label>
                            <p class="help-block">
                                <?php _e( 'To make staff member invisible to your customers set the visibility to "Private".', 'bookly' ) ?>
                            </p>
                            <select name="visibility" class="form-control" id="bookly-visibility">
                                <option value="public" <?php selected( $staff->get( 'visibility' ), 'public' ) ?>><?php _e( 'Public', 'bookly' ) ?></option>
                                <option value="private" <?php selected( $staff->get( 'visibility' ), 'private' ) ?>><?php _e( 'Private', 'bookly' ) ?></option>
                            </select>
                        </div>
                        <?php do_action( 'bookly_render_staff_form', $staff ) ?>
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
                                    <?php _e( 'Connected', 'bookly' ) ?> (<a href="<?php echo \BooklyLite\Lib\Utils\Common::escAdminUrl( \BooklyLite\Backend\Modules\Staff\Controller::page_slug, array( 'google_logout' => $staff->get( 'id' ) ) ) ?>" ><?php _e( 'disconnect', 'bookly' ) ?></a>)
                                <?php endif ?>
                            </p>
                        </div>
                        <?php if ( ! isset( $authUrl ) ) : ?>
                            <div class="form-group">
                                <label for="bookly-calendar-id"><?php _e( 'Calendar', 'bookly' ) ?></label>
                                <select class="form-control" name="google_calendar_id" id="bookly-calendar-id">
                                    <?php foreach ( $calendar_list as $id => $calendar ) : ?>
                                        <option
                                            <?php selected( $staff->get( 'google_calendar_id' ) == $id || $staff->get( 'google_calendar_id' ) == '' && $calendar['primary'] ) ?>
                                            value="<?php echo esc_attr( $id ) ?>">
                                            <?php echo esc_html( $calendar['summary'] ) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        <?php endif ?>

                        <input type="hidden" name="id" value="<?php echo $staff->get( 'id' ) ?>">
                        <input type="hidden" name="attachment_id" value="<?php echo $staff->get( 'attachment_id' ) ?>">

                        <div class="panel-footer">
                            <?php if ( \BooklyLite\Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                                <?php \BooklyLite\Lib\Utils\Common::deleteButton( 'bookly-delete', 'btn-lg pull-left' ) ?>
                            <?php endif ?>
                            <?php \BooklyLite\Lib\Utils\Common::submitButton() ?>
                            <?php \BooklyLite\Lib\Utils\Common::resetButton() ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="services">
                <div id="bookly-services-container" style="display: none"></div>
            </div>
            <div class="tab-pane" id="schedule">
                <div id="bookly-schedule-container" style="display: none"></div>
            </div>
            <div class="tab-pane" id="daysoff">
                <div id="bookly-holidays-container" style="display: none"></div>
            </div>
        </div>
    </div>
</div>