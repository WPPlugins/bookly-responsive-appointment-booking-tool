jQuery(function ($) {

    var Calendar = function($container, options) {
        var obj  = this;
        jQuery.extend(obj.options, options);

        // settings for fullcalendar.
        var settings = {
            firstDay:   obj.options.l10n.startOfWeek,
            allDayText: obj.options.l10n.allDay,
            buttonText: {
                today:  obj.options.l10n.today,
                month:  obj.options.l10n.month,
                week:   obj.options.l10n.week,
                day:    obj.options.l10n.day
            },
            axisFormat:    obj.options.l10n.mjsTimeFormat,
            slotDuration:  obj.options.l10n.slotDuration,
            // Text/Time Customization.
            timeFormat:    obj.options.l10n.mjsTimeFormat,
            monthNames:    obj.options.l10n.calendar.longMonths,
            monthNamesShort: obj.options.l10n.calendar.shortMonths,
            dayNames:      obj.options.l10n.calendar.longDays,
            dayNamesShort: obj.options.l10n.calendar.shortDays,
            allDaySlot: false,
            eventBackgroundColor: 'silver',
            // Agenda Options.
            displayEventEnd: true,
            // Event Dragging & Resizing.
            editable: false,
            // Event Data.
            eventSources: [{
                url: ajaxurl,
                data: {
                    action: 'bookly_get_staff_appointments',
                    csrf_token: obj.options.l10n.csrf_token,
                    staff_ids: function () {
                        if (obj.options.is_backend && obj.options.getCurrentStaffId() == 0) {
                            return obj.options.getStaffMemberIds();
                        } else {
                            return [obj.options.getCurrentStaffId()];
                        }
                    },
                    location_ids: function() {
                        if (obj.options.is_backend) {
                            return obj.options.getLocationIds();
                        } else {
                            return ['all'];
                        }
                    }
                }
            }],
            eventAfterRender: function (calEvent, $calEventList, calendar) {
                if (calEvent.rendering !== 'background') {
                    $calEventList.each(function () {
                        var $calEvent  = $(this),
                            origHeight = $calEvent.outerHeight(),
                            z_index    = $calEvent.zIndex();
                        // Mouse handlers.
                        $calEvent
                            .on('mouseenter', function () {
                                $calEvent.css({'z-index': 64, bottom: '', 'min-height': origHeight, height: ''});
                            }).on('mouseleave', function () {
                                $calEvent.css({'z-index': z_index, height: origHeight});
                            })
                            .removeClass('fc-short');
                    });
                }
            },
            // Clicking & Hovering.
            dayClick: function (date, jsEvent, view) {
                var staff_id, visible_staff_id;
                if (view.type == 'multiStaffDay') {
                    var cell = view.coordMap.getCell(jsEvent.pageX, jsEvent.pageY),
                        staffMembers = view.opt('staffMembers');
                    staff_id = staffMembers[cell.col].id;
                    visible_staff_id = 0;
                } else {
                    staff_id = visible_staff_id = obj.options.getCurrentStaffId();
                }
                showAppointmentDialog(
                    null,
                    staff_id,
                    date,
                    function (event) {
                        if (event == 'refresh') {
                            $container.fullCalendar('refetchEvents');
                        } else {
                            if (visible_staff_id == event.staffId || visible_staff_id == 0) {
                                if (event.start !== null) {
                                    if (event.id) {
                                        // Create event in calendar.
                                        $container.fullCalendar('renderEvent', event);
                                    } else {
                                        $container.fullCalendar('refetchEvents');
                                    }
                                }
                            } else {
                                // Switch to the event owner tab.
                                jQuery('li[data-staff_id=' + event.staffId + ']').click();
                            }
                        }

                        if (locationChanged) {
                            $container.fullCalendar('refetchEvents');
                            locationChanged = false;
                        }
                    }
                );
            },
            // Event Rendering.
            eventRender: function (calEvent, $event, view) {
                if (calEvent.rendering !== 'background') {
                    var $body = $event.find('.fc-title');
                    if (calEvent.desc) {
                        $body.append(calEvent.desc);
                    }

                    var $time = $event.find('.fc-time');
                    if (calEvent.header_text !== undefined) {
                        $time.html(calEvent.header_text);
                    }
                    if (obj.options.l10n.recurring_appointments.active == '1' && calEvent.series_id) {
                        $time.prepend(
                            $('<a class="bookly-fc-icon dashicons dashicons-admin-links"></a>')
                                .attr('title', obj.options.l10n.recurring_appointments.title)
                                .on('click', function (e) {
                                    e.stopPropagation();
                                    $(document.body).trigger('recurring_appointments.series_dialog', [calEvent.series_id, function (event) {
                                        // Switch to the event owner tab.
                                        jQuery('li[data-staff_id=' + event.staffId + ']').click();
                                    }]);
                                })
                        );
                    }
                    if (obj.options.l10n.waiting_list.active == '1' && calEvent.waitlisted > 0) {
                        $time.prepend(
                            $('<span class="bookly-fc-icon dashicons dashicons-list-view"></span>')
                                .attr('title', obj.options.l10n.waiting_list.title)
                        );
                    }
                    if (obj.options.l10n.packages.active == '1' && calEvent.package_id > 0) {
                        $time.prepend(
                            $('<span class="bookly-fc-icon dashicons dashicons-calendar" style="padding:0 2px;"></span>')
                                .attr('title', obj.options.l10n.packages.title)
                                .on('click', function (e) {
                                    e.stopPropagation();
                                    if (obj.options.l10n.packages.active == '1' && calEvent.package_id) {
                                        $(document.body).trigger('bookly_packages.schedule_dialog', [calEvent.package_id, function () {
                                            $container.fullCalendar('refetchEvents');
                                        }]);
                                    }
                                })
                        );
                    }
                    $time.prepend(
                        $('<a class="bookly-fc-icon dashicons dashicons-trash"></a>')
                            .attr('title', obj.options.l10n.delete)
                            .on('click', function (e) {
                                e.stopPropagation();
                                // Localize contains only string values
                                if (obj.options.l10n.recurring_appointments.active == '1' && calEvent.series_id) {
                                    $(document.body).trigger('recurring_appointments.delete_dialog', [$container, calEvent]);
                                } else {
                                    obj.$deleteDialog.data('calEvent', calEvent).modal('show');
                                }
                            })
                    );
                }
            },
            eventClick: function (calEvent, jsEvent, view) {
                var visible_staff_id;
                if (view.type == 'multiStaffDay') {
                    visible_staff_id = 0;
                } else {
                    visible_staff_id = calEvent.staffId;
                }

                showAppointmentDialog(
                    calEvent.id,
                    null,
                    null,
                    function (event) {
                        if (event == 'refresh') {
                            $container.fullCalendar('refetchEvents');
                        } else {
                            if (event.start !== null) {
                                if (visible_staff_id == event.staffId || visible_staff_id == 0) {
                                    // Update event in calendar.
                                    jQuery.extend(calEvent, event);
                                    $container.fullCalendar('updateEvent', calEvent);
                                } else {
                                    // Switch to the event owner tab.
                                    jQuery('li[data-staff_id=' + event.staffId + ']').click();
                                }
                            }
                        }

                        if (locationChanged) {
                            $container.fullCalendar('refetchEvents');
                            locationChanged = false;
                        }
                    }
                );
            },
            loading: function (isLoading) {
                if (isLoading) {
                    $('.fc-loading-inner').show();
                }
            },
            eventAfterAllRender: function () {
                $('.fc-loading-inner').hide();
            }
        };

        // Init fullcalendar
        $container.fullCalendar($.extend({}, settings, obj.options.fullcalendar));

        var $fcDatePicker = $('<input type=hidden />');

        $('.fc-toolbar .fc-center h2', $container).before($fcDatePicker).on('click', function () {
            $fcDatePicker.datepicker('setDate', $container.fullCalendar('getDate').toDate()).datepicker('show');
        });

        // Init date picker for fast navigation in FullCalendar.
        $fcDatePicker.datepicker({
            dayNamesMin:     settings.dayNamesShort,
            monthNames:      settings.monthNames,
            monthNamesShort: settings.monthNamesShort,
            firstDay:        settings.firstDay,
            beforeShow: function (input, inst) {
                inst.dpDiv.queue(function () {
                    inst.dpDiv.css({marginTop: '35px', 'font-size': '13.5px'});
                    inst.dpDiv.dequeue();
                });
            },
            onSelect: function (dateText, inst) {
                var d = new Date(dateText);
                $container.fullCalendar('gotoDate', d);
                if ($container.fullCalendar('getView').type != 'agendaDay' &&
                    $container.fullCalendar('getView').type != 'multiStaffDay') {
                    $container.find('.fc-day').removeClass('bookly-fc-day-active');
                    $container.find('.fc-day[data-date="' + moment(d).format('YYYY-MM-DD') + '"]').addClass('bookly-fc-day-active');
                }
            },
            onClose: function (dateText, inst) {
                inst.dpDiv.queue(function () {
                    inst.dpDiv.css({marginTop: '0'});
                    inst.dpDiv.dequeue();
                });
            }
        });

        /**
         * On delete appointment click.
         */
        if (obj.$deleteDialog.data('events') == undefined
            || obj.$deleteDialog.data('events').click == undefined)
        {
            obj.$deleteDialog.on('click', '#bookly-delete', function (e) {
                var calEvent = obj.$deleteDialog.data('calEvent'),
                    ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    type : 'POST',
                    url  : ajaxurl,
                    data : {
                        'action': 'bookly_delete_appointment',
                        'csrf_token': obj.options.l10n.csrf_token,
                        'appointment_id': calEvent.id,
                        'notify': $('#bookly-delete-notify').prop('checked') ? 1 : 0,
                        'reason': $('#bookly-delete-reason').val()
                    },
                    dataType   : 'json',
                    xhrFields  : {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success    : function (response) {
                        ladda.stop();
                        $container.fullCalendar('removeEvents', calEvent.id);
                        obj.$deleteDialog.modal('hide');
                    }
                });
            });
        }
    };

    var locationChanged = false;
    $('body').on('change', '#bookly-appointment-location', function() {
        locationChanged = true;
    });

    Calendar.prototype.$deleteDialog = $('#bookly-delete-dialog');
    Calendar.prototype.options = {
        fullcalendar: {},
        getCurrentStaffId: function () { return -1; },
        getStaffMemberIds: function () { return []; },
        getLocationIds:    function () { return []; },
        l10n: {},
        is_backend: true
    };

    window.BooklyCalendar = Calendar;
});