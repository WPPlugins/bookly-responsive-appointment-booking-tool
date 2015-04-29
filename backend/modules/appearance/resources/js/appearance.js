jQuery(function($) {
    var // Progress Tracker
        $progress_tracker_option = $('input#ab-progress-tracker-checkbox'),
        // Tabs
        $tabs = $('div.tabbable').find('.nav-tabs'),
        $tab_content = $('div.tab-content'),
        // Buttons.
        $update_button = $('#update_button'),
        $reset_button = $('#reset_button'),
        // Texts.
        $text_step_service = $('#ab-text-step-service'),
        $text_step_time = $('#ab-text-step-time'),
        $text_step_details = $('#ab-text-step-details'),
        $text_step_payment = $('#ab-text-step-payment'),
        $text_step_done = $('#ab-text-step-done'),
        $text_label_category = $('#ab-text-label-category'),
        $text_option_category = $('#ab-text-option-category'),
        $text_option_service = $('#ab-text-option-service'),
        $text_option_employee = $('#ab-text-option-employee'),
        $text_label_service = $('#ab-text-label-service'),
        $text_label_employee = $('#ab-text-label-employee'),
        $text_label_select_date = $('#ab-text-label-select_date'),
        $text_label_start_from = $('#ab-text-label-start_from'),
        $text_label_finish_by = $('#ab-text-label-finish_by'),
        $text_label_name = $('#ab-text-label-name'),
        $text_label_phone = $('#ab-text-label-phone'),
        $text_label_email = $('#ab-text-label-email'),
        $text_label_notes = $('#ab-text-label-notes'),
        $text_label_coupon = $('#ab-text-label-coupon'),
        $text_info_service = $('#ab-text-info-first'),
        $text_info_time = $('#ab-text-info-second'),
        $text_info_details = $('#ab-text-info-third'),
        $text_info_payment = $('#ab-text-info-fourth'),
        $text_info_done = $('#ab-text-info-fifth'),
        $text_info_coupon = $('#ab-text-info-coupon'),
        $color_picker = $('.wp-color-picker'),
        $ab_editable  = $('.ab_editable');

    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    // Tabs
    $tabs.find('.ab-step-tabs').on('click', function() {
        var $step_id = $(this).data('step-id');
        // hide all other tab content and show only current
        $tab_content.children('div[data-step-id!="' + $step_id + '"]').removeClass('active').hide();
        $tab_content.children('div[data-step-id="' + $step_id + '"]').addClass('active').show();
    }).filter('li:first').trigger('click');

    // Apply color from color picker.
    var applyColor = function() {
        var color_important = $color_picker.wpColorPicker('color') + '!important';
        $('div.ab-progress-tracker').find('li.ab-step-tabs').filter('.active').find('a').css('color', $color_picker.wpColorPicker('color'));
        $('div.ab-progress-tracker').find('li.ab-step-tabs').filter('.active').find('div.step').css('background', $color_picker.wpColorPicker('color'));
        $('.ab-mobile-step_1 label').css('color', $color_picker.wpColorPicker('color'));
        $('.ab-next-step, .ab-mobile-next-step').css('background', $color_picker.wpColorPicker('color'));
        $('.ab-week-days label').css('background-color', $color_picker.wpColorPicker('color'));
        $('.pickadate__calendar').attr('style', 'background: ' + color_important);
        $('.pickadate__header').attr('style', 'border-bottom: ' + '1px solid ' + color_important);
//        $('.pickadate__nav--next, .pickadate__nav--prev').attr('style', 'border-left: 6px solid ' + color_important);
//        $('.pickadate__nav--next:before').attr('style', 'border-left: 6px solid ' + color_important);
//        $('.pickadate__nav--prev:before').attr('style', 'border-right: 6px solid ' + color_important);
//        $('.pickadate__day:hover').attr('style', 'color: ' + color_important);
//        $('.pickadate__day--selected:hover').attr('style', '');
        $('.pickadate__day--selected').attr('style', 'color: ' + color_important);
        $('.pickadate__button--clear').attr('style', 'color: ' + color_important);
        $('.pickadate__button--today').attr('style', 'color: ' + color_important);
        $('.ab-columnizer .ab-available-day').css({
            'background': $color_picker.wpColorPicker('color'),
            'border-color': $color_picker.wpColorPicker('color')
        });
        $('.ladda-button').css('background-color', $color_picker.wpColorPicker('color'));
        $('.ab-columnizer .ab-available-hour').off().hover(
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
                    'border': '1px solid ' + '#cccccc'
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
        $('div.ab-formGroup > label.ab-formLabel').css('color', $color_picker.wpColorPicker('color'));
        $('.ab-to-second-step, .ab-to-fourth-step, .ab-to-third-step, .ab-final-step')
            .css('background', $color_picker.wpColorPicker('color'));
    };
    $color_picker.wpColorPicker({
        change : function() {
            applyColor();
        }
    });

    $('.ab-requested-date-from').pickadate({
        dateMin: true,
        clear: false,
        onRender: function() {
            applyColor();
        }
    });

    // Update options.
    $update_button.on('click', function() {
        var data = {
            action: 'ab_update_appearance_options',
            options: {
                // Color.
                'color'                  : $color_picker.wpColorPicker('color'),
                // Info text.
                'text_info_first_step'   : $.trim($text_info_service.text() == 'Empty' ? '' : $text_info_service.text()),
                'text_info_second_step'  : $.trim($text_info_time.text() == 'Empty' ? '' : $text_info_time.text()),
                'text_info_third_step'   : $.trim($text_info_details.text() == 'Empty' ? '' : $text_info_details.text()),
                'text_info_fourth_step'  : $.trim($text_info_payment.text() == 'Empty' ? '' : $text_info_payment.text()),
                'text_info_fifth_step'   : $.trim($text_info_done.text() == 'Empty' ? '' : $text_info_done.text()),
                'text_info_coupon'       : $.trim($text_info_coupon.text() == 'Empty' ? '' : $text_info_coupon.text()),
                // Step and label texts.
                'text_step_service'      : $.trim($text_step_service.text() == 'Empty' ? '' : $text_step_service.text()),
                'text_step_time'         : $.trim($text_step_time.text() == 'Empty' ? '' : $text_step_time.text()),
                'text_step_details'      : $.trim($text_step_details.text() == 'Empty' ? '' : $text_step_details.text()),
                'text_step_payment'      : $.trim($text_step_payment.text() == 'Empty' ? '' : $text_step_payment.text()),
                'text_step_done'         : $.trim($text_step_done.text() == 'Empty' ? '' : $text_step_done.text()),
                'text_label_category'    : $.trim($text_label_category.text() == 'Empty' ? '' : $text_label_category.text()),
                'text_label_service'     : $.trim($text_label_service.text() == 'Empty' ? '' : $text_label_service.text()),
                'text_label_employee'    : $.trim($text_label_employee.text() == 'Empty' ? '' : $text_label_employee.text()),
                'text_label_select_date' : $.trim($text_label_select_date.text() == 'Empty' ? '' : $text_label_select_date.text()),
                'text_label_start_from'  : $.trim($text_label_start_from.text() == 'Empty' ? '' : $text_label_start_from.text()),
                'text_label_finish_by'   : $.trim($text_label_finish_by.text() == 'Empty' ? '' : $text_label_finish_by.text()),
                'text_label_name'        : $.trim($text_label_name.text() == 'Empty' ? '' : $text_label_name.text()),
                'text_label_phone'       : $.trim($text_label_phone.text() == 'Empty' ? '' : $text_label_phone.text()),
                'text_label_email'       : $.trim($text_label_email.text() == 'Empty' ? '' : $text_label_email.text()),
                'text_label_notes'       : $.trim($text_label_notes.text() == 'Empty' ? '' : $text_label_notes.text()),
                'text_label_coupon'      : $.trim($text_label_coupon.text() == 'Empty' ? '' : $text_label_coupon.text()),
                'text_option_category'   : $.trim($text_option_category.text() == 'Empty' ? '' : $text_option_category.text()),
                'text_option_service'    : $.trim($text_option_service.text() == 'Empty' ? '' : $text_option_service.text()),
                'text_option_employee'   : $.trim($text_option_employee.text() == 'Empty' ? '' : $text_option_employee.text()),
                // Checkboxes.
                'progress_tracker'       : Number($('#ab-progress-tracker-checkbox').is(':checked'))
           } // options
        }; // data

        // update data and show spinner while updating
        $('#update_spinner').show();
        $.post(ajaxurl, data, function (response) {
            $('#update_spinner').hide();
            $('.alert').show();
        });
    });

    // Reset options to defaults.
    $reset_button.on('click', function() {
        // Reset color.
        $color_picker.wpColorPicker('color', $color_picker.data('selected'));

        // Reset texts.
        jQuery.each($('.editable'), function() {
            var $default_value  = $(this).data('default'),
                $steps          = $(this).data('link-class');

            $(this).text($default_value); //default value for texts
            $('.' + $steps).text($default_value); //default value for steps
            $(this).editable('setValue', $default_value); // default value for editable inputs
        });

        // default value for multiple inputs
        $text_label_category.editable('setValue', {
            label: $text_label_category.text(),
            option: $text_option_category.text(),
            id_option: $text_label_category.data('link-class')
        });

        $text_label_service.editable('setValue', {
            label: $text_label_service.text(),
            option: $text_option_service.text(),
            id_option: $text_label_service.data('link-class')
        });

        $text_label_employee.editable('setValue', {
            label: $text_label_employee.text(),
            option: $text_option_employee.text(),
            id_option: $text_label_employee.data('link-class')
        });

    });

    $progress_tracker_option.change(function(){
        $(this).is(':checked') ? $('div.ab-progress-tracker').show() : $('div.ab-progress-tracker').hide();
    }).trigger('change');

    // Clickable week-days.
    $('.ab-week-day').on('change', function () {
        var self = $(this);
        if (self.is(':checked') && !self.parent().hasClass('active')) {
            self.parent().addClass('active');
        } else if (self.parent().hasClass('active')) {
            self.parent().removeClass('active')
        }
    });

    /**
     * Helper functions.
     */
    function nl2br(str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
    }
    function escapeHtml(string) {
        var entityMap = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': '&quot;',
            "'": '&#39;',
            "/": '&#x2F;'
        };
        return String(string).replace(/[&<>"'\/]/g, function (s) {
            return entityMap[s];
        });
    }

    var multiple = function (options) {
        this.init('multiple', options, multiple.defaults);
    };

    //inherit from Abstract input
    $.fn.editableutils.inherit(multiple, $.fn.editabletypes.abstractinput);

    $.extend(multiple.prototype, {
        render: function() {
            this.$input = this.$tpl.find('input');
        },

        value2html: function(value, element) {
            if(!value) {
                $(element).empty();
                return;
            }
            $(element).text(value.label);
            $('#' + value.id_option).text(value.option);
        },

        activate: function () {
            this.$input.filter('[name="label"]').focus();
        },

        value2input: function(value) {
            if(!value) {
                return;
            }
            this.$input.filter('[name="label"]').val(value.label);
            this.$input.filter('[name="option"]').val(value.option);
            this.$input.filter('[name="id_option"]').val(value.id_option);
        },

        input2value: function() {
            return {
                label: this.$input.filter('[name="label"]').val(),
                option: this.$input.filter('[name="option"]').val(),
                id_option: this.$input.filter('[name="id_option"]').val()
            };
        }
    });

    multiple.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl: '<div class="editable-multiple"><label><input type="text" name="label" class="input-medium"></label></div>'+
            '<div style="margin-top:5px;" class="editable-multiple"><label><input type="text" name="option" class="input-medium"><input type="hidden" name="id_option"></label></div>',

        inputclass: ''
    });

    $.fn.editabletypes.multiple = multiple;

    $text_label_category.editable({
        value: {
            label: $text_label_category.text(),
            option: $text_option_category.text(),
            id_option: $text_label_category.data('link-class')
        }
    });
    $text_label_service.editable({
        value: {
            label: $text_label_service.text(),
            option: $text_option_service.text(),
            id_option: $text_label_service.data('link-class')
        }
    });
    $text_label_employee.editable({
        value: {
            label: $text_label_employee.text(),
            option: $text_option_employee.text(),
            id_option: $text_label_employee.data('link-class')
        }
    });

    $('#ab-text-info-first').add('#ab-text-info-second').add('#ab-text-info-third').add('#ab-text-info-fourth').add('#ab-text-info-fifth').add('#ab-text-info-coupon').editable({placement: 'right'});
    $ab_editable.editable();

    $.fn.editableform.template = '<form class="form-inline editableform"> <div class="control-group"> <div> <div class="editable-input"></div><div class="editable-buttons"></div></div><div style="margin-top: 10px;" class="editable-notes"></div><div class="editable-error-block"></div></div> </form>';

    $ab_editable.on('shown', function(e, editable) {
        $('.editable-notes').html($(e.target).data('notes'));
    });

    $("span[data-link-class^='text_step_']").on('save', function(e, params) {
        $("span[data-link-class='" + $(e.target).data('link-class') + "']").editable('setValue', params.newValue);
        $("span." + $(e.target).data('link-class')).text(params.newValue);
    });

    if(jQuery('.ab-authorizenet-payment').is(':checked')) {
        jQuery('form.authorizenet').show();
    }

    if(jQuery('.ab-stripe-payment').is(':checked')) {
        jQuery('form.stripe').show();
    }

    jQuery('input[type=radio]').change( function() {
        jQuery('form.authorizenet').add('form.stripe').hide();
        if(jQuery('.ab-authorizenet-payment').is(':checked')) {
            jQuery('form.authorizenet').show();
        } else if(jQuery('.ab-stripe-payment').is(':checked')) {
            jQuery('form.stripe').show();
        }
    });
}); // jQuery