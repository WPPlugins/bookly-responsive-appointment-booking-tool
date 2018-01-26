jQuery(function($) {
    var $no_result = $('#bookly-services-wrapper .no-result');
    // Remember user choice in the modal dialog.
    var update_staff_choice = null,
        $new_category_popover = $('#bookly-new-category'),
        $new_category_form = $('#new-category-form'),
        $new_category_name = $('#bookly-category-name');

    $new_category_popover.popover({
        html: true,
        placement: 'bottom',
        template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        content: $new_category_form.show().detach(),
        trigger: 'manual'
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
    $new_category_form.on('submit', function() {
        var data = $(this).serialize();

        $.post(ajaxurl, data, function(response) {
            $('#bookly-category-item-list').append(response.data.html);
            var $new_category = $('.bookly-category-item:last');
            // add created category to services
            $('select[name="category_id"]').append('<option value="' + $new_category.data('category-id') + '">' + $new_category.find('input').val() + '</option>');
            $new_category_popover.popover('hide');
        });
        return false;
    });

    // Cancel button.
    $new_category_form.on('click', 'button[type="button"]', function (e) {
        $new_category_popover.popover('hide');
    });

    // Save category.
    function saveCategory() {
        var $this = $(this),
            $item = $this.closest('.bookly-category-item'),
            field = $this.attr('name'),
            value = $this.val(),
            id    = $item.data('category-id'),
            data  = { action: 'bookly_update_category', id: id, csrf_token : BooklyL10n.csrf_token };
        data[field] = value;
        $.post(ajaxurl, data, function(response) {
            // Hide input field.
            $item.find('input').hide();
            $item.find('.displayed-value').show();
            // Show modified category name.
            $item.find('.displayed-value').text(value);
            // update edited category's name for services
            $('select[name="category_id"] option[value="' + id + '"]').text(value);
        });
    }

    // Categories list delegated events.
    $('#bookly-categories-list')

        // On category item click.
        .on('click', '.bookly-category-item', function(e) {
            if ($(e.target).is('.bookly-js-handle')) return;
            $('#bookly-js-services-list').html('<div class="bookly-loading"></div>');
            var $clicked = $(this);

            $.get(ajaxurl, {action:'bookly_get_category_services', category_id: $clicked.data('category-id'), csrf_token : BooklyL10n.csrf_token}, function(response) {
                if ( response.success ) {
                    $('.bookly-category-item').not($clicked).removeClass('active');
                    $clicked.addClass('active');
                    $('.bookly-category-title').text($clicked.text());
                    refreshList(response.data);
                }
            });
        })

        // On edit category click.
        .on('click', '.bookly-js-edit', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            var $this = $(this).closest('.bookly-category-item');
            $this.find('.displayed-value').hide();
            $this.find('input').show().focus();
        })

        // On blur save changes.
        .on('blur', 'input', saveCategory)

        // On press Enter save changes.
        .on('keypress', 'input', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                saveCategory.apply(this);
            }
        })

        // On delete category click.
        .on('click', '.bookly-js-delete', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            // Ask user if he is sure.
            if (confirm(BooklyL10n.are_you_sure)) {
                var $item = $(this).closest('.bookly-category-item');
                var data = { action: 'bookly_delete_category', id: $item.data('category-id'), csrf_token : BooklyL10n.csrf_token };
                $.post(ajaxurl, data, function(response) {
                    // Remove category item from Services
                    $('select[name="category_id"] option[value="' + $item.data('category-id') + '"]').remove();
                    // Remove category item from DOM.
                    $item.remove();
                    if ($item.is('.active')) {
                        $('.bookly-js-all-services').click();
                    }
                });
            }
        })

        .on('click', 'input', function(e) {
            e.stopPropagation();
        });

    // Services list delegated events.
    $('#bookly-services-wrapper')
        // On click on 'Add Service' button.
        .on('click', '.add-service', function(e) {
            e.preventDefault();
            var ladda = rangeTools.ladda(this);
            var selected_category_id = $('#bookly-categories-list .active').data('category-id'),
                data = { action: 'bookly_add_service', csrf_token : BooklyL10n.csrf_token };
            if (selected_category_id) {
                data['category_id'] = selected_category_id;
            }
            $.post(ajaxurl, data, function(response) {
                refreshList(response.data.html, response.data.service_id);
                ladda.stop();
            });
        })
        // On click on 'Delete' button.
        .on('click', '#bookly-delete', function(e) {
            if (confirm(BooklyL10n.are_you_sure)) {
                var ladda = rangeTools.ladda(this);

                var $for_delete = $('.service-checker:checked'),
                    data = { action: 'bookly_remove_services', csrf_token : BooklyL10n.csrf_token },
                    services = [],
                    $panels = [];

                $for_delete.each(function(){
                    var panel = $(this).parents('.bookly-js-collapse');
                    $panels.push(panel);
                    services.push(this.value);
                    if (panel.find('.bookly-js-service-type input[name="type"]:checked').val() == 'simple') {
                        $('#services_list .panel.bookly-js-collapse').each(function () {
                            if ($(this).find('.bookly-js-service-type input[name="type"]:checked').val() == 'package' && $(this).find('.bookly-js-package-sub-service option:selected').val() == panel.data('service-id')) {
                                $panels.push($(this));
                            }
                        });
                    }
                });
                data['service_ids[]'] = services;
                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        ladda.stop();
                        $.each($panels.reverse(), function (index) {
                            $(this).delay(500 * index).fadeOut(200, function () {
                                $(this).remove();
                            });
                        });
                        $(document.body).trigger( 'service.deleted', [ services ] );
                    }
                });
            }
        })

        .on('change', 'input.bookly-check-all-entities, input.bookly-js-check-entity', function () {
            var $container = $(this).parents('.form-group');
            if ($(this).hasClass('bookly-check-all-entities')) {
                $container.find('.bookly-js-check-entity').prop('checked', $(this).prop('checked'));
            } else {
                $container.find('.bookly-check-all-entities').prop('checked', $container.find('.bookly-js-check-entity:not(:checked)').length == 0);
            }
            var $form = $(this).closest('.panel.bookly-js-collapse'),
                service_id = $form.data('service-id'),
                service_type = $form.find('.bookly-js-service-type input[name="type"]:checked').val(),
                staff_index = $(this).closest('li').index() + 1;
            if (service_type == 'simple' && !$(this).is(':checked')) {
                $('#services_list .panel.bookly-js-collapse').each(function () {
                    if ($(this).find('.bookly-js-service-type input[name="type"]:checked').val() == 'package' && $(this).find('.bookly-js-package-sub-service option:selected').val() == service_id) {
                        $(this).find('.bookly-entity-selector li:nth-child(' + staff_index + ') input').prop('checked', false).trigger('change');
                    }
                });
            } else if (service_type == 'package' && $(this).is(':checked')) {
                var sub_service_id = $form.find('.bookly-js-package-sub-service option:selected').val();
                $('#services_list .panel.bookly-js-collapse').each(function () {
                    if ($(this).find('.bookly-js-service-type input[name="type"]:checked').val() == 'simple' && $(this).data('service-id') == sub_service_id) {
                        $(this).find('.bookly-entity-selector li:nth-child(' + staff_index + ') input').prop('checked', true).trigger('change');
                    }
                });
            }
            updateSelectorButton($container);
        });

    // Modal window events.
    var $modal = $('#bookly-update-service-settings');
    $modal
        .on('click', '.bookly-yes', function() {
            $modal.modal('hide');
            if ( $('#bookly-remember-my-choice').prop('checked') ) {
                update_staff_choice = true;
            }
            submitServiceFrom($modal.data('input'),true);
        })
        .on('click', '.bookly-no', function() {
            if ( $('#bookly-remember-my-choice').prop('checked') ) {
                update_staff_choice = false;
            }
            submitServiceFrom($modal.data('input'),false);
        });

    function refreshList(response,service_id) {
        var $list = $('#bookly-js-services-list');
        $list.html(response);
        if (response.indexOf('panel') >= 0) {
            $no_result.hide();
            onCollapseInitServiceForm();
            $list.booklyHelp();
        } else {
            $no_result.show();
        }
        if (service_id) {
            $('#service_' + service_id).collapse('show');
            $('#service_' + service_id).find('input[name=title]').focus();
        }
        makeSortable();
    }

    function initColorPicker($jquery_collection) {
        $jquery_collection.each(function(){
            $(this).data('last-color', $(this).val());
        });
        $jquery_collection.wpColorPicker({
            width: 200
        });
    }

    $('#services_list').on('change', '[name=capacity_min],[name=capacity_max]', function(){
        if ($(this).val() > 1) {
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).val('1').prop('readonly',true);
        }
    }).on('change', '[name=padding_left],[name=padding_right]', function(){
        if ($(this).val() > 0) {
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).val('0').prop('readonly', true);
            $(this).find('option:gt(0)').prop('disabled', true);
        }
    });

    function submitServiceFrom($form, update_staff) {
        $form.find('input[name=update_staff]').val(update_staff ? 1 : 0);
        $form.find('input[name=package_service_changed]').val($form.find('[name=package_service]').data('last_value') != $form.find('[name=package_service]').val() ? 1 : 0);
        var ladda = rangeTools.ladda($form.find('button.ajax-service-send[type=submit]').get(0)),
            data = $form.serializeArray();
        $(document.body).trigger( 'service.submitForm', [ $form, data ] );
        $.post(ajaxurl, data, function (response) {
            if (response.success) {
                var $panel = $form.parents('.bookly-js-collapse'),
                    $price = $form.find('[name=price]'),
                    $capacity_min = $form.find('[name=capacity_min]'),
                    $capacity_max = $form.find('[name=capacity_max]'),
                    $package_service = $form.find('[name=package_service]');
                $panel.find('.bookly-js-service-color span:nth-child(1)').css('background-color', response.data.colors[0] == '-1' ? 'grey' : response.data.colors[0]);
                $panel.find('.bookly-js-service-color span:nth-child(2)').css('background-color', response.data.colors[1] == '-1' ? 'grey' : response.data.colors[1]);
                $panel.find('.bookly-js-service-color span:nth-child(3)').css('background-color', response.data.colors[2] == '-1' ? 'grey' : response.data.colors[2]);
                $panel.find('.bookly-js-service-title').html(response.data.title);
                $panel.find('.bookly-js-service-duration').html(response.data.nice_duration);
                $panel.find('.bookly-js-service-price').html(response.data.price);
                $price.data('last_value', $price.val());
                $capacity_min.data('last_value', $capacity_min.val());
                $capacity_max.data('last_value', $capacity_max.val());
                $package_service.data('last_value', $package_service.val());
                booklyAlert(response.data.alert);
                if (response.data.new_extras_list) {
                    ExtrasL10n.list = response.data.new_extras_list
                }
                $.each(response.data.new_extras_ids, function (front_id, real_id) {
                    var $li = $('li.extra.new[data-extra-id="' + front_id + '"]', $form);
                    $('[name^="extras"]', $li).each(function () {
                        $(this).attr('name', $(this).attr('name').replace('[' + front_id + ']', '[' + real_id + ']'));
                    });
                    $('[id*="_extras_"]', $li).each(function () {
                        $(this).attr('id', $(this).attr('id').replace(front_id, real_id));
                    });
                    $('label[for*="_extras_"]', $li).each(function () {
                        $(this).attr('for', $(this).attr('for').replace(front_id, real_id));
                    });
                    $li.data('extra-id', real_id).removeClass('new');
                    $li.append('<input type="hidden" value="' + real_id + '" name="extras[' + real_id + '][id]">');
                });
            } else {
                booklyAlert({error: [response.data.message]});
            }
        }, 'json').always(function() {
            ladda.stop();
        });
    }

    function updateSelectorButton($container) {
        var entity_checked = $container.find('.bookly-js-check-entity:checked').length,
            $check_all = $container.find('.bookly-check-all-entities');
        if (entity_checked == 0) {
            $container.find('.bookly-entity-counter').text($check_all.data('nothing'));
        } else if (entity_checked == 1) {
            $container.find('.bookly-entity-counter').text($container.find('.bookly-js-check-entity:checked').data('title'));
        } else if (entity_checked == $container.find('.bookly-js-check-entity').length) {
            $container.find('.bookly-entity-counter').text($check_all.data('title'));
            $check_all.prop('checked', true);
        } else {
            $container.find('.bookly-entity-counter').text(entity_checked + '/' + $container.find('.bookly-js-check-entity').length);
        }
    }

    function checkCapacityError($panel) {
        if (parseInt($panel.find('[name="capacity_min"]').val()) > parseInt($panel.find('[name="capacity_max"]').val())) {
            $panel.find('form .bookly-js-services-error').html(BooklyL10n.capacity_error);
            $panel.find('[name="capacity_min"]').closest('.form-group').addClass('has-error');
            $panel.find('form .ajax-service-send').prop('disabled', true);
        } else {
            $panel.find('form .bookly-js-services-error').html('');
            $panel.find('[name="capacity_min"]').closest('.form-group').removeClass('has-error');
            $panel.find('form .ajax-service-send').prop('disabled', false);
        }
    }

    var $category = $('#bookly-category-item-list');
    $category.sortable({
        axis   : 'y',
        handle : '.bookly-js-handle',
        update : function( event, ui ) {
            var data = [];
            $category.children('li').each(function() {
                var $this = $(this);
                var position = $this.data('category-id');
                data.push(position);
            });
            $.ajax({
                type : 'POST',
                url  : ajaxurl,
                data : { action: 'bookly_update_category_position', position: data, csrf_token : BooklyL10n.csrf_token }
            });
        }
    });

    function makeSortable() {
        if ($('.bookly-js-all-services').hasClass('active')) {
            var $services = $('#services_list'),
                fixHelper = function(e, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                };
            $services.sortable({
                helper : fixHelper,
                axis   : 'y',
                handle : '.bookly-js-handle',
                update : function( event, ui ) {
                    var data = [];
                    $services.children('div').each(function() {
                        data.push($(this).data('service-id'));
                    });
                    $.ajax({
                        type : 'POST',
                        url  : ajaxurl,
                        data : { action: 'bookly_update_services_position', position: data, csrf_token : BooklyL10n.csrf_token }
                    });
                }
            });
        } else {
            $('#services_list .bookly-js-handle').hide();
        }
    }

    function onCollapseInitServiceForm() {
        $('.panel-collapse').on('show.bs.collapse.bookly', function () {
            var $panel = $(this),
                $staff_preference = $panel.find('[name=staff_preference]'),
                $staff_list = $panel.find('.bookly-staff-list'),
                $staff_box  = $panel.find('.bookly-preference-box');

            $staff_preference.on('change', function () {
                /** @see Service::PREFERRED_ORDER */
                if ($(this).val() == 'order' && $staff_list.html() == '') {
                    var $staff_ids = $staff_preference.data('default'),
                        $draggable = $('<div class="bookly-flex-cell"><i class="bookly-js-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move" title="' + BooklyL10n.reorder + '"></i><input type="hidden" name="positions[]"></div>');

                    $staff_ids.forEach(function (staff_id) {
                        $staff_list.append($draggable.clone().find('input').val(staff_id).end().append(BooklyL10n.staff[staff_id]));
                    });
                    Object.keys(BooklyL10n.staff).forEach(function (staff_id) {
                        staff_id = parseInt(staff_id);
                        if ($staff_ids.indexOf(staff_id) == -1) {
                            $staff_list.append($draggable.clone().find('input').val(staff_id).end().append(BooklyL10n.staff[staff_id]));
                        }
                    });
                }
                $staff_box.toggle($(this).val() == 'order');
            }).trigger('change');

            initColorPicker($panel.find('.bookly-js-color-picker'));

            $('[data-toggle="popover"]').popover({
                html: true,
                placement: 'top',
                trigger: 'hover',
                template: '<div class="popover bookly-font-xs" style="width: 220px" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
            });

            $.each($('.bookly-js-entity-selector-container',$(this)), function () {
                updateSelectorButton($(this));
            });

            $panel
                .find('[name=duration]').on('change', function () {
                    $panel.find('[name=start_time_info]').closest('.form-group').toggle($(this).val() >= 86400);
                }).trigger('change');

            $panel
                .find('.bookly-js-capacity').on('keyup change', function () {
                    checkCapacityError($(this).parents('.bookly-js-collapse'));
                });

            $panel
                .find('.ajax-service-send').on('click', function (e) {
                    e.preventDefault();
                    var $form = $(this).parents('form'),
                        show_modal = false;
                    if(update_staff_choice === null) {
                        $('.bookly-question', $form).each(function () {
                            if ($(this).data('last_value') != this.value) {
                                show_modal = true;
                            }
                        });
                    }
                    if (show_modal) {
                        $modal.data('input', $form).modal('show');
                    } else {
                        submitServiceFrom($form, update_staff_choice);
                    }
                });

            $panel
                .find('.js-reset').on('click', function () {
                    $(this).parents('form').trigger('reset');
                    var $color = $(this).parents('form').find('.wp-color-picker'),
                        $panel = $(this).parents('.bookly-js-collapse');
                    $staff_list.html('');
                    $staff_preference.trigger('change');
                    $color.val($color.data('last-color')).trigger('change');
                    $panel.find('.parent-range-start').trigger('change');
                    $panel.find('input[name=type]:checked').trigger('change');
                $.each($('.bookly-js-entity-selector-container',$panel), function () {
                        updateSelectorButton($(this));
                    });
                    checkCapacityError($panel);
                });

            $panel
                .find('.bookly-question').each(function () {
                    $(this).data('last_value', this.value);
                });

            $staff_list.sortable({
                axis   : 'y',
                handle : '.bookly-js-handle',
                update : function() {
                    var positions = [];
                    $('[name="positions[]"]', $(this)).each(function () {
                        positions.push(this.value);
                    });

                    $.ajax({
                        type : 'POST',
                        url  : ajaxurl,
                        data : {action: 'bookly_update_service_staff_preference_orders', service_id: $(this).data('service_id'), positions: positions, csrf_token: BooklyL10n.csrf_token}
                    });
                }
            });
            $panel.unbind('show.bs.collapse.bookly');
            if ($('input[name=type]', $panel).length > 1) {
                $('.bookly-js-service-type', $panel).show();
                $('input[name=type]', $panel).on( 'change', function(){
                    $panel.closest('.panel').find('.bookly-js-service').hide();
                    $panel.closest('.panel').find('.bookly-js-service-' + this.value).show();
                });
                $('input[name=type]:checked', $panel).trigger('change');
            }
            $(document.body).trigger( 'service.initForm', [ $panel, $panel.closest('.panel').data('service-id') ] );
        });
    }

    makeSortable();
    onCollapseInitServiceForm();
});