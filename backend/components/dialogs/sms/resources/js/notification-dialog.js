jQuery(function ($) {
    var $notificationList    = $('#bookly-js-notification-list'),
        $btnNewNotification  = $('#bookly-js-new-notification'),
        $modalNotification   = $('#bookly-js-notification-modal'),
        containers           = {
            settings : $('#bookly-js-settings-container', $modalNotification),
            statuses : $('.bookly-js-statuses-container', $modalNotification),
            services : $('.bookly-js-services-container', $modalNotification),
            recipient: $('.bookly-js-recipient-container', $modalNotification),
            message  : $('#bookly-js-message-container',  $modalNotification),
            attach   : $('.bookly-js-attach-container',   $modalNotification),
            codes    : $('.bookly-js-codes-container',    $modalNotification)
        },
        $offsets             = $('.bookly-js-offset', containers.settings),
        $notificationType    = $('select[name=\'notification[type]\']', containers.settings),
        $labelSend           = $('.bookly-js-offset-exists', containers.settings),
        $offsetBidirectional = $('.bookly-js-offset-bidirectional', containers.settings),
        $offsetBefore        = $('.bookly-js-offset-before', containers.settings),
        $btnSaveNotification = $('.bookly-js-save', $modalNotification),
        $helpType            = $('.bookly-js-help-block', containers.settings),
        $codes               = $('table.bookly-codes', $modalNotification),
        $status              = $("select[name='notification[settings][status]']", containers.settings),
        $defaultStatuses,
        useTinyMCE           = typeof(tinyMCE) !== 'undefined',
        $textarea            = $('#bookly-js-message', containers.message)
    ;

    function setNotificationText(text) {
        if (useTinyMCE) {
            tinyMCE.activeEditor.setContent(text);
        } else {
            return $textarea.val(text);
        }
    }

    function format(option) {
        return option.id && option.element.dataset.icon ? '<i class="fa fa-fw ' + option.element.dataset.icon + '"></i> ' + option.text : option.text;
    }

    $modalNotification
        .on('show.bs.modal.first', function () {
            $notificationType.trigger('change');
            $modalNotification.unbind('show.bs.modal.first');
            if (useTinyMCE) {
                tinymce.init(tinyMCEPreInit);
            }
            containers.message.siblings('a[data-toggle=collapse]').html(BooklyNotificationDialogL10n.title.container);
            $('.bookly-js-services', containers.settings).booklyDropdown();
            $('.modal-title', $modalNotification).html(BooklyNotificationDialogL10n.title.edit);
        });

    /**
     * Notification
     */
    $notificationType
        .on('change', function () {
            if ($(':selected', $notificationType).length == 0) {
                // Un supported notification type (without Pro)
                $notificationType.val('new_booking');
            }
            var $modalBody        = $(this).closest('.modal-body'),
                $attach           = $modalBody.find('.bookly-js-attach'),
                $selected         = $(':selected', $notificationType),
                set               = $selected.data('set').split(' '),
                recipients        = $selected.data('recipients'),
                showAttach        = $selected.data('attach') || [],
                hideServices      = true,
                hideStatuses      = true,
                notification_type = $selected.val()
            ;

            $helpType.hide();
            $offsets.hide();

            switch (notification_type) {
                case 'appointment_reminder':
                case 'ca_status_changed':
                case 'ca_status_changed_recurring':
                    hideStatuses = false;
                    hideServices = false;
                    break;
                case 'customer_birthday':
                case 'customer_new_wp_user':
                case 'last_appointment':
                    break;
                case 'new_booking':
                case 'new_booking_recurring':
                    hideStatuses = false;
                    hideServices = false;
                    break;
                case 'new_booking_combined':
                    $helpType.filter('.' + notification_type).show();
                    break;
                case 'new_package':
                case 'package_deleted':
                    break;
                case 'staff_day_agenda':
                    $("input[name='notification[settings][option]'][value=3]", containers.settings).prop('checked', true);
                    break;
                case 'staff_waiting_list':
                    break;
            }

            containers.statuses.toggle(!hideStatuses);
            containers.services.toggle(!hideServices);

            switch (set[0]) {
                case 'bidirectional':
                    $labelSend.show();
                    $('.bookly-js-offsets', $offsetBidirectional).each(function () {
                        $(this).toggle($(this).hasClass('bookly-js-' + set[1]));
                    });
                    if (set[1] !== 'full') {
                        $('.bookly-js-' + set[1] + ' input:radio', $offsetBidirectional).prop('checked', true);
                    }
                    $offsetBidirectional.show();
                    break;
                case 'before':
                    $offsetBefore.show();
                    $labelSend.show();
                    break;
            }

            // Hide/un hide recipient
            $.each(['customer', 'staff', 'admin'], function (index, value) {
                $("[name$='[to_" + value + "]']:checkbox", containers.recipient).closest('.checkbox').toggle(recipients.indexOf(value) != -1);
            });

            // Hide/un hide attach
            $attach.hide();
            $.each(showAttach, function (index, value) {
                $('.bookly-js-' + value, containers.attach).show();
            });
            $codes.hide();
            $codes.filter('.bookly-js-codes-' + notification_type).show();
        })
        .select2({
            minimumResultsForSearch: -1,
            width                  : '100%',
            theme                  : 'bootstrap',
            allowClear             : false,
            templateResult         : format,
            templateSelection      : format,
            escapeMarkup           : function (m) {
                return m;
            }
        });

    $('.bookly-js-services', $modalNotification).booklyDropdown({});

    $btnNewNotification
        .on('click', function () {
            showNotificationDialog();
        });

    $btnSaveNotification
        .on('click', function () {
            if (useTinyMCE) {
                tinyMCE.triggerSave();
            }
            var data  = $modalNotification.serializeArray(),
                ladda = Ladda.create(this);
            ladda.start();
            data.push({name: 'action', value: 'bookly_save_notification'});

            $.ajax({
                url     : ajaxurl,
                type    : 'POST',
                data    : data,
                dataType: 'json',
                success : function (response) {
                    ladda.stop();
                    if (response.success) {
                        $notificationList.DataTable().ajax.reload();
                        $modalNotification.modal('hide');
                    }
                }
            });
        });

    $notificationList
        .on('click', '[data-action=edit]', function () {
            var row  = $notificationList.DataTable().row($(this).closest('td')),
                data = row.data();
            showNotificationDialog(data.id);
        });

    function showNotificationDialog(id) {
        $('.bookly-js-loading:first-child', $modalNotification).addClass('bookly-loading').removeClass('collapse');
        $('.bookly-js-loading:last-child', $modalNotification).addClass('collapse');
        $modalNotification.modal('show');
        if (id === undefined) {
            setNotificationData(BooklyNotificationDialogL10n.defaultNotification);
        } else {
            $.ajax({
                url     : ajaxurl,
                type    : 'POST',
                data    : {
                    action    : 'bookly_get_notification_data',
                    csrf_token: BooklyNotificationDialogL10n.csrfToken,
                    id        : id
                },
                dataType: 'json',
                success : function (response) {
                    setNotificationData(response.data);
                }
            });
        }
    }

    function setNotificationData(data) {
        // Notification settings
        $("input[name='notification[id]']", containers.settings).val(data.id);
        $("input[name='notification[name]']", containers.settings).val(data.name);
        $("input[name='notification[active]'][value=" + data.active + "]", containers.settings).prop('checked', true);
        if ($defaultStatuses) {
            $status.html($defaultStatuses);
        } else {
            $defaultStatuses = $status.html();
        }
        if ($status.find('option[value="' + data.settings.status + '"]').length > 0) {
            $status.val(data.settings.status);
        } else {
            var custom_status = data.settings.status.charAt(0).toUpperCase() + data.settings.status.slice(1);

            $status.append($("<option></option>", {value: data.settings.status, text: custom_status.replace(/\-/g, ' ')})).val(data.settings.status);
        }

        $("input[name='notification[settings][services][any]'][value='" + data.settings.services.any + "']", containers.settings).prop('checked', true);
        $('.bookly-js-services', containers.settings).booklyDropdown('setSelected', data.settings.services.ids);

        $("input[name='notification[settings][option]'][value=" + data.settings.option + "]", containers.settings).prop('checked', true);
        $("select[name='notification[settings][offset_hours]']", containers.settings).val(data.settings.offset_hours);
        $("select[name='notification[settings][perform]']", containers.settings).val(data.settings.perform);
        $("select[name='notification[settings][at_hour]']", containers.settings).val(data.settings.at_hour);
        $("select[name='notification[settings][offset_bidirectional_hours]']", containers.settings).val(data.settings.offset_bidirectional_hours);
        $("select[name='notification[settings][offset_before_hours]']", containers.settings).val(data.settings.offset_before_hours);
        $("select[name='notification[settings][before_at_hour]']", containers.settings).val(data.settings.before_at_hour);

        // Recipients
        $("input[name='notification[to_staff]']", containers.settings).prop('checked', data.to_staff == '1');
        $("input[name='notification[to_customer]']", containers.settings).prop('checked', data.to_customer == '1');
        $("input[name='notification[to_admin]']", containers.settings).prop('checked', data.to_admin == '1');

        // Message
        $("input[name='notification[subject]']", containers.message).val(data.subject);
        $("input[name='notification[attach_ics]']", containers.message).prop('checked', data.attach_ics == '1');
        $("input[name='notification[attach_invoice]']", containers.message).prop('checked', data.attach_invoice == '1');

        setNotificationText(data.message);

        if (data.hasOwnProperty('id')) {
            $('.modal-title', $modalNotification).html(BooklyNotificationDialogL10n.title.edit);
            containers.settings.collapse('hide');
            containers.message.collapse('show');
            $('.bookly-js-save > span.ladda-label', $modalNotification).text(BooklyNotificationDialogL10n.title.save);
        } else {
            $('.modal-title', $modalNotification).html(BooklyNotificationDialogL10n.title.new);
            containers.settings.collapse('show');
            $('.bookly-js-save > span.ladda-label', $modalNotification).text(BooklyNotificationDialogL10n.title.create);
        }

        $notificationType.val(data.type).trigger('change');

        $('.bookly-js-loading', $modalNotification).toggleClass('collapse');
    }
});