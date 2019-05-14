jQuery(function ($) {
    let $dateFilter = $('#bookly-filter-date'),
        pickerRanges = [];

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

    pickerRanges[BooklyL10n.datePicker.last_7]    = [moment().subtract(7, 'days'), moment()];
    pickerRanges[BooklyL10n.datePicker.last_30]   = [moment().subtract(30, 'days'), moment()];
    pickerRanges[BooklyL10n.datePicker.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    pickerRanges[BooklyL10n.datePicker.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $dateFilter.daterangepicker({
        parentEl : $dateFilter.parent(),
        startDate: moment().subtract(7, 'days'),
        endDate  : moment(),
        ranges   : pickerRanges,
        autoUpdateInput: false,
        locale: {
            applyLabel : BooklyL10n.datePicker.apply,
            cancelLabel: BooklyL10n.datePicker.cancel,
            fromLabel  : BooklyL10n.datePicker.from,
            toLabel    : BooklyL10n.datePicker.to,
            customRangeLabel: BooklyL10n.datePicker.customRange,
            daysOfWeek : BooklyL10n.calendar.shortDays,
            monthNames : BooklyL10n.calendar.longMonths,
            firstDay   : parseInt(BooklyL10n.datePicker.startOfWeek),
            format     : BooklyL10n.datePicker.mjsDateFormat
        }
    },
    function(start, end, label) {
        switch (label) {
            default:
                var format = 'YYYY-MM-DD';
                $dateFilter
                    .data('date', start.format(format) + ' - ' + end.format(format))
                    .find('span')
                    .html(start.format(BooklyL10n.datePicker.mjsDateFormat) + ' - ' + end.format(BooklyL10n.datePicker.mjsDateFormat));
        }
    } );

    $dateFilter.on('apply.daterangepicker', function () {
        $(document.body).trigger('bookly.dateRange.changed', [$dateFilter.data('date')]);
    }).trigger('apply.daterangepicker');
});