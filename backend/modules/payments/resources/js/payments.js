jQuery(function($) {

    var
        $payments_list    = $('#bookly-payments-list'),
        $check_all_button = $('#bookly-check-all'),
        $date_filter      = $('#bookly-filter-date'),
        $type_filter      = $('#bookly-filter-type'),
        $staff_filter     = $('#bookly-filter-staff'),
        $service_filter   = $('#bookly-filter-service'),
        $payment_total    = $('#bookly-payment-total'),
        $delete_button    = $('#bookly-delete')
        ;

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
        serverSide: true,
        ajax: {
            url: ajaxurl,
            data: function ( d ) {
                return $.extend( {}, d, {
                    action: 'bookly_get_payments',
                    filter: {
                        created: $date_filter.data('date'),
                        type:    $type_filter.val(),
                        staff:   $staff_filter.val(),
                        service: $service_filter.val()
                    }
                } );
            },
            dataSrc: function (json) {
                $payment_total.html(json.total);

                return json.data;
            }
        },
        columns: [
            { data: 'created' },
            { data: 'type' },
            { data: 'customer', render: $.fn.dataTable.render.text() },
            { data: 'provider' },
            { data: 'service' },
            { data: 'start_date' },
            { data: 'paid' },
            { data: 'status' },
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function ( data, type, row, meta ) {
                    return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#bookly-payment-details-modal" data-payment_id="' + row.id + '"><i class="glyphicon glyphicon-list-alt"></i> ' + BooklyL10n.details + '</a>';
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
        ],
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
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

    $date_filter.daterangepicker(
        {
            parentEl: $date_filter.parent(),
            startDate: moment().subtract(30, 'days'), // by default selected is "Last 30 days"
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
            var format = 'YYYY-MM-DD';
            $date_filter
                .data('date', start.format(format) + ' - ' + end.format(format))
                .find('span')
                .html(start.format(BooklyL10n.mjsDateFormat) + ' - ' + end.format(BooklyL10n.mjsDateFormat));
        }
    );

    /**
     * On filters change.
     */
    $('.bookly-js-chosen-select').chosen({
        allow_single_deselect: true,
        disable_search_threshold: 10
    });
    $date_filter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $type_filter.on('change', function () { dt.ajax.reload(); });
    $staff_filter.on('change', function () { dt.ajax.reload(); });
    $service_filter.on('change', function () { dt.ajax.reload(); });

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
                    data   : data
                },
                dataType : 'json',
                success  : function(response) {
                    ladda.stop();
                    if (response.success) {
                        dt.rows($checkboxes.closest('td')).remove().draw();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }
    });
});

(function() {
    var module = angular.module('paymentDetails', ['paymentDetailsDialog']);
    module.controller('paymentDetailsCtrl', function($scope) {});
})();