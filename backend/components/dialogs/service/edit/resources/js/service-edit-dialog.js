jQuery(function ($) {
    'use strict';
    var $servicesList         = $('#services-list'),
        $serviceDialog        = $('#bookly-edit-service-modal'),
        $containers           = $('.bookly-js-service-containers .tab-pane > div'),
        $serviceLoading       = $('.bookly-js-service-containers > .bookly-loading', $serviceDialog),
        $serviceTabs          = $('.bookly-js-service-tabs', $serviceDialog),
        $wrapContainer        = $('.bookly-js-service-containers', $serviceDialog),
        $generalContainer     = $('#bookly-services-general-container', $serviceDialog),
        $advancedContainer    = $('#bookly-services-advanced-container', $serviceDialog),
        $timeContainer        = $('#bookly-services-time-container', $serviceDialog),
        $extrasContainer      = $('#bookly-services-extras-container', $serviceDialog),
        $specialDaysContainer = $('#bookly-services-special-days-container', $serviceDialog),
        $scheduleContainer    = $('#bookly-services-schedule-container', $serviceDialog),
        $additionalContainer  = $('#bookly-service-additional-html', $serviceDialog),
        $saveButton           = $('#bookly-save', $serviceDialog),
        updateStaffChoice     = null,
        $updateStaffModal     = $('#bookly-update-service-settings'),
        $serviceType          = $('[name="type"]', $serviceDialog),
        $serviceId            = $('[name="id"]', $serviceDialog),
        $serviceError         = $('.bookly-js-service-error', $serviceDialog)
    ;

    $servicesList.on('click', '[data-action="edit"]', function () {
        let data = $servicesList.DataTable().row($(this).closest('td')).data();
        $containers.html('');
        $serviceTabs.hide();
        $serviceLoading.show();
        $serviceDialog.modal('show');
        $.ajax({
            url     : ajaxurl,
            type    : 'POST',
            data    : {
                action    : 'bookly_get_service_data',
                csrf_token: BooklyServiceEditDialogL10n.csrfToken,
                id        : data.id
            },
            dataType: 'json',
            success : function (response) {
                $generalContainer.html(response.data.general_html);
                $advancedContainer.html(response.data.advanced_html);
                $timeContainer.html(response.data.time_html);
                $extrasContainer.html(response.data.extras_html);
                $specialDaysContainer.html(response.data.special_days_html);
                $scheduleContainer.html(response.data.schedule_html);
                $additionalContainer.html(response.data.additional_html);
                $serviceId.val(data.id);
                $serviceType.val(response.data.type);

                /**
                 * Init general tab
                 */
                var $panel           = $generalContainer,
                    $colorPicker     = $panel.find('.bookly-js-color-picker'),
                    $visibility      = $panel.find('input[name="visibility"]'),
                    $providers       = $panel.find('.bookly-js-providers'),
                    $staffPreference = $panel.find('[name=staff_preference]'),
                    $prefStaffOrder  = $panel.find('.bookly-js-preferred-staff-order'),
                    $prefStaffList   = $panel.find('.bookly-js-preferred-staff-list'),
                    $prefPeriod      = $panel.find('.bookly-js-preferred-period'),
                    staff_data       = {}
                ;
                // Color picker.
                initColorPicker($colorPicker);
                // Visibility.
                $visibility.off().on('change', function () {
                    $panel.find('.bookly-js-groups-list').toggle($panel.find('input[name="visibility"]:checked').val() === 'group');
                });
                // Providers.
                $providers.booklyDropdown();
                // Providers preference.
                $.each(response.data.staff, function (index, category) {
                    $.each(category.items, function (index, staff) {
                        staff_data[staff.id] = staff.full_name;
                    });
                });
                $staffPreference.on('change', function () {
                    if (this.value === 'order' && $prefStaffList.html() === '') {
                        var $staffIds  = $staffPreference.data('default'),
                            $draggable = $('<div class="bookly-flex-cell"><i class="bookly-js-handle bookly-margin-right-sm bookly-icon bookly-icon-draghandle bookly-cursor-move"></i><input type="hidden" name="positions[]" /></div>');
                        $draggable.find('i').attr('title', BooklyL10n.reorder);
                        $staffIds.forEach(function (staffId) {
                            $prefStaffList.append($draggable.clone().find('input').val(staffId).end().append(staff_data[staffId]));
                        });
                        Object.keys(BooklyServiceEditDialogL10n.staff).forEach(function (staffId) {
                            staffId = parseInt(staffId);
                            if ($staffIds.indexOf(staffId) === -1) {
                                $prefStaffList.append($draggable.clone().find('input').val(staffId).end().append(staff_data[staffId]));
                            }
                        });
                    }
                    $prefStaffOrder.toggle(this.value === 'order');
                    $prefPeriod.toggle(this.value === 'least_occupied_for_period' || this.value === 'most_occupied_for_period');
                }).trigger('change');
                // Preferred providers order.
                $prefStaffList.sortable({
                    axis  : 'y',
                    handle: '.bookly-js-handle',
                    update: function () {
                        var positions = [];
                        $prefStaffList.find('input').each(function () {
                            positions.push(this.value);
                        });
                        $.ajax({
                            type: 'POST',
                            url : ajaxurl,
                            data: {
                                action    : 'bookly_pro_update_service_staff_preference_orders',
                                service_id: $panel.data('service-id'),
                                positions : positions,
                                csrf_token: BooklyServiceEditDialogL10n.csrfToken
                            }
                        });
                    }
                });

                /**
                 * Init advanced tab
                 */
                $('.bookly-js-frequencies').booklyDropdown();

                $advancedContainer.off().on('change', '[name="recurrence_enabled"]', function () {
                    $advancedContainer.find('.bookly-js-frequencies').closest('.form-group').toggle(this.value != '0');
                    checkRepeatError($advancedContainer);
                }).on('change', '.bookly-js-frequencies input[type="checkbox"]', function () {
                    checkRepeatError($advancedContainer);
                }).on('change', '[name=limit_period]', function () {
                    $advancedContainer.find('[name=appointments_limit]').closest('.form-group').toggle(this.value !== 'off');
                }).on('keyup change', '.bookly-js-capacity', function () {
                    checkCapacityError($advancedContainer);
                });

                /**
                 * Init time tab
                 */
                var $duration        = $('.bookly-js-duration', $timeContainer),
                    $unitsBlock      = $('.bookly-js-units-block', $timeContainer),
                    $unitDuration    = $('.bookly-js-unit-duration', $timeContainer)
                ;
                // Duration (and unit duration).
                $duration.off().on('change', function () {
                    if (this.value === 'custom') {
                        $serviceDialog.find('.bookly-js-price-label').hide();
                        $serviceDialog.find('.bookly-js-unit-price-label').show();
                        $unitsBlock.show();
                    } else {
                        $serviceDialog.find('.bookly-js-price-label').show();
                        $serviceDialog.find('.bookly-js-unit-price-label').hide();
                        $unitDuration.val(this.value);
                        $unitsBlock.hide();
                    }
                }).trigger('change');
                $duration.add($unitDuration).on('change', function () {
                    $serviceDialog.find('.bookly-js-start-time-info').toggle(this.value >= 86400);
                });

                /**
                 * Init other settings.
                 */
                $('.bookly-js-simple-dropdown', $serviceDialog).booklyDropdown();

                // Fields that are repeated at staff level.
                $serviceDialog.find('.bookly-js-question').each(function () {
                    $(this).data('last_value', this.value);
                });

                $serviceDialog.find('.bookly-js-service').hide();
                $serviceDialog.find('.bookly-js-service-' + response.data.type).css('display', '');

                // Switch to 'General' tab if active is not visible
                if ($('.bookly-js-service-tabs li.active').css('display') == 'none') {
                    $('#bookly-services-general-tab').click();
                }

                $(document.body).trigger('service.initForm', [$wrapContainer, data.id]);

                $serviceTabs.show();
                $serviceLoading.hide();

                /**
                 * Save service
                 */
                $saveButton.off().on('click', function (e) {
                    e.preventDefault();
                    var showModal = false;
                    if (updateStaffChoice === null) {
                        $serviceDialog.find('.bookly-js-question').each(function () {
                            if ($(this).data('last_value') !== this.value && ($(this).attr('name') != 'price' || $serviceType.val() == 'simple' || $serviceType.val() == 'package')) {
                                showModal = true;
                            }
                        });
                    }
                    if (showModal) {
                        $updateStaffModal.data('panel', $panel).modal('show');
                    } else {
                        submitServiceFrom($serviceDialog, updateStaffChoice);
                    }
                });

                /**
                 * Update staff services modal
                 */
                $updateStaffModal.off().on('click', '.bookly-yes', function () {
                    $updateStaffModal.modal('hide');
                    if ($('#bookly-remember-my-choice').prop('checked')) {
                        updateStaffChoice = true;
                    }
                    submitServiceFrom($serviceDialog, 1);
                }).on('click', '.bookly-no', function () {
                    if ($('#bookly-remember-my-choice').prop('checked')) {
                        updateStaffChoice = false;
                    }
                    submitServiceFrom($serviceDialog, 0);
                });

                /**
                 * Local functions
                 */
                function initColorPicker($jquery_collection) {
                    $jquery_collection.each(function () {
                        $(this).data('last-color', $(this).val());
                    });
                    $jquery_collection.wpColorPicker({
                        width: 200
                    });
                }

                function checkRepeatError($panel) {
                    if ($panel.find('[name="recurrence_enabled"]').val() == 1 && $panel.find('[name="recurrence_frequencies[]"]:checked').length == 0) {
                        $panel.find('[name="recurrence_enabled"]').closest('.form-group').addClass('has-error');
                        $panel.find('.bookly-js-frequencies').closest('.form-group').find('button.dropdown-toggle').addClass('btn-danger').removeClass('btn-default');
                        $serviceError.find('.bookly-js-recurrence-error').remove();
                        $serviceError.append('<div class="bookly-js-recurrence-error bookly-js-error">' + BooklyL10n.recurrence_error + '</div>');
                    } else {
                        $panel.find('[name="recurrence_enabled"]').closest('.form-group').removeClass('has-error');
                        $panel.find('.bookly-js-frequencies').closest('.form-group').find('button.dropdown-toggle').removeClass('btn-danger').addClass('btn-default');
                        $serviceError.find('.bookly-js-recurrence-error').remove();
                    }
                    $saveButton.prop('disabled', $serviceError.find('.bookly-js-error').length > 0);
                }

                function checkCapacityError($panel) {
                    if (parseInt($panel.find('[name="capacity_min"]').val()) > parseInt($panel.find('[name="capacity_max"]').val())) {
                        $serviceError.find('.bookly-js-capacity-error').remove();
                        $serviceError.append('<div class="bookly-js-capacity-error bookly-js-error">' + BooklyL10n.capacity_error + '</div>');
                        $panel.find('[name="capacity_min"]').closest('.form-group').addClass('has-error');
                    } else {
                        $serviceError.find('.bookly-js-capacity-error').remove();
                        $panel.find('[name="capacity_min"]').closest('.form-group').removeClass('has-error');
                    }
                    $saveButton.prop('disabled', $serviceError.find('.bookly-js-error').length > 0);
                }

                function submitServiceFrom($panel, update_staff) {
                    $panel.find('input[name=update_staff]').val(update_staff ? 1 : 0);
                    $panel.find('input[name=package_service_changed]').val($panel.find('[name=package_service]').data('last_value') != $panel.find('[name=package_service]').val() ? 1 : 0);
                    var ladda = rangeTools.ladda($panel.find('#bookly-save').get(0)),
                        data = $panel.find('form').serializeArray();
                    $(document.body).trigger( 'service.submitForm', [ $panel, data ] );
                    $.post(ajaxurl, data, function (response) {
                        if (response.success) {
                            booklyAlert(response.data.alert);
                            if (response.data.new_extras_list) {
                                ExtrasL10n.list = response.data.new_extras_list
                            }
                            $servicesList.DataTable().ajax.reload();
                            $serviceDialog.modal('hide');
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                    }, 'json').always(function() {
                        ladda.stop();
                    });
                }
            }
        });
    });
});