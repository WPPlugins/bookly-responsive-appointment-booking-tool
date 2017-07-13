<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyLite\Lib\Entities\CustomerAppointment;
?>
<div id="bookly-tbs" class="wrap">
    <div class="bookly-tbs-body">
        <div class="page-header text-right clearfix">
            <div class="bookly-page-title">
                <?php _e( 'Appointments', 'bookly' ) ?>
            </div>
            <?php \BooklyLite\Backend\Modules\Support\Components::getInstance()->renderButtons( $this::page_slug ) ?>
        </div>
        <div class="panel panel-default bookly-main">
            <div class="panel-body">
                <div class="row">
                    <div class="form-inline bookly-margin-bottom-lg text-right">
                        <div class="form-group">
                            <button type="button" class="btn btn-default bookly-btn-block-xs" data-toggle="modal" data-target="#bookly-export-dialog"><i class="glyphicon glyphicon-export"></i> <?php _e( 'Export to CSV', 'bookly' ) ?></button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-default bookly-btn-block-xs bookly-limitation"><i class="glyphicon glyphicon-print"></i> <?php _e( 'Print', 'bookly' ) ?></button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-success bookly-btn-block-xs" id="bookly-add"><i class="glyphicon glyphicon-plus"></i> <?php _e( 'New appointment', 'bookly' ) ?></button>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-1">
                        <div class="form-group">
                            <input class="form-control" type="text" id="bookly-filter-id" placeholder="<?php esc_attr_e( 'No.', 'bookly' ) ?>" />
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <div class="bookly-margin-bottom-lg bookly-relative">
                            <button type="button" class="btn btn-block btn-default" id="bookly-filter-date" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                                <i class="dashicons dashicons-calendar-alt"></i>
                                <span>
                                    <?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( 'first day of this month' ) ?> - <?php echo \BooklyLite\Lib\Utils\DateTime::formatDate( 'last day of this month' ) ?>
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-chosen-select" id="bookly-filter-staff" data-placeholder="<?php echo esc_attr( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $staff_members as $staff ) : ?>
                                    <option value="<?php echo $staff['id'] ?>"><?php esc_html_e( $staff['full_name'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix visible-md-block"></div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-chosen-select" id="bookly-filter-customer" data-placeholder="<?php esc_attr_e( 'Customer', 'bookly' ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $customers as $customer ) : ?>
                                    <option value="<?php echo $customer['id'] ?>"><?php esc_html_e( $customer['name'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-chosen-select" id="bookly-filter-service" data-placeholder="<?php echo esc_attr( \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ) ?>">
                                <option value="-1"></option>
                                <?php foreach ( $services as $service ) : ?>
                                    <option value="<?php echo $service['id'] ?>"><?php esc_html_e( $service['title'] ) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <select class="form-control bookly-js-chosen-select" id="bookly-filter-status" data-placeholder="<?php esc_attr_e( 'Status', 'bookly' ) ?>">
                                <option value="-1"></option>
                                <option value="<?php echo CustomerAppointment::STATUS_PENDING ?>"><?php echo CustomerAppointment::statusToString( CustomerAppointment::STATUS_PENDING ) ?></option>
                                <option value="<?php echo CustomerAppointment::STATUS_APPROVED ?>"><?php echo CustomerAppointment::statusToString( CustomerAppointment::STATUS_APPROVED ) ?></option>
                                <option value="<?php echo CustomerAppointment::STATUS_CANCELLED ?>"><?php echo CustomerAppointment::statusToString( CustomerAppointment::STATUS_CANCELLED ) ?></option>
                                <option value="<?php echo CustomerAppointment::STATUS_REJECTED ?>"><?php echo CustomerAppointment::statusToString( CustomerAppointment::STATUS_REJECTED ) ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <table id="bookly-appointments-list" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th><?php _e( 'No.', 'bookly' ) ?></th>
                            <th><?php _e( 'Appointment Date', 'bookly' ) ?></th>
                            <th><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ?></th>
                            <th><?php _e( 'Customer Name', 'bookly' ) ?></th>
                            <th><?php _e( 'Customer Phone', 'bookly' ) ?></th>
                            <th><?php _e( 'Customer Email', 'bookly' ) ?></th>
                            <th><?php echo \BooklyLite\Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ?></th>
                            <th><?php _e( 'Duration', 'bookly' ) ?></th>
                            <th><?php _e( 'Status', 'bookly' ) ?></th>
                            <th><?php _e( 'Payment', 'bookly' ) ?></th>
                            <?php foreach ( $custom_fields as $custom_field ) : ?>
                                <th><?php echo $custom_field->label ?></th>
                            <?php endforeach ?>
                            <th></th>
                            <th width="16"><input type="checkbox" id="bookly-check-all" /></th>
                        </tr>
                    </thead>
                </table>

                <div class="text-right bookly-margin-top-lg">
                    <?php \BooklyLite\Lib\Utils\Common::deleteButton( '', '', '#bookly-delete-dialog' ) ?>
                </div>
            </div>
        </div>

        <?php \BooklyLite\Backend\Modules\Calendar\Components::getInstance()->renderDeleteDialog(); ?>
        <?php include '_export_dialog.php' ?>
        <?php include '_print_dialog.php' ?>

        <?php \BooklyLite\Backend\Modules\Calendar\Components::getInstance()->renderAppointmentDialog() ?>
        <?php do_action( 'bookly_render_component_appointments' ) ?>
    </div>
</div>
