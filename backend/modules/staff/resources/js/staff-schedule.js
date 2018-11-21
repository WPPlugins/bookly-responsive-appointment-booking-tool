jQuery(function ($) {
    var Schedule = function ($container, options) {
        var obj = this;
        jQuery.extend(obj.options, options);

        // Loads schedule list
        if (!$container.children().length || obj.options.refresh) {
            $container.html('<div class="bookly-loading"></div>');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: obj.options.get_staff_schedule,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    // fill in the container
                    $container.html(response.data.html);
                    $container.booklyHelp();

                    var schedule = response.data.schedule;
                    // init 'add break' functionality
                    $('.bookly-js-toggle-popover:not(.break-interval)', $container).popover({
                        html: true,
                        placement: 'bottom',
                        template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                        trigger: 'manual',
                        content: function () {
                            return $($(this).data('popover-content')).html()
                        }
                    }).on('click', function () {
                        $(this).popover('toggle');

                        var $popover = $(this).next('.popover'),
                            working_start = $popover.closest('.row').find('.working-schedule-start').val(),
                            $break_start = $popover.find('.break-start'),
                            $break_end = $popover.find('.break-end'),
                            working_start_time = working_start.split(':'),
                            working_start_hours = parseInt(working_start_time[0], 10),
                            break_start_hours = working_start_hours + 1;
                        if (break_start_hours < 10) {
                            break_start_hours = '0' + break_start_hours;
                        }
                        var break_end_hours = working_start_hours + 2;
                        if (break_end_hours < 10) {
                            break_end_hours = '0' + break_end_hours;
                        }
                        var break_end_hours_str = break_end_hours + ':' + working_start_time[1] + ':' + working_start_time[2],
                            break_start_hours_str = break_start_hours + ':' + working_start_time[1] + ':' + working_start_time[2];

                        $break_start.val(break_start_hours_str);
                        $break_end.val(break_end_hours_str);

                        hideInaccessibleBreaks($break_start, $break_end);

                        $popover.find('.bookly-popover-close').on('click', function () {
                            $popover.popover('hide');
                        });
                    });

                    $container.off()
                    // Save Schedule
                        .on('click', '#bookly-schedule-save', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            var data = {};
                            $('select.working-schedule-start, select.working-schedule-end, input:hidden', $container).each(function () {
                                data[this.name] = this.value;
                            });
                            data['location_id'] = $('#staff_location_id', $container).val();
                            data['custom_location_settings'] = $('#custom_location_settings', $container).val();
                            data['staff_id'] = options.get_staff_schedule.staff_id;
                            $.post(ajaxurl, $.param(data), function () {
                                ladda.stop();
                                obj.options.booklyAlert({success: [obj.options.l10n.saved]});
                            });
                        })
                        // Resets initial schedule values
                        .on('click', '#bookly-schedule-reset', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();

                            $('.working-schedule-start', $container).each(function () {
                                $(this).val($(this).data('default_value'));
                                $(this).trigger('change');
                            });

                            $('.working-schedule-end', $container).each(function () {
                                $(this).val($(this).data('default_value'));
                            });

                            // reset breaks
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {action: 'bookly_reset_breaks', breaks: $(this).data('default-breaks'), staff_cabinet:$(this).data('staff-cabinet')||0, csrf_token: obj.options.l10n.csrf_token},
                                dataType: 'json',
                                success: function (response) {
                                    for (var k in response) {
                                        var $content = $(response[k]);
                                        $('[data-staff_schedule_item_id=' + k + '] .breaks', $container).html($content);
                                        $content.find('.bookly-intervals-wrapper .delete-break').on('click', function () {
                                            deleteBreak.call(this);
                                        });
                                    }
                                },
                                complete: function () {
                                    ladda.stop();
                                }
                            });
                        })

                        .on('click', '.break-interval', function () {
                            var $button = $(this);
                            $('.popover').popover('hide');
                            var break_id = $button.closest('.bookly-intervals-wrapper').data('break_id');
                            $(this).popover({
                                html: true,
                                placement: 'bottom',
                                template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                                content: function () {
                                    return $('.bookly-js-content-break-' + break_id).html();
                                },
                                trigger: 'manual'
                            });

                            $(this).popover('toggle');

                            var $popover = $(this).next('.popover'),
                                $break_start = $popover.find('.break-start'),
                                $break_end = $popover.find('.break-end');

                            if ($button.hasClass('break-interval')) {
                                var interval = $button.html().trim().split(' - ');
                                rangeTools.setVal($break_start, interval[0]);
                                rangeTools.setVal($break_end, interval[1]);
                            }

                            hideInaccessibleBreaks($break_start, $break_end, true);

                            $popover.find('.bookly-popover-close').on('click', function () {
                                $popover.popover('hide');
                            });
                        })

                        .on('click', '.bookly-js-save-break', function (e) {
                            var $table = $(this).closest('.bookly-js-schedule-form'),
                                $row = $table.parents('.staff-schedule-item-row').first(),
                                $data = {
                                    action: 'bookly_staff_schedule_handle_break',
                                    staff_schedule_item_id: $row.data('staff_schedule_item_id'),
                                    start_time: $table.find('.break-start > option:selected').val(),
                                    end_time: $table.find('.break-end > option:selected').val(),
                                    working_end: $row.find('.working-schedule-end > option:selected').val(),
                                    working_start: $row.find('.working-schedule-start > option:selected').val(),
                                    csrf_token: obj.options.l10n.csrf_token
                                },
                                $break_interval_wrapper = $table.parents('.bookly-intervals-wrapper').first(),
                                ladda = Ladda.create(e.currentTarget);
                            ladda.start();

                            if ($break_interval_wrapper.data('break_id')) {
                                $data['break_id'] = $break_interval_wrapper.data('break_id');
                            }
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: $data,
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success) {
                                        if (response['item_content']) {
                                            var $new_break_interval_item = $(response['item_content']);
                                            $new_break_interval_item
                                                .hide()
                                                .appendTo($row.find('.breaks-list-content'))
                                                .fadeIn('slow');
                                        } else if (response.data.interval) {
                                            $break_interval_wrapper
                                                .find('.break-interval')
                                                .text(response.data.interval);
                                        }
                                        $('.popover').popover('hide');
                                    } else {
                                        obj.options.booklyAlert({error: [response.data.message]});
                                    }
                                },
                                complete: function () {
                                    ladda.stop();
                                }
                            });

                            return false;
                        })

                        .on('click', '.bookly-intervals-wrapper .delete-break', function () {
                            deleteBreak.call(this);
                        })

                        .on('change', '.break-start', function () {
                            var $start = $(this);
                            var $end = $start.parents('.bookly-flexbox').find('.break-end');
                            hideInaccessibleBreaks($start, $end);
                        })

                        .on('change', '.working-schedule-start', function () {
                            var $this = $(this),
                                $end_select = $this.closest('.bookly-flexbox').find('.working-schedule-end'),
                                start_time = $this.val();

                            // Hide end time options to keep them within 24 hours after start time.
                            var parts = start_time.split(':');
                            parts[0] = parseInt(parts[0]) + 24;
                            var end_time = parts.join(':');
                            var frag = document.createDocumentFragment();
                            var old_value = $end_select.val();
                            var new_value = null;
                            $('option', $end_select).each(function () {
                                if (this.value <= start_time || this.value > end_time) {
                                    var span = document.createElement('span');
                                    span.style.display = 'none';
                                    span.appendChild(this.cloneNode(true));
                                    frag.appendChild(span);
                                } else {
                                    frag.appendChild(this.cloneNode(true));
                                    if (new_value === null || old_value == this.value) {
                                        new_value = this.value;
                                    }
                                }
                            });
                            $end_select.empty().append(frag).val(new_value);

                            // when the working day is disabled (working start time is set to 'OFF')
                            // hide all the elements inside the row
                            if (!$this.val()) {
                                $this.closest('.row').find('.bookly-hide-on-off').hide();
                            } else {
                                $this.closest('.row').find('.bookly-hide-on-off').show();
                            }
                        })
                        // Change location
                        .on('change', '#staff_location_id', function () {
                            $('.tab-pane > div').hide();
                            var get_staff_schedule = {
                                    action    : options.get_staff_schedule.action,
                                    staff_id  : options.get_staff_schedule.staff_id,
                                    csrf_token: options.get_staff_schedule.csrf_token,
                                },
                                staff_location_id  = $('#staff_location_id', $container).val();
                            if ( staff_location_id != '') {
                                get_staff_schedule['location_id'] = staff_location_id;
                            }
                            new BooklyStaffSchedule($container, {
                                get_staff_schedule: get_staff_schedule,
                                l10n              : options.l10n,
                                refresh           : true
                            });

                            $container.show();
                        })
                        // Change default/custom settings for location
                        .on('change', '#custom_location_settings', function () {
                            if ($(this).val() == 1) {
                                $('.panel', $container).show();
                            } else {
                                $('.panel', $container).hide();
                            }
                        })
                    ;

                    $('#custom_location_settings', $container).trigger('change');
                    $('.working-schedule-start', $container).trigger('change');
                    $('.break-start', $container).trigger('change');
                }
            });
        }

        function hideInaccessibleBreaks($start, $end, force_keep_values) {
            var $row = $start.closest('.row'),
                $working_start = $row.find('.working-schedule-start'),
                $working_end = $row.find('.working-schedule-end'),
                frag1 = document.createDocumentFragment(),
                frag2 = document.createDocumentFragment(),
                old_value = $start.val(),
                new_value = null;

            $('option', $start).each(function () {
                if ((this.value < $working_start.val() || this.value >= $working_end.val()) && (!force_keep_values || this.value != old_value)) {
                    var span = document.createElement('span');
                    span.style.display = 'none';
                    span.appendChild(this.cloneNode(true));
                    frag1.appendChild(span);
                } else {
                    frag1.appendChild(this.cloneNode(true));
                    if (new_value === null || old_value == this.value) {
                        new_value = this.value;
                    }
                }
            });
            $start.empty().append(frag1).val(new_value);

            // Hide end time options with value less than in the start time.
            old_value = $end.val();
            new_value = null;
            $('option', $end).each(function () {
                if ((this.value <= $start.val() || this.value > $working_end.val()) && (!force_keep_values || this.value != old_value)) {
                    var span = document.createElement('span');
                    span.style.display = 'none';
                    span.appendChild(this.cloneNode(true));
                    frag2.appendChild(span);
                } else {
                    frag2.appendChild(this.cloneNode(true));
                    if (new_value === null || old_value == this.value) {
                        new_value = this.value;
                    }
                }
            });
            $end.empty().append(frag2).val(new_value);
        }

        function deleteBreak() {
            var $break_interval_wrapper = $(this).closest('.bookly-intervals-wrapper');
            if (confirm(obj.options.l10n.are_you_sure)) {
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {action: 'bookly_delete_staff_schedule_break', id: $break_interval_wrapper.data('break_id'), csrf_token: obj.options.l10n.csrf_token},
                    success: function (response) {
                        if (response.success) {
                            $break_interval_wrapper.remove();
                        }
                    },
                    complete: function () {
                        ladda.stop();
                    }
                });
            }
        }
    };

    Schedule.prototype.options = {
        get_staff_schedule: {
            action: 'bookly_get_staff_schedule',
            staff_id: -1,
            csrf_token: ''
        },
        booklyAlert: window.booklyAlert,
        l10n: {}
    };

    window.BooklyStaffSchedule = Schedule;
});

