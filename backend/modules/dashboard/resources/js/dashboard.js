jQuery(function ($) {
    let $dateFilter = $('#bookly-filter-date'),
        pickerRanges = [];

    /**
     * Init date range pickers.
     */
    moment.locale('en', {
        months       : BooklyL10n.datePicker.monthNames,
        monthsShort  : BooklyL10n.datePicker.monthNamesShort,
        weekdays     : BooklyL10n.datePicker.dayNames,
        weekdaysShort: BooklyL10n.datePicker.dayNamesShort,
        weekdaysMin  : BooklyL10n.datePicker.dayNamesMin
    });

    pickerRanges[BooklyL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    pickerRanges[BooklyL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    pickerRanges[BooklyL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    pickerRanges[BooklyL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $dateFilter.daterangepicker({
        parentEl : $dateFilter.parent(),
        startDate: moment().subtract(7, 'days'),
        endDate  : moment(),
        ranges   : pickerRanges,
        autoUpdateInput: false,
        locale: {
            applyLabel : BooklyL10n.dateRange.apply,
            cancelLabel: BooklyL10n.dateRange.cancel,
            fromLabel  : BooklyL10n.dateRange.from,
            toLabel    : BooklyL10n.dateRange.to,
            customRangeLabel: BooklyL10n.dateRange.customRange,
            daysOfWeek : BooklyL10n.datePicker.dayNamesShort,
            monthNames : BooklyL10n.datePicker.dayNames,
            firstDay   : parseInt(BooklyL10n.dateRange.firstDay),
            format     : BooklyL10n.dateRange.dateFormat
        }
    },
    function(start, end, label) {
        switch (label) {
            default:
                var format = 'YYYY-MM-DD';
                $dateFilter
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.dateRange.dateFormat) + ' - ' + end.format(BooklyL10n.dateRange.dateFormat));
        }
    } );

    $dateFilter.on('apply.daterangepicker', function () {
        $(document.body).trigger('bookly.dateRange.changed', [$dateFilter.data('date')]);
    }).trigger('apply.daterangepicker');
});