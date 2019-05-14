jQuery(function ($) {
    var $servicesList   = $('#services-list'),
        $checkAllButton = $('.bookly-js-check-all'),
        $filter         = $('#bookly-filter'),
        $deleteButton   = $('#bookly-delete'),
        $deleteModal    = $('.bookly-js-delete-cascade-confirm'),
        categories      = []
    ;
    var columns = [
        {
            data      : 'position',
            searchable: false
        },
        {
            render: function (data, type, row, meta) {
                return '<i class="bookly-icon bookly-icon-draghandle bookly-cursor-move" title="' + BooklyL10n.reorder + '"></i>';
            }
        },
    ];
    if (BooklyL10n.show_type) {
        columns.push({
            render            : function (data, type, row, meta) {
                return '<i class="fa fa-fw ' + row.type_icon + '" title="' + row.type + '"></i>';
            },
            responsivePriority: 2
        });
    }
    columns = columns.concat([
        {
            responsivePriority: 3,
            render            : function (data, type, row, meta) {
                return '<i class="fa fa-fw fa-circle" style="color:' + row.colors[0] + ';">';
            }
        },
        {
            data              : 'title',
            responsivePriority: 2
        },
        {
            responsivePriority: 3,
            render: function (data, type, row, meta) {
                $.each(BooklyL10n.categories, function (key, value) {
                    categories[value.id] = value.name;
                });
                if (row.category != null) {
                    return categories[row.category];
                } else {
                    return BooklyL10n.uncategorized;
                }
            }
        },
        {
            data              : 'duration',
            responsivePriority: 4,
        },
        {
            data              : 'price',
            responsivePriority: 3
        },
        {
            responsivePriority: 1,
            searchable        : false,
            render            : function (data, type, row, meta) {
                return '<button type="button" class="btn btn-default bookly-js-edit" data-action="edit"><i class="fa fa-fw fa-edit"></i> ' + BooklyL10n.edit + '</a>';
            }
        },
        {
            responsivePriority: 1,
            searchable        : false,
            render            : function (data, type, row, meta) {
                return '<input type="checkbox" class="bookly-js-delete" value="' + row.id + '" />';
            }
        }
    ]);
    /**
     * Notification list
     */
    var dt = $servicesList.DataTable({
        paging    : false,
        info      : false,
        processing: true,
        responsive: true,
        serverSide: false,
        rowReorder: {
            update  : true,
            dataSrc : 'position',
            snapX   : true,
            selector: '.bookly-icon-draghandle'
        },
        order     : [0, 'asc'],
        columnDefs: [
            {visible: false, targets: 0},
            {orderable: false, targets: '_all'}
        ],
        ajax      : {
            url : ajaxurl,
            data: {action: 'bookly_get_services', csrf_token: BooklyL10n.csrfToken}
        },
        columns   : columns,
        dom       : "<'row'<'col-sm-6'<'pull-left'>><'col-sm-6'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row pull-left'<'col-sm-12 bookly-margin-top-lg'p>>",
        language  : {
            zeroRecords: BooklyL10n.zeroRecords,
            processing : BooklyL10n.processing
        }
    }).on('row-reordered', function (e, diff, edit) {
        var positions = [];
        function sortByPosition(a, b){
            return ((a.position < b.position) ? -1 : ((a.position > b.position) ? 1 : 0));
        }
        dt.data().each(function (service) {
            positions.push({position: service.position, id: service.id});
        });
        $.ajax({
            url     : ajaxurl,
            type    : 'POST',
            data: {
                'action'     : 'bookly_update_services_position',
                'csrf_token' : BooklyL10n.csrfToken,
                'positions[]': $.map(positions.sort(sortByPosition), function (value) {
                    return value.id;
                })
            },
            dataType: 'json',
            success : function (response) {

            }
        });
    });

    /**
     * On filters change.
     */
    $filter
        .on('keyup', function () {
            dt.search(this.value).draw();
        })
        .on('keydown', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                return false;
            }
        })
    ;

    /**
     * Select all appointments.
     */
    $checkAllButton.on('change', function () {
        $servicesList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On appointment select.
     */
    $servicesList.on('change', 'tbody input:checkbox', function () {
        $checkAllButton.prop('checked', $servicesList.find('tbody input:not(:checked)').length == 0);
    });

    $deleteButton.on('click', function (e) {
        e.preventDefault();
        var data     = {
                action    : 'bookly_remove_services',
                csrf_token: BooklyL10n.csrfToken,
            },
            services = [],
            button   = this;

        var delete_services = function (ajaxurl, data) {
            var ladda       = rangeTools.ladda(button),
                service_ids = [],
                $checkboxes = $servicesList.find('tbody input:checked');

            $checkboxes.each(function () {
                service_ids.push(dt.row($(this).closest('td')).data().id);
            });
            data['service_ids[]'] = service_ids;

            $.post(ajaxurl, data, function (response) {
                if (!response.success) {
                    switch (response.data.action) {
                        case 'show_modal':
                            $deleteModal
                                .modal('show');
                            $('.bookly-js-delete', $deleteModal).off().on('click', function () {
                                delete_services(ajaxurl, $.extend(data, {force_delete: true}));
                                $deleteModal.modal('hide');
                            });
                            $('.bookly-js-edit', $deleteModal).off().on('click', function () {
                                rangeTools.ladda(this);
                                window.location.href = response.data.filter_url;
                            });
                            break;
                        case 'confirm':
                            if (confirm(BooklyL10n.are_you_sure)) {
                                delete_services(ajaxurl, $.extend(data, {force_delete: true}));
                            }
                            break;
                    }
                } else {
                    $(document.body).trigger('service.deleted', [services]);
                    dt.rows($checkboxes.closest('td')).remove().draw();
                }
                ladda.stop();
            });
        };

        delete_services(ajaxurl, data);
    });
});