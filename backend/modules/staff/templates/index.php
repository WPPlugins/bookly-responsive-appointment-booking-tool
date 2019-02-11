<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Lib;
use Bookly\Backend\Modules\Staff\Proxy;
/** @var BooklyPro\Lib\Entities\StaffCategory[] $categories */
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <?php if ( Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                <div class="bookly-page-title">
                    <?php _e( 'Staff Members', 'bookly' ) ?>
                    <span class="bookly-color-gray">(<span id="bookly-staff-count"></span>) <small class="text-muted" id="bookly-staff-archived-count"></small></span>
                </div>
                <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
            <?php else : ?>
                <div class="bookly-page-title">
                    <?php _e( 'Profile', 'bookly' ) ?>
                </div>
            <?php endif ?>
        </div>
        <div class="row">
            <div id="bookly-sidebar" class="col-sm-4"
                <?php if ( ! Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                    style="display: none"
                <?php endif ?>
            >
                <div id="bookly-js-staff-list" class="bookly-nav">
                    <div id="bookly-staff-categories">
                    <?php foreach ( $categories as $category ) : ?>
                        <div class="panel panel-default bookly-collapse<?php if ( $category['id'] === null ) : ?> bookly-js-unsortable<?php endif ?>" data-category="<?php echo $category['id'] ?: '' ?>">
                            <div class="panel-heading">
                                <div class="bookly-flexbox">
                                    <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%;">
                                        <?php if ( $category['id'] !== null ) : ?>
                                        <i class="bookly-js-categories-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move ui-sortable-handle" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                                        <?php endif ?>
                                    </div>
                                    <div class="bookly-flex-cell bookly-vertical-middle bookly-js-category-name">
                                        <a href="#category<?php echo $category['id'] ?>" class="panel-title<?php if ( $category['collapsed'] ) : ?> collapsed<?php endif ?>" role="button" data-toggle="collapse">
                                            <?php echo esc_html( $category['name'] ) ?>
                                        </a>
                                        <input class="form-control input-lg collapse" type="text" value="<?php echo esc_html( $category['name'] ) ?>"/>
                                    </div>
                                    <div class="bookly-flex-cell bookly-vertical-middle bookly-js-new-staff-member" style="width: 1%; padding-left: 10px;">
                                        <a href="#" style="font-size: 15px;" title="<?php esc_attr_e( 'Add new item to the category', 'bookly' ) ?>"><i class="fa fa-user-plus"></i> </a>
                                    </div>
                                    <?php if ( $category['id'] !== null ) : ?>
                                    <div class="bookly-flex-cell bookly-vertical-middle bookly-js-edit-category" style="width: 1%; padding-left: 10px;">
                                        <a href="#" style="font-size: 15px;" title="<?php esc_attr_e( 'Edit category name', 'bookly' ) ?>"><i class="fa fa-edit"></i> </a>
                                    </div>
                                    <div class="bookly-flex-cell bookly-vertical-middle bookly-js-delete-category" style="width: 1%; padding-left: 10px;">
                                        <a href="#" style="font-size: 15px;" title="<?php esc_attr_e( 'Delete category', 'bookly' ) ?>"><i class="fa fa-trash-alt"></i> </a>
                                    </div>
                                    <?php endif ?>
                                </div>
                            </div>
                            <div id="category<?php echo $category['id'] ?>" class="panel-collapse collapse<?php if ( ! $category['collapsed'] ) : ?> in<?php endif ?>">
                                <div class="panel-body">
                                    <ul class="bookly-js-staff-members" style="min-height: 10px;">
                                    <?php foreach ( $staff_members as $staff ) : ?>
                                        <?php if ( $staff['category_id'] == $category['id'] ): ?>
                                            <?php include '_list_item.php' ?>
                                        <?php endif ?>
                                    <?php endforeach ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                    </div>
                    <?php Proxy\Pro::renderStaffList() ?>
                </div>
                <?php Proxy\Pro::renderStaffPositionMessage() ?>
            </div>
            <div id="bookly-new-staff-form" style="display: none;">
                <div class="form-group bookly-margin-bottom-md">
                    <label for="bookly-new-staff-wpuser"><?php _e( 'User', 'bookly' ) ?></label>
                    <p class="help-block">
                        <?php _e( 'If this staff member requires separate login to access personal calendar, a regular WP user needs to be created for this purpose.', 'bookly' ) ?>
                        <?php _e( 'User with "Administrator" role will have access to calendars and settings of all staff members, user with another role will have access only to personal calendar and settings.', 'bookly' ) ?>
                        <?php _e( 'If you leave this field blank, this staff member will not be able to access personal calendar using WP backend.', 'bookly' ) ?>
                    </p>
                    <select class="form-control" name="bookly-new-staff-wpuser" id="bookly-new-staff-wpuser">
                        <option value=""><?php _e( 'Select from WP users', 'bookly' ) ?></option>
                        <?php foreach ( $users_for_staff as $user ) : ?>
                            <option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group bookly-margin-bottom-md">
                    <div class="form-field form-required">
                        <label for="bookly-new-staff-fullname"><?php _e( 'Full name', 'bookly' ) ?></label>
                        <input class="form-control bookly-js-new-staff-fullname" id="bookly-new-staff-fullname" name="bookly-new-staff-fullname" type="text">
                    </div>
                </div>

                <hr>
                <div class="text-right">
                    <?php Components\Controls\Buttons::renderSubmit( null, 'bookly-js-save-form' ) ?>
                    <?php Components\Controls\Buttons::renderCustom( null, 'bookly-popover-close btn-lg btn-default', __( 'Close', 'bookly' ) ) ?>
                </div>
            </div>
            <div id="bookly-new-staff-template" class="collapse">
                <li class="bookly-nav-item" id="bookly-staff-{{id}}" data-staff-id="{{id}}">
                    <div class="bookly-flexbox">
                        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                            <i class="bookly-js-handle bookly-icon bookly-icon-draghandle bookly-margin-right-sm bookly-cursor-move" title="<?php esc_attr_e( 'Reorder', 'bookly' ) ?>"></i>
                        </div>
                        <div class="bookly-flex-cell bookly-vertical-middle" style="width: 1%">
                            <div class="bookly-thumb bookly-thumb-sm bookly-margin-right-lg">
                            </div>
                        </div>
                        <div class="bookly-flex-cell bookly-vertical-middle">
                            {{name}}
                        </div>
                    </div>
                </li>
            </div>

            <div id="bookly-container-edit-staff" class="col-sm-8"></div>
        </div>
    </div>
    <?php Components\Dialogs\Common\CascadeDelete::render() ?>
    <?php Components\Dialogs\Common\UnsavedChanges::render() ?>
</div>