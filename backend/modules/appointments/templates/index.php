<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Modules\Appointments\Proxy;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Appointments', 'bookly' ) ?>
            </div>
            <?php Support\Buttons::render( $self::pageSlug() ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="form-inline bookly-margin-bottom-lg text-right">
                        <?php Proxy\Pro::renderExportButton() ?>
                        <?php Proxy\Pro::renderPrintButton() ?>
                        <div class="form-group">
                            <button type="button" class="btn btn-success bookly-btn-block-xs" id="bookly-add"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'New appointment', 'bookly' ) ?></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 col-lg-1">
                        <div class="form-group">
                            <input class="form-control" type="text" id="bookly-filter-id" placeholder="<?php esc_attr_e( 'No.', 'bookly' ) ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="bookly-margin-bottom-lg bookly-relative">
                            <button type="button" class="btn btn-block btn-default" id="bookly-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                <span>
                                    <?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="bookly-margin-bottom-lg bookly-relative">
                            <button type="button" class="btn btn-block btn-default" id="bookly-filter-creation-date" data-date="any">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                <span>
                                    <?php esc_html_e( 'Created at any time', 'bookly' ) ?>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-select" id="bookly-filter-staff" data-placeholder="<?php echo esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ) ?>">
                                <?php foreach ( $staff_members as $staff ) : ?>
                                    <option value="<?php echo $staff['id'] ?>"><?php echo esc_html( $staff['full_name'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix visible-md-block"></div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-select" id="bookly-filter-customer" data-placeholder="<?php esc_attr_e( 'Customer', 'bookly' ) ?>">
                                <?php foreach ( $customers as $customer ) : ?>
                                    <option value="<?php echo $customer['id'] ?>"><?php echo esc_html( $customer['full_name'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-select" id="bookly-filter-service" data-placeholder="<?php echo esc_attr( Common::getTranslatedOption( 'bookly_l10n_label_service' ) ) ?>">
                                <option value="0"><?php esc_html_e( 'Custom', 'bookly' ) ?></option>
                                <?php foreach ( $services as $service ) : ?>
                                    <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-1">
                        <div class="form-group">
                            <select class="form-control bookly-js-select" id="bookly-filter-status" data-placeholder="<?php esc_attr_e( 'Status', 'bookly' ) ?>">
                                <?php foreach ( CustomerAppointment::getStatuses() as $status ): ?>
                                    <option value="<?php echo $status ?>"><?php echo esc_html( CustomerAppointment::statusToString( $status ) ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="bookly-appointments-list" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'No.', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Appointment Date', 'bookly' ) ?></th>
                            <th><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ) ?></th>
                            <th><?php esc_html_e( 'Customer Name', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Customer Phone', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Customer Email', 'bookly' ) ?></th>
                            <?php if ( Config::groupBookingActive() ) : ?>
                                <th><?php esc_html_e( 'Number of persons', 'bookly' ) ?></th>
                            <?php endif ?>
                            <th><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_label_service' ) ) ?></th>
                            <th><?php esc_html_e( 'Duration', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Status', 'bookly' ) ?></th>
                            <th><?php esc_html_e( 'Payment', 'bookly' ) ?></th>
                            <?php Proxy\Ratings::renderTableHeader() ?>
                            <?php if ( Config::showNotes() ): ?>
                                <th><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_label_notes' ) ) ?></th>
                            <?php endif ?>
                            <?php foreach ( $custom_fields as $custom_field ) : ?>
                                <th><?php echo $custom_field->label ?></th>
                            <?php endforeach ?>
                            <th><?php esc_html_e( 'Created', 'bookly' ) ?></th>
                            <?php if ( $show_attachments ) : ?>
                                <th><?php esc_html_e( 'Attachments', 'bookly' ) ?></th>
                            <?php endif ?>
                            <th></th>
                            <th width="16"><input type="checkbox" id="bookly-check-all" /></th>
                        </tr>
                    </thead>
                </table>

                <div class="text-right bookly-margin-top-lg">
                    <?php Controls\Buttons::renderDelete( null, null, null, array( 'data-toggle' => 'modal', 'data-target'=> '#bookly-delete-dialog' ) ) ?>
                </div>
            </div>
        </div>

        <?php Proxy\Pro::renderExportDialog( $custom_fields ) ?>
        <?php Proxy\Pro::renderPrintDialog( $custom_fields ) ?>

        <?php Dialogs\Appointment\Delete\Dialog::render() ?>
        <?php Dialogs\Appointment\Edit\Dialog::render() ?>
        <?php Proxy\Shared::renderAddOnsComponents() ?>
    </div>
</div>
