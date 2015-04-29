<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="ab-title"><?php _e( 'Customers', 'ab' ); ?></div>
<div ng-app="customers" ng-controller="customersCtrl" style="min-width: 800px;" class="form-horizontal ng-cloak">

    <div class="control-group left">
        <label class=control-label><?php _e( 'Quick search customer', 'ab' ) ?></label>
        <div class=controls>
            <input type=text ng-model=filter />
        </div>
    </div>

    <div style="margin-left: 50%;">
        <div style="display: inline;" new-customer-dialog="createCustomer(customer)" backdrop="true" btn-class="btn btn-info"></div>
        <div style="display: inline;" btn-class="btn btn-info">
            <a href="#ab_import_customers_dialog" class="btn btn-info" data-toggle="modal"><?php _e( 'Import' , 'ab' ) ?></a>
            <?php include "_import.php"; ?>
        </div>
    </div>

    <table id="ab_customers_list" class="table table-striped" cellspacing=0 cellpadding=0 border=0 style="clear: both;">
        <thead>
        <tr>
            <th width=150 ng-class=css_class.name><a href="" ng-click=reload({sort:'name'})><?php _e( 'Name', 'ab' ); ?></a></th>
            <th width=100 ng-class=css_class.phone><a href="" ng-click=reload({sort:'phone'})><?php _e( 'Phone', 'ab' ); ?></a></th>
            <th width=150 ng-class=css_class.email><a href="" ng-click=reload({sort:'email'})><?php _e( 'Email', 'ab' ); ?></a></th>
            <th width=150 ng-class=css_class.notes><a href="" ng-click=reload({sort:'notes'})><?php _e( 'Notes', 'ab' ); ?></a></th>
            <th width=150 ng-class=css_class.last_appointment><a href="" ng-click=reload({sort:'last_appointment'})><?php _e( 'Last appointment', 'ab' ); ?></a></th>
            <th width=150 ng-class=css_class.total_appointments><a href="" ng-click=reload({sort:'total_appointments'})><?php _e( 'Total appointments', 'ab'); ?></a></th>
            <th width=150 ng-class=css_class.payments><a href="" ng-click=reload({sort:'payments'})><?php _e( 'Payments', 'ab'); ?></a></th>
        </tr>
        </thead>
        <tbody>
        <tr ng-repeat="customer in dataSource.customers">
            <td>
                <div ng-click="customer.edit_name = true" ng-hide=customer.edit_name class=displayed-value>{{customer.name}}</div>
                <span ng-show=customer.errors.name.required><?php _e( 'Required', 'ab' ) ?></span>
                <input class=ab-value ng-model=customer.name ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_name focus-me=customer.edit_name required />
            </td>
            <td class="ab-phone">
                <div ng-click="customer.edit_phone = true" ng-hide=customer.edit_phone class=displayed-value>{{customer.phone}}</div>
                <input class=ab-value ng-model=customer.phone ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_phone focus-me=customer.edit_phone />
            </td>
            <td>
                <div ng-click="customer.edit_email = true" ng-hide=customer.edit_email class=displayed-value>{{customer.email}}</div>
                <input class=ab-value ng-model=customer.email ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_email focus-me=customer.edit_email />
            </td>
            <td>
                <div ng-click="customer.edit_notes = true" ng-hide=customer.edit_notes class=displayed-value ng-bind-html="customer.notes | nl2br"></div>
                <textarea class=ab-value ng-model="customer.notes" ui-event="{blur:'saveCustomer(customer)'}" ng-show=customer.edit_notes focus-me=customer.edit_notes></textarea>
            </td>
            <td>
                <div ng-model=customer.last_appointment class=displayed-value>{{customer.last_appointment}}</div>
            </td>
            <td>
                <div ng-model=customer.total_appointments class=displayed-value>{{customer.total_appointments}}</div>
            </td>
            <td>
                <div ng-model=customer.payments class=displayed-value>{{customer.payments}}</div>
            </td>
            <td><a href="" ng-click="deleteCustomer(customer)" role="button" class="btn btn-danger" id="{{customer.id}}" name="customer_delete"><?php _e( 'Delete', 'ab' ) ?></a></td>
        </tr>
        <tr ng-hide="dataSource.customers.length || loading"><td colspan=6><?php _e( 'No customers', 'ab' ); ?></td></tr>
        </tbody>
    </table>
    <div class="btn-toolbar" ng-hide="dataSource.pages.length == 1">
        <div class="btn-group">
            <button ng-click=reload({page:page.number}) class="btn" ng-repeat="page in dataSource.pages" ng-switch on=page.active>
                <span ng-switch-when=true>{{page.number}}</span>
                <a href="" ng-switch-default>{{page.number}}</a>
            </button>
        </div>
    </div>
    <div ng-show="loading" class="loading-indicator">
        <img src="<?php echo plugins_url( 'backend/resources/images/ajax_loader_32x32.gif', AB_PATH . '/main.php' ) ?>" alt="" />
    </div>
</div>