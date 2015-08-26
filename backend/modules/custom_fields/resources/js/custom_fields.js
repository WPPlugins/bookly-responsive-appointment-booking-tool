jQuery(function($) {

    var $fields = $("ul#ab-custom-fields");

    $fields.sortable({
        axis   : 'y',
        handle : '.ab-handle'
    });

    /**
     * Build initial fields.
     */
    restoreFields();

    /**
     * On "Add new field" button click.
     */
    $('#ab-add-fields').on('click', 'button', function() {
        addField($(this).data('type'));
    });

    /**
     * On "Add new item" button click.
     */
    $fields.on('click', 'button', function() {
        addItem($(this).prev('ul'), $(this).data('type'));
    });

    /**
     * Delete field or checkbox/radio button/drop-down option.
     */
    $fields.on('click', '.ab-delete', function() {
        $(this).closest('li').fadeOut('fast', function() { $(this).remove(); });
    });

    /**
     * Submit custom fields form.
     */
    $('#ajax-send-custom-fields').on('click', function(e) {
        e.preventDefault();
        var data = [];
        $fields.children('li').each(function() {
            var $this = $(this);
            var field = {};
            switch ($this.data('type')) {
                case 'checkboxes':
                case 'radio-buttons':
                case 'drop-down':
                    field.items = [];
                    $this.find('li').each(function() {
                        field.items.push($(this).find('input').val());
                    });
                case 'text-field':
                case 'textarea':
                    field.type     = $this.data('type');
                    field.label    = $this.find('.ab-label').val();
                    field.required = $this.find('.ab-required').prop('checked');
                    field.id       = $this.data('ab-field-id');
            }
            data.push(field);
        });

        var ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            type      : 'POST',
            url       : ajaxurl,
            xhrFields : { withCredentials: true },
            data      : { action: 'ab_save_custom_fields', fields: JSON.stringify(data) },
            complete  : function() {
                ladda.stop();
            }
        });
    });

    /**
     * On 'Reset' click.
     */
    $('button[type=reset]').on('click', function() {
        $fields.empty();
        restoreFields();
    });

    /**
     * Add new field.
     *
     * @param type
     * @param id
     * @param label
     * @param required
     * @return {*|jQuery}
     */
    function addField(type, id, label, required) {
        var $new_field = $('ul#ab-templates > li[data-type=' + type + ']').clone();
        // Set id, label and required.
        if (typeof id == 'undefined') {
            id = Math.floor((Math.random() * 100000) + 1);
        }
        if (typeof label == 'undefined') {
            label = '';
        }
        if (typeof required == 'undefined') {
            required = false;
        }
        $new_field
            .hide()
            .data('ab-field-id', id)
            .find('.ab-required').prop({
                id      : 'required-' + id,
                checked : required
            })
            .next('label').attr('for', 'required-' + id)
            .end().end()
            .find('.ab-label').val(label);
        // Add new field to the list.
        $fields.append($new_field);
        $new_field.fadeIn('fast');
        // Make it sortable.
        $new_field.find('ul.ab-items').sortable({
            axis   : 'y',
            handle : '.ab-inner-handle'
        });
        // Set focus to label field.
        $new_field.find('.ab-label').focus();

        return $new_field;
    }

    /**
     * Add new checkbox/radio button/drop-down option.
     *
     * @param $ul
     * @param type
     * @param value
     * @return {*|jQuery}
     */
    function addItem($ul, type, value) {
        var $new_item = $('ul#ab-templates > li[data-type=' + type + ']').clone();
        if (typeof value != 'undefined') {
            $new_item.find('input').val(value);
        }
        $new_item.hide().appendTo($ul).fadeIn('fast').find('input').focus();

        return $new_item;
    }

    /**
     * Restore fields from BooklyL10n.custom_fields.
     */
    function restoreFields() {
        if (BooklyL10n.custom_fields) {
            var custom_fields = jQuery.parseJSON(BooklyL10n.custom_fields);
            $.each(custom_fields, function(i, field) {
                var $new_field = addField(field.type, field.id, field.label, field.required);

                //add children
                if (field.items) {
                    $.each(field.items, function(i, value) {
                        addItem($new_field.find('ul'), field.type + '-item', value);
                    });
                }
            });
        }
        $(':focus').blur();
    }
});
