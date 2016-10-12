jQuery(function($) {
    var // Progress Tracker.
        $progress_tracker_option = $('input#ab-progress-tracker-checkbox'),
        $staff_name_with_price_option = $('input#ab-staff-name-with-price-checkbox'),
        // Time slots setting.
        $blocked_timeslots_option = $('input#ab-blocked-timeslots-checkbox'),
        $day_one_column_option = $('input#ab-day-one-column-checkbox'),
        $show_calendar_option = $('input#ab-show-calendar-checkbox'),
        $required_employee_option = $('input#ab-required-employee-checkbox'),
        $required_location_option = $('input#ab-required-location-checkbox'),
        // Buttons.
        $save_button = $('#ajax-send-appearance'),
        $reset_button = $('button[type=reset]'),
        // Texts.
        $text_step_service = $('#ab-text-step-service'),
        $text_step_extras = $('#ab-text-step-extras'),
        $text_step_time = $('#ab-text-step-time'),
        $text_step_cart = $('#ab-text-step-cart'),
        $text_step_details = $('#ab-text-step-details'),
        $text_step_payment = $('#ab-text-step-payment'),
        $text_step_done = $('#ab-text-step-done'),
        $text_label_location = $('#ab-text-label-location'),
        $text_label_multiply = $('#ab-text-label-multiply'),
        $text_label_category = $('#ab-text-label-category'),
        $text_option_location = $('#ab-text-option-location'),
        $text_option_category = $('#ab-text-option-category'),
        $text_option_service = $('#ab-text-option-service'),
        $text_option_employee = $('#ab-text-option-employee'),
        $text_label_service = $('#ab-text-label-service'),
        $text_label_number_of_persons = $('#ab-text-label-number-of-persons'),
        $text_label_employee = $('#ab-text-label-employee'),
        $text_label_select_date = $('#ab-text-label-select_date'),
        $text_label_start_from = $('#ab-text-label-start_from'),
        $text_button_next = $('#ab-text-button-next'),
        $text_button_back = $('#ab-text-button-back'),
        $text_button_book_more = $('#ab-text-button-book-more'),
        $text_button_apply = $('#ab-text-button-apply'),
        $text_label_finish_by = $('#ab-text-label-finish_by'),
        $text_label_name = $('#ab-text-label-name'),
        $text_label_phone = $('#ab-text-label-phone'),
        $text_label_email = $('#ab-text-label-email'),
        $text_label_coupon = $('#ab-text-label-coupon'),
        $text_info_service = $('#ab-text-info-service'),
        $text_info_extras = $('#ab-text-info-extras'),
        $text_info_time = $('#ab-text-info-time'),
        $text_info_cart = $('#ab-text-info-cart'),
        $text_info_details = $('#ab-text-info-details'),
        $text_info_details_guest = $('#ab-text-info-details-guest'),
        $text_info_coupon = $('#ab-text-info-coupon'),
        $text_info_payment = $('#ab-text-info-payment'),
        $text_info_complete = $('#ab-text-info-complete'),
        $text_label_pay_paypal = $('#ab-text-label-pay-paypal'),
        $text_label_pay_ccard = $('#ab-text-label-pay-ccard'),
        $text_label_ccard_number = $('#ab-text-label-ccard-number'),
        $text_label_ccard_expire = $('#ab-text-label-ccard-expire'),
        $text_label_ccard_code = $('#ab-text-label-ccard-code'),
        $color_picker = $('.bookly-js-color-picker'),
        $ab_editable  = $('.ab_editable'),
        $text_label_pay_locally = $('#ab-text-label-pay-locally'),
        $text_label_pay_mollie = $('#ab-text-label-pay-mollie'),
        // Calendars.
        $second_step_calendar = $('.ab-selected-date'),
        $second_step_calendar_wrap = $('.ab-slot-calendar'),
        // Step settings.
        $step_settings = $('#bookly-js-step-settings')
    ;

    if (BooklyL10n.intlTelInput.enabled) {
        $('.ab-user-phone').intlTelInput({
            preferredCountries: [BooklyL10n.intlTelInput.country],
            defaultCountry: BooklyL10n.intlTelInput.country,
            geoIpLookup: function (callback) {
                $.get(ajaxurl, {action: 'ab_ip_info'}, function () {
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
        $('.bookly-js-actions > a').css('background-color', $color_picker.wpColorPicker('color'));
        $('.ab-next-step, .ab-mobile-next-step').css('background', $color_picker.wpColorPicker('color'));
        $('.ab-week-days label').css('background-color', $color_picker.wpColorPicker('color'));
        $('.picker__frame').attr('style', 'background: ' + color_important);
        $('.picker__header').attr('style', 'border-bottom: ' + '1px solid ' + color_important);
        $('.picker__day').mouseenter(function(){
            $(this).attr('style', 'color: ' + color_important);
        }).mouseleave(function(){ $(this).attr('style', $(this).hasClass('picker__day--selected') ? 'color: ' + color_important : '') });
        $('.picker__day--selected').attr('style', 'color: ' + color_important);
        $('.picker__button--clear').attr('style', 'color: ' + color_important);
        $('.picker__button--today').attr('style', 'color: ' + color_important);
        $('.ab-extra-step .bookly-extras-thumb.bookly-extras-selected').css('border-color', $color_picker.wpColorPicker('color'));
        $('.ab-columnizer .ab-day').css({
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
        $('.ab-nav-tabs .ladda-button, .ab-nav-steps .ladda-button, .ab-btn, .bookly-round-button').css('background-color', $color_picker.wpColorPicker('color'));
        $('.ab-back-step, .ab-next-step').css('background', $color_picker.wpColorPicker('color'));
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
            action: 'ab_update_appearance_options',
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
                // Validator.
                'text_required_location'       : getEditableValue($('#ab_appearance_text_required_location').html()),
                'text_required_service'        : getEditableValue($('#ab_appearance_text_required_service').html()),
                'text_required_employee'       : getEditableValue($('#ab_appearance_text_required_employee').html()),
                'text_required_name'           : getEditableValue($('#ab_appearance_text_required_name').html()),
                'text_required_phone'          : getEditableValue($('#ab_appearance_text_required_phone').html()),
                'text_required_email'          : getEditableValue($('#ab_appearance_text_required_email').html()),
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
        jQuery.each($('.editable'), function() {
            $(this).text($(this).data('default')); //default value for texts
            $(this).editable('setValue', $(this).data('default')); // default value for editable inputs
        });

        // Reset texts.
        jQuery.each($('.ab-service-list, .ab-employee-list'), function() {
            $(this).html($(this).data('default')); //default value
        });

        // default value for multiple inputs

        $text_label_location.editable('setValue', {
            label: $text_label_location.text(),
            option: $text_option_location.text(),
            id_option: $text_label_location.data('option-id'),
            extended: true,
            option2: $text_label_location.data('default-error'),
            id_option2: $text_label_location.data('error-id')
        });

        $text_label_category.editable('setValue', {
            label: $text_label_category.text(),
            option: $text_option_category.text(),
            id_option: $text_label_category.data('option-id')
        });

        $text_label_service.editable('setValue', {
            label: $text_label_service.text(),
            option: $text_option_service.text(),
            id_option: $text_label_service.data('option-id'),
            extended: true,
            option2: $text_label_service.data('default-error'),
            id_option2: $text_label_service.data('error-id')
        });

        $text_label_employee.editable('setValue', {
            label: $text_label_employee.text(),
            option: $text_option_employee.text(),
            id_option: $text_label_employee.data('option-id'),
            extended: true,
            option2: $text_label_employee.data('default-error'),
            id_option2: $text_label_employee.data('error-id')
        });

        $text_label_name.editable('setValue', {
            label: $text_label_name.text(),
            option: $text_label_name.data('default-error'),
            id_option: $text_label_name.data('option-id')
        });

        $text_label_phone.editable('setValue', {
            label: $text_label_phone.text(),
            option: $text_label_phone.data('default-error'),
            id_option: $text_label_phone.data('option-id')
        });

        $text_label_email.editable('setValue', {
            label: $text_label_email.text(),
            option: $text_label_email.data('default-error'),
            id_option: $text_label_email.data('option-id')
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
    $('.ab-week-day').on('change', function () {
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

    // Inherit from Abstract input.
    $.fn.editableutils.inherit(multiple, $.fn.editabletypes.abstractinput);

    $.extend(multiple.prototype, {
        render: function() {
            this.$input = this.$tpl.find('input');
            this.$more = jQuery('div.ad--extend', this.tpl);
        },

        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return;
            }
            $(element).text(value.label);
            $('#' + value.id_option).text(value.option);
            $('#' + value.id_option2).text(value.option2);
        },

        activate: function () {
            this.$input.filter('[name="label"]').focus();
        },

        value2input: function(value) {
            if(!value) {
                return;
            }
            if (value.extended) {
                this.$more.show();
            }
            this.$input.filter('[name="label"]').val(value.label);
            this.$input.filter('[name="option"]').val(value.option);
            this.$input.filter('[name="id_option"]').val(value.id_option);
            this.$input.filter('[name="option2"]').val(value.option2);
            this.$input.filter('[name="id_option2"]').val(value.id_option2);
        },

        input2value: function() {
            return {
                label:      this.$input.filter('[name="label"]').val(),
                option:     this.$input.filter('[name="option"]').val(),
                id_option:  this.$input.filter('[name="id_option"]').val(),
                option2:    this.$input.filter('[name="option2"]').val(),
                id_option2: this.$input.filter('[name="id_option2"]').val(),
                extended:   this.$more.is(':visible')
            };
        }
    });

    multiple.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-multiple"><input type="text" name="label" class="form-control input-sm" /></div>'+
        '<div style="margin-top:5px;" class="editable-multiple"><input type="text" name="option" class="form-control input-sm" /><input type="hidden" name="id_option" /></div><div class="ad--extend" style="display:none">'+
        '<div style="margin-top:5px;" class="editable-multiple"><input type="text" name="option2" class="form-control input-sm" /><input type="hidden" name="id_option2" /></div></div></div>',

        inputclass: ''
    });

    $.fn.editabletypes.multiple = multiple;
    $text_label_location.editable({
        value: {
            label: $text_label_location.text(),
            option: $text_option_location.text(),
            id_option: $text_label_location.data('option-id'),
            extended: true,
            option2: $text_label_location.data('default-error'),
            id_option2: $text_label_location.data('error-id')
        }
    });
    $text_label_category.editable({
        value: {
            label: $text_label_category.text(),
            option: $text_option_category.text(),
            id_option: $text_label_category.data('option-id')
        }
    });
    $text_label_service.editable({
        value: {
            label: $text_label_service.text(),
            option: $text_option_service.text(),
            id_option: $text_label_service.data('option-id'),
            extended: true,
            option2: $text_label_service.data('default-error'),
            id_option2: $text_label_service.data('error-id')
        }
    });
    $text_label_employee.editable({
        value: {
            label: $text_label_employee.text(),
            option: $text_option_employee.text(),
            id_option: $text_label_employee.data('option-id'),
            extended: true,
            option2: $text_label_employee.data('default-error'),
            id_option2: $text_label_employee.data('error-id')
        }
    });

    $text_label_name.editable({
        value: {
            label: $text_label_name.text(),
            option: $text_label_name.data('default-error'),
            id_option: $text_label_name.data('option-id')
        }
    });

    $text_label_phone.editable({
        value: {
            label: $text_label_phone.text(),
            option: $text_label_phone.data('default-error'),
            id_option: $text_label_phone.data('option-id')
        }
    });

    $text_label_email.editable({
        value: {
            label: $text_label_email.text(),
            option: $text_label_email.data('default-error'),
            id_option: $text_label_email.data('option-id')
        }
    });

    $text_info_service.add('#ab-text-info-time').add('#ab-text-info-details').add('#ab-text-info-payment').add('#ab-text-info-complete').add('#ab-text-info-coupon').editable({placement: 'right'});
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

    $("[data-mirror^='text_']").on('save', function (e, params) {
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
            data: { action: 'ab_dismiss_appearance_notice' }
        });
    })
}); // jQuery
