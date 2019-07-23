jQuery(function ($) {
    'use strict';

    let $staffList = $('#staff-list'),
        $modal = $('#bookly-staff-edit-modal'),
        $modalBody = $('.modal-body', $modal),
        $modalTitle = $('.modal-title', $modal),
        $modalFooter = $('.modal-footer ', $modal),
        $saveBtn = $('.bookly-js-save', $modalFooter),
        $archiveBtn = $('.bookly-js-staff-archive', $modalFooter),
        $validateErrors = $('.bookly-js-errors', $modalFooter),
        $deleteCascadeModal = $('.bookly-js-delete-cascade-confirm'),
        $staffCount = $('.bookly-js-staff-count'),
        currentTab,
        staff_id,
        holidays
    ;

    $staffList
        .on('click', '[data-action="edit"]', function () {
            let data = $staffList.DataTable().row($(this).closest('td')).data();
            staff_id = data.id;
            editStaff(staff_id);
        });

    $('#bookly-js-new-staff')
        .on('click', function () {
            if (BooklyStaffEditDialogL10n.proRequired == '1' && $staffCount.html() > 0) {
                booklyAlert({error: [BooklyStaffEditDialogL10n.limitation]});
                return false;
            } else {
                staff_id = 0;
                editStaff(staff_id);
            }
        });

    if (BooklyStaffEditDialogL10n.activeStaffId != '0') {
        staff_id = BooklyStaffEditDialogL10n.activeStaffId;
        editStaff(staff_id);
    }

    function editStaff(staff_id) {
        $modalTitle.html(staff_id ? BooklyStaffEditDialogL10n.editStaff : BooklyStaffEditDialogL10n.createStaff);
        $('#bookly-staff-delete', $modalFooter).toggle(staff_id != 0);

        $modalFooter.hide();
        $validateErrors.html('');
        $saveBtn.prop('disabled', false);
        $modalBody.html('<div class="bookly-loading"></div>');
        $modal.modal();
        $.get(ajaxurl, {action: 'bookly_get_staff_data', id: staff_id, csrf_token: BooklyStaffEditDialogL10n.csrfToken}, function (response) {
            $modalBody.html(response.data.html.edit);
            booklyAlert(response.data.alert);
            $modalFooter.show();
            holidays = response.data.holidays;
            let $details_container = $('#bookly-details-container', $modalBody),
                $services_container = $('#bookly-services-container', $modalBody),
                $schedule_container = $('#bookly-schedule-container', $modalBody),
                $holidays_container = $('#bookly-holidays-container', $modalBody),
                $special_days_container = $('#bookly-special-days-container', $modalBody)
            ;
            $details_container.append(response.data.html.details);
            $services_container.append(response.data.html.services);
            $schedule_container.append(response.data.html.schedule);
            $holidays_container.append(response.data.html.holidays);
            $special_days_container.append(response.data.html.special_days);

            $('.panel-footer', $modalBody).hide();

            new BooklyStaffDetails($details_container, {
                get_details : {},
                intlTelInput: BooklyStaffEditDialogL10n.intlTelInput,
                l10n: BooklyStaffEditDialogL10n
            });

            $archiveBtn.toggle(staff_id ? response.data.staff.visibility !== 'archive' : false);
            if (currentTab) {
                $('#' + currentTab, $modalBody).click();
            }
        });
    }

    /**
     * Delete staff member.
     */
    $modalFooter
        .on('click', '#bookly-staff-delete', function (e) {
            e.preventDefault();

            var ladda = Ladda.create(this),
                data  = {
                    action: 'bookly_remove_staff',
                    'staff_ids[]': staff_id,
                    csrf_token: BooklyStaffEditDialogL10n.csrfToken
                };
            ladda.start();

            var delete_staff = function (ajaxurl, data) {
                $.post(ajaxurl, data, function (response) {
                    ladda.stop();
                    if (!response.success) {
                        switch (response.data.action) {
                            case 'show_modal':
                                $deleteCascadeModal.modal('show');
                                break;
                            case 'confirm':
                                if (confirm(BooklyStaffEditDialogL10n.areYouSure)) {
                                    delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
                                }
                                break;
                        }
                    } else {
                        $modal.modal('hide');
                        $staffList.DataTable().ajax.reload();
                    }
                });
            };

            delete_staff(ajaxurl, data);
        });

    $modalBody
        // Delete staff avatar
        .on('click', '.bookly-thumb-delete', function () {
            var $thumb = $(this).parents('.bookly-js-image');
            $thumb.attr('style', '');
            $modalBody.find('[name=attachment_id]').val('').trigger('change');
        })

        // Open details tab
        .on('click', '#bookly-details-tab', function () {
            $('.tab-pane > div').hide();
            $('#bookly-details-container',  $modalBody).show();
        })

        // Open services tab
        .on('click', '#bookly-services-tab', function () {
            $('.tab-pane > div').hide();
            let $container = $('#bookly-services-container', $modalBody);
            new BooklyStaffServices($container, {
                get_staff_services: {
                    action: 'bookly_get_staff_services',
                    staff_id: staff_id,
                    csrf_token: BooklyStaffEditDialogL10n.csrfToken
                },
                onLoad: function () {
                    $('.panel-footer', $container).hide();
                    $('#bookly-services-save', $container).addClass('bookly-js-save');
                    $(document.body).trigger('staff_edit.validation', ['staff-services', false, '']);
                },
                l10n: BooklyStaffEditDialogL10n.services,
            });

            $('#bookly-services-save', $container).addClass('bookly-js-save');
            $container.show();
        })

        // Open special days tab
        .on('click', '#bookly-special-days-tab', function () {
            $('.tab-pane > div').hide();
            let $container = $('#bookly-special-days-container', $modalBody);
            new BooklyStaffSpecialDays($container, {
                staff_id: staff_id,
                csrf_token: BooklyStaffEditDialogL10n.csrfToken,
                l10n: SpecialDaysL10n
            });

            $('#bookly-js-special-days-save-days', $container).addClass('bookly-js-save');
            $container.show();
        })

        // Open schedule tab
        .on('click', '#bookly-schedule-tab', function () {
            $('.tab-pane > div').hide();
            let $container = $('#bookly-schedule-container', $modalBody);

            new BooklyStaffSchedule($container, {
                get_staff_schedule: {
                    action: 'bookly_get_staff_schedule',
                    staff_id: staff_id,
                    csrf_token: BooklyStaffEditDialogL10n.csrfToken
                },
                onLoad: function () {
                    $('.panel-footer', $container).hide();
                    $('#bookly-schedule-save', $container).addClass('bookly-js-save');
                },
                l10n: BooklyL10n
            });

            $('#bookly-schedule-save', $modalBody).addClass('bookly-js-save');
            $container.show();
        })

        // Open holiday tab
        .on('click', '#bookly-holidays-tab', function () {
            $('.tab-pane > div').hide();
            let $container = $('#bookly-holidays-container', $modalBody);

            new BooklyStaffDaysOff($container, {
                staff_id: staff_id,
                csrf_token: BooklyStaffEditDialogL10n.csrfToken,
                l10n: jQuery.extend(BooklyStaffEditDialogL10n.holidays, {holidays: holidays})
            });

            $container.show();
        })
        .on('click', '> .bookly-nav-justified [data-toggle=tab]', function () {
            currentTab = $(this).attr('id');
        });

    $deleteCascadeModal
        // Delete
        .on('click', '.bookly-js-delete', function () {
            $modalBody.html('<div class="bookly-loading"></div>');
            ladda = Ladda.create(this);
            ladda.start();
            delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
            $deleteCascadeModal.modal('hide');
            ladda.stop();
        })
        // Edit
        .on('click', '.bookly-js-edit', function () {
            ladda = Ladda.create(this);
            ladda.start();
            window.location.href = response.data.filter_url;
        });

    let waitResposes = 0,
        ladda,
        success;

    $saveBtn
        .on('click', function (e) {
            e.preventDefault();
            ladda = Ladda.create(this);
            ladda.start();

            let $buttons = $('.panel-footer', $modalBody);
            waitResposes = 0;
            success = true;
            $buttons
                .each(function () {
                    let $button = $('.bookly-js-save', this);
                    if ($button.length > 0) {
                        waitResposes++;
                        $button.trigger('click');
                    }
                });
        });

    $(document.body)
        .on('staff_edit.save', {},
            function (event, result) {
                if (waitResposes > 0) {
                    if (result.hasOwnProperty('error')){
                        success = false;
                    }
                    waitResposes --;
                }
                if (waitResposes <= 0) {
                    $staffList.DataTable().ajax.reload();
                    ladda ? ladda.stop() : null;
                    $modal.modal('hide');
                    booklyAlert({success: [BooklyStaffEditDialogL10n.settingsSaved]})
                }
            })
        .on('staff_edit.validation', {},
            function (event, tab, has_error, info) {
                let id = 'tab-' + tab + '-validation',
                    $container = $validateErrors.find('#' + id);
                if (has_error) {
                    if ($container.length === 0) {
                        $validateErrors.append($('<div/>').attr('id', id).html(info));
                    }
                } else {
                    $container.remove();
                }

                $saveBtn.prop('disabled', $('>', $validateErrors).length !== 0);
            });
});