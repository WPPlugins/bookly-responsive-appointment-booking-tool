import $ from 'jquery';
import {opt, laddaStart, scrollTo} from './shared.js';
import stepExtras from './extras_step.js';
import stepTime from './time_step.js';
import stepCart from './cart_step.js';

/**
 * Service step.
 */
export default function stepService(params) {
    if (opt[params.form_id].skip_steps.service) {
        if (!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'before_step_time') {
            stepExtras(params)
        } else {
            stepTime(params);
        }
        return;
    }
    var data = {
            action    : 'bookly_render_service',
            csrf_token: BooklyL10n.csrf_token,
        },
        $container = opt[params.form_id].$container;
    if (opt[params.form_id].use_client_time_zone) {
        data.time_zone        = opt[params.form_id].timeZone;
        data.time_zone_offset = opt[params.form_id].timeZoneOffset;
    }
    $.extend(data, params);
    $.ajax({
        url         : BooklyL10n.ajaxurl,
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

                var $chain_item_draft          = $('.bookly-js-chain-item.bookly-js-draft', $container),
                    $select_location           = $('.bookly-js-select-location', $container),
                    $select_category           = $('.bookly-js-select-category', $container),
                    $select_service            = $('.bookly-js-select-service', $container),
                    $select_employee           = $('.bookly-js-select-employee', $container),
                    $select_duration           = $('.bookly-js-select-units-duration', $container),
                    $select_nop                = $('.bookly-js-select-number-of-persons', $container),
                    $select_quantity           = $('.bookly-js-select-quantity', $container),
                    $date_from                 = $('.bookly-js-date-from', $container),
                    $week_day                  = $('.bookly-js-week-day', $container),
                    $select_time_from          = $('.bookly-js-select-time-from', $container),
                    $select_time_to            = $('.bookly-js-select-time-to', $container),
                    $next_step                 = $('.bookly-js-next-step', $container),
                    $mobile_next_step          = $('.bookly-js-mobile-next-step', $container),
                    $mobile_prev_step          = $('.bookly-js-mobile-prev-step', $container),
                    locations                  = response.locations,
                    categories                 = response.categories,
                    services                   = response.services,
                    staff                      = response.staff,
                    chain                      = response.chain,
                    required                   = response.required,
                    defaults                   = opt[params.form_id].defaults,
                    services_per_location      = response.services_per_location,
                    last_chain_key             = 0,
                    category_selected          = false,
                    service_name_with_duration = response.service_name_with_duration,
                    show_ratings               = response.show_ratings;

                // Init Pickadate.
                $date_from.pickadate({
                    formatSubmit    : 'yyyy-mm-dd',
                    format          : opt[params.form_id].date_format,
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
                    firstDay        : opt[params.form_id].firstDay,
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
                    laddaStart(this);
                    stepCart({form_id: params.form_id,from_step : 'service'});
                });

                // insert data into select
                var setSelect = function($select, data, value) {
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
                    if ($select.find('option[value="' + value + '"]').length) {
                        $select.val(value);
                    }
                };

                var setSelects = function($chain_item, location_id, category_id, service_id, staff_id) {
                    var _location_id = (services_per_location && location_id) ? location_id : 0;
                    var _staff = {}, _services = {}, _categories = {}, _nop = {}, _max_capacity = null, _min_capacity = null;
                    $.each(staff, function(id, staff_member) {
                        if (!location_id || locations[location_id].staff.hasOwnProperty(id)) {
                            if (!service_id) {
                                if (!category_id) {
                                    _staff[id] = $.extend({}, staff_member);
                                } else {
                                    $.each(staff_member.services, function(s_id) {
                                        if (services[s_id].category_id == category_id) {
                                            _staff[id] = $.extend({}, staff_member);
                                            return false;
                                        }
                                    });
                                }
                            } else if (staff_member.services.hasOwnProperty(service_id)) {
                                $.each(staff_member.services[service_id].locations, function(loc_id, loc_srv) {
                                    if (_location_id && _location_id != loc_id) {
                                        return true;
                                    }
                                    _min_capacity = _min_capacity ? Math.min(_min_capacity, loc_srv.min_capacity) : loc_srv.min_capacity;
                                    _max_capacity = _max_capacity ? Math.max(_max_capacity, loc_srv.max_capacity) : loc_srv.max_capacity;
                                    _staff[id] = {
                                        id   : id,
                                        name : staff_member.name + (
                                            loc_srv.price != null && (_location_id || !services_per_location)
                                                ? ' (' + loc_srv.price + ')'
                                                : ''
                                        ),
                                        pos  : staff_member.pos
                                    };
                                });
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
                        if (services_per_location) {
                            $.each(staff, function (st_id) {
                                $.each(staff[st_id].services, function (s_id) {
                                    if (staff[st_id].services[s_id].locations.hasOwnProperty(_location_id)) {
                                        category_ids.push(services[s_id].category_id);
                                        service_ids.push(s_id);
                                    }
                                });
                            });
                        } else {
                            $.each(locations[location_id].staff, function(st_id) {
                                $.each(staff[st_id].services, function(s_id) {
                                    category_ids.push(services[s_id].category_id);
                                    service_ids.push(s_id);
                                });
                            });
                        }
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
                    var nop = $('.bookly-js-select-number-of-persons', $chain_item).val() || 1;
                    var max_capacity = service_id
                        ? (staff_id
                            ? (staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id) ?
                                    staff[staff_id].services[service_id].locations[_location_id].max_capacity :
                                    1
                            )
                            : _max_capacity ? _max_capacity : 1)
                        : 1;
                    var min_capacity = service_id
                        ? (staff_id
                            ? (staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id) ?
                                    staff[staff_id].services[service_id].locations[_location_id].min_capacity :
                                    1
                            )
                            : _min_capacity ? _min_capacity : 1)
                        : 1;
                    for (var i = min_capacity; i <= max_capacity; ++ i) {
                        _nop[i] = { id: i, name: i, pos: i };
                    }
                    if (nop > max_capacity) {
                        nop = max_capacity;
                    }
                    if (nop < min_capacity || !opt[params.form_id].form_attributes.show_number_of_persons) {
                        nop = min_capacity;
                    }
                    // Add ratings to staff names
                    if (show_ratings) {
                        $.each(staff, function (id, staff_member) {
                            if (_staff.hasOwnProperty(staff_member.id)) {
                                if (service_id) {
                                    if (staff_member.services.hasOwnProperty(service_id) && staff_member.services[service_id].rating) {
                                        _staff[staff_member.id].name = '★' + staff_member.services[service_id].rating + ' ' + _staff[staff_member.id].name;
                                    }
                                } else if (staff_member.rating) {
                                    _staff[staff_member.id].name = '★' + staff_member.rating + ' ' + _staff[staff_member.id].name;
                                }
                            }
                        });
                    }
                    setSelect($chain_item.find('.bookly-js-select-category'), _categories, category_id);
                    setSelect($chain_item.find('.bookly-js-select-service'), _services, service_id);
                    setSelect($chain_item.find('.bookly-js-select-employee'), _staff, staff_id);
                    setSelect($chain_item.find('.bookly-js-select-number-of-persons'), _nop, nop);
                };

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
                        var _location_id = services_per_location ? location_id : 0;
                        if (staff_id) {
                            if (!locations[location_id].staff.hasOwnProperty(staff_id)) {
                                staff_id = '';
                            } else if (service_id && !staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id)) {
                                staff_id = '';
                            }
                        }
                        if (service_id) {
                            var valid = false;
                            $.each(locations[location_id].staff, function(id) {
                                if (staff[id].services.hasOwnProperty(service_id) && staff[id].services[service_id].locations.hasOwnProperty(_location_id)) {
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
                    updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
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

                var updateServiceDurationSelect = function($chain_item, service_id, staff_id, location_id) {
                    var $units_duration = $chain_item.find('.bookly-js-select-units-duration'),
                        current_duration = $units_duration.val();
                    $units_duration.find('option').remove();
                    if (service_id) {
                        var getUnitsByStaffId = function (staff_id) {
                            if (!staff_id || services_per_location && !location_id) {
                                return services[service_id].hasOwnProperty('units')
                                    ? services[service_id]['units']
                                    : [{'value': '', 'title': '-'}];
                            }

                            var locationId = location_id ? location_id : 0,
                                staffLocations = staff[staff_id].services[service_id].locations;
                            if (staffLocations === undefined) {
                                return [{'value': '', 'title': '-'}];
                            }
                            var staffLocation = staffLocations.hasOwnProperty(locationId) ? staffLocations[locationId] : staffLocations[0];
                            return staffLocation.units || [{'value': '', 'title': '-'}];
                        };

                        // add slots for picked service
                        $.each(getUnitsByStaffId(staff_id), function (i, item) {
                            $units_duration.append($('<option>', {
                                value: item.value,
                                text: item.title
                            }));
                        });
                        if ($units_duration.find('option[value="' + current_duration + '"]').length != 0) {
                            $units_duration.val(current_duration);
                        }
                    } else {
                        $units_duration.append($('<option>', {
                            value: '',
                            text: '-'
                        }));
                    }
                };

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
                    updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
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
                    updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
                });

                // Set up draft selects.
                if (service_name_with_duration) {
                    $.each(services, function(id, service) {
                        service.name = service.name + ' ( ' + service.duration + ' )';
                    });
                }

                setSelect($select_location, locations);
                setSelect($select_category, categories);
                setSelect($select_service, services);
                if (show_ratings) {
                    var _staff = {};
                    $.each(staff, function (id, staff_member) {
                        _staff[id] = $.extend({}, staff_member);
                        if (staff_member.rating) {
                            _staff[id].name = '★' + staff_member.rating + ' ' + _staff[id].name;
                        }
                    });
                    setSelect($select_employee, _staff);
                } else {
                    setSelect($select_employee, staff);
                }
                $select_location.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_locations);
                $select_category.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_categories);
                $select_service.closest('.bookly-form-group').toggle(!(opt[params.form_id].form_attributes.hide_services && defaults.service_id));
                $select_employee.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_staff_members);
                $select_duration.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_service_duration);
                $select_nop.closest('.bookly-form-group').toggle(opt[params.form_id].form_attributes.show_number_of_persons);
                $select_quantity.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_quantity);
                if (defaults.location_id) {
                    $select_location.val(defaults.location_id).trigger('change');
                }
                if (defaults.category_id) {
                    $select_category.val(defaults.category_id).trigger('change');
                }
                if (defaults.service_id) {
                    $select_service.val(defaults.service_id).trigger('change');
                }
                if (defaults.staff_id) {
                    $select_employee.val(defaults.staff_id).trigger('change');
                }

                if (opt[params.form_id].form_attributes.hide_date) {
                    $('.bookly-js-available-date', $container).hide();
                }
                if (opt[params.form_id].form_attributes.hide_week_days) {
                    $('.bookly-js-week-days', $container).hide();
                }
                if (opt[params.form_id].form_attributes.hide_time_range) {
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
                    if (!opt[params.form_id].form_attributes.hide_locations && chain_item.location_id) {
                        $('.bookly-js-select-location', $chain_item).val(chain_item.location_id).trigger('change');
                    }
                    if (chain_item.service_id) {
                        $('.bookly-js-select-service', $chain_item).val(chain_item.service_id).trigger('change');
                        if (opt[params.form_id].form_attributes.hide_categories) {
                            if (opt[params.form_id].form_attributes.hasOwnProperty('const_category_id')) {
                                // Keep category selected by 'admin'.
                                $('.bookly-js-select-category', $chain_item).val(opt[params.form_id].form_attributes.const_category_id);
                            } else {
                                // Deselect category to keep full list of services.
                                $('.bookly-js-select-category', $chain_item).val('');
                            }
                        } else {
                            $('.bookly-js-select-category', $chain_item).val(services[chain_item.service_id].category_id).trigger('change');
                        }
                    }
                    if (!opt[params.form_id].form_attributes.hide_staff_members && chain_item.staff_ids.length == 1 && chain_item.staff_ids[0]) {
                        $('.bookly-js-select-employee', $chain_item).val(chain_item.staff_ids[0]).trigger('change');
                    }
                    if (chain_item.number_of_persons > 1) {
                        $('.bookly-js-select-number-of-persons', $chain_item).val(chain_item.number_of_persons);
                    }
                    if (chain_item.units > 1) {
                        $('.bookly-js-select-units-duration', $chain_item).val(chain_item.units);
                    }
                    if (chain_item.quantity > 1) {
                        $('.bookly-js-select-quantity', $chain_item).val(chain_item.quantity);
                    }
                });

                $container.on('click', '.bookly-js-mobile-step-1 .bookly-js-add-chain', function () {
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
                });
                $container.on('click', '.bookly-js-mobile-step-1 .bookly-js-actions button[data-action="drop"]', function () {
                    $(this).closest('.bookly-js-chain-item').remove();
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
                        if (required.hasOwnProperty('location') && required.location && !$select_location.val()) {
                            valid = false;
                            $select_location.addClass('bookly-error');
                            $('.bookly-js-select-location-error', $chain).show();
                            $scroll_to = $select_location;
                        }
                        if (required.staff && !$select_employee.val()) {
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

                        laddaStart(this);

                        // Prepare chain data.
                        var chain = {},
                            has_extras = 0,
                            time_requirements = 0,
                            _time_requirements = {'required': 2, 'optional': 1, 'off': 0};
                        $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                            var $chain_item = $(this),
                                staff_ids = [],
                                _service = services[$('.bookly-js-select-service', $chain_item).val()];
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
                                units             : $('.bookly-js-select-units-duration', $chain_item).val() || 1,
                                number_of_persons : $('.bookly-js-select-number-of-persons', $chain_item).val() || 1,
                                quantity          : $('.bookly-js-select-quantity', $chain_item).val() ? $('.bookly-js-select-quantity', $chain_item).val() : 1
                            };
                            time_requirements = Math.max(time_requirements, _time_requirements[_service.hasOwnProperty('time_requirements') ? _service.time_requirements : 'required']);
                            has_extras += _service.has_extras;
                        });

                        // Prepare days.
                        var days = [];
                        $('.bookly-js-week-days .active input.bookly-js-week-day', $container).each(function() {
                            days.push(this.value);
                        });
                        $.ajax({
                            type : 'POST',
                            url  : BooklyL10n.ajaxurl,
                            data : {
                                action     : 'bookly_session_save',
                                csrf_token : BooklyL10n.csrf_token,
                                form_id    : params.form_id,
                                chain      : chain,
                                date_from  : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                days       : days,
                                time_from  : $select_time_from.val(),
                                time_to    : $select_time_to.val(),
                                no_extras  : has_extras == 0
                            },
                            dataType    : 'json',
                            xhrFields   : { withCredentials: true },
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success     : function (response) {
                                opt[params.form_id].no_time = time_requirements == 0;
                                opt[params.form_id].no_extras = has_extras == 0;
                                if (opt[params.form_id].skip_steps.extras) {
                                    stepTime({form_id: params.form_id});
                                } else {
                                    if (has_extras == 0 || opt[params.form_id].step_extras == 'after_step_time') {
                                        stepTime({form_id: params.form_id});
                                    } else {
                                        stepExtras({form_id: params.form_id});
                                    }
                                }
                            }
                        });
                    }
                });

                $mobile_next_step.on('click', function (e,skip_scroll) {
                    if (stepServiceValidator()) {
                        if (opt[params.form_id].skip_steps.service_part2) {
                            laddaStart(this);
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

                if (opt[params.form_id].skip_steps.service_part1) {
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