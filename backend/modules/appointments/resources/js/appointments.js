jQuery(function($) {

    var
        $appointmentsList   = $('#bookly-appointments-list'),
        $checkAllButton     = $('#bookly-check-all'),
        $idFilter           = $('#bookly-filter-id'),
        $appointmentDateFilter = $('#bookly-filter-date'),
        $creationDateFilter = $('#bookly-filter-creation-date'),
        $staffFilter        = $('#bookly-filter-staff'),
        $customerFilter     = $('#bookly-filter-customer'),
        $serviceFilter      = $('#bookly-filter-service'),
        $statusFilter       = $('#bookly-filter-status'),
        $addButton          = $('#bookly-add'),
        $printDialog        = $('#bookly-print-dialog'),
        $printButton        = $('#bookly-print'),
        $exportDialog       = $('#bookly-export-dialog'),
        $exportButton       = $('#bookly-export'),
        $deleteButton       = $('#bookly-delete'),
        isMobile            = false,
        urlParts            = document.URL.split('#'),
        pickers = {
            dateFormat:       'YYYY-MM-DD',
            appointmentDate: {
                startDate: moment().startOf('month'),
                endDate  : moment().endOf('month'),
            },
            creationDate: {
                startDate: moment(),
                endDate  : moment().add(100, 'years'),
            },
        }
    ;

    try {
        document.createEvent("TouchEvent");
        isMobile = true;
    } catch (e) {

    }

    $('.bookly-js-select').val(null);

    // Apply filter from anchor
    if (urlParts.length > 1) {
        urlParts[1].split('&').forEach(function (part) {
            var params = part.split('=');
            if (params[0] == 'appointment-date') {
                if (params['1'] == 'any') {
                    $appointmentDateFilter
                        .data('date', 'any').find('span')
                        .html(BooklyL10n.any_time);
                } else {
                    pickers.appointmentDate.startDate = moment(params['1'].substring(0, 10));
                    pickers.appointmentDate.endDate = moment(params['1'].substring(11));
                    $appointmentDateFilter
                        .data('date', pickers.appointmentDate.startDate.format(pickers.dateFormat) + ' - ' + pickers.appointmentDate.endDate.format(pickers.dateFormat))
                        .find('span')
                        .html(pickers.appointmentDate.startDate.format(BooklyL10n.mjsDateFormat) + ' - ' + pickers.appointmentDate.endDate.format(BooklyL10n.mjsDateFormat));
                }
            } else if (params[0] == 'tasks') {
                $appointmentDateFilter
                    .data('date', 'null').find('span')
                    .html(BooklyL10n.tasks.title);
            } else if (params[0] == 'created-date') {
                pickers.creationDate.startDate = moment(params['1'].substring(0, 10));
                pickers.creationDate.endDate = moment(params['1'].substring(11));
                $creationDateFilter
                    .data('date', pickers.creationDate.startDate.format(pickers.dateFormat) + ' - ' + pickers.creationDate.endDate.format(pickers.dateFormat))
                    .find('span')
                    .html(pickers.creationDate.startDate.format(BooklyL10n.mjsDateFormat) + ' - ' + pickers.creationDate.endDate.format(BooklyL10n.mjsDateFormat));
            } else {
                $('#bookly-filter-' + params[0]).val(params[1]);
            }
        });
    } else {
        $.each(BooklyL10n.filter, function (field, value) {
            if (value != '') {
                $('#bookly-filter-' + field).val(value);
            }
            // check if select has correct values
            if ($('#bookly-filter-' + field).prop('type') == 'select-one') {
                if ($('#bookly-filter-' + field + ' option[value="' + value + '"]').length == 0) {
                    $('#bookly-filter-' + field).val(null);
                }
            }
        });
    }

    /**
     * Init DataTables.
     */
    var columns = [
        { data: 'id', responsivePriority: 2 },
        { data: 'start_date', responsivePriority: 2 },
        { data: 'staff.name', responsivePriority: 2 },
        { data: 'customer.full_name', render: $.fn.dataTable.render.text(), responsivePriority: 2 },
        {
            data: 'customer.phone',
            responsivePriority: 3,
            render: function (data, type, row, meta) {
                if (isMobile) {
                    return '<a href="tel:' + $.fn.dataTable.render.text().display(data) + '">' + $.fn.dataTable.render.text().display(data) + '</a>';
                } else {
                    return $.fn.dataTable.render.text().display(data);
                }
            }
        },
        { data: 'customer.email', render: $.fn.dataTable.render.text(), responsivePriority: 3 }
    ];
    if (BooklyL10n.add_columns.number_of_persons) {
        columns.push({
            data: 'number_of_persons',
            render: $.fn.dataTable.render.text(),
            responsivePriority: 3
        });
    }
    columns = columns.concat([
        {
            data: 'service.title',
            responsivePriority: 2,
            render: function ( data, type, row, meta ) {
                if (row.service.extras.length) {
                    var extras = '<ul class="bookly-list list-dots">';
                    $.each(row.service.extras, function (key, item) {
                        extras += '<li><nobr>' + item.title + '</nobr></li>';
                    });
                    extras += '</ul>';
                    return data + extras;
                }
                else {
                    return data;
                }
            }
        },
        { data: 'service.duration', responsivePriority: 3 },
        { data: 'status', responsivePriority: 2 },
        {
            data: 'payment',
            responsivePriority: 2,
            render: function ( data, type, row, meta ) {
                return '<a href="#bookly-payment-details-modal" data-toggle="modal" data-payment_id="' + row.payment_id + '">' + data + '</a>';
            }
        }
    ]);

    if (BooklyL10n.add_columns.ratings) {
        columns.push({
            data: 'rating',
            render: function ( data, type, row, meta ) {
                if (row.rating_comment == null) {
                    return row.rating;
                } else {
                    return '<a href="#" data-toggle="popover" data-trigger="focus" data-placement="bottom" data-content="' + $.fn.dataTable.render.text().display(row.rating_comment) + '">' + $.fn.dataTable.render.text().display(row.rating) + '</a>';
                }
            },
            responsivePriority: 1
        });
    }

    if (BooklyL10n.add_columns.notes) {
        columns.push({
            data: 'notes',
            render: $.fn.dataTable.render.text(),
            responsivePriority: 3
        });
    }
    $.each(BooklyL10n.cf_columns, function (i, cf_id) {
        columns.push({
            data: 'custom_fields.' + cf_id,
            render: $.fn.dataTable.render.text(),
            responsivePriority: 3,
            orderable: false
        });
    });
    columns.push({
        data: 'created_date',
        render: $.fn.dataTable.render.text(),
        responsivePriority: 3
    });
    if (BooklyL10n.add_columns.attachments) {
        columns.push({
            data: 'attachment',
            render: function (data, type, row, meta) {
                if (data == '1') {
                    return '<button type="button" class="btn btn-link bookly-js-attachment" title="' + BooklyL10n.attachments + '"><span class="dashicons dashicons-paperclip"></span></button>';
                }
                return '';
            },
            responsivePriority: 1
        });
    }

    var dt = $appointmentsList.DataTable({
        order: [[ 1, 'desc' ]],
        info: false,
        paging: false,
        searching: false,
        processing: true,
        responsive: true,
        serverSide: true,
        drawCallback: function( settings ) {
            $('[data-toggle="popover"]').on('click', function (e) {
                e.preventDefault();
            }).popover();
        },
        ajax: {
            url : ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookly_get_appointments', csrf_token : BooklyL10n.csrf_token}, {
                    filter: {
                        id          : $idFilter.val(),
                        date        : $appointmentDateFilter.data('date'),
                        created_date: $creationDateFilter.data('date'),
                        staff       : $staffFilter.val(),
                        customer    : $customerFilter.val(),
                        service     : $serviceFilter.val(),
                        status      : $statusFilter.val()
                    }
                }, d);
            }
        },
        columns: columns.concat([
            {
                responsivePriority: 1,
                orderable: false,
                render: function ( data, type, row, meta ) {
                    return '<button type="button" class="btn btn-default bookly-js-edit"><i class="glyphicon glyphicon-edit"></i> ' + BooklyL10n.edit + '</a>';
                }
            },
            {
                responsivePriority: 1,
                orderable: false,
                render: function ( data, type, row, meta ) {
                    return '<input type="checkbox" value="' + row.ca_id + '" data-appointment="' + row.id + '" />';
                }
            }
        ]),
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
        }
    });

    /**
     * Add appointment.
     */
    $addButton.on('click', function () {
        showAppointmentDialog(
            null,
            null,
            moment(),
            function(event) {
                dt.ajax.reload();
            }
        )
    });

    /**
     * Edit appointment.
     */
    $appointmentsList
        .on('click', 'button.bookly-js-edit', function (e) {
            e.preventDefault();
            var data = dt.row($(this).closest('td')).data();
            showAppointmentDialog(
                data.id,
                null,
                null,
                function (event) {
                    dt.ajax.reload();
                }
            )
        });

    /**
     * Export.
     */
    $exportButton.on('click', function () {
        var columns = [];
        $exportDialog.find('input:checked').each(function () {
            columns.push(this.value);
        });
        var config = {
            autoPrint: false,
            fieldSeparator: $('#bookly-csv-delimiter').val(),
            exportOptions: {
                columns: columns
            },
            filename: 'Appointments'
        };
        $.fn.dataTable.ext.buttons.csvHtml5.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.csvHtml5, config));
    });

    /**
     * Print.
     */
    $printButton.on('click', function () {
        var columns = [];
        $printDialog.find('input:checked').each(function () {
            columns.push(this.value);
        });
        var config = {
            title: '',
            exportOptions: {
                columns: columns
            },
            customize: function (win) {
                win.document.firstChild.style.backgroundColor = '#fff';
                win.document.body.id = 'bookly-tbs';
                $(win.document.body).find('table').removeClass('collapsed');
            }
        };
        $.fn.dataTable.ext.buttons.print.action(null, dt, null, $.extend({}, $.fn.dataTable.ext.buttons.print, config));
    });

    /**
     * Select all appointments.
     */
    $checkAllButton.on('change', function () {
        $appointmentsList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On appointment select.
     */
    $appointmentsList.on('change', 'tbody input:checkbox', function () {
        $checkAllButton.prop('checked', $appointmentsList.find('tbody input:not(:checked)').length == 0);
    });

    /**
     * Delete appointments.
     */
    $deleteButton.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();

        var data = [];
        var $checkboxes = $appointmentsList.find('tbody input:checked');
        $checkboxes.each(function () {
            data.push({ca_id: this.value, id: $(this).data('appointment')});
        });

        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action     : 'bookly_delete_customer_appointments',
                csrf_token : BooklyL10n.csrf_token,
                data       : data,
                notify     : $('#bookly-delete-notify').prop('checked') ? 1 : 0,
                reason     : $('#bookly-delete-reason').val()
            },
            dataType : 'json',
            success  : function(response) {
                ladda.stop();
                $('#bookly-delete-dialog').modal('hide');
                if (response.success) {
                    dt.draw(false);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    /**
     * Init date range pickers.
     */
    moment.locale('en', {
        months       : BooklyL10n.calendar.longMonths,
        monthsShort  : BooklyL10n.calendar.shortMonths,
        weekdays     : BooklyL10n.calendar.longDays,
        weekdaysShort: BooklyL10n.calendar.shortDays,
        weekdaysMin  : BooklyL10n.calendar.shortDays
    });

    var
        pickerRanges1 = {},
        pickerRanges2 = {}
    ;
    pickerRanges1[BooklyL10n.any_time]   = [moment(), moment().add(100, 'years')];
    pickerRanges1[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    pickerRanges1[BooklyL10n.today]      = [moment(), moment()];
    pickerRanges1[BooklyL10n.tomorrow]   = [moment().add(1, 'days'), moment().add(1, 'days')];
    pickerRanges1[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
    pickerRanges1[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
    pickerRanges1[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
    pickerRanges1[BooklyL10n.next_month] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
    $.extend(pickerRanges2, pickerRanges1);
    if (BooklyL10n.tasks.enabled) {
        pickerRanges1[BooklyL10n.tasks.title] = [moment(), moment().add(1, 'days')];
    }

    $appointmentDateFilter.daterangepicker(
        {
            parentEl : $appointmentDateFilter.parent(),
            startDate: pickers.appointmentDate.startDate,
            endDate  : pickers.appointmentDate.endDate,
            ranges   : pickerRanges1,
            autoUpdateInput: false,
            locale: {
                applyLabel : BooklyL10n.apply,
                cancelLabel: BooklyL10n.cancel,
                fromLabel  : BooklyL10n.from,
                toLabel    : BooklyL10n.to,
                customRangeLabel: BooklyL10n.custom_range,
                daysOfWeek : BooklyL10n.calendar.shortDays,
                monthNames : BooklyL10n.calendar.longMonths,
                firstDay   : parseInt(BooklyL10n.startOfWeek),
                format     : BooklyL10n.mjsDateFormat
            }
        },
        function(start, end, label) {
            switch (label) {
                case BooklyL10n.tasks.title:
                    $appointmentDateFilter
                        .data('date', 'null')
                        .find('span')
                        .html(BooklyL10n.tasks.title);
                    break;
                case BooklyL10n.any_time:
                    $appointmentDateFilter
                        .data('date', 'any')
                        .find('span')
                        .html(BooklyL10n.any_time);
                    break;
                default:
                    $appointmentDateFilter
                        .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                        .find('span')
                        .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        }
    );

    $creationDateFilter.daterangepicker(
        {
            parentEl : $creationDateFilter.parent(),
            startDate: pickers.creationDate.startDate,
            endDate  : pickers.creationDate.endDate,
            ranges: pickerRanges2,
            autoUpdateInput: false,
            locale: {
                applyLabel : BooklyL10n.apply,
                cancelLabel: BooklyL10n.cancel,
                fromLabel  : BooklyL10n.from,
                toLabel    : BooklyL10n.to,
                customRangeLabel: BooklyL10n.custom_range,
                daysOfWeek : BooklyL10n.calendar.shortDays,
                monthNames : BooklyL10n.calendar.longMonths,
                firstDay   : parseInt(BooklyL10n.startOfWeek),
                format     : BooklyL10n.mjsDateFormat
            }
        },
        function(start, end, label) {
            switch (label) {
                case BooklyL10n.tasks.title:
                    $creationDateFilter
                        .data('date', 'null')
                        .find('span')
                        .html(BooklyL10n.tasks.title);
                    break;
                case BooklyL10n.any_time:
                    $creationDateFilter
                        .data('date', 'any')
                        .find('span')
                        .html(BooklyL10n.createdAtAnyTime);
                    break;
                default:
                    $creationDateFilter
                        .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                        .find('span')
                        .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            }
        }
    );

    /**
     * On filters change.
     */
    $('.bookly-js-select')
        .on('select2:unselecting', function(e) {
            e.preventDefault();
            $(this).val(null).trigger('change');
        })
        .select2({
            width: '100%',
            theme: 'bootstrap',
            allowClear: true,
            language  : {
                noResults: function() { return BooklyL10n.no_result_found; }
            }
        });

    $idFilter.on('keyup', function () { dt.ajax.reload(); });
    $appointmentDateFilter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $creationDateFilter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $staffFilter.on('change', function () { dt.ajax.reload(); });
    $customerFilter.on('change', function () { dt.ajax.reload(); });
    $serviceFilter.on('change', function () { dt.ajax.reload(); });
    $statusFilter.on('change', function () { dt.ajax.reload(); });
});