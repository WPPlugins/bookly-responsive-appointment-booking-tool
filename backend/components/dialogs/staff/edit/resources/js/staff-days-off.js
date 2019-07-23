jQuery(function ($) {

    var DaysOff = function($container, options) {
        var obj  = this;
        jQuery.extend(obj.options, options);

        if (!$container.children().length) {
            $container.html('<div class="bookly-loading"></div>');
            $.ajax({
                url         : ajaxurl,
                data        : {action: 'bookly_staff_holidays', id: obj.options.staff_id, csrf_token: obj.options.csrf_token},
                xhrFields   : {withCredentials: true},
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    $container.html(response.data.html);
                    init($container, obj);
                }
            });
        } else {
            init($container, obj);
        }
    };

    function init($container, obj) {
        if ($container.data('init') != true) {
            var d = new Date();
            $('.bookly-js-holidays').jCal({
                day: new Date(d.getFullYear(), 0, 1),
                days: 1,
                showMonths: 12,
                scrollSpeed: 350,
                action: 'bookly_staff_holidays_update',
                csrf_token: obj.options.csrf_token,
                staff_id: obj.options.staff_id,
                events: obj.options.l10n.holidays,
                dayOffset: parseInt(obj.options.l10n.firstDay),
                loadingImg: obj.options.l10n.loading_img,
                dow: obj.options.l10n.days,
                ml: obj.options.l10n.months,
                we_are_not_working: obj.options.l10n.we_are_not_working,
                repeat: obj.options.l10n.repeat,
                close: obj.options.l10n.close
            });

            $('.bookly-js-jCalBtn', $container).on('click', function (e) {
                e.preventDefault();
                var trigger = $(this).data('trigger');
                $('.bookly-js-holidays', $container).find($(trigger)).trigger('click');
            });

            $container.data('init', true);
        }
    }

    DaysOff.prototype.options = {
        staff_id  : -1,
        csrf_token: '',
        l10n: {}
    };

    window.BooklyStaffDaysOff = DaysOff;
});