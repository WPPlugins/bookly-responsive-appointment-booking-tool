jQuery(function ($) {

    var $fullCalendar = $('#bookly-fc-wrapper .bookly-js-calendar-element'),
        $tabs         = $('.bookly-js-calendar-tab'),
        $staff        = $('input.bookly-js-check-entity'),
        $showAll      = $('input#bookly-check-all-entities'),
        firstHour     = new Date().getHours(),
        $staffButton  = $('#bookly-staff-button'),
        staffMembers  = [],
        staffIds      = getCookie('bookly_cal_st_ids'),
        tabId         = getCookie('bookly_cal_tab_id'),
        lastView      = getCookie('bookly_cal_view'),
        views         = 'month,agendaWeek,agendaDay,multiStaffDay';

    if (views.indexOf(lastView) == -1) {
        lastView = 'multiStaffDay';
    }
    // Init tabs and staff member filters.
    if (staffIds === null) {
        $staff.each(function (index, value) {
            this.checked = true;
            $tabs.filter('[data-staff_id=' + this.value + ']').show();
        });
    } else if (staffIds != '') {
        $.each(staffIds.split(','), function (index, value) {
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

    /**
     * Calculate height of FullCalendar.
     *
     * @return {number}
     */
    function heightFC() {
        var window_height = $(window).height(),
            wp_admin_bar_height = $('#wpadminbar').height(),
            bookly_calendar_tabs_height = $('#bookly-fc-wrapper .tabbable').outerHeight(true),
            height_to_reduce = wp_admin_bar_height + bookly_calendar_tabs_height,
            $wrap = $('#wpbody-content .wrap');

        if ($wrap.css('margin-top')) {
            height_to_reduce += parseInt($wrap.css('margin-top').replace('px', ''), 10);
        }

        if ($wrap.css('margin-bottom')) {
            height_to_reduce += parseInt($wrap.css('margin-bottom').replace('px', ''), 10);
        }

        var res = window_height - height_to_reduce - 130;

        return res > 620 ? res : 620;
    }

    var options = {
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
        getStaffMembers: function () {
            return staffMembers;
        },
        l10n: BooklyL10n
    };

    var calendar = new BooklyCalendar($fullCalendar, options);

    $('.fc-agendaDay-button').addClass('fc-corner-right');
    if ($tabs.filter('.active').data('staff_id') == 0) {
        $('.fc-agendaDay-button').hide();
    } else {
        $('.fc-multiStaffDay-button').hide();
    }

    $(window).on('resize', function () {
        $fullCalendar.fullCalendar('option', 'height', heightFC());
    });

    // Click on tabs.
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
        if ($tabs.filter(':visible.active').length == 0) {
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
        $staff.filter(':checked').each(function () {
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

});