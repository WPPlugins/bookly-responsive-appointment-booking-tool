/**
 * Day calendar datepicker.
 */
function DayPicker() {
    // Private functions and variables.
    var widget = {
        $container         : null,
        $month_days        : null,
        $prev_date_handler : null,
        $next_date_handler : null,
        $picker            : null, // jQuery UI DatePicker.
        $calendar          : null, // WeekCalendar instance.
        first_day          : null,
        rebuildDayLine     : function(date) {
            var timestamp      = +date / 1000,
                seconds_in_day = 60 * 60 * 24;
            for (var i = 1; i <= 7; ++ i) {
                var next_day_timestamp = (timestamp + (i * seconds_in_day)) * 1000,
                    next_day           = new Date(next_day_timestamp),
                    $day_of_month      = jQuery(this.$month_days.get(i - 1));
                $day_of_month
                    .text(next_day.getDate())
                    .data('date_timestamp', next_day_timestamp / 1000);
            }
        },
        /**
         * Constructor.
         */
        init : function() {
            this.$container         = jQuery('div#day-calendar-picker');
            this.$month_days        = this.$container.find('.ab-day-of-month');
            this.$prev_date_handler = this.$container.find('.ab-week-picker-arrow-prev');
            this.$next_date_handler = this.$container.find('.ab-week-picker-arrow-next');
            this.$picker            = this.$container.find('#appendedInput');
            this.first_day          = this.$container.data('first_day');
            // Init datepicker.
            this.$picker.datepicker({
                dateFormat        : BooklyL10n['dateFormat'],
                showOtherMonths   : true,
                selectOtherMonths : true,
                firstDay          : widget.first_day,
                monthNames        : BooklyL10n['longMonths'],
                monthNamesShort   : BooklyL10n['shortMonths'],
                dayNames          : BooklyL10n['longDays'],
                dayNamesMin       : BooklyL10n['shortDays'],
                dayNamesShort     : BooklyL10n['shortDays'],
                onSelect          : function() {
                    var date = widget.$picker.datepicker('getDate');
                    widget.rebuildDayLine(date);
                    if (widget.$calendar) {
                        widget.$calendar.weekCalendar('gotoWeek', date);
                    }
                    widget.$month_days.parent().removeClass('active');
                }
            });
            // Handle events.
            this.$month_days.on('click', function(e) {
                e.preventDefault();
                var $clicked          = jQuery(this),
                    $clicked_day_date = new Date(parseInt($clicked.data('date_timestamp'), 10) * 1000);
                widget.rebuildDayLine($clicked_day_date);
                widget.$picker.datepicker('setDate', $clicked_day_date);
                widget.$month_days.parent().removeClass('active');
                $clicked.parent().addClass('active');
                if (widget.$calendar) {
                    widget.$calendar.weekCalendar('gotoWeek', $clicked_day_date);
                }
            });
            this.$prev_date_handler.on('click', function() {
                var date = widget.$picker.datepicker('getDate');
                date.setDate(date.getDate() - 1);
                widget.rebuildDayLine(date);
                widget.$picker.datepicker('setDate', date);
                if (widget.$calendar) {
                    widget.$calendar.weekCalendar('gotoWeek', date);
                }
            });
            this.$next_date_handler.on('click', function() {
                var date = widget.$picker.datepicker('getDate');
                date.setDate(date.getDate() + 1);
                widget.rebuildDayLine(date);
                widget.$picker.datepicker('setDate', date);
                if (widget.$calendar) {
                    widget.$calendar.weekCalendar('gotoWeek', date);
                }
            });
        }
    };

    // Init.
    widget.init();

    // Return public methods.
    return {
        show : function() {
            widget.$container.show();
        },
        hide : function() {
            widget.$container.hide();
        },

        /**
         * Set specific date to the widget (and update $calendar if update_week_calendar is true).
         *
         * @param date
         * @param update_week_calendar
         */
        setDate : function(date, update_week_calendar) {
            widget.rebuildDayLine(date);
            widget.$picker.datepicker('setDate', date);
            if (widget.$calendar && (update_week_calendar === undefined || update_week_calendar)) {
                widget.$calendar.weekCalendar('gotoWeek', date);
            }
        },
        getDate : function() {
            return widget.$picker.datepicker('getDate');
        },
        /**
         * Attach WeekCalendar to the picker.
         * @param WeekCalendar $calendar
         */
        attachCalendar : function($calendar) {
            widget.$calendar = $calendar;
        }
    };
}