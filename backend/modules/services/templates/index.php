<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Services', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="row">
            <div id="bookly-sidebar" class="col-sm-4">
                <div id="bookly-categories-list" class="bookly-nav">
                    <div class="bookly-nav-item active bookly-category-item bookly-js-all-services">
                        <div class="bookly-padding-vertical-xs"><?php _e( 'All Services', 'bookly' ) ?></div>
                    </div>
                     <ul id="bookly-category-item-list">
                        <?php foreach ( $category_collection as $category ) include '_category_item.php'; ?>
                    </ul>
                </div>

                <div class="form-group">
                    <button id="bookly-new-category" type="button"
                            class="btn btn-xlg btn-block btn-success-outline">
                        <i class="dashicons dashicons-plus-alt"></i>
                        <?php _e( 'New Category', 'bookly' ) ?>
                    </button>
                </div>

                <form method="post" id="new-category-form" style="display: none">
                    <div class="form-group bookly-margin-bottom-md">
                        <div class="form-field form-required">
                            <label for="bookly-category-name"><?php _e( 'Name', 'bookly' ) ?></label>
                            <input class="form-control" id="bookly-category-name" type="text" name="name" />
                            <input type="hidden" name="action" value="bookly_category_form" />
                        </div>
                    </div>

                    <hr />
                    <div class="text-right">
                        <button type="submit" class="btn btn-success">
                            <?php _e( 'Save', 'bookly' ) ?>
                        </button>
                        <button type="button" class="btn btn-default">
                            <?php _e( 'Cancel', 'bookly' ) ?>
                        </button>
                    </div>
                </form>
            </div>

            <div id="bookly-services-wrapper" class="col-sm-8">
                <div class="panel panel-default bookly-main">
                    <div class="panel-body">
                        <h4 class="bookly-block-head">
                            <span class="bookly-category-title"><?php _e( 'All Services', 'bookly' ) ?></span>
                            <button type="button" class="add-service ladda-button pull-right btn btn-success" data-spinner-size="40" data-style="zoom-in">
                                <span class="ladda-label"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'Add Service', 'bookly' ) ?></span>
                            </button>
                        </h4>

                        <p class="bookly-margin-top-xlg no-result" <?php if ( ! empty ( $service_collection ) ) : ?>style="display: none;"<?php endif ?>>
                            <?php _e( 'No services found. Please add services.', 'bookly' ) ?>
                        </p>

                        <div class="bookly-margin-top-xlg" id="ab-services-list">
                            <?php include '_list.php' ?>
                        </div>
                        <div class="text-right">
                            <?php \BooklyLite\Lib\Utils\Common::deleteButton() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ab-staff-update" class="modal fade" tabindex=-1 role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h2"><?php _e( 'Update service setting', 'bookly' ) ?></div>
                </div>
                <div class="modal-body">
                    <p><?php _e( 'You are about to change a service setting which is also configured separately for each staff member. Do you want to update it in staff settings too?', 'bookly' ) ?></p>
                    <div class="checkbox">
                        <label>
                            <input id="ab-remember-my-choice" type="checkbox">
                            <?php _e( 'Remember my choice', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-default ab-no" data-dismiss="modal" aria-hidden="true">
                        <?php _e( 'No, update just here in services', 'bookly' ) ?>
                    </button>
                    <button type="submit" class="btn btn-success ab-yes"><?php _e( 'Yes', 'bookly' ) ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="hidden">
    <?php do_action( 'bookly_render_after_service_list', $service_collection ) ?>
</div>