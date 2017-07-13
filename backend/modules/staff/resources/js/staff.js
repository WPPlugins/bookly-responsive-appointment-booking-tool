jQuery(function($) {
    var $staff_list       = $('#bookly-staff-list'),
        $new_form         = $('#bookly-new-staff'),
        $wp_user_select   = $('#bookly-new-staff-wpuser'),
        $name_input       = $('#bookly-new-staff-fullname'),
        $edit_form        = $('#bookly-container-edit-staff')
    ;
    function saveNewForm() {
        booklyAlert({error: [BooklyL10n.limitations]});
    }

    $edit_form.on('click', '.bookly-pretty-indicator', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection').toJSON(),
                img_src
                ;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                $edit_form.find('[name=attachment_id]').val(selection[0].id);
                $('#bookly-js-staff-avatar').find('.bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                $('.bookly-thumb-delete').show();
                $(this).hide();
            }
        });

        frame.open();
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
            $('#ab-newstaff-member').popover('hide');
        }
    });

    $staff_list.on('click', '.bookly-js-handle', function (e) {
        e.stopPropagation();
    });

    /**
     * Load staff profile on click on staff in the list.
     */
    $staff_list.on('click', 'li', function() {
        var $this = $(this);
        // Mark selected element as active
        $staff_list.find('.active').removeClass('active');
        $this.addClass('active');

        var staff_id = $this.data('staff-id');
        var active_tab_id = $('.nav .active a').attr('id');
        $edit_form.html('<div class="bookly-loading"></div>');
        $.get(ajaxurl, { action: 'bookly_edit_staff', id: staff_id }, function (response) {
            $edit_form.html(response.data.html);
            booklyAlert(response.data.alert);
            var $loading_indicator   = $('.bookly-loading'),
                $details_container   = $('#bookly-details-container'),
                $services_container  = $('#bookly-services-container'),
                $schedule_container  = $('#bookly-schedule-container'),
                $holidays_container  = $('#bookly-holidays-container'),
                $delete_staff_button = $('#bookly-delete'),
                $save_staff_button   = $('#bookly-save'),
                $staff_full_name     = $('#bookly-full-name'),
                $staff_wp_user       = $('#bookly-wp-user'),
                $staff_email         = $('#bookly-email'),
                $staff_phone         = $('#bookly-phone'),
                $schedule_form,
                $services_form;

            if (BooklyL10n.intlTelInput.enabled) {
                $staff_phone.intlTelInput({
                    preferredCountries: [BooklyL10n.intlTelInput.country],
                    defaultCountry: BooklyL10n.intlTelInput.country,
                    geoIpLookup: function (callback) {
                        $.get(ajaxurl, {action: 'bookly_ip_info'}, function () {}, 'json').always(function (resp) {
                            var countryCode = (resp && resp.country) ? resp.country : '';
                            callback(countryCode);
                        });
                    },
                    utilsScript: BooklyL10n.intlTelInput.utils
                });
            }

            // Delete staff member.
            $delete_staff_button.on('click', function (e) {
                e.preventDefault();
                if (confirm(BooklyL10n.are_you_sure)) {
                    booklyAlert({error: [BooklyL10n.limitations]});
                }
            });

            // Save staff member details.
            $save_staff_button.on('click',function(e){
                e.preventDefault();
                var $form = $(this).closest('form'),
                    data  = $form.serializeArray(),
                    ladda = Ladda.create(this),
                    phone;
                try {
                    phone = BooklyL10n.intlTelInput.enabled ? $staff_phone.intlTelInput('getNumber') : $staff_phone.val();
                    if (phone == '') {
                        phone = $staff_phone.val();
                    }
                } catch (error) {  // In case when intlTelInput can't return phone number.
                    phone = $staff_phone.val();
                }
                data.push({name: 'action', value: 'bookly_update_staff'});
                data.push({name: 'phone',  value: phone});
                ladda.start();
                $.post(ajaxurl, data, function (response) {
                    if (response.success) {
                        booklyAlert({success : [BooklyL10n.saved]});
                        // Update staff name throughout the page.
                        $('.bookly-js-staff-name-1').text($staff_full_name.val());
                        // Update wp users in new staff form.
                        $wp_user_select.children(':not(:first)').remove();
                        $.each(response.data.wp_users, function (index, wp_user) {
                            var $option = $('<option>')
                                .data('email', wp_user.user_email)
                                .val(wp_user.ID)
                                .text(wp_user.display_name);
                            $wp_user_select.append($option);
                        });
                    } else {
                        booklyAlert({error : [response.data.error]});
                    }
                    ladda.stop();
                });
            });

            // Delete staff avatar
            $('.bookly-thumb-delete').on('click', function () {
                var $thumb = $(this).parents('.bookly-js-image');
                $.post(ajaxurl, {action: 'bookly_delete_staff_avatar', id: 1}, function () {
                    $thumb.attr('style', '');
                    $edit_form.find('[name=attachment_id]').val('');
                });
            });

            $staff_wp_user.on('change', function () {
                if (this.value) {
                    $staff_full_name.val($staff_wp_user.find(':selected').text());
                    $staff_email.val($staff_wp_user.find(':selected').data('email'));
                }
            });

            $('input.all-locations, input.location').on('change', function () {
                var $panel = $(this).parents('.locations-row');
                if ($(this).hasClass('all-locations')) {
                    $panel.find('.location').prop('checked', $(this).prop('checked'));
                } else {
                    $panel.find('.all-locations').prop('checked', $panel.find('.location:not(:checked)').length == 0);
                }
                updateLocationsButton($panel);
            });

            function updateLocationsButton($panel) {
                var locations_checked = $panel.find('.location:checked').length;
                if (locations_checked == 0) {
                    $panel.find('.locations-count').text(BooklyL10n.selector.nothing_selected);
                } else if (locations_checked == 1) {
                    $panel.find('.locations-count').text($panel.find('.location:checked').data('location_name'));
                } else {
                    if (locations_checked == $panel.find('.location').length) {
                        $panel.find('.locations-count').text(BooklyL10n.selector.all_selected);
                    } else {
                        $panel.find('.locations-count').text(locations_checked + '/' + $panel.find('.location').length);
                    }
                }
            }
            updateLocationsButton($('.locations-row'));

            // Open details tab
            $('#bookly-details-tab').on('click', function () {
                $('.tab-pane > div').hide();
                $details_container.show();
            });

            // Open services tab
            $('#bookly-services-tab').on('click', function () {
                $('.tab-pane > div').hide();
                $services_container.show();

                // Load services form
                if (!$services_container.children().length) {
                    $loading_indicator.show();
                    $.post(ajaxurl, {action: 'bookly_staff_services', id: 1}, function (response) {
                        $services_container.html(response);
                        $services_form = $('form', $services_container);
                        $(document.body).trigger( 'special_hours.tab_init', [ $services_container ] );
                        var autoTickCheckboxes = function () {
                            // Handle 'select category' checkbox.
                            $('.ab-services-category .bookly-category-checkbox').each(function () {
                                $(this).prop(
                                    'checked',
                                    $('.bookly-category-services .ab-service-checkbox.bookly-category-' + $(this).data('category-id') + ':not(:checked)').length == 0
                                );
                            });
                            // Handle 'select all services' checkbox.
                            $('#bookly-check-all-entities').prop(
                                'checked',
                                $('.ab-service-checkbox:not(:checked)').length == 0
                            );
                        };

                        $('input[name^="capacity"]', $services_container).on('change', function () {
                            $(this).val(1);
                            $(this).prop('readonly',true);
                            booklyAlert({error: [BooklyL10n.limitations]});
                        });

                        // Select all services related to chosen category
                        $('.bookly-category-checkbox', $services_form).on('click', function () {
                            $('.bookly-category-services .bookly-category-' + $(this).data('category-id')).prop('checked', $(this).is(':checked')).change();
                            autoTickCheckboxes();
                        });

                        // Check and uncheck all services
                        $('#bookly-check-all-entities').on('click', function () {
                            $('.ab-service-checkbox', $services_form).prop('checked', $(this).is(':checked')).change();
                            $('.bookly-category-checkbox').prop('checked', $(this).is(':checked'));
                        });

                        // Select service
                        $('.ab-service-checkbox', $services_form).on('click', function () {
                            autoTickCheckboxes();
                        }).on('change', function () {
                            var $this   = $(this);
                            var $inputs = $this.closest('li').find('input:not(:checkbox)');
                            $inputs.attr('disabled', ! $this.is(':checked'));
                        });

                        // Save services
                        $('#bookly-services-save').on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            $.post(ajaxurl, $services_form.serialize(), function (response) {
                                ladda.stop();
                                if (response.success) {
                                    booklyAlert({success : [BooklyL10n.saved]});
                                }
                            });
                        });

                        // After reset auto tick group checkboxes.
                        $('#bookly-services-reset').on('click', function () {
                            setTimeout(function() {
                                autoTickCheckboxes();
                                $('.ab-service-checkbox', $services_form).trigger('change');
                            }, 0);
                        });

                        autoTickCheckboxes();
                        $loading_indicator.hide();
                    });
                }
            });

            // Open 'Special Days' tab
            $('#bookly-special-days-tab').on('click', function () {
                $(document.body).trigger( 'special_days.tab_show', [ 1, $loading_indicator ] );
            });

            // Open schedule tab
            $('#bookly-schedule-tab').on('click', function () {
                $('.tab-pane > div').hide();
                $schedule_container.show();

                // Loads schedule list
                if (!$schedule_container.children().length) {
                    $loading_indicator.show();
                    $.post(ajaxurl, {action: 'bookly_staff_schedule', id: 1}, function (response) {
                        // fill in the container
                        $schedule_container.html(response);
                        $schedule_form = $('form', $schedule_container);

                        // Resets initial values
                        $('#bookly-schedule-reset').on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();

                            $('.working-schedule-start', $schedule_container).each(function () {
                                $(this).val($(this).data('default_value'));
                                $(this).trigger('change');
                            });

                            $('.working-schedule-end', $schedule_container).each(function () {
                                $(this).val($(this).data('default_value'));
                            });

                            // reset breaks
                            $.ajax({
                                url      : ajaxurl,
                                type     : 'POST',
                                data     : { action : 'bookly_reset_breaks', breaks : $(this).data('default-breaks') },
                                dataType : 'json',
                                success  : function(response) {
                                    for (var k in response) {
                                        var $content = $(response[k]);
                                        $('[data-staff_schedule_item_id=' + k +'] .breaks', $schedule_container).html($content);
                                        $content.find('.bookly-intervals-wrapper .delete-break').on('click', function(){
                                            deleteBreak.call(this);
                                        });
                                    }
                                },
                                complete : function() {
                                    ladda.stop();
                                }
                            });
                        });

                        $('#bookly-schedule-save').on('click', function (e) {
                            e.preventDefault();
                            var ladda = Ladda.create(this);
                            ladda.start();
                            var data = {};
                            $('select.working-schedule-start, select.working-schedule-end, input:hidden', $('#bookly-schedule-container')).each(function () {
                                data[this.name] = this.value;
                            });
                            $.post(ajaxurl, $.param(data), function (response) {
                                ladda.stop();
                                booklyAlert({success : [BooklyL10n.saved]});
                            });
                        });

                        // init 'add break' functionality
                        $('.bookly-js-toggle-popover:not(.break-interval)').popover({
                            html     : true,
                            placement: 'bottom',
                            template : '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
                            trigger  : 'manual',
                            content  : function () {
                                return $($(this).data('popover-content')).html()
                            }
                        }).on('click', function () {
                            $(this).popover('toggle');

                            var $popover      = $(this).next('.popover'),
                                working_start = $popover.closest('.row').find('.working-schedule-start').val(),
                                $break_start  = $popover.find('.break-start'),
                                $break_end    = $popover.find('.break-end'),
                                working_start_time  = working_start.split(':'),
                                working_start_hours = parseInt(working_start_time[0], 10),
                                break_start_hours   = working_start_hours + 1;
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

                        $schedule_container.on('click', '.break-interval', function () {
                            $('.popover').popover('hide');
                            var break_id = $(this).closest('.bookly-intervals-wrapper').data('break_id');
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

                            var $popover     = $(this).next('.popover'),
                                $break_start = $popover.find('.break-start'),
                                $break_end   = $popover.find('.break-end');

                            hideInaccessibleBreaks($break_start, $break_end, true);

                            $popover.find('.bookly-popover-close').on('click', function () {
                                $popover.popover('hide');
                            });
                        });

                        $schedule_container.on('click', '.bookly-js-save-break', function (e) {
                            var $table = $(this).closest('.bookly-js-schedule-form'),
                                $row   = $table.parents('.staff-schedule-item-row').first(),
                                $data  = {
                                    action        : 'bookly_staff_schedule_handle_break',
                                    staff_schedule_item_id : $row.data('staff_schedule_item_id'),
                                    start_time    : $table.find('.break-start > option:selected').val(),
                                    end_time      : $table.find('.break-end > option:selected').val(),
                                    working_end   : $row.find('.working-schedule-end > option:selected').val(),
                                    working_start : $row.find('.working-schedule-start > option:selected').val()
                                },
                                $break_interval_wrapper = $table.parents('.bookly-intervals-wrapper').first(),
                                ladda = Ladda.create(e.currentTarget);
                            ladda.start();

                            if ($break_interval_wrapper.data('break_id')) {
                                $data['break_id'] = $break_interval_wrapper.data('break_id');
                            }

                            $.post(ajaxurl, $data, function (response) {
                                    if (response.success) {
                                        if (response['item_content']) {
                                            var $new_break_interval_item = $(response['item_content']);
                                            $new_break_interval_item
                                                .hide()
                                                .appendTo($row.find('.breaks-list-content'))
                                                .fadeIn('slow');
                                            $new_break_interval_item.find('.delete-break').on('click', function () {
                                                deleteBreak.call(this);
                                            });
                                        } else if (response.data.interval) {
                                            $break_interval_wrapper
                                                .find('.break-interval')
                                                .text(response.data.interval);
                                        }
                                        $('.popover').popover('hide');
                                    } else {
                                        booklyAlert({error : [response.data.message]});
                                    }
                                },
                                'json'
                            ).always(function () {
                                ladda.stop()
                            });

                            return false;
                        });

                        $schedule_container.on('change', '.break-start', function() {
                            var $start = $(this);
                            var $end = $start.parents('.bookly-flexbox').find('.break-end');
                            hideInaccessibleBreaks($start, $end);
                        }).trigger('change');

                        $('.working-schedule-start', $schedule_container).on('change', function () {
                            var $this = $(this),
                                $end_select = $this.closest('.bookly-flexbox').find('.working-schedule-end'),
                                start_time = $this.val();

                            // Hide end time options to keep them within 24 hours after start time.
                            var parts     = start_time.split(':');
                            parts[0]      = parseInt(parts[0]) + 24;
                            var end_time  = parts.join(':');
                            var frag      = document.createDocumentFragment();
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
                        }).trigger('change');

                        $schedule_container.find('.bookly-intervals-wrapper .delete-break').on('click', function() {
                            deleteBreak.call(this);
                        });

                        $loading_indicator.hide();
                    });
                }
            });

            // Open 'Days off' tab
            $('#bookly-holidays-tab').on('click', function () {
                $('.tab-pane > div').hide();
                $holidays_container.show();

                if (!$holidays_container.children().length) {
                    $loading_indicator.show();
                    $holidays_container.load(ajaxurl, { action: 'bookly_staff_holidays', id: 1 }, function(){ $loading_indicator.hide(); });
                }
            });

            function hideInaccessibleBreaks( $start, $end, force_keep_values ) {
                var $row           = $start.closest('.row'),
                    $working_start = $row.find('.working-schedule-start'),
                    $working_end   = $row.find('.working-schedule-end'),
                    frag1          = document.createDocumentFragment(),
                    frag2          = document.createDocumentFragment(),
                    old_value      = $start.val(),
                    new_value      = null;

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
                if (confirm(BooklyL10n.are_you_sure)) {
                    var ladda = Ladda.create(this);
                    ladda.start();
                    $.post(ajaxurl, { action: 'bookly_delete_staff_schedule_break', id: $break_interval_wrapper.data('break_id') }, function (response) {
                        if (response.success) {
                            $break_interval_wrapper.remove();
                        }
                    }).always(function () {
                        ladda.stop()
                    });
                }
            }

            $('#' + active_tab_id).click();
        });
    }).find('li.active').click();

    $wp_user_select.on('change', function () {
        if (this.value) {
            $name_input.val($(this).find(':selected').text());
        }
    });

    $staff_list.sortable({
        axis   : 'y',
        handle : '.bookly-js-handle',
        update : function( event, ui ) {
            var data = [];
            $staff_list.children('li').each(function() {
                var $this = $(this);
                var position = $this.data('staff-id');
                data.push(position);
            });
            $.ajax({
                type : 'POST',
                url  : ajaxurl,
                data : { action: 'bookly_update_staff_position', position: data }
            });
        }
    });

    $('#ab-newstaff-member').popover({
        html: true,
        placement: 'bottom',
        template: '<div class="popover" style="width: calc(100% - 20px)" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        content: $new_form.show().detach(),
        trigger: 'manual'
    }).on('click', function () {
        var $button = $(this);
        $button.popover('toggle');
        var $popover = $button.next('.popover');
        $popover.find('.bookly-js-save-form').on('click', function () {
            saveNewForm();
        });
        $popover.find('.bookly-popover-close').on('click', function () {
            $popover.popover('hide');
        });
    }).on('shown.bs.popover', function () {
        var $button = $(this);
        $button.next('.popover').find($name_input).focus();
    }).on('hidden.bs.popover', function (e) {
        //clear input
        $name_input.val('');
        $(e.target).data("bs.popover").inState.click = false;
    });
});