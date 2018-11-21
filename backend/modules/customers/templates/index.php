<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Customers\Proxy;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php esc_html_e( 'Customers', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search customer', 'bookly' ) ?>" />
                        </div>
                    </div>
                    <div class="col-md-8 form-inline bookly-margin-bottom-lg text-right">
                        <?php
                            Proxy\Pro::renderExportButton();
                            Proxy\Pro::renderImportButton();
                        ?>
                        <div class="form-group">
                            <button type="button" class="btn btn-success bookly-btn-block-xs" id="bookly-add" data-toggle="modal" data-target="#bookly-customer-dialog"><i class="glyphicon glyphicon-plus"></i> <?php esc_html_e( 'New customer', 'bookly' ) ?></button>
                        </div>
                    </div>
                </div>

                <table id="bookly-customers-list" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( get_option( 'bookly_l10n_label_name' ) ) ?></th>
                            <th><?php esc_html_e( get_option( 'bookly_l10n_label_first_name' ) ) ?></th>
                            <th><?php esc_html_e( get_option( 'bookly_l10n_label_last_name' ) ) ?></th>
                            <th><?php esc_html_e( 'User', 'bookly' ) ?></th>
                            <?php Proxy\CustomerGroups::renderCustomerTableHeader() ?>
                            <th><?php esc_html_e( get_option( 'bookly_l10n_label_phone' ) ) ?></th>
                            <th><?php esc_html_e( get_option( 'bookly_l10n_label_email' ) ) ?></th>
                            <?php foreach ( $info_fields as $field ) : ?>
                                <th><?php echo $field->label ?></th>
                            <?php endforeach ?>
                            <th><?php esc_html_e( 'Notes', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Last appointment', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Total appointments', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Payments', 'bookly' ) ?></th>
                            <?php Proxy\Pro::renderCustomerAddressTableHeader() ?>
                            <?php if ( \Bookly\Lib\Config::proActive() ) : ?>
                                <th>Facebook</th>
                            <?php endif ?>
                            <th></th>
                            <th width="16"><input type="checkbox" id="bookly-check-all" /></th>
                        </tr>
                    </thead>
                </table>

                <div class="text-right form-inline bookly-margin-top-lg">
                    <div class="form-group">
                        <button type="button" id="bookly-merge-with" class="btn btn-default" data-toggle="modal" data-target="#bookly-merge-dialog" disabled="disabled" style="display:none"><i class="glyphicon glyphicon-road"></i> <?php esc_html_e( 'Merge with', 'bookly' ) ?></button>
                    </div>
                    <div class="form-group">
                        <button type="button" id="bookly-select-for-merge" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i> <?php esc_html_e( 'Select for merge', 'bookly' ) ?></button>
                    </div>
                    <div class="form-group">
                        <?php Dialogs\Customer\Delete\Dialog::renderDeleteButton() ?>
                    </div>
                </div>

                <div id="bookly-merge-list" class="bookly-margin-top-xlg" style="display:none">
                    <h4><?php esc_html_e( 'Merge list', 'bookly' ) ?></h4>
                </div>
            </div>
        </div>

        <?php
            Proxy\Pro::renderImportDialog();
            Proxy\Pro::renderExportDialog( $info_fields );
            Dialogs\Customer\Delete\Dialog::render();
        ?>

        <div id="bookly-merge-dialog" class="modal fade" tabindex=-1 role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <div class="modal-title h2"><?php _e( 'Merge customers', 'bookly' ) ?></div>
                    </div>
                    <div class="modal-body">
                        <?php esc_html_e( 'You are about to merge customers from the merge list with the selected one. This will result in losing the merged customers and moving all their appointments to the selected customer. Are you sure you want to continue?', 'bookly' ) ?>
                    </div>
                    <div class="modal-footer">
                        <?php Buttons::renderCustom( 'bookly-merge', 'btn-danger btn-lg', esc_html__( 'Merge', 'bookly' ), array(), '<span class="ladda-label"><i class="glyphicon glyphicon-road"></i> {caption}</span>' ) ?>
                        <?php Buttons::renderCustom( null, 'btn-default btn-lg', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'modal' ) ) ?>
                    </div>
                </div>
            </div>
        </div>

        <div ng-app="customer" ng-controller="customerCtrl">
            <div customer-dialog=saveCustomer(customer) customer="customer"></div>
            <?php Dialogs\Customer\Edit\Dialog::render() ?>
        </div>
    </div>
</div>