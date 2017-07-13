;(function() {

    var module = angular.module('appointmentDialog', ['ui.date', 'customerDialog', 'paymentDetailsDialog']);

    /**
     * DataSource service.
     */
    module.factory('dataSource', function($q, $rootScope, $filter) {
        var ds = {
            loaded : false,
            data : {
                staff         : [],
                customers     : [],
                start_time    : [],
                end_time      : [],
                time_interval : 900,
                status        : {
                    items: [],
                    default: null
                }
            },
            form : {
                screen     : null,
                id         : null,
                staff      : null,
                service    : null,
                date       : null,
                repeat     : {
                    enabled  : null,
                    repeat   : null,
                    daily    : { every : null },
                    weekly   : { on : null },
                    biweekly : { on : null },
                    monthly  : { on : null, day : null, weekday : null },
                    until    : null
                },
                schedule   : {
                    items : [],
                    edit  : null,
                    page  : null,
                    another_time : []
                },
                start_time : null,
                end_time   : null,
                customers  : [],
                notification : null
            },
            loadData : function() {
                var deferred = $q.defer();
                if (!ds.loaded) {
                    jQuery.get(
                        ajaxurl,
                        { action : 'bookly_get_data_for_appointment_form' },
                        function(data) {
                            ds.loaded = true;
                            ds.data = data;
                            // Add empty element to beginning of array for single-select customer form
                            ds.data.customers.unshift({name: ''});

                            if (data.staff.length) {
                                ds.form.staff = data.staff[0];
                            }
                            ds.form.start_time = data.start_time[0];
                            ds.form.end_time   = data.end_time[1];
                            deferred.resolve();
                        },
                        'json'
                    );
                } else {
                    deferred.resolve();
                }

                return deferred.promise;
            },
            findStaff : function(id) {
                var result = null;
                jQuery.each(ds.data.staff, function(key, item) {
                    if (item.id == id) {
                        result = item;
                        return false;
                    }
                });
                return result;
            },
            findService : function(staff_id, id) {
                var result = null,
                    staff  = ds.findStaff(staff_id);

                if (staff !== null) {
                    jQuery.each(staff.services, function(key, item) {
                        if (item.id == id) {
                            result = item;
                            return false;
                        }
                    });
                }
                return result;
            },
            findTime : function(source, date) {
                var result = null,
                    value_to_find = $filter('date')(date, 'HH:mm'),
                    time = source == 'start' ? ds.data.start_time : ds.data.end_time;

                jQuery.each(time, function(key, item) {
                    if (item.value >= value_to_find) {
                        result = item;
                        return false;
                    }
                });
                return result;
            },
            findCustomer : function(id) {
                var result = null;
                jQuery.each(ds.data.customers, function(key, item) {
                    if (item.id == id) {
                        result = item;
                        return false;
                    }
                });
                return result;
            },
            resetCustomers : function() {
                ds.data.customers.forEach(function(customer) {
                    customer.custom_fields     = [];
                    customer.extras            = [];
                    customer.status            = ds.data.status.default;
                    customer.number_of_persons = 1;
                    customer.compound_token    = null;
                    customer.location_id       = null;
                    customer.payment_id        = null;
                    customer.payment_type      = null;
                    customer.payment_title     = null;
                });
            },
            getDataForEndTime : function() {
                var result = [];
                if (ds.form.start_time) {
                    var start_time = ds.form.start_time.value.split(':'),
                        end = (24 + parseInt(start_time[0])) + ':' + start_time[1];
                    jQuery.each(ds.data.end_time, function(key, item) {
                        if (item.value > end) {
                            return false;
                        }
                        if (item.value > ds.form.start_time.value) {
                            result.push(item);
                        }
                    });
                }
                return result;
            },
            setEndTimeBasedOnService : function() {
                var i = jQuery.inArray(ds.form.start_time, ds.data.start_time),
                    d = ds.form.service ? ds.form.service.duration : ds.data.time_interval;
                if (ds.form.service && ds.form.service.duration == 86400) {
                    ds.form.start_time =  ds.data.start_time[0];
                    ds.form.end_time = ds.data.end_time[ 86400 / ds.data.time_interval ];
                } else {
                    if (i !== -1) {
                        for (; i < ds.data.end_time.length; ++i) {
                            d -= ds.data.time_interval;
                            if (d < 0) {
                                break;
                            }
                        }
                        ds.form.end_time = ds.data.end_time[i];
                    }
                }
            },
            getStartAndEndDates : function() {
                var start_date = new Date(ds.form.date.getTime()),
                    start_time = ds.form.start_time.value.split(':'),
                    end_date   = new Date(ds.form.date.getTime()),
                    end_time   = ds.form.end_time.value.split(':');
                start_date.setHours(start_time[0]);
                start_date.setMinutes(start_time[1]);
                end_date.setHours(end_time[0]);
                end_date.setMinutes(end_time[1]);

                return {
                    start_date : $filter('date')(start_date, 'yyyy-MM-dd HH:mm:00'),
                    end_date   : $filter('date')(end_date, 'yyyy-MM-dd HH:mm:00')
                };
            },
            getTotalNumberOfPersons : function () {
                var result = 0;
                ds.form.customers.forEach(function (item) {
                    result += parseInt(item.number_of_persons);
                });

                return result;
            },
            getTotalNumberOfNotCancelledPersons: function () {
                var result = 0;
                ds.form.customers.forEach(function (item) {
                    if (item.status != 'cancelled') {
                        result += parseInt(item.number_of_persons);
                    }
                });

                return result;
            },
            getTotalNumberOfCancelledPersons: function () {
                var result = 0;
                ds.form.customers.forEach(function (item) {
                    if (item.status == 'cancelled') {
                        result += parseInt(item.number_of_persons);
                    }
                });

                return result;
            }
        };

        return ds;
    });

    /**
     * Controller for 'create/edit appointment' dialog form.
     */
    module.controller('appointmentDialogCtrl', function($scope, $element, dataSource, $filter) {
        // Set up initial data.
        $scope.$calendar = null;
        // Set up data source.
        $scope.dataSource = dataSource;
        $scope.form = dataSource.form;  // shortcut
        // Error messages.
        $scope.errors = {};
        // Callback to be called after editing appointment.
        var callback = null;

        /**
         * Prepare the form for new event.
         *
         * @param int staff_id
         * @param moment start_date
         * @param function _callback
         */
        $scope.configureNewForm = function(staff_id, start_date, _callback) {
            var weekday = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][start_date.format('d')];
            jQuery.extend($scope.form, {
                screen     : 'main',
                id         : null,
                staff      : dataSource.findStaff(staff_id),
                service    : null,
                date       : start_date.clone().local().toDate(),
                start_time : dataSource.findTime('start', start_date.format('HH:mm')),
                end_time   : null,
                repeat     : {
                    enabled  : 0,
                    repeat   : 'daily',
                    daily    : { every: 1 },
                    weekly   : { on : [weekday] },
                    biweekly : { on : [weekday] },
                    monthly  : { on : 'day', day : start_date.format('D'), weekday : weekday },
                    until    : start_date.clone().add(1, 'month').format('YYYY-MM-DD')
                },
                schedule   : {
                    items : [],
                    edit  : 0,
                    page  : 0,
                    another_time : []
                },
                customers  : [],
                notification  : 'no',
                internal_note : null
            });
            $scope.errors = {};
            dataSource.setEndTimeBasedOnService();
            callback = _callback;

            $scope.reInitChosen();
            $scope.prepareExtras();
            $scope.prepareCustomFields();
            $scope.dataSource.resetCustomers();
        };

        /**
         * Prepare the form for editing an event.
         */
        $scope.configureEditForm = function(appointment_id, _callback) {
            $scope.loading = true;
            jQuery.post(
                ajaxurl,
                {action: 'bookly_get_data_for_appointment', id: appointment_id},
                function(response) {
                    $scope.$apply(function($scope) {
                        if (response.success) {
                            var start_date = moment(response.data.start_date),
                                end_date   = moment(response.data.end_date);
                            jQuery.extend($scope.form, {
                                screen     : 'main',
                                id         : appointment_id,
                                staff      : $scope.dataSource.findStaff(response.data.staff_id),
                                service    : $scope.dataSource.findService(response.data.staff_id, response.data.service_id),
                                date       : start_date.clone().local().toDate(),
                                start_time : $scope.dataSource.findTime('start', start_date.format('HH:mm')),
                                end_time   : start_date.format('YYYY-MM-DD') == end_date.format('YYYY-MM-DD')
                                    ? $scope.dataSource.findTime('end', end_date.format('HH:mm'))
                                    : $scope.dataSource.findTime('end', (24 + end_date.hour()) + end_date.format(':mm')),
                                repeat     : {
                                    enabled  : 0,
                                    repeat   : 'daily',
                                    daily    : { every: 1 },
                                    weekly   : { on : [] },
                                    biweekly : { on : [] },
                                    monthly  : { on : 'day', day : '1', weekday : 'mon' },
                                    until    : start_date.clone().add(1, 'month').format('YYYY-MM-DD')
                                },
                                schedule   : {
                                    items : [],
                                    edit  : 0,
                                    page  : 0,
                                    another_time : []
                                },
                                customers  : [],
                                notification : 'no',
                                internal_note : response.data.internal_note
                            });

                            $scope.reInitChosen();
                            $scope.prepareExtras();
                            $scope.prepareCustomFields();
                            $scope.dataSource.resetCustomers();

                            var customers_ids = [];
                            response.data.customers.forEach(function (item, i, arr) {
                                var customer = $scope.dataSource.findCustomer(item.id),
                                    clone = {};
                                if (customers_ids.indexOf(item.id) === -1) {
                                    customers_ids.push(item.id);
                                    clone = customer;
                                } else {
                                    // For Error: ngRepeat:dupes & chosen directive
                                    angular.copy(customer, clone);
                                }
                                clone.ca_id             = item.ca_id;
                                clone.extras            = item.extras;
                                clone.status            = item.status;
                                clone.custom_fields     = item.custom_fields;
                                clone.number_of_persons = item.number_of_persons;
                                clone.location_id       = item.location_id;
                                clone.payment_id        = item.payment_id;
                                clone.payment_type      = item.payment_type;
                                clone.payment_title     = item.payment_title;
                                clone.compound_token    = item.compound_token;
                                clone.compound_service  = item.compound_service;
                                $scope.form.customers.push(clone);
                            });
                        }
                        $scope.loading = false;
                    });
                },
                'json'
            );
            $scope.errors = {};
            callback = _callback;
        };

        var checkTimeInterval = function() {
            var dates = $scope.dataSource.getStartAndEndDates();
            jQuery.post(
                ajaxurl,
                {
                    action         : 'bookly_check_appointment_date_selection',
                    start_date     : dates.start_date,
                    end_date       : dates.end_date,
                    appointment_id : $scope.form.id,
                    staff_id       : $scope.form.staff ? $scope.form.staff.id : null,
                    service_id     : $scope.form.service ? $scope.form.service.id : null
                },
                function (response) {
                    $scope.$apply(function ($scope) {
                        $scope.errors = response;
                    });
                },
                'json'
            );
        };

        $scope.onServiceChange = function() {
            $scope.dataSource.setEndTimeBasedOnService();
            $scope.reInitChosen();
            $scope.prepareExtras();
            $scope.prepareCustomFields();
            checkTimeInterval();
        };

        $scope.onStaffChange = function() {
            $scope.form.service = null;
        };

        $scope.onStartTimeChange = function() {
            $scope.dataSource.setEndTimeBasedOnService();
            checkTimeInterval();
        };

        $scope.onEndTimeChange = function() {
            checkTimeInterval();
        };

        $scope.processForm = function() {
            $scope.loading = true;

            var dates     = $scope.dataSource.getStartAndEndDates(),
                schedule  = [],
                customers = []
            ;

            angular.forEach($scope.form.schedule.items, function (item) {
                if (!item.deleted) {
                    schedule.push(item.slots);
                }
            });

            $scope.form.customers.forEach(function (item, i, arr) {
                var customer_extras = {};
                if ($scope.form.service) {
                    jQuery('#bookly-extras .service_' + $scope.form.service.id + ' input.extras-count').each(function () {
                        var extra_id = jQuery(this).data('id');
                        if (item.extras[extra_id] !== undefined) {
                            customer_extras[extra_id] = item.extras[extra_id];
                        }
                    });
                }
                customers.push({
                    id                : item.id,
                    ca_id             : item.ca_id,
                    custom_fields     : item.custom_fields,
                    extras            : customer_extras,
                    location_id       : item.location_id,
                    number_of_persons : item.number_of_persons,
                    status            : item.status
                });
            });

            jQuery.post(
                ajaxurl,
                {
                    action        : 'bookly_save_appointment_form',
                    id            : $scope.form.id,
                    staff_id      : $scope.form.staff ? $scope.form.staff.id : null,
                    service_id    : $scope.form.service ? $scope.form.service.id : null,
                    start_date    : dates.start_date,
                    end_date      : dates.end_date,
                    repeat        : JSON.stringify($scope.form.repeat),
                    schedule      : schedule,
                    customers     : JSON.stringify(customers),
                    notification  : $scope.form.notification,
                    internal_note : $scope.form.internal_note
                },
                function (response) {
                    $scope.$apply(function($scope) {
                        if (response.success) {
                            if (callback) {
                                // Call callback.
                                callback(response.data);
                            }
                            // Close the dialog.
                            $element.children().modal('hide');
                        } else {
                            $scope.errors = response.errors;
                        }
                        $scope.loading = false;
                    });
                },
                'json'
            );
        };

        // On 'Cancel' button click.
        $scope.closeDialog = function () {
            // Close the dialog.
            $element.children().modal('hide');
        };

        $scope.reInitChosen = function () {
            jQuery('#bookly-chosen')
                .chosen('destroy')
                .chosen({
                    search_contains     : true,
                    width               : '100%',
                    max_selected_options: dataSource.form.service ? dataSource.form.service.capacity + dataSource.getTotalNumberOfCancelledPersons() : 0
                });
        };

        $scope.statusToString = function (status) {
            return dataSource.data.status.items[status];
        };

        /**************************************************************************************************************
         * New customer                                                                                               *
         **************************************************************************************************************/

        /**
         * Create new customer.
         * @param customer
         */
        $scope.createCustomer = function(customer) {
            // Add new customer to the list.
            var new_customer = {
                id                : customer.id.toString(),
                name              : customer.name,
                custom_fields     : customer.custom_fields,
                extras            : customer.extras,
                status            : customer.status,
                number_of_persons : 1,
                compound_token    : null,
                location_id       : null,
                payment_id        : null,
                payment_type      : null,
                payment_title     : null
            };

            if (customer.email || customer.phone){
                new_customer.name += ' (' + [customer.email, customer.phone].filter(Boolean).join(', ') + ')';
            }

            dataSource.data.customers.push(new_customer);

            // Make it selected.
            if (!dataSource.form.service || dataSource.form.customers.length < dataSource.form.service.capacity) {
                dataSource.form.customers.push(new_customer);
            }

            setTimeout(function() { jQuery('#bookly-chosen').trigger('chosen:updated'); }, 0);
        };

        $scope.removeCustomer = function(customer) {
            $scope.form.customers.splice($scope.form.customers.indexOf(customer), 1);
        };

        /**************************************************************************************************************
         * Customer Details                                                                                           *
         **************************************************************************************************************/

        $scope.editCustomerDetails = function(customer) {
            var $dialog = jQuery('#bookly-customer-details-dialog');
            $dialog.find('input.ab-custom-field:text, textarea.ab-custom-field, select.ab-custom-field').val('');
            $dialog.find('input.ab-custom-field:checkbox, input.ab-custom-field:radio').prop('checked', false);
            $dialog.find('#bookly-extras :checkbox').prop('checked', false);

            customer.custom_fields.forEach(function (field) {
                var $custom_field = $dialog.find('#ab--custom-fields > *[data-id="' + field.id + '"]');
                switch ($custom_field.data('type')) {
                    case 'checkboxes':
                        field.value.forEach(function (value) {
                            $custom_field.find('.ab-custom-field').filter(function () {
                                return this.value == value;
                            }).prop('checked', true);
                        });
                        break;
                    case 'radio-buttons':
                        $custom_field.find('.ab-custom-field').filter(function () {
                            return this.value == field.value;
                        }).prop('checked', true);
                        break;
                    default:
                        $custom_field.find('.ab-custom-field').val(field.value);
                        break;
                }
            });

            $dialog.find('#bookly-extras .extras-count').val(0);
            angular.forEach(customer.extras, function (extra_count, extra_id) {
                $dialog.find('#bookly-extras .extras-count[data-id="' + extra_id + '"]').val(extra_count);
            });

            // Prepare select for number of persons.
            var $number_of_persons = $dialog.find('#ab-edit-number-of-persons');

            var max = $scope.form.service
                ? parseInt($scope.form.service.capacity) - $scope.dataSource.getTotalNumberOfNotCancelledPersons() + ( customer.status != 'cancelled' ? parseInt(customer.number_of_persons) : 0 )
                : 1;
            $number_of_persons.empty();
            for (var i = 1; i <= max; ++i) {
                $number_of_persons.append('<option value="' + i + '">' + i + '</option>');
            }
            if (customer.number_of_persons > max) {
                $number_of_persons.append('<option value="' + customer.number_of_persons + '">' + customer.number_of_persons + '</option>');
            }
            $number_of_persons.val(customer.number_of_persons);
            $dialog.find('#ab-appointment-status').val(customer.status);
            $dialog.find('#ab-appointment-location').val(customer.location_id);
            $dialog.find('#ab-deposit-due').val(customer.due);
            $scope.edit_customer = customer;

            $dialog.modal({show: true, backdrop: false})
                .on('hidden.bs.modal', function () {
                    jQuery('body').addClass('modal-open');
                });
        };

        $scope.prepareExtras = function () {
            if ($scope.form.service) {
                jQuery('#bookly-extras > *').hide();
                var $service_extras = jQuery('#bookly-extras .service_' + $scope.form.service.id);
                if ($service_extras.length) {
                    $service_extras.show();
                    jQuery('#bookly-extras').show();
                } else {
                    jQuery('#bookly-extras').hide();
                }
            } else {
                jQuery('#bookly-extras').hide();
            }
        };

        // Hide or unhide custom fields for current service
        $scope.prepareCustomFields = function () {
            if (BooklyL10nAppDialog.cf_per_service == 1) {
                var show = false;
                jQuery('#ab--custom-fields div[data-services]').each(function() {
                    var $this = jQuery(this);
                    if (dataSource.form.service !== null) {
                        var services = $this.data('services');
                        if (services && jQuery.inArray(dataSource.form.service.id, services) > -1) {
                            $this.show();
                            show = true;
                        } else {
                            $this.hide();
                        }
                    } else {
                        $this.hide();
                    }
                });
                if (show) {
                    jQuery('#ab--custom-fields').show();
                } else {
                    jQuery('#ab--custom-fields').hide();
                }
            }
        };

        $scope.saveCustomFields = function() {
            var result  = [],
                extras  = {},
                $fields = jQuery('#ab--custom-fields > *'),
                $number_of_persons = jQuery('#bookly-customer-details-dialog #ab-edit-number-of-persons')
            ;

            $fields.each(function () {
                var $this = jQuery(this),
                    value;
                if ($this.is(':visible')) {
                    switch ($this.data('type')) {
                        case 'checkboxes':
                            value = [];
                            $this.find('.ab-custom-field:checked').each(function () {
                                value.push(this.value);
                            });
                            break;
                        case 'radio-buttons':
                            value = $this.find('.ab-custom-field:checked').val();
                            break;
                        default:
                            value = $this.find('.ab-custom-field').val();
                            break;
                    }
                    result.push({id: $this.data('id'), value: value});
                }
            });

            if ($scope.form.service) {
                jQuery('#bookly-extras .service_' + $scope.form.service.id + ' input.extras-count').each(function () {
                    if (this.value > 0) {
                        extras[jQuery(this).data('id')] = this.value;
                    }
                });
            }

            $scope.edit_customer.custom_fields = result;
            $scope.edit_customer.number_of_persons = $number_of_persons.val();
            $scope.edit_customer.location_id = jQuery('#bookly-customer-details-dialog #ab-appointment-location').val();
            $scope.edit_customer.extras = extras;
            $scope.edit_customer.status = jQuery('#bookly-customer-details-dialog #ab-appointment-status').val();

            jQuery('#bookly-customer-details-dialog').modal('hide');
        };

        /**************************************************************************************************************
         * Payment Details                                                                                            *
         **************************************************************************************************************/

        $scope.completePayment = function(payment_id, payment_title) {
            jQuery.each($scope.dataSource.data.customers, function(key, item) {
                if (item.payment_id == payment_id) {
                    item.payment_type  = 'full';
                    item.payment_title = payment_title;
                }
            });
        };

        /**************************************************************************************************************
         * Schedule of Recurring Appointments                                                                         *
         **************************************************************************************************************/

        $scope.schSchedule = function ($event) {
            var extras = [];
            $scope.form.customers.forEach(function (item, i, arr) {
                extras.push(item.extras);
            });

            if (
                ($scope.form.repeat.repeat == 'weekly' || $scope.form.repeat.repeat == 'biweekly') &&
                $scope.form.repeat[$scope.form.repeat.repeat].on.length == 0
            ) {
                $scope.errors.repeat_weekdays_empty = true;
            } else {
                delete $scope.errors.repeat_weekdays_empty;
                var ladda = Ladda.create($event.currentTarget);
                ladda.start();
                var dates = $scope.dataSource.getStartAndEndDates();
                jQuery.post(
                    ajaxurl,
                    {
                        action     : 'bookly_recurring_appointments_get_schedule',
                        staff_id   : $scope.form.staff.id,
                        service_id : $scope.form.service.id,
                        datetime   : dates.start_date,
                        until      : $scope.form.repeat.until,
                        repeat     : $scope.form.repeat.repeat,
                        params     : $scope.form.repeat[$scope.form.repeat.repeat],
                        extras     : extras
                    },
                    function (response) {
                        $scope.$apply(function($scope) {
                            $scope.form.schedule.items = response.data;
                            $scope.form.schedule.page  = 0;
                            $scope.form.schedule.another_time = [];
                            angular.forEach($scope.form.schedule.items, function (item) {
                                if (item.another_time) {
                                    var page = parseInt( ( item.index - 1 ) / 10 ) + 1;
                                    if ($scope.form.schedule.another_time.indexOf(page) < 0) {
                                        $scope.form.schedule.another_time.push(page);
                                    }
                                }
                            });
                            $scope.form.screen = 'schedule';
                            ladda.stop();
                        });
                    },
                    'json'
                );
            }
        };
        $scope.schFormatDate = function(date) {
            var m = moment(date),
                weekday = m.format('d'),
                month   = m.format('M'),
                day     = m.format('DD');

            return BooklyL10nAppDialog.calendar.shortDays[weekday] + ', ' + BooklyL10nAppDialog.calendar.shortMonths[month-1] + ' ' + day;
        };
        $scope.schFormatTime = function(slots, options) {
            for (var i = 0; i < options.length; ++ i) {
                if (slots == options[i].value) {
                    return options[i].title;
                }
            }
        };
        $scope.schFirstPage = function() {
            return $scope.form.schedule.page == 0;
        };
        $scope.schLastPage = function() {
            var lastPageNum = Math.ceil($scope.form.schedule.items.length / 10 - 1);
            return $scope.form.schedule.page == lastPageNum;
        };
        $scope.schNumberOfPages = function() {
            return Math.ceil($scope.form.schedule.items.length / 10);
        };
        $scope.schStartingItem = function() {
            return $scope.form.schedule.page * 10;
        };
        $scope.schPageBack = function() {
            $scope.form.schedule.page = $scope.form.schedule.page - 1;
        };
        $scope.schPageForward = function() {
            $scope.form.schedule.page = $scope.form.schedule.page + 1;
        };
        $scope.schOnWeekdayClick = function (weekday) {
            var idx = $scope.form.repeat.weekly.on.indexOf(weekday);

            // is currently selected
            if (idx > -1) {
                $scope.form.repeat.weekly.on.splice(idx, 1);
            }
            // is newly selected
            else {
                $scope.form.repeat.weekly.on.push(weekday);
            }
            // copy weekly to biweekly
            $scope.form.repeat.biweekly.on = $scope.form.repeat.weekly.on.slice();
        };
        $scope.schOnDateChange = function(item) {
            var extras = [];
            $scope.form.customers.forEach(function (item, i, arr) {
                extras.push(item.extras);
            });

            var exclude = [];
            angular.forEach($scope.form.schedule.items, function (_item) {
                if (item.slots != _item.slots && !_item.deleted) {
                    exclude.push(_item.slots);
                }
            });
            jQuery.post(
                ajaxurl,
                {
                    action       : 'bookly_recurring_appointments_get_schedule',
                    staff_id     : $scope.form.staff.id,
                    service_id   : $scope.form.service.id,
                    datetime     : item.date + ' 00:00',
                    until        : item.date,
                    repeat       : 'daily',
                    params       : {every: 1},
                    with_options : 1,
                    exclude      : exclude,
                    extras       : extras
                },
                function (response) {
                    $scope.$apply(function($scope) {
                        if (response.data.length) {
                            item.options = response.data[0].options;
                            var found = false;
                            jQuery.each(item.options, function (key, option) {
                                if ( option.value == item.slots ) {
                                    found = true;
                                    return false;
                                }
                            });
                            if (!found) {
                                item.slots = item.options[0].value;
                            }
                        } else {
                            item.options = [];
                        }
                    });
                },
                'json'
            );
        };
        $scope.schIsScheduleEmpty = function () {
            return $scope.form.schedule.items.every(function(item) {
                return item.deleted;
            });
        };
        $scope.schDateOptions = {
            dateFormat      : 'D, M dd, yy',
            dayNamesMin     : BooklyL10nAppDialog.calendar.shortDays,
            dayNamesShort   : BooklyL10nAppDialog.calendar.shortDays,
            monthNames      : BooklyL10nAppDialog.calendar.longMonths,
            monthNamesShort : BooklyL10nAppDialog.calendar.shortMonths,
            firstDay        : BooklyL10nAppDialog.startOfWeek
        };

        /**
         * Datepicker options.
         */
        $scope.dateOptions = {
            dateFormat      : BooklyL10nAppDialog.dpDateFormat,
            dayNamesMin     : BooklyL10nAppDialog.calendar.shortDays,
            monthNames      : BooklyL10nAppDialog.calendar.longMonths,
            monthNamesShort : BooklyL10nAppDialog.calendar.shortMonths,
            firstDay        : BooklyL10nAppDialog.startOfWeek
        };
    });

    /**
     * Directive for slide up/down.
     */
    module.directive('mySlideUp', function() {
        return function(scope, element, attrs) {
            element.hide();
            // watch the expression, and update the UI on change.
            scope.$watch(attrs.mySlideUp, function(value) {
                if (value) {
                    element.delay(0).slideDown();
                } else {
                    element.slideUp();
                }
            });
        };
    });

    /**
     * Directive for chosen.
     */
    module.directive('chosen',function($timeout) {
        var linker = function(scope,element,attrs) {
            scope.$watch(attrs['chosen'], function() {
                element.trigger('chosen:updated');
            });

            scope.$watchCollection(attrs['ngModel'], function() {
                $timeout(function() {
                    element.trigger('chosen:updated');
                });
            });

            scope.reInitChosen();
        };

        return {
            restrict:'A',
            link: linker
        };
    });

    /**
     * Directive for Popover jQuery plugin.
     */
    module.directive('popover', function() {
        return function(scope, element, attrs) {
            element.popover({
                trigger : 'hover',
                content : function() { return this.getAttribute('popover'); },
                html    : true,
                placement: 'top',
                template: '<div class="popover bookly-font-xs" style="width: 220px" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
            });
        };
    });

    /**
     * Filters for pagination in Schedule.
     */
    module.filter('startFrom', function() {
        return function(input, start){
            start = +start;
            return input.slice(start);
        }
    });
    module.filter('range', function() {
        return function(input, total) {
            total = parseInt(total);

            for (var i = 1; i <= total; ++ i) {
                input.push(i);
            }

            return input;
        };
    });
})();

/**
 * @param int appointment_id
 * @param int staff_id
 * @param moment start_date
 * @param function callback
 */
var showAppointmentDialog = function (appointment_id, staff_id, start_date, callback) {
    var $dialog = jQuery('#bookly-appointment-dialog');
    var $scope = angular.element($dialog[0]).scope();
    $scope.$apply(function ($scope) {
        $scope.loading = true;
        $dialog
            .find('.modal-title')
            .text(appointment_id ? BooklyL10nAppDialog.title.edit_appointment : BooklyL10nAppDialog.title.new_appointment);
        // Populate data source.
        $scope.dataSource.loadData().then(function() {
            $scope.loading = false;
            if (appointment_id) {
                $scope.configureEditForm(appointment_id, callback);
            } else {
                $scope.configureNewForm(staff_id, start_date, callback);
            }
        });
    });

    // hide customer details dialog, if it remained opened.
    if (jQuery('#bookly-customer-details-dialog').hasClass('in')) {
        jQuery('#bookly-customer-details-dialog').modal('hide');
    }

    // hide new customer dialog, if it remained opened.
    if (jQuery('#bookly-customer-dialog').hasClass('in')) {
        jQuery('#bookly-customer-dialog').modal('hide');
    }

    $dialog.modal('show');
};