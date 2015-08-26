(function($) {
    window.bookly = function(options) {
        var $container  = $('#ab-booking-form-' + options.form_id);
        var today       = new Date();
        var Options     = $.extend(options, {
            skip_first_step : (
                options.attributes.hide_categories &&
                options.attributes.category_id &&
                options.attributes.hide_services &&
                options.attributes.service_id &&
                options.attributes.hide_staff_members &&
                !options.attributes.show_number_of_persons &&
                options.attributes.hide_date_and_time
            ),
            skip_date_time : options.attributes.hide_date_and_time,
            skip_service   : options.attributes.hide_categories
                && options.attributes.category_id
                && options.attributes.hide_services
                && options.attributes.service_id
                && options.attributes.hide_staff_members
                && !options.attributes.show_number_of_persons
        });

        // initialize
        if (Options.is_finished) {
            fifthStep();
        } else {
            firstStep();
        }

        //
        function firstStep() {

            if (Options.is_cancelled) {
                fourthStep();

            } else if (Options.is_finished) {
                fifthStep();

            } else {
                $.ajax({
                    url         : Options.ajaxurl,
                    data        : { action: 'ab_render_service', form_id: Options.form_id, time_zone_offset: today.getTimezoneOffset() },
                    dataType    : 'json',
                    xhrFields   : { withCredentials: true },
                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                    success     : function (response) {
                        if (response.success) {
                            $container.html(response.html);

                            var $select_category  = $('.ab-select-category', $container),
                                $select_service   = $('.ab-select-service', $container),
                                $select_nop       = $('.ab-select-number-of-persons', $container),
                                $select_staff     = $('.ab-select-employee', $container),
                                $date_from        = $('.ab-date-from', $container),
                                $week_day         = $('.ab-week-day', $container),
                                $select_time_from = $('.ab-select-time-from', $container),
                                $select_time_to   = $('.ab-select-time-to', $container),
                                $service_error    = $('.ab-select-service-error', $container),
                                $time_error       = $('.ab-select-time-error', $container),
                                $next_step        = $('.ab-next-step', $container),
                                $mobile_next_step = $('.ab-mobile-next-step', $container),
                                $mobile_prev_step = $('.ab-mobile-prev-step', $container),
                                categories        = response.categories,
                                services          = response.services,
                                staff             = response.staff
                            ;

                            // Overwrite attributes if necessary.
                            if (response.attributes) {
                                if (!Options.attributes.hide_categories && Options.attributes.service_id != response.attributes.service_id) {
                                    Options.attributes.category_id = null;
                                }
                                Options.attributes.service_id = response.attributes.service_id;
                                if (!Options.attributes.hide_staff_members) {
                                    Options.attributes.staff_member_id = response.attributes.staff_member_id;
                                }
                                Options.attributes.number_of_persons = response.attributes.number_of_persons;
                            }

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
                                        $('.ab-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container).attr('checked', true).trigger('change');
                                    }
                                }
                            });

                            function setSelectNumberOfPersons() {
                                var service_id = $select_service.val();
                                if (service_id) {
                                    var staff_id = $select_staff.val();
                                    var number_of_persons = $select_nop.val();
                                    var max_capacity = staff_id ? staff[staff_id].services[service_id].max_capacity : services[service_id].max_capacity;
                                    $select_nop.empty();
                                    for (var i = 1; i <= max_capacity; ++ i) {
                                        $select_nop.append('<option value="' + i +'">' + i + '</option>');
                                    }
                                    if (number_of_persons <= max_capacity) {
                                        $select_nop.val(number_of_persons);
                                    }
                                } else {
                                    $select_nop.empty().append('<option value="1">1</option>');
                                }
                            }

                            // fill the selects
                            setSelect($select_category, categories);
                            setSelect($select_service, services);
                            setSelect($select_staff, staff);

                            // Category select change
                            $select_category.on('change', function() {
                                var category_id = this.value;

                                // filter the services and staff
                                // if service or staff is selected, leave it selected
                                if (category_id) {
                                    setSelect($select_service, categories[category_id].services);
                                    setSelect($select_staff, categories[category_id].staff, true);
                                // show all services and staff
                                // if service or staff is selected, reset it
                                } else {
                                    setSelect($select_service, services);
                                    setSelect($select_staff, staff);
                                }
                            });

                            // Service select change
                            $select_service.on('change', function() {
                                var service_id = this.value;

                                // select the category
                                // filter the staffs by service
                                // show staff with price
                                // if staff selected, leave it selected
                                // if staff not selected, select all
                                if (service_id) {
                                    $select_category.val(services[service_id].category_id);
                                    setSelect($select_staff, services[service_id].staff, true);
                                // filter staff by category
                                } else {
                                    var category_id = $select_category.val();
                                    if (category_id) {
                                        setSelect($select_staff, categories[category_id].staff, true);
                                    } else {
                                        setSelect($select_staff, staff, true);
                                    }

                                }
                                setSelectNumberOfPersons();
                            });

                            // Staff select change
                            $select_staff.on('change', function() {
                                var staff_id = this.value;
                                var category_id = $select_category.val();

                                // filter services by staff and category
                                // if service selected, leave it
                                if (staff_id) {
                                    var services_a = {};
                                    if (category_id) {
                                        $.each(staff[staff_id].services, function(index, st) {
                                            if (services[st.id].category_id == category_id) {
                                                services_a[st.id] = st;
                                            }
                                        });
                                    } else {
                                        services_a = staff[staff_id].services;
                                    }
                                    setSelect($select_service, services_a, true);
                                // filter services by category
                                } else {
                                    if (category_id) {
                                        setSelect($select_service, categories[category_id].services, true);
                                    } else {
                                        setSelect($select_service, services, true);
                                    }
                                }
                                setSelectNumberOfPersons();
                            });

                            // Category
                            if (Options.attributes.category_id) {
                                $select_category.val(Options.attributes.category_id).trigger('change');
                            }
                            // Services
                            if (Options.attributes.service_id) {
                                $select_service.val(Options.attributes.service_id).trigger('change');
                            }
                            // Employee
                            if (Options.attributes.staff_member_id) {
                                $select_staff.val(Options.attributes.staff_member_id).trigger('change');
                            }
                            // Number of persons
                            if (Options.attributes.number_of_persons) {
                                $select_nop.val(Options.attributes.number_of_persons);
                            }

                            hideByAttributes();

                            // change the week days
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

                                var first_value =  $('option:first', $select_time_to).val();
                                $select_time_to.val(end_time >= first_value ? end_time : first_value);
                            });

                            var firstStepValidator = function(button_type) {
                                var valid           = true,
                                    $select_wrap    = $select_service.parent(),
                                    $time_wrap_from = $select_time_from.parent(),
                                    $time_wrap_to   = $select_time_to.parent(),
                                    $scroll_to      = null;

                                $service_error.hide();
                                $time_error.hide();
                                $select_wrap.removeClass('ab-error');
                                $time_wrap_from.removeClass('ab-error');
                                $time_wrap_to.removeClass('ab-error');

                                // service validation
                                if (!$select_service.val()) {
                                    valid = false;
                                    $select_wrap.addClass('ab-error');
                                    $service_error.show();
                                    $scroll_to = $select_wrap;
                                }

                                // date validation
                                $date_from.css('borderColor', $date_from.val() ? '' : 'red');
                                if (!$date_from.val()) {
                                    valid = false;
                                    if ($scroll_to === null) {
                                        $scroll_to = $date_from;
                                    }
                                }

                                // time validation
                                if (button_type !== 'mobile' && $select_time_from.val() == $select_time_to.val()) {
                                    valid = false;
                                    $time_wrap_from.addClass('ab-error');
                                    $time_wrap_to.addClass('ab-error');
                                    $time_error.show();
                                    if ($scroll_to === null) {
                                        $scroll_to = $time_wrap_from;
                                    }
                                }

                                // week days
                                if (!$('.ab-week-day:checked', $container).length) {
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

                                if (firstStepValidator('simple')) {

                                    var ladda = Ladda.create(this);
                                    ladda.start();

                                    // Prepare staff ids.
                                    var staff_ids = [];
                                    if ($select_staff.val()) {
                                        staff_ids.push($select_staff.val());
                                    } else {
                                        $select_staff.find('option').each(function() {
                                            if (this.value) {
                                                staff_ids.push(this.value);
                                            }
                                        });
                                    }
                                    // Prepare days.
                                    var days = [];
                                    $('.ab-week-days .active input.ab-week-day', $container).each(function() {
                                        days.push(this.value);
                                    });

                                    $.ajax({
                                        url  : Options.ajaxurl,
                                        data : {
                                            action            : 'ab_session_save',
                                            form_id           : Options.form_id,
                                            service_id        : $select_service.val(),
                                            number_of_persons : $select_nop.val(),
                                            staff_ids         : staff_ids,
                                            date_from         : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                            days              : days,
                                            time_from         : $select_time_from.val(),
                                            time_to           : $select_time_to.val()
                                        },
                                        dataType : 'json',
                                        xhrFields : { withCredentials: true },
                                        crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                        success : function (response) {
                                            secondStep();
                                        }
                                    });
                                }
                            });

                            //
                            $mobile_next_step.on('click', function () {
                                if (firstStepValidator('mobile')) {
                                    if (Options.skip_date_time) {
                                        var ladda = Ladda.create(this);
                                        ladda.start();
                                        $next_step.trigger('click');
                                    } else {
                                        $('.ab-mobile-step_1', $container).hide();
                                        $('.ab-mobile-step_2', $container).css('display', 'block');
                                        if (Options.skip_service) {
                                            $mobile_prev_step.remove();
                                        }
                                        scrollTo($container);
                                    }
                                }

                                return false;
                            });

                            //
                            $mobile_prev_step.on('click', function () {
                                $('.ab-mobile-step_1', $container).show();
                                $('.ab-mobile-step_2', $container).hide();

                                if ($select_service.val()) {
                                    $('.ab-select-service', $container).parent().removeClass('ab-error');
                                }
                                return false;
                            });

                            if (Options.skip_first_step) {
                                $next_step.trigger('click');
                            } else if (Options.skip_service) {
                                $mobile_next_step.trigger('click');
                            }
                        }
                    } // ajax success
                }); // ajax
            }
        }

        var xhr_render_time = null;
        function secondStep(time_is_busy, selected_date) {
            if( xhr_render_time != null ) {
                xhr_render_time.abort();
                xhr_render_time = null;
            }
            xhr_render_time = $.ajax({
                url         : Options.ajaxurl,
                data :      { action: 'ab_render_time', form_id: Options.form_id, selected_date: selected_date },
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success == false) {
                        // The session doesn't contain data.
                        firstStep();
                        return;
                    }
                    $container.html(response.html);

                    var $columnizer_wrap = $('.ab-columnizer-wrap', $container),
                        $columnizer      = $('.ab-columnizer', $container),
                        $next_button     = $('.ab-time-next', $container),
                        $prev_button     = $('.ab-time-prev', $container),
                        slot_height      = 35,
                        column_width     = 127,
                        columns          = 0,
                        screen_index     = 0,
                        $current_screen  = null,
                        $screens,
                        slots_per_column,
                        columns_per_screen,
                        has_more_slots      = response.has_more_slots,
                        show_day_per_column = response.day_one_column;

                    // 'BACK' button.
                    $('.ab-to-first-step', $container).on('click', function(e) {
                        e.preventDefault();
                        var ladda = Ladda.create(this);
                        ladda.start();
                        firstStep();
                    }).toggle(!Options.skip_first_step);

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
                                    var selected_date = this.get('select', 'yyyy-mm-dd');
                                    if (response.slots[selected_date]) {
                                        // Get data from response.slots.
                                        $columnizer.html(response.slots[selected_date]).css('left', '0px');
                                        columns = 0;
                                        screen_index = 0;
                                        $current_screen = null;
                                        initSlots();
                                        $prev_button.hide();
                                        $next_button.toggle($screens.length != 1);
                                    } else {
                                        // Load new data from server.
                                        secondStep(false, selected_date);
                                        showSpinner();
                                    }
                                }
                            },
                            onClose: function() {
                                this.open(false);
                            },
                            onRender: function(){
                                var selected_date = new Date(Date.UTC(this.get('view').year, this.get('view').month));
                                $('.picker__nav--next').on('click', function(){
                                    selected_date.setMonth(selected_date.getMonth() + 1);
                                    secondStep(false, selected_date.toJSON().substr(0, 10));
                                    showSpinner();
                                });
                                $('.picker__nav--prev').on('click', function(){
                                    selected_date.setMonth(selected_date.getMonth() - 1);
                                    secondStep(false, selected_date.toJSON().substr(0, 10));
                                    showSpinner();
                                });
                            }
                        });
                        // Insert slots for selected day.
                        var selected_date = $input.pickadate('picker').get('select', 'yyyy-mm-dd');
                        $columnizer.html(response.slots[selected_date]);
                    } else {
                        // Insert all slots.
                        var slots = '';
                        $.each(response.slots, function(group, group_slots) {
                            slots += group_slots;
                        });
                        $container.find('.ab-columnizer').html( slots );
                    }

                    if (response.has_slots) {
                        if (time_is_busy) {
                            $container.prepend(time_is_busy);
                        }

                        // Calculate number of slots per column.
                        slots_per_column = parseInt($(window).height() / slot_height, 10);
                        if (slots_per_column < 4) {
                            slots_per_column = 4;
                        } else if (slots_per_column > 10) {
                            slots_per_column = 10;
                        }
                        // Calculate number of columns per screen.
                        columns_per_screen = parseInt(
                            ($container.width() - (
                                Options.show_calendar && $(window).width() >= 650 ? 310 : 0
                            )) / column_width, 10
                        );
                        if (columns_per_screen > 10) {
                            columns_per_screen = 10;
                        }

                        initSlots();

                        if (!has_more_slots && $screens.length == 1) {
                            $next_button.hide();
                        }

                        var hammertime = $('.ab-second-step', $container).hammer({ swipe_velocity: 0.1 });

                        hammertime.on('swipeleft', function() {
                            if ($next_button.is(':visible')) {
                                $next_button.trigger('click');
                            }
                        });

                        hammertime.on('swiperight', function() {
                            if ($prev_button.is(':visible')) {
                                $prev_button.trigger('click');
                            }
                        });

                        $next_button.on('click', function (e) {
                            $prev_button.show();
                            if ($screens.eq(screen_index + 1).length) {
                                $columnizer.animate(
                                    { left: '-=' + $current_screen.width() },
                                    { duration: 800 }
                                );
                                $current_screen = $screens.eq(++ screen_index);
                                $columnizer_wrap.animate(
                                    { height: $current_screen.height() },
                                    { duration: 800 }
                                );

                                if (screen_index + 1 == $screens.length && !has_more_slots) {
                                    $next_button.hide();
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
                                        action: 'ab_render_next_time',
                                        form_id: options.form_id,
                                        last_slot: $button.val()
                                    },
                                    ladda = Ladda.create(document.querySelector('.ab-time-next'));

                                ladda.start();
                                $.ajax({
                                    type : 'POST',
                                    url  : options.ajaxurl,
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
                                                if ($('button.ab-available-day[value="' + $first_day.attr('value') + '"]', $container).length) {
                                                    $html = $html.not(':first');
                                                }
                                                $columnizer.append($html);
                                                initSlots();
                                                $next_button.trigger('click');
                                            } else { // no available time
                                                $next_button.hide();
                                            }
                                        } else { // no available time
                                            $next_button.hide();
                                        }
                                        ladda.stop();
                                    }
                                });
                            }
                        });

                        $prev_button.on('click', function () {
                            $next_button.show();
                            $current_screen = $screens.eq(-- screen_index);
                            $columnizer.animate(
                                { left: '+=' + $current_screen.width() },
                                { duration: 800 }
                            );
                            $columnizer_wrap.animate(
                                { height: $current_screen.height() },
                                { duration: 800 }
                            );
                            if (screen_index === 0) {
                                $prev_button.hide();
                            }
                        });
                    }
                    // skip scroll when first step is hidden
                    if (!Options.skip_first_step) {
                        scrollTo($container);
                    }

                    function showSpinner(){
                        $('.ab-time-screen,.ab-not-time-screen', $container).addClass('ab-spin-overlay');
                        var opts = {
                            lines: 11, // The number of lines to draw
                            length: 11, // The length of each line
                            width: 4, // The line thickness
                            radius: 5 // The radius of the inner circle
                        };
                        new Spinner(opts).spin($screens.eq(screen_index).get(0));
                    }

                    function initSlots() {
                        var $buttons     = $('> button', $columnizer),
                            slots_count  = 0,
                            max_slots    = 0,
                            $button,
                            $column,
                            $screen;

                        if (show_day_per_column) {
                            /**
                             * Create columns for 'Show each day in one column' mode.
                             */
                            while ($buttons.length > 0) {
                                // Create column.
                                if ($buttons.eq(0).hasClass('ab-available-day')) {
                                    slots_count = 1;
                                    $column = $('<div class="ab-column" />');
                                    $button = $($buttons.splice(0, 1));
                                    $button.addClass('ab-first-child');
                                    $column.append($button);
                                } else {
                                    slots_count ++;
                                    $button = $($buttons.splice(0, 1));
                                    // If it is last slot in the column.
                                    if (!$buttons.length || $buttons.eq(0).hasClass('ab-available-day')) {
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
                            while ( has_more_slots ? $buttons.length > slots_per_column : $buttons.length ) {
                                $column = $('<div class="ab-column" />');
                                max_slots = slots_per_column;
                                if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('ab-available-day')) {
                                    // If this is the first column of a screen and the first slot in this column is not day
                                    // then put 1 slot less in this column because createScreens adds 1 more
                                    // slot to such columns.
                                    -- max_slots;
                                }
                                for (var i = 0; i < max_slots; ++ i) {
                                    if (i + 1 == max_slots && $buttons.eq(0).hasClass('ab-available-day')) {
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
                                    if (!$first_slot.hasClass('ab-available-day')) {
                                        var group = $first_slot.data('group'),
                                            $group_slot = $('button.ab-available-day[value="' + group + '"]:last', $container);
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
                        $('button.ab-available-hour', $container).off('click').on('click', function (e) {
                            e.preventDefault();
                            var $this = $(this),
                                data = {
                                    action: 'ab_session_save',
                                    appointment_datetime: $this.val(),
                                    staff_ids: [$this.data('staff_id')],
                                    form_id: options.form_id
                                },
                                ladda = Ladda.create(this);

                            ladda.start();
                            $.ajax({
                                type : 'POST',
                                url  : options.ajaxurl,
                                data : data,
                                dataType : 'json',
                                xhrFields : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success : function (response) {
                                    thirdStep();
                                }
                            });
                        });

                        // Columnizer width & height.
                        $('.ab-second-step', $container).width(function() {
                            if (Options.show_calendar && $(window).width() >= 650) {
                                return parseInt(($container.width() - 310) / column_width, 10) * column_width;
                            } else {
                                return cols_per_screen * column_width;
                            }
                        });
                        $columnizer_wrap.height($current_screen.height());
                    }
                }
            });
        }

        //
        function thirdStep() {
            $.ajax({
                url         : Options.ajaxurl,
                data        : { action: 'ab_render_details', form_id: Options.form_id },
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        scrollTo($container);

                        // Init
                        var $button_next    = $('.ab-to-fourth-step', $container),
                            $back_button    = $('.ab-to-second-step', $container),
                            $phone_field    = $('.ab-user-phone', $container),
                            $email_field    = $('.ab-user-email', $container),
                            $name_field     = $('.ab-full-name', $container),
                            $phone_error    = $('.ab-user-phone-error', $container),
                            $email_error    = $('.ab-user-email-error', $container),
                            $name_error     = $('.ab-full-name-error', $container),
                            $errors         = $('.ab-user-phone-error, .ab-user-email-error, .ab-full-name-error, div.ab-custom-field-error', $container),
                            $fields         = $('.ab-user-phone, .ab-user-email, .ab-full-name, .ab-custom-field', $container)
                        ;

                        $phone_field.intlTelInput({
                            preferredCountries: [Options.country],
                            defaultCountry: Options.country,
                            geoIpLookup: function(callback) {
                                $.get(Options.ajaxurl, {action: 'ab_ip_info'}, function() {}, 'json').always(function(resp) {
                                    var countryCode = (resp && resp.country) ? resp.country : '';
                                    callback(countryCode);
                                });
                            },
                            utilsScript: Options.intlTelInput_utils
                        });
                        $button_next.on('click', function(e) {
                            e.preventDefault();
                            var custom_fields_data = [],
                                checkbox_values
                            ;

                            $.each(Options.custom_fields, function(i, field) {
                                switch (field.type) {
                                    case 'text-field':
                                        custom_fields_data.push({
                                            id      : field.id,
                                            value   : $('input[name="ab-custom-field-' + field.id + '"]', $container).val()
                                        });
                                        break;
                                    case 'textarea':
                                        custom_fields_data.push({
                                            id      : field.id,
                                            value   : $('textarea[name="ab-custom-field-' + field.id + '"]', $container).val()
                                        });
                                        break;
                                    case 'checkboxes':
                                        if ($('input[name="ab-custom-field-' + field.id + '"][type=checkbox]:checked', $container).length) {
                                            checkbox_values = [];
                                            $('input[name="ab-custom-field-' + field.id + '"][type=checkbox]:checked', $container).each(function () {
                                                checkbox_values.push($(this).val());
                                            });
                                            custom_fields_data.push({
                                                id      : field.id,
                                                value   : checkbox_values
                                            });
                                        }
                                        break;
                                    case 'radio-buttons':
                                        if ($('input[name="ab-custom-field-' + field.id + '"][type=radio]:checked', $container).length) {
                                            custom_fields_data.push({
                                                id      : field.id,
                                                value   : $('input[name="ab-custom-field-' + field.id + '"][type=radio]:checked', $container).val()
                                            });
                                        }
                                        break;
                                    case 'drop-down':
                                        custom_fields_data.push({
                                            id      : field.id,
                                            value   : $('select[name="ab-custom-field-' + field.id + '"] > option:selected', $container).val()
                                        });
                                        break;
                                }
                            });

                            var data = {
                                    action        : 'ab_session_save',
                                    form_id       : Options.form_id,
                                    name          : $name_field.val(),
                                    phone         : $phone_field.intlTelInput('getNumber'),
                                    email         : $email_field.val(),
                                    custom_fields : JSON.stringify(custom_fields_data)
                                },
                                ladda = Ladda.create(this);

                            ladda.start();
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
                                    $fields.removeClass('ab-details-error');

                                    if (response.length == 0) {
                                        fourthStep();
                                    } else {
                                        ladda.stop();
                                        var $scroll_to = null;
                                        if (response.name) {
                                            $name_error.html(response.name);
                                            $name_field.addClass('ab-details-error');
                                            $scroll_to = $name_field;
                                        }
                                        if (response.phone) {
                                            $phone_error.html(response.phone);
                                            $phone_field.addClass('ab-details-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $phone_field;
                                            }
                                        }
                                        if (response.email) {
                                            $email_error.html(response.email);
                                            $email_field.addClass('ab-details-error');
                                            if ($scroll_to === null) {
                                                $scroll_to = $email_field;
                                            }
                                        }
                                        if (response.custom_fields) {
                                            $.each(response.custom_fields, function(key, value) {
                                                $('.' + key + '-error', $container).html(value);
                                                $('[name=' + key + ']', $container).addClass('ab-details-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $('[name=' + key + ']', $container);
                                                }
                                            });
                                        }
                                        if ($scroll_to !== null) {
                                            scrollTo($scroll_to);
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
                }
            });
        }

        //
        function fourthStep() {
            $.ajax({
                url        : Options.ajaxurl,
                data       : {action: 'ab_render_payment', form_id: Options.form_id},
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

                        if (Options.is_cancelled) {
                            Options.is_cancelled = false;
                        }

                        var $local_pay = $('.ab-local-payment', $container),
                            $local_pay_button = $('.ab-local-pay-button', $container),
                            $back_button = $('.ab-to-third-step', $container),
                            $buttons = $('.ab-paypal-payment-button,.ab-card-payment-button,form.ab-authorizenet,form.ab-stripe,.ab-local-pay-button', $container)
                            ;

                        $local_pay.on('click', function () {
                            $buttons.hide();
                            $local_pay_button.show();
                        });


                        $('.ab-final-step', $container).on('click', function (e) {
                            var ladda = Ladda.create(this);

                            if ($('.ab-local-payment', $container).is(':checked') || $(this).hasClass('ab-coupon-payment')) { // handle only if was selected local payment !
                                e.preventDefault();
                                ladda.start();
                                save();
                            }
                        });

                        $back_button.on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();

                            thirdStep();
                        });
                    }
                }
            });
        }

        //
        function fifthStep() {
            $.ajax({
                url         : Options.ajaxurl,
                data        : { action : 'ab_render_complete', form_id : Options.form_id },
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        if (Options.is_available || Options.is_finished) {
                            if (response.final_step_url) {
                                document.location.href = response.final_step_url;
                            } else {
                                $container.html(response.html.success);
                                scrollTo($container);
                            }

                            Options.is_finished = false;
                        } else {
                            secondStep(response.html.error);
                        }
                    }
                }
            });
        }

        // =========== helpers ===================

        function hideByAttributes() {
            if (Options.skip_first_step) {
                $('.ab-first-step', $container).hide();
            }
            if (Options.attributes.hide_categories && Options.attributes.category_id) {
                $('.ab-category', $container).hide();
            }
            if (Options.attributes.hide_services && Options.attributes.service_id) {
                $('.ab-service', $container).hide();
            }
            if (Options.attributes.hide_staff_members) {
                $('.ab-employee', $container).hide();
            }
            if (Options.attributes.hide_date_and_time) {
                $('.ab-available-date', $container).parent().hide();
            }
            if (!Options.attributes.show_number_of_persons) {
                $('.ab-number-of-persons', $container).hide();
            }
            if (Options.attributes.show_number_of_persons &&
                !Options.attributes.hide_staff_members &&
                !Options.attributes.hide_services &&
                !Options.attributes.hide_categories) {
                $('.ab-mobile-step_1', $container).addClass('ab-four-cols');
            }
        }

        // insert data into select
        function setSelect($select, data, leave_selected) {
            var selected = $select.val();
            var reset    = true;
            // reset select
            $('option:not([value=""])', $select).remove();
            // and fill the new data
            var docFragment = document.createDocumentFragment();

            function valuesToArray(obj) {
                return Object.keys(obj).map(function (key) { return obj[key]; });
            }

            function compare(a, b) {
                if (parseInt(a.position) < parseInt(b.position))
                    return -1;
                if (parseInt(a.position) > parseInt(b.position))
                    return 1;
                return 0;
            }

            // sort select by position
            data = valuesToArray(data).sort(compare);

            $.each(data, function(id, object) {
                id = object.id;

                if (selected === id && leave_selected) {
                    reset = false;
                }
                var option = document.createElement('option');
                option.value = id;
                option.text = object.name;
                docFragment.appendChild(option);
            });
            $select.append(docFragment);
            // set default value of select
            $select.val(reset ? '' : selected);
        }

        //
        function save() {
            $.ajax({
                type        : 'POST',
                url         : Options.ajaxurl,
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                data        : { action  : 'ab_save_appointment', form_id : Options.form_id },
                dataType    : 'json'
            }).done(function(response) {
                Options.is_available = response.success;
                fifthStep();
            });
        }

        /**
         * Scroll to element if it is not visible.
         *
         * @param $elem
         */
        function scrollTo( $elem ) {
            var elemTop   = $elem.offset().top;
            var scrollTop = $(window).scrollTop();
            if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
                $('html,body').animate({ scrollTop: (elemTop - 24) }, 500);
            }
        }

    }

})(jQuery);
