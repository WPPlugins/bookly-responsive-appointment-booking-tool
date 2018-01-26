jQuery(function($) {
    var
        $color_picker                   = $('.bookly-js-color-picker'),
        $editableElements               = $('.bookly-js-editable'),
        $show_progress_tracker          = $('#bookly-show-progress-tracker'),
        $step_settings                  = $('#bookly-step-settings'),
        // Service step.
        $staff_name_with_price          = $('#bookly-staff-name-with-price'),
        $service_name_with_duration     = $('#bookly-service-name-with-duration'),
        $required_employee              = $('#bookly-required-employee'),
        $required_location              = $('#bookly-required-location'),
        // Time step.
        $time_step_calendar             = $('.bookly-js-selected-date'),
        $time_step_calendar_wrap        = $('.bookly-js-slot-calendar'),
        $show_blocked_timeslots         = $('#bookly-show-blocked-timeslots'),
        $show_day_one_column            = $('#bookly-show-day-one-column'),
        $show_calendar                  = $('#bookly-show-calendar'),
        $day_one_column                 = $('#bookly-day-one-column'),
        $day_multi_columns              = $('#bookly-day-multi-columns'),
        // Step repeat.
        $repeat_step_calendar           = $('.bookly-js-repeat-until'),
        $repeat_variants                = $('[class^="bookly-js-variant"]'),
        $repeat_variant                 = $('.bookly-js-repeat-variant'),
        $repeat_variant_monthly         = $('.bookly-js-repeat-variant-monthly'),
        $repeat_weekly_week_day         = $('.bookly-js-week-day'),
        $repeat_monthly_specific_day    = $('.bookly-js-monthly-specific-day'),
        $repeat_monthly_week_day        = $('.bookly-js-monthly-week-day'),
        // Step details.
        $required_phone                 = $('#bookly-cst-required-phone'),
        $show_login_button              = $('#bookly-show-login-button'),
        $first_last_name                = $('#bookly-cst-first-last-name'),
        $show_notes_field               = $('#bookly-show-notes'),
        // Buttons.
        $save_button                    = $('#ajax-send-appearance'),
        $reset_button                   = $('button[type=reset]'),
        $checkboxes                     = $('#bookly-appearance').find('input[type="checkbox"]')
    ;

    $checkboxes.each(function () {
        $(this).data('default', $(this).prop('checked'));
    });
    // Menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    // Apply color from color picker.
    var applyColor = function() {
        var color = $color_picker.wpColorPicker('color'),
            color_important = color + '!important;';
        $('.bookly-progress-tracker').find('.active').css('color', color).find('.step').css('background', color);
        $('.bookly-js-mobile-step-1 label').css('color', color);
        $('.bookly-label-error').css('color', color);
        $('.bookly-js-actions > a').css('background-color', color);
        $('.bookly-js-mobile-next-step').css('background', color);
        $('.bookly-js-week-days label').css('background-color', color);
        $('.picker__frame').attr('style', 'background: ' + color_important);
        $('.picker__header').attr('style', 'border-bottom: ' + '1px solid ' + color_important);
        $('.picker__day').off().mouseenter(function() {
            $(this).attr('style', 'color: ' + color_important);
        }).mouseleave(function(){
            $(this).attr('style', $(this).hasClass('picker__day--selected') ? 'color: ' + color_important : '')
        });
        $('.picker__day--selected').attr('style', 'color: ' + color_important);
        $('.picker__button--clear').attr('style', 'color: ' + color_important);
        $('.picker__button--today').attr('style', 'color: ' + color_important);
        $('.bookly-extra-step .bookly-extras-thumb.bookly-extras-selected').css('border-color', color);
        $('.bookly-columnizer .bookly-day, .bookly-schedule-date,.bookly-pagination li.active').css({
            'background': color,
            'border-color': color
        });
        $('.bookly-columnizer .bookly-hour').off().hover(
            function() { // mouse-on
                $(this).css({
                    'color': color,
                    'border': '2px solid ' + color
                });
                $(this).find('.bookly-hour-icon').css({
                    'border-color': color,
                    'color': color
                });
                $(this).find('.bookly-hour-icon > span').css({
                    'background': color
                });
            },
            function() { // mouse-out
                $(this).css({
                    'color': '#333333',
                    'border': '1px solid #cccccc'
                });
                $(this).find('.bookly-hour-icon').css({
                    'border-color': '#333333',
                    'color': '#cccccc'
                });
                $(this).find('.bookly-hour-icon > span').css({
                    'background': '#cccccc'
                });
            }
        );
        $('.bookly-details-step label').css('color', color);
        $('.bookly-card-form label').css('color', color);
        $('.bookly-nav-tabs .ladda-button, .bookly-nav-steps .ladda-button, .bookly-btn, .bookly-round, .bookly-square').css('background-color', color);
        $('.bookly-triangle').css('border-bottom-color', color);
        $('#bookly-pickadate-style').html('.picker__nav--next:before { border-left: 6px solid ' + color_important + ' } .picker__nav--prev:before { border-right: 6px solid ' + color_important + ' }');
    };

    // Init color picker.
    $color_picker.wpColorPicker({
        change : applyColor
    });

    // Init editable elements.
    $editableElements.editable();

    // Show progress tracker.
    $show_progress_tracker.on('change', function() {
        $('.bookly-progress-tracker').toggle(this.checked);
    }).trigger('change');

    // Show step specific settings.
    $('li.bookly-nav-item').on('shown.bs.tab', function (e) {
        $step_settings.children().hide();
        switch (e.target.getAttribute('data-target')) {
            case '#bookly-step-1': $step_settings.find('.bookly-js-service-settings').show(); break;
            case '#bookly-step-3': $step_settings.find('.bookly-js-time-settings').show(); break;
            case '#bookly-step-6': $step_settings.find('.bookly-js-details-settings').show(); break;
            case '#bookly-step-7': $step_settings.find('.bookly-js-payment-settings').show(); break;
            case '#bookly-step-8': $step_settings.find('.bookly-js-done-settings').show(); break;
        }
    });

    // Dismiss help notice.
    $('#bookly-js-hint-alert').on('closed.bs.alert', function () {
        $.ajax({
            url: ajaxurl,
            data: { action: 'bookly_dismiss_appearance_notice', csrf_token : BooklyL10n.csrf_token }
        });
    });

    /**
     * Step Service
     */

    // Init calendar.
    $('.bookly-js-date-from').pickadate({
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

    // Show price next to staff member name.
    $staff_name_with_price.on('change', function () {
        var staff = $('.bookly-js-select-employee').val();
        if (staff) {
            $('.bookly-js-select-employee').val(staff * -1);
        }
        $('.employee-name-price').toggle($staff_name_with_price.prop("checked"));
        $('.employee-name').toggle(!$staff_name_with_price.prop("checked"));
    }).trigger('change');

    // Show duration next to service name.
    $service_name_with_duration.on('change', function () {
        var service = $('.bookly-js-select-service').val();
        if (service) {
            $('.bookly-js-select-service').val(service * -1);
        }
        $('.service-name-duration').toggle($service_name_with_duration.prop("checked"));
        $('.service-name').toggle(!$service_name_with_duration.prop("checked"));
    }).trigger('change');

    // Clickable week-days.
    $repeat_weekly_week_day.on('change', function () {
        $(this).parent().toggleClass('active', this.checked);
    });


    /**
     * Step Time
     */

    // Init calendar.
    $time_step_calendar.pickadate({
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
    $time_step_calendar_wrap.find('.picker__holder').css({ top : '0px', left : '0px' });

    // Show calendar.
    $show_calendar.on('change', function() {
        if (this.checked) {
            $time_step_calendar_wrap.show();
            $day_multi_columns.find('.col3,.col4,.col5,.col6,.col7').hide();
            $day_multi_columns.find('.col2 button:gt(0)').attr('style', 'display: none !important');
            $day_one_column.find('.col2,.col3,.col4,.col5,.col6,.col7').hide();
        } else {
            $time_step_calendar_wrap.hide();
            $day_multi_columns.find('.col2 button:gt(0)').attr('style', 'display: block !important');
            $day_multi_columns.find('.col2 button.bookly-js-first-child').attr('style', 'background: ' + $color_picker.wpColorPicker('color') + '!important;display: block !important');
            $day_multi_columns.find('.col3,.col4,.col5,.col6,.col7').css('display','inline-block');
            $day_one_column.find('.col2,.col3,.col4,.col5,.col6,.col7').css('display','inline-block');
        }
    }).trigger('change');

    // Show blocked time slots.
    $show_blocked_timeslots.on('change', function(){
        if (this.checked) {
            $('.bookly-hour.no-booked').removeClass('no-booked').addClass('booked');
        } else {
            $('.bookly-hour.booked').removeClass('booked').addClass('no-booked');
        }
    });

    // Show day as one column.
    $show_day_one_column.change(function() {
        if (this.checked) {
            $day_one_column.show();
            $day_multi_columns.hide();
        } else {
            $day_one_column.hide();
            $day_multi_columns.show();
        }
    });

    /**
     * Step repeat.
     */

    // Init calendar.
    $repeat_step_calendar.pickadate({
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
    $repeat_variant.on('change', function () {
        $repeat_variants.hide();
        $('.bookly-js-variant-' + this.value).show()
    }).trigger('change');

    $repeat_variant_monthly.on('change', function () {
        $repeat_monthly_week_day.toggle(this.value != 'specific');
        $repeat_monthly_specific_day.toggle(this.value == 'specific');
    }).trigger('change');

    $repeat_weekly_week_day.on('change', function () {
        var $this = $(this);
        if ($this.is(':checked')) {
            $this.parent().not("[class*='active']").addClass('active');
        } else {
            $this.parent().removeClass('active');
        }
    });


    /**
     * Step Details
     */

    // Init phone field.
    if (BooklyL10n.intlTelInput.enabled) {
        $('.bookly-user-phone').intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            initialCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : '';
                    callback(countryCode);
                });
            },
            utilsScript: BooklyL10n.intlTelInput.utils
        });
    }

    // Show login form.
    $show_login_button.change(function () {
        $('#bookly-js-show-login-form').toggle(this.checked);
    }).trigger('change');

    // Show first and last name.
    $first_last_name.on('change', function () {
        $first_last_name.popover('toggle');
        if (this.checked) {
            $('.bookly-details-full-name').css('display', 'none');
            $('.bookly-details-first-last-name').css('display', 'table');
        } else {
            $('.bookly-details-full-name').css('display', 'block');
            $('.bookly-details-first-last-name').css('display', 'none');
        }
    });

    // Show notes field.
    $show_notes_field.change(function () {
        $('#bookly-js-notes').toggle(this.checked);
    }).trigger('change');

    /**
     * Step Payment.
     */

    // Switch payment step view (single/several services).
    $('#bookly-payment-step-view').on('change', function () {
        $('.bookly-js-payment-single-app').toggle(this.value == 'single-app');
        $('.bookly-js-payment-several-apps').toggle(this.value == 'several-apps');
    });

    // Show credit card form.
    $('.bookly-payment-nav :radio').on('change', function () {
        $('form.bookly-card-form').toggle(this.id == 'bookly-card-payment');
    });

    /**
     * Step Done.
     */

    // Switch done step view (success/error).
    $('#bookly-done-step-view').on('change', function () {
        $('.bookly-js-done-success').toggle(this.value == 'booking-success');
        $('.bookly-js-done-limit-error').toggle(this.value == 'booking-limit-error');
        $('.bookly-js-done-processing').toggle(this.value == 'booking-processing');
    });


    /**
     * Misc.
     */

    // Custom CSS.
    $('#bookly-custom-css-save').on('click', function (e) {
        var $custom_css         = $('#bookly-custom-css'),
            $modal              = $('#bookly-custom-css-dialog');

        saved_css = $custom_css.val();

        var ladda = Ladda.create(this);
        ladda.start();

        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action     : 'bookly_save_custom_css',
                csrf_token : BooklyL10n.csrf_token,
                custom_css : $custom_css.val()
            },
            dataType : 'json',
            success  : function (response) {
                if (response.success) {
                    $modal.modal('hide');
                    booklyAlert({success : [response.data.message]});
                }
            },
            complete : function () {
                ladda.stop();
            }
        });
    });

    $('#bookly-custom-css-cancel').on('click', function (e) {
        var $custom_css = $('#bookly-custom-css'),
            $modal      = $('#bookly-custom-css-dialog');

        $modal.modal('hide');

        $custom_css.val(saved_css);
    });

    $('#bookly-custom-css').keydown(function(e) {
        if(e.keyCode === 9) { //tab button
            var start = this.selectionStart;
            var end = this.selectionEnd;

            var $this = $(this);
            var value = $this.val();

            $this.val(value.substring(0, start)
                + "\t"
                + value.substring(end));

            this.selectionStart = this.selectionEnd = start + 1;

            e.preventDefault();
        }
    });

    // Save options.
    $save_button.on('click', function(e) {
        e.preventDefault();
        // Prepare data.
        var data = {
            action: 'bookly_update_appearance_options',
            csrf_token: BooklyL10n.csrf_token,
            options: {
                // Color.
                'bookly_app_color'                      : $color_picker.wpColorPicker('color'),
                // Checkboxes.
                'bookly_app_service_name_with_duration' : Number($service_name_with_duration.prop('checked')),
                'bookly_app_show_blocked_timeslots'     : Number($show_blocked_timeslots.prop('checked')),
                'bookly_app_show_calendar'              : Number($show_calendar.prop('checked')),
                'bookly_app_show_day_one_column'        : Number($show_day_one_column.prop('checked')),
                'bookly_app_show_login_button'          : Number($show_login_button.prop('checked')),
                'bookly_app_show_notes'                 : Number($show_notes_field.prop('checked')),
                'bookly_app_show_progress_tracker'      : Number($show_progress_tracker.prop('checked')),
                'bookly_app_staff_name_with_price'      : Number($staff_name_with_price.prop('checked')),
                'bookly_app_required_employee'          : Number($required_employee.prop('checked')),
                'bookly_app_required_location'          : Number($required_location.prop('checked')),
                'bookly_cst_required_phone'             : Number($required_phone.prop('checked')),
                'bookly_cst_first_last_name'            : Number($first_last_name.prop('checked'))
            }
        };
        // Add data from editable elements.
        $editableElements.each(function () {
            $.extend(data.options, $(this).editable('getValue', true));
        });

        // Update data and show spinner while updating.
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

        // Reset editable texts.
        $editableElements.each(function () {
            $(this).editable('setValue', $.extend({}, $(this).data('values')));
        });

        $checkboxes.each(function () {
            if ($(this).prop('checked') != $(this).data('default')) {
                $(this).prop('checked', $(this).data('default')).trigger('change');
            }
        });
        $first_last_name.popover('hide');
    });
});