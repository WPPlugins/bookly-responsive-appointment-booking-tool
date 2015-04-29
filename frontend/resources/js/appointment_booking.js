(function($) {
    $.fn.appointmentBooking = function(options) {
        var $container = this,
            Appointment = {
                is_available : false,
                returned_to_first_step : false,
                is_finished: options.last_step,
                is_cancelled: options.cancelled,
                save_response : function() {
                    var payment_type = $('#ab-booking-form-' + options.form_id).find('input[type="radio"]:checked').val();
                    var d = new Date();
                    return $.ajax({
                        type : 'POST',
                        url  : options.ajaxurl,
                        xhrFields : { withCredentials: true },
                        data : {
                            action : 'ab_save_appointment',
                            form_id : options.form_id,
                            payment_type : payment_type,
                            client_time_zone_offset : d.getTimezoneOffset()
                        }
                    });
                }, // save_response
                save_action : function() {
                    Appointment.save_response().done(function(response) {
                            var $response = $.parseJSON(response);
                            Appointment.is_available = !!$response.state;
                            // remove cookie if appointment had just been booked
                            if (Appointment.is_available && $.cookie('first_step')) {
                                $.removeCookie('first_step');
                            }

                            fifthStep();
                    }); // done
                }, // save_action,
                skip_first_step : (
                    options.ab_attributes.ch
                    && options.ab_attributes.cid
                    && options.ab_attributes.hs
                    && options.ab_attributes.sid
                    && options.ab_attributes.he
                    && options.ab_attributes.ha
                ),
                skip_available  : options.ab_attributes.ha,
                skip_service    : options.ab_attributes.ch
                    && options.ab_attributes.cid
                    && options.ab_attributes.hs
                    && options.ab_attributes.sid
                    && options.ab_attributes.he
            }; // Appointment

        function firstStep() {
            if (Appointment.is_cancelled) {
                fourthStep();
            } else if (Appointment.is_finished) {
                fifthStep();
            } else {
                var d = new Date();

                $.ajax({
                    url : options.ajaxurl,
                    data : { action: 'ab_render_service', form_id: options.form_id, client_time_zone_offset: d.getTimezoneOffset() },
                    dataType : 'json',
                    xhrFields : { withCredentials: true },
                    success : function (response) {
                        $container.html(response.html);

                        if (Appointment.skip_first_step) {
                            $('.ab-first-step').hide();
                        }

                        var booking = new BookedService,
                            $select_category = $('.ab-select-category', $container),
                            $select_service = $('.ab-select-service', $container),
                            $select_employee = $('.ab-select-employee', $container),
                            $requested_date_from = $('.ab-requested-date-from', $container),
                            $requested_time_from = $('.ab-requested-time-from', $container),
                            $requested_time_to = $('.ab-requested-time-to', $container),
                            $service_error =  $('.ab-select-service-error', $container),
                            abCategories = response.categories,
                            abServices   = response.services,
                            abStaff      = response.staff;

                        // Overwrite ab_attributes if necessary.
                        if (response.attributes) {
                            options.ab_attributes.sid = response.attributes.sid;
                            options.ab_attributes.eid = response.attributes.eid;
                        }

                        if (options.ab_attributes.ch && options.ab_attributes.cid){
                            $('.ab-category', $container).hide();
                        }

                        if (options.ab_attributes.hs && options.ab_attributes.sid){
                            $('.ab-service', $container).hide();
                        }

                        if (options.ab_attributes.he){
                            $('.ab-employee', $container).hide();
                        }

                        if (options.ab_attributes.ha){
                            $('.ab-available-date', $container).parent().hide();
                        }

                        var AB_Category = Backbone.Model.extend();
                        var AB_Service = Backbone.Model.extend();
                        var AB_Employee =  Backbone.Model.extend();

                        var AB_Categories = Backbone.Collection.extend({
                            model: AB_Category,
                            comparator: function(category) {
                                return parseInt(category.get('id')) ? category.get('name') : null;
                            }
                        });

                        var AB_Services = Backbone.Collection.extend({
                            model: AB_Service,
                            comparator: 'name'
                        });

                        var AB_Staff = Backbone.Collection.extend({
                            model: AB_Employee,
                            comparator: 'name'
                        });

                        var AB_OptionView = Backbone.View.extend({
                            tagName: "option",

                            initialize: function(){
                                _.bindAll(this, 'render');
                            },
                            render: function(){
                                this.$el.attr('value', this.model.get('id')).html(this.model.get('name'));
                                return this;
                            }
                        });

                        var AB_SelectView = Backbone.View.extend({
                            events: {
                                "change": "changeSelected"
                            },
                            cookie_data : function() {
                                return $.cookie('first_step');
                            },
                            initialize: function() {
                                _.bindAll(this, 'addOne', 'addAll');
                                this.selectView = [];
                                this.collection.bind('reset', this.addAll);
                            },
                            addOne: function(option) {
                                var optionView = new AB_OptionView({ model: option });
                                this.selectView.push(optionView);
                                this.$el.append(optionView.render().el);
                            },
                            addAll: function() {
                                _.each(this.selectView, function(optionView) { optionView.remove(); });
                                this.selectView = [];

                                this.collection.each(this.addOne);

                                if (this.selectedId) {
                                    this.$el.val(this.selectedId);
                                }
                            },
                            changeSelected: function() {
                                this.setSelectedId(this.$el.val());
                            }
                        });

                        var AB_CategoriesView = AB_SelectView.extend({
                            setSelectedId: function(categoryId) {
                                this.selectedId = categoryId;
                                booking.set({ service_id: null, staff_id: [] });
                                if (this.staffView.selectedId && !this.servicesView.selectedId) {
                                    this.servicesView.collection.reset();
                                    if (categoryId) {
                                        this.servicesView.setCategoryEmployeeIds(categoryId, this.staffView.selectedId);
                                    } else {
                                        this.servicesView.setEmployeeId(this.staffView.selectedId);
                                    }
                                } else  {
                                    this.servicesView.selectedId = null;
                                    this.staffView.selectedId = null;
                                    this.servicesView.collection.reset();
                                    this.staffView.collection.reset();
                                    if (categoryId) {
                                        this.servicesView.setCategoryId(categoryId);
                                        this.staffView.setCategoryId(categoryId);
                                    } else {
                                        this.servicesView.setDefaultValues();
                                        this.staffView.setDefaultValues();
                                    }
                                }
                            },
                            setEmployeeId: function(employeeId) {
                                if (abStaff[employeeId] != undefined) {
                                    this.populate(abStaff[employeeId].categories);
                                } else {
                                    this.setDefaultValues();
                                }
                            },
                            populate: function(categories) {
                                var category;
                                for (var category_id in categories) {
                                    category = new AB_Service();
                                    category.set({
                                        id: category_id,
                                        name: categories[category_id].name
                                    });
                                    this.collection.add(category);
                                }
                                this.addAll();
                            },
                            setDefaultValues: function () {
                                var category;
                                for (var category_id in abCategories) {
                                    category = new AB_Category();
                                    category.set({
                                        id: category_id,
                                        name: abCategories[category_id].name
                                    });
                                    this.collection.add(category);
                                }
                                this.addAll();
                            }
                        });

                        var AB_ServicesView = AB_SelectView.extend({
                            setSelectedId: function(serviceId) {
                                this.selectedId = serviceId;
                                this.staffView.selectedId = null;
                                if (serviceId) {
                                    booking.set({ service_id: serviceId });
                                    if (!this.categoriesView.selectedId) {
                                        this.categoriesView.selectedId = Number(abServices[serviceId].category_id);
                                        this.categoriesView.$el.val(this.categoriesView.selectedId);
                                    }
                                    if (this.staffView.$el.val()) {
                                        this.staffView.selectedId = this.staffView.$el.val();
                                        this.staffView.collection.reset();
                                        this.staffView.setServiceId(serviceId);
                                        if (this.categoriesView.selectedId) {
                                            this.categoriesView.selectedId = Number(abServices[serviceId].category_id);
                                            this.categoriesView.$el.val(this.categoriesView.selectedId);
                                        }
                                    } else {
                                        this.staffView.collection.reset();
                                        this.staffView.setServiceId(serviceId);
                                        if (abServices[serviceId]) {
                                            this.categoriesView.selectedId = Number(abServices[serviceId].category_id);
                                            this.categoriesView.$el.val(this.categoriesView.selectedId);
                                        } else {
                                            this.setDefaultValues();
                                        }
                                    }
                                } else if (this.categoriesView.selectedId) {
                                    booking.set({ service_id: null });
                                    this.staffView.$el.val('');
                                    this.staffView.collection.reset();
                                    this.staffView.setCategoryId(this.categoriesView.selectedId);
                                    this.staffView.$el.find('option:first').show();
                                    this.categoriesView.collection.reset();
                                    this.categoriesView.setDefaultValues();
                                    this.categoriesView.$el.val(null);
                                }
                            },
                            setCategoryId: function(categoryId) {
                                if (abCategories[categoryId] != undefined) {
                                    this.populate(abCategories[categoryId].services);
                                } else {
                                    this.setDefaultValues();
                                }
                            },
                            setEmployeeId: function(employeeId) {
                                if (abStaff[employeeId] != undefined) {
                                    this.populate(abStaff[employeeId].services);
                                } else {
                                    this.setDefaultValues();
                                }
                            },
                            setCategoryEmployeeIds: function(categoryId, employeeId) {
                                var service, collection = this.collection, employee = abStaff[employeeId];
                                // It is possible that employeeId does not exist and remain only as short code argument
                                if (employee) {
                                    _.each(employee.services, function(srv, serviceId) {
                                        if (Number(srv.category_id) == categoryId) {
                                            service = new AB_Service();
                                            service.set({
                                                id: serviceId,
                                                name: employee.services[serviceId].name
                                            });
                                            collection.add(service);
                                        }
                                    });
                                    this.addAll();
                                }
                            },
                            populate: function(services) {
                                var service;
                                for (var service_id in services) {
                                    service = new AB_Service();
                                    service.set({
                                        id: service_id,
                                        name: services[service_id].name
                                    });
                                    this.collection.add(service);
                                }
                                this.addAll();
                            },
                            setDefaultValues: function () {
                                var service;
                                for (var service_id in abServices) {
                                    service = new AB_Service();
                                    service.set({
                                        id: service_id,
                                        name: abServices[service_id].name
                                    });
                                    this.collection.add(service);
                                }
                                this.addAll();
                            },
                            getCategory: function(serviceId) {
                                var category_id;
                                _.each(abServices, function(service) {
                                    if (Number(service.id == Number(serviceId))) {
                                        category_id = service.category_id
                                    }
                                });
                                return category_id;
                            },
                            getIdsByCategoryAndEmployee: function(cat_id, employee_id) {
                                var ids = [];
                                _.each(abServices, function(services) {
                                    if (services.category_id == cat_id) {
                                        _.each(services, function(k, v) {
                                            if (v == 'staff' && k != null) {
                                                for (var i in k) {
                                                    if (i == employee_id) {
                                                        ids.push(parseInt(services.id));
                                                    }
                                                }
                                            }
                                        });
                                    }
                                });
                                return ids;
                            }
                        });

                        var AB_StaffView = AB_SelectView.extend({
                            setSelectedId: function(employeeId) {
                                this.selectedId = employeeId;
                                var staff_ids = [];
                                this.collection.each(function (employee) { staff_ids.push(employee.get('id')) });
                                booking.set({ staff_id: staff_ids });
                                if (employeeId) {
                                    booking.set({ staff_id: [employeeId] });
                                    if (!this.categoriesView.selectedId && !this.servicesView.selectedId) {
                                        if (this.categoriesView.$el.val() && this.servicesView.$el.val()) {
                                            this.categoriesView.selectedId = this.categoriesView.$el.val();
                                            this.servicesView.selectedId = this.servicesView.$el.val();
                                        } else if (this.categoriesView.$el.val()) {
                                            this.categoriesView.selectedId = this.categoriesView.$el.val();
                                        } else if (this.servicesView.$el.val()) {
                                            this.servicesView.selectedId = this.servicesView.$el.val();
                                        }
                                        this.categoriesView.collection.reset();
                                        this.servicesView.collection.reset();
                                        this.servicesView.setEmployeeId(employeeId);
                                        this.categoriesView.setEmployeeId(employeeId);
                                    } else if (!this.servicesView.selectedId) {
                                        this.servicesView.collection.reset();
                                        this.servicesView.setCategoryEmployeeIds(this.categoriesView.selectedId, employeeId);
                                    }
                                } else if (!this.categoriesView.selectedId && !this.servicesView.selectedId) {
                                    this.categoriesView.collection.reset();
                                    this.servicesView.collection.reset();
                                    this.categoriesView.setDefaultValues();
                                    this.servicesView.setDefaultValues();
                                } else if (this.categoriesView.selectedId && !this.servicesView.selectedId) {
                                    this.categoriesView.collection.reset();
                                    this.categoriesView.setDefaultValues();
                                    this.categoriesView.$el.val(this.categoriesView.selectedId);
                                    this.servicesView.setCategoryId(this.categoriesView.selectedId);
                                }
                            },
                            setServiceId: function(serviceId) {
                                if (abServices[serviceId] != undefined) {
                                    this.populate(abServices[serviceId].staff);
                                } else {
                                    this.setDefaultValues();
                                }
                            },
                            setCategoryId: function(categoryId) {
                                if (abCategories[categoryId] != undefined) {
                                    this.populate(abCategories[categoryId].staff);
                                } else {
                                    this.setDefaultValues();
                                }
                            },
                            populate: function(staff) {
                                var employee, staff_ids = [], selected_staff_id = this.selectedId;
                                for (var employee_id in staff) {
                                    employee = new AB_Employee();
                                    employee.set({
                                        id: employee_id,
                                        name: staff[employee_id].name
                                    });
                                    this.collection.add(employee);
                                }
                                // If selected a Staff - push single staff_id - otherwise push all staff_ids.
                                if (this.selectedId) {
                                    staff_ids.push(this.selectedId);
                                }
                                else {
                                    this.collection.each(function (employee) {
                                        staff_ids.push(employee.get('id'));
                                    });
                                }
                                booking.set({ staff_id: staff_ids });

                                this.addAll();
                            },
                            setDefaultValues: function () {
                                var employee;
                                for (var employee_id in abStaff) {
                                    employee = new AB_Employee;
                                    employee.set({
                                        id: employee_id,
                                        name: abStaff[employee_id].name
                                    });
                                    this.collection.add(employee);
                                }
                                this.addAll();
                            }
                        });

                        var categoriesView = new AB_CategoriesView({el: $select_category, collection: new AB_Categories() });
                        var servicesView = new AB_ServicesView({el: $select_service, collection: new AB_Services() });
                        var staffView = new AB_StaffView({el: $select_employee, collection: new AB_Staff() });

                        categoriesView.servicesView = servicesView;
                        categoriesView.staffView = staffView;
                        servicesView.staffView = staffView;
                        servicesView.categoriesView = categoriesView;
                        staffView.categoriesView = categoriesView;
                        staffView.servicesView = servicesView;
                        categoriesView.setDefaultValues();
                        servicesView.setDefaultValues();
                        staffView.setDefaultValues();

                        // Init
                        booking.set({
                            service_id: $select_service.val(),
                            requested_date_from: $requested_date_from.val(),
                            requested_time_from: $requested_time_from.val(),
                            requested_time_to: $requested_time_to.val(),
                            available_days: [],
                            form_id: options.form_id,
                            staff_id: [],
                            options: options.ab_attributes
                        });

                        // Categories
                        if (options.ab_attributes.cid) {
                            $select_category.val(options.ab_attributes.cid);
                            categoriesView.setSelectedId(options.ab_attributes.cid);
                        }

                        // Services
                        if (options.ab_attributes.sid) {
                            $select_service.val(options.ab_attributes.sid);
                            servicesView.setSelectedId(options.ab_attributes.sid);
                        }

                        // Employee
                        if (options.ab_attributes.eid) {
                            $select_employee.val(options.ab_attributes.eid);
                            staffView.setSelectedId(options.ab_attributes.eid);
                        }

                        // If returned from other steps by back-button - load data from cookie
                        if ($.cookie('first_step') && Appointment.returned_to_first_step) {
                            var cookie_data = servicesView.cookie_data();
                            if (cookie_data) {
                                // overwrite selected service & category
                                if (cookie_data.service_id) {
                                    servicesView.$el.val(cookie_data.service_id);
                                    categoriesView.$el.val(servicesView.getCategory(cookie_data.service_id));
                                }
                                // overwrite selected staff
                                if (cookie_data.staff_id[0]) {
                                    staffView.collection.reset();
                                    staffView.setServiceId(servicesView.$el.val());
                                    if (cookie_data.staff_id.length == 1) {
                                        staffView.$el.val(cookie_data.staff_id[0]);
                                    }
                                }

                            }
                        } else if ($.cookie('first_step')) {
                            $.removeCookie('first_step');
                        }

                        $('.ab-week-day:checked', $container).each(function () {
                            booking.get('available_days').push($(this).val());
                        });

                        $requested_date_from.pickadate({
                            dateMin: options.no_current_day_appointments,
                            clear: false,
                            today: (options.no_current_day_appointments == 1) ? false : options.today_text,
                            firstDay: options.start_of_week
                        });

                        // clear hash
                        if (document.location.hash) {
                            document.location.href = document.location.href.split('#')[0];
                        }

                        var next_step_handler = function() {
                            var valid = true,
                                $select_wrap    = $('.ab-select-service').parents('.ab-select-wrap'),
                                $time_wrap_from = $('.ab-requested-time-from').parents('.ab-select-wrap'),
                                $time_wrap_to   = $('.ab-requested-time-to').parents('.ab-select-wrap');
                            $service_error.hide();
                            if (!$select_service.val()) {
                                if (!$select_wrap.hasClass('ab-service-error')) {
                                    $select_wrap.addClass('ab-service-error');
                                }

                                $service_error.show();
                                valid = false;
                            }

                            if ($requested_date_from.val()) {
                                $requested_date_from.css('borderColor', '');
                            } else {
                                $requested_date_from.css('borderColor', 'red');
                                valid = false;
                            }
                            if ($requested_time_from.val() == $requested_time_to.val()) {
                                if (!$time_wrap_from.hasClass('ab-service-error') && !$time_wrap_to.hasClass('ab-service-error')) {
                                    $time_wrap_from.addClass('ab-service-error');
                                    $time_wrap_to.addClass('ab-service-error');
                                }
                                $('.ab-select-time-error').show();
                                valid = false;
                            }

                            if (!$('.ab-week-day:checked').length) {
                                valid = false;
                            }
                            return valid;
                        };

                        $('.ab-next-step', $container).on('click', function (e) {
                            e.preventDefault();
                            if (next_step_handler()) {
                                if (!booking.get('service_id')) {
                                    booking.set({ service_id : servicesView.$el.val() });
                                }

                                if ($requested_time_from.find('option:selected').val() == '00:00' &&
                                    $requested_time_to.find('option:selected').val() == '01:00' &&
                                    $requested_time_from.find('option:selected').text() == '12:00 AM' &&
                                    $requested_time_to.find('option:selected').text() == '1:00 AM'
                                    ) {
                                    booking.set({ requested_time_to: '23:59' });
                                }

                                var ladda = Ladda.create(this);
                                ladda.start();

                                $.ajax({
                                    url  : options.ajaxurl,
                                    data : booking.toJSON(),
                                    dataType : 'json',
                                    xhrFields : { withCredentials: true },
                                    success : function (response) {
                                        if (typeof response != "object") {
                                            // set up cookie
                                            $.cookie.raw = $.cookie.json = true;
                                            // (re)write cookie
                                            if ($.cookie('first_step')) {
                                                $.removeCookie('first_step');
                                            }
                                            $.cookie('first_step', booking.toJSON());
                                            Appointment.returned_to_first_step = false;
                                        }
                                        secondStep();
                                    }
                                });
                            }
                        });

                        $('.ab-week-day', $container).on('change', function () {
                            var self = $(this),
                                value = $(this).val();
                            if (self.is(':checked')) {
                                if (!self.parent().hasClass('active')) {
                                    self.parent().addClass('active');
                                }
                                booking.get('available_days').push(value);
                            } else {
                                if (self.parent().hasClass('active')) {
                                    self.parent().removeClass('active')
                                }
                                booking.get('available_days').splice($.inArray(value, booking.get('available_days')), 1);
                            }
                        });

                        $requested_time_from.on('change', function () {
                            var start_time       = $(this).val(),
                                end_time         = $requested_time_to.val(),
                                $last_time_entry = $('option:last', $requested_time_from);

                            // clear the "time_to" list
                            $requested_time_to.empty();
                            // case when we click on the not last time entry
                            if ($requested_time_from[0].selectedIndex < $last_time_entry.index()) {
                                // clone and append all next "time_from" time entries
                                // to "time_to" list
                                $('option', this).each(function () {
                                    if ($(this).val() > start_time) {
                                        $requested_time_to.append($(this).clone());
                                    }
                                });
                            // case when we click on the last time entry
                            } else {
                                $requested_time_to
                                    .append($last_time_entry.clone())
                                    .val($last_time_entry.val());
                            }

                            var first_value =  $('option:first', $requested_time_to).val();
                            if (end_time >= first_value) {
                                $requested_time_to.val(end_time);
                            } else {
                                // select first available "time_to" entry
                                $requested_time_to.val(first_value);
                            }

                            booking.set({
                                requested_time_from: start_time,
                                requested_time_to:   $requested_time_to.val()
                            });
                        });

                        $requested_time_to.on('change', function () {
                            booking.set({ requested_time_to: $(this).val() });
                        });

                        $requested_date_from.on('change', function () {
                            var date = $(this).data('pickadate').getDate(true);

                            // Checks appropriate day of the week
                            $('.ab-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container)
                                .attr('checked', true).trigger('change');

                            booking.set({
                                requested_date_from: date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate()
                            });
                        }).trigger('change');

                        $('.ab-mobile-next-step', $container).on('click', function () {
                            if (next_step_handler()) {
                                if (Appointment.skip_available) {
                                    var ladda = Ladda.create(this);
                                    ladda.start();
                                    $('.ab-mobile-step_2 .ab-next-step', $container).trigger('click');
                                } else {
                                    $(this).parents('.ab-mobile-step_1', $container).hide();
                                    $('.ab-mobile-step_2', $container).show();
                                    if (Appointment.skip_service) {
                                        $('.ab-mobile-prev-step', $container).attr("style", "display: none !important");
                                    }
                                }
                            }

                            return false;
                        });
                        $('.ab-mobile-prev-step', $container).on('click', function () {
                            $('.ab-mobile-step_1', $container).show();
                            $('.ab-mobile-step_2', $container).hide();

                            if ($select_service.val()) {
                                var $select_wrap = $('.ab-select-service').parents('.ab-select-wrap');
                                if ($select_wrap.hasClass('ab-service-error')) {
                                    $select_wrap.removeClass('ab-service-error');
                                }
                            }

                            return false;
                        });

                        // #11681: if requested_time_to wasn't selected by default
                        if ( ! $requested_time_to.find('option:selected').length ) {
                            $requested_time_to.find('option:first').attr('selected', 'selected');
                        }

                        if (Appointment.skip_service) {
                            $('.ab-mobile-next-step', $container).trigger('click');
                        }

                        if (Appointment.skip_first_step) {
                            $('.ab-next-step', $container).trigger('click');
                        }
                    }
                });
            }
        }

        function secondStep(time_is_busy) {
            var d = new Date()

            $.ajax({
                url  : options.ajaxurl,
                data : { action: 'ab_render_time', form_id: options.form_id, client_time_zone_offset: d.getTimezoneOffset() },
                dataType : 'json',
                xhrFields : { withCredentials: true },
                success : function (response) {
                    if (response.status === 'error') {
                        // No time is available. The user selected completely non-working days.
                        $container.html(response.html);
                        $('.ab-to-first-step', $container).on('click', function(e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            firstStep();
                        });
                        return false;
                    }
                    else if (response.status == 'success') {
                        // There are available time slots.
                        $container.html(response.html);

                        if (time_is_busy) {
                            $container.prepend(time_is_busy);
                        }

                        var $next_button = $('.ab-time-next', $container),
                            $prev_button = $('.ab-time-prev', $container),
                            $back_button = $('.ab-to-first-step', $container),
                            $list = $('.ab-second-step', $container),
                            $columnizer_wrap = $('.ab-columnizer-wrap', $list),
                            $columnizer = $('.ab-columnizer', $columnizer_wrap),
                            $column,
                            $screen,
                            $current_screen,
                            $button,
                            screen_index = 0,
                            $screens,
                            item_height = 35,
                            column_width = 127,
                            columns = 0,
                            $current_booking_form = $('#ab-booking-form-' + options.form_id),
                            screen_width = $current_booking_form.width(),
                            window_height = $(window).height(),
                            columns_per_screen = parseInt(screen_width / column_width, 10);

                        if (Appointment.skip_first_step) {
                            $back_button.hide();
                        }

                        if (window_height < 4 * item_height) {
                            window_height = 4 * item_height;
                        }
                        else if (window_height > 8 * item_height) {
                            window_height = 8 * item_height;
                        }
                        var items_per_column = parseInt(window_height / item_height, 10);
                        $columnizer_wrap.css({ height: (items_per_column * item_height + 25) });

                        function createColumns() {
                            var $buttons =  $('> button', $columnizer);
                            var max_length = $buttons.length > items_per_column ? items_per_column : 0;

                            while ($buttons.length > max_length) {
                                $column = $('<div class="ab-column" />');

                                var items_in_column = items_per_column;
                                if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('ab-available-day')) {
                                    // If this is the first column of a screen and the first slot in this column is not day
                                    // then put 1 slot less in this column because createScreens adds 1 more
                                    // slot to such columns.
                                    -- items_in_column;
                                }

                                for (var i = 0; i < items_in_column; ++ i) {
                                    if (i + 1 == items_in_column && $buttons.eq(0).hasClass('ab-available-day')) {
                                        // Skip the last slot if it is day.
                                        break;
                                    }
                                    $button = $($buttons.splice(0, 1));
                                    if (i == 0) {
                                        $button.addClass('ab-first-child');
                                    } else if (i + 1 == items_in_column) {
                                        $button.addClass('ab-last-child');
                                    }
                                    $column.append($button);
                                }
                                $columnizer.append($column);
                                ++ columns;
                            }
                        }

                        function createScreens() {
                            var $columns = $('> .ab-column', $columnizer);

                            if ($container.width() < 2 * column_width) {
                                screen_width = 2 * column_width;
                            }

                            if ($columns.length < columns_per_screen) {
                                columns_per_screen = $columns.length;
                            }

                            while ($columns.length && $columns.length >= columns_per_screen) {
                                $screen = $('<div class="ab-time-screen"/>');
                                for (var i = 0; i < columns_per_screen; i++) {
                                    $column = $($columns.splice(0, 1));
                                    if (i == 0) {
                                        $column.addClass('ab-first-column');
                                        var $first_button_in_first_column = $column.filter('.ab-first-column')
                                            .find('.ab-first-child');
                                        // in first column first button is time
                                        if (!$first_button_in_first_column.hasClass('ab-available-day')) {
                                            var curr_date = $first_button_in_first_column.data('date'),
                                                $curr_date = $('button.ab-available-day[value="' + curr_date + '"]:last');
                                            // copy dateslot to first column
                                            $column.prepend($curr_date.clone());
                                        }
                                    }
                                    $screen.append($column);
                                }
                                $columnizer.append($screen);
                            }
                            $screens = $('.ab-time-screen', $columnizer);
                        }

                        function onTimeSelectionHandler(e, el) {
                            e.preventDefault();
                            var data = {
                                    action: 'ab_session_save',
                                    booked_datetime: el.val(),
                                    staff_id: [el.data('staff_id')],
                                    form_id: options.form_id
                                },
                                ladda = Ladda.create(el[0]);

                            ladda.start();
                            $.ajax({
                                type : 'POST',
                                url  : options.ajaxurl,
                                data : data,
                                xhrFields : { withCredentials: true },
                                success : function (response) {
                                    thirdStep();
                                }
                            });
                        }

                        $next_button.on('click', function (e) {
                            e.preventDefault();
                            var last_date;
                            $prev_button.show();
                            if ($screens.eq(screen_index + 1).length) {
                                $columnizer.animate(
                                    { left: '-=' + $current_screen.width() },
                                    { duration: 800, complete: function () {
                                        $next_button.show();
                                        $prev_button.show();
                                        $back_button.show();
                                    } }
                                );
                                $current_screen = $screens.eq(++screen_index);
                            } else {
                                $button = $('> button:last', $columnizer);
                                if ($button.length) {
                                    last_date = $button.val();
                                } else {
                                    last_date = $('.ab-column:last > button:last', $columnizer).val();
                                }
                                // Render Next Time
                                var data = {
                                        action: 'ab_render_next_time',
                                        form_id: options.form_id,
                                        start_date: last_date
                                    },
                                    ladda = Ladda.create(document.querySelector('.ab-time-next'));
                                ladda.start();
                                $.ajax({
                                    type : 'POST',
                                    url  : options.ajaxurl,
                                    data : data,
                                    dataType : 'json',
                                    xhrFields : { withCredentials: true },
                                    success : function (response) {
                                        if (response.status == 'error') { // no available time
                                            $('.ab-teaser').empty();
                                            $list.html(response.html);
                                            $prev_button.hide();
                                        }
                                        else if (response.status == 'success') { // if there are available time
                                            $columnizer.append(response.html);
                                            createColumns();
                                            createScreens();
                                            $next_button.trigger('click');
                                            $('button.ab-available-hour').off('click').on('click', function (e) {
                                                e.preventDefault();
                                                onTimeSelectionHandler(e, $(this));
                                            });
                                        }
                                        ladda.stop();
                                    }
                                });
                            }
                        });

                        $prev_button.on('click', function () {
                            $current_screen = $screens.eq(--screen_index);
                            $columnizer.animate({ left: '+=' + $current_screen.width() },
                                { duration: 800,  complete: function () {
                                    if (screen_index) {
                                        $prev_button.show();
                                    }
                                    $next_button.show();
                                    $back_button.show();
                                }});
                            if (screen_index === 0) {
                                $prev_button.hide();
                            }
                        });

                        $('button.ab-available-hour').off('click').on('click', function (e) {
                            e.preventDefault();
                            onTimeSelectionHandler(e, $(this));
                        });

                        $back_button.on('click', function (e) {
                            e.preventDefault();
                            Appointment.returned_to_first_step = true;
                            var ladda = Ladda.create(this);
                            ladda.start();
                            firstStep();
                        });

                        createColumns();
                        createScreens();
                        $current_screen = $screens.eq(0);

                        // fixing styles
                        $list.css({
                            'width': function() {
                                var columns_count = parseInt($current_booking_form.width() / column_width, 10);
                                return columns_count * column_width;
                            },
                            'margin-left': 'auto',
                            'margin-right': 'auto',
                            'max-width': '2850px',
                            'max-height': '2534px'
                        });

                        var hammertime = $list.hammer({ swipe_velocity: 0.1 });

                        hammertime.on('swipeleft', function() {
                            $next_button.trigger('click');
                        });

                        hammertime.on('swiperight', function() {
                            if ($prev_button.is(':visible')) {
                                $prev_button.trigger('click');
                            }
                        });
                    }
                    else {
                        // The session doesn't contain data
                        firstStep();
                        return false;
                    }
                }
            });
        }

        function thirdStep() {
            var d = new Date();

            $.ajax({
                url  : options.ajaxurl,
                data : { action: 'ab_render_your_details', form_id: options.form_id, client_time_zone_offset: d.getTimezoneOffset() },
                xhrFields : { withCredentials: true },
                success : function (response) {
                    // The session doesn't contain data
                    if (response.length == 0) {
                        return false;
                    }
                    $container.html(response);

                    // Init
                    var $button_next = $('.ab-to-fourth-step', $container),
                        $back_button = $('.ab-to-second-step', $container),
                        $phone_field = $('.ab-user-phone', $container),
                        $email_field = $('.ab-user-email', $container),
                        $name_field = $('.ab-full-name', $container),
                        $notes_field = $('.ab-user-notes', $container),
                        $phone_error = $('.ab-user-phone-error', $container),
                        $email_error = $('.ab-user-email-error', $container),
                        $name_error = $('.ab-full-name-error', $container);

                    $button_next.on('click', function(e) {
                        e.preventDefault();
                        var data = {
                                action: 'ab_session_save',
                                form_id: options.form_id,
                                name: $name_field.val(),
                                phone: $phone_field.val(),
                                email: $email_field.val(),
                                notes: $notes_field.val()
                            },
                            ladda = Ladda.create(this);
                        ladda.start();
                        $.ajax({
                            type : 'POST',
                            url  : options.ajaxurl,
                            data : data,
                            xhrFields : { withCredentials: true },
                            success : function (response) {
                                // Error messages
                                $name_error.html('');
                                $phone_error.html('');
                                $email_error.html('');
                                if ($name_field.hasClass('ab-details-error')) {
                                    $name_field.removeClass('ab-details-error');
                                }
                                if ($phone_field.hasClass('ab-details-error')) {
                                    $phone_field.removeClass('ab-details-error');
                                }
                                if ($email_field.hasClass('ab-details-error')) {
                                    $email_field.removeClass('ab-details-error');
                                }
                                if ($.isEmptyObject(response)) {
                                    fourthStep();
                                } else {
                                    ladda.stop();
                                    if (response.name) {
                                        $name_error.html(response.name);
                                        $name_field.addClass('ab-details-error');
                                    }
                                    if (response.phone) {
                                        $phone_error.html(response.phone);
                                        $phone_field.addClass('ab-details-error');
                                    }
                                    if (response.email) {
                                        $email_error.html(response.email);
                                        $email_field.addClass('ab-details-error');
                                    }
                                }
                            }
                        });
                    });

                    $back_button.on('click', function (e) {
                        e.preventDefault();
                        var ladda = Ladda.create(this);
                        ladda.start();
                        secondStep();
                    });
                }
            });
        }

        function fourthStep() {
            var d = new Date();

            $.ajax({
                url  : options.ajaxurl,
                data : { action: 'ab_render_payment', form_id: options.form_id, client_time_zone_offset: d.getTimezoneOffset() },
                xhrFields : { withCredentials: true },
                success : function (response) {
                    // The session doesn't contain data or payment is disabled in Admin Settings
                    if (response.status == 'no-data') {
                        Appointment.save_action();
                        return false;
                    }

                    $container.html(response.html);

                    var $local_pay = $('.ab-local-payment', $container),
                        $paypal_pay = $('.ab-paypal-payment', $container),
                        $authorizenet_pay = $('.ab-authorizenet-payment', $container),
                        $stripe_pay = $('.ab-stripe-payment', $container),
                        $local_pay_button = $('.ab-local-pay-button', $container),
                        $paypal_pay_button = $('.ab-paypal-payment-button', $container),
                        $card_payment_button = $('.ab-card-payment-button', $container),
                        $back_button = $('.ab-to-third-step', $container),
                        $apply_coupon_button = $('#apply-coupon', $container),
                        $coupon_input =  $('input.ab-user-coupon', $container),
                        $coupon_error = $('.ab-coupon-error', $container),
                        $coupon_info_text = $('#ab-info-text-coupon', $container);

                    $local_pay.on('click', function () {
                        $paypal_pay_button.hide();
                        $card_payment_button.hide();
                        $('form.authorizenet').hide();
                        $('form.stripe').hide();
                        $local_pay_button.show();
                    });

                    $paypal_pay.on('click', function () {
                        $local_pay_button.hide();
                        $card_payment_button.hide();
                        $('form.authorizenet').hide();
                        $('form.stripe').hide();
                        $paypal_pay_button.show();
                    });

                    $authorizenet_pay.on('click', function () {
                        $local_pay_button.hide();
                        $paypal_pay_button.hide();
                        $('form.stripe').hide();
                        $card_payment_button.show();
                        $('form.authorizenet').show();
                    });

                    $stripe_pay.on('click', function () {
                        $local_pay_button.hide();
                        $paypal_pay_button.hide();
                        $('form.authorizenet').hide();
                        $card_payment_button.show();
                        $('form.stripe').show();
                    });

                    $apply_coupon_button.on('click', function(e) {
                        var ladda = Ladda.create(this);
                        ladda.start();
                        $coupon_error.text('');
                        $coupon_input.removeClass('ab-details-error');

                        var data = {
                            action    : 'ab_apply_coupon',
                            ab_coupon : $coupon_input.val(),
                            form_id   : options.form_id
                        };

                        $.ajax({
                            type : 'POST',
                            url  : options.ajaxurl,
                            data : data,
                            dataType : 'json',
                            xhrFields : { withCredentials: true },
                            success : function (response) {
                                if (response.status == 'success') {
                                    $coupon_info_text.html(response.text);
                                    $coupon_input.replaceWith(data.ab_coupon);
                                    $apply_coupon_button.replaceWith('✓');
                                }
                                else if (response.status == 'error'){
                                    $coupon_error.html(response.error);
                                    $coupon_input.addClass('ab-details-error');
                                    $coupon_info_text.html(response.text);
                                }
                                ladda.stop();
                            },
                            error: function(response) {
                                ladda.stop();
                            }
                        });
                    });

                    $('.ab-final-step', $container).on('click', function (e) {
                        if ($('.ab-local-payment').is(':checked')) { // handle only if was selected local payment !
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            Appointment.save_action();
                        } else if ($('.ab-authorizenet-payment').is(':checked') || $('.ab-stripe-payment').is(':checked')) { // handle only if was selected AuthorizeNet payment !
                            if ($('.ab-authorizenet-payment').is(':checked')){
                                var card_action = 'ab_authorize_net_aim';
                                var card_form   = 'authorizenet';
                            }else{
                                var card_action = 'ab_stripe';
                                var card_form   = 'stripe';
                            }

                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();

                            var data = {
                                action          : card_action,
                                ab_card_number  : $('.' + card_form + ' input[name="ab_card_number"]').val(),
                                ab_card_code    : $('.' + card_form + ' input[name="ab_card_code"]').val(),
                                ab_card_month   : $('.' + card_form + ' select[name="ab_card_month"]').val(),
                                ab_card_year    : $('.' + card_form + ' select[name="ab_card_year"]').val(),
                                form_id         : options.form_id
                            };

                            $.ajax({
                                type : 'POST',
                                url  : options.ajaxurl,
                                data : data,
                                xhrFields : { withCredentials: true },
                                success : function (response) {
                                    var _response;
                                    try {
                                        _response = JSON.parse(response);
                                    } catch (e) {}
                                    if (typeof _response === 'object') {
                                        var $response = $.parseJSON(response);

                                        if ($response.error){
                                            ladda.stop();
                                            $('.' + card_form + ' .ab-card-error').text($response.error);
                                        } else {
                                            Appointment.is_available = !!$response.state;
                                            // remove cookie if appointment had just been booked
                                            if (Appointment.is_available && $.cookie('first_step')) {
                                                $.removeCookie('first_step');
                                            }
                                            fifthStep();
                                        }
                                    } else {
                                        ladda.stop();
                                    }
                                }
                            });
                        }
                    });

                    $back_button.on('click', function (e) {
                        e.preventDefault();
                        var ladda = Ladda.create(this);
                        ladda.start();
                        if (Appointment.is_cancelled) {
                            Appointment.is_cancelled = false;
                            Appointment.is_finished  = false;
                            $.ajax({
                                url  : options.ajaxurl,
                                data : { action: 'ab_destroy_user_data' },
                                xhrFields : { withCredentials: true }
                            });
                            if ($.cookie('first_step')) {
                                $.removeCookie('first_step');
                            }
                            firstStep();
                        } else {
                            thirdStep();
                        }
                    });
                }
            });
        }

        function fifthStep() {
            $.ajax({
                url  : options.ajaxurl,
                data : { action : 'ab_render_complete', form_id : options.form_id },
                xhrFields : { withCredentials: true },
                success : function (response) {
                    // The session doesn't contain data
                    if (response.length == 0) {
                        return false;
                    }

                    var $response = $.parseJSON(response);

                    if (Appointment.is_available || Appointment.is_finished) {
                        Appointment.is_finished = false;
                        // Show Progress Tracker if enabled in settings
                        $response.step ? $container.html($response.step + $response.state.success) :
                            $container.html($response.state.success);

                        // navigate to booking-form
                        if (!document.location.hash) {
                            document.location.hash = 'ab-booking-form-' + options.form_id;
                        }
                    } else {
                        secondStep($response.state.error);
                    }
                }
            });
        }

        if (Appointment.is_finished) {
            fifthStep();
        } else {
            firstStep();
        }

        var BookedService = Backbone.Model.extend({
            defaults: {
                form_id: null,
                action: 'ab_session_save',
                service_id: null,
                staff_id: [],
                requested_date_from: null,
                requested_time_from: null,
                requested_time_to: null,
                available_days: []
            }
        });
    };
})(jQuery);
