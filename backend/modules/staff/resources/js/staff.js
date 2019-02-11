jQuery(function ($) {
    var $categories_list      = $('#bookly-staff-categories'),
        $new_form             = $('#bookly-new-staff-form'),
        $wp_user_select       = $('#bookly-new-staff-wpuser'),
        $name_input           = $('.bookly-js-new-staff-fullname'),
        $staff_count          = $('#bookly-staff-count'),
        $edit_form            = $('#bookly-container-edit-staff'),
        $new_staff_member     = $('.bookly-js-new-staff-member'),
        $new_category_popover = $('#bookly-new-staff-category'),
        $new_category_form    = $('#bookly-new-category-form'),
        $new_category_name    = $('#bookly-category-name'),
        $delete_cascade_modal = $('.bookly-js-delete-cascade-confirm');

    makeCategoriesSortable();
    makeStaffSortable();

    /**
     * Load staff profile on click on staff in the list.
     */
    $categories_list
        .on('click', 'li', function () {
            var $this = $(this);
            // Mark selected element as active
            $categories_list.find('.active').removeClass('active');
            $this.addClass('active');

            var staff_id = $this.data('staff-id'),
                active_tab_id = $('.nav .active a').attr('id');
            $edit_form.html('<div class="bookly-loading"></div>');
            $.get(ajaxurl, {action: 'bookly_edit_staff', id: staff_id, csrf_token: BooklyL10n.csrf_token}, function (response) {
                $edit_form.html(response.data.html.edit);
                booklyAlert(response.data.alert);
                var $details_container = $('#bookly-details-container', $edit_form),
                    $services_container = $('#bookly-services-container', $edit_form),
                    $schedule_container = $('#bookly-schedule-container', $edit_form),
                    $holidays_container = $('#bookly-holidays-container', $edit_form)
                ;
                $details_container.html(response.data.html.details);

                new BooklyStaffDetails($details_container, {
                    get_details: {},
                    intlTelInput: BooklyL10n.intlTelInput,
                    l10n: BooklyL10n,
                    renderWpUsers: function (wp_users) {
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

                /**
                 * Delete staff member.
                 */
                $('#bookly-staff-delete', $edit_form).on('click', function (e) {
                    e.preventDefault();

                    var ladda = Ladda.create(this),
                        data = {
                            action: 'bookly_delete_staff',
                            id: staff_id,
                            csrf_token: BooklyL10n.csrf_token
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
                                $staff_count.text($categories_list.find('.bookly-js-staff-members').children().length);
                                $categories_list.find('li:first').click();
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
                            action: 'bookly_get_staff_services',
                            staff_id: staff_id,
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
                        staff_id: staff_id,
                        csrf_token: BooklyL10n.csrf_token,
                        l10n: SpecialDaysL10n
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
                        staff_id: staff_id,
                        csrf_token: BooklyL10n.csrf_token,
                        l10n: BooklyL10n
                    });

                    $holidays_container.show();
                });

                $('#' + active_tab_id).click();
            });
        })
        // Edit category.
        .on('click', '.bookly-js-edit-category', function (e) {
            e.preventDefault();
            var $category = $(this).closest('.panel[data-category]'),
                $name = $category.find('.bookly-js-category-name a'),
                $input = $category.find('.bookly-js-category-name input');
            if ($name.is(':visible')) {
                $name.hide();
                $input.show().focus();
                $input.off().on('blur', function () {
                    var category_id   = $category.data('category'),
                        category_name = $input.val(),
                        data          = {action: 'bookly_pro_rename_staff_category', id: category_id, name: category_name, csrf_token: BooklyL10n.csrf_token};
                    $.post(ajaxurl, data, function (response) {
                        // update edited category's name for staff
                        $('select[name="category_id"] option[value="' + category_id + '"]').text(category_name);
                        $category.find('.bookly-js-category-name input').hide();
                        $category.find('.bookly-js-category-name a').text(category_name).show();
                    });
                });
            }
        })
        // Delete category.
        .on('click', '.bookly-js-delete-category', function (e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            // Ask user if he is sure.
            if (confirm(BooklyL10n.are_you_sure)) {
                var $item = $(this).closest('.panel[data-category]');
                var data = {action: 'bookly_pro_delete_staff_category', id: $item.data('category'), csrf_token: BooklyL10n.csrf_token};
                $.post(ajaxurl, data, function (response) {
                    $item.find('ul.bookly-js-staff-members li').each(function () {
                        $(this).appendTo($('.panel[data-category=""] .bookly-js-staff-members', $categories_list));
                    });
                    var $category_option = $('select[name="category_id"] option[value="' + $item.data('category') + '"]');
                    if ($category_option.length) {
                        $category_option.remove();
                        $('select[name="category_id"]').val('');
                    }
                    $item.remove();
                    $categories_list.sortable('refresh');
                });
            }
        })
        // Expand/Collapse categories
        .on('click', '[data-toggle="collapse"]', function (e) {
            var $category = $(this).closest('.panel[data-category]'),
                data = {
                    action: 'bookly_update_staff_categories_filter',
                    csrf_token: BooklyL10n.csrf_token,
                    category_id: $category.data('category'),
                    collapsed: $category.find('.panel-collapse').hasClass('in') ? 1 : 0
                };
            $.post(ajaxurl, data);
        }).find('li.active').click();

    if (BooklyL10n.active_staff_id == 0) {
        $categories_list.find('ul.bookly-js-staff-members li.bookly-nav-item:not(.bookly-js-archived):first').click();
    } else {
        $categories_list.find('ul.bookly-js-staff-members #bookly-staff-' + BooklyL10n.active_staff_id).click();
    }

    /**
     * Change staff avatar.
     */
    $edit_form.on('click', '.bookly-pretty-indicator', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var frame = wp.media({
            library : {type: 'image'},
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

    // Create new staff popover
    $new_staff_member.each(function () {
        addCreateStaffPopover($(this));
    });

    // Select wp user in create new staff popover
    $wp_user_select.on('change', function () {
        if (this.value) {
            $name_input.val($(this).find(':selected').text());
        }
    });

    // Save new staff on enter press
    $name_input.on('keypress', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            createNewStaff($(this).closest('.popover'));
        }
    });

    // Close new staff form on esc
    $new_form.on('keypress', function (e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 27) {
            $('#bookly-newstaff-member').popover('hide');
        }
    });

    // Disable selecting staff when click on sort handle
    $categories_list.on('click', '.bookly-js-handle', function (e) {
        e.stopPropagation();
    });

    // New category popover
    $new_category_popover.popover({
        html     : true,
        placement: 'bottom',
        template : '<div class="popover" style="width: calc(100% - 20px)" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        content  : $new_category_form.show().detach(),
        trigger  : 'manual'
    }).on('click', function () {
        $(this).popover('toggle');
    }).on('shown.bs.popover', function () {
        // focus input
        $new_category_name.focus();
    }).on('hidden.bs.popover', function () {
        //clear input
        $new_category_name.val('');
    });

    // Save new category.
    $new_category_form.on('submit', function (e) {
        e.preventDefault();
        createNewCategory();
    });

    // Cancel creating category button.
    $new_category_form.on('click', 'button[type="button"]', function (e) {
        $new_category_popover.popover('hide');
    });

    function createNewStaff(e) {
        var ladda    = Ladda.create($('.bookly-js-save-form').get(0)),
            data     = {
                action    : 'bookly_create_staff',
                wp_user_id: $wp_user_select.val(),
                full_name : $name_input.val(),
                csrf_token: BooklyL10n.csrf_token
            },
            category = e.data('category');
        if (category != '') data.category_id = category;
        ladda.start();
        if (validateForm($new_form)) {
            $.post(ajaxurl, data, function (response) {
                if (response.success) {
                    var category = response.data.category == null ? '' : response.data.category;
                    $categories_list.find('.panel[data-category="' + category + '"] ul.bookly-js-staff-members').append(
                        $('#bookly-new-staff-template').clone().show().html()
                            .replace(/{{id}}/g, response.data.id)
                            .replace(/{{name}}/g, response.data.name)
                    );

                    $staff_count.text($categories_list.find('[data-staff-id]').length);
                    $categories_list.find('.panel[data-category="' + category + '"] ul.bookly-js-staff-members [data-staff-id]:last').trigger('click');
                    ladda.stop();
                    if ($wp_user_select.val()) {
                        $wp_user_select.find('option:selected').remove();
                        $wp_user_select.val('');
                    }
                    $name_input.val('');
                    $('.bookly-js-new-staff-member').popover('hide');
                }
            });
        } else {
            ladda.stop();
        }
    }

    function createNewCategory() {
        var data = $new_category_form.serialize(),
            ladda = Ladda.create($new_category_form.find('button[type="submit"]')[0]);
        ladda.start();
        $.post(ajaxurl, data, function (response) {
            $('#bookly-staff-categories div.panel:last-child').before(
                $('#bookly-new-category-template').clone().show().html()
                    .replace(/{{id}}/g, response.data.id)
                    .replace(/{{name}}/g, response.data.name)
            );
            $categories_list.sortable('refresh');
            makeStaffSortable();
            addCreateStaffPopover($('#bookly-staff-categories [data-category="' + response.data.id + '"] .bookly-js-new-staff-member'));
            // add created category to staff
            $('select[name="category_id"]').append('<option value="' + response.data.id + '">' + response.data.name + '</option>');
            ladda.stop();
            $new_category_popover.popover('hide');
        });
    }

    function updateStaffPositions() {
        var data = {'categories': [], 'staff': []};
        $categories_list.find('.panel[data-category]').each(function () {
            if ($(this).data('category')) {
                data.categories.push($(this).data('category'));
            }
        });
        $categories_list.find('.bookly-js-staff-members').children('li').each(function () {
            var $this       = $(this),
                staff_id    = $this.data('staff-id'),
                category_id = $this.closest('.panel[data-category]').data('category');

            data.staff.push({'staff_id': staff_id, 'category_id': category_id});
        });
        $.ajax({
            type: 'POST',
            url : ajaxurl,
            data: {action: 'bookly_update_staff_position', data: data, csrf_token: BooklyL10n.csrf_token}
        });
    }

    function makeStaffSortable() {
        $(".bookly-js-staff-members").sortable({
            axis                : 'y',
            placeholder         : "ui-state-highlight",
            forceHelperSize     : true,
            forcePlaceholderSize: true,
            dropOnEmpty         : true,
            connectWith         : "ul.bookly-js-staff-members",
            stop                : updateStaffPositions
        }).disableSelection();
    }

    function makeCategoriesSortable() {
        $categories_list.sortable({
            axis  : 'y',
            handle: '.bookly-js-categories-handle',
            items : "div.panel:not(.bookly-js-unsortable)",
            stop  : updateStaffPositions
        }).disableSelection();
    }

    function addCreateStaffPopover(e) {
        // Create new staff popover
        e.popover({
            html     : true,
            placement: 'bottom',
            template : '<div class="popover bookly-js-new-staff-popover" style="width: calc(100% - 20px)" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content  : $new_form.show().detach(),
            trigger  : 'manual'
        }).on('click', function (e) {
            // Prevent navigating to '#'.
            e.preventDefault();
            $new_staff_member.each(function () {
                $(this).popover('hide');
            });
            if (BooklyL10n.pro_required == '1' && $staff_count.html() >= '1') {
                booklyAlert({error: [BooklyL10n.limitation]});
                return false;
            } else {
                var $button = $(this);
                $button.popover('toggle');
                var $popover = $button.next('.popover').off();
                $popover.data('category', $button.closest('.panel').data('category'));
                $popover.find('.bookly-js-new-staff-fullname').focus();
                $popover
                    .on('click', '.bookly-js-save-form', function () {
                        createNewStaff($popover);
                    })
                    .on('click', '.bookly-popover-close', function () {
                        $popover.popover('hide');
                    });
            }
        }).on('hidden.bs.popover', function (e) {
            //clear input
            $name_input.val('');
            $(e.target).data("bs.popover").inState.click = false;
        });
    }

    $staff_count.html($categories_list.find('ul.bookly-js-staff-members li.bookly-nav-item:not(.bookly-js-archived)').length);
});