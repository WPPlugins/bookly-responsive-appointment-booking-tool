;(function() {

    var module = angular.module('appointments', ['ui.utils', 'ui.date', 'ngSanitize']);

    module.factory('dataSource', function($q, $rootScope) {
        var ds = {
            appointments : [],
            total     : 0,
            pages     : [],
            loadData  : function(params) {
                var deferred = $q.defer();
                jQuery.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : jQuery.extend({ action : 'ab_get_appointments' }, params),
                    dataType : 'json',
                    success : function(response) {
                        if (response.success) {
                            ds.appointments = response.data.appointments;
                            ds.total   = response.data.total;
                            ds.pages   = [];
                            for (var i = 0; i < response.data.pages; ++ i) {
                                ds.pages.push({
                                    number : i + 1,
                                    active : response.data.active_page == i + 1
                                });
                            }
                        }
                        $rootScope.$apply(deferred.resolve);
                    },
                    error : function() {
                        ds.appointments = [];
                        ds.total     = 0;
                        $rootScope.$apply(deferred.resolve);
                    }
                });

                return deferred.promise;
            }
        };

        return ds;
    });

    module.controller('appointmentsCtrl', function($scope, dataSource) {
        // Set up initial data.
        var params = {
            page       : 1,
            sort       : 'start_date',
            order      : 'desc',
            date_start : '',
            date_end   : ''
        };
        $scope.loading   = true;
        $scope.css_class = {
            staff_name      : '',
            customer_name   : '',
            service_title   : '',
            start_date      : 'desc',
            service_duration: '',
            price           : ''
        };

        var format = 'YYYY-MM-DD';
        $scope.date_start = moment().startOf('month').format(format);
        $scope.date_end   = moment().endOf('month').format(format);

        // Set up data source (data will be loaded in reload function).
        $scope.dataSource = dataSource;

        $scope.reload = function( opt ) {
            $scope.loading = true;
            if (opt !== undefined) {
                if (opt.sort !== undefined) {
                    if (params.sort === opt.sort) {
                        // Toggle order when sorting by the same field.
                        params.order = params.order === 'asc' ? 'desc' : 'asc';
                    } else {
                        params.order = 'asc';
                    }
                    $scope.css_class = {
                        staff_name      : '',
                        customer_name   : '',
                        service_title   : '',
                        start_date      : '',
                        service_duration: '',
                        price           : ''
                    };
                    $scope.css_class[opt.sort] = params.order;
                }
                jQuery.extend(params, opt);
            }
            params.date_start = $scope.date_start;
            params.date_end   = $scope.date_end;
            dataSource.loadData(params).then(function() {
                $scope.loading = false;
            });
        };

        $scope.reload();

        /**
         * New appointment.
         *
         * @param appointment
         */
        $scope.newAppointment = function() {
            showAppointmentDialog(
                null,
                null,
                moment(),
                null,
                function(event) {
                    $scope.$apply(function($scope) {
                        $scope.reload();
                    });
                }
            )
        };

        /**
         * Edit appointment.
         *
         * @param appointment
         */
        $scope.editAppointment = function(appointment) {
            showAppointmentDialog(
                appointment.appointment_id,
                appointment.staff_id,
                moment(appointment.start_date),
                moment(appointment.end_date),
                function(event) {
                    $scope.$apply(function($scope) {
                        $scope.reload();
                    });
                }
            )
        };

        /**
         * Delete customer appointments.
         */
        $scope.deleteAppointments = function() {
            var ids = [];
            jQuery('table input[type=checkbox]:checked').each(function() {
                ids.push(jQuery(this).data('appointment_id'));
            });
            if( ids.length ) {
                $scope.loading = true;
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ab_delete_customer_appointment',
                        ids: ids
                    },
                    dataType: 'json',
                    success: function (response) {
                        $scope.$apply(function ($scope) {
                            $scope.reload();
                        });
                    }
                });
            } else{
                alert(BooklyL10n.please_select_at_least_one_row);
            }
        };

        // Init date range picker.
        var picker_ranges = {};
        picker_ranges[BooklyL10n.today]      = [moment(), moment()];
        picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
        picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
        picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
        picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
        picker_ranges[BooklyL10n.next_month] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];

        jQuery('#reportrange').daterangepicker(
            {
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                ranges: picker_ranges,
                locale: {
                    applyLabel : BooklyL10n.apply,
                    cancelLabel: BooklyL10n.cancel,
                    fromLabel  : BooklyL10n.from,
                    toLabel    : BooklyL10n.to,
                    customRangeLabel: BooklyL10n.custom_range,
                    daysOfWeek : BooklyL10n.shortDays,
                    monthNames : BooklyL10n.longMonths,
                    firstDay   : parseInt(BooklyL10n.startOfWeek),
                    format     : BooklyL10n.mjsDateFormat
                }
            },
            function(start, end) {
                jQuery('#reportrange span').html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
                $scope.$apply(function($scope){
                    $scope.date_start = start.format(format);
                    $scope.date_end   = end.format(format);
                    $scope.reload();
                });
            }
        );
    });

    // Bootstrap 'appointmentForm' application.
    angular.bootstrap(document.getElementById('ab-appointment-form'), ['appointmentForm']);
})();