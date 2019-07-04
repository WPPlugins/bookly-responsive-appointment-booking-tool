jQuery(function($) {
    var
        $color_picker                   = $('.bookly-js-color-picker'),
        $editableElements               = $('.bookly-js-editable'),
        $show_progress_tracker          = $('#bookly-show-progress-tracker'),
        $align_buttons_left             = $('#bookly-align-buttons-left'),
        $step_settings                  = $('#bookly-step-settings'),
        $bookly_show_step_extras        = $('#bookly-show-step-extras'),
        $bookly_show_step_repeat        = $('#bookly-show-step-repeat'),
        $bookly_show_step_cart          = $('#bookly-show-step-cart'),
        // Service step.
        $staff_name_with_price          = $('#bookly-staff-name-with-price'),
        $service_duration_with_price    = $('#bookly-service-duration-with-price'),
        $service_name_with_duration     = $('#bookly-service-name-with-duration'),
        $required_employee              = $('#bookly-required-employee'),
        $required_location              = $('#bookly-required-location'),
        $show_ratings                   = $('#bookly-show-ratings'),
        $show_chain_appointments        = $('#bookly-show-chain-appointments'),
        $service_select                 = $('.bookly-js-select-service'),
        $staff_select                   = $('.bookly-js-select-employee'),
        $duration_select                = $('.bookly-js-select-duration'),
        $location_select                = $('.bookly-js-select-location'),

    // Time step.
        $time_step_nop                  = $('#bookly-show-nop-on-time-step'),
        $time_step_calendar             = $('.bookly-js-selected-date'),
        $time_step_calendar_wrap        = $('.bookly-js-slot-calendar'),
        $show_blocked_timeslots         = $('#bookly-show-blocked-timeslots'),
        $show_waiting_list              = $('#bookly-show-waiting-list'),
        $show_day_one_column            = $('#bookly-show-day-one-column'),
        $show_time_zone_switcher        = $('#bookly-show-time-zone-switcher'),
        $show_calendar                  = $('#bookly-show-calendar'),
        $day_one_column                 = $('#bookly-day-one-column'),
        $day_multi_columns              = $('#bookly-day-multi-columns'),
        $columnizer                     = $('.bookly-time-step .bookly-columnizer-wrap'),
        // Step repeat.
        $repeat_step_calendar           = $('.bookly-js-repeat-until'),
        $repeat_variants                = $('[class^="bookly-js-variant"]'),
        $repeat_variant                 = $('.bookly-js-repeat-variant'),
        $repeat_variant_monthly         = $('.bookly-js-repeat-variant-monthly'),
        $repeat_weekly_week_day         = $('.bookly-js-week-day'),
        $repeat_monthly_specific_day    = $('.bookly-js-monthly-specific-day'),
        $repeat_monthly_week_day        = $('.bookly-js-monthly-week-day'),
        // Step Cart.
        $show_cart_extras               = $('#bookly-show-cart-extras'),
        // Step details.
        $required_details               = $('#bookly-cst-required-details'),
        $show_login_button              = $('#bookly-show-login-button'),
        $show_facebook_login_button     = $('#bookly-show-facebook-login-button'),
        $first_last_name                = $('#bookly-cst-first-last-name'),
        $confirm_email                  = $('#bookly-cst-show-email-confirm'),
        $show_notes_field               = $('#bookly-show-notes'),
        $show_birthday_fields           = $('#bookly-show-birthday'),
        $show_address_fields            = $('#bookly-show-address'),
        $show_google_maps               = $('#bookly-show-google-maps'),
        $show_custom_fields             = $('#bookly-show-custom-fields'),
        $show_customer_information      = $('#bookly-show-customer-information'),
        $show_files                     = $('#bookly-show-files'),
        // Step payment.
        $show_coupons                   = $('#bookly-show-coupons'),
        // Buttons.
        $save_button                    = $('#ajax-send-appearance'),
        $reset_button                   = $('button[type=reset]'),
        $checkboxes                     = $('#bookly-appearance').find('input[type="checkbox"]'),
        $selects                        = $('#bookly-appearance').find('select[data-default]')
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

    // Align buttons to the left
    $align_buttons_left.on('change', function () {
        if (this.checked) {
            $('.bookly-nav-steps > div.bookly-right').removeClass('bookly-right').addClass('bookly-left');
        } else {
            $('.bookly-nav-steps > div.bookly-left').removeClass('bookly-left').addClass('bookly-right');
        }
    });

    // Show steps.
    $('.bookly-js-show-step').on('change', function () {
        var target = $(this).data('target'),
            $button = $('li.bookly-nav-item[data-target="#' + target + '"]'),
            $step = $('div[data-step="' + target + '"]');
        if ($(this).prop('checked')) {
            $button.show();
            $step.show();
        } else {
            if ($button.hasClass('active')) {
                $('li.bookly-nav-item[data-target="#bookly-step-1"]').trigger('click');
            }
            $button.hide();
            $step.hide();
        }
        // Hide/show cart buttons
        if ( target == 'bookly-step-5') {
            $('.bookly-js-go-to-cart').toggle( $(this).prop('checked') );
        }

        $('.bookly-progress-tracker > div:visible').each(function (num) {
            $(this).find('.bookly-js-step-number').html(num + 1);
        });
        $('.bookly-js-appearance-steps > li:visible').each(function (num) {
            $(this).find('.bookly-js-step-number').html(num + 1);
        });
    }).trigger('change');

    // Show step specific settings.
    $('li.bookly-nav-item').on('shown.bs.tab', function (e) {
        $step_settings.children().hide();
        switch (e.target.getAttribute('data-target')) {
            case '#bookly-step-1': $step_settings.find('.bookly-js-service-settings').show(); break;
            case '#bookly-step-3': $step_settings.find('.bookly-js-time-settings').show(); break;
            case '#bookly-step-5': $step_settings.find('.bookly-js-cart-settings').show(); break;
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
        weekdaysFull   : BooklyL10n.daysFull,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        onRender       : applyColor,
        firstDay       : BooklyL10n.startOfWeek == 1
    });

    // Show price next to staff member name.
    $staff_name_with_price.on('change', function () {
        var staff = $staff_select.val();
        if (staff) {
            $('.bookly-js-select-employee').val(staff * -1);
        }
        $('.employee-name-price').toggle($staff_name_with_price.prop("checked"));
        $('.employee-name').toggle(!$staff_name_with_price.prop("checked"));
    }).trigger('change');

    if ($service_duration_with_price.prop("checked")) {
        $duration_select.val(-1);
    }

    // Show price next to service duration.
    $service_duration_with_price.on('change', function () {
        var duration = $duration_select.val();
        if (duration) {
            $duration_select.val(duration * -1);
        }
        $('.bookly-js-duration-price').toggle($service_duration_with_price.prop("checked"));
        $('.bookly-js-duration').toggle(!$service_duration_with_price.prop("checked"));
    }).trigger('change');

    $show_ratings.on('change', function () {
        var state = $(this).prop('checked');
        $('.bookly-js-select-employee option').each(function () {
            if ($(this).val() != '0') {
                if (!state) {
                    if ($(this).text().charAt(0) == '★') {
                        $(this).text($(this).text().substring(5));
                    }
                } else {
                    var rating = Math.round(10 * (Math.random() * 6 + 1)) / 10;
                    if (rating <= 5) {
                        $(this).text('★' + rating.toFixed(1) + ' ' + $(this).text());
                    }
                }
            }
        });
    }).trigger('change');

    // Show chain appointments
    $show_chain_appointments.on('change', function () {
        $('.bookly-js-chain-appointments').toggle( this.checked );
    });

    // Show duration next to service name.
    $service_name_with_duration.on('change', function () {
        var service = $service_select.val();
        if (service) {
            $service_select.val(service * -1);
        }
        $('.service-name-duration').toggle($service_name_with_duration.prop("checked"));
        $('.service-name').toggle(!$service_name_with_duration.prop("checked"));
    }).trigger('change');

    // Show price next to service duration.
    $service_duration_with_price.on('change', function () {
        if ($(this).prop('checked')) {
            $('option[value="1"]', $duration_select).each(function () {
                $(this).text($(this).attr('data-text-1'));
            });
        } else {
            $('option[value="1"]', $duration_select).each(function () {
                $(this).text($(this).attr('data-text-0'));
            });
        }
    }).trigger('change');

    // Clickable week-days.
    $repeat_weekly_week_day.on('change', function () {
        $(this).parent().toggleClass('active', this.checked);
    });

    // Highlight affected inputs.
    $required_employee.on('click', function () {
        bookly_highlight($staff_select);
    });
    $staff_name_with_price.on('click', function () {
        bookly_highlight($staff_select);
    });
    $show_ratings.on('click', function () {
        bookly_highlight($staff_select);
    });
    $service_name_with_duration.on('click', function () {
        bookly_highlight($service_select);
    });
    $service_duration_with_price.on('click', function () {
        bookly_highlight($duration_select);
    });
    $required_location.on('click', function () {
        bookly_highlight($location_select);
    });
    $required_details.on('change', function () {
        if (this.value == 'phone' || this.value == 'both') {
            bookly_highlight($('.bookly-js-details-phone input'));
        }
        if (this.value == 'email' || this.value == 'both') {
            bookly_highlight($('.bookly-js-details-email input'));
        }
    });

    /**
     * Step Time
     */

    // Init calendar.
    $time_step_calendar.pickadate({
        formatSubmit   : 'yyyy-mm-dd',
        format         : BooklyL10n.date_format,
        min            : true,
        weekdaysFull   : BooklyL10n.daysFull,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        close          : false,
        clear          : false,
        today          : false,
        closeOnSelect  : false,
        onRender       : applyColor,
        firstDay       : BooklyL10n.startOfWeek == 1,
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
    $show_blocked_timeslots.on('change', function () {
        if (this.checked) {
            $('.bookly-hour.no-booked').removeClass('no-booked').addClass('booked');
            $('.bookly-column .bookly-hour.booked .bookly-time-additional', $columnizer).text('');
        } else {
            $('.bookly-hour.booked').removeClass('booked').addClass('no-booked');
            if ($time_step_nop.prop('checked')) {
                $('.bookly-column .bookly-hour:not(.booked):not(.bookly-slot-in-waiting-list) .bookly-time-additional', $columnizer).each(function () {
                    var nop = Math.ceil(Math.random() * 9);
                    if (BooklyL10n.nop_format == 'busy') {
                        $(this).text('[' + nop + '/10]');
                    } else {
                        $(this).text('[' + nop + ']');
                    }
                });
            }
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

    // Show time zone switcher
    $show_time_zone_switcher.on('change', function() {
        $('.bookly-js-time-zone-switcher').toggle(this.checked);
    }).trigger('change');

    // Show nop/capacity
    $time_step_nop.on('change', function () {
        if (this.checked) {
            $('.bookly-column', $columnizer).addClass('bookly-column-wide');
            $('.bookly-column .bookly-hour:not(.booked):not(.bookly-slot-in-waiting-list) .bookly-time-additional', $columnizer).each(function () {
                var nop = Math.ceil(Math.random() * 9);
                if (BooklyL10n.nop_format == 'busy') {
                    $(this).text('[' + nop + '/10]');
                } else {
                    $(this).text('[' + nop + ']');
                }
            });
            $('.bookly-column.col5', $columnizer).hide();
            $('.bookly-column.col6', $columnizer).hide();
            $('.bookly-column.col7', $columnizer).hide();
        } else {
            $('.bookly-column', $columnizer).removeClass('bookly-column-wide');
            $('.bookly-column .bookly-hour:not(.bookly-slot-in-waiting-list) .bookly-time-additional', $columnizer).text('');
            if (!$show_calendar.prop('checked')) {
                $('.bookly-column', $columnizer).removeClass('bookly-column-wide').show();
            }
        }
    }).trigger('change');

    $show_waiting_list.on('change', function () {
        if (this.checked) {
            $('.bookly-column .bookly-hour.no-waiting-list, .bookly-column .bookly-hour.bookly-slot-in-waiting-list').each(function () {
                $(this).removeClass('no-waiting-list').addClass('bookly-slot-in-waiting-list').find('.bookly-time-additional').text('(' + Math.floor(Math.random() * 10) + ')');
            })
        } else {
            $('.bookly-column .bookly-hour.bookly-slot-in-waiting-list').removeClass('bookly-slot-in-waiting-list').addClass('no-waiting-list').find('.bookly-time-additional').text('');
            $time_step_nop.trigger('change');
        }
    }).trigger('change');

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
        weekdaysFull   : BooklyL10n.daysFull,
        weekdaysShort  : BooklyL10n.days,
        monthsFull     : BooklyL10n.months,
        labelMonthNext : BooklyL10n.nextMonth,
        labelMonthPrev : BooklyL10n.prevMonth,
        onRender       : applyColor,
        firstDay       : BooklyL10n.startOfWeek == 1
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
     * Step Repeat.
     */
    $bookly_show_step_repeat.change(function () {
        $('.bookly-js-repeat-enabled').toggle(this.checked);
    }).trigger('change');

    /**
     * Step Cart
     */
    $show_cart_extras.change(function () {
        $('.bookly-js-extras-cart').toggle(this.checked);
    }).trigger('change');

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

    // Show login button.
    $show_login_button.change(function () {
        $('#bookly-login-button').toggle(this.checked);
    }).trigger('change');

    // Show Facebook login button.
    $show_facebook_login_button.change(function () {
        if ($(this).data('appid') == '') {
            if (this.checked) {
                $('#bookly-facebook-warning').modal('show');
                this.checked = false;
            }
        } else {
            $('#bookly-facebook-login-button').toggle(this.checked);
        }
    });

    // Show first and last name.
    $first_last_name.on('change', function () {
        $first_last_name.popover('toggle');
        if (this.checked) {
            $('.bookly-js-details-full-name').addClass('collapse');
            $('.bookly-js-details-first-last-name').removeClass('collapse');
            if ($confirm_email.is(':checked')) {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').removeClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            } else {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            }
        } else {
            $('.bookly-js-details-full-name').removeClass('collapse');
            $('.bookly-js-details-first-last-name').addClass('collapse');
            if ($confirm_email.is(':checked')) {
                $('.bookly-js-details-email').addClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').removeClass('collapse');
            } else {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            }
        }
    });

    // Show first and last name.
    $confirm_email.on('change', function () {
        if (this.checked) {
            if ($first_last_name.is(':checked')) {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').removeClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            } else {
                $('.bookly-js-details-email').addClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').removeClass('collapse');
            }
        } else {
            if ($first_last_name.is(':checked')) {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            } else {
                $('.bookly-js-details-email').removeClass('collapse');
                $('.bookly-js-details-confirm').addClass('collapse');
                $('.bookly-js-details-email-confirm').addClass('collapse');
            }
        }
    });

    // Show notes field.
    $show_notes_field.change(function () {
        $('#bookly-js-notes').toggle(this.checked);
    }).trigger('change');

    // Show birthday fields
    $show_birthday_fields.change(function () {
        $('#bookly-js-birthday').toggle(this.checked);
    }).trigger('change');

    // Show address fields
    $show_address_fields.change(function () {
        $('#bookly-js-address').toggle(this.checked);
        if (this.checked) {
            $show_google_maps.closest('[data-toggle="popover"]').popover('destroy');
            $show_google_maps.prop('disabled', false);
        } else {
            $show_google_maps.closest('[data-toggle="popover"]').popover();
            $show_google_maps.prop('checked', false).prop('disabled', true).trigger('change');
        }
    }).trigger('change');

    // Show address fields
    $show_google_maps.change(function () {
        $('.bookly-js-google-maps').toggle(this.checked);
    }).trigger('change');

    $show_custom_fields.change(function () {
        $('.bookly-js-custom-fields').toggle(this.checked);
        if (this.checked) {
            $show_files.closest('[data-toggle="popover"]').popover('destroy');
            $show_files.prop('disabled', false);
        } else {
            $show_files.closest('[data-toggle="popover"]').popover();
            $show_files.prop('checked', false).prop('disabled', true).trigger('change');
        }
    }).trigger('change');

    $show_files.change(function () {
        $('.bookly-js-files').toggle(this.checked);
    }).trigger('change');

    $show_customer_information.change(function () {
        $('.bookly-js-customer-information').toggle(this.checked);
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

    $show_coupons.on('change', function () {
        $('.bookly-js-payment-coupons').toggle( this.checked );
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
    $('.bookly-js-simple-popover').popover();

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
    $save_button.on('click', function (e) {
        e.preventDefault();
        // Prepare data.
        var data = {
            action    : 'bookly_update_appearance_options',
            csrf_token: BooklyL10n.csrf_token,
            options   : {
                // Color.
                'bookly_app_color'                      : $color_picker.wpColorPicker('color'),
                // Checkboxes.
                'bookly_app_service_name_with_duration' : Number($service_name_with_duration.prop('checked')),
                'bookly_app_show_blocked_timeslots'     : Number($show_blocked_timeslots.prop('checked')),
                'bookly_app_show_calendar'              : Number($show_calendar.prop('checked')),
                'bookly_app_show_day_one_column'        : Number($show_day_one_column.prop('checked')),
                'bookly_app_show_time_zone_switcher'    : Number($show_time_zone_switcher.prop('checked')),
                'bookly_app_show_login_button'          : Number($show_login_button.prop('checked')),
                'bookly_app_show_facebook_login_button' : Number($show_facebook_login_button.prop('checked')),
                'bookly_app_show_notes'                 : Number($show_notes_field.prop('checked')),
                'bookly_app_show_birthday'              : Number($show_birthday_fields.prop('checked')),
                'bookly_app_show_address'               : Number($show_address_fields.prop('checked')),
                'bookly_app_show_progress_tracker'      : Number($show_progress_tracker.prop('checked')),
                'bookly_app_align_buttons_left'         : Number($align_buttons_left.prop('checked')),
                'bookly_app_staff_name_with_price'      : Number($staff_name_with_price.prop('checked')),
                'bookly_app_service_duration_with_price': Number($service_duration_with_price.prop('checked')),
                'bookly_app_required_employee'          : Number($required_employee.prop('checked')),
                'bookly_app_required_location'          : Number($required_location.prop('checked')),
                'bookly_group_booking_app_show_nop'     : Number($time_step_nop.prop('checked')),
                'bookly_ratings_app_show_on_frontend'   : Number($show_ratings.prop('checked')),
                'bookly_cst_required_details'           : $required_details.val() == 'both' ? ['phone', 'email'] : [$required_details.val()],
                'bookly_cst_first_last_name'            : Number($first_last_name.prop('checked')),
                'bookly_app_show_email_confirm'         : Number($confirm_email.prop('checked')),
                'bookly_service_extras_enabled'         : Number($bookly_show_step_extras.prop('checked')),
                'bookly_recurring_appointments_enabled' : Number($bookly_show_step_repeat.prop('checked')),
                'bookly_cart_enabled'                   : Number($bookly_show_step_cart.prop('checked')),
                'bookly_chain_appointments_enabled'     : Number($show_chain_appointments.prop('checked')),
                'bookly_coupons_enabled'                : Number($show_coupons.prop('checked')),
                'bookly_custom_fields_enabled'          : Number($show_custom_fields.prop('checked')),
                'bookly_customer_information_enabled'   : Number($show_customer_information.prop('checked')),
                'bookly_files_enabled'                  : Number($show_files.prop('checked')),
                'bookly_waiting_list_enabled'           : Number($show_waiting_list.prop('checked')),
                'bookly_google_maps_address_enabled'    : Number($show_google_maps.prop('checked')),
                'bookly_service_extras_show_in_cart'    : Number($show_cart_extras.prop('checked'))
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
        $selects.each(function () {
            if ($(this).val() != $(this).data('default')) {
                $(this).val($(this).data('default')).trigger('change');
            }
        });
        $first_last_name.popover('hide');
    });

    function bookly_highlight($element) {
        var color = $color_picker.wpColorPicker('color');
        $element.css('background-color', color);
        setTimeout(function () {
            $element.css('background-color', '#fff')
        }, 500);
    }
});