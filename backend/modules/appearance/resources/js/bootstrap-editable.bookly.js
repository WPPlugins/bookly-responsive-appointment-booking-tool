jQuery(function($) {
    var Bookly = function (options) {
        this.init('bookly', options, Bookly.defaults);
    };

    // Inherit from Abstract input.
    $.fn.editableutils.inherit(Bookly, $.fn.editabletypes.abstractinput);

    $.extend(Bookly.prototype, {
        html2value: function(html) {
            return $.extend({}, $(this.options.scope).data('values'));
        },
        value2html: function (values, element) {
            $.each(values, function (option_name, option_value) {
                // Find all elements which display option value.
                $('.bookly-js-option[data-option="' + option_name + '"]').each(function () {
                    var $this = $(this);
                    if (!$this.hasClass('editable') || $this.is(element)) {
                        // Update text.
                        $this.text(option_value);
                    }
                });
            });
        },
        activate: function () {
            this.$tpl.find(':input:first').focus();
        },
        value2input: function (values) {
            var _this = this;
            this.$tpl.empty();
            $.each(values, function (option_name, option_value) {
                var $row = $('<div/>')
                    .css({position: 'relative', 'margin-top': '6px'})
                    .appendTo(_this.$tpl);
                switch (_this.options.fieldType) {
                    case 'input':
                        // Create input with "x" button.
                        var $clear = $('<span class="editable-clear-x"></span>');
                        var $input = $('<input/>', {
                            type : 'text',
                            class: 'form-control',
                            name : option_name,
                            value: option_value
                        });
                        $input.keyup(function(e) {
                            // arrows, enter, tab, etc
                            if (~$.inArray(e.keyCode, [40,38,9,13,27])) {
                                return;
                            }
                            clearTimeout(this.t);
                            this.t = setTimeout(function() {
                                var len = $input.val().length,
                                    visible = $clear.is(':visible');
                                if (len && !visible) {
                                    $clear.show();
                                }
                                if (!len && visible) {
                                    $clear.hide();
                                }
                            }, 100);
                        });
                        $clear.click(function () {
                            $clear.hide();
                            $input.val('').focus();
                        });
                        $row.append($input).append($clear);
                        break;
                    case 'number':
                        // Create input[type="number"]
                        $('<input/>', {
                            type : 'number',
                            class: 'form-control',
                            name : option_name,
                            min  : $(_this.options.scope).data('min'),
                            step : $(_this.options.scope).data('step')
                        }).val(option_value).appendTo($row);
                        break;
                    default :
                        // Create textarea.
                        $('<textarea/>', {
                            class: 'form-control',
                            name : option_name,
                            rows : 7
                        }).val(option_value).appendTo($row);
                        break;
                }
            });
            // Set codes.
            this.$tpl.closest('form').find('.bookly-js-codes').html($(this.options.scope).data('codes'));
        },
        input2value: function () {
            var _this  = this;
            var values = {};
            this.$tpl.find(':input').each(function () {
                values[this.name] = this.value;
                // Find all elements which display option value.
                var option_name  = this.name;
                var option_value = this.value;
                $('.bookly-js-option.' + option_name).each(function () {
                    var $this = $(this);
                    if ($this.hasClass('editable') && !$this.is(_this.$tpl)) {
                        // Update editable value.
                        var val = $this.editable('getValue', true);
                        val[option_name] = option_value;
                        $this.editable('setValue', val);
                    }
                });
            });

            return values;
        }
    });

    Bookly.defaults = $.extend({}, $.fn.editabletypes.abstractinput.defaults, {
        tpl:       '<div/>',
        fieldType: 'input'
    });

    $.fn.editabletypes.bookly = Bookly;

    // Set template for popovers and editable form.
    $.fn.popover.Constructor.DEFAULTS.template = '<div class="popover"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>';
    $.fn.editableform.template = '<form class="form-inline editableform"><div class="control-group"><div><div class="editable-input"></div><div class="editable-buttons"></div></div><div class="bookly-js-codes"></div><div class="editable-error-block"></div></div></form>';
    $.fn.editableform.buttons = '<div class="btn-group btn-group-sm"><button type="submit" class="btn btn-success editable-submit"><span class="glyphicon glyphicon-ok"></span></button><button type="button" class="btn btn-default editable-cancel"><span class="glyphicon glyphicon-remove"></span></button></div>';
});