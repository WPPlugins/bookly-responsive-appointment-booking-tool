jQuery(function($) {
    var $no_result = $('#ab_services_wrapper .no-result');

    // On new category form submit.
    $('#new-category-form').on('submit', function(event) {
        var data = $(this).serialize();
        $.post(ajaxurl, data, function(response) {
            $('.ab-category-item-list').append(response);
            $('#new_category_popup').ab_popup('close');
            // add created category to services
            $.each($('#services_list').find('select[name="category_id"]'), function(key, value) {
                var $new_category = $('.ab-category-item:last');
                $(value).append('<option value="' + $new_category.data('id') + '">'
                    + $new_category.find('input').val() + ' </option>');
            });
        });
        return false;
    });

    // Preventing multiple creation of new category by pressing Enter-key
    $('input[value="ab_category_form"]').parent().find('input:first').one('keypress', function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) {
            $(this).trigger(e).blur();
        }
    });

    // Categories list delegated events.
    $('#ab-categories-list')

        // On category item click.
        .on('click', '.ab-category-item', function() {
            var $clicked = $(this);
            $.get(ajaxurl, {action:'ab_category_services', category_id: $clicked.data('id')}, function(response) {
                $('.ab-category-item').not($clicked).removeClass('ab-active');
                $('.ab-category-title').text($clicked.text());
                $clicked.addClass('ab-active');
                refreshList(response);
            });
        })

        // On edit category click.
        .on('click', '.ab-category-item .ab-edit', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            // Hide edit button.
            $(this).hide()
                // Hide displayed category name and delete button.
                .siblings('.displayed-value, .ab-delete').hide().end()
                // Show input field.
                .nextAll('.value').show().focus();
        })

        // On blur of category edit input.
        .on('blur', '.ab-category-item input.value', function() {
            var $this = $(this),
                $item = $this.closest('.ab-category-item'),
                field = $this.attr('name'),
                value = $this.attr('value'),
                id    = $item.data('id');
            if (value) {
                var data = { action: 'ab_update_category', id: id };
                data[field] = value;
                $.post(ajaxurl, data, function(response) {
                    // Hide input field.
                    $this.hide()
                        // Show modified category name.
                        .prevAll('.displayed-value').text(value).show().end()
                        // Show edit and delete buttons.
                        .siblings('.ab-edit, .ab-delete').show();
                    // update edited category's name for services
                    $.each($('#services_list').find('select[name="category_id"]'), function(k, v) {
                        $(v).find('option:selected[value="' + id + '"]').text(value);
                    });
                });
            }
        })

        // On delete category click.
        .on('click', '.ab-category-item .ab-delete', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            // Ask user if he is sure.
            if (confirm(BooklyL10n.are_you_sure)) {
                var $item = $(this).closest('.ab-category-item');
                var data = { action: 'ab_delete_category', id: $item.data('id') };
                $.post(ajaxurl, data, function(response) {
                    // Remove category item from Services
                    $.each($('#services_list').find('select[name="category_id"]'), function(key, value) {
                        $(value).find('option[value="' + $item.data('id') + '"]').remove();
                    });
                    // Remove category item from DOM.
                    $item.remove();
                    if ($item.is('.ab-active')) {
                        location.reload(true);
                    }
                });
            }
        });


    // Services list delegated events.
    $('#ab_services_wrapper')

        // On click on editable cell.
        .on('click', '.editable-cell div.displayed-value', function() {
            var $this = $(this);
            $this.hide().next('.value').show();
            // Fix FF accidental blur of input[type=number].
            setTimeout( function() { $this.next('.value').focus(); }, 100 );
        })

        // On blur of input in editable cell.
        .on('blur', '.editable-cell input.value', function() {
            var $this = $(this),
                field = $this.attr('name'),
                value = $this.attr('value'),
                id    = $this.parents('.service-row').attr('id');
            if (value) {
                var data = { action: 'ab_update_service_value', id: id };
                data[field] = value;
                $.post(ajaxurl, data, function(response) {
                    $this.hide();
                    $this.prev('.displayed-value').text(value).show();
                });
            }
        })

        // On change in 'Duration' or 'Category' drop-down lists.
        .on('change', 'select', function() {
            var $this = $(this),
                field = $this.attr('name'),
                value = $this.val(),
                $row  = $this.parents('.service-row'),
                id    = $row.attr('id');
            if (value) {
                var data = { action: 'ab_update_service_value', id: id };
                data[field] = value;
                $.post(ajaxurl, data, function(response) {
                    if ($this.attr('name') == 'category_id') {
                        var services_category_id = parseInt($('.ab-category-item.ab-active').data('id')),
                            selected_category_id = parseInt(value);
                        if (services_category_id && selected_category_id != services_category_id) {
                            if ($('#services_list > tbody > tr').length == 1) {
                                $('#services_list > tbody > tr').remove();
                                $('#services_list').hide();
                                $no_result.show();
                            } else {
                                $row.removeClass('last').prev().addClass('last');
                                $row.remove();
                            }
                        }
                    }
                });
            }
        })

        // On click on 'Add Service' button.
        .on('click', 'a.add-service', function(e) {
            e.preventDefault();
            var selected_category_id = $('#ab-categories-list .ab-active').data('id'),
                data = { action: 'ab_add_service' };
            if (selected_category_id) {
                data['category_id'] = selected_category_id;
            }
            $.post(ajaxurl, data, function(response) {
                refreshList(response);
            });
        })

        // On change in `select row` checkbox.
        .on('change', 'input.row-checker', function() {
            if ($(this).attr('checked')) {
                $(this).parents('.service-row').addClass('checked');
            } else {
                $(this).parents('.service-row').removeClass('checked');
            }
        })

        // On click on 'Delete' button.
        .on('click', 'a.delete', function(e){
            e.preventDefault();
            var $checked_rows = $('#services_list .service-row.checked');
            if (!$checked_rows.length) {
                alert(BooklyL10n.please_select_at_least_one_service);
                return false;
            }
            var selected_category_id = $('#ab-categories-list .ab-active').data('id'),
                data = { action: 'ab_remove_services' },
                row_ids = [];
            $checked_rows.each(function() {
                row_ids.push($(this).attr('id'));
            });
            if (selected_category_id) {
                data['category_id'] = selected_category_id;
            }
            data['service_ids[]'] = row_ids;
            $.post(ajaxurl, data, function() {
                $checked_rows.fadeOut(700, function() {
                    $(this).each(function() {
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('last').prev().addClass('last');
                        }
                    });
                    $(this).remove();
                    $('#services_list .service-row').removeClass('even odd').each(function(index, value) {
                        if (index % 2) {
                            $(this).addClass('even');
                        } else {
                            $(this).addClass('odd');
                        }
                    });
                    if (!$('#services_list > tbody > tr').length) {
                        $('#services_list').hide();
                        $no_result.show();
                    }
                });
            });
        })

        // On change in `select staff` checkbox.
        .on('change', 'input.all-staff, input.staff', function(){
            var $this = $(this),
                $row = $this.parents('.service-row'),
                staff_ids = [],
                data = { action: 'ab_assign_staff', service_id: $row.attr('id') };
            if ($this.hasClass('all-staff')) {
                $row.find('.staff').prop('checked', $this.prop('checked'));
            } else {
                $row.find('.all-staff').prop(
                    'checked',
                    $row.find('.staff:not(:checked)').length == 0
                );
            }
            $row.find('.staff:checked').each(function(){
                staff_ids.push($(this).val());
            });
            data['staff_ids[]'] = staff_ids;
            $.post(ajaxurl, data, function(response) {
                if (response) {
                    $row.find('.staff-count').text(response);
                }
            });
        })

        .on('change', '[name=capacity]', function(){
            $('#lite_notice').modal('show');
            $(this).val(1);
        });

    function refreshList(response) {
        var $list = $('#ab-services-list');
        $list.html(response);
        doNotCloseDropDowns();
        initColorPicker($list.find('.service-color'));
        initPopovers();

        if (response) {
            $no_result.hide();
        } else {
            $no_result.show();
        }
    }

    function initColorPicker($jquery_collection) {
        $jquery_collection.wpColorPicker({
            change: function() {
                var data = {
                    action :'ab_update_service_value',
                    id     : $(this).parents('.service-row').first().attr('id')
                };
                data['color'] = $(this).wpColorPicker('color');
                $.post(ajaxurl, data);
            }
        });
    }

    function doNotCloseDropDowns() {
        $('#ab-services-list .dropdown-menu').on('click', function(e) {
            e.stopPropagation();
        });
    }

    function initPopovers() {
        // Popovers initialization.
        $('.ab-popover').popover({
            trigger : 'hover'
        });
    }

    doNotCloseDropDowns();
    initColorPicker($('.service-color'));
    initPopovers();

    $.ajaxSetup({
        mode: 'abort',
        port: 'ab_service'
    });
});
