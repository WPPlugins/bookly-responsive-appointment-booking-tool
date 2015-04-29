jQuery(function ($) {
    // Resolve conflict between Bootstrap and jQuery UI in favor of jQuery UI.
    $.fn.button.noConflict();

    var $week_calendar_wrapper            = $('#week_calendar_wrapper'),
        $week_calendar                    = $week_calendar_wrapper.find('.ab-calendar-element'),
        $tabs                             = $week_calendar_wrapper.find('.nav-tabs'),
        $day_calendar_wrapper             = $('#day_calendar_wrapper'),
        $day_calendar                     = $day_calendar_wrapper.find('.ab-calendar-element'),
        $first_day_of_week                = parseInt($('#ab_calendar_data_holder .ab-calendar-first-day').text(), 10),
        $time_format                      = $('#ab_calendar_data_holder .ab-calendar-time-format').text(),
        $staff_tabs                       = $('.ab-calendar-tab'),
    // Staff filter vars
        $staff_filter_button              = $('#ab-staff-button'),
        $all_staff_option                 = $('#ab-filter-all-staff'),
        $staff_option                     = $('.ab-staff-option'),

        $calendar_common_options          = {
            timeslotsPerHour: BooklyL10n.timeslotsPerHour,
            timeslotHeight: 25,
            scrollToHourMillis : 0,
            businessHours: {start: 8, end: 18},
            firstDayOfWeek: $first_day_of_week,
            hourLine: true,
            displayFreeBusys: true,
            useShortDayNames: true,
            showHeader: false,
            headerSeparator: '',
            dateFormat: ', M d',
            timeFormat: $time_format,
            use24Hour: ($time_format.toLowerCase().indexOf('a') ==  -1),
            newEventText: BooklyL10n.new_appointment,
            allowEventDelete: true,
            draggable: function(calEvent, element) {
                return false;
            },
            resizable: function(calEvent, element) {
                return false;
            },
            eventDelete: function(calEvent, element, dayFreeBusyManager, calendar, clickEvent) {
                if (confirm(BooklyL10n.are_you_sure)) {
                    $.post(ajaxurl, {'action' : 'ab_delete_appointment', 'appointment_id' : calEvent.id }, function () {
                        calendar.weekCalendar('removeEvent', calEvent.id);
                    });
                }
            },
            eventRender : function(calEvent, $event) {
                $event.css('backgroundColor', calEvent.color);
                $event.find('.wc-time').css({
                    backgroundColor   : calEvent.color,
                    borderLeftColor   : calEvent.color,
                    borderRightColor  : calEvent.color,
                    borderTopColor    : calEvent.color,
                    borderBottomColor : '#ABABAB'
                });
            },
            eventAfterRender : function(calEvent, $calEventList) {
                $calEventList.each(function () {
                    var $calEvent   = $(this);
                    var titleHeight = $calEvent.find('.wc-title').height();
                    var origHeight  = $calEvent.height();

                    if ( origHeight < titleHeight ) {
                        var $info   = $('<div class="wc-information"/>');
                        var $delete = $('.wc-cal-event-delete', $calEvent);

                        $delete.hide();
                        $('.wc-time', $calEvent).prepend($info);

                        // mouse handlers
                        $info.on('mouseenter', function () {
                            $calEvent.css({height: (titleHeight + 30), 'z-index': 1});
                            $info.hide();
                            $delete.show();
                        });
                        $calEvent.on('mouseleave', function () {
                            $calEvent.css({height: origHeight, 'z-index': 'auto'});
                            $delete.hide();
                            $info.show();
                        });
                    }
                });
            },
            eventBody : function(calEvent, calendar) {
                var body = '<div class="wc-service-name">' + calEvent.title + '</div>';
                if (calEvent.desc) {
                    body += calEvent.desc;
                }
                return body;
            }
        },
        $week_calendar_options = {
            daysToShow: 7,
            eventNew: function(calEvent, element, dayFreeBusyManager, calendar, mouseupEvent) {
                element.hide().remove();
                showAppointmentDialog(
                    null,
                    $week_calendar_wrapper.find('.ab-calendar-tab.active').data('staff-id'),
                    calEvent.start,
                    null,
                    calendar,
                    'week',
                    calEvent.notes
                );
            },
            eventClick: function(calEvent, element, dayFreeBusyManager, calendar, clickEvent) {
                showAppointmentDialog(
                    calEvent.id,
                    $week_calendar_wrapper.find('.ab-calendar-tab.active').data('staff-id'),
                    calEvent.start,
                    calEvent.end,
                    calendar,
                    'week',
                    calEvent.notes
                );
            },
            data: (function() {
                var xhr;

                return function(start, end, callback) {
                    if (xhr && xhr.readyState != 4) {
                        xhr.abort();
                    }
                    $.post(
                        ajaxurl,
                        {
                            action     : 'ab_week_staff_appointments',
                            start_date : getFormattedDateForCalendar(start),
                            end_date   : getFormattedDateForCalendar(end),
                            staff_id   : $week_calendar_wrapper.find('.ab-calendar-tab.active').data('staff-id')
                        },
                        function (response) {
                            var appointments = $.map(response.events, function(value) {
                                return {
                                    id    : parseInt(value.id, 10),
                                    start : new Date(value.start),
                                    end   : new Date(value.end),
                                    title : value.title,
                                    desc  : value.desc,
                                    color : value.color,
                                    notes : value.notes ? value.notes : false
                                };
                            });
                            var free_busys = $.map(response.freebusys, function(value) {
                                return {
                                    start : new Date(value.start),
                                    end   : new Date(value.end),
                                    free  : value.free,
                                    notes : value.notes ? value.notes : false
                                };
                            });

                            callback({ events: appointments, freebusys: free_busys });
                        },
                        'json'
                    );
                };
            })(),
            height: function() {
                var $window_height             = $(window).height(),
                    $wp_admin_bar_height       = $('#wpadminbar').height(),
                    $ab_calendar_header_height = $('#ab_calendar_header').height(),
                    $ab_calendar_tabs_height   = $('#week_calendar_wrapper .tabbable').outerHeight(true),
                    $height_to_reduce          = $wp_admin_bar_height + $ab_calendar_header_height + $ab_calendar_tabs_height,
                    $wrap                      = $('#wpbody-content .wrap');

                if ($wrap.css('margin-top')) {
                    $height_to_reduce += parseInt($wrap.css('margin-top').replace('px', ''), 10);
                }

                if ($wrap.css('margin-bottom')) {
                    $height_to_reduce += parseInt($wrap.css('margin-bottom').replace('px', ''), 10);
                }

                return $window_height - $height_to_reduce;
            }
        },
        $day_calendar_options = {
            data: (function() {
                var xhr;

                return function(start, end, callback) {
                    var selected_staff_ids = [];
                    $('.ab-staff-option:checked').each(function() {
                        selected_staff_ids.push(this.value);
                    });
                    var data = {
                        action     : 'ab_day_staff_appointments',
                        start_date : getFormattedDateForCalendar(start),
                        staff_id   : selected_staff_ids
                    };
                    if (xhr && xhr.readyState != 4) {
                        xhr.abort();
                    }
                    xhr = $.post(
                        ajaxurl, data, function (response) {
                            var appointments = $.map(response.events, function(value) {
                                return {
                                    id     : parseInt(value.id, 10),
                                    start  : new Date(value.start),
                                    end    : new Date(value.end),
                                    title  : value.title,
                                    color  : value.color,
                                    desc   : value.desc,
                                    userId : parseInt(value.userId, 10),
                                    notes  : value.notes ? value.notes : false
                                };
                            });
                            var free_busys = $.map(response.freebusys, function(value) {
                                return {
                                    start  : new Date(value.start),
                                    end    : new Date(value.end),
                                    free   : value.free,
                                    userId : parseInt(value.userId, 10),
                                    notes  : value.notes ? value.notes : false
                                };
                            });

                            callback({ events: appointments, freebusys: free_busys });
                        },
                        'json'
                    );
                };
            })(),
            users: [],
            getUserId: function(user, index, calendar) {
                return user.staff_id;
            },
            getUserName: function(user, index, calendar) {
                return user.full_name;
            },
            daysToShow: 1,
            height: function() {
                var $window_height             = $(window).height(),
                    $wp_admin_bar_height       = $('#wpadminbar').height(),
                    $ab_calendar_header_height = $('#ab_calendar_header').height(),
                    $height_to_reduce          = $wp_admin_bar_height + $ab_calendar_header_height + $('#day_calendar_wrapper').outerHeight(true),
                    $wrap                      = $('#wpbody-content .wrap');

                if ($wrap.css('margin-top')) {
                    $height_to_reduce += parseInt($wrap.css('margin-top').replace('px', ''), 10);
                }

                if ($wrap.css('margin-bottom')) {
                    $height_to_reduce += parseInt($wrap.css('margin-bottom').replace('px', ''), 10);
                }

                return $window_height - $height_to_reduce;
            },
            eventNew: function(calEvent, element, dayFreeBusyManager, calendar, mouseupEvent) {
                element.hide().remove();
                showAppointmentDialog(
                    null,
                    calEvent.userId,
                    calEvent.start,
                    null,
                    calendar,
                    'day',
                    calEvent.notes
                );
            },
            eventClick: function(calEvent, element, dayFreeBusyManager, calendar, clickEvent) {
                showAppointmentDialog(
                    calEvent.id,
                    calEvent.userId,
                    calEvent.start,
                    calEvent.end,
                    calendar,
                    'day',
                    calEvent.notes
                );
            }
        };

    // Datepickers.
    var week_picker = new WeekPicker();
    var day_picker  = new DayPicker();
    week_picker.attachCalendar($week_calendar);

    // week calendar
    $week_calendar.weekCalendar($.extend({}, $calendar_common_options, $week_calendar_options));

    // click on tabs
    $tabs.find('.ab-calendar-tab').on('click', function(e) {
        e.stopPropagation();
        $('.ab-calendar-tab').removeClass('active');
        $(this).addClass('active');
        // prevents console error of initialization
        $week_calendar.weekCalendar(); $week_calendar.weekCalendar('refresh');
    });

    // today
    $('.ab-nav-calendar .ab-calendar-today').on('click', function(){
        var $active_view_button = $('.ab-nav-calendar .ab-calendar-switch-view.ab-button-active');
        if ($active_view_button.hasClass('ab-calendar-day')) {
            day_picker.setDate(new Date());
        } else {
            week_picker.setDate(new Date());
        }
    });

    // day/week view
    $('.ab-nav-calendar .ab-calendar-switch-view').on('click', function() {
        var $this = $(this);

        if ($this.hasClass('ab-button-active')) {
            return false;
        }
        $('.ab-nav-calendar .ab-calendar-switch-view').not($this).removeClass('ab-button-active');
        $('.ab-nav-calendar .ab-calendar-today').removeClass('ab-button-active');
        $this.addClass('ab-button-active');

        // Switch to day-view
        if ($this.hasClass('ab-calendar-day')) {
            $week_calendar_wrapper.hide();
            $week_calendar.remove();
            week_picker.hide();

            var $day_calendar_container = $day_calendar_wrapper.find('.ab-calendar-element-container'),
                date_from_week_picker = week_picker.getStartDate(),

                /**
                 * Checks if current date belongs to selected week of week-picker
                 *
                 * @return bool
                 */
                    is_current_week = function() {
                    var now_date = new Date(),
                        first_day_of_week = startAndEndOfWeek(date_from_week_picker).first_day_of_week,
                        last_day_of_week = startAndEndOfWeek(date_from_week_picker).last_day_of_week;

                    return first_day_of_week <= now_date && now_date <= last_day_of_week;
                },

                /**
                 * result is an object of dates for start (monday)
                 *   and end (sunday) of week based on supplied date object
                 *
                 * @return object
                 */
                    startAndEndOfWeek = function(date) {
                    var result = {
                        first_day_of_week : null,
                        last_day_of_week  : null
                    };

                    // If no date object supplied, use current date
                    // Copy date so don't modify supplied date
                    var now = date ? new Date(date) : new Date();

                    // set time to some convenient value
                    now.setHours(0, 0, 0, 0);

                    // Get the previous Monday
                    var monday = new Date(now);
                    monday.setDate(monday.getDate() - monday.getDay() + 1);

                    // Get next Sunday
                    var sunday = new Date(now);
                    sunday.setDate(sunday.getDate() - sunday.getDay() + 7);

                    // set result's days
                    result.first_day_of_week = monday;
                    result.last_day_of_week  = sunday;

                    return result;
                };

            // Set visible users.
            var users = [];
            $('.ab-staff-option:checked').each(function() {
                users.push({staff_id: parseInt(this.value), full_name: $(this).next().text()});
            });
            $day_calendar = $('<div class="ab-calendar-element" />').appendTo($day_calendar_container);
            $day_calendar.weekCalendar($.extend({date: date_from_week_picker}, $calendar_common_options, $day_calendar_options, {users: users}));
            $day_calendar_wrapper.show();

            day_picker.attachCalendar($day_calendar);

            // if week is current - set current date, otherwise set date from week-picker
            is_current_week() ?
                day_picker.setDate(new Date(), true) :
                day_picker.setDate(date_from_week_picker, true);

            day_picker.show();

            // styles-fixing
            $('td.wc-scrollbar-shim').hide();
            // Switch to week view
        } else {
            $day_calendar_wrapper.hide();
            $day_calendar.remove();
            day_picker.hide();

            var $week_calendar_container = $week_calendar_wrapper.find('.ab-calendar-element-container'),
                date_from_day_picker = day_picker.getDate();

            // Show tabs based on selected staff members.
            var active_set = false;
            $staff_option.each(function() {
                if (this.checked) {
                    $('.ab-staff-tab-' + this.value).show().toggleClass('active', active_set === false);
                    active_set = true;
                } else {
                    $('.ab-staff-tab-' + this.value).hide().removeClass('active');
                }
            });

            $week_calendar = $('<div class="ab-calendar-element" />').appendTo($week_calendar_container);
            if (active_set) {
                $week_calendar_wrapper.show();
            }
            $week_calendar.weekCalendar($.extend({date: date_from_day_picker}, $calendar_common_options, $week_calendar_options));
            scrollShim = document.querySelector('.wc-scrollbar-shim');
            if ( scrollShim !== null ) scrollShim.style.width = scrollWidth + 'px';

            // Set date from day calendar
            week_picker.setDate(date_from_day_picker, false);
            week_picker.attachCalendar($week_calendar);
            week_picker.show();
        } // end of Week View
    });

    // Staff filter
    $('.ab-staff-filter-button').on('click', function (e) {
        e.stopPropagation();
        var menu = $(this).parent().find('.dropdown-menu');
        if (menu.hasClass('open')) {
            menu.removeClass('open').hide();
        } else {
            menu.addClass('open').show();
        }
    });

    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
    });

    $all_staff_option.on('change', function () {
        $staff_option.prop('checked', this.checked);
        if (this.checked) {
            $staff_option.filter(':first').trigger('change');
            $staff_tabs.show();
        } else {
            $week_calendar_wrapper.hide();
            $day_calendar_wrapper.hide();
        }
    });

    $staff_option.on('change', function (e) {
        var self = $(this),
            $tab = $('.ab-staff-tab-' + self.val()),
            $active_tab = $('ul.nav-tabs').find('li.active'),
            is_day = $('button.ab-calendar-day').hasClass('ab-button-active'),
            staff_option_checked = parseInt($staff_option.filter(':checked').length),
            unchecked_options = [];

        $all_staff_option.prop('checked', $staff_option.filter(':not(:checked)').length == 0);

        if (is_day) { // Day
            // checkboxes
            if (staff_option_checked) {
                $day_calendar_wrapper.show();
                // Refresh visible users in calendar.
                var users = [];
                $('.ab-staff-option:checked').each(function() {
                    users.push({staff_id: parseInt(this.value), full_name: $(this).next().text()});
                });
                $day_calendar.weekCalendar('option', 'users', users);
                $day_calendar.weekCalendar('refresh');
                // css-fix
                $('td.wc-scrollbar-shim').hide();
            } else { // No staff selected
                $day_calendar_wrapper.hide();
            }
        } else { // Week
            if (this.checked) {
                $tab.show().click();
                $staff_option.each(function(k, v) {
                    if ($(v).is(':not(:checked)') && !unchecked_options[$(v).val()]) {
                        unchecked_options.push($(v).val());
                    }
                });
                $active_tab.parent().find('li').each(function(k, v) {
                    unchecked_options.forEach(function(option) {
                        if ($(v).data('staff-id') == option) {
                            $('ul.nav-tabs').find('li').filter('[data-staff-id="' + option + '"]').hide();
                        }
                    });
                });
            } else {
                $tab.hide();
                $active_tab.is(':visible') ? $active_tab.click() : $staff_tabs.filter(':visible').filter(':first').click();
            }
            staff_option_checked ? $week_calendar_wrapper.show() : $week_calendar_wrapper.hide();
        }

        // Changes staff filter button name
        var selected_staff_numb = $staff_option.filter(':checked').length;
        if (selected_staff_numb == 0) {
            $staff_filter_button.text('No staff selected');
        } else if (selected_staff_numb == 1) {
            $staff_filter_button.text($("label[for='" + $staff_option.filter(':checked').attr('id') + "']").text());
        } else {
            $staff_filter_button.text(selected_staff_numb + ' staff members');
        }
    });

    // End staff filter

    /**
     * Get formatted date(php: Y-m-d H:i:s) for calendar
     *
     * @param date
     * @return {String}
     */
    function getFormattedDateForCalendar(date) {
        var $hours = date.getHours(), $minutes = date.getMinutes();

        if ($hours < 10) {
            $hours = '0' + $hours;
        }

        if ($minutes < 10) {
            $minutes = '0' + $minutes;
        }

        return $.datepicker.formatDate( 'yy-mm-dd ', date ) + $hours + ':' + $minutes + ':00';
    }


    /*
     * scroll width
     *
     * not null for preventing console errors when no one staff exists
     */
    var scroll = document.querySelector('.wc-scrollable-grid'),
        scrollShim = document.querySelector('.wc-scrollbar-shim'),
        scrollWidth = scroll !== null ? scroll.offsetWidth - scroll.clientWidth : 0;

    if ( scrollShim !== null ) scrollShim.style.width = scrollWidth + 'px';

    /* firefox bug border */
    if ( $.browser.mozilla )
        $('.wc-time-column-header:first-child').css('width','43px');

    $('#email_notification').on('click', function() {
       $('#email_notification_text').show();
    });
});