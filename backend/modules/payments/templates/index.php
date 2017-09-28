<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Payments', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4 col-lg-3">
                        <div class="bookly-margin-bottom-lg bookly-relative">
                            <button type="button" class="btn btn-block btn-default" id="bookly-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( '-30 day' ) ) ?> - <?php echo date( 'Y-m-d' ) ?>">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                <span>
                                    <?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( '-30 days' ) ?> - <?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( 'today' ) ?>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <div class="form-group">
                            <select id="bookly-filter-type" class="form-control bookly-js-chosen-select" data-placeholder="<?php esc_attr_e( 'Type', 'bookly' ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $types as $type ) : ?>
                                    <option value="<?php echo esc_attr( $type ) ?>">
                                        <?php echo \BooklyLite\Lib\Entities\Payment::typeToString( $type ) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <select id="bookly-filter-staff" class="form-control bookly-js-chosen-select" data-placeholder="<?php esc_attr_e( 'Provider', 'bookly' ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $providers as $provider ) : ?>
                                    <option value="<?php echo $provider['id'] ?>"><?php echo esc_html( $provider['full_name'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <select id="bookly-filter-service" class="form-control bookly-js-chosen-select" data-placeholder="<?php esc_attr_e( 'Service', 'bookly' ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $services as $service ) : ?>
                                    <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="bookly-payments-list" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th><?php _e( 'Date', 'bookly' ) ?></th>
                            <th><?php _e( 'Type', 'bookly' ) ?></th>
                            <th><?php _e( 'Customer', 'bookly' ) ?></th>
                            <th><?php _e( 'Provider', 'bookly' ) ?></th>
                            <th><?php _e( 'Service', 'bookly' ) ?></th>
                            <th><?php _e( 'Appointment Date', 'bookly' ) ?></th>
                            <th><?php _e( 'Amount', 'bookly' ) ?></th>
                            <th><?php _e( 'Status', 'bookly' ) ?></th>
                            <th></th>
                            <th width="16"><input type="checkbox" id="bookly-check-all"></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="6"><div class="pull-right"><?php _e( 'Total', 'bookly' ) ?>:</div></th>
                            <th colspan="4"><span id="bookly-payment-total"></span></th>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-right bookly-margin-top-lg">
                    <?php \BooklyLite\Lib\Utils\Common::deleteButton() ?>
                </div>
            </div>
        </div>

        <div ng-app="paymentDetails" ng-controller="paymentDetailsCtrl">
            <div payment-details-dialog></div>
            <?php \BooklyLite\Backend\Modules\Payments\Components::getInstance()->renderPaymentDetailsDialog() ?>
        </div>
    </div>
</div>
