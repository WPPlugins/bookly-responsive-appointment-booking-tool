jQuery(function ($) {

    var $fullCalendar = $('#full_calendar_wrapper .ab-calendar-element'),
        $tabs         = $('li.ab-calendar-tab'),
        $staff        = $('input.ab-staff-filter'),
        $showAll      = $('input#ab-filter-all-staff'),
        firstHour     = new Date().getHours(),
        $staffButton  = $('#ab-staff-button'),
        staffMembers  = [],
        staffIds      = getCookie('bookly_cal_st_ids'),
        tabId         = getCookie('bookly_cal_tab_id'),
        lastView      = getCookie('bookly_cal_view'),
        views         = 'month,agendaWeek,agendaDay,multiStaffDay';

    if (views.indexOf(lastView) == -1) {
        lastView = 'agendaWeek';
    }
    // Init tabs and staff member filters.
    if (staffIds === null) {
        $staff.each(function(index, value){
            this.checked = true;
            $tabs.filter('[data-staff_id=' + this.value + ']').show();
        });
    } else if (staffIds != '') {
        $.each(staffIds.split(','), function (index, value){
            $staff.filter('[value=' + value + ']').prop('checked', true);
            $tabs.filter('[data-staff_id=' + value + ']').show();
        });
    } else {
        $('.dropdown-toggle').dropdown('toggle');
    }

    $tabs.filter('[data-staff_id=' + tabId + ']').addClass('active');
    if ($tabs.filter('li.active').length == 0) {
        $tabs.eq(0).addClass('active').show();
        $staff.filter('[value=' + $tabs.eq(0).data('staff_id') + ']').prop('checked', true);
    }
    updateStaffButton();

    // Init FullCalendar.
    $fullCalendar.fullCalendar({
        // General Display.
        firstDay: BooklyL10n.startOfWeek,
        header: {
            left: 'prev,next today',
            center: 'title',
            right: views
        },
        height: heightFC(),
        // Views.
        defaultView: lastView,
        scrollTime: firstHour+':00:00',
        views: {
            agendaWeek: {
                columnFormat: 'ddd, D'
            },
            multiStaffDay: {
                staffMembers: staffMembers
            }
        },
        eventBackgroundColor: 'silver',
        // Agenda Options.
        allDaySlot:   false,
        allDayText:   BooklyL10n.allDay,
        axisFormat:   BooklyL10n.mjsTimeFormat,
        slotDuration: BooklyL10n.slotDuration,
        // Text/Time Customization.
        timeFormat:   BooklyL10n.mjsTimeFormat,
        displayEventEnd: true,
        buttonText : {
            today: BooklyL10n.today,
            month: BooklyL10n.month,
            week:  BooklyL10n.week,
            day:   BooklyL10n.day
        },
        monthNames:      BooklyL10n.longMonths,
        monthNamesShort: BooklyL10n.shortMonths,
        dayNames:        BooklyL10n.longDays,
        dayNamesShort:   BooklyL10n.shortDays,
        // Event Dragging & Resizing.
        editable: false,
        // Event Data.
        eventSources: [{
            url: ajaxurl,
            data: {
                action    : 'ab_get_staff_appointments',
                staff_ids : function() {
                    var ids = [];
                    if ($tabs.filter('.active').data('staff_id') == 0) {
                        for (var i = 0; i < staffMembers.length; ++i) {
                            ids.push(staffMembers[i].id);
                        }
                    } else {
                        ids.push($tabs.filter('.active').data('staff_id'));
                    }
                    return ids;
                }
            }
        }],
        // Event Rendering.
        eventRender : function(calEvent, $event) {
            var body = '<div class="fc-service-name">' + calEvent.title + '<i class="delete-event icon-rt glyphicon glyphicon-trash"></i></div>';

            if (calEvent.desc) {
                body += calEvent.desc;
            }

            $event.find('.fc-title').html(body);

            $event.find('i.delete-event').on('click', function(e) {
                e.stopPropagation();
                if (confirm(BooklyL10n.are_you_sure)) {
                    $.post(ajaxurl, {'action' : 'ab_delete_appointment', 'appointment_id' : calEvent.id }, function () {
                        $fullCalendar.fullCalendar('removeEvents', calEvent.id);
                    });
                }
            });
        },
        eventAfterRender : function(calEvent, $calEventList, calendar) {
            $calEventList.each(function () {
                var $calEvent   = $(this);
                var titleHeight = $calEvent.find('.fc-title').height();
                var origHeight  = $calEvent.height();

                if ( origHeight < titleHeight ) {
                    var $info   = $('<i class="icon-rt glyphicon glyphicon-info-sign"></i>');
                    var $delete = $('.delete-event', $calEvent);

                    $delete.hide();
                    $('.fc-content', $calEvent).append($info);
                    $('.fc-content', $calEvent).append($delete);

                    var z_index = $calEvent.zIndex();
                    // Mouse handlers.
                    $info.on('mouseenter', function () {
                        $calEvent.css({height: (titleHeight + 30), 'z-index': 64});
                        $info.hide();
                        $delete.show();
                        $calEvent.removeClass('fc-short');
                    });
                    $calEvent.on('mouseleave', function () {
                        $calEvent.css({height: origHeight, 'z-index': z_index});
                        $delete.hide();
                        $info.show();
                        $calEvent.addClass('fc-short');
                    });
                }
            });
        },
        // Clicking & Hovering.
        dayClick: function(date, jsEvent, view) {
            var staff_id, visible_staff_id;
            if (view.type == 'multiStaffDay') {
                var cell = view.coordMap.getCell(jsEvent.pageX, jsEvent.pageY);
                var staffMembers = view.opt('staffMembers');
                staff_id = staffMembers[cell.col].id;
                visible_staff_id = 0;
            } else {
                staff_id = visible_staff_id = $tabs.filter('.active').data('staff_id');
            }

            showAppointmentDialog(
                null,
                staff_id,
                date,
                null,
                function(event) {
                    if (visible_staff_id == event.staffId || visible_staff_id == 0) {
                        // Create event in calendar.
                        $fullCalendar.fullCalendar('renderEvent', event );
                    } else {
                        // Switch to the event owner tab.
                        jQuery('li[data-staff_id=' + event.staffId + ']').click();
                    }
                }
            );
        },
        eventClick: function(calEvent, jsEvent, view) {
            var visible_staff_id;
            if (view.type == 'multiStaffDay') {
                visible_staff_id = 0;
            } else {
                visible_staff_id = calEvent.staffId;
            }

            showAppointmentDialog(
                calEvent.id,
                calEvent.staffId,
                calEvent.start,
                calEvent.end,
                function(event) {
                    if (visible_staff_id == event.staffId || visible_staff_id == 0) {
                        // Update event in calendar.
                        jQuery.extend(calEvent, event);
                        $fullCalendar.fullCalendar('updateEvent', calEvent);
                    } else {
                        // Switch to the event owner tab.
                        jQuery('li[data-staff_id=' + event.staffId + ']').click();
                    }
                }
            );
        },
        loading: function (bool) {
            bool ? $('.ab-loading-inner').show() : $('.ab-loading-inner').hide();
        },
        viewRender: function(view, element){
            setCookie('bookly_cal_view',view.type);
        }
    });

    $('.fc-agendaDay-button').addClass('fc-corner-right');
    if ($tabs.filter('.active').data('staff_id') == 0) {
        $('.fc-agendaDay-button').hide();
    } else {
        $('.fc-multiStaffDay-button').hide();
    }

    // Init date picker for fast navigation in FullCalendar.
    var $fcDatePicker = $('<input type=hidden />');
    $('.fc-toolbar .fc-center h2').before($fcDatePicker).on('click', function() {
        $fcDatePicker.datepicker('setDate', $fullCalendar.fullCalendar('getDate').toDate()).datepicker('show');
    });
    $fcDatePicker.datepicker({
        dayNamesMin     : BooklyL10n.shortDays,
        monthNames      : BooklyL10n.longMonths,
        monthNamesShort : BooklyL10n.shortMonths,
        firstDay        : BooklyL10n.startOfWeek,
        beforeShow: function(input, inst){
            inst.dpDiv.queue(function() {
                inst.dpDiv.css({marginTop: '35px'});
                inst.dpDiv.dequeue();
            });
        },
        onSelect: function(dateText, inst) {
            var d = new Date(dateText);
            $fullCalendar.fullCalendar('gotoDate', d);
            if( $fullCalendar.fullCalendar('getView').type != 'agendaDay' &&
                $fullCalendar.fullCalendar('getView').type != 'multiStaffDay' ){
                $fullCalendar.find('.fc-day').removeClass('ab-fcday-active');
                $fullCalendar.find('.fc-day[data-date="' + moment(d).format('YYYY-MM-DD') + '"]').addClass('ab-fcday-active');
            }
        },
        onClose: function(dateText, inst) {
            inst.dpDiv.queue(function() {
                inst.dpDiv.css({marginTop: '0'});
                inst.dpDiv.dequeue();
            });
        }
    });

    $(window).on('resize', function() {
        $fullCalendar.fullCalendar('option', 'height', heightFC());
    });

    // Click on tabs.
    $tabs.on('click', function(e) {
        e.preventDefault();
        $tabs.removeClass('active');
        $(this).addClass('active');

        var staff_id = $(this).data('staff_id');
        setCookie('bookly_cal_tab_id', staff_id);

        if (staff_id == 0) {
            $('.fc-agendaDay-button').hide();
            $('.fc-multiStaffDay-button').show();
            $fullCalendar.fullCalendar('changeView', 'multiStaffDay');
            $fullCalendar.fullCalendar('refetchEvents');
        } else {
            $('.fc-multiStaffDay-button').hide();
            $('.fc-agendaDay-button').show();
            var view = $fullCalendar.fullCalendar('getView');
            if (view.type == 'multiStaffDay') {
                $fullCalendar.fullCalendar('changeView', 'agendaDay');
            }
            $fullCalendar.fullCalendar('refetchEvents');
        }
    });

    $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
    });

    /**
     * On show all staff checkbox click.
     */
    $showAll.on('change', function () {
        $tabs.filter('[data-staff_id!=0]').toggle(this.checked);
        $staff
            .prop('checked', this.checked)
            .filter(':first').triggerHandler('change');
    });

    /**
     * On staff checkbox click.
     */
    $staff.on('change', function (e) {
        updateStaffButton();

        $tabs.filter('[data-staff_id=' + this.value + ']').toggle(this.checked);
        if ($tabs.filter(':visible.active').length == 0 ) {
            $tabs.filter(':visible:first').triggerHandler('click');
        } else if ($tabs.filter('.active').data('staff_id') == 0) {
            var view = $fullCalendar.fullCalendar('getView');
            if (view.type == 'multiStaffDay') {
                view.displayView($fullCalendar.fullCalendar('getDate'));
            }
            $fullCalendar.fullCalendar('refetchEvents');
        }
    });

    function updateStaffButton() {
        $showAll.prop('checked', $staff.filter(':not(:checked)').length == 0);

        // Update staffMembers array.
        var ids = [];
        staffMembers.length = 0;
        $staff.filter(':checked').each(function() {
            staffMembers.push({id: this.value, name: this.getAttribute('data-staff_name')});
            ids.push(this.value);
        });
        setCookie('bookly_cal_st_ids', ids);

        // Update button text.
        var number = $staff.filter(':checked').length;
        if (number == 0) {
            $staffButton.text(BooklyL10n.noStaffSelected);
        } else if (number == 1) {
            $staffButton.text($staff.filter(':checked').data('staff_name'));
        } else {
            $staffButton.text(number + '/' + $staff.length);
        }
    }

    /**
     * Set cookie.
     *
     * @param key
     * @param value
     */
    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';expires=' + expires.toUTCString();
    }

    /**
     * Get cookie.
     *
     * @param key
     * @return {*}
     */
    function getCookie(key) {
        var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

    /**
     * Calculate height of FullCalendar.
     *
     * @return {number}
     */
    function heightFC() {
        var window_height           = $(window).height(),
            wp_admin_bar_height     = $('#wpadminbar').height(),
            ab_calendar_tabs_height = $('#full_calendar_wrapper .tabbable').outerHeight(true),
            height_to_reduce        = wp_admin_bar_height + ab_calendar_tabs_height,
            $wrap                   = $('#wpbody-content .wrap');

        if ($wrap.css('margin-top')) {
            height_to_reduce += parseInt($wrap.css('margin-top').replace('px', ''), 10);
        }

        if ($wrap.css('margin-bottom')) {
            height_to_reduce += parseInt($wrap.css('margin-bottom').replace('px', ''), 10);
        }

        var res = window_height - height_to_reduce - 130;

        return res > 620 ? res : 620;
    }

});