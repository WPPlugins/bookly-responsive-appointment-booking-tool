jQuery(function ($) {
    'use strict';

    var $container  = $('.bookly-js-dashboard-appointments'),
        $dateFilter = $('select#bookly-filter-date', $container),
        totals  = {
            $approved: $('.bookly-js-approved', $container),
            $pending : $('.bookly-js-pending',  $container),
            $total   : $('.bookly-js-total',    $container),
            $revenue : $('.bookly-js-revenue',  $container),
        },
        href    = {
            $approved: $('.bookly-js-href-approved', $container),
            $pending : $('.bookly-js-href-pending',  $container),
            $total   : $('.bookly-js-href-total',    $container),
            $revenue : $('.bookly-js-href-revenue',  $container),
        },
        revenue = {
            label: BooklyAppointmentsWidgetL10n.revenue,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            fill: true,
            data: [],
            yAxisID: 'y-axis-1',
        },
        total   = {
            label: BooklyAppointmentsWidgetL10n.appointments,
            borderColor: 'rgb(201, 203, 207)',
            backgroundColor: 'rgba(201, 203, 207, 0.5)',
            fill: true,
            data: [],
            yAxisID: 'y-axis-2'
        };

    var chart = Chart.Line(document.getElementById('canvas').getContext('2d'), {
        data: {
            labels: [],
            datasets: [revenue, total]
        },
        options: {
            responsive: true,
            hoverMode : 'index',
            stacked   : false,
            title     : {
                display: false,
            },
            elements: {
                line: {
                    tension: 0.01
                }
            },
            scales: {
                yAxes: [{
                    type: 'linear',
                    display: true,
                    position: 'left',
                    id: 'y-axis-1',
                    scaleLabel: {
                        labelString: BooklyAppointmentsWidgetL10n.revenue + ' ('+ BooklyAppointmentsWidgetL10n.currency +')',
                        display: true,
                    }
                }, {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    id: 'y-axis-2',
                    scaleLabel: {
                        labelString: BooklyAppointmentsWidgetL10n.appointments,
                        display: true,
                    }
                }],
            },
            legend: {
                position: 'bottom',
            },
        }
    });

    $(document.body).on('bookly.dateRange.changed', {},
        function (event, data) {
            $container.parent().loading(true);
            $.ajax({
                url     : ajaxurl,
                type    : 'POST',
                data    : {
                    action    : 'bookly_get_appointments_data_for_dashboard',
                    csrf_token: BooklyAppointmentsWidgetL10n.csrfToken,
                    range     : data
                },
                dataType: 'json',
                success : function (response) {
                    $container.parent().loading(false);
                    revenue.data = [];
                    total.data = [];
                    $.each(response.data.days,function (date, item) {
                        revenue.data.push(item.revenue);
                        total.data.push(item.total);
                    });
                    totals.$revenue.html(response.data.totals.revenue);
                    totals.$approved.html(response.data.totals.approved);
                    totals.$pending.html(response.data.totals.pending);
                    totals.$total.html(response.data.totals.total);

                    href.$revenue.attr('href', response.data.filters.revenue);
                    href.$approved.attr('href',response.data.filters.approved);
                    href.$pending.attr('href', response.data.filters.pending);
                    href.$total.attr('href',   response.data.filters.total);

                    chart.data.labels = response.data.labels;
                    chart.update();
                }
            });
        }
    );
    $dateFilter.on('change', function () {
        $(document.body).trigger('bookly.dateRange.changed', [$dateFilter.val()]);
    }).trigger('change');
});