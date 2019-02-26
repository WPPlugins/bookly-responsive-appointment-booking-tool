jQuery(function($) {
    'use strict';
    var
        $customersList        = $('#bookly-customers-list'),
        $mergeListContainer   = $('#bookly-merge-list'),
        $mergeList            = $customersList.clone().prop('id', '').find('th:last').remove().end().appendTo($mergeListContainer),
        $filter               = $('#bookly-filter'),
        $checkAllButton       = $('#bookly-check-all'),
        $customerDialog       = $('#bookly-customer-dialog'),
        $addButton            = $('#bookly-add'),
        $selectForMergeButton = $('#bookly-select-for-merge'),
        $mergeWithButton      = $('#bookly-merge-with'),
        $mergeDialog          = $('#bookly-merge-dialog'),
        $mergeButton          = $('#bookly-merge'),
        row
    ;

    var columns = [
        {data: 'full_name', render: $.fn.dataTable.render.text(), responsivePriority: 2, visible: BooklyL10n.first_last_name == 0},
        {data: 'first_name', render: $.fn.dataTable.render.text(), responsivePriority: 2, visible: BooklyL10n.first_last_name == 1},
        {data: 'last_name', render: $.fn.dataTable.render.text(), responsivePriority: 2, visible: BooklyL10n.first_last_name == 1},
        {data: 'wp_user', render: $.fn.dataTable.render.text(), responsivePriority: 2}
    ];
    if (BooklyL10n.groupsActive == 1) {
        columns.push({data: 'group_name', render: $.fn.dataTable.render.text(), responsivePriority: 2});
    }
    columns = columns.concat([
        {data: 'phone', render: $.fn.dataTable.render.text(), responsivePriority: 2},
        {data: 'email', render: $.fn.dataTable.render.text(), responsivePriority: 2}
    ]);
    BooklyL10n.infoFields.forEach(function (field, i) {
        columns.push({
            data: 'info_fields.' + i + '.value' + (field.type === 'checkboxes' ? '[, ]' : ''),
            render: $.fn.dataTable.render.text(),
            responsivePriority: 3,
            orderable: false
        });
    });
    columns = columns.concat([
        {data: 'notes', render: $.fn.dataTable.render.text(), responsivePriority: 2},
        {data: 'last_appointment', responsivePriority: 2},
        {data: 'total_appointments', responsivePriority: 2},
        {data: 'payments', responsivePriority: 2}
    ]);

    if (BooklyL10n.proEnabled == 1){
        columns = columns.concat([
            {data: 'address', render: $.fn.dataTable.render.text(), responsivePriority: 3, orderable: false},
            {
                data: 'facebook_id',
                responsivePriority: 2,
                render: function (data, type, row, meta) {
                    return data ? '<a href="https://www.facebook.com/app_scoped_user_id/' + data + '/" target="_blank"><span class="dashicons dashicons-facebook"></span></a>' : '';
                }
            }
        ]);
    }

    /**
     * Init DataTables.
     */
    var dt = $customersList.DataTable({
        order       : [[0, 'asc']],
        info        : false,
        searching   : false,
        lengthChange: false,
        pageLength  : 25,
        pagingType  : 'numbers',
        processing  : true,
        responsive  : true,
        serverSide  : true,
        ajax        : {
            url : ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({}, d, {
                    action    : 'bookly_get_customers',
                    csrf_token: BooklyL10n.csrfToken,
                    filter    : $filter.val()
                });
            }
        },
        columns: columns.concat([
            {
                responsivePriority: 1,
                orderable         : false,
                searchable        : false,
                render            : function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#bookly-customer-dialog"><i class="glyphicon glyphicon-edit"></i> ' + BooklyL10n.edit + '</button>';
                }
            },
            {
                responsivePriority: 1,
                orderable         : false,
                searchable        : false,
                render            : function (data, type, row, meta) {
                    return '<input type="checkbox" value="' + row.id + '" />';
                }
            }
        ]),
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row pull-left'<'col-sm-12 bookly-margin-top-lg'p>>",
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
        }
    });

    /**
     * Select all customers.
     */
    $checkAllButton.on('change', function () {
        $customersList.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On customer select.
     */
    $customersList.on('change', 'tbody input:checkbox', function () {
        $checkAllButton.prop('checked', $customersList.find('tbody input:not(:checked)').length == 0);
        $mergeWithButton.prop('disabled', $customersList.find('tbody input:checked').length != 1);
    });

    /**
     * Edit customer.
     */
    $customersList.on('click', 'button', function () {
        row = dt.row($(this).closest('td'));
    });

    /**
     * New customer.
     */
    $addButton.on('click', function () {
        row = null;
    });

    /**
     * On show modal.
     */
    $customerDialog.on('show.bs.modal', function () {
        var $title = $customerDialog.find('.modal-title');
        var $button = $customerDialog.find('.modal-footer button:first');
        var customer;
        if (row) {
            customer = $.extend(true, {}, row.data());
            $title.text(BooklyL10n.edit_customer);
            $button.text(BooklyL10n.save);
        } else {
            customer = {
                id          : '',
                wp_user_id  : '',
                group_id    : '',
                full_name   : '',
                first_name  : '',
                last_name   : '',
                phone       : '',
                email       : '',
                country     : '',
                state       : '',
                postcode    : '',
                city        : '',
                street      : '',
                address     : '',
                info_fields : [],
                notes       : '',
                birthday    : ''
            };
            BooklyL10n.infoFields.forEach(function (field) {
                customer.info_fields.push({id: field.id, value: field.type === 'checkboxes' ? [] : ''});
            });
            $title.text(BooklyL10n.new_customer);
            $button.text(BooklyL10n.create_customer);
        }

        var $scope = angular.element(this).scope();
        $scope.$apply(function ($scope) {
            $scope.customer = customer;
            setTimeout(function () {
                if (BooklyL10nCustDialog.intlTelInput.enabled) {
                    $('#phone', $customerDialog).intlTelInput('setNumber', customer.phone);
                } else {
                    $('#phone', $customerDialog).val(customer.phone);
                }
                $('#wp_user', $customerDialog).trigger('change.select2');
            }, 0);
        });
    });

    /**
     * On filters change.
     */
    $filter.on('keyup', function () { dt.ajax.reload(); });

    /**
     * Merge list.
     */
    var mdt = $mergeList.DataTable({
        order      : [[0, 'asc']],
        info       : false,
        searching  : false,
        paging     : false,
        responsive : true,
        columns: columns.concat([
            {
                responsivePriority: 1,
                orderable         : false,
                searchable        : false,
                render            : function (data, type, row, meta) {
                    return '<button type="button" class="btn btn-default"><i class="glyphicon glyphicon-remove"></i></button>';
                }
            }
        ]),
        language: {
            zeroRecords: BooklyL10n.zeroRecords
        }
    });

    /**
     * Select for merge.
     */
    $selectForMergeButton.on('click', function () {
        var $checkboxes = $customersList.find('tbody input:checked');

        if ($checkboxes.length) {
            $checkboxes.each(function () {
                var data = dt.row($(this).closest('td')).data();
                if (mdt.rows().data().indexOf(data) < 0) {
                    mdt.row.add(data).draw();
                }
                this.checked = false;
            }).trigger('change');
            $mergeWithButton.show();
            $mergeListContainer.show();
        }
    });

    /**
     * Merge customers.
     */
    $mergeButton.on('click', function () {
        var ladda = Ladda.create(this),
            ids = [];
        ladda.start();
        mdt.rows().every(function () {
            ids.push(this.data().id);
        });
        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action     : 'bookly_merge_customers',
                csrf_token : BooklyL10n.csrfToken,
                target_id  : $customersList.find('tbody input:checked').val(),
                ids        : ids
            },
            dataType : 'json',
            success  : function(response) {
                ladda.stop();
                $mergeDialog.modal('hide');
                if (response.success) {
                    dt.ajax.reload(null, false);
                    mdt.clear();
                    $mergeListContainer.hide();
                    $mergeWithButton.hide();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    /**
     * Remove customer from merge list.
     */
    $mergeList.on('click', 'button', function () {
        mdt.row($(this).closest('td')).remove().draw();
        var any = mdt.rows().any();
        $mergeWithButton.toggle(any);
        $mergeListContainer.toggle(any);
    });

    /**
     * Import & export customers.
     */
    Ladda.bind('#bookly-import-customers-dialog button[type=submit]');
    Ladda.bind('#bookly-export-customers-dialog button[type=submit]', {timeout: 2000});
});

(function() {
    var module = angular.module('customer', ['customerDialog']);
    module.controller('customerCtrl', function($scope) {
        $scope.customer = {
            id          : '',
            wp_user_id  : '',
            group_id    : '',
            full_name   : '',
            first_name  : '',
            last_name   : '',
            phone       : '',
            email       : '',
            country     : '',
            state       : '',
            postcode    : '',
            city        : '',
            street      : '',
            address     : '',
            info_fields : [],
            notes       : '',
            birthday    : ''
        };
        $scope.saveCustomer = function(customer) {
            jQuery('#bookly-customers-list').DataTable().ajax.reload(null, false);
        };
    });
})();