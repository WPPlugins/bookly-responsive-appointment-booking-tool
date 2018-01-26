(function($) {
    window.bookly = function(Options) {
        var $container     = $('#bookly-form-' + Options.form_id),
            timeZone       = typeof Intl === 'object' ? Intl.DateTimeFormat().resolvedOptions().timeZone : undefined,
            timeZoneOffset = new Date().getTimezoneOffset();
        Options.skip_steps.service = Options.skip_steps.service_part1 && Options.skip_steps.service_part2;

        // initialize
        if (Options.status.booking == 'finished') {
            stepComplete();
        } else if (Options.status.booking == 'cancelled') {
            stepPayment();
        } else {
            stepService({new_chain : true});
        }

        /**
         * Service step.
         */
        function stepService(params) {
            if (Options.skip_steps.service) {
                if (!Options.skip_steps.extras) {
                    stepExtras(params)
                } else {
                    stepTime(params);
                }
                return;
            }
            var data = $.extend({
                action           : 'bookly_render_service',
                csrf_token       : BooklyL10n.csrf_token,
                form_id          : Options.form_id,
                time_zone        : timeZone,
                time_zone_offset : timeZoneOffset
            }, params);
            $.ajax({
                url         : Options.ajaxurl,
                data        : data,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        BooklyL10n.csrf_token = response.csrf_token;
                        $container.html(response.html);
                        if (params === undefined) { // Scroll when returning to the step Service. default value {new_chain : true}
                            scrollTo($container);
                        }

                        var $chain_item_draft = $('.bookly-js-chain-item.bookly-js-draft', $container),
                            $select_location  = $('.bookly-js-select-location', $container),
                            $select_category  = $('.bookly-js-select-category', $container),
                            $select_service   = $('.bookly-js-select-service',  $container),
                            $select_employee  = $('.bookly-js-select-employee', $container),
                            $select_nop       = $('.bookly-js-select-number-of-persons', $container),
                            $select_quantity  = $('.bookly-js-select-quantity', $container),
                            $date_from        = $('.bookly-js-date-from', $container),
                            $week_day         = $('.bookly-js-week-day', $container),
                            $select_time_from = $('.bookly-js-select-time-from', $container),
                            $select_time_to   = $('.bookly-js-select-time-to', $container),
                            $next_step        = $('.bookly-js-next-step', $container),
                            $mobile_next_step = $('.bookly-js-mobile-next-step', $container),
                            $mobile_prev_step = $('.bookly-js-mobile-prev-step', $container),
                            locations         = response.locations,
                            categories        = response.categories,
                            services          = response.services,
                            staff             = response.staff,
                            chain             = response.chain,
                            last_chain_key    = 0,
                            category_selected = false
                        ;

                        // Init Pickadate.
                        $date_from.pickadate({
                            formatSubmit    : 'yyyy-mm-dd',
                            format          : Options.date_format,
                            min             : response.date_min || true,
                            max             : response.date_max || true,
                            clear           : false,
                            close           : false,
                            today           : BooklyL10n.today,
                            monthsFull      : BooklyL10n.months,
                            weekdaysFull    : BooklyL10n.days,
                            weekdaysShort   : BooklyL10n.daysShort,
                            labelMonthNext  : BooklyL10n.nextMonth,
                            labelMonthPrev  : BooklyL10n.prevMonth,
                            firstDay        : Options.start_of_week,
                            onSet           : function(timestamp) {
                                if ($.isNumeric(timestamp.select)) {
                                    // Checks appropriate day of the week
                                    var date = new Date(timestamp.select);
                                    $('.bookly-js-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container).attr('checked', true).trigger('change');
                                }
                            }
                        });

                        $('.bookly-js-go-to-cart', $container).on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepCart({from_step : 'service'});
                        });

                        // insert data into select
                        function setSelect($select, data, value) {
                            // reset select
                            $('option:not([value=""])', $select).remove();
                            // and fill the new data
                            var docFragment = document.createDocumentFragment();

                            function valuesToArray(obj) {
                                return Object.keys(obj).map(function (key) { return obj[key]; });
                            }

                            function compare(a, b) {
                                if (parseInt(a.pos) < parseInt(b.pos))
                                    return -1;
                                if (parseInt(a.pos) > parseInt(b.pos))
                                    return 1;
                                return 0;
                            }

                            // sort select by position
                            data = valuesToArray(data).sort(compare);

                            $.each(data, function(key, object) {
                                var option = document.createElement('option');
                                option.value = object.id;
                                option.text = object.name;
                                docFragment.appendChild(option);
                            });
                            $select.append(docFragment);
                            // set default value of select
                            $select.val(value);
                        }

                        function setSelects($chain_item, location_id, category_id, service_id, staff_id) {
                            var _staff = {}, _services = {}, _categories = {}, _nop = {};
                            $.each(staff, function(id, staff_member) {
                                if (!location_id || locations[location_id].staff.hasOwnProperty(id)) {
                                    if (!service_id) {
                                        if (!category_id) {
                                            _staff[id] = staff_member;
                                        } else {
                                            $.each(staff_member.services, function(s_id) {
                                                if (services[s_id].category_id == category_id) {
                                                    _staff[id] = staff_member;
                                                    return false;
                                                }
                                            });
                                        }
                                    } else if (staff_member.services.hasOwnProperty(service_id)) {
                                        if (staff_member.services[service_id].price != null) {
                                            _staff[id] = {
                                                id   : id,
                                                name : staff_member.name + ' (' + staff_member.services[service_id].price + ')',
                                                pos  : staff_member.pos
                                            };
                                        } else {
                                            _staff[id] = staff_member;
                                        }
                                    }
                                }
                            });
                            if (!location_id) {
                                _categories = categories;
                                $.each(services, function(id, service) {
                                    if (!category_id || service.category_id == category_id) {
                                        if (!staff_id || staff[staff_id].services.hasOwnProperty(id)) {
                                            _services[id] = service;
                                        }
                                    }
                                });
                            } else {
                                var category_ids = [],
                                    service_ids  = [];
                                $.each(locations[location_id].staff, function(st_id) {
                                    $.each(staff[st_id].services, function(s_id) {
                                        category_ids.push(services[s_id].category_id);
                                        service_ids.push(s_id);
                                    });
                                });
                                $.each(categories, function(id, category) {
                                    if ($.inArray(parseInt(id), category_ids) > -1) {
                                        _categories[id] = category;
                                    }
                                });
                                $.each(services, function(id, service) {
                                    if ($.inArray(id, service_ids) > -1) {
                                        if (!category_id || service.category_id == category_id) {
                                            if (!staff_id || staff[staff_id].services.hasOwnProperty(id)) {
                                                _services[id] = service;
                                            }
                                        }
                                    }
                                });
                            }
                            var nop = $chain_item.find('.bookly-js-select-number-of-persons').val();
                            var max_capacity = service_id
                                ? (staff_id
                                    ? staff[staff_id].services[service_id].max_capacity
                                    : services[service_id].max_capacity)
                                : 1;
                            var min_capacity = service_id
                                ? (staff_id
                                    ? staff[staff_id].services[service_id].min_capacity
                                    : services[service_id].min_capacity)
                                : 1;
                            for (var i = min_capacity; i <= max_capacity; ++ i) {
                                _nop[i] = { id: i, name: i, pos: i };
                            }
                            if (nop > max_capacity) {
                                nop = max_capacity;
                            }
                            if (nop < min_capacity || !Options.attributes.show_number_of_persons) {
                                nop = min_capacity;
                            }
                            setSelect($chain_item.find('.bookly-js-select-category'), _categories, category_id);
                            setSelect($chain_item.find('.bookly-js-select-service'), _services, service_id);
                            setSelect($chain_item.find('.bookly-js-select-employee'), _staff, staff_id);
                            setSelect($chain_item.find('.bookly-js-select-number-of-persons'), _nop, nop);
                        }

                        $container.off('click').off('change');

                        // Location select change
                        $container.on('change', '.bookly-js-select-location', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = this.value,
                                category_id = $chain_item.find('.bookly-js-select-category').val(),
                                service_id  = $chain_item.find('.bookly-js-select-service').val(),
                                staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                                ;

                            // Validate selected values.
                            if (location_id) {
                                if (staff_id && !locations[location_id].staff.hasOwnProperty(staff_id)) {
                                    staff_id = '';
                                }
                                if (service_id) {
                                    var valid = false;
                                    $.each(locations[location_id].staff, function(id) {
                                        if (staff[id].services.hasOwnProperty(service_id)) {
                                            valid = true;
                                            return false;
                                        }
                                    });
                                    if (!valid) {
                                        service_id = '';
                                    }
                                }
                                if (category_id) {
                                    var valid = false;
                                    $.each(locations[location_id].staff, function(id) {
                                        $.each(staff[id].services, function(s_id) {
                                            if (services[s_id].category_id == category_id) {
                                                valid = true;
                                                return false;
                                            }
                                        });
                                        if (valid) {
                                            return false;
                                        }
                                    });
                                    if (!valid) {
                                        category_id = '';
                                    }
                                }
                            }
                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        });

                        // Category select change
                        $container.on('change', '.bookly-js-select-category', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.bookly-js-select-location').val(),
                                category_id = this.value,
                                service_id  = $chain_item.find('.bookly-js-select-service').val(),
                                staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                                ;

                            // Validate selected values.
                            if (category_id) {
                                category_selected = true;
                                if (service_id) {
                                    if (services[service_id].category_id != category_id) {
                                        service_id = '';
                                    }
                                }
                                if (staff_id) {
                                    var valid = false;
                                    $.each(staff[staff_id].services, function(id) {
                                        if (services[id].category_id == category_id) {
                                            valid = true;
                                            return false;
                                        }
                                    });
                                    if (!valid) {
                                        staff_id = '';
                                    }
                                }
                            } else {
                                category_selected = false;
                            }
                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        });

                        // Service select change
                        $container.on('change', '.bookly-js-select-service', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.bookly-js-select-location').val(),
                                category_id = category_selected
                                    ? $chain_item.find('.bookly-js-select-category').val()
                                    : '',
                                service_id  = this.value,
                                staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                                ;

                            // Validate selected values.
                            if (service_id) {
                                if (staff_id && !staff[staff_id].services.hasOwnProperty(service_id)) {
                                    staff_id = '';
                                }
                            }
                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                            if (service_id) {
                                $chain_item.find('.bookly-js-select-category').val(services[service_id].category_id);
                            }
                        });

                        // Staff select change
                        $container.on('change', '.bookly-js-select-employee', function() {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.bookly-js-select-location').val(),
                                category_id = $('.bookly-js-select-category', $chain_item).val(),
                                service_id  = $chain_item.find('.bookly-js-select-service').val(),
                                staff_id    = this.value
                                ;

                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        });

                        // Set up draft selects.
                        if (Options.attributes.show_service_duration) {
                            $.each(services, function(id, service) {
                                service.name = service.name + ' ( ' + service.duration + ' )';
                            });
                        }

                        setSelect($select_location, locations);
                        setSelect($select_category, categories);
                        setSelect($select_service,  services);
                        setSelect($select_employee, staff);
                        $select_location.closest('.bookly-form-group').toggle(!Options.attributes.hide_locations);
                        $select_category.closest('.bookly-form-group').toggle(!Options.attributes.hide_categories);
                        $select_service.closest('.bookly-form-group').toggle(!(Options.attributes.hide_services && Options.attributes.service_id));
                        $select_employee.closest('.bookly-form-group').toggle(!Options.attributes.hide_staff_members);
                        $select_nop.closest('.bookly-form-group').toggle(Options.attributes.show_number_of_persons);
                        $select_quantity.closest('.bookly-form-group').toggle(!Options.attributes.hide_quantity);
                        if (Options.attributes.location_id) {
                            $select_location.val(Options.attributes.location_id).trigger('change');
                        }
                        if (Options.attributes.category_id) {
                            $select_category.val(Options.attributes.category_id).trigger('change');
                        }
                        if (Options.attributes.service_id) {
                            $select_service.val(Options.attributes.service_id).trigger('change');
                        }
                        if (Options.attributes.staff_member_id) {
                            $select_employee.val(Options.attributes.staff_member_id).trigger('change');
                        }

                        if (Options.attributes.hide_date) {
                            $('.bookly-js-available-date', $container).hide();
                        }
                        if (Options.attributes.hide_week_days) {
                            $('.bookly-js-week-days', $container).hide();
                        }
                        if (Options.attributes.hide_time_range) {
                            $('.bookly-js-time-range', $container).hide();
                        }

                        // Create chain items.
                        $.each(chain, function(key, chain_item) {
                            var $chain_item = $chain_item_draft
                                .clone()
                                .data('chain_key', key)
                                .removeClass('bookly-js-draft')
                                .css('display', 'table');
                            $chain_item_draft.find('select').each(function (i, select) {
                                $chain_item.find('select:eq(' + i + ')').val(select.value);
                            });
                            last_chain_key = key;
                            if (key == 0) {
                                $chain_item.find('.bookly-js-actions button[data-action="drop"]').remove();
                            }
                            $('.bookly-js-chain-item:last', $container).after($chain_item);
                            if (!Options.attributes.hide_locations && chain_item.location_id) {
                                $('.bookly-js-select-location', $chain_item).val(chain_item.location_id).trigger('change');
                            }
                            if (chain_item.service_id) {
                                $('.bookly-js-select-service', $chain_item).val(chain_item.service_id).trigger('change');
                                if (Options.attributes.hide_categories) {
                                    // Deselect category to keep full list of services.
                                    $('.bookly-js-select-category', $chain_item).val('');
                                }
                            }
                            if (!Options.attributes.hide_staff_members && chain_item.staff_ids.length == 1 && chain_item.staff_ids[0]) {
                                $('.bookly-js-select-employee', $chain_item).val(chain_item.staff_ids[0]).trigger('change');
                            }
                            if (chain_item.number_of_persons > 1) {
                                $('.bookly-js-select-number-of-persons', $chain_item).val(chain_item.number_of_persons);
                            }
                            if (chain_item.quantity > 1) {
                                $('.bookly-js-select-quantity', $chain_item).val(chain_item.quantity);
                            }
                        });

                        $container.on('click', '.bookly-js-mobile-step-1 .bookly-js-actions button', function () {
                            switch ($(this).data('action')) {
                                case 'plus':
                                    var $new_chain = $chain_item_draft.clone();
                                    $chain_item_draft.find('select').each(function (i, select) {
                                        $new_chain.find('select:eq(' + i + ')').val(select.value);
                                    });
                                    $('.bookly-js-chain-item:last', $container)
                                        .after(
                                            $new_chain
                                                .data('chain_key', ++ last_chain_key)
                                                .removeClass('bookly-js-draft')
                                                .css('display', 'table')
                                        );
                                    break;
                                case 'drop':
                                    $(this).closest('.bookly-js-chain-item').remove();
                                    break;
                            }
                        });

                        // change week days
                        $week_day.on('change', function () {
                            var $this = $(this);
                            if ($this.is(':checked')) {
                                $this.parent().not("[class*='active']").addClass('active');
                            } else {
                                $this.parent().removeClass('active');
                            }
                        });

                        // time from
                        $select_time_from.on('change', function () {
                            var start_time       = $(this).val(),
                                end_time         = $select_time_to.val(),
                                $last_time_entry = $('option:last', $select_time_from);

                            $select_time_to.empty();

                            // case when we click on the not last time entry
                            if ($select_time_from[0].selectedIndex < $last_time_entry.index()) {
                                // clone and append all next "time_from" time entries to "time_to" list
                                $('option', this).each(function () {
                                    if ($(this).val() > start_time) {
                                        $select_time_to.append($(this).clone());
                                    }
                                });
                            // case when we click on the last time entry
                            } else {
                                $select_time_to.append($last_time_entry.clone()).val($last_time_entry.val());
                            }

                            var first_value = $('option:first', $select_time_to).val();
                            $select_time_to.val(end_time >= first_value ? end_time : first_value);
                        });

                        var stepServiceValidator = function() {
                            $('.bookly-js-select-service-error',  $container).hide();
                            $('.bookly-js-select-employee-error', $container).hide();
                            $('.bookly-js-select-location-error', $container).hide();

                            var valid            = true,
                                $select_service  = null,
                                $select_employee = null,
                                $select_location = null,
                                $scroll_to       = null;

                            $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                                var $chain = $(this);
                                $select_service  = $('.bookly-js-select-service',  $chain);
                                $select_employee = $('.bookly-js-select-employee', $chain);
                                $select_location = $('.bookly-js-select-location', $chain);

                                $select_service.removeClass('bookly-error');
                                $select_employee.removeClass('bookly-error');
                                $select_location.removeClass('bookly-error');

                                // service validation
                                if (!$select_service.val()) {
                                    valid = false;
                                    $select_service.addClass('bookly-error');
                                    $('.bookly-js-select-service-error', $chain).show();
                                    $scroll_to = $select_service;
                                }
                                if (Options.required.hasOwnProperty('location') && Options.required.location && !$select_location.val()) {
                                    valid = false;
                                    $select_location.addClass('bookly-error');
                                    $('.bookly-js-select-location-error', $chain).show();
                                    $scroll_to = $select_location;
                                }
                                if (Options.required.staff && !$select_employee.val()) {
                                    valid = false;
                                    $select_employee.addClass('bookly-error');
                                    $('.bookly-js-select-employee-error', $chain).show();
                                    $scroll_to = $select_employee;
                                }
                            });

                            $date_from.removeClass('bookly-error');
                            // date validation
                            if (!$date_from.val()) {
                                valid = false;
                                $date_from.addClass('bookly-error');
                                if ($scroll_to === null) {
                                    $scroll_to = $date_from;
                                }
                            }

                            // week days
                            if (!$('.bookly-js-week-day:checked', $container).length) {
                                valid = false;
                                if ($scroll_to === null) {
                                    $scroll_to = $week_day;
                                }
                            }

                            if ($scroll_to !== null) {
                                scrollTo($scroll_to);
                            }

                            return valid;
                        };

                        // "Next" click
                        $next_step.on('click', function (e) {
                            e.preventDefault();

                            if (stepServiceValidator()) {

                                ladda_start(this);

                                // Prepare chain data.
                                var chain = {};
                                var has_extras = 0;
                                $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                                    var $chain_item = $(this);
                                    var staff_ids = [];
                                    if ($('.bookly-js-select-employee', $chain_item).val()) {
                                        staff_ids.push($('.bookly-js-select-employee', $chain_item).val());
                                    } else {
                                        $('.bookly-js-select-employee', $chain_item).find('option').each(function () {
                                            if (this.value) {
                                                staff_ids.push(this.value);
                                            }
                                        });
                                    }
                                    chain[$chain_item.data('chain_key')] = {
                                        location_id       : $('.bookly-js-select-location', $chain_item).val(),
                                        service_id        : $('.bookly-js-select-service', $chain_item).val(),
                                        staff_ids         : staff_ids,
                                        number_of_persons : $('.bookly-js-select-number-of-persons', $chain_item).val(),
                                        quantity          : $('.bookly-js-select-quantity', $chain_item).val() ? $('.bookly-js-select-quantity', $chain_item).val() : 1
                                    };
                                    has_extras += services[$('.bookly-js-select-service', $chain_item).val()].has_extras;
                                });

                                // Prepare days.
                                var days = [];
                                $('.bookly-js-week-days .active input.bookly-js-week-day', $container).each(function() {
                                    days.push(this.value);
                                });
                                $.ajax({
                                    type : 'POST',
                                    url  : Options.ajaxurl,
                                    data : {
                                        action     : 'bookly_session_save',
                                        csrf_token : BooklyL10n.csrf_token,
                                        form_id    : Options.form_id,
                                        chain      : chain,
                                        date_from  : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                        days       : days,
                                        time_from  : $select_time_from.val(),
                                        time_to    : $select_time_to.val()
                                    },
                                    dataType    : 'json',
                                    xhrFields   : { withCredentials: true },
                                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                    success     : function (response) {
                                        if (!Options.skip_steps.extras) {
                                            if (has_extras == 0) {
                                                Options.no_extras = true;
                                                stepTime();
                                            } else {
                                                Options.no_extras = false;
                                                stepExtras();
                                            }
                                        } else {
                                            stepTime();
                                        }
                                    }
                                });
                            }
                        });


                        //
                        $mobile_next_step.on('click', function (e,skip_scroll) {
                            if (stepServiceValidator()) {
                                if (Options.skip_steps.service_part2) {
                                    ladda_start(this);
                                    $next_step.trigger('click');
                                } else {
                                    $('.bookly-js-mobile-step-1', $container).hide();
                                    $('.bookly-js-mobile-step-2', $container).css('display', 'block');
                                    if (skip_scroll != true) {
                                        scrollTo($container);
                                    }
                                }
                            }

                            return false;
                        });

                        if (Options.skip_steps.service_part1) {
                            // Skip scrolling
                            $mobile_next_step.trigger('click', [true]);
                            $mobile_prev_step.remove();
                        } else {
                            $mobile_prev_step.on('click', function () {
                                $('.bookly-js-mobile-step-1', $container).show();
                                $('.bookly-js-mobile-step-2', $container).hide();
                                if ($select_service.val()) {
                                    $('.bookly-js-select-service', $container).parent().removeClass('bookly-error');
                                }
                                return false;
                            });
                        }
                    }
                } // ajax success
            }); // ajax
        }

        /**
         * Extras step.
         */
        function stepExtras(params) {
            var data = $.extend({
                action     : 'bookly_render_extras',
                csrf_token : BooklyL10n.csrf_token,
                form_id    : Options.form_id
            }, params);
            if (Options.skip_steps.service) {
                // If Service step is skipped then we need to send time zone offset.
                data.time_zone        = timeZone;
                data.time_zone_offset = timeZoneOffset;
            }
            $.ajax({
                url: Options.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        BooklyL10n.csrf_token = response.csrf_token;
                        $container.html(response.html);
                        if (params === undefined) { // Scroll when returning to the step Extras.
                            scrollTo($container);
                        }
                        var $next_step = $('.bookly-js-next-step', $container),
                            $back_step = $('.bookly-js-back-step', $container),
                            $goto_cart = $('.bookly-js-go-to-cart', $container),
                            $extras_items = $('.bookly-js-extras-item', $container),
                            $extras_summary = $('.bookly-js-extras-summary span', $container),
                            currency = response.currency,
                            $this,
                            $input;

                        $extras_items.each(function (index, elem) {
                            var $this = $(this);
                            var $input = $this.find('input');
                            $this.find('.bookly-js-extras-thumb').on('click', function () {
                                extras_changed($this, $input.val() > 0 ? 0 : 1);
                            });
                            $this.find('.bookly-js-count-control').on('click', function() {
                                var count = parseInt($input.val());
                                count = $(this).hasClass('bookly-js-extras-increment')
                                    ? Math.min($this.data('max_quantity'), count + 1)
                                    : Math.max(0, count - 1);
                                extras_changed($this, count);
                            });
                        });

                        function extras_changed($extras_item, quantity) {
                            var $input = $extras_item.find('input');
                            var $total = $extras_item.find('.bookly-js-extras-total-price');
                            var total_price = quantity * parseFloat($extras_item.data('price'));

                            $total.text(currency.format.replace('1', total_price.toFixed(currency.precision)));
                            $input.val(quantity);
                            $extras_item.find('.bookly-js-extras-thumb').toggleClass('bookly-extras-selected', quantity > 0);

                            // Updating summary
                            var amount = 0;
                            $extras_items.each(function (index, elem) {
                                var $this = $(this);
                                amount += parseFloat($this.data('price')) * $this.find('input').val();
                            });
                            if (amount) {
                                $extras_summary.html(' + ' + currency.format.replace('1', amount.toFixed(currency.precision)));
                            } else {
                                $extras_summary.html('');
                            }
                        }

                        $goto_cart.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepCart({from_step : 'extras'});
                        });

                        $next_step.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            var extras = {};
                            $('.bookly-js-extras-container', $container).each(function () {
                                var $extras_container = $(this);
                                var chain_id = $extras_container.data('chain');
                                var chain_extras = {};
                                // Get checked extras for chain.
                                $extras_container.find('.bookly-js-extras-item').each(function (index, elem) {
                                    $this = $(this);
                                    $input = $this.find('input');
                                    if ($input.val() > 0) {
                                        chain_extras[$this.data('id')] = $input.val();
                                    }
                                });
                                extras[chain_id] = JSON.stringify(chain_extras);
                            });
                            $.ajax({
                                type : 'POST',
                                url  : Options.ajaxurl,
                                data : {
                                    action     : 'bookly_session_save',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : Options.form_id,
                                    extras     : extras
                                },
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    stepTime();
                                }
                            });
                        });
                        $back_step.on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepService();
                        }).toggle(!Options.skip_steps.service);
                    }
                }
            });
        }

        /**
         * Time step.
         */
        var xhr_render_time = null;
        function stepTime(params, error_message) {
            if (xhr_render_time != null) {
                xhr_render_time.abort();
                xhr_render_time = null;
            }
            var data = $.extend({
                action: 'bookly_render_time',
                csrf_token: BooklyL10n.csrf_token,
                form_id: Options.form_id,
                skip_extras: false
            }, params);
            if (Options.skip_steps.service) {
                // If Service step is skipped then we need to send time zone offset.
                data.time_zone        = timeZone;
                data.time_zone_offset = timeZoneOffset;
            }
            xhr_render_time = $.ajax({
                url         : Options.ajaxurl,
                data        : data,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success == false) {
                        // The session doesn't contain data.
                        stepService();
                        return;
                    }
                    BooklyL10n.csrf_token = response.csrf_token;
                    $container.html(response.html);

                    var $columnizer_wrap  = $('.bookly-columnizer-wrap', $container),
                        $columnizer       = $('.bookly-columnizer', $columnizer_wrap),
                        $time_next_button = $('.bookly-time-next',  $container),
                        $time_prev_button = $('.bookly-time-prev',  $container),
                        $current_screen   = null,
                        slot_height       = 36,
                        column_width      = 127,
                        columns           = 0,
                        screen_index      = 0,
                        has_more_slots    = response.has_more_slots,
                        form_hidden       = false,
                        $screens,
                        slots_per_column,
                        columns_per_screen,
                        show_day_per_column = response.day_one_column
                    ;
                    // 'BACK' button.
                    $('.bookly-js-back-step', $container).on('click', function (e) {
                        e.preventDefault();
                        ladda_start(this);
                        if (!Options.skip_steps.extras && !Options.no_extras) {
                            stepExtras();
                        } else {
                            stepService();
                        }
                    }).toggle(!Options.skip_steps.service || !Options.skip_steps.extras);

                    $('.bookly-js-go-to-cart', $container).on('click', function(e) {
                        e.preventDefault();
                        ladda_start(this);
                        stepCart({from_step : 'time'});
                    });

                    if (Options.show_calendar) {
                        // Init calendar.
                        var $input = $('.bookly-js-selected-date', $container);
                        $input.pickadate({
                            formatSubmit  : 'yyyy-mm-dd',
                            format        : Options.date_format,
                            min           : response.date_min || true,
                            max           : response.date_max || true,
                            weekdaysFull  : BooklyL10n.days,
                            weekdaysShort : BooklyL10n.daysShort,
                            monthsFull    : BooklyL10n.months,
                            firstDay      : Options.start_of_week,
                            clear         : false,
                            close         : false,
                            today         : false,
                            disable       : response.disabled_days,
                            closeOnSelect : false,
                            klass : {
                                picker: 'picker picker--opened picker--focused'
                            },
                            onSet: function(e) {
                                if (e.select) {
                                    var date = this.get('select', 'yyyy-mm-dd');
                                    if (response.slots[date]) {
                                        // Get data from response.slots.
                                        $columnizer.html(response.slots[date]).css('left', '0px');
                                        columns = 0;
                                        screen_index = 0;
                                        $current_screen = null;
                                        initSlots();
                                        $time_prev_button.hide();
                                        $time_next_button.toggle($screens.length != 1);
                                    } else {
                                        // Load new data from server.
                                        stepTime({selected_date : date});
                                        showSpinner();
                                    }
                                }
                                this.open();   // Fix ultimate-member plugin
                            },
                            onClose: function() {
                                this.open(false);
                            },
                            onRender: function() {
                                var date = new Date(Date.UTC(this.get('view').year, this.get('view').month));
                                $('.picker__nav--next').on('click', function() {
                                    date.setUTCMonth(date.getUTCMonth() + 1);
                                    stepTime({selected_date : date.toJSON().substr(0, 10)});
                                    showSpinner();
                                });
                                $('.picker__nav--prev').on('click', function() {
                                    date.setUTCMonth(date.getUTCMonth() - 1);
                                    stepTime({selected_date : date.toJSON().substr(0, 10)});
                                    showSpinner();
                                });
                            }
                        });
                        // Insert slots for selected day.
                        var date = $input.pickadate('picker').get('select', 'yyyy-mm-dd');
                        $columnizer.html(response.slots[date]);
                    } else {
                        // Insert all slots.
                        var slots = '';
                        $.each(response.slots, function(group, group_slots) {
                            slots += group_slots;
                        });
                        $columnizer.html(slots);
                    }

                    if (response.has_slots) {
                        if (error_message) {
                            $container.find('.bookly-holder.bookly-label-error').html(error_message);
                        } else {
                            $container.find('.bookly-holder.bookly-label-error').hide();
                        }

                        // Calculate number of slots per column.
                        slots_per_column = parseInt($(window).height() / slot_height, 10);
                        if (slots_per_column < 4) {
                            slots_per_column = 4;
                        } else if (slots_per_column > 10) {
                            slots_per_column = 10;
                        }

                        columns_per_screen = parseInt($columnizer_wrap.width() / column_width, 10);

                        if (columns_per_screen > 10) {
                            columns_per_screen = 10;
                        } else if (columns_per_screen == 0) {
                            // Bookly form display hidden.
                            form_hidden = true;
                            columns_per_screen = 4;
                        }

                        initSlots();

                        if (!has_more_slots && $screens.length == 1) {
                            $time_next_button.hide();
                        }

                        var hammertime = $('.bookly-time-step', $container).hammer({ swipe_velocity: 0.1 });

                        hammertime.on('swipeleft', function() {
                            if ($time_next_button.is(':visible')) {
                                $time_next_button.trigger('click');
                            }
                        });

                        hammertime.on('swiperight', function() {
                            if ($time_prev_button.is(':visible')) {
                                $time_prev_button.trigger('click');
                            }
                        });

                        $time_next_button.on('click', function (e) {
                            $time_prev_button.show();
                            if ($screens.eq(screen_index + 1).length) {
                                $columnizer.animate(
                                    { left: (Options.is_rtl ? '+' : '-') + ( screen_index + 1 ) * $current_screen.width() },
                                    { duration: 800 }
                                );

                                $current_screen = $screens.eq(++ screen_index);
                                $columnizer_wrap.animate(
                                    { height: $current_screen.height() },
                                    { duration: 800 }
                                );

                                if (screen_index + 1 == $screens.length && !has_more_slots) {
                                    $time_next_button.hide();
                                }
                            } else if (has_more_slots) {
                                // Do ajax request when there are more slots.
                                var $button = $('> button:last', $columnizer);
                                if ($button.length == 0) {
                                    $button = $('.bookly-column:hidden:last > button:last', $columnizer);
                                    if ($button.length == 0) {
                                        $button = $('.bookly-column:last > button:last', $columnizer);
                                    }
                                }

                                // Render Next Time
                                var data = {
                                        action     : 'bookly_render_next_time',
                                        csrf_token : BooklyL10n.csrf_token,
                                        form_id    : Options.form_id,
                                        last_slot  : $button.val()
                                    },
                                    ladda = ladda_start(this);

                                $.ajax({
                                    type : 'POST',
                                    url  : Options.ajaxurl,
                                    data : data,
                                    dataType : 'json',
                                    xhrFields : { withCredentials: true },
                                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                    success : function (response) {
                                        if (response.success) {
                                            if (response.has_slots) { // if there are available time
                                                has_more_slots = response.has_more_slots;
                                                var $html = $(response.html);
                                                // The first slot is always a day slot.
                                                // Check if such day slot already exists (this can happen
                                                // because of time zone offset) and then remove the first slot.
                                                var $first_day = $html.eq(0);
                                                if ($('button.bookly-day[value="' + $first_day.attr('value') + '"]', $container).length) {
                                                    $html = $html.not(':first');
                                                }
                                                $columnizer.append($html);
                                                initSlots();
                                                $time_next_button.trigger('click');
                                            } else { // no available time
                                                $time_next_button.hide();
                                            }
                                        } else { // no available time
                                            $time_next_button.hide();
                                        }
                                        ladda.stop();
                                    }
                                });
                            }
                        });

                        $time_prev_button.on('click', function () {
                            $time_next_button.show();
                            $current_screen = $screens.eq(-- screen_index);
                            $columnizer.animate(
                                { left: (Options.is_rtl ? '+' : '-') + screen_index * $current_screen.width() },
                                { duration: 800 }
                            );
                            $columnizer_wrap.animate(
                                { height: $current_screen.height() },
                                { duration: 800 }
                            );
                            if (screen_index === 0) {
                                $time_prev_button.hide();
                            }
                        });
                    }
                    if (params === undefined) {     // Scroll when returning to the step Time.
                        scrollTo($container);
                    }

                    function showSpinner() {
                        $('.bookly-time-screen,.bookly-not-time-screen', $container).addClass('bookly-spin-overlay');
                        var opts = {
                            lines : 11, // The number of lines to draw
                            length: 11, // The length of each line
                            width : 4,  // The line thickness
                            radius: 5   // The radius of the inner circle
                        };
                        if ($screens) {
                            new Spinner(opts).spin($screens.eq(screen_index).get(0));
                        } else {
                            // Calendar not available month.
                            new Spinner(opts).spin($('.bookly-not-time-screen', $container).get(0));
                        }
                    }

                    function initSlots() {
                        var $buttons    = $('> button', $columnizer),
                            slots_count = 0,
                            max_slots   = 0,
                            $button,
                            $column,
                            $screen;

                        if (show_day_per_column) {
                            /**
                             * Create columns for 'Show each day in one column' mode.
                             */
                            while ($buttons.length > 0) {
                                // Create column.
                                if ($buttons.eq(0).hasClass('bookly-day')) {
                                    slots_count = 1;
                                    $column = $('<div class="bookly-column" />');
                                    $button = $($buttons.splice(0, 1));
                                    $button.addClass('bookly-js-first-child');
                                    $column.append($button);
                                } else {
                                    slots_count ++;
                                    $button = $($buttons.splice(0, 1));
                                    // If it is last slot in the column.
                                    if (!$buttons.length || $buttons.eq(0).hasClass('bookly-day')) {
                                        $button.addClass('bookly-last-child');
                                        $column.append($button);
                                        $columnizer.append($column);
                                    } else {
                                        $column.append($button);
                                    }
                                }
                                // Calculate max number of slots.
                                if (slots_count > max_slots) {
                                    max_slots = slots_count;
                                }
                            }
                        } else {
                            /**
                             * Create columns for normal mode.
                             */
                            while (has_more_slots ? $buttons.length > slots_per_column : $buttons.length) {
                                $column = $('<div class="bookly-column" />');
                                max_slots = slots_per_column;
                                if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('bookly-day')) {
                                    // If this is the first column of a screen and the first slot in this column is not day
                                    // then put 1 slot less in this column because createScreens adds 1 more
                                    // slot to such columns.
                                    -- max_slots;
                                }
                                for (var i = 0; i < max_slots; ++ i) {
                                    if (i + 1 == max_slots && $buttons.eq(0).hasClass('bookly-day')) {
                                        // Skip the last slot if it is day.
                                        break;
                                    }
                                    $button = $($buttons.splice(0, 1));
                                    if (i == 0) {
                                        $button.addClass('bookly-js-first-child');
                                    } else if (i + 1 == max_slots) {
                                        $button.addClass('bookly-last-child');
                                    }
                                    $column.append($button);
                                }
                                $columnizer.append($column);
                                ++ columns;
                            }
                        }
                        /**
                         * Create screens.
                         */
                        var $columns = $('> .bookly-column', $columnizer);

                        while (has_more_slots ? $columns.length >= columns_per_screen : $columns.length) {
                            $screen = $('<div class="bookly-time-screen"/>');
                            for (var i = 0; i < columns_per_screen; ++i) {
                                $column = $($columns.splice(0, 1));
                                if (i == 0) {
                                    $column.addClass('bookly-js-first-column');
                                    var $first_slot = $column.find('.bookly-js-first-child');
                                    // In the first column the first slot is time.
                                    if (!$first_slot.hasClass('bookly-day')) {
                                        var group = $first_slot.data('group'),
                                            $group_slot = $('button.bookly-day[value="' + group + '"]:last', $container);
                                        // Copy group slot to the first column.
                                        $column.prepend($group_slot.clone());
                                    }
                                }
                                $screen.append($column);
                            }
                            $columnizer.append($screen);
                        }
                        $screens = $('.bookly-time-screen', $columnizer);
                        if ($current_screen === null) {
                            $current_screen = $screens.eq(0);
                        }

                        // On click on a slot.
                        $('button.bookly-hour', $container).off('click').on('click', function (e) {
                            e.preventDefault();
                            var $this = $(this),
                                data = {
                                    action     : 'bookly_session_save',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : Options.form_id,
                                    slots      : this.value
                                };
                            $this.attr({'data-style': 'zoom-in','data-spinner-color':'#333','data-spinner-size':'40'});
                            ladda_start(this);
                            $.ajax({
                                type : 'POST',
                                url  : Options.ajaxurl,
                                data : data,
                                dataType  : 'json',
                                xhrFields : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success : function (response) {
                                    if (!Options.skip_steps.repeat) {
                                        stepRepeat();
                                    } else if (Options.cart.enabled) {
                                        stepCart({add_to_cart : true, from_step : 'time'});
                                    } else {
                                        stepDetails({add_to_cart : true});
                                    }
                                }
                            });
                        });

                        // Columnizer width & height.
                        $('.bookly-time-step', $container).width(columns_per_screen * column_width);
                        $columnizer_wrap.height(form_hidden
                            ? $('.bookly-column.bookly-js-first-column button', $current_screen).length * (slot_height + 3)
                            : $current_screen.height());
                        form_hidden = false;
                    }
                }
            });
        }

        /**
         * Repeat step.
         */
        function stepRepeat(params, error) {
            if (Options.skip_steps.repeat) {
                stepCart(params, error)
            } else {
                var data = $.extend({
                    action     : 'bookly_render_repeat',
                    csrf_token : BooklyL10n.csrf_token,
                    form_id    : Options.form_id
                }, params);
                $.ajax({
                    url         : Options.ajaxurl,
                    data        : data,
                    dataType    : 'json',
                    xhrFields   : { withCredentials: true },
                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                    success     : function (response) {
                        if (response.success) {
                            $container.html(response.html);
                            scrollTo($container);

                            var $repeat_enabled   = $('.bookly-js-repeat-appointment-enabled', $container),
                                $next_step        = $('.bookly-js-next-step', $container),
                                $repeat_container = $('.bookly-js-repeat-variants-container', $container),
                                $variants         = $('[class^="bookly-js-variant"]', $repeat_container),
                                $repeat_variant   = $('.bookly-js-repeat-variant', $repeat_container),
                                $button_get_schedule = $('.bookly-js-get-schedule', $repeat_container),
                                $variant_weekly   = $('.bookly-js-variant-weekly', $repeat_container),
                                $variant_monthly  = $('.bookly-js-repeat-variant-monthly', $repeat_container),
                                $date_until       = $('.bookly-js-repeat-until', $repeat_container),
                                $repeat_times     = $('.bookly-js-repeat-times', $repeat_container),
                                $monthly_specific_day = $('.bookly-js-monthly-specific-day', $repeat_container),
                                $monthly_week_day = $('.bookly-js-monthly-week-day', $repeat_container),
                                $repeat_every_day = $('.bookly-js-repeat-daily-every', $repeat_container),
                                $week_day         = $('.bookly-js-week-day', $repeat_container),
                                $schedule_container = $('.bookly-js-schedule-container', $container),
                                $days_error       = $('.bookly-js-days-error', $repeat_container),
                                $schedule_slots   = $('.bookly-js-schedule-slots',$schedule_container),
                                $intersection_info = $('.bookly-js-intersection-info', $schedule_container),
                                $info_help  = $('.bookly-js-schedule-help', $schedule_container),
                                $info_wells = $('.bookly-well', $schedule_container),
                                $pagination = $('.bookly-pagination', $schedule_container),
                                $schedule_row_template = $('.bookly-schedule-row-template .bookly-schedule-row', $schedule_container),
                                pages_warning_info = response.pages_warning_info,
                                short_date_format = response.short_date_format,
                                bound_date = {min: response.date_min || true, max: response.date_max || true},
                                schedule = []
                            ;
                            var repeat = {
                                prepareButtonNextState : function () {
                                    // Disable/Enable next button
                                    var is_disabled = $next_step.prop('disabled'),
                                        new_prop_disabled = schedule.length == 0;
                                    for (var i = 0; i < schedule.length; i++) {
                                        if (is_disabled) {
                                            if (!schedule[i].deleted) {
                                                new_prop_disabled = false;
                                                break;
                                            }
                                        } else if (schedule[i].deleted) {
                                            new_prop_disabled = true;
                                        } else {
                                            new_prop_disabled = false;
                                            break;
                                        }
                                    }
                                    $next_step.prop('disabled', new_prop_disabled);
                                },
                                addTimeSlotControl : function ($schedule_row, options, prefer_time, current_time ) {
                                    var $time = '';
                                    if(options.length) {
                                        var prefer;
                                        $time = $('<select/>');
                                        $.each(options, function (index, option) {
                                            $time.append($('<option>' + option.title + '</option>').val(option.value));
                                            if (!prefer) {
                                                if (option.title == prefer_time) {
                                                    // Select by time title.
                                                    $time.val(option.value);
                                                    prefer = true;
                                                } else if (option.title == current_time) {
                                                    $time.val(option.value);
                                                }
                                            }
                                        });
                                    }
                                    $schedule_row.find('.bookly-schedule-time').html($time);
                                    $schedule_row.find('div.bookly-label-error').toggle(!options.length);
                                },
                                renderSchedulePage : function (page) {
                                    var $row,
                                        count = schedule.length,
                                        rows_on_page = 5,
                                        start = rows_on_page * page - rows_on_page,
                                        warning_pages = [];
                                    $schedule_slots.html('');
                                    for (var i = start, j = 0; j < rows_on_page && i < count; i++, j++) {
                                        $row = $schedule_row_template.clone();
                                        $row.data('datetime', schedule[i].datetime);
                                        $row.data('index', schedule[i].index);
                                        $('> div:first-child', $row).html(schedule[i].index);
                                        $('.bookly-schedule-date', $row).html(schedule[i].display_date);
                                        $('.bookly-schedule-time', $row).html(schedule[i].display_time);
                                        if (schedule[i].another_time) {
                                            $('.bookly-schedule-intersect', $row).show();
                                        }
                                        if (schedule[i].deleted) {
                                            $row.find('.bookly-schedule-appointment').addClass('bookly-appointment-hidden');
                                        }
                                        $schedule_slots.append($row);
                                    }
                                    if (count > rows_on_page) {
                                        var $btn = $('<li/>').html('«');
                                        $btn.on('click', function () {
                                            var page = parseInt($pagination.find('.active').html());
                                            if (page > 1) {
                                                repeat.renderSchedulePage(page - 1);
                                            }
                                        });
                                        $pagination.html($btn);
                                        for (i = 0, j = 1; i < count; i += 5, j++) {
                                            $btn = $('<li/>').html(j);
                                            $pagination.append($btn);
                                            $btn.on('click', function () {
                                                repeat.renderSchedulePage($(this).html());
                                            });
                                        }
                                        $pagination.find('li:eq(' + page + ')').addClass('active');
                                        $btn = $('<li/>').html('»');
                                        $btn.on('click', function () {
                                            var page = parseInt($pagination.find('.active').html());
                                            if (page < count / rows_on_page) {
                                                repeat.renderSchedulePage(page + 1);
                                            }
                                        });
                                        $pagination.append($btn).show();

                                        for (i = 0; i < count; i++) {
                                            if (schedule[i].another_time) {
                                                page = parseInt(i / rows_on_page) + 1;
                                                warning_pages.push(page);
                                                i = page * rows_on_page - 1;
                                            }
                                        }
                                        if (warning_pages.length > 0) {
                                            $intersection_info.html(pages_warning_info.replace('{list}', warning_pages.join(', ')));
                                        }
                                        $info_wells.toggle(warning_pages.length > 0);
                                        $pagination.toggle(count > rows_on_page);
                                    } else {
                                        $pagination.hide();
                                        $info_wells.hide();
                                        for (i = 0; i < count; i++) {
                                            if (schedule[i].another_time) {
                                                $info_help.show();
                                                break;
                                            }
                                        }
                                    }
                                },
                                renderFullSchedule: function (data) {
                                    schedule = data; // it has global scope
                                    // Prefer time is display time selected on step time.
                                    var prefer_time = null;
                                    $.each(schedule, function (index, item) {
                                        if (!prefer_time && !item.another_time) {
                                            prefer_time = item.display_time;
                                        }
                                    });
                                    repeat.renderSchedulePage(1);
                                    $schedule_container.show();

                                    $next_step.prop('disabled', schedule.length == 0);
                                    $schedule_slots.on('click', 'button[data-action]', function () {
                                        var $schedule_row = $(this).closest('.bookly-schedule-row');
                                        var row_index = $schedule_row.data('index') - 1;
                                        switch ($(this).data('action')) {
                                            case 'drop':
                                                schedule[row_index].deleted = true;
                                                $schedule_row.find('.bookly-schedule-appointment').addClass('bookly-appointment-hidden');
                                                repeat.prepareButtonNextState();
                                                break;
                                            case 'restore':
                                                schedule[row_index].deleted = false;
                                                $schedule_row.find('.bookly-schedule-appointment').removeClass('bookly-appointment-hidden');
                                                $next_step.prop('disabled', false);
                                                break;
                                            case 'edit':
                                                var $date = $('<input type="text"/>'),
                                                    $edit_button = $(this),
                                                    ladda_round = ladda_start(this);
                                                $schedule_row.find('.bookly-schedule-date').html($date);
                                                $date.pickadate({
                                                    min             : bound_date.min,
                                                    max             : bound_date.max,
                                                    formatSubmit    : 'yyyy-mm-dd',
                                                    format          : short_date_format,
                                                    clear           : false,
                                                    close           : false,
                                                    today           : BooklyL10n.today,
                                                    monthsFull      : BooklyL10n.months,
                                                    weekdaysFull    : BooklyL10n.days,
                                                    weekdaysShort   : BooklyL10n.daysShort,
                                                    labelMonthNext  : BooklyL10n.nextMonth,
                                                    labelMonthPrev  : BooklyL10n.prevMonth,
                                                    firstDay        : Options.start_of_week,
                                                    onSet: function() {
                                                        var exclude = [];
                                                        $.each(schedule, function (index, item) {
                                                            if ((row_index != index) && !item.deleted) {
                                                                exclude.push(item.slots);
                                                            }
                                                        });
                                                        $.ajax({
                                                            url : Options.ajaxurl,
                                                            type: 'POST',
                                                            data: {
                                                                action     : 'bookly_recurring_appointments_get_daily_customer_schedule',
                                                                csrf_token : BooklyL10n.csrf_token,
                                                                date       : this.get('select', 'yyyy-mm-dd'),
                                                                form_id    : Options.form_id,
                                                                exclude    : exclude
                                                            },
                                                            dataType: 'json',
                                                            xhrFields: {withCredentials: true},
                                                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                            success: function (response) {
                                                                $edit_button.hide();
                                                                ladda_round.stop();
                                                                if (response.data.length) {
                                                                    repeat.addTimeSlotControl($schedule_row, response.data[0].options, prefer_time, schedule[row_index].display_time);
                                                                    $schedule_row.find('button[data-action="save"]').show();
                                                                } else {
                                                                    repeat.addTimeSlotControl($schedule_row, [] );
                                                                    $schedule_row.find('button[data-action="save"]').hide();
                                                                }
                                                            }
                                                        });
                                                    }
                                                });

                                                var slots = JSON.parse(schedule[row_index].slots);
                                                $date.pickadate('picker').set('select', new Date(slots[0][2]));
                                                break;
                                            case 'save':
                                                $(this).hide();
                                                $schedule_row.find('button[data-action="edit"]').show();
                                                var $date_container = $schedule_row.find('.bookly-schedule-date'),
                                                    $time_container = $schedule_row.find('.bookly-schedule-time'),
                                                    $select = $time_container.find('select'),
                                                    option = $select.find('option:selected');
                                                schedule[row_index].slots = $select.val();
                                                schedule[row_index].display_date = $date_container.find('input').val();
                                                schedule[row_index].display_time = option.text();
                                                $date_container.html(schedule[row_index].display_date);
                                                $time_container.html(schedule[row_index].display_time);
                                                break;
                                        }
                                    });
                                },
                                isDateMatchesSelections: function (current_date) {
                                    switch ($repeat_variant.val()) {
                                        case 'daily':
                                            if (($repeat_every_day.val() > 6 || $.inArray(current_date.format('ddd').toLowerCase(), repeat.week_days) != -1) && (current_date.diff(repeat.date_from, 'days') % $repeat_every_day.val() == 0)) {
                                                return true;
                                            }
                                            break;
                                        case 'weekly':
                                        case 'biweekly':
                                            if (($repeat_variant.val() == 'weekly' || current_date.diff(repeat.date_from.clone().startOf('isoWeek'), 'weeks') % 2 == 0) && ($.inArray(current_date.format('ddd').toLowerCase(), repeat.checked_week_days) != -1)) {
                                                return true;
                                            }
                                            break;
                                        case 'monthly':
                                            switch ($variant_monthly.val()) {
                                                case 'specific':
                                                    if (current_date.format('D') == $monthly_specific_day.val()) {
                                                        return true;
                                                    }
                                                    break;
                                                case 'last':
                                                    if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && current_date.clone().endOf('month').diff(current_date, 'days') < 7) {
                                                        return true;
                                                    }
                                                    break;
                                                default:
                                                    var month_diff = current_date.diff(current_date.clone().startOf('month'), 'days');
                                                    if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && month_diff >= ($variant_monthly.prop('selectedIndex') - 1) * 7 && month_diff < $variant_monthly.prop('selectedIndex') * 7) {
                                                        return true;
                                                    }
                                            }
                                            break;
                                    }

                                    return false;
                                },
                                updateRepeatDate: function () {
                                    var number_of_times = 0,
                                        repeat_times = $repeat_times.val(),
                                        date_from = bound_date.min.slice(),
                                        date_until = $date_until.pickadate('picker').get('select'),
                                        moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date).add(5, 'years');
                                    date_from[1]++;
                                    repeat.date_from = moment(date_from.join(','), 'YYYY,M,D');

                                    repeat.week_days = [];
                                    $monthly_week_day.find('option').each(function () {
                                        repeat.week_days.push($(this).val());
                                    });

                                    repeat.checked_week_days = [];
                                    $week_day.each(function () {
                                        if ($(this).prop('checked')) {
                                            repeat.checked_week_days.push($(this).val());
                                        }
                                    });

                                    var current_date = repeat.date_from.clone();
                                    do {
                                        if (repeat.isDateMatchesSelections(current_date)) {
                                            number_of_times++
                                        }
                                        current_date.add(1, 'days');
                                    } while (number_of_times < repeat_times && current_date.isBefore(moment_until));
                                    $date_until.val(current_date.subtract(1, 'days').format('MMMM D, YYYY'));
                                    $date_until.pickadate('picker').set('select', new Date(current_date.format('YYYY'), current_date.format('M') - 1, current_date.format('D')))
                                },
                                updateRepeatTimes: function () {
                                    var number_of_times = 0,
                                        date_from = bound_date.min.slice(),
                                        date_until = $date_until.pickadate('picker').get('select'),
                                        moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date);

                                    date_from[1]++;
                                    repeat.date_from = moment(date_from.join(','), 'YYYY,M,D');

                                    repeat.week_days = [];
                                    $monthly_week_day.find('option').each(function () {
                                        repeat.week_days.push($(this).val());
                                    });

                                    repeat.checked_week_days = [];
                                    $week_day.each(function () {
                                        if ($(this).prop('checked')) {
                                            repeat.checked_week_days.push($(this).val());
                                        }
                                    });

                                    var current_date = repeat.date_from.clone();
                                    do {
                                        if (repeat.isDateMatchesSelections(current_date)) {
                                            number_of_times++
                                        }
                                        current_date.add(1, 'days');
                                    } while (current_date.isBefore(moment_until));
                                    $repeat_times.val(number_of_times);
                                }
                            };

                            $date_until.pickadate({
                                formatSubmit    : 'yyyy-mm-dd',
                                format          : Options.date_format,
                                min             : bound_date.min,
                                max             : bound_date.max,
                                clear           : false,
                                close           : false,
                                today           : BooklyL10n.today,
                                monthsFull      : BooklyL10n.months,
                                weekdaysFull    : BooklyL10n.days,
                                weekdaysShort   : BooklyL10n.daysShort,
                                labelMonthNext  : BooklyL10n.nextMonth,
                                labelMonthPrev  : BooklyL10n.prevMonth,
                                firstDay        : Options.start_of_week
                            });

                            var open_repeat_onchange = $repeat_enabled.on('change', function () {
                                $repeat_container.toggle($(this).prop('checked'));
                                if ($(this).prop('checked')) {
                                    repeat.prepareButtonNextState();
                                } else {
                                    $next_step.prop('disabled', false);
                                }
                            });
                            if (response.repeated) {
                                var repeat_data = response.repeat_data;
                                var repeat_params = repeat_data.params;

                                $repeat_enabled.prop('checked', true);
                                $repeat_variant.val(repeat_data.repeat);
                                var until = repeat_data.until.split('-');
                                $date_until.pickadate('set').set('select', new Date(until[0], until[1]-1, until[2]));
                                switch (repeat_data.repeat) {
                                    case 'daily':
                                        $repeat_every_day.val(repeat_params.every);
                                        break;
                                    case 'weekly':
                                        //break skipped
                                    case 'biweekly':
                                        $('.bookly-js-week-days input.bookly-js-week-day', $repeat_container)
                                            .prop('checked', false)
                                            .parent()
                                            .removeClass('active');
                                        repeat_params.on.forEach(function(val) {
                                            $('.bookly-js-week-days input.bookly-js-week-day[value='+val+']', $repeat_container)
                                                .prop('checked', true)
                                                .parent()
                                                .addClass('active');
                                        });
                                        break;
                                    case 'monthly':
                                        if (repeat_params.on === 'day') {
                                            $variant_monthly.val('specific');
                                            $('.bookly-js-monthly-specific-day[value='+repeat_params.day+']', $repeat_container).prop('checked', true);
                                        } else {
                                            $variant_monthly.val(repeat_params.on);
                                            $monthly_week_day.val(repeat_params.weekday);
                                        }
                                        break;
                                }
                                repeat.renderFullSchedule(response.schedule);
                            }
                            open_repeat_onchange.trigger('change');

                            if (!response.could_be_repeated) {
                                $repeat_enabled.attr('disabled', true);
                            }

                            $repeat_variant.on('change', function () {
                                $variants.hide();
                                $repeat_container.find('.bookly-js-variant-' + this.value).show();
                                repeat.updateRepeatTimes();
                            }).trigger('change');

                            $variant_monthly.on('change', function () {
                                $monthly_week_day.toggle(this.value != 'specific');
                                $monthly_specific_day.toggle(this.value == 'specific');
                                repeat.updateRepeatTimes();
                            }).trigger('change');

                            $week_day.on('change', function () {
                                var $this = $(this);
                                if ($this.is(':checked')) {
                                    $this.parent().not("[class*='active']").addClass('active');
                                } else {
                                    $this.parent().removeClass('active');
                                }
                                repeat.updateRepeatTimes();
                            });

                            $monthly_specific_day.val(response.date_min[2]);

                            $monthly_specific_day.on('change', function () {
                                repeat.updateRepeatTimes();
                            });

                            $monthly_week_day.on('change', function () {
                                repeat.updateRepeatTimes();
                            });

                            $date_until.on('change', function () {
                                repeat.updateRepeatTimes();
                            });

                            $repeat_every_day.on('change', function () {
                                repeat.updateRepeatTimes();
                            });

                            $repeat_times.on('change', function () {
                                repeat.updateRepeatDate();
                            });

                            $button_get_schedule.on('click', function () {
                                $schedule_container.hide();
                                var data = {
                                        action     : 'bookly_recurring_appointments_get_customer_schedule',
                                        csrf_token : BooklyL10n.csrf_token,
                                        form_id    : Options.form_id,
                                        repeat     : $repeat_variant.val(),
                                        until      : $date_until.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                        params     : {}
                                    },
                                    ladda = ladda_start(this);

                                switch (data.repeat) {
                                    case 'daily':
                                        data.params = {every: $repeat_every_day.val()};
                                        break;
                                    case 'weekly':
                                    case 'biweekly':
                                        data.params.on = [];
                                        $('.bookly-js-week-days input.bookly-js-week-day:checked', $variant_weekly).each(function () {
                                            data.params.on.push(this.value);
                                        });
                                        if (data.params.on.length == 0) {
                                            $days_error.toggle(true);
                                            ladda.stop();
                                            return false;
                                        } else {
                                            $days_error.toggle(false);
                                        }
                                        break;
                                    case 'monthly':
                                        if ($variant_monthly.val() == 'specific') {
                                            data.params = {on: 'day', day: $monthly_specific_day.val()};
                                        } else {
                                            data.params = {on: $variant_monthly.val(), weekday: $monthly_week_day.val()};
                                        }
                                        break;
                                }
                                $schedule_slots.off('click');
                                $.ajax({
                                    url : Options.ajaxurl,
                                    type: 'POST',
                                    data: data,
                                    dataType: 'json',
                                    xhrFields: {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success: function (response) {
                                        if (response.success) {
                                            repeat.renderFullSchedule(response.data);
                                            ladda.stop();
                                        }
                                    }
                                });
                            });

                            $('.bookly-js-back-step', $container).on('click', function (e) {
                                e.preventDefault();
                                ladda_start(this);
                                $.ajax({
                                    type: 'POST',
                                    url: Options.ajaxurl,
                                    data: {
                                        action: 'bookly_session_save',
                                        csrf_token: BooklyL10n.csrf_token,
                                        form_id: Options.form_id,
                                        unrepeat: 1
                                    },
                                    dataType: 'json',
                                    xhrFields: {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success: function (response) {
                                        stepTime();
                                    }
                                });
                            });

                            $('.bookly-js-go-to-cart', $container).on('click', function(e) {
                                e.preventDefault();
                                ladda_start(this);
                                stepCart({from_step : 'repeat'});
                            });

                            $('.bookly-js-next-step', $container).on('click', function (e) {
                                ladda_start(this);
                                if ($repeat_enabled.is(':checked')) {
                                    var slots_to_send = [];
                                    var repeat = 0;
                                    schedule.forEach(function (item) {
                                        if (!item.deleted) {
                                            var slots = JSON.parse(item.slots);
                                            slots_to_send = slots_to_send.concat(slots);
                                            repeat++;
                                        }
                                    });
                                    $.ajax({
                                        type: 'POST',
                                        url: Options.ajaxurl,
                                        data: {
                                            action: 'bookly_session_save',
                                            csrf_token: BooklyL10n.csrf_token,
                                            form_id: Options.form_id,
                                            slots: JSON.stringify(slots_to_send),
                                            repeat: repeat
                                        },
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            stepCart({add_to_cart : true, from_step : 'repeat'});
                                        }
                                    });
                                } else {
                                    $.ajax({
                                        type: 'POST',
                                        url: Options.ajaxurl,
                                        data: {
                                            action: 'bookly_session_save',
                                            csrf_token: BooklyL10n.csrf_token,
                                            form_id: Options.form_id,
                                            unrepeat: 1
                                        },
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            stepCart({add_to_cart: true, from_step : 'repeat'});
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            }
        }

        /**
         * Cart step.
         */
        function stepCart(params, error) {
            if (!Options.cart.enabled) {
                stepDetails(params);
            } else {
                if (params && params.from_step) {
                    // Record previous step if it was given in params.
                    Options.cart.prev_step = params.from_step;
                }
                var data = $.extend({
                    action     : 'bookly_render_cart',
                    csrf_token : BooklyL10n.csrf_token,
                    form_id    : Options.form_id
                }, params);
                $.ajax({
                    url: Options.ajaxurl,
                    data: data,
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            $container.html(response.html);
                            if (error){
                                $('.bookly-holder.bookly-label-error', $container).html(error.message);
                                $('tr[data-cart-key="'+ error.failed_key +'"]', $container).addClass('bookly-label-error');
                            } else {
                                $('.bookly-holder.bookly-label-error', $container).hide();
                            }
                            scrollTo($container);
                            $('.bookly-js-next-step', $container).on('click', function () {
                                ladda_start(this);
                                stepDetails();
                            });
                            $('.bookly-add-item', $container).on('click', function () {
                                ladda_start(this);
                                stepService({new_chain : true});
                            });
                            // 'BACK' button.
                            $('.bookly-js-back-step', $container).on('click', function (e) {
                                e.preventDefault();
                                ladda_start(this);
                                switch (Options.cart.prev_step) {
                                    case 'service': stepService(); break;
                                    case 'extras':  stepExtras();  break;
                                    case 'time':    stepTime();    break;
                                    case 'repeat':  stepRepeat();  break;
                                    default:        stepService();
                                }
                            });
                            $('.bookly-js-actions button', $container).on('click', function () {
                                ladda_start(this);
                                var $this = $(this),
                                    $cart_item = $this.closest('tr');
                                switch ($this.data('action')) {
                                    case 'drop':
                                        $.ajax({
                                            url: Options.ajaxurl,
                                            data: {
                                                action     : 'bookly_cart_drop_item',
                                                csrf_token : BooklyL10n.csrf_token,
                                                form_id    : Options.form_id,
                                                cart_key   : $cart_item.data('cart-key')
                                            },
                                            dataType: 'json',
                                            xhrFields: {withCredentials: true},
                                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                            success: function (response) {
                                                if (response.success) {
                                                    var remove_cart_key = $cart_item.data('cart-key'),
                                                        $trs_to_remove  = $('tr[data-cart-key="'+remove_cart_key+'"]', $container)
                                                    ;
                                                    $cart_item.delay(300).fadeOut(200, function () {
                                                        $('.bookly-js-total-price', $container).html(response.data.total_price);
                                                        $('.bookly-js-total-deposit-price', $container).html(response.data.total_deposit_price);
                                                        $trs_to_remove.remove();
                                                        if ($('tr[data-cart-key]').length == 0) {
                                                            $('.bookly-js-back-step', $container).hide();
                                                            $('.bookly-js-next-step', $container).hide();
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                        break;
                                    case 'edit':
                                        stepService({edit_cart_item : $cart_item.data('cart-key')});
                                        break;
                                }
                            });
                        }
                    }
                });
            }
        }

        /**
         * Details step.
         */
        function stepDetails(params) {
            var data = $.extend({
                action     : 'bookly_render_details',
                csrf_token : BooklyL10n.csrf_token,
                form_id    : Options.form_id
            }, params);
            $.ajax({
                url         : Options.ajaxurl,
                data        : data,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        scrollTo($container);
                        // Init.
                        var phone_number        = '',
                            $guest_info         = $('.bookly-js-guest',            $container),
                            $phone_field        = $('.bookly-js-user-phone-input', $container),
                            $email_field        = $('.bookly-js-user-email',       $container),
                            $full_name_field    = $('.bookly-js-full-name',        $container),
                            $first_name_field   = $('.bookly-js-first-name',       $container),
                            $last_name_field    = $('.bookly-js-last-name',        $container),
                            $notes_field        = $('.bookly-js-user-notes',       $container ),
                            $phone_error        = $('.bookly-js-user-phone-error', $container),
                            $email_error        = $('.bookly-js-user-email-error', $container),
                            $name_error         = $('.bookly-js-full-name-error',  $container),
                            $first_name_error   = $('.bookly-js-first-name-error', $container),
                            $last_name_error    = $('.bookly-js-last-name-error',  $container),
                            $captcha            = $('.bookly-js-captcha-img',      $container),
                            $errors             = $('.bookly-js-full-name-error, .bookly-js-first-name-error, .bookly-js-last-name-error, .bookly-js-user-phone-error, .bookly-js-user-email-error, .bookly-custom-field-error', $container),
                            $fields             = $('.bookly-js-full-name, .bookly-js-first-name, .bookly-js-last-name, .bookly-js-user-phone-input, .bookly-js-user-email, .bookly-custom-field', $container),
                            $modals             = $('.bookly-js-modal',            $container),
                            $login_modal        = $('.bookly-js-login',            $container),
                            $cst_modal          = $('.bookly-js-cst-duplicate',    $container),
                            $next_btn           = $('.bookly-js-next-step',        $container)
                        ;
                        if (Options.intlTelInput.enabled) {
                            $phone_field.intlTelInput({
                                preferredCountries: [Options.intlTelInput.country],
                                initialCountry: Options.intlTelInput.country,
                                geoIpLookup: function (callback) {
                                    $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                                        var countryCode = (resp && resp.country) ? resp.country : '';
                                        callback(countryCode);
                                    });
                                },
                                utilsScript: Options.intlTelInput.utils
                            });
                        }
                        // Init modals.
                        $('body > .bookly-js-modal.' + Options.form_id).remove();
                        $modals
                            .addClass(Options.form_id).appendTo('body')
                            .on('click', '.bookly-js-close', function (e) {
                                e.preventDefault();
                                $(e.delegateTarget).removeClass('bookly-in')
                                    .find('form').trigger('reset').end()
                                    .find('input').removeClass('bookly-error').end()
                                    .find('.bookly-label-error').html('')
                                ;
                            })
                        ;
                        // Login modal.
                        $('.bookly-js-login-show', $container).on('click', function(e) {
                            e.preventDefault();
                            $login_modal.addClass('bookly-in');
                        });
                        $('button:submit', $login_modal).on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : {
                                    action     : 'bookly_wp_user_login',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : Options.form_id,
                                    log        : $login_modal.find('[name="log"]').val(),
                                    pwd        : $login_modal.find('[name="pwd"]').val(),
                                    rememberme : $login_modal.find('[name="rememberme"]').prop('checked') ? 1 : 0
                                },
                                dataType    : 'json',
                                xhrFields   : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (response.success) {
                                        BooklyL10n.csrf_token = response.data.csrf_token;
                                        $guest_info.fadeOut('slow');
                                        $full_name_field.val(response.data.full_name).removeClass('bookly-error');
                                        $first_name_field.val(response.data.first_name).removeClass('bookly-error');
                                        $last_name_field.val(response.data.last_name).removeClass('bookly-error');
                                        if (response.data.phone) {
                                            $phone_field.removeClass('bookly-error');
                                            if (Options.intlTelInput.enabled) {
                                                $phone_field.intlTelInput('setNumber', response.data.phone);
                                            } else {
                                                $phone_field.val(response.data.phone);
                                            }
                                        }
                                        $email_field.val(response.data.email).removeClass('bookly-error');
                                        $errors.filter(':not(.bookly-custom-field-error)').html('');
                                        $login_modal.removeClass('bookly-in');
                                    } else if (response.error_code == 8) {
                                        $login_modal.find('input').addClass('bookly-error');
                                        $login_modal.find('.bookly-label-error').html(response.error);
                                    }
                                    ladda.stop();
                                }
                            })
                        });
                        // Customer duplicate modal.
                        $('button:submit', $cst_modal).on('click', function (e) {
                            e.preventDefault();
                            $cst_modal.removeClass('bookly-in');
                            $next_btn.trigger('click', [1]);
                        });

                        $next_btn.on('click', function(e, force_update_customer) {
                            e.preventDefault();
                            var custom_fields = [],
                                checkbox_values,
                                captcha_ids = [],
                                ladda = ladda_start(this)
                            ;
                            $('.bookly-custom-fields-container', $container).each(function () {
                                var $cf_container = $(this),
                                    key = $cf_container.data('key'),
                                    custom_fields_data = [];
                                $('div.bookly-custom-field-row', $cf_container).each(function() {
                                    var $this = $(this);
                                    switch ($this.data('type')) {
                                        case 'text-field':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('input.bookly-custom-field').val()
                                            });
                                            break;
                                        case 'textarea':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('textarea.bookly-custom-field').val()
                                            });
                                            break;
                                        case 'checkboxes':
                                            checkbox_values = [];
                                            $this.find('input.bookly-custom-field:checked').each(function () {
                                                checkbox_values.push(this.value);
                                            });
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : checkbox_values
                                            });
                                            break;
                                        case 'radio-buttons':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('input.bookly-custom-field:checked').val() || null
                                            });
                                            break;
                                        case 'drop-down':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('select.bookly-custom-field').val()
                                            });
                                            break;
                                        case 'captcha':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('input.bookly-custom-field').val()
                                            });
                                            captcha_ids.push($this.data('id'));
                                            break;
                                    }
                                });
                                custom_fields[key] = {custom_fields: JSON.stringify(custom_fields_data)};
                            });

                            try {
                                phone_number = Options.intlTelInput.enabled ? $phone_field.intlTelInput('getNumber') : $phone_field.val();
                                if (phone_number == '') {
                                    phone_number = $phone_field.val();
                                }
                            } catch (error) {  // In case when intlTelInput can't return phone number.
                                phone_number = $phone_field.val();
                            }
                            var data = {
                                action                  : 'bookly_session_save',
                                csrf_token              : BooklyL10n.csrf_token,
                                form_id                 : Options.form_id,
                                full_name               : $full_name_field.val(),
                                first_name              : $first_name_field.val(),
                                last_name               : $last_name_field.val(),
                                phone                   : phone_number,
                                email                   : $email_field.val(),
                                notes                   : $notes_field.val(),
                                cart                    : custom_fields,
                                captcha_ids             : JSON.stringify(captcha_ids),
                                force_update_customer   : !Options.update_details_dialog || force_update_customer
                            };
                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : data,
                                dataType    : 'json',
                                xhrFields   : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    // Error messages
                                    $errors.empty();
                                    $fields.removeClass('bookly-error');

                                    if (response.success) {
                                        if (Options.woocommerce.enabled) {
                                            var data = {
                                                action     : 'bookly_add_to_woocommerce_cart',
                                                csrf_token : BooklyL10n.csrf_token,
                                                form_id    : Options.form_id
                                            };
                                            $.ajax({
                                                type        : 'POST',
                                                url         : Options.ajaxurl,
                                                data        : data,
                                                dataType    : 'json',
                                                xhrFields   : { withCredentials: true },
                                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                                success     : function (response) {
                                                    if (response.success) {
                                                        window.location.href = Options.woocommerce.cart_url;
                                                    } else {
                                                        ladda.stop();
                                                        stepTime(undefined, response.error);
                                                    }
                                                }
                                            });
                                        } else {
                                            stepPayment();
                                        }
                                    } else {
                                        var $scroll_to = null;
                                        if (response.appointments_limit_reached) {
                                            stepComplete({error: 'appointments_limit_reached'});
                                        } else {
                                            ladda.stop();
                                            if (response.full_name) {
                                                $name_error.html(response.full_name);
                                                $full_name_field.addClass('bookly-error');
                                                $scroll_to = $full_name_field;
                                            }
                                            if (response.first_name) {
                                                $first_name_error.html(response.first_name);
                                                $first_name_field.addClass('bookly-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $first_name_field;
                                                }
                                            }
                                            if (response.last_name) {
                                                $last_name_error.html(response.last_name);
                                                $last_name_field.addClass('bookly-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $last_name_field;
                                                }
                                            }
                                            if (response.phone) {
                                                $phone_error.html(response.phone);
                                                $phone_field.addClass('bookly-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $phone_field;
                                                }
                                            }
                                            if (response.email) {
                                                $email_error.html(response.email);
                                                $email_field.addClass('bookly-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $email_field;
                                                }
                                            }
                                            if (response.custom_fields) {
                                                $.each(response.custom_fields, function (key, fields) {
                                                    $.each(fields, function (field_id, message) {
                                                        var $custom_fields_collector = $('.bookly-custom-fields-container[data-key="' + key + '"]', $container);
                                                        var $div = $('[data-id="' + field_id + '"]', $custom_fields_collector);
                                                        $div.find('.bookly-custom-field-error').html(message);
                                                        $div.find('.bookly-custom-field').addClass('bookly-error');
                                                        if ($scroll_to === null) {
                                                            $scroll_to = $div.find('.bookly-custom-field');
                                                        }
                                                    });
                                                });
                                            }
                                            if (response.customer) {
                                                $cst_modal
                                                    .find('.bookly-js-modal-body').html(response.customer).end()
                                                    .addClass('bookly-in')
                                                ;
                                            }
                                        }
                                        if ($scroll_to !== null) {
                                            scrollTo($scroll_to);
                                        }
                                    }
                                }
                            });
                        });

                        $('.bookly-js-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            if (Options.cart.enabled) {
                                stepCart();
                            } else if (!Options.skip_steps.repeat) {
                                stepRepeat();
                            } else {
                                stepTime();
                            }
                        });

                        $('.bookly-js-captcha-refresh',  $container).on('click', function() {
                            $captcha.css('opacity','0.5');
                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : {action: 'bookly_custom_fields_captcha_refresh', form_id: Options.form_id, csrf_token : BooklyL10n.csrf_token},
                                dataType    : 'json',
                                xhrFields   : {withCredentials: true},
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    if (response.success) {
                                        $captcha.attr('src', response.data.captcha_url).on('load', function() {
                                            $captcha.css('opacity', '1');
                                        });
                                    }
                                }
                            });
                        });
                    }
                }
            });
        }

        /**
         * Payment step.
         */
        function stepPayment() {
            $.ajax({
                type       : 'POST',
                url        : Options.ajaxurl,
                data       : {action: 'bookly_render_payment', csrf_token : BooklyL10n.csrf_token, form_id: Options.form_id, page_url: document.URL.split('#')[0]},
                dataType   : 'json',
                xhrFields  : {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success    : function (response) {
                    if (response.success) {
                        // If payment step is disabled.
                        if (response.disabled) {
                            save();
                            return;
                        }

                        $container.html(response.html);
                        scrollTo($container);
                        if (Options.status.booking == 'cancelled') {
                            Options.status.booking = 'ok';
                        }

                        var $payments  = $('.bookly-payment', $container),
                            $apply_coupon_button = $('.bookly-js-apply-coupon', $container),
                            $coupon_input = $('input.bookly-user-coupon', $container),
                            $coupon_error = $('.bookly-js-coupon-error', $container),
                            $coupon_info_text = $('.bookly-info-text-coupon', $container),
                            $bookly_payment_nav = $('.bookly-payment-nav', $container),
                            $buttons = $('.bookly-gateway-buttons,form.bookly-authorize-net,form.bookly-stripe', $container)
                        ;
                        $payments.on('click', function() {
                            $buttons.hide();
                            $('.bookly-gateway-buttons.pay-' + $(this).val(), $container).show();
                            if ($(this).val() == 'card') {
                                $('form.bookly-' + $(this).data('form'), $container).show();
                            }
                        });
                        $payments.eq(0).trigger('click');

                        $apply_coupon_button.on('click', function (e) {
                            var ladda = ladda_start(this);
                            $coupon_error.text('');
                            $coupon_input.removeClass('bookly-error');

                            var data = {
                                action      : 'bookly_coupons_apply_coupon',
                                csrf_token  : BooklyL10n.csrf_token,
                                form_id     : Options.form_id,
                                coupon_code : $coupon_input.val()
                            };

                            $.ajax({
                                type        : 'POST',
                                url         : Options.ajaxurl,
                                data        : data,
                                dataType    : 'json',
                                xhrFields   : {withCredentials: true},
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    if (response.success) {
                                        $coupon_info_text.html(response.text);
                                        $coupon_input.replaceWith(data.coupon_code);
                                        $apply_coupon_button.replaceWith('✓');
                                        if (response.total <= 0) {
                                            $bookly_payment_nav.hide();
                                            $buttons.hide();
                                            $('.bookly-gateway-buttons.pay-coupon', $container).show();
                                            $('.bookly-coupon-free', $container).attr('checked', 'checked').val(data.coupon);
                                        } else {
                                            // Set new price for payment request
                                            $('input.bookly-payment-amount', $container).val(response.total);
                                            var $payu_latam_form = $('.bookly-payu_latam-form', $container);
                                            if ($payu_latam_form.length) {
                                                $.post(Options.ajaxurl, {action: 'bookly_payu_latam_refresh_tokens', csrf_token : BooklyL10n.csrf_token, form_id: Options.form_id})
                                                    .done(function(response){
                                                        if (response.success) {
                                                            $payu_latam_form.find('input[name=referenceCode]').val(response.data.referenceCode);
                                                            $payu_latam_form.find('input[name=signature]').val(response.data.signature);
                                                        }
                                                    }, 'json' );
                                            }
                                        }
                                    } else if (response.error_code == 6) {
                                        $coupon_error.html(response.error);
                                        $coupon_input.addClass('bookly-error');
                                        $coupon_info_text.html(response.text);
                                        scrollTo($coupon_error);
                                    }
                                    ladda.stop();
                                },
                                error : function () {
                                    ladda.stop();
                                }
                            });
                        });

                        $('.bookly-js-next-step', $container).on('click', function (e) {
                            var ladda = ladda_start(this),
                                $form
                            ;
                            if ($('.bookly-payment[value=local]', $container).is(':checked') || $(this).hasClass('bookly-js-coupon-payment')) {
                                // handle only if was selected local payment !
                                e.preventDefault();
                                save();

                            } else if ($('.bookly-payment[value=card]', $container).is(':checked')) {
                                var stripe = $('.bookly-payment[data-form=stripe]', $container).is(':checked');
                                var card_action = stripe ? 'bookly_stripe_payment' : 'bookly_authorize_net_aim_payment';
                                $form = $container.find(stripe ? '.bookly-stripe' : '.bookly-authorize-net');
                                e.preventDefault();

                                var data = {
                                    action: card_action,
                                    csrf_token: BooklyL10n.csrf_token,
                                    card: {
                                        number   : $form.find('input[name="card_number"]').val(),
                                        cvc      : $form.find('input[name="card_cvc"]').val(),
                                        exp_month: $form.find('select[name="card_exp_month"]').val(),
                                        exp_year : $form.find('select[name="card_exp_year"]').val()
                                    },
                                    form_id: Options.form_id
                                };

                                var card_payment = function (data) {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        data       : data,
                                        dataType   : 'json',
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success    : function (response) {
                                            if (response.success) {
                                                stepComplete();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            } else if (response.error_code == 7) {
                                                ladda.stop();
                                                $form.find('.bookly-js-card-error').text(response.error);
                                            }
                                        }
                                    });
                                };
                                if (stripe && $form.find('#publishable_key').val()) {
                                    try {
                                        Stripe.setPublishableKey($form.find('#publishable_key').val());
                                        Stripe.createToken(data.card, function (status, response) {
                                            if (response.error) {
                                                $form.find('.bookly-js-card-error').text(response.error.message);
                                                ladda.stop();
                                            } else {
                                                // Token from stripe.js
                                                data['card'] = response['id'];
                                                card_payment(data);
                                            }
                                        });
                                    } catch (e) {
                                        $form.find('.bookly-js-card-error').text(e.message);
                                        ladda.stop();
                                    }
                                } else {
                                    card_payment(data);
                                }
                            } else if (    $('.bookly-payment[value=paypal]',     $container).is(':checked')
                                        || $('.bookly-payment[value=2checkout]',  $container).is(':checked')
                                        || $('.bookly-payment[value=payu_latam]', $container).is(':checked')
                                        || $('.bookly-payment[value=payson]',     $container).is(':checked')
                                        || $('.bookly-payment[value=mollie]',     $container).is(':checked')
                            ) {
                                e.preventDefault();
                                $form = $(this).closest('form');
                                if ($form.find('input.bookly-payment-id').length > 0 ) {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        data       : {
                                            action:       'bookly_save_pending_appointment',
                                            csrf_token:   BooklyL10n.csrf_token,
                                            form_id:      Options.form_id,
                                            payment_type: $form.data('gateway')
                                        },
                                        dataType   : 'json',
                                        success    : function (response) {
                                            if (response.success) {
                                                $form.find('input.bookly-payment-id').val(response.payment_id);
                                                $form.submit();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            }
                                        }
                                    });
                                } else  {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : Options.ajaxurl,
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        data       : {action: 'bookly_check_cart', csrf_token : BooklyL10n.csrf_token, form_id: Options.form_id},
                                        dataType   : 'json',
                                        success    : function (response) {
                                            if (response.success) {
                                                $form.submit();
                                            } else if (response.error_code == 3) {
                                                handle_error_3(response);
                                            }
                                        }
                                    });
                                }
                            }
                        });

                        $('.bookly-js-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            ladda_start(this);
                            stepDetails();
                        });
                    }
                }
            });
        }

        /**
         * Complete step.
         */
        function stepComplete(params) {
            var data = $.extend({
                action     : 'bookly_render_complete',
                csrf_token : BooklyL10n.csrf_token,
                form_id    : Options.form_id
            }, params);
            if (Options.final_step_url && !data.error) {
                document.location.href = Options.final_step_url;
            } else {
                $.ajax({
                    url: Options.ajaxurl,
                    data: data,
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            $container.html(response.html);
                            scrollTo($container);
                        }
                    }
                });
            }
        }

        // =========== helpers ===================

        //
        function save() {
            $.ajax({
                type        : 'POST',
                url         : Options.ajaxurl,
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                data        : { action : 'bookly_save_appointment', csrf_token : BooklyL10n.csrf_token, form_id : Options.form_id },
                dataType    : 'json'
            }).done(function(response) {
                if (response.success) {
                    stepComplete();
                } else if (response.error_code == 3) {
                    handle_error_3(response);
                }
            });
        }

        function ladda_start(elem) {
            var ladda = Ladda.create(elem);
            ladda.start();
            return ladda;
        }

        /**
         * Handle error with code 3 which means one of the cart item is not available anymore.
         *
         * @param response
         */
        function handle_error_3(response) {
            if (Options.cart.enabled) {
                stepCart(undefined, {
                    failed_key : response.failed_cart_key,
                    message    : response.error
                });
            } else {
                stepTime(undefined, response.error);
            }
        }

        /**
         * Scroll to element if it is not visible.
         *
         * @param $elem
         */
        function scrollTo($elem) {
            var elemTop   = $elem.offset().top;
            var scrollTop = $(window).scrollTop();
            if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
                $('html,body').animate({ scrollTop: (elemTop - 24) }, 500);
            }
        }

    };

})(jQuery);
