jQuery(function($) {

    var
        $customers_list     = $('#bookly-customers-list'),
        $filter             = $('#bookly-filter'),
        $check_all_button   = $('#bookly-check-all'),
        $customer_dialog    = $('#bookly-customer-dialog'),
        $add_button         = $('#bookly-add'),
        $delete_button      = $('#bookly-delete'),
        $delete_dialog      = $('#bookly-delete-dialog'),
        $delete_button_no   = $('#bookly-delete-no'),
        $delete_button_yes  = $('#bookly-delete-yes'),
        $remember_choice    = $('#bookly-delete-remember-choice'),
        remembered_choice,
        row
        ;

    /**
     * Init DataTables.
     */
    var dt = $customers_list.DataTable({
        order: [[ 0, 'asc' ]],
        info: false,
        searching: false,
        lengthChange: false,
        pageLength: 25,
        pagingType: 'numbers',
        processing: true,
        responsive: true,
        serverSide: true,
        ajax: {
            url: ajaxurl,
            data: function (d) {
                return $.extend({}, d, {
                    action: 'bookly_get_customers',
                    filter: $filter.val()
                });
            }
        },
        columns: [
            { data: 'name', render: $.fn.dataTable.render.text() },
            { data: 'wp_user', render: $.fn.dataTable.render.text() },
            { data: 'phone', render: $.fn.dataTable.render.text() },
            { data: 'email', render: $.fn.dataTable.render.text() },
            { data: 'notes', render: $.fn.dataTable.render.text() },
            { data: 'last_appointment' },
            { data: 'total_appointments' },
            { data: 'payments' },
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function ( data, type, row, meta ) {
                    return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#bookly-customer-dialog"><i class="glyphicon glyphicon-edit"></i> ' + BooklyL10n.edit + '</button>';
                }
            },
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function ( data, type, row, meta ) {
                    return '<input type="checkbox" value="' + row.id + '">';
                }
            }
        ],
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row pull-left'<'col-sm-12 bookly-margin-top-lg'p>>",
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
        }
    });

    /**
     * Select all coupons.
     */
    $check_all_button.on('change', function () {
        $customers_list.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On coupon select.
     */
    $customers_list.on('change', 'tbody input:checkbox', function () {
        $check_all_button.prop('checked', $customers_list.find('tbody input:not(:checked)').length == 0);
    });

    /**
     * Edit customer.
     */
    $customers_list.on('click', 'button', function () {
        row = dt.row($(this).closest('td'));
    });

    /**
     * New coupon.
     */
    $add_button.on('click', function () {
        row = null;
    });

    /**
     * On show modal.
     */
    $customer_dialog.on('show.bs.modal', function () {
        var $title = $customer_dialog.find('.modal-title');
        var $button = $customer_dialog.find('.modal-footer button:first');
        var customer;
        if (row) {
            customer = row.data();
            $title.text(BooklyL10n.edit_customer);
            $button.text(BooklyL10n.save);
        } else {
            customer = {
                id         : '',
                wp_user_id : '',
                name       : '',
                phone      : '',
                email      : '',
                notes      : ''
            };
            $title.text(BooklyL10n.new_customer);
            $button.text(BooklyL10n.create);
        }

        var $scope = angular.element(this).scope();
        $scope.$apply(function ($scope) {
            $scope.customer.id         = customer.id;
            $scope.customer.wp_user_id = customer.wp_user_id;
            $scope.customer.name       = customer.name;
            $scope.customer.phone      = customer.phone;
            $scope.customer.email      = customer.email;
            $scope.customer.notes      = customer.notes;
            setTimeout(function() {
                $customer_dialog.find('#phone').intlTelInput('setNumber', customer.phone);
            }, 0);
        });
    });

    /**
     * Delete customers.
     */
    $delete_button.on('click', function () {
        if (!remembered_choice) {
            $delete_dialog.modal('show');
        } else {
            deleteCustomers(this, remembered_choice);
        }}
    );

    $delete_button_no.on('click', function () {
        if ($remember_choice.prop('checked')) {
            remembered_choice = false;
        }
        deleteCustomers(this, false);
    });

    $delete_button_yes.on('click', function () {
        if ($remember_choice.prop('checked')) {
            remembered_choice = true;
        }
        deleteCustomers(this, true);
    });

    function deleteCustomers(button, with_wp_user) {
        var ladda = Ladda.create(button);
        ladda.start();

        var data = [];
        var $checkboxes = $customers_list.find('tbody input:checked');
        $checkboxes.each(function () {
            data.push(this.value);
        });

        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action       : 'bookly_delete_customers',
                data         : data,
                with_wp_user : with_wp_user
            },
            dataType : 'json',
            success  : function(response) {
                ladda.stop();
                $delete_dialog.modal('hide');
                if (response.success) {
                    dt.ajax.reload(null, false);
                } else {
                    alert(response.data.message);
                }
            }
        });
    }

    /**
     * On filters change.
     */
    $filter.on('keyup', function () { dt.ajax.reload(); });

    $('.bookly-limitation').on('click', function () {
        booklyAlert({error: [BooklyL10n.limitations]});
    });
});

(function() {
    var module = angular.module('customer', ['customerDialog']);
    module.controller('customerCtrl', function($scope) {
        $scope.customer = {
            id         : '',
            wp_user_id : '',
            name       : '',
            phone      : '',
            email      : '',
            notes      : ''
        };
        $scope.saveCustomer = function(customer) {
            jQuery('#bookly-customers-list').DataTable().ajax.reload(null, false);
        };
    });
})();