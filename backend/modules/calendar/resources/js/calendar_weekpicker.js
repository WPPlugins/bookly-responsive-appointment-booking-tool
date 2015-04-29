/**
 * Week calendar datepicker.
 */
function WeekPicker() {
    // Private functions and variables.
    var widget = {
        $container  : null,
        $picker     : null, // jQuery UI DatePicker.
        $output     : null, // Place to display week-start and week-end dates.
        $calendar   : null, // WeekCalendar instance.
        start_date  : null,
        end_date    : null,
        first_day   : null,
        date_format : null,
        /**
         * Set start and end dates based on Date object.
         * @param Date date.
         */
        updateStartAndEndDates : function(date) {
            var seconds_in_one_day = 86400000;
            var days_to_rewind     = date.getDay() <= 0 ? 7 - this.first_day : date.getDay() - this.first_day;
            var days_to_add        = 6;
            this.start_date        = new Date(date.valueOf() - days_to_rewind * seconds_in_one_day); //rewind to start day
            this.end_date          = new Date(this.start_date.valueOf() + days_to_add * seconds_in_one_day); //add 6 days to get last day
        },
        /**
         * Highlight all days in currently selected week.
         */
        highlightSelectedWeek : function() {
            window.setTimeout(function () {
                widget.$picker.find('.ui-datepicker-current-day a').addClass('ui-state-active')
            }, 1);
        },
        /**
         * Display start and end dates of the selected week.
         * @param Date start_date
         * @param Date end_date
         */
        outputFormattedDate : function() {
            // show formatted date values
            var formatted_start_date = jQuery.datepicker.formatDate(this.date_format, this.start_date, {monthNamesShort: BooklyL10n['shortMonths'], monthNames : BooklyL10n['longMonths']}),
                formatted_end_date   = jQuery.datepicker.formatDate(this.date_format, this.end_date, {monthNamesShort: BooklyL10n['shortMonths'], monthNames : BooklyL10n['longMonths']});
            this.$output.val(BooklyL10n['Week'] + formatted_start_date + ' - ' + formatted_end_date);
        },
        /**
         * Set specific date to the widget (and update $calendar if update_week_calendar is true).
         *
         * @param date
         * @param update_week_calendar
         */
        setDate : function(date, update_week_calendar) {
            this.updateStartAndEndDates(date);
            this.$picker.datepicker('setDate', date);
            this.outputFormattedDate();
            this.highlightSelectedWeek();
            if (this.$calendar && (update_week_calendar === undefined || update_week_calendar)) {
                this.$calendar.weekCalendar('gotoWeek', this.start_date);
            }
        },
        /**
         * Constructor.
         */
        init : function() {
            this.$container  = jQuery('div#week-calendar-picker');
            this.$picker     = this.$container.find('div.ab-week-picker');
            this.$output     = this.$container.find('input.ab-date-calendar');
            this.first_day   = this.$container.data('first_day');
            this.date_format = BooklyL10n['dateFormat'];
            // Init start and end dates.
            this.updateStartAndEndDates(new Date());
            // Init datepicker.
            this.$picker.datepicker({
                showOtherMonths   : true,
                selectOtherMonths : true,
                firstDay          : widget.first_day,
                monthNames        : BooklyL10n['longMonths'],
                monthNamesShort   : BooklyL10n['shortMonths'],
                dayNames          : BooklyL10n['longDays'],
                dayNamesMin       : BooklyL10n['shortDays'],
                dayNamesShort     : BooklyL10n['shortDays'],
                onSelect          : function(dateText, inst) {
                    widget.updateStartAndEndDates(widget.$picker.datepicker('getDate'));
                    widget.outputFormattedDate();
                    widget.highlightSelectedWeek();
                    if (widget.$calendar) {
                        widget.$calendar.weekCalendar('gotoWeek', widget.start_date);
                    }
                },
                beforeShowDay     : function(date) {
                    var cssClass = '';
                    if ((date >= widget.start_date || date.getDate() == widget.start_date.getDate()) && date <= widget.end_date) {
                        cssClass = 'ui-datepicker-current-day';
                    }
                    return [true, cssClass];
                },
                onChangeMonthYear : function(year, month, inst) {
                    widget.highlightSelectedWeek();
                }
            });
            this.highlightSelectedWeek();
            // Display start and end dates.
            this.outputFormattedDate();
            // Handle events.
            this.$container
                .on('mousemove', '.ui-datepicker-calendar tr', function() {
                    jQuery(this).find('td a').addClass('ui-state-hover');
                })
                .on('mouseleave', '.ui-datepicker-calendar tr', function() {
                    jQuery(this).find('td a').removeClass('ui-state-hover');
                });
            jQuery('body').click(function(e) {
                jQuery('.dropdown-menu:visible').removeClass('open').hide();
                if (widget.$picker.is(':visible')) {
                    widget.$picker.hide();
                }
            });
            this.$picker.click(function(e) {
                e.stopPropagation();
            });
            // open week picker
            this.$output.on('click', function(e) {
                widget.$picker.show();
                e.stopPropagation();
            });
            // do not close week picker when the previous or next arrow is clicked
            this.$container.find('.prev, .next').on('click', function(e) {
                e.stopPropagation();
            });
            // handle click on the "previous week" arrow
            this.$container.find('.prev').on('click', function() {
                var date = widget.$picker.datepicker('getDate');
                date.addDays(-7);
                widget.setDate(date);
            });
            // handle click on the "next week" arrow
            this.$container.find('.next').on('click', function() {
                var date = widget.$picker.datepicker('getDate');
                date.addDays(7);
                widget.setDate(date);
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
        setDate : function(date, update_week_calendar) {
            widget.setDate(date, update_week_calendar);
        },
        getStartDate : function() {
            return widget.start_date;
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