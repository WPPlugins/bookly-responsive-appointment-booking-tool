jQuery(function($) {

    var
        $appointments_list = $('#bookly-appointments-list'),
        $check_all_button  = $('#bookly-check-all'),
        $id_filter         = $('#bookly-filter-id'),
        $date_filter       = $('#bookly-filter-date'),
        $staff_filter      = $('#bookly-filter-staff'),
        $customer_filter   = $('#bookly-filter-customer'),
        $service_filter    = $('#bookly-filter-service'),
        $status_filter     = $('#bookly-filter-status'),
        $add_button        = $('#bookly-add'),
        $print_dialog      = $('#bookly-print-dialog'),
        $print_button      = $('#bookly-print'),
        $export_dialog     = $('#bookly-export-dialog'),
        $export_button     = $('#bookly-export'),
        $delete_button     = $('#bookly-delete'),
        isMobile           = false,
        urlParts           = document.URL.split('#')
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
            if (params[0] == 'range') {
                var format = 'YYYY-MM-DD',
                    start  = moment(params['1'].substring(0, 10)),
                    end    = moment(params['1'].substring(11));
                $date_filter
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
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
        { data: 'customer.email', render: $.fn.dataTable.render.text(), responsivePriority: 5 }
    ];
    if (BooklyL10n.add_columns.number_of_persons) {
        columns.push({
            data: 'number_of_persons',
            render: $.fn.dataTable.render.text(),
            responsivePriority: 4
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
        { data: 'service.duration', responsivePriority: 2 },
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
            responsivePriority: 2
        });
    }

    if (BooklyL10n.add_columns.notes) {
        columns.push({
            data: 'notes',
            render: $.fn.dataTable.render.text(),
            responsivePriority: 4
        });
    }
    $.each(BooklyL10n.cf_columns, function (i, cf_id) {
        columns.push({
            data: 'custom_fields.' + cf_id,
            render: $.fn.dataTable.render.text(),
            responsivePriority: 4,
            orderable: false
        });
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

    var dt = $appointments_list.DataTable({
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
                        id      : $id_filter.val(),
                        date    : $date_filter.data('date'),
                        staff   : $staff_filter.val(),
                        customer: $customer_filter.val(),
                        service : $service_filter.val(),
                        status  : $status_filter.val()
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
                    return '<input type="checkbox" value="' + row.ca_id + '" />';
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
    $add_button.on('click', function () {
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
    $appointments_list
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
    $export_button.on('click', function () {
        var columns = [];
        $export_dialog.find('input:checked').each(function () {
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
    $print_button.on('click', function () {
        var columns = [];
        $print_dialog.find('input:checked').each(function () {
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
    $check_all_button.on('change', function () {
        $appointments_list.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On appointment select.
     */
    $appointments_list.on('change', 'tbody input:checkbox', function () {
        $check_all_button.prop('checked', $appointments_list.find('tbody input:not(:checked)').length == 0);
    });

    /**
     * Delete appointments.
     */
    $delete_button.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();

        var data = [];
        var $checkboxes = $appointments_list.find('tbody input:checked');
        $checkboxes.each(function () {
            data.push(this.value);
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
     * Init date range picker.
     */
    moment.locale('en', {
        months       : BooklyL10n.calendar.longMonths,
        monthsShort  : BooklyL10n.calendar.shortMonths,
        weekdays     : BooklyL10n.calendar.longDays,
        weekdaysShort: BooklyL10n.calendar.shortDays,
        weekdaysMin  : BooklyL10n.calendar.shortDays
    });

    var picker_ranges = {};
    picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.today]      = [moment(), moment()];
    picker_ranges[BooklyL10n.tomorrow]   = [moment().add(1, 'days'), moment().add(1, 'days')];
    picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.next_month] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];
    if (BooklyL10n.tasks.enabled) {
        picker_ranges[BooklyL10n.tasks.title] = [moment(), moment().add(1, 'days')];
    }

    $date_filter.daterangepicker(
        {
            parentEl: $date_filter.parent(),
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            ranges: picker_ranges,
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
            if (label != BooklyL10n.tasks.title) {
                var format = 'YYYY-MM-DD';
                $date_filter
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
            } else {
                $date_filter
                    .data('date', null)
                    .find('span')
                    .html(BooklyL10n.tasks.title);
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

    $id_filter.on('keyup', function () { dt.ajax.reload(); });
    $date_filter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $staff_filter.on('change', function () { dt.ajax.reload(); });
    $customer_filter.on('change', function () { dt.ajax.reload(); });
    $service_filter.on('change', function () { dt.ajax.reload(); });
    $status_filter.on('change', function () { dt.ajax.reload(); });
});