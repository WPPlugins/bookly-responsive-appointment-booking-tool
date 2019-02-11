jQuery(function($) {
    // Remember user choice in the modal dialog.
    var updateStaffChoice     = null,
        $no_result            = $('#bookly-services-wrapper .no-result'),
        $new_category_popover = $('#bookly-new-category'),
        $new_category_form    = $('#new-category-form'),
        $new_category_name    = $('#bookly-category-name'),
        $update_service_modal = $('#bookly-update-service-settings'),
        $delete_cascade_modal = $('.bookly-js-delete-cascade-confirm')
    ;

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
                if(response.success) {
                    refreshList(response.data.html, response.data.service_id);
                } else {
                    booklyAlert({error: [response.data.message]});
                }
                ladda.stop();
            });
        })
        // On click on 'Delete' button.
        .on('click', '#bookly-delete', function(e) {
            e.preventDefault();
            var data = {
                    action: 'bookly_remove_services',
                    csrf_token: BooklyL10n.csrf_token
                },
                services = [],
                $panels = [],
                $for_delete = $('.service-checker:checked'),
                button = this;

            var delete_services = function (ajaxurl, data) {
                var ladda = rangeTools.ladda(button);
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

                $.post(ajaxurl, data, function (response) {
                    if (!response.success) {
                        switch (response.data.action) {
                            case 'show_modal':
                                $delete_cascade_modal
                                    .modal('show');
                                $('.bookly-js-delete', $delete_cascade_modal).off().on('click', function () {
                                    delete_services(ajaxurl, $.extend(data, {force_delete: true}));
                                    $delete_cascade_modal.modal('hide');
                                });
                                $('.bookly-js-edit', $delete_cascade_modal).off().on('click', function () {
                                    rangeTools.ladda(this);
                                    window.location.href = response.data.filter_url;
                                });
                                break;
                            case 'confirm':
                                if (confirm(BooklyL10n.are_you_sure)) {
                                    delete_services(ajaxurl, $.extend(data, {force_delete: true}));
                                }
                                break;
                        }
                    } else {
                        $.each($panels.reverse(), function (index) {
                            $(this).delay(500 * index).fadeOut(200, function () {
                                $(this).remove();
                            });
                        });
                        $(document.body).trigger( 'service.deleted', [ services ] );
                    }
                    ladda.stop();
                });
            };

            delete_services(ajaxurl, data);
        })

        // On service expand.
        .on('show.bs.collapse', '.bookly-js-collapse', function () {
            if ($(this).data('bookly-inited')) {
                return;
            }
            var $panel            = $(this),
                $types            = $panel.find('.bookly-js-service-type input:radio'),  // simple, compound, package, etc.
                $colorPicker      = $panel.find('.bookly-js-color-picker'),
                $visibility       = $panel.find('.bookly-js-visibility'),
                $capacity         = $panel.find('.bookly-js-capacity'),  // min and max
                $duration         = $panel.find('.bookly-js-duration'),
                $unitsBlock       = $panel.find('.bookly-js-units-block'),
                $unitDuration     = $panel.find('.bookly-js-unit-duration'),
                $providers        = $panel.find('.bookly-js-providers'),
                $staffPreference  = $panel.find('[name=staff_preference]'),
                $limitPeriod      = $panel.find('[name=limit_period]'),
                $prefStaffOrder   = $panel.find('.bookly-js-preferred-staff-order'),
                $prefStaffList    = $panel.find('.bookly-js-preferred-staff-list'),
                $prefPeriod       = $panel.find('.bookly-js-preferred-period'),
                $simpleDropdowns  = $panel.find('.bookly-js-simple-dropdown'),
                $repeat           = $panel.find('[name="recurrence_enabled"]'),
                $btnSave          = $panel.find('.ajax-service-send'),
                $btnReset         = $panel.find('.js-reset')
            ;
            // Color picker.
            initColorPicker($colorPicker);
            // Visibility.
            $visibility.on('change', function () {
                $panel.find('.bookly-js-groups-list').toggle(this.value === 'group');
            });
            $limitPeriod.on('change', function () {
                $('[name=appointments_limit]', $panel).toggle(this.value !== 'off');
            }).trigger('change');
            // Capacity (min and max).
            $capacity.on('keyup change', function () {
                checkCapacityError($panel);
            });
            // Duration (and unit duration).
            $duration.on('change', function () {
                if (this.value === 'custom') {
                    $panel.find('.bookly-js-price-label').hide();
                    $panel.find('.bookly-js-unit-price-label').show();
                    $unitsBlock.show();
                } else {
                    $panel.find('.bookly-js-price-label').show();
                    $panel.find('.bookly-js-unit-price-label').hide();
                    $unitDuration.val(this.value);
                    $unitsBlock.hide();
                }
            });
            $duration.add($unitDuration).on('change', function () {
                $panel.find('[name=start_time_info]').closest('.form-group').toggle(this.value >= 86400);
            }).trigger('change');
            // Providers.
            $providers.booklyDropdown({
                onChange: function (values, selected, all) {
                    var serviceId   = $panel.data('service-id'),
                        serviceType = $types.filter(':checked').val()
                    ;
                    if (serviceType === 'simple' && !selected) {
                        $('#services_list .panel.bookly-js-collapse').each(function () {
                            var $anotherPanel = $(this);
                            if (
                                $anotherPanel.find('.bookly-js-service-type input:radio:checked').val() === 'package' &&
                                $anotherPanel.find('.bookly-js-package-sub-service option:selected').val() == serviceId
                            ) {
                                if (all) {
                                    $anotherPanel.find('.bookly-js-providers').booklyDropdown('deselectAll');
                                } else {
                                    $anotherPanel.find('.bookly-js-providers').booklyDropdown('deselect', values);
                                }
                            }
                        });
                    } else if (serviceType === 'package' && selected) {
                        var subServiceId = $panel.find('.bookly-js-package-sub-service option:selected').val();
                        $('#services_list .panel.bookly-js-collapse').each(function () {
                            var $anotherPanel = $(this);
                            if (
                                $anotherPanel.find('.bookly-js-service-type input:radio:checked').val() === 'simple' &&
                                $anotherPanel.data('service-id') == subServiceId
                            ) {
                                if (all) {
                                    $anotherPanel.find('.bookly-js-providers').booklyDropdown('selectAll');
                                } else {
                                    $anotherPanel.find('.bookly-js-providers').booklyDropdown('select', values);
                                }
                            }
                        });
                    }
                }
            });
            $repeat.on('change', function() {
                checkRepeatError($panel);
            });
            $panel.on('change', '.bookly-js-frequencies input[type="checkbox"]', function () {
                checkRepeatError($panel);
            });
            // Providers preference.
            $staffPreference.on('change', function () {
                /** @see Service::PREFERRED_ORDER */
                if (this.value === 'order' && $prefStaffList.html() === '') {
                    var $staffIds  = $staffPreference.data('default'),
                        $draggable = $('<div class="bookly-flex-cell"><i class="bookly-js-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move"></i><input type="hidden" name="positions[]" /></div>');
                    $draggable.find('i').attr('title', BooklyL10n.reorder);
                    $staffIds.forEach(function (staffId) {
                        $prefStaffList.append($draggable.clone().find('input').val(staffId).end().append(BooklyL10n.staff[staffId]));
                    });
                    Object.keys(BooklyL10n.staff).forEach(function (staffId) {
                        staffId = parseInt(staffId);
                        if ($staffIds.indexOf(staffId) === -1) {
                            $prefStaffList.append($draggable.clone().find('input').val(staffId).end().append(BooklyL10n.staff[staffId]));
                        }
                    });
                }
                $prefStaffOrder.toggle(this.value === 'order');
                $prefPeriod.toggle(this.value === 'least_occupied_for_period' || this.value === 'most_occupied_for_period');
            }).trigger('change');
            // Preferred providers order.
            $prefStaffList.sortable({
                axis   : 'y',
                handle : '.bookly-js-handle',
                update : function() {
                    var positions = [];
                    $prefStaffList.find('input').each(function () {
                        positions.push(this.value);
                    });
                    $.ajax({
                        type : 'POST',
                        url  : ajaxurl,
                        data : {
                            action: 'bookly_pro_update_service_staff_preference_orders',
                            service_id: $panel.data('service-id'),
                            positions: positions,
                            csrf_token: BooklyL10n.csrf_token
                        }
                    });
                }
            });
            // Save button.
            $btnSave.on('click', function (e) {
                e.preventDefault();
                var showModal = false;
                if (updateStaffChoice === null) {
                    $panel.find('.bookly-js-question').each(function () {
                        if ($(this).data('last_value') !== this.value) {
                            showModal = true;
                        }
                    });
                }
                if (showModal) {
                    $update_service_modal.data('panel', $panel).modal('show');
                } else {
                    submitServiceFrom($panel, updateStaffChoice);
                }
            });
            // Reset button.
            $btnReset.on('click', function () {
                var $this  = $(this),
                    $form  = $this.closest('form');

                $form.trigger('reset');
                $colorPicker.val($colorPicker.data('last-color')).trigger('change');
                $visibility.trigger('change');
                $duration.trigger('change');
                checkCapacityError($panel);
                checkRepeatError($panel);
                $prefStaffList.html('');
                $staffPreference.trigger('change');
                $panel.find('.parent-range-start').trigger('change');
                $panel.find('input[name=type]:checked').trigger('change');

                setTimeout(function () {
                    $providers.booklyDropdown('reset');
                    $simpleDropdowns.booklyDropdown('reset');
                    $(document.body).trigger('service.resetForm', [$panel]);
                }, 0);
            });
            // Fields that are repeated at staff level.
            $panel.find('.bookly-js-question').each(function () {
                $(this).data('last_value', this.value);
            });
            // Service types.
            if ($types.size() > 1) {
                $panel.find('.bookly-js-service-type').show();
                $types.on( 'change', function () {
                    $panel.find('.bookly-js-service').hide();
                    $panel.find('.bookly-js-service-' + this.value).css('display', '');
                    // Toggle class for inline or vertical displaying color circles
                    $('.bookly-js-service-color', $panel).toggleClass('bookly-vertical-colors', this.value === 'collaborative');
                });
                $types.filter(':checked').trigger('change');
            }
            // Other drop-downs.
            $simpleDropdowns.booklyDropdown();

            $(document.body).trigger( 'service.initForm', [$panel] );

            $panel.data('bookly-inited', 1);
        });

    // Modal window events.
    $update_service_modal
        .on('click', '.bookly-yes', function() {
            $update_service_modal.modal('hide');
            if ( $('#bookly-remember-my-choice').prop('checked') ) {
                updateStaffChoice = true;
            }
            submitServiceFrom($update_service_modal.data('panel'), true);
        })
        .on('click', '.bookly-no', function() {
            if ( $('#bookly-remember-my-choice').prop('checked') ) {
                updateStaffChoice = false;
            }
            submitServiceFrom($update_service_modal.data('panel'), false);
        })
    ;

    function refreshList(response,service_id) {
        var $list = $('#bookly-js-services-list');
        $list.html(response);
        if (response.indexOf('panel') >= 0) {
            $no_result.hide();
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

    function submitServiceFrom($panel, update_staff) {
        $panel.find('input[name=update_staff]').val(update_staff ? 1 : 0);
        $panel.find('input[name=package_service_changed]').val($panel.find('[name=package_service]').data('last_value') != $panel.find('[name=package_service]').val() ? 1 : 0);
        var ladda = rangeTools.ladda($panel.find('button.ajax-service-send[type=submit]').get(0)),
            data = $panel.find('form').serializeArray();
        $(document.body).trigger( 'service.submitForm', [ $panel, data ] );
        $.post(ajaxurl, data, function (response) {
            if (response.success) {
                var $price = $panel.find('[name=price]'),
                    $capacity_min = $panel.find('[name=capacity_min]'),
                    $capacity_max = $panel.find('[name=capacity_max]'),
                    $package_service = $panel.find('[name=package_service]');
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
                    var $li = $('li.extra.new[data-extra-id="' + front_id + '"]', $panel);
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

    function checkRepeatError($panel) {
        if ($panel.find('[name="recurrence_enabled"]').val() == 1 && $panel.find('[name="recurrence_frequencies[]"]:checked').length == 0) {
            $panel.find('[name="recurrence_enabled"]').closest('.form-group').addClass('has-error');
            $panel.find('.bookly-js-frequencies').closest('.form-group').find('button.dropdown-toggle').addClass('btn-danger').removeClass('btn-default');
            $panel.find('form .bookly-js-recurrence-error').html(BooklyL10n.recurrence_error);
            $panel.find('.ajax-service-send').prop('disabled', true);
        } else {
            $panel.find('[name="recurrence_enabled"]').closest('.form-group').removeClass('has-error');
            $panel.find('.bookly-js-frequencies').closest('.form-group').find('button.dropdown-toggle').removeClass('btn-danger').addClass('btn-default');
            $panel.find('form .bookly-js-recurrence-error').html('');
            $panel.find('.ajax-service-send').prop('disabled', false);
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

    makeSortable();
});