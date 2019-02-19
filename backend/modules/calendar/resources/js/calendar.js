jQuery(function ($) {

    var $fullCalendar    = $('#bookly-fc-wrapper .bookly-js-calendar-element'),
        $tabs            = $('.bookly-js-calendar-tab'),
        $staffFilter     = $('#bookly-js-staff-filter'),
        $locationsFilter = $('#bookly-js-locations-filter'),
        firstHour        = new Date().getHours(),
        $gcSyncButton    = $('#bookly-google-calendar-sync'),
        $ocSyncButton    = $('#bookly-outlook-calendar-sync'),
        staffMembers     = [],  // Do not override staffMembers, it is used as a reference
        staffIds         = getCookie('bookly_cal_st_ids'),
        locationIds      = getCookie('bookly_cal_location_ids'),
        tabId            = getCookie('bookly_cal_tab_id'),
        lastView         = getCookie('bookly_cal_view'),
        views            = 'month,agendaWeek,agendaDay,multiStaffDay';

    if (views.indexOf(lastView) == -1) {
        lastView = 'multiStaffDay';
    }

    /**
     * Init tabs.
     */
    $tabs.on('click', function (e) {
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
    $tabs.filter('[data-staff_id=' + tabId + ']').addClass('active');
    if ($tabs.filter('li.active').length === 0) {
        $tabs.eq(0).addClass('active').show();
    }

    /**
     * Init staff filter.
     */
    $staffFilter.booklyDropdown({
        onChange: function (values, selected, all) {
            var ids = [];
            staffMembers.length = 0;
            this.booklyDropdown('getSelectedExt').forEach(function (item) {
                ids.push(item.value);
                staffMembers.push({id: item.value, name: item.name});
            });
            setCookie('bookly_cal_st_ids', ids);
            if (all) {
                $tabs.filter('[data-staff_id!=0]').toggle(selected);
            } else {
                values.forEach(function (value) {
                    $tabs.filter('[data-staff_id=' + value + ']').toggle(selected);
                });
            }
            if ($tabs.filter(':visible.active').length === 0) {
                $tabs.filter(':visible:first').triggerHandler('click');
            } else if ($tabs.filter('.active').data('staff_id') === 0) {
                var view = $fullCalendar.fullCalendar('getView');
                if (view.type === 'multiStaffDay') {
                    view.displayView($fullCalendar.fullCalendar('getDate'));
                }
                $fullCalendar.fullCalendar('refetchEvents');
            }
        }
    });
    if (staffIds === null) {
        $staffFilter.booklyDropdown('selectAll');
    } else if (staffIds !== '') {
        $staffFilter.booklyDropdown('setSelected', staffIds.split(','));
    } else {
        $staffFilter.booklyDropdown('toggle');
    }
    // Populate staffMembers.
    $staffFilter.booklyDropdown('getSelectedExt').forEach(function (item) {
        staffMembers.push({id: item.value, name: item.name});
        $tabs.filter('[data-staff_id=' + item.value + ']').show();
    });

    /**
     * Init locations filter.
     */
    $locationsFilter.booklyDropdown({
        onChange: function (values, selected, all) {
            locationIds = this.booklyDropdown('getSelected');
            setCookie('bookly_cal_location_ids', locationIds);
            var view = $fullCalendar.fullCalendar('getView');
            if (view.type === 'multiStaffDay') {
                view.displayView($fullCalendar.fullCalendar('getDate'));
            }
            $fullCalendar.fullCalendar('refetchEvents');
        }
    });
    if (locationIds === null) {
        $locationsFilter.booklyDropdown('selectAll');
    } else if (locationIds !== '') {
        $locationsFilter.booklyDropdown('setSelected', locationIds.split(','));
    } else {
        $locationsFilter.booklyDropdown('toggle');
    }
    // Populate locationIds.
    locationIds = $locationsFilter.booklyDropdown('getSelected');

    /**
     * Init FullCalendar.
     */
    new BooklyCalendar($fullCalendar, {
        fullcalendar: {
            // General Display.
            header: {
                left: 'prev,next today',
                center: 'title',
                right: views
            },
            height: heightFC(),
            // Views.
            defaultView: lastView,
            scrollTime: firstHour + ':00:00',
            views: {
                agendaWeek: {
                    columnFormat: 'ddd, D'
                },
                multiStaffDay: {
                    staffMembers: staffMembers
                }
            },
            viewRender: function (view, element) {
                setCookie('bookly_cal_view', view.type);
            }
        },
        getCurrentStaffId: function () {
            return $tabs.filter('.active').data('staff_id');
        },
        getStaffMemberIds: function () {
            var ids = [];
            staffMembers.forEach(function (staff) {
                ids.push(staff.id);
            });

            return ids;
        },
        getLocationIds: function () {
            return locationIds;
        },
        l10n: BooklyL10n
    });

    function heightFC() {
        var height = $(window).height() - $('#bookly-fc-wrapper').offset().top - 20;

        return height > 620 ? height : 620;
    }

    $('.fc-agendaDay-button').addClass('fc-corner-right');
    if ($tabs.filter('.active').data('staff_id') == 0) {
        $('.fc-agendaDay-button').hide();
    } else {
        $('.fc-multiStaffDay-button').hide();
    }

    $(window).on('resize', function () {
        $fullCalendar.fullCalendar('option', 'height', heightFC());
    });

    /**
     * Set cookie.
     *
     * @param key
     * @param value
     */
    function setCookie(key, value) {
        var expires = new Date();
        expires.setTime(expires.getTime() + 86400000); // 60 × 60 × 24 × 1000
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
     * Sync with Google Calendar.
     */
    $gcSyncButton.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.post(
            ajaxurl,
            {action: 'bookly_advanced_google_calendar_sync', csrf_token: BooklyL10n.csrf_token},
            function (response) {
                if (response.success) {
                    $fullCalendar.fullCalendar('refetchEvents');
                }
                booklyAlert(response.data.alert);
                ladda.stop();
            },
            'json'
        );
    });

    /**
     * Sync with Outlook Calendar.
     */
    $ocSyncButton.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.post(
            ajaxurl,
            {action: 'bookly_outlook_calendar_sync', csrf_token: BooklyL10n.csrf_token},
            function (response) {
                if (response.success) {
                    $fullCalendar.fullCalendar('refetchEvents');
                }
                booklyAlert(response.data.alert);
                ladda.stop();
            },
            'json'
        );
    });
});