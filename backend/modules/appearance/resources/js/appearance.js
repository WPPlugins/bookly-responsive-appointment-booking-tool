jQuery(function($) {
    var // Progress Tracker.
        $progress_tracker_option = $('input#ab-progress-tracker-checkbox'),
        $staff_name_with_price_option = $('input#ab-staff-name-with-price-checkbox'),
        // Time slots setting.
        $blocked_timeslots_option = $('input#ab-blocked-timeslots-checkbox'),
        $day_one_column_option    = $('input#ab-day-one-column-checkbox'),
        $show_calendar_option     = $('input#ab-show-calendar-checkbox'),
        $required_employee_option = $('input#ab-required-employee-checkbox'),
        $required_location_option = $('input#ab-required-location-checkbox'),
        // Buttons.
        $save_button          = $('#ajax-send-appearance'),
        $reset_button         = $('button[type=reset]'),
        // Texts.
        $text_step_service    = $('#bookly_l10n_step_service'),
        $text_step_extras     = $('#bookly_l10n_step_extras'),
        $text_step_time       = $('#bookly_l10n_step_time'),
        $text_step_cart       = $('#bookly_l10n_step_cart'),
        $text_step_details    = $('#bookly_l10n_step_details'),
        $text_step_payment    = $('#bookly_l10n_step_payment'),
        $text_step_done       = $('#bookly_l10n_step_done'),
        $text_label_location  = $('#bookly_l10n_label_location'),
        $text_label_multiply  = $('#bookly_l10n_label_multiply'),
        $text_label_category  = $('#bookly_l10n_label_category'),
        $text_option_location = $('#bookly_l10n_option_location'),
        $text_option_category = $('#bookly_l10n_option_category'),
        $text_option_service  = $('#bookly_l10n_option_service'),
        $text_option_employee = $('#bookly_l10n_option_employee'),
        $text_label_service   = $('#bookly_l10n_label_service'),
        $text_label_number_of_persons = $('#bookly_l10n_label_number_of_persons'),
        $text_label_employee  = $('#bookly_l10n_label_employee'),
        $text_label_select_date = $('#bookly_l10n_label_select_date'),
        $text_label_start_from = $('#bookly_l10n_label_start_from'),
        $text_button_next     = $('#bookly_l10n_button_next'),
        $text_button_back     = $('#bookly_l10n_button_back'),
        $text_button_book_more = $('#bookly_l10n_button_book_more'),
        $text_button_apply    = $('#bookly_l10n_button_apply'),
        $text_label_finish_by = $('#bookly_l10n_label_finish_by'),
        $text_label_name      = $('#bookly_l10n_label_name'),
        $text_label_phone     = $('#bookly_l10n_label_phone'),
        $text_label_email     = $('#bookly_l10n_label_email'),
        $text_label_coupon    = $('#bookly_l10n_label_coupon'),
        $text_info_service    = $('#bookly_l10n_info_service_step'),
        $text_info_extras     = $('#bookly_l10n_info_extras_step'),
        $text_info_time       = $('#bookly_l10n_info_time_step'),
        $text_info_cart       = $('#bookly_l10n_info_cart_step'),
        $text_info_details    = $('#bookly_l10n_info_details_step'),
        $text_info_details_guest = $('#bookly_l10n_info_details_step_guest'),
        $text_info_coupon     = $('#bookly_l10n_info_coupon'),
        $text_info_payment    = $('#bookly_l10n_info_payment_step'),
        $text_info_complete   = $('#bookly_l10n_info_complete_step'),
        $text_label_pay_paypal = $('#bookly_l10n_label_pay_paypal'),
        $text_label_pay_ccard = $('#bookly_l10n_label_pay_ccard'),
        $text_label_ccard_number = $('#bookly_l10n_label_ccard_number'),
        $text_label_ccard_expire = $('#bookly_l10n_label_ccard_expire'),
        $text_label_ccard_code = $('#bookly_l10n_label_ccard_code'),
        $color_picker         = $('.bookly-js-color-picker'),
        $ab_editable          = $('.bookly-editable'),
        $text_label_pay_locally = $('#bookly_l10n_label_pay_locally'),
        $text_label_pay_mollie = $('#bookly_l10n_label_pay_mollie'),
        // Calendars.
        $second_step_calendar = $('.ab-selected-date'),
        $second_step_calendar_wrap = $('.ab-slot-calendar'),
        // Step settings.
        $step_settings        = $('#bookly-js-step-settings'),
        // Step repeat.
        $repeat_ui_step_calendar = $('.bookly-repeat-until'),
        $repeat_ui_variants   = $('[class^="bookly-variant"]'),
        $repeat_ui_variant    = $('.bookly-repeat-variant'),
        $repeat_ui_variant_monthly = $('.variant-monthly'),
        $repeat_ui_weekly_week_day = $('.bookly-week-day'),
        $repeat_ui_monthly_specific_day = $('.bookly-monthly-specific-day'),
        $repeat_ui_monthly_week_day = $('.bookly-monthly-week-day'),
        // Step repeat l10n
        $text_info_repeat_step = $('#bookly_l10n_info_repeat_step'),
        $text_label_repeat    = $('#bookly_l10n_label_repeat'),
        $text_repeat          = $('#bookly_l10n_repeat'),
        $text_repeat_another_time = $('#bookly_l10n_repeat_another_time'),
        $text_repeat_biweekly = $('#bookly_l10n_repeat_biweekly'),
        $text_repeat_daily    = $('#bookly_l10n_repeat_daily'),
        $text_repeat_day      = $('#bookly_l10n_repeat_day'),
        $text_repeat_days     = $('#bookly_l10n_repeat_days'),
        $text_repeat_deleted  = $('#bookly_l10n_repeat_deleted'),
        $text_repeat_every    = $('#bookly_l10n_repeat_every'),
        $text_repeat_first    = $('#bookly_l10n_repeat_first'),
        $text_repeat_first_in_cart_info = $('#bookly_l10n_repeat_first_in_cart_info').first(),
        $text_repeat_fourth   = $('#bookly_l10n_repeat_fourth'),
        $text_repeat_last     = $('#bookly_l10n_repeat_last'),
        $text_repeat_monthly  = $('#bookly_l10n_repeat_monthly'),
        $text_repeat_on       = $('#bookly_l10n_repeat_on'),
        $text_repeat_on_week  = $('#bookly_l10n_repeat_on_week'),
        $text_repeat_required_week_days = $('#bookly_l10n_repeat_required_week_days'),
        $text_repeat_schedule = $('#bookly_l10n_repeat_schedule'),
        $text_repeat_schedule_help = $('#bookly_l10n_repeat_schedule_help'),
        $text_repeat_schedule_info = $('#bookly_l10n_repeat_schedule_info'),
        $text_repeat_second   = $('#bookly_l10n_repeat_second'),
        $text_repeat_specific = $('#bookly_l10n_repeat_specific'),
        $text_repeat_third    = $('#bookly_l10n_repeat_third'),
        $text_repeat_this_appointment = $('#bookly_l10n_repeat_this_appointment'),
        $text_repeat_until    = $('#bookly_l10n_repeat_until'),
        $text_repeat_weekly   = $('#bookly_l10n_repeat_weekly'),
        $text_step_repeat     = $('#bookly_l10n_step_repeat')
    ;

    if (BooklyL10n.intlTelInput.enabled) {
        $('.ab-user-phone').intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            defaultCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get(ajaxurl, {action: 'bookly_ip_info'}, function () {
                }, 'json').always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: BooklyL10n.intlTelInput.utils
        });
    }

    $staff_name_with_price_option.on('change', function () {
        var staff = $('.ab-select-employee').val();
        if (staff) {
            $('.ab-select-employee').val(staff * -1);
        }
        $('.employee-name-price').toggle($staff_name_with_price_option.prop("checked"));
        $('.employee-name').toggle(!$staff_name_with_price_option.prop("checked"));
    }).trigger('change');

    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    // Tabs.
    $('li.bookly-nav-item').on('shown.bs.tab', function (e) {
        $step_settings.children().hide();
        switch (e.target.getAttribute('data-target')) {
            case '#ab-step-1': $step_settings.find('#bookly-js-step-service').show(); break;
            case '#ab-step-3': $step_settings.find('#bookly-js-step-time').show(); break;
        }
    });

    function getEditableValue(val) {
        return $.trim(val == 'Empty' ? '' : val);
    }
    // Apply color from color picker.
    var applyColor = function() {
        var color_important = $color_picker.wpColorPicker('color') + '!important';
        $('.ab-progress-tracker').find('.active').css('color', $color_picker.wpColorPicker('color')).find('.step').css('background', $color_picker.wpColorPicker('color'));
        $('.ab-mobile-step_1 label').css('color', $color_picker.wpColorPicker('color'));
        $('.ab-label-error').css('color', $color_picker.wpColorPicker('color'));
        $('.bookly-js-actions > a').css('background-color', $color_picker.wpColorPicker('color'));
        $('.bookly-next-step, .ab-mobile-next-step').css('background', $color_picker.wpColorPicker('color'));
        $('.bookly-week-days label').css('background-color', $color_picker.wpColorPicker('color'));
        $('.picker__frame').attr('style', 'background: ' + color_important);
        $('.picker__header').attr('style', 'border-bottom: ' + '1px solid ' + color_important);
        $('.picker__day').mouseenter(function(){
            $(this).attr('style', 'color: ' + color_important);
        }).mouseleave(function(){ $(this).attr('style', $(this).hasClass('picker__day--selected') ? 'color: ' + color_important : '') });
        $('.picker__day--selected').attr('style', 'color: ' + color_important);
        $('.picker__button--clear').attr('style', 'color: ' + color_important);
        $('.picker__button--today').attr('style', 'color: ' + color_important);
        $('.bookly-extra-step .bookly-extras-thumb.bookly-extras-selected').css('border-color', $color_picker.wpColorPicker('color'));
        $('.ab-columnizer .ab-day, .bookly-schedule-date,.bookly-pagination li.active').css({
            'background': $color_picker.wpColorPicker('color'),
            'border-color': $color_picker.wpColorPicker('color')
        });
        $('.ab-columnizer .ab-hour').off().hover(
            function() { // mouse-on
                $(this).css({
                    'color': $color_picker.wpColorPicker('color'),
                    'border': '2px solid ' + $color_picker.wpColorPicker('color')
                });
                $(this).find('.ab-hour-icon').css({
                    'border-color': $color_picker.wpColorPicker('color'),
                    'color': $color_picker.wpColorPicker('color')
                });
                $(this).find('.ab-hour-icon > span').css({
                    'background': $color_picker.wpColorPicker('color')
                });
            },
            function() { // mouse-out
                $(this).css({
                    'color': '#333333',
                    'border': '1px solid #cccccc'
                });
                $(this).find('.ab-hour-icon').css({
                    'border-color': '#333333',
                    'color': '#cccccc'
                });
                $(this).find('.ab-hour-icon > span').css({
                    'background': '#cccccc'
                });
            }
        );
        $('.ab-details-step label').css('color', $color_picker.wpColorPicker('color'));
        $('.ab-card-form label').css('color', $color_picker.wpColorPicker('color'));
        $('.ab-nav-tabs .ladda-button, .bookly-nav-steps .ladda-button, .ab-btn, .bookly-round, .bookly-square').css('background-color', $color_picker.wpColorPicker('color'));
        $('.bookly-triangle').css('border-bottom-color', $color_picker.wpColorPicker('color'));
        $('.bookly-back-step, .bookly-next-step').css('background', $color_picker.wpColorPicker('color'));
        var style_arrow = '.picker__nav--next:before { border-left: 6px solid ' + $color_picker.wpColorPicker('color') + '!important; } .picker__nav--prev:before { border-right: 6px solid ' + $color_picker.wpColorPicker('color') + '!important; }';
        $('#ab--style-arrow').html(style_arrow);
    };
    $color_picker.wpColorPicker({
        change : applyColor
    });
    // Init calendars.
    $('.ab-date-from').pickadate({
        formatSubmit   : 'yyyy-mm-dd',
        format         : BooklyL10n.date_format,
        min            : true,
        clear          : false,
        close          : false,
        today          : BooklyL10n.today,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        onRender       : applyColor,
        firstDay       : BooklyL10n.start_of_week == 1
    });

    $second_step_calendar.pickadate({
        formatSubmit   : 'yyyy-mm-dd',
        format         : BooklyL10n.date_format,
        min            : true,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        close          : false,
        clear          : false,
        today          : false,
        closeOnSelect  : false,
        onRender       : applyColor,
        firstDay       : BooklyL10n.start_of_week == 1,
        klass : {
            picker: 'picker picker--opened picker--focused'
        },
        onClose : function() {
            this.open(false);
        }
    });
    $second_step_calendar_wrap.find('.picker__holder').css({ top : '0px', left : '0px' });
    $second_step_calendar_wrap.toggle($show_calendar_option.prop('checked'));

    // Update options.
    $save_button.on('click', function(e) {
        e.preventDefault();
        var data = {
            action: 'bookly_update_appearance_options',
            options: {
                // Color.
                'color'                        : $color_picker.wpColorPicker('color'),
                // Info text.
                'text_info_service_step'       : getEditableValue($text_info_service.text()),
                'text_info_extras_step'        : getEditableValue($text_info_extras.text()),
                'text_info_time_step'          : getEditableValue($text_info_time.text()),
                'text_info_cart_step'          : getEditableValue($text_info_cart.text()),
                'text_info_details_step'       : getEditableValue($text_info_details.text()),
                'text_info_details_step_guest' : getEditableValue($text_info_details_guest.text()),
                'text_info_payment_step'       : getEditableValue($text_info_payment.text()),
                'text_info_complete_step'      : getEditableValue($text_info_complete.text()),
                'text_info_coupon'             : getEditableValue($text_info_coupon.text()),
                // Step and label texts.
                'text_step_service'            : getEditableValue($text_step_service.text()),
                'text_step_extras'             : getEditableValue($text_step_extras.text()),
                'text_step_time'               : getEditableValue($text_step_time.text()),
                'text_step_cart'               : getEditableValue($text_step_cart.text()),
                'text_step_details'            : getEditableValue($text_step_details.text()),
                'text_step_payment'            : getEditableValue($text_step_payment.text()),
                'text_step_done'               : getEditableValue($text_step_done.text()),
                'text_label_location'          : getEditableValue($text_label_location.text()),
                'text_label_category'          : getEditableValue($text_label_category.text()),
                'text_label_service'           : getEditableValue($text_label_service.text()),
                'text_label_number_of_persons' : getEditableValue($text_label_number_of_persons.text()),
                'text_label_multiply'          : getEditableValue($text_label_multiply.text()),
                'text_label_employee'          : getEditableValue($text_label_employee.text()),
                'text_label_select_date'       : getEditableValue($text_label_select_date.text()),
                'text_label_start_from'        : getEditableValue($text_label_start_from.text()),
                'text_button_next'             : getEditableValue($text_button_next.text()),
                'text_button_back'             : getEditableValue($text_button_back.text()),
                'text_button_apply'            : getEditableValue($text_button_apply.text()),
                'text_button_book_more'        : getEditableValue($text_button_book_more.text()),
                'text_label_finish_by'         : getEditableValue($text_label_finish_by.text()),
                'text_label_name'              : getEditableValue($text_label_name.text()),
                'text_label_phone'             : getEditableValue($text_label_phone.text()),
                'text_label_email'             : getEditableValue($text_label_email.text()),
                'text_label_coupon'            : getEditableValue($text_label_coupon.text()),
                'text_option_location'         : getEditableValue($text_option_location.text()),
                'text_option_category'         : getEditableValue($text_option_category.text()),
                'text_option_service'          : getEditableValue($text_option_service.text()),
                'text_option_employee'         : getEditableValue($text_option_employee.text()),
                'text_label_pay_locally'       : getEditableValue($text_label_pay_locally.text()),
                'text_label_pay_mollie'        : getEditableValue($text_label_pay_mollie.text()),
                'text_label_pay_paypal'        : getEditableValue($text_label_pay_paypal.text()),
                'text_label_pay_ccard'         : getEditableValue($text_label_pay_ccard.text()),
                'text_label_ccard_number'      : getEditableValue($text_label_ccard_number.text()),
                'text_label_ccard_expire'      : getEditableValue($text_label_ccard_expire.text()),
                'text_label_ccard_code'        : getEditableValue($text_label_ccard_code.text()),
                // Repeat
                'text_info_repeat_step'        : getEditableValue($text_info_repeat_step.text()),
                'text_label_repeat'            : getEditableValue($text_label_repeat.text()),
                'text_repeat'                  : getEditableValue($text_repeat.text()),
                'text_repeat_another_time'     : getEditableValue($text_repeat_another_time.text()),
                'text_repeat_biweekly'         : getEditableValue($text_repeat_biweekly.text()),
                'text_repeat_daily'            : getEditableValue($text_repeat_daily.text()),
                'text_repeat_day'              : getEditableValue($text_repeat_day.text()),
                'text_repeat_days'             : getEditableValue($text_repeat_days.text()),
                'text_repeat_deleted'          : getEditableValue($text_repeat_deleted.text()),
                'text_repeat_every'            : getEditableValue($text_repeat_every.text()),
                'text_repeat_first'            : getEditableValue($text_repeat_first.text()),
                'text_repeat_first_in_cart_info' : getEditableValue($text_repeat_first_in_cart_info.text()),
                'text_repeat_fourth'           : getEditableValue($text_repeat_fourth.text()),
                'text_repeat_last'             : getEditableValue($text_repeat_last.text()),
                'text_repeat_monthly'          : getEditableValue($text_repeat_monthly.text()),
                'text_repeat_on'               : getEditableValue($text_repeat_on.text()),
                'text_repeat_on_week'          : getEditableValue($text_repeat_on_week.text()),
                'text_repeat_required_week_days' : getEditableValue($text_repeat_required_week_days.text()),
                'text_repeat_schedule'         : getEditableValue($text_repeat_schedule.text()),
                'text_repeat_schedule_help'    : getEditableValue($text_repeat_schedule_help.text()),
                'text_repeat_schedule_info'    : getEditableValue($text_repeat_schedule_info.text()),
                'text_repeat_second'           : getEditableValue($text_repeat_second.text()),
                'text_repeat_specific'         : getEditableValue($text_repeat_specific.text()),
                'text_repeat_third'            : getEditableValue($text_repeat_third.text()),
                'text_repeat_this_appointment' : getEditableValue($text_repeat_this_appointment.text()),
                'text_repeat_until'            : getEditableValue($text_repeat_until.text()),
                'text_repeat_weekly'           : getEditableValue($text_repeat_weekly.text()),
                'text_step_repeat'             : getEditableValue($text_step_repeat.text()),
                // Validator.
                'text_required_location'       : getEditableValue($('#bookly_l10n_required_location').html()),
                'text_required_service'        : getEditableValue($('#bookly_l10n_required_service').html()),
                'text_required_employee'       : getEditableValue($('#bookly_l10n_required_employee').html()),
                'text_required_name'           : getEditableValue($('#bookly_l10n_required_name').html()),
                'text_required_phone'          : getEditableValue($('#bookly_l10n_required_phone').html()),
                'text_required_email'          : getEditableValue($('#bookly_l10n_required_email').html()),
                // Checkboxes.
                'progress_tracker'  : Number($progress_tracker_option.prop('checked')),
                'staff_name_with_price': Number($staff_name_with_price_option.prop('checked')),
                'blocked_timeslots' : Number($blocked_timeslots_option.prop('checked')),
                'day_one_column'    : Number($day_one_column_option.prop('checked')),
                'show_calendar'     : Number($show_calendar_option.prop('checked')),
                'required_employee' : Number($required_employee_option.prop('checked')),
                'required_location' : Number($required_location_option.prop('checked'))
           } // options
        }; // data

        // update data and show spinner while updating
        var ladda = Ladda.create(this);
        ladda.start();
        $.post(ajaxurl, data, function (response) {
            ladda.stop();
            booklyAlert({success : [BooklyL10n.saved]});
        });
    });

    // Reset options to defaults.
    $reset_button.on('click', function() {
        // Reset color.
        $color_picker.wpColorPicker('color', $color_picker.data('selected'));

        // Reset texts.
        jQuery.each($('.editable:not([data-type=multiple])'), function() {
             $(this).text($(this).data('option-default')); //default value for texts
             $(this).editable('setValue', $(this).data('option-default')); // default value for editable inputs
        });

        // Reset texts.
        jQuery.each($('.ab-service-list, .ab-employee-list'), function() {
            $(this).html($(this).data('default')); //default value
        });

        // default value for multiple inputs
        $('[data-type=multiple]').each(function () {
            var $elem = $(this),
                options = $elem.data('options-default'),
                $target;
            $elem.data('options',options);
            $.each(options, function (name, value) {
                $target = $('#' + name);
                if ($target.is(':text')) {
                    $target.val(value);
                } else {
                    $target.text(value);
                }
                $target = $('[name=' + name + ']:text');
                if ($target.is(':text')) {
                    $target.val(value);
                } else {
                    $target.text(value);
                }
            });
        });
    });

    $progress_tracker_option.change(function(){
        $('.ab-progress-tracker').toggle($(this).is(':checked'));
    }).trigger('change');

    var day_one_column = $('.ab-day-one-column'),
        day_columns    = $('.ab-day-columns');

    if ($show_calendar_option.prop('checked')) {
        $second_step_calendar_wrap.show();
        day_columns.find('.col3,.col4,.col5,.col6,.col7').hide();
        day_columns.find('.col2 button:gt(0)').attr('style', 'display: none !important');
        day_one_column.find('.col2,.col3,.col4,.col5,.col6,.col7').hide();
    }

    // Change show calendar
    $show_calendar_option.change(function() {
        if (this.checked) {
            $second_step_calendar_wrap.show();
            day_columns.find('.col3,.col4,.col5,.col6,.col7').hide();
            day_columns.find('.col2 button:gt(0)').attr('style', 'display: none !important');
            day_one_column.find('.col2,.col3,.col4,.col5,.col6,.col7').hide();
        } else {
            $second_step_calendar_wrap.hide();
            day_columns.find('.col2 button:gt(0)').attr('style', 'display: block !important');
            day_columns.find('.col2 button.ab-first-child').attr('style', 'background: ' + $color_picker.wpColorPicker('color') + '!important;display: block !important');
            day_columns.find('.col3,.col4,.col5,.col6,.col7').css('display','inline-block');
            day_one_column.find('.col2,.col3,.col4,.col5,.col6,.col7').css('display','inline-block');
        }
    });

    // Change blocked time slots.
    $blocked_timeslots_option.change(function(){
        if (this.checked) {
            $('.ab-hour.no-booked').removeClass('no-booked').addClass('booked');
        } else {
            $('.ab-hour.booked').removeClass('booked').addClass('no-booked');
        }
    });

    // Change day one column.
    $day_one_column_option.change(function() {
        if (this.checked) {
            day_one_column.show();
            day_columns.hide();
        } else {
            day_one_column.hide();
            day_columns.show();
        }
    });

    // Clickable week-days.
    $('.bookly-week-day').on('change', function () {
        var self = $(this);
        if (self.is(':checked') && !self.parent().hasClass('active')) {
            self.parent().addClass('active');
        } else if (self.parent().hasClass('active')) {
            self.parent().removeClass('active')
        }
    });

    var multiple = function (options) {
        this.init('multiple', options, multiple.defaults);
    };

    // Step repeat.
    $repeat_ui_step_calendar.pickadate({
        formatSubmit   : 'yyyy-mm-dd',
        format         : BooklyL10n.date_format,
        min            : true,
        clear          : false,
        close          : false,
        today          : BooklyL10n.today,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        onRender       : applyColor,
        firstDay       : BooklyL10n.start_of_week == 1
    });
    $repeat_ui_variant.on('change', function () {
        $repeat_ui_variants.hide();
        $('.bookly-variant-' + this.value).show()
    }).trigger('change');

    $repeat_ui_variant_monthly.on('change', function () {
        $repeat_ui_monthly_week_day.toggle(this.value != 'specific');
        $repeat_ui_monthly_specific_day.toggle(this.value == 'specific');
    }).trigger('change');

    $repeat_ui_weekly_week_day.on('change', function () {
        var $this = $(this);
        if ($this.is(':checked')) {
            $this.parent().not("[class*='active']").addClass('active');
        } else {
            $this.parent().removeClass('active');
        }
    });

    // Inherit from Abstract input.
    $.fn.editableutils.inherit(multiple, $.fn.editabletypes.abstractinput);

    $.extend(multiple.prototype, {
        container: null,
        $elem  : null,
        render : function() {
            this.container = jQuery('div.bookly-js-container', this.tpl);
        },

        value2html: function (value, element) { },

        activate: function () {
            this.container.find(':text:eq(0)').focus();
        },

        value2input: function(value) {
            if(!value) {
                return;
            }
            var container = this.container;
            this.$elem = value.elem;
            if (!this.$elem.data('options')) {
                this.$elem.data('options', this.$elem.data('options-default'));
            }
            container.html('');
            $.each(this.$elem.data('options'), function (id, value) {
                $('<input/>', {
                    type : 'text',
                    class: 'form-control input-sm',
                    name : id,
                    value: value || ''
                }).appendTo(container);
            });
        },

        input2value: function() {
            var options = {};
            $.each(this.container.find(':text'), function () {
                var name = $(this).attr('name'),
                    $target = $('#' + name);
                options[name] = this.value;
                if($target.is(':text')){
                    $target.val(this.value);
                } else {
                    $target.text(this.value);
                }
            });
            this.$elem.data('options', options);
            return {elem: this.$elem};
        }
    });
    multiple.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="bookly-editable-multiple"><div class="bookly-js-container">',
        inputclass: ''
    });

    $.fn.editabletypes.multiple = multiple;
    $('[data-type=multiple]').each(function () {
        var $elem = $(this);
        $elem.editable({ value: { elem: $elem}});
    });

    $text_info_service.add('#bookly_l10n_info_time_step').add('#bookly_l10n_info_details_step').add('#bookly_l10n_info_payment_step').add('#bookly_l10n_info_complete_step').add('#bookly_l10n_info_coupon').editable({placement: 'right'});
    $ab_editable.editable();

    $.fn.editableform.template = '<form class="form-inline editableform"> <div class="control-group"> <div> <div class="editable-input"></div><div class="editable-buttons"></div></div><div class="editable-notes"></div><div class="editable-error-block"></div></div> </form>';
    $.fn.editableform.buttons = '<div class="btn-group btn-group-sm"><button type="submit" class="btn btn-success editable-submit"><span class="glyphicon glyphicon-ok"></span></button><button type="button" class="btn btn-default editable-cancel"><span class="glyphicon glyphicon-remove"></span></button></div>';

    $ab_editable.on('shown', function(e, editable) {
        $('.popover').find('.arrow').removeClass().addClass('popover-arrow');
        $('.editable-notes').html($(e.target).data('notes'));
    });
    $('[data-type="multiple"]').on('shown', function(e, editable) {
        $('.popover').find('.arrow').removeClass().addClass('popover-arrow');
    });

    $("[data-mirror]").on('save', function (e, params) {
        $("." + $(e.target).data('mirror')).editable('setValue', params.newValue);
        switch ($(e.target).data('mirror')){
            case 'text_services':
                $(".ab-service-list").html(params.newValue.label);
                break;
            case 'text_locations':
                $(".ab-location-list").html(params.newValue.label);
                break;
            case 'text_employee':
                $(".ab-employee-list").html(params.newValue.label);
                break;
        }
    });

    $('input[type=radio]').change(function () {
        if ($('.ab-card-payment').is(':checked')) {
            $('form.ab-card-form').show();
        } else {
            $('form.ab-card-form').hide();
        }
    });

    $('#bookly-js-hint-alert').on('closed.bs.alert', function () {
        $.ajax({
            url: ajaxurl,
            data: { action: 'bookly_dismiss_appearance_notice' }
        });
    })
}); // jQuery
