<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Customers', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body ab-customers-list">
        <div ng-app="customers" ng-controller="customersCtrl" class="form-horizontal ng-cloak">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="control-group">
                        <label for="ab_filter"><?php _e( 'Quick search customer', 'bookly' ) ?></label>
                        <div class=controls>
                            <input id="ab_filter" style="display: inline-block;width: auto;margin-bottom: 20px" class="form-control" type=text ng-model=filter />
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div style="display: inline;" new-customer-dialog="createCustomer(customer)" backdrop="true" btn-class="btn btn-info"></div>
                    <a href="#ab_import_customers_dialog" class="btn btn-info pull-right" data-toggle="modal"><?php _e( 'Import', 'bookly' ) ?></a>
                    <a style="margin-right: 5px;" href=#ab_new_customer_dialog class="btn btn-info pull-right" data-backdrop=true data-toggle="modal"><?php _e( 'New customer', 'bookly' ) ?></a>
                    <?php include "_import.php" ?>
                </div>
            </div>

            <div class="table-responsive">
                <table id="ab_customers_list" class="table table-striped" cellspacing=0 cellpadding=0 border=0 style="clear: both;">
                    <thead>
                    <tr>
                        <th style="width: 150px" ng-class=css_class.name><a href="" ng-click=reload({sort:'name'})><?php _e( 'Name', 'bookly' ) ?></a></th>
                        <th style="width: 150px" ng-class=css_class.wp_user><a href="" ng-click=reload({sort:'wp_user'})><?php _e( 'User', 'bookly' ) ?></a></th>
                        <th style="width: 100px" ng-class=css_class.phone><a href="" ng-click=reload({sort:'phone'})><?php _e( 'Phone', 'bookly' ) ?></a></th>
                        <th style="width: 250px" ng-class=css_class.email><a href="" ng-click=reload({sort:'email'})><?php _e( 'Email', 'bookly' ) ?></a></th>
                        <th style="width: 250px" ng-class=css_class.notes><a href="" ng-click=reload({sort:'notes'})><?php _e( 'Notes', 'bookly' ) ?></a></th>
                        <th ng-class=css_class.last_appointment><a href="" ng-click=reload({sort:'last_appointment'})><?php _e( 'Last appointment', 'bookly' ) ?></a></th>
                        <th ng-class=css_class.total_appointments><a href="" ng-click=reload({sort:'total_appointments'})><?php _e( 'Total appointments', 'bookly') ?></a></th>
                        <th ng-class=css_class.payments><a href="" ng-click=reload({sort:'payments'})><?php _e( 'Payments', 'bookly') ?></a></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="customer in dataSource.customers">
                        <td>
                            <div ng-click="customer.edit_name = true" ng-hide=customer.edit_name class=displayed-value>{{customer.name}}</div>
                            <span ng-show=customer.errors.name.required><?php _e( 'Required', 'bookly' ) ?></span>
                            <input class="form-control ab-value" ng-model=customer.name ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_name focus-me=customer.edit_name required />
                        </td>
                        <td>
                            <div ng-click="customer.edit_wp_user = true" ng-hide=customer.edit_wp_user class=displayed-value>{{customer.wp_user.display_name}}</div>
                            <select ng-model="customer.wp_user" ng-options="wp_user as wp_user.display_name for wp_user in dataSource.wp_users" ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_wp_user
                                    focus-me=customer.edit_wp_user class="form-control">
                                <option value=""></option>
                            </select>
                        </td>
                        <td class="ab-phone" click-outside="saveCustomerPhone(customer)">
                            <div ng-click="customer.edit_phone = true" ng-hide=customer.edit_phone class=displayed-value>{{customer.phone}}</div>
                            <input class="form-control ab-value" intl-tel-input=customer.edit_phone intl-tel-input-id=customer.id ng-model=customer.phone ng-show=customer.edit_phone focus-me=customer.edit_phone />
                        </td>
                        <td>
                            <div ng-click="customer.edit_email = true" ng-hide=customer.edit_email class=displayed-value>{{customer.email}}</div>
                            <input class="form-control ab-value" ng-model=customer.email ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_email focus-me=customer.edit_email />
                        </td>
                        <td>
                            <div ng-click="customer.edit_notes = true" ng-hide=customer.edit_notes class=displayed-value ng-bind-html="customer.notes | nl2br"></div>
                            <textarea class="form-control ab-value" ng-model="customer.notes" ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_notes focus-me=customer.edit_notes></textarea>
                        </td>
                        <td>
                            <div ng-model=customer.last_appointment >{{customer.last_appointment}}</div>
                        </td>
                        <td class="text-right">
                            <div ng-model=customer.total_appointments >{{customer.total_appointments}}</div>
                        </td>
                        <td class="text-right">
                            <div ng-model=customer.payments >{{customer.payments}}</div>
                        </td>
                        <td><input type="checkbox" data-customer_id="{{customer.id}}"></td>
                    </tr>
                    </tbody>
                </table>
                <div ng-hide="dataSource.customers.length || loading">
                    <td colspan=9><div class="alert alert-info"><?php _e( 'No customers.', 'bookly' ) ?></div></td>
                </div>
            </div>

            <div class="btn-toolbar">
                <div class="col-xs-8">
                    <div ng-show="dataSource.pages.length > 1">
                        <div class="btn-group" ng-show="dataSource.paginator.beg">
                            <button ng-click=reload({page:1}) class="btn btn-default">
                                1
                            </button>
                        </div>
                        <div class="btn-group">
                            <button ng-click=reload({page:page.number}) class="btn btn-default" ng-class="{'active': page.active}" ng-repeat="page in dataSource.pages">
                                {{page.number}}
                            </button>
                        </div>
                        <div class="btn-group" ng-show="dataSource.paginator.end != false">
                            <button ng-click=reload({page:dataSource.paginator.end.number}) class="btn btn-default">
                                {{dataSource.paginator.end.number}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-xs-4">
                    <a class="btn btn-info pull-right" ng-click="deleteCustomers()"><?php _e( 'Delete', 'bookly' ) ?></a>
                </div>
            </div>
            <div ng-show="loading" class="loading-indicator">
                <span class="ab-loader"></span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ab-customer-delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e( 'Delete customers', 'bookly' ) ?></h4>
            </div>
            <div class="modal-body">
                <?php _e( 'You are about to delete customers which may have WordPress accounts associated to them. Do you want to delete those accounts too (if there are any)?', 'bookly' ) ?>
                <div class="checkbox">
                    <label>
                        <input id="ab-remember-my-choice" type="checkbox"> <?php _e( 'Remember my choice', 'bookly' ) ?>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default ab-no" data-dismiss="modal"><?php _e( 'No, delete just customers', 'bookly' ) ?></button>
                <button type="button" class="btn btn-info ab-yes"><?php _e( 'Yes', 'bookly' ) ?></button>
            </div>
        </div>
    </div>
</div>