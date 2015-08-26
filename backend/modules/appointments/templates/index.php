<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?php _e( 'Appointments', 'bookly' ) ?></h3>
    </div>
    <div class="panel-body">
        <div ng-app="appointments" ng-controller="appointmentsCtrl" class="form-horizontal ng-cloak">

            <form class="form-inline" action="<?php echo admin_url( 'admin-ajax.php' ) ?>?action=ab_export_to_csv" method="post" style="margin-bottom: 20px">
                <div id=reportrange class="pull-left ab-reportrange">
                    <i class="glyphicon glyphicon-calendar"></i>
                    <span data-date="<?php echo date( 'F j, Y', strtotime( 'first day of' ) ) ?> - <?php echo date( 'F j, Y', strtotime( 'last day of' ) ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( 'first day of' ) ) ?> - <?php echo date_i18n( get_option( 'date_format' ), strtotime( 'last day of' ) ) ?></span> <b style="margin-top: 8px;" class=caret></b>
                </div>
                <input type="hidden" name="date_start" ng-value="date_start" />
                <input type="hidden" name="date_end" ng-value="date_end" />
                <span class="help-inline"><?php _e( 'Delimiter', 'bookly' ) ?></span>
                <select name="delimiter" style="width: 125px;height: 30px" class="form-control">
                    <option value=","><?php _e( 'Comma (,)', 'bookly' ) ?></option>
                    <option value=";"><?php _e( 'Semicolon (;)', 'bookly' ) ?></option>
                </select>
                <button type="submit" class="btn btn-info"><?php _e( 'Export to CSV', 'bookly' ) ?></button>
                <button type="button" class="btn btn-info pull-right" ng-click="newAppointment()"><?php _e( 'New appointment', 'bookly' ) ?></button>
            </form>

            <div class="table-responsive">
                <table id="ab_appointments_list" class="table table-striped" cellspacing=0 cellpadding=0 border=0 style="clear: both;">
                    <thead>
                    <tr>
                        <th style="width: 14%;" ng-class="css_class.start_date"><a href="" ng-click="reload({sort:'start_date'})"><?php _e( 'Booking Time', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.staff_name"><a href="" ng-click="reload({sort:'staff_name'})"><?php _e( 'Staff Member', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.customer_name"><a href="" ng-click="reload({sort:'customer_name'})"><?php _e( 'Customer Name', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.service_title"><a href="" ng-click="reload({sort:'service_title'})"><?php _e( 'Service', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" ng-class="css_class.service_duration"><a href="" ng-click="reload({sort:'service_duration'})"><?php _e( 'Duration', 'bookly' ) ?></a></th>
                        <th style="width: 14%;" colspan="3" ng-class="css_class.price"><a href="" ng-click="reload({sort:'price'})"><?php _e( 'Price', 'bookly' ) ?></a></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr ng-repeat="appointment in dataSource.appointments">
                        <td>{{appointment.start_date_f}}</td>
                        <td>{{appointment.staff_name}}</td>
                        <td>{{appointment.customer_name}}</td>
                        <td>{{appointment.service_title}}</td>
                        <td>{{appointment.service_duration}}</td>
                        <td>{{appointment.price}}</td>
                        <td>
                            <button class="btn btn-info pull-right" ng-click="editAppointment(appointment)">
                                <?php _e( 'Edit', 'bookly' ) ?>
                            </button>
                        </td>
                        <td><input type="checkbox" data-appointment_id="{{appointment.id}}"></td>
                    </tr>
                    </tbody>
                </table>
                <div ng-hide="dataSource.appointments.length || loading" class="alert alert-info"><?php _e( 'No appointments for selected period.', 'bookly' ) ?></div>
            </div>

            <div class="btn-toolbar">
                <div class="col-xs-8">
                    <div class="btn-group" ng-hide="dataSource.pages.length == 1">
                        <button ng-click="reload({page:page.number})" class="btn btn-default" ng-repeat="page in dataSource.pages" ng-switch on="page.active">
                            <span ng-switch-when="true">{{page.number}}</span>
                            <a href="" ng-switch-default>{{page.number}}</a>
                        </button>
                    </div>
                </div>
                <div class="col-xs-4">
                    <a class="btn btn-info pull-right" ng-click="deleteAppointments()"><?php _e( 'Delete', 'bookly' ) ?></a>
                </div>
            </div>

            <div ng-show="loading" class="loading-indicator">
                <span class="ab-loader"></span>
            </div>
        </div>
        <div id="ab-appointment-form">
            <?php include AB_PATH . '/backend/modules/calendar/templates/_appointment_form.php' ?>
        </div>
    </div>
</div>
