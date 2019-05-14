jQuery(function ($) {
    'use strict';

    var $dialog       = $('#bookly-service-categories-modal'),
        $categories   = $('#bookly-services-categories', $dialog),
        $template     = $('#bookly-new-category-template'),
        $newCategory  = $('#bookly-js-new-category', $dialog),
        $servicesList = $('#services-list'),
        $save         = $('#bookly-save', $dialog),
        new_category_id
    ;
    // Add category
    $newCategory.on('click', function () {
        $categories.append(
            $template.clone().show().html()
                .replace(/{{id}}/g, 'new' + ++new_category_id)
                .replace(/{{name}}/g, '')
        );
        $categories.find('.form-group:last input[name="category_name"]').focus();
    });
    // Remove category
    $categories.on('click', '.bookly-js-delete-category', function (e) {
        e.preventDefault();
        $(this).closest('li.form-group').remove();
    });
    // Save categories
    $save.on('click', function (e) {
        e.preventDefault();
        var ladda      = Ladda.create(this),
            categories = [];
        ladda.start();
        $categories.find('li').each(function (position, category) {
            categories.push({id: $(category).find('[name="category_id"]').val(), name: $(category).find('[name="category_name"]').val()});
        });
        $.post(
            ajaxurl,
            {
                action    : 'bookly_update_service_categories',
                categories: categories,
                csrf_token: BooklyL10n.csrfToken
            },
            function (response) {
                if (response.success) {
                    BooklyL10n.categories = response.data;
                    $servicesList.DataTable().ajax.reload();
                    $dialog.modal('hide');
                }
                ladda.stop();
            });
    });

    $dialog.off().on('show.bs.modal', function () {
        new_category_id = 0;
        // Show categories list
        $categories.html('');
        BooklyL10n.categories.forEach(function (category) {
            $categories.append(
                $template.clone().show().html()
                    .replace(/{{id}}/g, category.id)
                    .replace(/{{name}}/g, category.name)
            );
        });
    });

    $categories.sortable({
        axis  : 'y',
        handle: '.bookly-js-draghandle',
    });
});