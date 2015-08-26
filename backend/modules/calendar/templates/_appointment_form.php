<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<style>
    .search-choice { display: none; }
</style>
<div ng-controller=appointmentDialogCtrl>
    <div id=ab_appointment_dialog class="modal fade">
        <div class="modal-dialog">
            <div ng-show=loading class="modal-content loading-indicator">
                <div class="modal-body">
                    <span class="ab-loader"></span>
                </div>
            </div>
            <div ng-hide=loading class="modal-content">
                <form ng-submit=processForm() class=form-horizontal>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php _e( 'New appointment', 'bookly' ) ?></h4>
                    </div>
                    <div class="modal-body">

                        <div style="padding: 0 15px;">
                            <div class=form-group>
                                <label for="ab_provider"><?php _e( 'Provider', 'bookly' ) ?></label>
                                <select id="ab_provider" class="field form-control" ng-model="form.staff" ng-options="s.full_name for s in dataSource.data.staff" ng-change="onStaffChange()"></select>
                            </div>

                            <div class=form-group>
                                <label for="ab_service"><?php _e( 'Service', 'bookly' ) ?></label>
                                <div my-slide-up="errors.service_required" style="color: red; margin-top: 5px;">
                                    <?php _e( 'Please select a service', 'bookly' ) ?>
                                </div>
                                <select id="ab_service" class="field form-control" ng-model="form.service" ng-options="s.title for s in form.staff.services" ng-change="onServiceChange()">
                                    <option value=""><?php _e( '-- Select a service --', 'bookly' ) ?></option>
                                </select>
                            </div>

                            <div class=form-group>
                                <label for="ab_date"><?php _e( 'Date', 'bookly' ) ?></label>
                                <input id="ab_date" class="form-control ab-auto-w" type=text ng-model=form.date ui-date="dateOptions" autocomplete="off" />
                            </div>
                            <div class="form-group">
                                <div ng-hide="form.service.duration == 86400">
                                    <label for="ab_period"><?php _e( 'Period', 'bookly' ) ?></label>
                                    <div>
                                        <select id="ab_period" style="display: inline" class="form-control ab-auto-w" ng-model=form.start_time
                                                ng-options="t.title for t in dataSource.data.start_time"
                                                ng-change=onStartTimeChange()></select>
                                        <span>&nbsp;<?php _e( 'to', 'bookly' ) ?>&nbsp;</span>
                                        <select style="display: inline" class="form-control ab-auto-w" ng-model=form.end_time
                                                ng-options="t.title for t in dataSource.getDataForEndTime()"
                                                ng-change=onEndTimeChange()></select>

                                        <div my-slide-up=errors.date_interval_warning id=date_interval_warning_msg style="color: green; margin-top: 5px;">
                                            <?php _e( 'Selected period doesn\'t match service duration', 'bookly' ) ?>
                                        </div>
                                        <div my-slide-up="errors.time_interval" ng-bind="errors.time_interval" style="color: red; margin-top: 5px;"></div>
                                    </div>
                                </div>
                                <div my-slide-up=errors.date_interval_not_available id=date_interval_not_available_msg style="color: red; margin-top: 5px;">
                                    <?php _e( 'The selected period is occupied by another appointment', 'bookly' ) ?>
                                </div>
                            </div>

                            <div class=form-group>
                                <label>
                                    <?php _e( 'Customers', 'bookly' ) ?>
                                    <span ng-show="form.service" title="<?php echo esc_attr( __( 'Selected / maximum', 'bookly' ) ) ?>">({{dataSource.getTotalNumberOfPersons()}}/{{form.service.capacity}})</span>
                                </label>
                                <div my-slide-up="errors.customers_required" style="color: red; margin-top: 5px;"><?php _e( 'Please select a customer', 'bookly' ) ?></div>
                                <div my-slide-up="errors.overflow_capacity" ng-bind="errors.overflow_capacity" style="color: red; margin-top: 5px;"></div>
                                <ul class="ab-customer-list">
                                    <li ng-repeat="customer in form.customers">
                                        {{customer.number_of_persons}}&times;<i class="glyphicon glyphicon-user"></i>
                                        <a ng-click="editCustomFields(customer)" title="<?php echo esc_attr( __( 'Edit booking details', 'bookly' ) ) ?>">{{customer.name}}</a>
                                        <span ng-click="removeCustomer(customer)" class="glyphicon glyphicon-remove ab-pointer" title="<?php echo esc_attr( __( 'Remove customer', 'bookly' ) ) ?>"></span>
                                    </li>
                                </ul>

                                <div ng-show="!form.service || dataSource.getTotalNumberOfPersons() < form.service.capacity">
                                    <select id="chosen" multiple data-placeholder="<?php echo esc_attr( __( '-- Search customers --', 'bookly' ) ) ?>"
                                            class="field chzn-select form-control" chosen="dataSource.data.customers"
                                            ng-model="form.customers" ng-options="c.name for c in dataSource.data.customers">
                                    </select><br/>
                                    <a href=#ab_new_customer_dialog class="{{btn_class}}" data-backdrop={{backdrop}} data-toggle="modal"><?php _e( 'New customer', 'bookly' ) ?></a>
                                </div>
                            </div>

                            <div class=form-group>
                                <label></label>
                                <input class="form-control" style="margin-top: 0" type="checkbox" ng-model=form.email_notification /> <?php _e( 'Send email notifications', 'bookly' ) ?>
                                <?php AB_Utils::popover( __( 'If email or SMS notifications are enabled and you want the customer or the staff member to be notified about this appointment after saving, tick this checkbox before clicking Save.', 'bookly' ), 'width:16px;margin-left:0;' ) ?>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <div class=dialog-button-wrapper>
                            <?php AB_Utils::submitButton() ?>
                            <a ng-click=closeDialog() class=ab-reset-form href="" data-dismiss="modal"><?php _e( 'Cancel', 'bookly' ) ?></a>
                        </div>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div style="margin-bottom: 2px;" class="ab-inline-block ab-create-customer" new-customer-dialog=createCustomer(customer) backdrop=false btn-class=""></div>
    <?php include '_custom_fields_form.php' ?>
</div>
