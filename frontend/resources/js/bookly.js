(function($) {
    window.bookly = function(Options) {
        var $container = $('#bookly-form-' + Options.form_id),
            time_zone_offset = (new Date).getTimezoneOffset();
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
                form_id          : Options.form_id,
                time_zone_offset : time_zone_offset
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
                        if (params === undefined) { // Scroll when returning to the step Service. default value {new_chain : true}
                            scrollTo($container);
                        }

                        var $chain_item_draft = $('.bookly-js-chain-item.bookly-js-draft', $container),
                            $select_location  = $('.ab-select-location', $container),
                            $select_category  = $('.ab-select-category', $container),
                            $select_service   = $('.ab-select-service',  $container),
                            $select_employee  = $('.ab-select-employee', $container),
                            $select_nop       = $('.ab-select-number-of-persons', $container),
                            $select_quantity  = $('.ab-select-quantity', $container),
                            $date_from        = $('.ab-date-from', $container),
                            $week_day         = $('.bookly-week-day', $container),
                            $select_time_from = $('.ab-select-time-from', $container),
                            $select_time_to   = $('.ab-select-time-to', $container),
                            $next_step        = $('.bookly-next-step', $container),
                            $mobile_next_step = $('.ab-mobile-next-step', $container),
                            $mobile_prev_step = $('.ab-mobile-prev-step', $container),
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
                                    $('.bookly-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container).attr('checked', true).trigger('change');
                                }
                            }
                        });

                        $('.bookly-go-to-cart', $container).on('click', function (e) {
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
                            var nop = $chain_item.find('.ab-select-number-of-persons').val();
                            var max_capacity = service_id
                                ? (staff_id
                                    ? staff[staff_id].services[service_id].capacity
                                    : services[service_id].max_capacity)
                                : 1;
                            for (var i = 1; i <= max_capacity; ++ i) {
                                _nop[i] = { id: i, name: i, pos: i };
                            }
                            if (nop > max_capacity) {
                                nop = max_capacity;
                            }
                            setSelect($chain_item.find('.ab-select-category'), _categories, category_id);
                            setSelect($chain_item.find('.ab-select-service'), _services, service_id);
                            setSelect($chain_item.find('.ab-select-employee'), _staff, staff_id);
                            setSelect($chain_item.find('.ab-select-number-of-persons'), _nop, nop);
                        }

                        $container.off('click').off('change');

                        // Location select change
                        $container.on('change', '.ab-select-location', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = this.value,
                                category_id = $chain_item.find('.ab-select-category').val(),
                                service_id  = $chain_item.find('.ab-select-service').val(),
                                staff_id    = $chain_item.find('.ab-select-employee').val()
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
                        $container.on('change', '.ab-select-category', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.ab-select-location').val(),
                                category_id = this.value,
                                service_id  = $chain_item.find('.ab-select-service').val(),
                                staff_id    = $chain_item.find('.ab-select-employee').val()
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
                        $container.on('change', '.ab-select-service', function () {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.ab-select-location').val(),
                                category_id = category_selected
                                    ? $chain_item.find('.ab-select-category').val()
                                    : '',
                                service_id  = this.value,
                                staff_id    = $chain_item.find('.ab-select-employee').val()
                                ;

                            // Validate selected values.
                            if (service_id) {
                                if (staff_id && !staff[staff_id].services.hasOwnProperty(service_id)) {
                                    staff_id = '';
                                }
                            }
                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                            if (service_id) {
                                $chain_item.find('.ab-select-category').val(services[service_id].category_id);
                            }
                        });

                        // Staff select change
                        $container.on('change', '.ab-select-employee', function() {
                            var $chain_item = $(this).closest('.bookly-js-chain-item'),
                                location_id = $chain_item.find('.ab-select-location').val(),
                                category_id = $('.ab-select-category', $chain_item).val(),
                                service_id  = $chain_item.find('.ab-select-service').val(),
                                staff_id    = this.value
                                ;

                            setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        });

                        // Set up draft selects.
                        setSelect($select_location, locations);
                        setSelect($select_category, categories);
                        setSelect($select_service,  services);
                        setSelect($select_employee, staff);
                        $select_location.closest('.ab-formGroup').toggle(!Options.attributes.hide_locations);
                        $select_category.closest('.ab-formGroup').toggle(!Options.attributes.hide_categories);
                        $select_service.closest('.ab-formGroup').toggle(!(Options.attributes.hide_services && Options.attributes.service_id));
                        $select_employee.closest('.ab-formGroup').toggle(!Options.attributes.hide_staff_members);
                        $select_nop.closest('.ab-formGroup').toggle(Options.attributes.show_number_of_persons);
                        $select_quantity.closest('.ab-formGroup').toggle(!Options.attributes.hide_quantity);
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
                            $('.ab-available-date', $container).hide();
                        }
                        if (Options.attributes.hide_week_days) {
                            $('.bookly-week-days', $container).hide();
                        }
                        if (Options.attributes.hide_time_range) {
                            $('.ab-time-range', $container).hide();
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
                            if (chain_item.location_id) {
                                $('.ab-select-location', $chain_item).val(chain_item.location_id).trigger('change');
                            }
                            if (chain_item.service_id) {
                                $('.ab-select-service', $chain_item).val(chain_item.service_id).trigger('change');
                            }
                            if (chain_item.staff_ids.length == 1 && chain_item.staff_ids[0]) {
                                $('.ab-select-employee', $chain_item).val(chain_item.staff_ids[0]).trigger('change');
                            }
                            if (chain_item.number_of_persons > 1) {
                                $('.ab-select-number-of-persons', $chain_item).val(chain_item.number_of_persons);
                            }
                            if (chain_item.quantity > 1) {
                                $('.ab-select-quantity', $chain_item).val(chain_item.quantity);
                            }
                        });

                        $container.on('click', '.ab-mobile-step_1 .bookly-js-actions button', function () {
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
                            $('.ab-select-service-error',  $container).hide();
                            $('.ab-select-employee-error', $container).hide();
                            $('.ab-select-location-error', $container).hide();

                            var valid            = true,
                                $select_service  = null,
                                $select_employee = null,
                                $select_location = null,
                                $scroll_to       = null;

                            $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                                var $chain = $(this);
                                $select_service  = $('.ab-select-service',  $chain);
                                $select_employee = $('.ab-select-employee', $chain);
                                $select_location = $('.ab-select-location', $chain);

                                $select_service.removeClass('ab-error');
                                $select_employee.removeClass('ab-error');
                                $select_location.removeClass('ab-error');

                                // service validation
                                if (!$select_service.val()) {
                                    valid = false;
                                    $select_service.addClass('ab-error');
                                    $('.ab-select-service-error', $chain).show();
                                    $scroll_to = $select_service;
                                }
                                if (Options.required.hasOwnProperty('location') && Options.required.location && !$select_location.val()) {
                                    valid = false;
                                    $select_location.addClass('ab-error');
                                    $('.ab-select-location-error', $chain).show();
                                    $scroll_to = $select_location;
                                }
                                if (Options.required.staff && !$select_employee.val()) {
                                    valid = false;
                                    $select_employee.addClass('ab-error');
                                    $('.ab-select-employee-error', $chain).show();
                                    $scroll_to = $select_employee;
                                }
                            });

                            $date_from.removeClass('ab-error');
                            // date validation
                            if (!$date_from.val()) {
                                valid = false;
                                $date_from.addClass('ab-error');
                                if ($scroll_to === null) {
                                    $scroll_to = $date_from;
                                }
                            }

                            // week days
                            if (!$('.bookly-week-day:checked', $container).length) {
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
                                $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                                    var $chain_item = $(this);
                                    var staff_ids = [];
                                    if ($('.ab-select-employee', $chain_item).val()) {
                                        staff_ids.push($('.ab-select-employee', $chain_item).val());
                                    } else {
                                        $('.ab-select-employee', $chain_item).find('option').each(function () {
                                            if (this.value) {
                                                staff_ids.push(this.value);
                                            }
                                        });
                                    }
                                    chain[$chain_item.data('chain_key')] = {
                                        location_id       : $('.ab-select-location', $chain_item).val(),
                                        service_id        : $('.ab-select-service', $chain_item).val(),
                                        staff_ids         : staff_ids,
                                        number_of_persons : $('.ab-select-number-of-persons', $chain_item).val(),
                                        quantity          : $('.ab-select-quantity', $chain_item).val() ? $('.ab-select-quantity', $chain_item).val() : 1
                                    };
                                });
                                // Prepare days.
                                var days = [];
                                $('.bookly-week-days .active input.bookly-week-day', $container).each(function() {
                                    days.push(this.value);
                                });
                                $.ajax({
                                    type : 'POST',
                                    url  : Options.ajaxurl,
                                    data : {
                                        action    : 'bookly_session_save',
                                        form_id   : Options.form_id,
                                        chain     : chain,
                                        date_from : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                        days      : days,
                                        time_from : $select_time_from.val(),
                                        time_to   : $select_time_to.val()
                                    },
                                    dataType    : 'json',
                                    xhrFields   : { withCredentials: true },
                                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                    success     : function (response) {
                                        if (!Options.skip_steps.extras) {
                                            stepExtras();
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
                                    $('.ab-mobile-step_1', $container).hide();
                                    $('.ab-mobile-step_2', $container).css('display', 'block');
                                    if (Options.skip_steps.service) {
                                        $mobile_prev_step.remove();
                                    }
                                    if (skip_scroll != true) {
                                        scrollTo($container);
                                    }
                                }
                            }

                            return false;
                        });

                        $mobile_prev_step.on('click', function () {
                            $('.ab-mobile-step_1', $container).show();
                            $('.ab-mobile-step_2', $container).hide();
                            if ($select_service.val()) {
                                $('.ab-select-service', $container).parent().removeClass('ab-error');
                            }
                            return false;
                        });

                        if (Options.skip_steps.service_part1) {
                            // Skip scrolling
                            $mobile_next_step.trigger('click', [true]);
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
                action  : 'bookly_render_extras',
                form_id : Options.form_id
            }, params);
            if (Options.skip_steps.service) {
                // If Service step is skipped then we need to send time zone offset.
                data.time_zone_offset = time_zone_offset;
            }
            $.ajax({
                url: Options.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        if (params === undefined) { // Scroll when returning to the step Extras.
                            scrollTo($container);
                        }
                        var $next_step = $('.bookly-next-step', $container),
                            $back_step = $('.bookly-back-step', $container),
                            $goto_cart = $('.bookly-go-to-cart', $container),
                            $extras_items = $('.bookly-js-extras-item', $container),
                            $extras_summary = $('.bookly-extras-summary span', $container),
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
                                    action  : 'bookly_session_save',
                                    form_id : Options.form_id,
                                    extras  : extras
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
                form_id: Options.form_id
            }, params);
            if (Options.skip_steps.service) {
                // If Service step is skipped then we need to send time zone offset.
                data.time_zone_offset = time_zone_offset;
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
                    $container.html(response.html);

                    var $columnizer_wrap  = $('.ab-columnizer-wrap', $container),
                        $columnizer       = $('.ab-columnizer', $columnizer_wrap),
                        $time_next_button = $('.ab-time-next',  $container),
                        $time_prev_button = $('.ab-time-prev',  $container),
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
                    $('.bookly-back-step', $container).on('click', function (e) {
                        e.preventDefault();
                        ladda_start(this);
                        if (!Options.skip_steps.extras) {
                            stepExtras();
                        } else {
                            stepService();
                        }
                    }).toggle(!Options.skip_steps.service || !Options.skip_steps.extras);

                    $('.bookly-go-to-cart', $container).on('click', function(e) {
                        e.preventDefault();
                        ladda_start(this);
                        stepCart({from_step : 'time'});
                    });

                    if (Options.show_calendar) {
                        // Init calendar.
                        var $input = $('.ab-selected-date', $container);
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
                            $container.find('.ab--holder.ab-label-error').html(error_message);
                        } else {
                            $container.find('.ab--holder.ab-label-error').hide();
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

                        var hammertime = $('.ab-time-step', $container).hammer({ swipe_velocity: 0.1 });

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
                                    $button = $('.ab-column:hidden:last > button:last', $columnizer);
                                    if ($button.length == 0) {
                                        $button = $('.ab-column:last > button:last', $columnizer);
                                    }
                                }

                                // Render Next Time
                                var data = {
                                        action    : 'bookly_render_next_time',
                                        form_id   : Options.form_id,
                                        last_slot : $button.val()
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
                                                if ($('button.ab-day[value="' + $first_day.attr('value') + '"]', $container).length) {
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
                        $('.ab-time-screen,.ab-not-time-screen', $container).addClass('ab-spin-overlay');
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
                            new Spinner(opts).spin($('.ab-not-time-screen', $container).get(0));
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
                                if ($buttons.eq(0).hasClass('ab-day')) {
                                    slots_count = 1;
                                    $column = $('<div class="ab-column" />');
                                    $button = $($buttons.splice(0, 1));
                                    $button.addClass('ab-first-child');
                                    $column.append($button);
                                } else {
                                    slots_count ++;
                                    $button = $($buttons.splice(0, 1));
                                    // If it is last slot in the column.
                                    if (!$buttons.length || $buttons.eq(0).hasClass('ab-day')) {
                                        $button.addClass('ab-last-child');
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
                                $column = $('<div class="ab-column" />');
                                max_slots = slots_per_column;
                                if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('ab-day')) {
                                    // If this is the first column of a screen and the first slot in this column is not day
                                    // then put 1 slot less in this column because createScreens adds 1 more
                                    // slot to such columns.
                                    -- max_slots;
                                }
                                for (var i = 0; i < max_slots; ++ i) {
                                    if (i + 1 == max_slots && $buttons.eq(0).hasClass('ab-day')) {
                                        // Skip the last slot if it is day.
                                        break;
                                    }
                                    $button = $($buttons.splice(0, 1));
                                    if (i == 0) {
                                        $button.addClass('ab-first-child');
                                    } else if (i + 1 == max_slots) {
                                        $button.addClass('ab-last-child');
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
                        var $columns = $('> .ab-column', $columnizer),
                            cols_per_screen = $columns.length < columns_per_screen ? $columns.length : columns_per_screen;

                        while (has_more_slots ? $columns.length >= cols_per_screen : $columns.length) {
                            $screen = $('<div class="ab-time-screen"/>');
                            for (var i = 0; i < cols_per_screen; ++i) {
                                $column = $($columns.splice(0, 1));
                                if (i == 0) {
                                    $column.addClass('ab-first-column');
                                    var $first_slot = $column.find('.ab-first-child');
                                    // In the first column the first slot is time.
                                    if (!$first_slot.hasClass('ab-day')) {
                                        var group = $first_slot.data('group'),
                                            $group_slot = $('button.ab-day[value="' + group + '"]:last', $container);
                                        // Copy group slot to the first column.
                                        $column.prepend($group_slot.clone());
                                    }
                                }
                                $screen.append($column);
                            }
                            $columnizer.append($screen);
                        }
                        $screens = $('.ab-time-screen', $columnizer);
                        if ($current_screen === null) {
                            $current_screen = $screens.eq(0);
                        }

                        // On click on a slot.
                        $('button.ab-hour', $container).off('click').on('click', function (e) {
                            e.preventDefault();
                            var $this = $(this),
                                data = {
                                    action  : 'bookly_session_save',
                                    form_id : Options.form_id,
                                    slots   : this.value
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
                                        stepCart({add_to_cart : true});
                                    } else {
                                        stepDetails({add_to_cart : true});
                                    }
                                }
                            });
                        });

                        // Columnizer width & height.
                        $('.ab-time-step', $container).width(cols_per_screen * column_width);
                        $columnizer_wrap.height(form_hidden
                            ? $('.ab-column.ab-first-column button', $current_screen).length * (slot_height + 3)
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
                    action  : 'bookly_render_repeat',
                    form_id : Options.form_id
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

                            var $repeat_enabled   = $('.bookly-repeat-appointment-enabled', $container),
                                $next_step        = $('.bookly-next-step', $container),
                                $repeat_container = $('.bookly-repeat-variants-container', $container),
                                $variants         = $('[class^="bookly-variant"]', $repeat_container),
                                $repeat_variant   = $('.bookly-repeat-variant', $repeat_container),
                                $button_get_schedule = $('.bookly-get-schedule', $repeat_container),
                                $variant_weekly   = $('.bookly-variant-weekly', $repeat_container),
                                $variant_monthly  = $('.variant-monthly', $repeat_container),
                                $date_until       = $('.bookly-repeat-until', $repeat_container),
                                $monthly_specific_day = $('.bookly-monthly-specific-day', $repeat_container),
                                $monthly_week_day = $('.bookly-monthly-week-day', $repeat_container),
                                $repeat_every_day = $('.bookly-repeat-daily-every', $repeat_container),
                                $week_day         = $('.bookly-week-day', $repeat_container),
                                $schedule_container = $('.bookly-schedule-container', $container),
                                $days_error       = $('.bookly-js-days-error', $repeat_container),
                                $schedule_slots   = $('.bookly-schedule-slots',$schedule_container),
                                $intersection_info = $('.bookly-intersection-info', $schedule_container),
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
                                    var $time = $('<select/>'),
                                        prefer;
                                    $.each(options, function (index, option) {
                                        $time.append($('<option value="' + option.value + '">' + option.title + '</option>'));
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
                                    $schedule_row.find('.bookly-schedule-time').html($time);
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
                                }
                            };

                            var open_repeat_onchange = $repeat_enabled.on('change', function () {
                                $repeat_container.toggle($(this).prop('checked'));
                                if ($(this).prop('checked')) {
                                    repeat.prepareButtonNextState();
                                } else {
                                    $next_step.prop('disabled', false);
                                }
                            });
                            if (response.repeated) {
                                $repeat_enabled.prop('checked', true);
                            }
                            open_repeat_onchange.trigger('change');

                            if (!response.could_be_repeated) {
                                $repeat_enabled.attr('disabled', true);
                            }


                            $repeat_variant.on('change', function () {
                                $variants.hide();
                                $repeat_container.find('.bookly-variant-' + this.value).show()
                            }).trigger('change');

                            $variant_monthly.on('change', function () {
                                $monthly_week_day.toggle(this.value != 'specific');
                                $monthly_specific_day.toggle(this.value == 'specific');
                            }).trigger('change');

                            $week_day.on('change', function () {
                                var $this = $(this);
                                if ($this.is(':checked')) {
                                    $this.parent().not("[class*='active']").addClass('active');
                                } else {
                                    $this.parent().removeClass('active');
                                }
                            });

                            $monthly_specific_day.val(response.date_min[2]);

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

                            $button_get_schedule.on('click', function () {
                                $schedule_container.hide();
                                var data = {
                                        action : 'bookly_recurring_appointments_get_customer_schedule',
                                        form_id: Options.form_id,
                                        repeat : $repeat_variant.val(),
                                        until  : $date_until.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                        params : {}
                                    },
                                    ladda = ladda_start(this);

                                switch (data.repeat) {
                                    case 'daily':
                                        data.params = {every: $repeat_every_day.val()};
                                        break;
                                    case 'weekly':
                                    case 'biweekly':
                                        data.params.on = [];
                                        $('.bookly-week-days input.bookly-week-day:checked', $variant_weekly).each(function () {
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
                                            schedule = response.data;
                                            // Prefer time is display time selected on step time.
                                            var prefer_time = null;
                                            $.each(schedule, function (index, item) {
                                                if (!prefer_time && !item.another_time) {
                                                    prefer_time = item.display_time;
                                                }
                                            });
                                            repeat.renderSchedulePage(1);
                                            $schedule_container.show();
                                            ladda.stop();
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
                                                                        action  : 'bookly_recurring_appointments_get_daily_customer_schedule',
                                                                        date    : this.get('select', 'yyyy-mm-dd'),
                                                                        form_id : Options.form_id,
                                                                        exclude : exclude
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
                                                        var date = new Date();
                                                        date.setTime(slots[0][2] * 1000);
                                                        $date.pickadate('picker').set('select', date);
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
                                        }
                                    }
                                });
                            });

                            $('.bookly-back-step', $container).on('click', function (e) {
                                e.preventDefault();
                                ladda_start(this);
                                $.ajax({
                                    type: 'POST',
                                    url: Options.ajaxurl,
                                    data: {
                                        action: 'bookly_session_save',
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

                            $('.bookly-go-to-cart', $container).on('click', function(e) {
                                e.preventDefault();
                                ladda_start(this);
                                stepCart({from_step : 'repeat'});
                            });

                            $('.bookly-next-step', $container).on('click', function (e) {
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
                                            form_id: Options.form_id,
                                            slots: JSON.stringify(slots_to_send),
                                            repeat: repeat
                                        },
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            stepCart({add_to_cart: true});
                                        }
                                    });
                                } else {
                                    $.ajax({
                                        type: 'POST',
                                        url: Options.ajaxurl,
                                        data: {
                                            action: 'bookly_session_save',
                                            form_id: Options.form_id,
                                            unrepeat: 1
                                        },
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            stepCart({add_to_cart: true});
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
                var data = $.extend({
                    action  : 'bookly_render_cart',
                    form_id : Options.form_id
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
                                $('.ab--holder.ab-label-error', $container).html(error.message);
                                $('tr[data-cart-key="'+ error.failed_key +'"]', $container).addClass('ab-label-error');
                            } else {
                                $('.ab--holder.ab-label-error', $container).hide();
                            }
                            scrollTo($container);
                            $('.bookly-next-step', $container).on('click', function () {
                                ladda_start(this);
                                stepDetails();
                            });
                            $('.ab-add-item', $container).on('click', function () {
                                ladda_start(this);
                                stepService({new_chain : true});
                            });
                            // 'BACK' button.
                            $('.bookly-back-step', $container).on('click', function (e) {
                                e.preventDefault();
                                ladda_start(this);
                                if (params && params.from_step) { //if cart icon was clicked
                                    if (params.from_step == 'service') {
                                        stepService();
                                    } else if (params.from_step == 'extras') {
                                        stepExtras();
                                    } else if (params.from_step === 'time') {
                                        stepTime();
                                    } else {
                                        stepRepeat();
                                    }
                                } else { //if cart was reached in normal way
                                    if (!Options.skip_steps.repeat) {
                                        stepRepeat();
                                    } else {
                                        stepTime();
                                    }
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
                                                action   : 'bookly_cart_drop_item',
                                                form_id  : Options.form_id,
                                                cart_key : $cart_item.data('cart-key')
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
                                                            $('.bookly-back-step', $container).hide();
                                                            $('.bookly-next-step', $container).hide();
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
                action  : 'bookly_render_details',
                form_id : Options.form_id
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
                        // Init
                        var $phone_field  = $('.ab-user-phone-input', $container),
                            $email_field  = $('.ab-user-email',       $container),
                            $name_field   = $('.ab-full-name',        $container),
                            $phone_error  = $('.ab-user-phone-error', $container),
                            $email_error  = $('.ab-user-email-error', $container),
                            $name_error   = $('.ab-full-name-error',  $container),
                            $captcha      = $('.ab-captcha-img',      $container),
                            $errors       = $('.ab-user-phone-error, .ab-user-email-error, .ab-full-name-error, div.ab-custom-field-error', $container),
                            $fields       = $('.ab-user-phone-input, .ab-user-email, .ab-full-name, .ab-custom-field', $container),
                            phone_number  = ''
                        ;
                        if (Options.intlTelInput.enabled) {
                            $phone_field.intlTelInput({
                                preferredCountries: [Options.intlTelInput.country],
                                defaultCountry: Options.intlTelInput.country,
                                geoIpLookup: function (callback) {
                                    $.get(Options.ajaxurl, {action: 'bookly_ip_info'}, function () {
                                    }, 'json').always(function (resp) {
                                        var countryCode = (resp && resp.country) ? resp.country : '';
                                        callback(countryCode);
                                    });
                                },
                                utilsScript: Options.intlTelInput.utils
                            });
                        }
                        $('.bookly-next-step', $container).on('click', function(e) {
                            e.preventDefault();
                            var custom_fields = [],
                                checkbox_values,
                                captcha_ids = [],
                                ladda = ladda_start(this)
                            ;
                            $('.bookly-custom-fields-container', $container).each(function () {
                                var $cf_container = $(this),
                                    key = $cf_container.data('cart_key'),
                                    custom_fields_data = [];
                                $('div.ab-custom-field-row', $cf_container).each(function() {
                                    var $this = $(this);
                                    switch ($this.data('type')) {
                                        case 'text-field':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('input.ab-custom-field').val()
                                            });
                                            break;
                                        case 'textarea':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('textarea.ab-custom-field').val()
                                            });
                                            break;
                                        case 'checkboxes':
                                            checkbox_values = [];
                                            $this.find('input.ab-custom-field:checked').each(function () {
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
                                                value  : $this.find('input.ab-custom-field:checked').val() || null
                                            });
                                            break;
                                        case 'drop-down':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('select.ab-custom-field').val()
                                            });
                                            break;
                                        case 'captcha':
                                            custom_fields_data.push({
                                                id     : $this.data('id'),
                                                value  : $this.find('input.ab-custom-field').val()
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
                                action      : 'bookly_session_save',
                                form_id     : Options.form_id,
                                name        : $name_field.val(),
                                phone       : phone_number,
                                email       : $email_field.val(),
                                cart        : custom_fields,
                                captcha_ids : JSON.stringify(captcha_ids)
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
                                    $fields.removeClass('ab-error');

                                    if (response.length == 0) {
                                        if (Options.woocommerce.enabled) {
                                            var data = {
                                                action  : 'bookly_add_to_woocommerce_cart',
                                                form_id : Options.form_id
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
                                        ladda.stop();
                                        var $scroll_to = null;
                                        if (response.name) {
                                            $name_error.html(response.name);
                                            $name_field.addClass('ab-error');
                                            $scroll_to = $name_field;
                                        }
                                        if (response.phone) {
                                            $phone_error.html(response.phone);
                                            $phone_field.addClass('ab-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $phone_field;
                                            }
                                        }
                                        if (response.email) {
                                            $email_error.html(response.email);
                                            $email_field.addClass('ab-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $email_field;
                                            }
                                        }
                                        if (response.custom_fields) {
                                            $.each(response.custom_fields, function (key, fields) {
                                                $.each(fields, function (field_id, message) {
                                                    var $custom_fields_collector = $('.bookly-custom-fields-container[data-cart_key="' + key + '"]', $container);
                                                    var $div = $('[data-id="' + field_id + '"]', $custom_fields_collector);
                                                    $div.find('.ab-custom-field-error').html(message);
                                                    $div.find('.ab-custom-field').addClass('ab-error');
                                                    if ($scroll_to === null) {
                                                        $scroll_to = $div.find('.ab-custom-field');
                                                    }
                                                });
                                            });
                                        }
                                        if ($scroll_to !== null) {
                                            scrollTo($scroll_to);
                                        }
                                    }
                                }
                            });
                        });

                        $('.bookly-back-step', $container).on('click', function (e) {
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

                        $('.ab-captcha-refresh',  $container).on('click', function() {
                            $captcha.css('opacity','0.5');
                            $.get(Options.ajaxurl, {action: 'bookly_captcha_refresh', form_id: Options.form_id}, function(response) {
                                if (response.success) {
                                    $captcha.attr('src', response.data.captcha_url).on('load', function() {
                                        $captcha.css('opacity', '1');
                                    });
                                }
                            }, 'json');
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
                data       : {action: 'bookly_render_payment', form_id: Options.form_id, page_url: document.URL.split('#')[0]},
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

                        var $payments  = $('.ab-payment', $container),
                            $apply_coupon_button = $('.btn-apply-coupon', $container),
                            $coupon_input = $('input.ab-user-coupon', $container),
                            $coupon_error = $('.ab-coupon-error', $container),
                            $coupon_info_text = $('.ab-info-text-coupon', $container),
                            $ab_payment_nav = $('.ab-payment-nav', $container),
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
                            $coupon_input.removeClass('ab-field-error');

                            var data = {
                                action : 'bookly_apply_coupon',
                                form_id: Options.form_id,
                                coupon : $coupon_input.val()
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
                                        $coupon_input.replaceWith(data.coupon);
                                        $apply_coupon_button.replaceWith('✓');
                                        if (response.total <= 0) {
                                            $ab_payment_nav.hide();
                                            $buttons.hide();
                                            $('.bookly-gateway-buttons.pay-coupon', $container).show();
                                            $('.bookly-coupon-free', $container).attr('checked', 'checked').val(data.coupon);
                                        } else {
                                            // Set new price for payment request
                                            $('input.bookly-payment-amount', $container).val(response.total);
                                            var $payu_latam_form = $('.bookly-payu_latam-form', $container);
                                            if ($payu_latam_form.length) {
                                                $.post(Options.ajaxurl, {action: 'bookly_payu_latam_refresh_tokens', form_id: Options.form_id})
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
                                        $coupon_input.addClass('ab-field-error');
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

                        $('.bookly-next-step', $container).on('click', function (e) {
                            var ladda = ladda_start(this),
                                $form
                            ;
                            if ($('.ab-payment[value=local]', $container).is(':checked') || $(this).hasClass('ab-coupon-payment')) {
                                // handle only if was selected local payment !
                                e.preventDefault();
                                save();

                            } else if ($('.ab-payment[value=card]', $container).is(':checked')) {
                                var stripe = $('.ab-payment[data-form=stripe]', $container).is(':checked');
                                var card_action = stripe ? 'bookly_stripe' : 'bookly_authorize_net_aim';
                                $form = $container.find(stripe ? '.bookly-stripe' : '.bookly-authorize-net');
                                e.preventDefault();

                                var data = {
                                    action: card_action,
                                    card: {
                                        number   : $form.find('input[name="ab_card_number"]').val(),
                                        cvc      : $form.find('input[name="ab_card_cvc"]').val(),
                                        exp_month: $form.find('select[name="ab_card_exp_month"]').val(),
                                        exp_year : $form.find('select[name="ab_card_exp_year"]').val()
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
                                                $form.find('.ab-card-error').text(response.error);
                                            }
                                        }
                                    });
                                };
                                if (stripe && $form.find('#publishable_key').val()) {
                                    try {
                                        Stripe.setPublishableKey($form.find('#publishable_key').val());
                                        Stripe.createToken(data.card, function (status, response) {
                                            if (response.error) {
                                                $form.find('.ab-card-error').text(response.error.message);
                                                ladda.stop();
                                            } else {
                                                // Token from stripe.js
                                                data['card'] = response['id'];
                                                card_payment(data);
                                            }
                                        });
                                    } catch (e) {
                                        $form.find('.ab-card-error').text(e.message);
                                        ladda.stop();
                                    }
                                } else {
                                    card_payment(data);
                                }
                            } else if (    $('.ab-payment[value=paypal]',     $container).is(':checked')
                                        || $('.ab-payment[value=2checkout]',  $container).is(':checked')
                                        || $('.ab-payment[value=payu_latam]', $container).is(':checked')
                                        || $('.ab-payment[value=payson]',     $container).is(':checked')
                                        || $('.ab-payment[value=mollie]',     $container).is(':checked')
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
                                        data       : {action: 'bookly_check_cart', form_id: Options.form_id},
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

                        $('.bookly-back-step', $container).on('click', function (e) {
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
        function stepComplete() {
            if (Options.final_step_url) {
                document.location.href = Options.final_step_url;
            } else {
                $.ajax({
                    url: Options.ajaxurl,
                    data: {action: 'bookly_render_complete', form_id: Options.form_id},
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
                data        : { action : 'bookly_save_appointment', form_id : Options.form_id },
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
