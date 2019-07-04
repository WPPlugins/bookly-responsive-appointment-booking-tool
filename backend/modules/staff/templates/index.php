<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Lib;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <?php if ( Lib\Utils\Common::isCurrentUserAdmin() ) : ?>
                <div class="bookly-page-title">
                    <?php esc_html_e( 'Staff Members', 'bookly' ) ?>
                    <small class="bookly-color-gray">(<small class="bookly-js-staff-count"><div class="bookly-loading-16"></div></small>)
                    </small>
                </div>
            <?php else : ?>
                <div class="bookly-page-title">
                    <?php esc_html_e( 'Profile', 'bookly' ) ?>
                    <small class="bookly-js-staff-count" style="color: transparent"></small>
                </div>
            <?php endif ?>
            <?php Components\Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <?php if ( Lib\Utils\Common::isCurrentUserAdmin() ): ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search staff', 'bookly' ) ?>"/>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <select class="form-control bookly-js-select" id="bookly-filter-visibility" data-placeholder="<?php esc_attr_e( 'Visibility', 'bookly' ) ?>">
                                    <option value="public"><?php echo esc_html_e( 'Public', 'bookly' ) ?></option>
                                    <option value="private"><?php echo esc_html_e( 'Private', 'bookly' ) ?></option>
                                    <?php if ( Lib\Config::proActive() ): ?>
                                        <option value="archive"><?php echo esc_html_e( 'Archived', 'bookly' ) ?></option>
                                    <?php endif ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 form-inline bookly-margin-bottom-lg text-right">
                            <div class="form-group">
                                <?php Components\Dialogs\Staff\Categories\Proxy\Pro::renderAdd() ?>
                            </div>
                            <div class="form-group">
                                <?php Components\Controls\Buttons::renderAdd( 'bookly-js-new-staff', 'btn-success', esc_html__( 'Add staff...', 'bookly' ), array( 'data-toggle' => 'modal', 'data-target' => '#bookly-create-staff-modal', ) ) ?>
                            </div>
                        </div>
                    </div>
                <?php endif ?>
                <table id="staff-list" class="table table-striped" style="width: 100%">
                    <thead>
                    <tr>
                        <th style="display: none;"></th>
                        <th width="24"></th>
                        <th><?php esc_html_e( 'Name', 'bookly' ) ?></th>
                        <?php if ( Lib\Config::proActive() ): ?>
                            <th><?php esc_html_e( 'Category', 'bookly' ) ?></th>
                        <?php endif ?>
                        <th><?php esc_html_e( 'Email', 'bookly' ) ?></th>
                        <th><?php esc_html_e( 'Phone', 'bookly' ) ?></th>
                        <th><?php esc_html_e( 'User', 'bookly' ) ?></th>
                        <th width="75"></th>
                        <th width="16"><input type="checkbox" class="bookly-js-check-all"/></th>
                    </tr>
                    </thead>
                </table>
                <div class="text-right bookly-margin-top-lg">
                    <?php if ( Lib\Utils\Common::isCurrentUserAdmin() ): ?>
                        <?php Components\Controls\Buttons::renderDelete() ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <?php Components\Dialogs\Staff\Edit\Dialog::render() ?>
    <?php Components\Dialogs\Staff\Categories\Proxy\Pro::renderDialog() ?>
    <?php Components\Dialogs\Common\CascadeDelete::render() ?>
    <?php Components\Dialogs\Common\UnsavedChanges::render() ?>
</div>