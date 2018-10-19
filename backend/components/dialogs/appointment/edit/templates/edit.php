<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy;
use Bookly\Backend\Components\Dialogs\Appointment\AttachPayment\Proxy as AttachPaymentProxy;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
?>
<div ng-app="appointmentDialog" ng-controller="appointmentDialogCtrl">
    <div id=bookly-appointment-dialog class="modal fade" tabindex=-1 role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form ng-submit=processForm()>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <div class="modal-title h2"><?php _e( 'New appointment', 'bookly' ) ?></div>
                    </div>
                    <div ng-show=loading class="modal-body">
                        <div class="bookly-loading"></div>
                    </div>
                    <div ng-hide="loading || form.screen != 'main'" class="modal-body">
                        <div class=form-group>
                            <label for="bookly-provider"><?php _e( 'Provider', 'bookly' ) ?></label>
                            <select id="bookly-provider" class="form-control" ng-model="form.staff" ng-options="s.full_name + (form.staff_any == s ? ' (' + dataSource.l10n.staff_any + ')' : '') for s in dataSource.data.staff" ng-change="onStaffChange()"></select>
                        </div>

                        <div class=form-group>
                            <label for="bookly-service"><?php _e( 'Service', 'bookly' ) ?></label>
                            <select id="bookly-service" class="form-control" ng-model="form.service"
                                    ng-options="s.title for s in form.staff.services" ng-change="onServiceChange()">
                                <option value=""><?php _e( '-- Select a service --', 'bookly' ) ?></option>
                            </select>
                            <p class="text-danger" my-slide-up="errors.service_required">
                                <?php _e( 'Please select a service', 'bookly' ) ?>
                            </p>
                        </div>

                        <?php Proxy\Pro::renderCustomServiceFields() ?>

                        <?php if ( Config::locationsActive() ): ?>
                            <div class="form-group">
                                <label for="bookly-appointment-location"><?php _e( 'Location', 'bookly' ) ?></label>
                                <select id="bookly-appointment-location" class="form-control" ng-model="form.location"
                                        ng-options="l.name for l in form.staff.locations" ng-change="onLocationChange()">
                                    <option value=""></option>
                                </select>
                            </div>
                        <?php endif ?>

                        <?php Proxy\Tasks::renderSkipDate() ?>

                        <div ng-hide="form.skip_date">
                            <div class=form-group>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <label for="bookly-date"><?php _e( 'Date', 'bookly' ) ?></label>
                                        <input id="bookly-date" class="form-control" type=text
                                               ng-model=form.date ui-date="dateOptions" autocomplete="off"
                                               ng-change=onDateChange()>
                                    </div>
                                    <div class="col-sm-8">
                                        <div ng-hide="form.service.duration >= 86400">
                                            <label for="bookly-period"><?php _e( 'Period', 'bookly' ) ?></label>
                                            <div class="bookly-flexbox">
                                                <div class="bookly-flex-cell">
                                                    <select id="bookly-period" class="form-control" ng-model=form.start_time
                                                            ng-options="t.title for t in dataSource.getDataForStartTime()"
                                                            ng-change=onStartTimeChange()></select>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 4%">
                                                    <div class="bookly-margin-horizontal-md"><?php _e( 'to', 'bookly' ) ?></div>
                                                </div>
                                                <div class="bookly-flex-cell" style="width: 48%">
                                                    <select class="form-control" ng-model=form.end_time
                                                            ng-options="t.title for t in form.end_time_data"
                                                            ng-change=onEndTimeChange()></select>
                                                </div>
                                            </div>
                                            <p class="text-success" my-slide-up=errors.date_interval_warning id=date_interval_warning_msg>
                                                <?php _e( 'Selected period doesn\'t match service duration', 'bookly' ) ?>
                                            </p>
                                            <p class="text-success" my-slide-up="errors.time_interval" ng-bind="errors.time_interval"></p>
                                        </div>
                                    </div>
                                    <div class="text-success col-sm-12" my-slide-up=errors.date_interval_not_available id=date_interval_not_available_msg>
                                        <?php _e( 'The selected period is occupied by another appointment', 'bookly' ) ?>
                                    </div>
                                </div>
                                <p class="text-success" my-slide-up=errors.interval_not_in_staff_schedule id=interval_not_in_staff_schedule_msg>
                                    <?php _e( 'Selected period doesn\'t match provider\'s schedule', 'bookly' ) ?>
                                </p>
                                <p class="text-success" my-slide-up=errors.interval_not_in_service_schedule id=interval_not_in_service_schedule_msg>
                                    <?php _e( 'Selected period doesn\'t match service schedule', 'bookly' ) ?>
                                </p>
                            </div>

                            <?php Proxy\RecurringAppointments::renderSubForm() ?>
                        </div>

                        <div class=form-group>
                            <label for="bookly-select2"><?php _e( 'Customers', 'bookly' ) ?></label>
                            <span ng-show="form.service && form.service.id" title="<?php esc_attr_e( 'Selected / maximum', 'bookly' ) ?>">
                                ({{dataSource.getTotalNumberOfPersons()}}/{{form.service.capacity_max}})
                            </span>
                            <span ng-show="form.customers.length > 5" ng-click="form.expand_customers_list = !form.expand_customers_list" role="button">
                                <i class="fa fa-fw" ng-class="{'fa-angle-down':!form.expand_customers_list, 'fa-angle-up':form.expand_customers_list}"></i>
                            </span>
                            <p class="text-success" ng-show=form.service my-slide-up="form.service.capacity_min > 1 && form.service.capacity_min > dataSource.getTotalNumberOfPersons()">
                                <?php _e( 'Minimum capacity', 'bookly' ) ?>: {{form.service.capacity_min}}
                            </p>
                            <ul class="bookly-flexbox">
                                <li ng-repeat="customer in form.customers" class="bookly-flex-row" ng-hide="$index > 4 && !form.expand_customers_list">
                                    <a ng-click="editCustomerDetails(customer)" title="<?php esc_attr_e( 'Edit booking details', 'bookly' ) ?>" class="bookly-flex-cell bookly-padding-bottom-sm" href>{{customer.name}}</a>
                                    <span class="bookly-flex-cell text-right text-nowrap bookly-padding-bottom-sm">
                                        <?php Proxy\Shared::renderAppointmentDialogCustomersList() ?>
                                        <span class="dropdown">
                                            <button type="button" class="btn btn-sm btn-default bookly-margin-left-xs" data-toggle="dropdown" popover="<?php esc_attr_e( 'Status', 'bookly' ) ?>: {{statusToString(customer.status)}}">
                                                <span ng-class="{'fa fa-fw': true, 'fa-clock': customer.status == 'pending', 'fa-check': customer.status == 'approved', 'fa-times': customer.status == 'cancelled', 'fa-times-circle': customer.status == 'rejected', 'fa-list-alt': customer.status == 'waitlisted', 'fa-check-circle': customer.status == 'done'}"></span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href ng-click="customer.status = 'pending'">
                                                        <span class="fa fa-fw fa-clock"></span>
                                                        <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_PENDING ) ) ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href ng-click="customer.status = 'approved'">
                                                        <span class="fa fa-fw fa-check"></span>
                                                        <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_APPROVED ) ) ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href ng-click="customer.status = 'cancelled'">
                                                        <span class="fa fa-fw fa-times"></span>
                                                        <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_CANCELLED ) ) ?>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href ng-click="customer.status = 'rejected'">
                                                        <span class="fa fa-fw fa-times-circle"></span>
                                                        <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_REJECTED ) ) ?>
                                                    </a>
                                                </li>
                                                <?php if ( Config::waitingListActive() ): ?>
                                                    <li>
                                                        <a href ng-click="customer.status = 'waitlisted'">
                                                            <span class="fa fa-fw fa-list-alt"></span>
                                                            <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_WAITLISTED ) ) ?>
                                                        </a>
                                                    </li>
                                                <?php endif ?>
                                                <?php if ( Config::tasksActive() ): ?>
                                                    <li>
                                                        <a href ng-click="customer.status = 'done'">
                                                            <span class="fa fa-fw fa-check-circle"></span>
                                                            <?php echo esc_html( CustomerAppointment::statusToString( CustomerAppointment::STATUS_DONE ) ) ?>
                                                        </a>
                                                    </li>
                                                <?php endif ?>
                                            </ul>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-default bookly-margin-left-xs" data-toggle="modal" href="#bookly-payment-details-modal" data-payment_id="{{customer.payment_id}}" ng-show="customer.payment_id || customer.payment_create" popover="<?php esc_attr_e( 'Payment', 'bookly' ) ?>: {{customer.payment_title}}" ng-disabled="customer.payment_create">
                                            <span ng-class="{'bookly-js-toggle-popover fa fa-fw': true, 'fa-clipboard-check': customer.payment_type == 'full', 'fa-hourglass': customer.payment_type == 'partial'}"></span>
                                        </button>

                                        <?php Proxy\Pro::renderAttachPaymentButton() ?>

                                        <span class="btn btn-sm btn-default disabled bookly-margin-left-xs" style="opacity:1;cursor:default;"><i class="fa fa-fw fa-user"></i>&times;{{customer.number_of_persons}}</span>
                                        <?php if ( Config::packagesActive() ) : ?>
                                        <button type="button" class="btn btn-sm btn-default bookly-margin-left-xs" ng-click="editPackageSchedule(customer)" ng-show="customer.package_id" popover="<?php esc_attr_e( 'Package schedule', 'bookly' ) ?>">
                                            <span class="fa fa-fw fa-calendar-alt"></span>
                                        </button>
                                        <?php endif ?>
                                        <a ng-click="removeCustomer(customer)" class="fa fa-fw fa-trash-alt text-danger bookly-vertical-middle" href="#"
                                           popover="<?php esc_attr_e( 'Remove customer', 'bookly' ) ?>"></a>
                                    </span>
                                </li>
                            </ul>
                            <span class="btn btn-default" ng-show="form.customers.length > 5 && !form.expand_customers_list" ng-click="form.expand_customers_list = !form.expand_customers_list" style="width: 100%; line-height: 0; padding-top: 0; padding-bottom: 8px; margin-bottom: 10px;" role="button">...</span>
                            <div <?php if ( ! Config::waitingListActive() ): ?>ng-show="!form.service || dataSource.getTotalNumberOfNotCancelledPersons() < form.service.capacity_max"<?php endif ?>>
                                <div class="form-group">
                                    <div class="input-group">
                                        <select id="bookly-select2" multiple data-placeholder="<?php esc_attr_e( '-- Search customers --', 'bookly' ) ?>"
                                                class="form-control"
                                                ng-model="form.customers" ng-options="c.name for c in dataSource.data.customers"
                                                ng-change="onCustomersChange({{form.customers}}, {{dataSource.getTotalNumberOfNotCancelledPersons()}})">
                                        </select>
                                        <span class="input-group-btn">
                                            <a class="btn btn-success" ng-click="openNewCustomerDialog()">
                                                <i class="fa fa-fw fa-plus"></i>
                                                <?php _e( 'New customer', 'bookly' ) ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-danger" my-slide-up="errors.overflow_capacity" ng-bind="errors.overflow_capacity"></p>
                            <p class="text-success" my-slide-up="errors.customers_appointments_limit" ng-repeat="customer_error in errors.customers_appointments_limit">
                                {{customer_error}}
                            </p>
                        </div>

                        <div class=form-group>
                            <label for="bookly-notification"><?php _e( 'Send notifications', 'bookly' ) ?></label>
                            <p class="help-block"><?php is_admin() ?
                                    _e( 'If email or SMS notifications are enabled and you want customers or staff member to be notified about this appointment after saving, select appropriate option before clicking Save. With "If status changed" the notifications are sent to those customers whose status has just been changed. With "To all customers" the notifications are sent to everyone in the list.', 'bookly' ) :
                                    _e( 'If email or SMS notifications are enabled and you want customers or yourself to be notified about this appointment after saving, select appropriate option before clicking Save. With "If status changed" the notifications are sent to those customers whose status has just been changed. With "To all customers" the notifications are sent to everyone in the list.', 'bookly' ) ?></p>
                            <select class="form-control" style="margin-top: 0" ng-model=form.notification id="bookly-notification" ng-init="form.notification = '<?php echo get_user_meta( get_current_user_id(), 'bookly_appointment_form_send_notifications', true ) ?>' || 'no'" >
                                <option value="no"><?php _e( 'Don\'t send', 'bookly' ) ?></option>
                                <option value="changed_status"><?php _e( 'If status changed', 'bookly' ) ?></option>
                                <option value="all"><?php _e( 'To all customers', 'bookly' ) ?></option>
                            </select>
                        </div>

                        <div class=form-group>
                            <label for="bookly-internal-note"><?php _e( 'Internal note', 'bookly' ) ?></label>
                            <textarea class="form-control" ng-model=form.internal_note id="bookly-internal-note"></textarea>
                        </div>
                    </div>
                    <?php Proxy\RecurringAppointments::renderSchedule() ?>
                    <div class="modal-footer">
                        <div ng-hide=loading>
                            <?php Proxy\Shared::renderAppointmentDialogFooter() ?>
                            <?php Buttons::renderSubmit( null, null, null, array( 'ng-hide' => 'form.repeat.enabled && !form.skip_date && form.screen == \'main\'', 'ng-disabled' => '!form.skip_date && form.repeat.enabled && schIsScheduleEmpty()', 'formnovalidate' => '' ) ) ?>
                            <?php Buttons::renderCustom( null, 'btn-lg btn-default', __( 'Cancel', 'bookly' ), array( 'ng-click' => 'closeDialog()', 'data-dismiss' => 'modal' ) ) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div customer-dialog=createCustomer(customer)></div>
    <div payment-details-dialog="callbackPayment(payment_action, payment_id, payment_title, customer_id, payment_type)"></div>

    <?php Dialogs\Appointment\CustomerDetails\Dialog::render() ?>
    <?php AttachPaymentProxy\Pro::renderAttachPaymentDialog() ?>
    <?php Dialogs\Customer\Edit::render() ?>
    <?php Dialogs\Payment\Dialog::render() ?>
</div>
