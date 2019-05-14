jQuery(function($) {

    var
        $payments_list    = $('#bookly-payments-list'),
        $check_all_button = $('#bookly-check-all'),
        $id_filter        = $('#bookly-filter-id'),
        $creationDateFilter = $('#bookly-filter-date'),
        $type_filter      = $('#bookly-filter-type'),
        $customer_filter  = $('#bookly-filter-customer'),
        $staff_filter     = $('#bookly-filter-staff'),
        $service_filter   = $('#bookly-filter-service'),
        $status_filter    = $('#bookly-filter-status'),
        $payment_total    = $('#bookly-payment-total'),
        $delete_button    = $('#bookly-delete'),
        $download_invoice = $('#bookly-download-invoices'),
        urlParts          = document.URL.split('#'),
        pickers = {
            dateFormat:     'YYYY-MM-DD',
            creationDate: {
                startDate: moment().subtract(30, 'days'),
                endDate  : moment(),
            },
        };

    if (urlParts.length > 1) {
        urlParts[1].split('&').forEach(function (part) {
            var params = part.split('=');
            if (params[0] == 'created-date') {
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
    }

    $('.bookly-js-select')
        .val(null)
        .on('select2:unselecting', function(e) {
            e.preventDefault();
            $(this).val(null).trigger('change');
        })
        .select2({
            allowClear: true,
            theme: 'bootstrap',
            language: {
                noResults: function() { return BooklyL10n.no_result_found; }
            }
        });

    /**
     * Init Columns.
     */
    var columns = [
        { data: 'id', responsivePriority: 9 },
        {
            data: 'created',
            responsivePriority: 8,
            render: function ( data, type, row, meta ) {
                return row.created_format;
            }
        },
        { data: 'type', responsivePriority: 7 },
        { data: 'customer', render: $.fn.dataTable.render.text(), responsivePriority: 6 },
        { data: 'provider', responsivePriority: 4 },
        { data: 'service', responsivePriority: 3 },
        {
            data: 'start_date',
            responsivePriority: 2,
            render: function ( data, type, row, meta ) {
                return row.start_date_format;
            }
        },
        { data: 'paid', responsivePriority: 1 },
        { data: 'status', responsivePriority: 3 },
        {
            responsivePriority: 1,
            orderable: false,
            searchable: false,
            render: function ( data, type, row, meta ) {
                var buttons = '';
                if (BooklyL10n.invoice.enabled) {
                    buttons += '<button type="button" class="btn btn-default bookly-margin-right-md" data-action="view-invoice" data-payment_id="' + row.id + '"><i class="dashicons dashicons-media-text"></i> ' + BooklyL10n.invoice.button + '</a>';
                }
                return buttons + '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#bookly-payment-details-modal" data-payment_id="' + row.id + '"><i class="glyphicon glyphicon-list-alt"></i> ' + BooklyL10n.details + '</a>';
            }
        },
        {
            responsivePriority: 1,
            orderable: false,
            searchable: false,
            render: function ( data, type, row, meta ) {
                return '<input type="checkbox" value="' + row.id + '">';
            }
        }
    ];

    /**
     * Init DataTables.
     */
    var dt = $payments_list.DataTable({
        order: [[ 0, 'asc' ]],
        paging: false,
        info: false,
        searching: false,
        processing: true,
        responsive: true,
        serverSide: false,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function ( d ) {
                return $.extend( {}, d, {
                    action: 'bookly_get_payments',
                    csrf_token: BooklyL10n.csrf_token,
                    filter: {
                        id      : $id_filter.val(),
                        created : $creationDateFilter.data('date'),
                        type    : $type_filter.val(),
                        customer: $customer_filter.val(),
                        staff   : $staff_filter.val(),
                        service : $service_filter.val(),
                        status  : $status_filter.val()
                    }
                } );
            },
            dataSrc: function (json) {
                $payment_total.html(json.total);

                return json.data;
            }
        },
        columns: columns,
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing: BooklyL10n.processing
        }
    });

    /**
     * Select all coupons.
     */
    $check_all_button.on('change', function () {
        $payments_list.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On coupon select.
     */
    $payments_list.on('change', 'tbody input:checkbox', function () {
        $check_all_button.prop('checked', $payments_list.find('tbody input:not(:checked)').length == 0);
    });

    /**
     * Init date range picker.
     */
    moment.locale('en', {
        months:        BooklyL10n.calendar.longMonths,
        monthsShort:   BooklyL10n.calendar.shortMonths,
        weekdays:      BooklyL10n.calendar.longDays,
        weekdaysShort: BooklyL10n.calendar.shortDays,
        weekdaysMin:   BooklyL10n.calendar.shortDays
    });

    var picker_ranges = {};
    picker_ranges[BooklyL10n.yesterday]  = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.today]      = [moment(), moment()];
    picker_ranges[BooklyL10n.last_7]     = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.last_30]    = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.this_month] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.last_month] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $creationDateFilter.daterangepicker(
        {
            parentEl: $creationDateFilter.parent(),
            startDate: pickers.creationDate.startDate,
            endDate  : pickers.creationDate.endDate,
            ranges: picker_ranges,
            locale: {
                applyLabel:  BooklyL10n.apply,
                cancelLabel: BooklyL10n.cancel,
                fromLabel:   BooklyL10n.from,
                toLabel:     BooklyL10n.to,
                customRangeLabel: BooklyL10n.custom_range,
                daysOfWeek:  BooklyL10n.calendar.shortDays,
                monthNames:  BooklyL10n.calendar.longMonths,
                firstDay:    parseInt(BooklyL10n.startOfWeek),
                format:      BooklyL10n.mjsDateFormat
            }
        },
        function(start, end) {
            $creationDateFilter
                .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                .find('span')
                .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
        }
    );

    $id_filter.on('keyup', function () { dt.ajax.reload(); });
    $creationDateFilter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $type_filter.on('change', function () { dt.ajax.reload(); });
    $customer_filter.on('change', function () { dt.ajax.reload(); });
    $staff_filter.on('change', function () { dt.ajax.reload(); });
    $service_filter.on('change', function () { dt.ajax.reload(); });
    $status_filter.on('change', function () { dt.ajax.reload(); });

    /**
     * Delete payments.
     */
    $delete_button.on('click', function () {
        if (confirm(BooklyL10n.are_you_sure)) {
            var ladda = Ladda.create(this);
            ladda.start();

            var data = [];
            var $checkboxes = $payments_list.find('tbody input:checked');
            $checkboxes.each(function () {
                data.push(this.value);
            });

            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action : 'bookly_delete_payments',
                    csrf_token : BooklyL10n.csrf_token,
                    data : data
                },
                dataType : 'json',
                success  : function(response) {
                    if (response.success) {
                        dt.rows($checkboxes.closest('td')).remove().draw();
                    } else {
                        alert(response.data.message);
                    }
                    ladda.stop();
                }
            });
        }
    });

    $payments_list.on('click', '[data-action=view-invoice]', function () {
        window.location = $download_invoice.data('action') + '&invoices=' + $(this).data('payment_id');
    });

    $download_invoice.on('click', function () {
        var invoices = [];
        $payments_list.find('tbody input:checked').each(function () {
            invoices.push(this.value);
        });
        if (invoices.length) {
            window.location = $(this).data('action') + '&invoices=' + invoices.join(',');
        }
    });
});

(function() {
    var module = angular.module('paymentDetails', ['paymentDetailsDialog']);
    module.controller('paymentDetailsCtrl', function($scope) {});
})();