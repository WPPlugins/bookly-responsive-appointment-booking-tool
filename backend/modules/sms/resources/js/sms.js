jQuery(function($) {

    var $form_login    = $('.ab-login-form'),
        $form_forgot   = $('.ab-forgot-form'),
        $form_register = $('.ab-register-form');

    booklyAlert(BooklyL10n.alert);

    $('.show-register-form').on('click', function (e) {
        e.preventDefault();
        $form_login.hide();
        $form_register.show();
        $form_forgot.hide();
    });

    $('.ab--show-login-form').on('click', function (e) {
        e.preventDefault();
        $form_login.show();
        $form_register.hide();
        $form_forgot.hide();
    });

    $('.show-forgot-form').on('click', function (e) {
        e.preventDefault();
        $form_forgot.show();
        $form_login.hide();
        $form_register.hide();
    });

    $('.form-forgot-next').on('click', function (e) {
        e.preventDefault();
        var $btn  = $(this);
        var $form = $(this).parents('form');
        var $code = $form.find('input[name="code"]');
        var $pwd  = $form.find('input[name="password"]');
        var $username   = $form.find('input[name="username"]');
        var $pwd_repeat = $form.find('input[name="password_repeat"]');
        var data  = { action: 'bookly_forgot_password', step: $btn.data('step'), 'username': $username.val() };
        switch ($(this).data('step')) {
            case 0:
                forgot_helper( data, function() {
                    $username.parent().addClass('hidden');
                    $code.parent().removeClass('hidden');
                    $btn.data('step', 1);
                });
                break;
            case 1:
                data.code = $code.val();
                forgot_helper(data, function() {
                    $code.parent().addClass('hidden');
                    $pwd.parent().removeClass('hidden');
                    $pwd_repeat.parent().removeClass('hidden');
                    $btn.data('step', 2);
                });
                break;
            case 2:
                data.code = $code.val();
                data.password = $pwd.val();
                data.password_repeat = $pwd_repeat.val();
                if (data.password == data.password_repeat && data.password != '') {
                    forgot_helper(data, function() {
                        $('.ab--show-login-form').trigger('click');
                        $btn.data('step', 0);
                        $username.parent().removeClass('hidden');
                        $pwd.parent().addClass('hidden');
                        $pwd_repeat.parent().addClass('hidden');
                        $form.trigger('reset');
                    });
                } else {
                    booklyAlert({error: [BooklyL10n.passwords_no_same]});
                }
                break;
        }
    });

    function forgot_helper(data, callback) {
        $.ajax({
            method     : 'POST',
            url        : ajaxurl,
            data       : data,
            dataType   : 'json',
            xhrFields  : {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success    : function (response) {
                if (response.success) {
                    callback();
                } else {
                    if (response.data && response.data.message) {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }
        });
    }

    $('#sms_tabs [data-target="#' + BooklyL10n.current_tab + '"]').tab('show');

    $('.bookly-admin-notify').on('change', function () {
        var $checkbox = $(this);
        $checkbox.hide().prev('img').show();
        $.get(ajaxurl, {action: 'bookly_admin_notify', option_name: $checkbox.attr('name'), value: $checkbox.is(':checked') ? 1 : 0 }, function () {}, 'json').always(function () {
            $checkbox.show().prev('img').hide();
        });
    });

    $('#ajax-send-change-password').on('click', function (e) {
        e.preventDefault();
        var $form = $('#form-change-password');
        var new_password = $form.find('#new_password').val();
        if ($form.find('#old_password').val() != '') {
            if (new_password == $form.find('#new_password_repeat').val() && new_password != '') {
                $.ajax({
                    type        : 'POST',
                    url         : ajaxurl,
                    data        : $form.serialize(),
                    dataType    : 'json',
                    xhrFields   : { withCredentials: true },
                    crossDomain : 'withCredentials' in new XMLHttpRequest(),
                    success     : function (response) {
                        if (response.success) {
                            $('#modal_change_password').modal('hide');
                            $form.trigger('reset');
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                });
            } else {
                booklyAlert({error: [BooklyL10n.passwords_no_same]});
            }
        } else {
            booklyAlert({error: [BooklyL10n.input_old_password]});
        }
    });

    /**
     * Notifications Tab.
     */
    var $phone_input = $('#admin_phone');
    if (BooklyL10n.intlTelInput.enabled) {
        $phone_input.intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            defaultCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get(ajaxurl, {action: 'bookly_ip_info'}, function () {
                }, 'json').always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: BooklyL10n.intlTelInput.utils
        });
    }
    $('#js-submit-notifications').on('click', function (e) {
        e.preventDefault();
        var ladda = Ladda.create(this);
        ladda.start();
        var $form = $(this).parents('form');
        $form.bookly_sms_administrator_phone = BooklyL10n.intlTelInput.enabled ? $phone_input.intlTelInput('getNumber') : $phone_input.val();
        $form.submit();
    });
    $('#send_test_sms').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url         : ajaxurl,
            data        : { action: 'bookly_send_test_sms', phone_number: BooklyL10n.intlTelInput.enabled ? $phone_input.intlTelInput('getNumber') : $phone_input.val() },
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success) {
                    booklyAlert({success: [response.message]});
                } else {
                    booklyAlert({error: [response.message]});
                }
            }
        });
    });

    /**
     * Auto-Recharge Tab.
     */
    var $recharge_init    = $('#bookly-auto-recharge-init'),
        $recharge_decline = $('#bookly-auto-recharge-decline'),
        $recharge_amount  = $('#bookly-recharge-amount')
        ;
    $recharge_init.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.get(ajaxurl, {action: 'bookly_init_auto_recharge', amount: $recharge_amount.val()}, function () {
        }, 'json').always(function (response) {
            if (response.success) {
                window.location.replace(response.data.paypal_preapproval);
            } else {
                ladda.stop();
                booklyAlert({error: [response.data.message]});
            }
        });
    });
    $recharge_decline.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();
        $.get(ajaxurl, {action: 'bookly_decline_auto_recharge'}, function () {}, 'json')
            .always(function (response) {
                ladda.stop();
                if (response.success) {
                    $recharge_amount.prop('disabled', false);
                    $recharge_init.prop('disabled', false);
                    $recharge_decline.prop('disabled', true);
                    booklyAlert({success: [response.data.message]});
                } else {
                    booklyAlert({error: [response.data.message]});
                }
            });
    });

    /**
     * Date range pickers options.
     */
    var picker_ranges = {};
    picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.today]      = [moment(), moment()];
    picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.last_month] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
    moment.locale('en', {
        months       : BooklyL10n.calendar.longMonths,
        monthsShort  : BooklyL10n.calendar.shortMonths,
        weekdays     : BooklyL10n.calendar.longDays,
        weekdaysShort: BooklyL10n.calendar.shortDays,
        weekdaysMin  : BooklyL10n.calendar.shortDays
    });
    var locale = {
        applyLabel      : BooklyL10n.apply,
        cancelLabel     : BooklyL10n.cancel,
        fromLabel       : BooklyL10n.from,
        toLabel         : BooklyL10n.to,
        customRangeLabel: BooklyL10n.custom_range,
        daysOfWeek      : BooklyL10n.calendar.shortDays,
        monthNames      : BooklyL10n.calendar.longMonths,
        firstDay        : parseInt(BooklyL10n.startOfWeek),
        format          : BooklyL10n.mjsDateFormat
    };

    /**
     * Purchases Tab.
     */
    $('[data-target="#purchases"]').one('click', function() {
        var $date_range = $('#purchases_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        );
        var dt = $('#bookly-purchases').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url : ajaxurl,
                data: function (d) {
                    return {
                        action: 'bookly_get_purchases_list',
                        range:  $date_range.data('date')
                    };
                },
                dataSrc: 'list'
            },
            columns: [
                { data: "date" },
                { data: "time" },
                { data: "type" },
                { data: "order" },
                { data: "status" },
                { data: "amount" }
            ],
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing:  BooklyL10n.processing
            }
        });

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    });

    /**
     * SMS Details Tab.
     */
    $('[data-target="#sms_details"]').one('click', function() {
        var $date_range = $('#sms_date_range');
        $date_range.daterangepicker(
            {
                parentEl : $date_range.parent(),
                startDate: moment().subtract(30, 'days'), // by default select "Last 30 days"
                ranges   : picker_ranges,
                locale   : locale
            },
            function (start, end) {
                var format = 'YYYY-MM-DD';
                $date_range
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        );
        var dt = $('#bookly-sms').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url : ajaxurl,
                data: function (d) {
                    return {
                        action: 'bookly_get_sms_list',
                        range:  $date_range.data('date')
                    };
                },
                dataSrc: 'list'
            },
            columns: [
                { data: "date" },
                { data: "time" },
                { data: "message" },
                { data: "phone" },
                { data: "sender_id" },
                { data: "charge" },
                { data: "status" }
            ],
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing:  BooklyL10n.processing
            }
        });

        $date_range.on('apply.daterangepicker', function () { dt.ajax.reload(); });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    /**
     * Prices Tab.
     */
    $("[data-target='#price']").one('click', function() {
        fillPriceTable();
    });
    if ($('form.ab-login-form').length){
        fillPriceTable();
    }

    function fillPriceTable() {
        var dt = $('#bookly-prices').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url : ajaxurl,
                data: { action: 'bookly_get_price_list' },
                dataSrc: 'list'
            },
            columns: [
                {
                    responsivePriority: 1,
                    render: function ( data, type, row, meta ) {
                        return '<div class="iti-flag ' + row.country_iso_code + '"></div>';
                    }
                },
                { data: "country_name" },
                { data: "phone_code" },
                {
                    render: function ( data, type, row, meta ) {
                        return '$' + row.price.replace(/0+$/, '');
                    }
                },
                {
                    render: function ( data, type, row, meta ) {
                        if (row.price_alt == '') {
                            return '-';
                        } else {
                            return '$' + row.price_alt.replace(/0+$/, '');
                        }
                    }
                }
            ],
            language: {
                zeroRecords: BooklyL10n.zeroRecords,
                processing:  BooklyL10n.processing
            }
        });
    }

    /**
     * Sender ID Tab.
     */
    $("[data-target='#sender_id']").one('click', function() {
        var $request_sender_id = $('#bookly-request-sender_id'),
            $reset_sender_id   = $('#bookly-reset-sender_id'),
            $cancel_sender_id  = $('#bookly-cancel-sender_id'),
            $sender_id         = $('#bookly-sender-id-input');

        var dt = $('#bookly-sender-ids').DataTable({
            ordering: false,
            paging: false,
            info: false,
            searching: false,
            processing: true,
            responsive: true,
            ajax: {
                url: ajaxurl,
                data: { action: 'bookly_get_sender_ids_list' },
                dataSrc: function (json) {
                    if (json.pending) {
                        $sender_id.val(json.pending);
                        $request_sender_id.hide();
                        $sender_id.prop('disabled',true);
                        $cancel_sender_id.show();
                    }

                    return json.list;
                }
            },
            columns: [
                { data: "date" },
                { data: "name" },
                { data: "status" },
                { data: "status_date" }
            ],
            language: {
                zeroRecords: BooklyL10n.zeroRecords2,
                processing:  BooklyL10n.processing
            }
        });

        $request_sender_id.on('click', function () {
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                data : {action: 'bookly_request_sender_id', 'sender_id': $sender_id.val()},
                dataType : 'json',
                xhrFields: {withCredentials: true},
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [BooklyL10n.sender_id.sent]});
                        $request_sender_id.hide();
                        $sender_id.prop('disabled',true);
                        $cancel_sender_id.show();
                        dt.ajax.reload();
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                }
            }).always(function () {
                ladda.stop();
            });
        });

        $reset_sender_id.on('click', function (e) {
            e.preventDefault();
            if (confirm(BooklyL10n.are_you_sure)) {
                $.ajax({
                    url: ajaxurl,
                    data: {action: 'bookly_reset_sender_id'},
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    success: function (response) {
                        if (response.success) {
                            booklyAlert({success: [BooklyL10n.sender_id.set_default]});
                            $('.bookly-js-sender-id').html('Bookly');
                            $('.bookly-js-approval-date').remove();
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            booklyAlert({error: [response.data.message]});
                        }
                    }
                });
            }
        });

        $cancel_sender_id.on('click',function () {
            if (confirm(BooklyL10n.are_you_sure)) {
                var ladda = Ladda.create(this);
                ladda.start();
                $.ajax({
                    method     : 'POST',
                    url        : ajaxurl,
                    data:      {action: 'bookly_cancel_sender_id'},
                    dataType   : 'json',
                    success    : function (response) {
                        if (response.success) {
                            $sender_id.prop('disabled', false).val('');
                            $request_sender_id.show();
                            $cancel_sender_id.hide();
                            dt.ajax.reload();
                        } else {
                            if (response.data && response.data.message) {
                                booklyAlert({error: [response.data.message]});
                            }
                        }
                    }
                }).always(function () {
                    ladda.stop();
                });
            }
        });
        $(this).on('click', function () { dt.ajax.reload(); });
    });

    $('#bookly-open-tab-sender-id').on('click', function (e) {
        e.preventDefault();
        $('#sms_tabs li[data-target="#sender_id"]').trigger('click');
    });
});