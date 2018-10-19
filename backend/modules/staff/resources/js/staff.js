jQuery(function($) {
    var $staff_list       = $('#bookly-staff-list'),
        $new_form         = $('#bookly-new-staff'),
        $wp_user_select   = $('#bookly-new-staff-wpuser'),
        $name_input       = $('#bookly-new-staff-fullname'),
        $staff_count      = $('#bookly-staff-count'),
        $edit_form        = $('#bookly-container-edit-staff'),
        $delete_cascade_modal = $('.bookly-js-delete-cascade-confirm');

    function saveNewForm() {
        var ladda = Ladda.create($('.bookly-js-save-form').get(0)),
        data = {
            action     : 'bookly_create_staff',
            wp_user_id : $wp_user_select.val(),
            full_name  : $name_input.val(),
            csrf_token : BooklyL10n.csrf_token
        };
        ladda.start();
        if (validateForm($new_form)) {
            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    $staff_list.append(response.data.html);
                    $staff_count.text($staff_list.find('[data-staff-id]').length);
                    $staff_list.find('[data-staff-id]:last').trigger('click');
                    ladda.stop();

                    $('#bookly-newstaff-member').popover('hide');
                    if ($wp_user_select.val()) {
                        $wp_user_select.find('option:selected').remove();
                        $wp_user_select.val('');
                    }
                    $name_input.val('');
                }
            });
        } else {
            ladda.stop();
        }

    }

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
            $('#bookly-newstaff-member').popover('hide');
        }
    });

    $staff_list.on('click', '.bookly-js-handle', function (e) {
        e.stopPropagation();
    });

    $edit_form
        .on('click', '.bookly-pretty-indicator', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var frame = wp.media({
                library: {type: 'image'},
                multiple: false
            });
            frame.on('select', function () {
                var selection = frame.state().get('selection').toJSON(),
                    img_src;
                if (selection.length) {
                    if (selection[0].sizes['thumbnail'] !== undefined) {
                        img_src = selection[0].sizes['thumbnail'].url;
                    } else {
                        img_src = selection[0].url;
                    }
                    $edit_form.find('[name=attachment_id]').val(selection[0].id).trigger('change');
                    $('#bookly-js-staff-avatar').find('.bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                    $('.bookly-thumb-delete').show();
                    $(this).hide();
                }
            });

            frame.open();
        });

    /**
     * Load staff profile on click on staff in the list.
     */
    $staff_list.on('click', 'li', function() {
        var $this = $(this);
        // Mark selected element as active
        $staff_list.find('.active').removeClass('active');
        $this.addClass('active');

        var staff_id = $this.data('staff-id'),
            active_tab_id = $('.nav .active a').attr('id');
        $edit_form.html('<div class="bookly-loading"></div>');
        $.get(ajaxurl, {action: 'bookly_edit_staff', id: staff_id, csrf_token: BooklyL10n.csrf_token}, function (response) {
            $edit_form.html(response.data.html.edit);
            booklyAlert(response.data.alert);
            var $details_container   = $('#bookly-details-container', $edit_form),
                $services_container  = $('#bookly-services-container', $edit_form),
                $schedule_container  = $('#bookly-schedule-container', $edit_form),
                $holidays_container  = $('#bookly-holidays-container', $edit_form)
            ;
            $details_container.html(response.data.html.details);

            new BooklyStaffDetails($details_container, {
                get_details   : {},
                intlTelInput  : BooklyL10n.intlTelInput,
                l10n          : BooklyL10n,
                renderWpUsers : function (wp_users) {
                    $wp_user_select.children(':not(:first)').remove();
                    $.each(wp_users, function (index, wp_user) {
                        var $option = $('<option>')
                            .data('email', wp_user.user_email)
                            .val(wp_user.ID)
                            .text(wp_user.display_name);
                        $wp_user_select.append($option);
                    });
                }
            });

            // Delete staff member.
            $('#bookly-staff-delete', $edit_form).on('click', function (e) {
                e.preventDefault();

                var ladda = Ladda.create(this),
                    data = {
                        action: 'bookly_delete_staff',
                        id: staff_id, csrf_token:
                        BooklyL10n.csrf_token
                    };
                ladda.start();

                var delete_staff = function (ajaxurl, data) {
                    $.post(ajaxurl, data, function (response) {
                        ladda.stop();
                        if (!response.success) {
                            switch (response.data.action) {
                                case 'show_modal':
                                    $delete_cascade_modal
                                        .modal('show');
                                    $('.bookly-js-delete', $delete_cascade_modal).off().on('click', function () {
                                        $edit_form.html('<div class="bookly-loading"></div>');
                                        ladda = Ladda.create(this);
                                        ladda.start();
                                        delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
                                        $delete_cascade_modal.modal('hide');
                                        ladda.stop();
                                    });
                                    $('.bookly-js-edit', $delete_cascade_modal).off().on('click', function () {
                                        ladda = Ladda.create(this);
                                        ladda.start();
                                        window.location.href = response.data.filter_url;
                                    });
                                    break;
                                case 'confirm':
                                    if (confirm(BooklyL10n.are_you_sure)) {
                                        $edit_form.html('<div class="bookly-loading"></div>');
                                        delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
                                    }
                                    break;
                            }
                        } else {
                            $edit_form.html('');
                            $wp_user_select.children(':not(:first)').remove();
                            $.each(response.data.wp_users, function (index, wp_user) {
                                var $option = $('<option>')
                                    .data('email', wp_user.user_email)
                                    .val(wp_user.ID)
                                    .text(wp_user.display_name);
                                $wp_user_select.append($option);
                            });
                            $('#bookly-staff-' + staff_id).remove();
                            $staff_count.text($staff_list.children().length);
                            $staff_list.children(':first').click();
                        }
                    });
                };

                delete_staff(ajaxurl, data);
            });

            // Delete staff avatar
            $('.bookly-thumb-delete', $edit_form).on('click', function () {
                var $thumb = $(this).parents('.bookly-js-image');
                $.post(ajaxurl, {action: 'bookly_delete_staff_avatar', id: staff_id, csrf_token: BooklyL10n.csrf_token}, function (response) {
                    if (response.success) {
                        $thumb.attr('style', '');
                        $edit_form.find('[name=attachment_id]').val('').trigger('change');
                    }
                });
            });

            // Open details tab
            $('#bookly-details-tab', $edit_form).on('click', function () {
                $('.tab-pane > div').hide();
                $details_container.show();
            });

            // Open services tab
            $('#bookly-services-tab', $edit_form).on('click', function () {
                $('.tab-pane > div').hide();

                new BooklyStaffServices($services_container, {
                    get_staff_services: {
                        action    : 'bookly_get_staff_services',
                        staff_id  : staff_id,
                        csrf_token: BooklyL10n.csrf_token
                    },
                    l10n: BooklyL10n,
                    refresh: BooklyL10n.locations_custom == 1
                });

                $services_container.show();
            });

            // Open special days tab
            $('#bookly-special-days-tab', $edit_form).on('click', function () {
                new BooklyStaffSpecialDays($('.bookly-js-special-days-container'), {
                    staff_id  : staff_id,
                    csrf_token: BooklyL10n.csrf_token,
                    l10n      : SpecialDaysL10n
                });
            });

            // Open schedule tab
            $('#bookly-schedule-tab', $edit_form).on('click', function () {
                $('.tab-pane > div').hide();

                new BooklyStaffSchedule($schedule_container, {
                    get_staff_schedule: {
                        action: 'bookly_get_staff_schedule',
                        staff_id: staff_id,
                        csrf_token: BooklyL10n.csrf_token
                    },
                    l10n: BooklyL10n
                });

                $schedule_container.show();
            });

            // Open holiday tab
            $('#bookly-holidays-tab').on('click', function () {
                $('.tab-pane > div').hide();

                new BooklyStaffDaysOff($holidays_container, {
                    staff_id  : staff_id,
                    csrf_token: BooklyL10n.csrf_token,
                    l10n      : BooklyL10n
                });

                $holidays_container.show();
            });

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
                data : {action: 'bookly_update_staff_position', position: data, csrf_token: BooklyL10n.csrf_token}
            });
        }
    });

    $('#bookly-newstaff-member').popover({
        html: true,
        placement: 'bottom',
        template: '<div class="popover" style="width: calc(100% - 20px)" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        content: $new_form.show().detach(),
        trigger: 'manual'
    }).on('click', function () {
        if (BooklyL10n.pro_required == '1' && $staff_count.html() >= '1') {
            booklyAlert({error: [BooklyL10n.limitation]});
            return false;
        } else {
            var $button = $(this);
            $button.popover('toggle');
            var $popover = $button.next('.popover').off();
            $popover
                .on('click', '.bookly-js-save-form', function () {
                    saveNewForm();
                })
                .on('click', '.bookly-popover-close', function () {
                    $popover.popover('hide');
                });
        }
    }).on('shown.bs.popover', function () {
        var $button = $(this);
        $button.next('.popover').find($name_input).focus();
    }).on('hidden.bs.popover', function (e) {
        //clear input
        $name_input.val('');
        $(e.target).data("bs.popover").inState.click = false;
    });
});