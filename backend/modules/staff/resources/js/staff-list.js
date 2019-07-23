jQuery(function ($) {
    var $staffList   = $('#staff-list'),
        $checkAllButton = $('.bookly-js-check-all'),
        $filter         = $('#bookly-filter'),
        $deleteButton   = $('#bookly-delete'),
        $deleteModal    = $('.bookly-js-delete-cascade-confirm'),
        $staffCount     = $('.bookly-js-staff-count'),
        $filterVisibility = $('#bookly-filter-visibility'),
        $filterArchived   = $('#bookly-filter-archived')
    ;

    $('.bookly-js-select').val(null);
    $.each(BooklyL10n.filter, function (field, value) {
        if (value != '') {
            let $elem = $('#bookly-filter-' + field);
            if ($elem.is(':checkbox')) {
                $elem.prop('checked', value == '1');
            } else {
                $elem.val(value);
            }
        }
        // check if select has correct values
        if ($('#bookly-filter-' + field).prop('type') == 'select-one') {
            if ($('#bookly-filter-' + field + ' option[value="' + value + '"]').length == 0) {
                $('#bookly-filter-' + field).val(null);
            }
        }
    });

    $deleteModal
        .on('hidden.bs.modal', function () {
            jQuery('body').addClass('modal-open');
        });
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
        {
            data: 'full_name',
            responsivePriority: 2
        },
    ];
    if (BooklyL10n.proRequired !== "1") {
        columns = columns.concat([
            {
                responsivePriority: 3,
                render: function (data, type, row, meta) {
                    if (row.category_id != null) {
                        return BooklyL10n.categories.find(x => x.id === row.category_id).name;
                    } else {
                        return BooklyL10n.uncategorized;
                    }
                }
            }]);
    } else {

    }
    columns = columns.concat([
        {
            data: 'email',
            responsivePriority: 2
        },
        {
            data: 'phone',
            responsivePriority: 2
        },
        {
            data: 'wp_user',
            responsivePriority: 2
        },
        {
            responsivePriority: 1,
            searchable: false,
            render: function (data, type, row, meta) {
                return '<button type="button" class="btn btn-default" data-action="edit"><i class="fa fa-fw fa-edit"></i> ' + BooklyL10n.edit + '</a>';
            }
        },
        {
            responsivePriority: 1,
            searchable: false,
            render: function (data, type, row, meta) {
                return '<input type="checkbox" class="bookly-js-delete" value="' + row.id + '" />';
            }
        }
    ]);

    /**
     * Notification list
     */
    var dt = $staffList.DataTable({
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
            data: {action: '', csrf_token: BooklyL10n.csrfToken},
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookly_get_staff_list', csrf_token: BooklyL10n.csrfToken}, {
                    filter: {
                        visibility: $filterVisibility.val(),
                        archived  : $filterArchived.prop('checked')?1:0
                    }
                }, d);
            },
            dataSrc: function (json) {
                $staffCount.html(json.data.total);
                return json.data.list;
            }
        },
        columns   : columns,
        rowCallback: function (row, data) {
            if ( data.visibility == 'archive' ) {
                $(row).addClass('text-muted');
            }
        },
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
                action     : 'bookly_update_staff_position',
                csrf_token : BooklyL10n.csrfToken,
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
        $staffList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On appointment select.
     */
    $staffList.on('change', 'tbody input:checkbox', function () {
        $checkAllButton.prop('checked', $staffList.find('tbody input:not(:checked)').length == 0);
    });

    $deleteButton
        .on('click', function (e) {
            e.preventDefault();
            var data = {
                    action: 'bookly_remove_staff',
                    csrf_token: BooklyL10n.csrfToken,
                },
                staff = [],
                button = this;

            var delete_staff = function (ajaxurl, data) {
                var ladda = rangeTools.ladda(button),
                    staff_ids = [],
                    $checkboxes = $staffList.find('tbody input:checked');

                $checkboxes.each(function () {
                    staff_ids.push(dt.row($(this).closest('td')).data().id);
                });
                data['staff_ids[]'] = staff_ids;

                $.post(ajaxurl, data, function (response) {
                    if (!response.success) {
                        switch (response.data.action) {
                            case 'show_modal':
                                $deleteModal
                                    .modal('show');
                                $('.bookly-js-delete', $deleteModal).off().on('click', function () {
                                    delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
                                    $deleteModal.modal('hide');
                                });
                                $('.bookly-js-edit', $deleteModal).off().on('click', function () {
                                    rangeTools.ladda(this);
                                    window.location.href = response.data.filter_url;
                                });
                                break;
                            case 'confirm':
                                if (confirm(BooklyL10n.areYouSure)) {
                                    delete_staff(ajaxurl, $.extend(data, {force_delete: true}));
                                }
                                break;
                        }
                    } else {
                        $(document.body).trigger('service.deleted', [staff]);
                        dt.rows($checkboxes.closest('td')).remove().draw();
                        $staffCount.html(response.data.total);
                    }
                    ladda.stop();
                });
            };

            delete_staff(ajaxurl, data);
        });

    $('.bookly-js-select')
        .on('select2:unselecting', function(e) {
            e.preventDefault();
            $(this).val(null).trigger('change');
        })
        .select2({
            width: '100%',
            theme: 'bootstrap',
            allowClear: true,
            language  : {
                noResults: function() { return BooklyL10n.no_result_found; }
            }
        });

    $filterVisibility.on('change', function () {dt.ajax.reload();});
    $filterArchived.on('change', function () {dt.ajax.reload();});
});