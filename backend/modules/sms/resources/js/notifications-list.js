jQuery(function($) {
    var $notificationList       = $('#bookly-js-notification-list'),
        $btnCheckAll            = $('.bookly-js-check-all', $notificationList),
        $modalGeneralSettings   = $('#bookly-js-general-settings-modal'),
        $btnGeneralSettings     = $('#bookly-js-settings'),
        $btnSaveGeneralSettings = $('.bookly-js-save', $modalGeneralSettings),
        $modalTestEmail         = $('#bookly-test-email-notifications-modal'),
        $btnTestEmail           = $('#bookly-js-test-email-notifications'),
        $testNotificationsList  = $('#bookly-js-test-notifications-list', $modalTestEmail),
        $btnDeleteNotifications = $('#bookly-js-delete-notifications'),
        $filter                 = $('#bookly-filter')
    ;

    /**
     * Notification list
     */
    var dt = $notificationList
        .DataTable({
            paging: false,
            info: false,
            processing: true,
            responsive: true,
            serverSide: false,
            ajax: {
                url: ajaxurl,
                data: {action: 'bookly_get_notifications', csrf_token: BooklyL10n.csrfToken, gateway: BooklyL10n.gateway}
            },
            order: [1, 'asc'],
            columns: [
                {
                    data: 'order',
                    responsivePriority: 2,
                    render: function (data, type, row, meta) {
                        return '<span class="hidden">' + data + '</span><i class="fa fa-fw ' + row.icon + '" title="' + row.title + '"></i>';
                    }
                },
                {
                    data: 'name',
                    responsivePriority: 1
                },
                {
                    data: 'active',
                    responsivePriority: 2,
                    render: function (data, type, row, meta) {
                        return '<span class="label ' + (row.active == 1 ? 'label-success' : 'label-danger') + '">' + BooklyL10n.state[data] + '</span>' + ' (<a href="#" data-action="toggle-active">' + BooklyL10n.action[data] + '</a>)';
                    }
                },
                {
                    data: 'active',
                    className: 'text-right',
                    orderable: false,
                    responsivePriority: 1,
                    render: function (data, type, row, meta) {
                        return ' <button type="button" class="btn btn-default ladda-button" data-action="edit" data-spinner-size="40" data-style="zoom-in" data-spinner-color="#666666"><i class="glyphicon glyphicon-edit"></i> <span class="ladda-label">' + BooklyL10n.edit + '</span></a>';
                    }
                },
                {
                    orderable: false,
                    responsivePriority: 1,
                    render: function (data, type, row, meta) {
                        return '<input type="checkbox" class="bookly-js-delete" value="' + row.id + '" />';
                    }
                }
            ],
            dom: "<'row'<'col-sm-6'<'pull-left'>><'col-sm-6'>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row pull-left'<'col-sm-12 bookly-margin-top-lg'p>>",
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing: BooklyL10n.processing
            }
        })
        .on('click', '[data-action=toggle-active]', function (e) {
            var row = dt.row($(this).closest('td')),
                data = row.data();
            e.preventDefault();
            data.active = data.active === '1' ? '0' : '1';
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action     : 'bookly_set_notification_state',
                    csrf_token : BooklyL10n.csrfToken,
                    id         : data.id,
                    active     : data.active
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        row.data(data).draw();
                        booklyAlert({success: [BooklyL10n.settingsSaved]});
                    }
                }
            });
        });

    /**
     * On filters change.
     */
    $filter
        .on('keyup', function () {
            dt.search(this.value).draw();
        })
        .on('keydown', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        })
    ;


    /**
     * Select all notifications.
     */
    $btnCheckAll.on('change', function () {
        $('tbody input:checkbox', $notificationList).prop('checked', this.checked);
    });

    /**
     * General settings
     */
    $btnGeneralSettings
        .on('click', function () {
            $modalGeneralSettings.modal('show');
        });

    $btnSaveGeneralSettings
        .on('click',function () {
            var ladda = Ladda.create(this),
                data  = $(this).closest('form').serializeArray()
            ;
            data.push({name: 'action', value: 'bookly_save_general_settings_for_notifications'});

            ladda.start();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.settingsSaved]});
                        $modalGeneralSettings.modal('hide');
                    }
                    ladda.stop();
                }
            });
        });

    /**
     * Delete taxes.
     */
    $btnDeleteNotifications.on('click', function () {
        if (confirm(BooklyL10n.areYouSure)) {
            var ladda = Ladda.create(this),
                data = [],
                $checkboxes = $('input.bookly-js-delete:checked', $notificationList);
            ladda.start();

            $checkboxes.each(function () {
                data.push(this.value);
            });

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_delete_notifications',
                    csrf_token: BooklyL10n.csrfToken,
                    notifications: data
                },
                dataType: 'json',
                success: function (response) {
                    ladda.stop();
                    if (response.success) {
                        dt.rows($checkboxes.closest('td')).remove().draw();
                    }
                }
            });
        }
    });

    $btnTestEmail
        .on('click', function () {
            $modalTestEmail.modal()
        });

    $modalTestEmail
        .on('change', '#bookly-check-all-entities', function () {
            $(':checkbox', $testNotificationsList).prop('checked', this.checked);
            $(':checkbox:first-child', $testNotificationsList).trigger('change');
        })
        .on('click', '.btn-success', function () {
            var ladda = Ladda.create(this),
                data  = $(this).closest('form').serializeArray();
            ladda.start();
            $(':checked', $testNotificationsList).each(function () {
                data.push({name: 'notification_ids[]', value: $(this).data('notification-id')});
            });
            data.push({name: 'action', value: 'bookly_test_email_notifications'});
            $.ajax({
                url : ajaxurl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (response) {
                    ladda.stop();
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.sentSuccessfully]});
                    }
                }
            });
        })
        .on('shown.bs.modal', function () {
            $testNotificationsList.html('');
            var active = 0;
            (dt.rows().data()).each(function (notification) {
                var $checkbox = $('<input/>', {type: 'checkbox', checked: notification.active == '1', 'data-notification-id': notification.id}),
                    $div = $('<div/>', {class: 'checkbox'}).append($('<label/>').append($checkbox).append(notification.name));
                $testNotificationsList.append($('<li/>', {class: 'bookly-padding-horizontal-md'}).append($div));
                if (notification.active == '1') {
                    active++;
                }
            });
            $('.bookly-js-count', $modalTestEmail).html(active);
        });

    $testNotificationsList
        .on('change', ':checkbox', function () {
            $('.bookly-js-count', $modalTestEmail).html($(':checked', $testNotificationsList).length);
        });

    booklyAlert(BooklyL10n.alert);
});