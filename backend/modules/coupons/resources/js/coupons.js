jQuery(function($) {

    var
        $coupons_list       = $('#bookly-coupons-list'),
        $coupon_modal       = $('#bookly-coupon-modal'),
        $save_button        = $('#bookly-coupon-save')
        ;

    /**
     * Init DataTables.
     */
    var dt = $coupons_list.DataTable({
        order: [[ 0, "asc" ]],
        paging: false,
        info: false,
        searching: false,
        processing: true,
        responsive: true,
        data: [],
        columns: [
            { data: "code" },
            { data: "discount" },
            { data: "deduction" },
            { data: 'service_ids'},
            { data: "usage_limit" },
            { data: "used" },
            {
                responsivePriority: 1,
                orderable: false,
                searchable: false,
                render: function ( data, type, row, meta ) {
                    return '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#bookly-coupon-modal"><i class="glyphicon glyphicon-edit"></i> ' + BooklyL10n.edit + '</button>';
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
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing:  BooklyL10n.processing
        }
    });

    /**
     * Save coupon.
     */
    $save_button.on('click', function (e) {
        e.preventDefault();
        $coupon_modal.modal('hide');
        booklyAlert({error: [BooklyL10n.limitations]});
    });
    $('#bookly-add,#bookly-delete').on('click', function (e) {
        e.preventDefault();
        booklyAlert({error: [BooklyL10n.limitations]});
    });

});
