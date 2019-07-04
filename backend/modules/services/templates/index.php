<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Services\Proxy;
use Bookly\Backend\Components\Dialogs;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php esc_html_e( 'Services', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search services', 'bookly' ) ?>" />
                        </div>
                    </div>
                    <div class="col-md-8 form-inline bookly-margin-bottom-lg text-right">
                        <div class="form-group">
                            <?php Controls\Buttons::renderCustom( 'bookly-js-categories', 'btn-default', esc_html__( 'Categories...', 'bookly' ), array( 'data-toggle' => 'modal', 'data-target'=> '#bookly-service-categories-modal' ) ) ?>
                        </div>
                        <div class="form-group">
                            <?php Controls\Buttons::renderAdd( 'bookly-js-new-service', 'btn-success', esc_html__( 'Add service...', 'bookly' ), array( 'data-toggle' => 'modal', 'data-target'=> '#bookly-create-service-modal' ) ) ?>
                        </div>
                    </div>
                </div>
                <table id="services-list" class="table table-striped" style="width: 100%">
                    <thead>
                    <tr>
                        <th style="display: none;"></th>
                        <th width="24"></th>
                        <?php if ( Proxy\Shared::prepareServiceTypes( array() ) ) : ?>
                            <th width="24"></th>
                        <?php endif ?>
                        <th width="24"></th>
                        <th><?php esc_html_e( 'Title', 'bookly' ) ?></th>
                        <th><?php esc_html_e( 'Category', 'bookly' ) ?></th>
                        <th><?php esc_html_e( 'Duration', 'bookly' ) ?></th>
                        <th><?php esc_html_e( 'Price', 'bookly' ) ?></th>
                        <th width="75"></th>
                        <th width="16"><input type="checkbox" class="bookly-js-check-all"/></th>
                    </tr>
                    </thead>
                </table>
                <div class="text-right bookly-margin-top-lg">
                    <?php Controls\Buttons::renderDelete() ?>
                </div>
            </div>
        </div>
    </div>
    <?php Dialogs\Common\CascadeDelete::render() ?>
    <?php Dialogs\Service\Create\Dialog::render() ?>
    <?php Dialogs\Service\Edit\Dialog::render() ?>
    <?php Dialogs\Service\Categories\Dialog::render() ?>
    <div id="bookly-update-service-settings" class="modal fade" tabindex=-1 role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div class="modal-title h2"><?php _e( 'Update service setting', 'bookly' ) ?></div>
                </div>
                <div class="modal-body">
                    <p><?php _e( 'You are about to change a service setting which is also configured separately for each staff member. Do you want to update it in staff settings too?', 'bookly' ) ?></p>
                    <div class="checkbox">
                        <label>
                            <input id="bookly-remember-my-choice" type="checkbox">
                            <?php _e( 'Remember my choice', 'bookly' ) ?>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-default bookly-no" data-dismiss="modal" aria-hidden="true">
                        <?php _e( 'No, update just here in services', 'bookly' ) ?>
                    </button>
                    <button type="submit" class="btn btn-success bookly-yes"><?php _e( 'Yes', 'bookly' ) ?></button>
                </div>
            </div>
        </div>
    </div>
</div>