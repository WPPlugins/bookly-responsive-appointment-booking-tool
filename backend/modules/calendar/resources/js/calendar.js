jQuery(function ($) {

    var $fullCalendar = $('#bookly-fc-wrapper .bookly-js-calendar-element'),
        $tabs         = $('.bookly-js-calendar-tab'),
        $staff        = $('input.bookly-js-check-entity'),
        $showAll      = $('input#bookly-check-all-entities'),
        firstHour     = new Date().getHours(),
        $syncButton   = $('#bookly-google-calendar-sync'),
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
        $('.bookly-js-staff-filter.dropdown-toggle').dropdown('toggle');
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
        var height = $(window).height() - $('#bookly-fc-wrapper').offset().top - 20;

        return height > 620 ? height : 620;
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

    /**
     * @param {jQuery} $container
     * @param {Object} callbacks
     */
    function initLocationFilter($container, callbacks)
    {
        var elements = {};

        var initElements = function() {
            elements.toggleButton = $container.find('.bookly-js-toggle-button');
            elements.title = $container.find('.bookly-js-locations-title');

            elements.items = $container.find('input[type=checkbox]');
            elements.itemAllLocations = elements.items.filter('.bookly-js-all-locations');
            elements.itemNoLocations = elements.items.filter('.bookly-js-no-locations');
            elements.locationItems = elements.items.filter('.bookly-js-locations-item');
        };

        var restoreItemsFromCookie = function () {
            var selectedValues = getCookie('bookly_cal_location_ids');

            if (!selectedValues) {
                return;
            }

            selectedValues = selectedValues.split(',');

            if (selectedValues.indexOf('all') !== -1) {
                elements.locationItems.prop('checked', true);
            } else {
                selectedValues.forEach(function (value) {
                    elements.locationItems.filter('[value=' + value + ']').prop('checked', true);
                });
            }
        };

        var getSelectedItems = function () {
            return elements.locationItems.filter(':checked');
        };

        var updateTitle = function() {
            var selectedItems = getSelectedItems(),
                selectedCount = selectedItems.length,
                locationCount = elements.locationItems.length;

            if (selectedCount === 0) {
                elements.title.text($container.data('nothing_selected'));
            } else if (selectedCount === locationCount) {
                elements.title.text($container.data('all_selected'));
            } else if (selectedCount === 1) {
                elements.title.text(selectedItems.data('name'));
            } else {
                elements.title.text(selectedCount + '/' + locationCount);
            }

            elements.itemAllLocations.prop('checked', selectedCount === locationCount);
        };

        var saveCookie = function () {
            var selectedItems = getSelectedItems(),
                selectedValues = [];

            selectedItems.each(function () {
                selectedValues.push(this.value);
            });

            setCookie('bookly_cal_location_ids', selectedValues);
        };

        var initEvents = function () {
            elements.itemAllLocations.on('change', function () {
                elements.locationItems.prop('checked', elements.itemAllLocations.prop('checked'));
            });

            elements.items.on('change', function() {
                updateTitle();
                saveCookie();

                if (callbacks.change instanceof Function) {
                    callbacks.change();
                }
            });
        };

        function setDefaultLocations()
        {
            var locationIds = getCookie('bookly_cal_location_ids');

            if (!locationIds) {
                $container.find('input[type="checkbox"]').prop('checked', true);
                setCookie('bookly_cal_location_ids', ['all']);
            }
        }

        initElements();
        setDefaultLocations();
        restoreItemsFromCookie();
        initEvents();
        updateTitle();
    }

    initLocationFilter($('.bookly-js-locations-filter'), {
        change: function() {
            var view = $fullCalendar.fullCalendar('getView');
            if (view.type === 'multiStaffDay') {
                view.displayView($fullCalendar.fullCalendar('getDate'));
            }
            $fullCalendar.fullCalendar('refetchEvents');
        }
    });

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

    /**
     * Sync with Google Calendar.
     */
    $syncButton.on('click', function () {
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

});