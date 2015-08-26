jQuery(function($) {

    var $name_input = $('#ab-newstaff-fullname'),
        $email_input = $('#ab-newstaff-email'),
        $list_item_number = $('#ab-list-item-number'),
        $wp_user_select = $('#ab-newstaff-wpuser'),
        $staff_list = $('#ab-staff-list'),
        $staff_member = $('.ab-staff-member'),
        $edit_form = $('#ab-edit-staff-member'),
        $new_form = $('#ab-new-satff');

    function saveNewForm() {
        $('#lite_notice').modal('show');
    }

    // Saves new staff form
    $('#ab-save-newstaff').on('click', function () {
        saveNewForm();
    });

    // Save new staff on enter press
    $name_input.on('keypress', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            saveNewForm();
        }
    });

    // Close new staff form on esc
    $new_form.on('keypress', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 27) {
            $('.ab-popup-wrapper').ab_popup('close');
        }
    });

    function loadEditForm() {
        // Marks selected element as active
        $(this).parent().find('.ab-active').removeClass('ab-active');
        $(this).addClass('ab-active');

        var staff_id = $(this).data('staff-id');
        var active_tab_id = $('ul.nav li.active a').attr('id');
        $edit_form.html('<div class="loading-indicator"><span class="ab-loader"></span></div>');
        $.get(ajaxurl, { action: 'ab_edit_staff', id: staff_id }, function (response) {
            $edit_form.html(response);

            var service_container  = $('#ab-staff-services-container'),
                loading_indicator  = $('.loading-indicator'),
                details_container  = $('#ab-staff-details-container'),
                schedule_container = $('#ab-staff-schedule-container'),
                holidays_container = $('#ab-staff-holidays-container'),
                $schedule_form,
                services_form;

            initHelps();
            initNotices();
            Ladda.bind( 'button[type=submit]' );

            // Deletes staff avatar
            $('#ab-delete-avatar').on('click', function () {
                $.post(ajaxurl, { action: 'ab_delete_staff_avatar', id: staff_id }, function () {
                    $('#ab-staff-avatar-image', $edit_form).remove();
                });
            });

            $('#ab-update-staff').on('click', function (e) {
                if (!validateForm($edit_form)) {
                    e.preventDefault(e);
                    e.stopPropagation(e);
                } else {
                    $edit_form.phone = $("#ab-staff-phone").intlTelInput('getNumber');
                }
            });

            $('#ab-staff-wpuser').on('change', function () {
                if ($(this).val()) {
                    $('#ab-staff-full-name').val($(this).find(':selected').text());
                    $('#ab-staff-email').val($(this).find(':selected').data('email'));
                }
            });

            // Opens services tab
            $('#ab-staff-services-tab').on('click', function () {
                activateTab.call($(this));
                service_container.show();

                // Loads services form
                if (!service_container.children().length) {
                    loading_indicator.show();
                    $.post(ajaxurl, { action: 'ab_staff_services', id: staff_id }, function (response) {
                        service_container.html(response);
                        services_form = $('form', service_container);

                        var auto_tick_checkboxes = function() {
                            // Handle 'select category' checkbox.
                            $('.ab-services-category .ab-category-checkbox').each(function() {
                                $(this).prop(
                                    'checked',
                                    $('.ab-category-services .ab-service-checkbox.ab-category-' + $(this).data('category-id') + ':not(:checked)').length == 0
                                );
                            });
                            // Handle 'select all services' checkbox.
                            $('#ab-all-services').prop(
                                'checked',
                                $('.ab-service-checkbox:not(:checked)').length == 0
                            );
                        };

                        // Select all services related to chosen category
                        $('.ab-category-checkbox', services_form).bind('click', function () {
                            $('.ab-category-services .ab-category-' + $(this).data('category-id')).prop('checked', $(this).is(':checked')).change();
                            auto_tick_checkboxes();
                        });

                        // Check and uncheck all services
                        $('#ab-all-services').on('click', function () {
                            $('.ab-service-checkbox', services_form).prop('checked', $(this).is(':checked')).change();
                            $('.ab-category-checkbox').prop('checked', $(this).is(':checked'));
                        });

                        // Select service
                        $('.ab-service-checkbox', services_form).on('click', function () {
                            var $this  = $(this);
                            var $price = $this.closest('li').find('.ab-price');
                            $this.is(':checked') ? $price.removeAttr('disabled') : $price.attr('disabled', true);
                            auto_tick_checkboxes();
                        });

                        $('.ab-service-checkbox',services_form).on('change', function(){
                            var $input_fields = $('.ab-price[name="price['+$(this).val()+']"]').add('.ab-price[name="capacity['+$(this).val()+']"]');
                            $(this).is(':checked') ? $input_fields.removeAttr('disabled') : $input_fields.attr('disabled', true);
                        });

                        // Saves services
                        $('#ajax-send-service').on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            $.post(ajaxurl, services_form.serialize(), function (response) {
                                ladda.stop();
                                if (response.success) {
                                    showSuccessNotice();
                                }
                            });
                        });

                        // After reset auto tick group checkboxes.
                        $('button[type=reset]').on('click', function() {
                            setTimeout(auto_tick_checkboxes, 0);
                        });

                        auto_tick_checkboxes();
                        loading_indicator.hide();

                        services_form.find('input[name^="capacity"]').on('change', function(){
                            if($(this).val() > 1) {
                                $(this).val(1);
                                $('#lite_notice').modal('show');
                            }
                        });
                    });
                }
            });

            // Opens schedule tab
            $('#ab-staff-schedule-tab').on('click', function () {
                activateTab.call($(this));
                schedule_container.show();

                // Loads schedule list
                if (!schedule_container.children().length) {
                    loading_indicator.show();
                    $.post(ajaxurl, { action: 'ab_staff_schedule', id: staff_id }, function (response) {
                        // fill in the container
                        schedule_container.html(response);
                        $schedule_form = $('form', schedule_container);

                        initNotices();
                        // Resets initial values
                        $('#ab-schedule-reset').on('click', function (e) {
                            e.preventDefault();
                            $('.working-start', schedule_container).each(function () {
                                $(this).val($(this).data('default_value'));
                                $(this).trigger('change');
                            });

                            $('.working-end', schedule_container).each(function () {
                                $(this).val($(this).data('default_value'));
                            });

                            // reset breaks
                            $.ajax({
                                url      : ajaxurl,
                                type     : 'POST',
                                data     : { action : 'ab_reset_breaks', breaks : $(this).data('default-breaks') },
                                dataType : 'json',
                                success  : function(response) {
                                    for (var k in response) {
                                        var $content = $(response[k]);

                                        $("[data-staff_schedule_item_id=" + k +"] .breaks", schedule_container).html($content);

                                        $content.find('.break-interval').on('click', function(){
                                            markBreakIntervalActive.call($(this));
                                        }).end().find('.break-interval-wrapper .delete-break').on('click', function(){
                                            deleteStaffScheduleBreakInterval.call($(this));
                                        }).end().find('.break-start').on('change', function(){
                                            var start = $(this);
                                            var end   = start.parent().find('.break-end');
                                            checkStaffScheduleBreakTimeRange(start,end);
                                        });

                                        schedule_container.find('.ab-popup-wrapper').ab_popup();
                                    }
                                }
                            });
                        });

                        $('#ajax-send-staff-schedule', schedule_container).on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            var data = {};
                            $('select.working-start, select.working-end, input:hidden', $schedule_form).each(function() {
                                data[this.name] = this.value;
                            });
                            $.post(ajaxurl, $.param(data), function (response) {
                                ladda.stop();
                                showSuccessNotice();
                            });
                        });

                        // init "add break" functionality
                        schedule_container.find('.ab-popup-wrapper').ab_popup();
                        schedule_container.find('.ab-popup-trigger:not(.break-interval)').on('click', function() {
                            var $row                = $(this).closest('.staff-schedule-item-row'),
                                working_start       = $row.find('.working-start').val(),
                                $break_start        = $row.find('.add-break .break-start'),
                                $break_end          = $row.find('.add-break .break-end'),
                                working_start_time  = working_start.split(':'),
                                working_start_hours = parseInt(working_start_time[0], 10);

                            var break_start_hours  = working_start_hours + 1;
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
                            // Set $break_end value after DOM update.
                            setTimeout(function() { $break_end.val(break_end_hours_str); }, 0);
                        });

                        // when the working day is disabled (working start time is set to "OFF")
                        // hide all the elements inside the row
                        schedule_container.find('.working-start').on('change', function(){
                            var $this = $(this),
                                $row  = $this.parents('.staff-schedule-item-row');

                            if (!$this.val()) {
                                $row.find('.hide-on-non-working-day').attr('style','display:none!important;');
                            } else {
                                $row.find('.hide-on-non-working-day').show();
                            }
                        });

                        $('.working-start', schedule_container).on('change', function () {
                            var $this = $(this),
                                $end_select = $this.nextAll('.working-end'),
                                start_time = $this.val();

                            // Hide end time options to keep them within 24 hours after start time.
                            var parts = start_time.split(':');
                            parts[0] = parseInt(parts[0]) + 24;
                            var end_time = parts.join(':');
                            $('option', $end_select).each(function () {
                                var $this = $(this);
                                if (this.value <= start_time || this.value > end_time) {
                                    if ($this.parent().is(':not(span)')) {
                                        $this.wrap("<span>").parent().hide();
                                    }
                                } else if ($this.parent().is('span')) {
                                    $this.unwrap();
                                }
                            });
                        }).trigger('change');

                        schedule_container.find('.ab-popup-wrapper .break-interval').on('click', function(){
                            markBreakIntervalActive.call($(this));
                        });

                        schedule_container.find('.break-interval-wrapper .delete-break').on('click', function(){
                            deleteStaffScheduleBreakInterval.call($(this));
                        });

                        schedule_container.find('.break-start').on('change', function(){
                            var start = $(this);
                            var end   = start.parent().find('.break-end');
                            checkStaffScheduleBreakTimeRange(start,end);
                        }).trigger('change');

                        schedule_container.delegate('.ab-save-break', 'click', function() {
                            var $table                  = $(this).closest('table'),
                                $row                    = $table.parents('.staff-schedule-item-row').first(),
                                $break_list_label       = $row.find('.breaks-list-label span'),
                                $break_interval_wrapper = $table.parents('.break-interval-wrapper').first(),
                                $error                  = $table.parents('.ab-popup-wrapper').find('.error'),
                                $data                   = {
                                    action                 : 'ab_staff_schedule_handle_break',
                                    working_start          : $row.find('.working-start > option:selected').val(),
                                    working_end            : $row.find('.working-end > option:selected').val(),
                                    start_time             : $table.find('.break-start > option:selected').val(),
                                    end_time               : $table.find('.break-end > option:selected').val(),
                                    staff_schedule_item_id : $row.data('staff_schedule_item_id')
                                };

                            if ($break_interval_wrapper.data('break_id')) {
                                $data['break_id'] = $break_interval_wrapper.data('break_id');
                            }

                            $.post(
                                ajaxurl,
                                $data,
                                function (response) {
                                    if (response['success']) {
                                        if (response['item_content']) {
                                            var $new_break_interval_item = $(response['item_content']);
                                            $new_break_interval_item
                                              .hide()
                                              .appendTo($row.find('.breaks-list-content'))
                                              .fadeIn('slow');
                                            $new_break_interval_item.find('.break-interval').on('click', function(){
                                                markBreakIntervalActive.call($(this));
                                            });
                                            $new_break_interval_item.find('.delete-break').on('click', function(){
                                                deleteStaffScheduleBreakInterval.call($(this));
                                            });
                                            if ($break_list_label.is(':hidden')) {
                                                $break_list_label.fadeIn('slow');
                                            }
                                            schedule_container.find('.ab-popup-wrapper').ab_popup();
                                        } else if (response['new_interval']) {
                                            $break_interval_wrapper
                                              .find('.break-interval.active')
                                              .text(response['new_interval'])
                                              .removeClass('active');
                                        }
                                        $row.find('.ab-popup-wrapper').ab_popup('close');
                                    } else {
                                        $error.text(response['error_msg']);
                                        $error.slideDown();
                                        var t = setTimeout(function() {
                                            $error.hide();
                                            clearTimeout(t);
                                        }, 3000);
                                    }
                                },
                                'json'
                            );

                            return false;
                        });
                        loading_indicator.hide();
                    });
                }
            });

            // Opens details tab
            $('#ab-staff-details-tab').on('click', function () {
                activateTab.call($(this));
                details_container.show();
            });

            // Opens "Days off" tab
            $('#ab-staff-holidays-tab').on('click', function () {
                activateTab.call($(this));
                holidays_container.show();

                if (!holidays_container.children().length) {
                    loading_indicator.show();
                    holidays_container.load(ajaxurl, { action: 'ab_staff_holidays', id: staff_id }, function(){ loading_indicator.hide(); });
                }
            });

            function markBreakIntervalActive() {
                $(this).parents('.breaks-list-content').find('.break-interval').removeClass('.active');
                $(this).addClass('active');
            }

            function checkStaffScheduleBreakTimeRange( start, end )
            {
                var start_time = start.val();

                $('span > option', end).each(function () {
                    if (start_time < $(this).val()) {
                        $(this).unwrap();
                    }
                });

                // Hides end time options with value less than in the start time
                $('option', end).each(function () {
                    if ($(this).val() <= start_time) {
                        $(this).wrap("<span>").parent().hide();
                    }
                });
            }

            function deleteStaffScheduleBreakInterval() {
                var $break_interval_wrapper = $(this).parent(),
                    $row = $break_interval_wrapper.parents('.staff-schedule-item-row').first();
                if (confirm(BooklyL10n.are_you_sure)) {
                    $.post(ajaxurl, { action: 'ab_delete_staff_schedule_break', id: $break_interval_wrapper.data('break_id') }, function (response) {
                        $break_interval_wrapper.fadeOut(700, function() {
                            $(this).remove();
                            if (!$row.find('.break-interval-wrapper').length) {
                                $row.find('.breaks-list-label span').hide();
                            }
                        });
                    });
                }
            }

            function activateTab() {
                $('.ab-staff-tab-content').not(':hidden').hide();
            }

            $('#' + active_tab_id).click();

            $("#ab-staff-phone").intlTelInput({
                preferredCountries: [BooklyL10n.country],
                defaultCountry: BooklyL10n.country,
                geoIpLookup: function(callback) {
                    $.get(ajaxurl, {action: 'ab_ip_info'}, function() {}, 'json').always(function(resp) {
                        var countryCode = (resp && resp.country) ? resp.country : '';
                        callback(countryCode);
                    });
                },
                utilsScript: BooklyL10n.intlTelInput_utils
            });

        });
    }

    function initHelps() {
        // Popovers initialization.
        $('.ab-popover').popover({
            trigger : 'hover'
        });
        $('.ab-popover-ext').popover({
            trigger : 'hover',
            content: function() {
                return $('#' + $(this).data('ext_id')).html()
            },
            html: true
        });
    }

    function initNotices() {
        $('.notice.is-dismissible').each( function() {
            var $this = $(this),
                $button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>'),
                btnText = commonL10n.dismiss || '';

            $button.find('.screen-reader-text').text(btnText);

            $this.append($button);

            $button.on('click.wp-dismiss-notice', function(event) {
                event.preventDefault();
                $this.fadeTo(100, 0, function() {
                    $(this).slideUp(100);
                });
            });
        });
    }

    function showSuccessNotice() {
        $('.notice.notice-success').slideDown(100).fadeTo(100, 100);
    }

    $wp_user_select.on('change', function () {
        if ($(this).val()) {
            $name_input.val($(this).find(':selected').text());
        }
    });

    $staff_member.click(function () {
        loadEditForm.call(this);
    });
    $staff_member.filter('[data-active="true"]').click();

    initHelps();

    var $staff = $('ul#ab-staff-list');
    $staff.sortable({
        axis   : 'y',
        handle : '.ab-handle',
        update : function( event, ui ) {
            var data = [];
            $staff.children('li').each(function() {
                var $this = $(this);
                var position = $this.data('staff-id');
                data.push(position);
            });
            $.ajax({
                type : 'POST',
                url  : ajaxurl,
                data : { action: 'ab_update_staff_position', position: data }
            });
        }
    });

});
