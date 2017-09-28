jQuery(function($) {
    var $no_result = $('#bookly-services-wrapper .no-result');
    // Remember user choice in the modal dialog.
    var update_staff_choice = null,
        $new_category_popover = $('#bookly-new-category'),
        $new_category_form = $('#new-category-form'),
        $new_category_name = $('#bookly-category-name');

    $new_category_popover.popover({
        html: true,
        placement: 'bottom',
        template: '<div class="popover" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
        content: $new_category_form.show().detach(),
        trigger: 'manual'
    }).on('click', function () {
        $(this).popover('toggle');
    }).on('shown.bs.popover', function () {
        // focus input
        $new_category_name.focus();
    }).on('hidden.bs.popover', function (e) {
        //clear input
        $new_category_name.val('');
    });

    // Save new category.
    $new_category_form.on('submit', function() {
        var data = $(this).serialize();

        $.post(ajaxurl, data, function(response) {
            $('#bookly-category-item-list').append(response);
            var $new_category = $('.bookly-category-item:last');
            // add created category to services
            $('select[name="category_id"]').append('<option value="' + $new_category.data('category-id') + '">' + $new_category.find('input').val() + '</option>');
        });
        $new_category_popover.popover('hide');
        return false;
    });

    // Cancel button.
    $new_category_form.on('click', 'button[type="button"]', function (e) {
        $new_category_popover.popover('hide');
    });

    // Categories list delegated events.
    $('#bookly-categories-list')

        // On category item click.
        .on('click', '.bookly-category-item', function(e) {
            if ($(e.target).is('.bookly-js-handle')) return;
            $('#ab-services-list').html('<div class="bookly-loading"></div>');
            var $clicked = $(this);

            $.get(ajaxurl, {action:'bookly_get_category_services', category_id: $clicked.data('category-id')}, function(response) {
                if ( response.success ) {
                    $('.bookly-category-item').not($clicked).removeClass('active');
                    $clicked.addClass('active');
                    $('.bookly-category-title').text($clicked.text());
                    refreshList(response.data, 0);
                }
            });
        })

        // On edit category click.
        .on('click', '.bookly-js-edit', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            var $this = $(this).closest('.bookly-category-item');
            $this.find('.displayed-value').hide();
            $this.find('input').show().focus();
        })

        // On blur save changes.
        .on('blur', 'input', function() {
            var $this = $(this),
                $item = $this.closest('.bookly-category-item'),
                field = $this.attr('name'),
                value = $this.val(),
                id    = $item.data('category-id'),
                data  = { action: 'bookly_update_category', id: id };
            data[field] = value;
            $.post(ajaxurl, data, function(response) {
                // Hide input field.
                $item.find('input').hide();
                $item.find('.displayed-value').show();
                // Show modified category name.
                $item.find('.displayed-value').text(value);
                // update edited category's name for services
                $('select[name="category_id"] option[value="' + id + '"]').text(value);
            });
        })

        // On press Enter save changes.
        .on('keypress', 'input', function (e) {
            var code = e.keyCode || e.which;
            if (code == 13) {
                $(this).blur();
            }
        })

        // On delete category click.
        .on('click', '.bookly-js-delete', function(e) {
            // Keep category item click from being executed.
            e.stopPropagation();
            // Prevent navigating to '#'.
            e.preventDefault();
            // Ask user if he is sure.
            if (confirm(BooklyL10n.are_you_sure)) {
                var $item = $(this).closest('.bookly-category-item');
                var data = { action: 'bookly_delete_category', id: $item.data('category-id') };
                $.post(ajaxurl, data, function(response) {
                    // Remove category item from Services
                    $('select[name="category_id"] option[value="' + $item.data('category-id') + '"]').remove();
                    // Remove category item from DOM.
                    $item.remove();
                    if ($item.is('.active')) {
                        $('.bookly-js-all-services').click();
                    }
                });
            }
        })

        .on('click', 'input', function(e) {
            e.stopPropagation();
        });

    // Services list delegated events.
    $('#bookly-services-wrapper')
        // On click on 'Add Service' button.
        .on('click', '.add-service', function(e) {
            e.preventDefault();
            var ladda = rangeTools.ladda(this);
            var selected_category_id = $('#bookly-categories-list .active').data('category-id'),
                data = { action: 'bookly_add_service' };
            if (selected_category_id) {
                data['category_id'] = selected_category_id;
            }
            $.post(ajaxurl, data, function(response) {
                refreshList(response.data.html, response.data.service_id);
                ladda.stop();
            });
        })
        // On click on 'Delete' button.
        .on('click', '#bookly-delete', function(e) {
            if (confirm(BooklyL10n.are_you_sure)) {
                var ladda = rangeTools.ladda(this);

                var $for_delete = $('.service-checker:checked'),
                    data = { action: 'bookly_remove_services' },
                    services = [],
                    $panels = [];

                $for_delete.each(function(){
                    var panel = $(this).parents('.bookly-js-collapse');
                    $panels.push(panel);
                    services.push(this.value);
                });
                data['service_ids[]'] = services;
                $.post(ajaxurl, data, function() {
                    ladda.stop();
                    $.each($panels.reverse(), function (index) {
                        $(this).delay(500 * index).fadeOut(200, function () {
                            $(this).remove();
                        });
                    });
                });
            }
        })

        .on('change', 'input.bookly-check-all-entities, input.bookly-js-check-entity', function () {
            var $panel = $(this).parents('.bookly-js-collapse');
            if ($(this).hasClass('bookly-check-all-entities')) {
                $panel.find('.bookly-js-check-entity').prop('checked', $(this).prop('checked'));
            } else {
                $panel.find('.bookly-check-all-entities').prop('checked', $panel.find('.bookly-js-check-entity:not(:checked)').length == 0);
            }
            updateStaffButton($panel);
        });

    // Modal window events.
    var $modal = $('#ab-staff-update');
    $modal
        .on('click', '.ab-yes', function() {
            $modal.modal('hide');
            if ( $('#ab-remember-my-choice').prop('checked') ) {
                update_staff_choice = true;
            }
            submitServiceFrom($modal.data('input'),true);
        })
        .on('click', '.ab-no', function() {
            if ( $('#ab-remember-my-choice').prop('checked') ) {
                update_staff_choice = false;
            }
            submitServiceFrom($modal.data('input'),false);
        });

    function refreshList(response,service_id) {
        var $list = $('#ab-services-list');
        $list.html(response);
        if (response.indexOf('panel') >= 0) {
            $no_result.hide();
            makeServicesSortable();
            onCollapseInitChildren();
            $list.booklyHelp();
        } else {
            $no_result.show();
        }
        $('#service_' + service_id).collapse('show');
        $('#service_' + service_id).find('input[name=title]').focus();
    }

    function initColorPicker($jquery_collection) {
        $jquery_collection.each(function(){
            $(this).data('last-color', $(this).val());
        });
        $jquery_collection.wpColorPicker({
            width: 200
        });
    }

    $('#ab-services-list').on('change', '[name=capacity]', function(){
        if ($(this).val() > 1) {
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).val('1').prop('readonly',true);
        }
    }).on('change', '[name=padding_left],[name=padding_right]', function(){
        if ($(this).val() > 0) {
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).val('0').prop('readonly', true);
            $(this).find('option:gt(0)').prop('disabled', true);
        }
    });

    function submitServiceFrom($form, update_staff) {
        $form.find('input[name=update_staff]').val(update_staff ? 1 : 0);
        var ladda = rangeTools.ladda($form.find('button.ajax-service-send[type=submit]').get(0)),
            data = $form.serializeArray();
        if ($form.find('input[name=type]:checked').val() == 'compound') {
            $form.find('li[data-sub-service-id]').each(function () {
                data.push({name: 'sub_services[]', value: $(this).data('sub-service-id')});
            });
        } else {
            data.push({name: 'type', value: 'simple'});
            data.push({name: 'sub_services[]', value: false});
        }
        $.post(ajaxurl, data, function (response) {
            if (response.success) {
                var $panel = $form.parents('.bookly-js-collapse'),
                    $price = $form.find('[name=price]'),
                    $capacity = $form.find('[name=capacity]');
                $panel.find('.bookly-js-service-color').css('background-color', response.data.color);
                $panel.find('.bookly-js-service-title').html(response.data.title);
                $panel.find('.bookly-js-service-duration').html(response.data.nice_duration);
                $panel.find('.bookly-js-service-price').html(response.data.price);
                $price.data('last_value', $price.val());
                $capacity.data('last_value', $capacity.val());
                booklyAlert({success : [BooklyL10n.saved]});
                $.each(response.data.new_extras, function (front_id, real_id) {
                    var $li = $('li.extra.new[data-extra-id="' + front_id + '"]', $form);
                    $('[name^="extras"]', $li).each(function () {
                        var name = $(this).attr('name');
                        name = name.replace('[' + front_id + ']', '[' + real_id + ']');
                        $(this).attr('name', name);
                    });
                    $li.data('extra-id', real_id).removeClass('new');
                    $li.append('<input type="hidden" value="' + real_id + '" name="extras[' + real_id + '][id]">');
                });
            } else {
                booklyAlert({error: [response.data.message]});
            }
        }, 'json').always(function() {
            ladda.stop();
        });
    }

    function updateStaffButton($panel) {
        var staff_checked = $panel.find('.bookly-js-check-entity:checked').length;
        if (staff_checked == 0) {
            $panel.find('.bookly-entity-counter').text(BooklyL10n.selector.nothing_selected);
        } else if (staff_checked == 1) {
            $panel.find('.bookly-entity-counter').text($panel.find('.bookly-js-check-entity:checked').data('staff_name'));
        } else {
            $panel.find('.bookly-entity-counter').text(staff_checked + '/' + $panel.find('.bookly-js-check-entity').length);
        }
    }

    var $category = $('#bookly-category-item-list');
    $category.sortable({
        axis   : 'y',
        handle : '.bookly-js-handle',
        update : function( event, ui ) {
            var data = [];
            $category.children('li').each(function() {
                var $this = $(this);
                var position = $this.data('category-id');
                data.push(position);
            });
            $.ajax({
                type : 'POST',
                url  : ajaxurl,
                data : { action: 'bookly_update_category_position', position: data }
            });
        }
    });

    function makeServicesSortable() {
        if ($('.bookly-js-all-services').hasClass('active')) {
            var $services = $('#services_list'),
                fixHelper = function(e, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                };
            $services.sortable({
                helper : fixHelper,
                axis   : 'y',
                handle : '.bookly-js-handle',
                update : function( event, ui ) {
                    var data = [];
                    $services.children('div').each(function() {
                        data.push($(this).data('service-id'));
                    });
                    $.ajax({
                        type : 'POST',
                        url  : ajaxurl,
                        data : { action: 'bookly_update_services_position', position: data }
                    });
                }
            });
        } else {
            $('#services_list .bookly-js-handle').hide();
        }
    }

    function onCollapseInitChildren() {
        $('.panel-collapse').on('show.bs.collapse.bookly', function () {
            var $panel = $(this);
            var $sub_services = $('.ab--service-list', $panel);
            initColorPicker($panel.find('.bookly-js-color-picker'));
            $('input[name=type]', $panel).on( 'click', function(){
                if ($(this).val() == 'simple') {
                    $('.ab--for-simple', $panel).show();
                    $('.ab--for-compound', $panel).hide();
                } else {
                    $('.ab--for-simple', $panel).hide();
                    $('.ab--for-compound', $panel).show();
                }
            });
            $('input[name=type]:checked', $panel).trigger('click');

            $('[data-toggle="popover"]').popover({
                html: true,
                placement: 'top',
                trigger: 'hover',
                template: '<div class="popover bookly-font-xs" style="width: 220px" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
            });

            var initSubServicesLi = function ($li) {
                $("option[value=" + $li.data('sub-service-id') + "]", $sub_services).prop('disabled', true);
                $('.ab--sub-service-remove', $li).click(function () {
                    $('li.list-group-item[data-sub-service-id="' + $li.data('sub-service-id') + '"]', $panel).remove();
                    $("option[value=" + $li.data('sub-service-id') + "]", $sub_services).prop('disabled', false);
                });
            };

            $('.ab--sub-services li.list-group-item[data-sub-service-id]', $panel).each(function () {
                initSubServicesLi($(this));
            });

            $sub_services.on('change', function () {
                if ($(this).val()) {
                    var $li = $('.ab--templates.services .template_' + $sub_services.val() + ' li').clone();
                    $li.insertBefore($(this).parents('li'));
                    initSubServicesLi($li);
                    $(this).val(0);
                }
            });

            $('.ab--sub-services', $panel).sortable({axis: 'y', items: "[data-sub-service-id]"});

            updateStaffButton($(this).parents('.bookly-js-collapse'));

            $panel.find('.ajax-service-send').on('click', function (e) {
                e.preventDefault();
                var $form = $(this).parents('form'),
                    show_modal = false;
                if(update_staff_choice === null) {
                    $('.ab-question', $form).each(function () {
                        if ($(this).data('last_value') != $(this).val()) {
                            show_modal = true;
                        }
                    });
                }
                if (show_modal) {
                    $modal.data('input', $form).modal('show');
                } else {
                    submitServiceFrom($form, update_staff_choice);
                }
            });

            $panel.find('.js-reset').on('click', function () {
                $(this).parents('form').trigger('reset');
                var $color = $(this).parents('form').find('.wp-color-picker'),
                    $panel = $(this).parents('.bookly-js-collapse');
                $color.val($color.data('last-color')).trigger('change');
                $panel.find('.parent-range-start').trigger('change');
                updateStaffButton($panel);
            });
            $panel.find('.ab-question').each(function () {
                $(this).data('last_value', $(this).val());
            });
            $panel.unbind('show.bs.collapse.bookly');
            $(document.body).trigger( 'service_list.service_expand', [ $panel, $panel.closest('.panel').data('service-id') ] );
        });
    }
    makeServicesSortable();
    onCollapseInitChildren();

    /*<Extras>*/
    $('.extras-container').sortable({
        axis   : 'y',
        handle : '.bookly-js-handle',
        update : function( event, ui ) {
            var data = [];
            $(this).find('.extra').each(function() {
                data.push($(this).data('extra-id'));
            });
            $.ajax({
                type : 'POST',
                url  : ajaxurl,
                data : { action: 'bookly_update_extra_position', position: data }
            });
        }
    });

    $(document).on('click', '.bookly-js-collapse .extra-new', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var children = $('.extras-container li');

        var id = 1;
        children.each(function (i, el) {
            var elId = parseInt($(el).data('extra-id'));
            id = (elId >= id) ? elId + 1 : id;
        });
        var template = $('.ab--templates.extras').html();
        var $container = $(this).parents('.bookly-js-collapse').find('.extras-container');
        id++;
        $container.append(
            template.replace(/%id%/g, id)
        );
        $('#title_' + id).focus();
    });

    $(document).on('click', '.bookly-js-collapse .extra-attachment', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var extra = $(this).parents('.extra');
        var frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection').toJSON(),
                img_src
            ;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                extra.find("[name='extras[" + extra.data('extra-id') + "][attachment_id]']").val(selection[0].id);
                extra.find('.extra-attachment-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                extra.find('.bookly-js-remove-attachment').show();
                $(this).hide();
            }
        });

        frame.open();
    });

    $(document).on('click', '.bookly-js-collapse .bookly-js-remove-attachment', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).hide();
        var extra = $(this).parents('.extra');
        extra.find("[name='extras[" + extra.data('extra-id') + "][attachment_id]']").attr('value', '');
        extra.find('.extra-attachment-image').attr('style', '');
        extra.find('.extra-attachment').show();
    }).on('change', '.popover-range-start, .popover-range-end', function () {
        var $popover_content = $(this).closest('.popover-content');
        rangeTools.hideInaccessibleBreaks($popover_content.find('.popover-range-start'), $popover_content.find('.popover-range-end'));
    });

    $(document).on('click', '.bookly-js-collapse .extra-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (confirm(BooklyL10n.are_you_sure)) {
            var extra = $(this).parents('.extra');
            if (!extra.hasClass('new')) {
                $.post(ajaxurl, {action: 'bookly_service_extras_delete_service_extra', id: extra.data('extra-id')}, function () {
                });
            }
            extra.remove();
        }
    });
    /*</Extras>*/
});