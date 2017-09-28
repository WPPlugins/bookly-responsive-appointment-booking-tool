;(function() {

    angular.module('paymentDetailsDialog', []).directive('paymentDetailsDialog', function() {
        return {
            restrict: 'A',
            replace: true,
            scope: {
                callback: '&paymentDetailsDialog'
            },
            templateUrl: 'bookly-payment-details-dialog.tpl',
            // The linking function will add behavior to the template.
            link: function (scope, element, attrs) {
                var $body   = element.find('.modal-body'),
                    spinner = $body.html();

                element
                    .on('show.bs.modal', function (e, payment_id) {
                        if (payment_id === undefined) {
                            payment_id = e.relatedTarget.getAttribute('data-payment_id');
                        }
                        jQuery.ajax({
                            url:      ajaxurl,
                            data:     {action: 'bookly_get_payment_details', payment_id: payment_id},
                            dataType: 'json',
                            success:  function (response) {
                                if (response.success) {
                                    $body.html(response.data.html);
                                    $body.find('#bookly-complete-payment').on('click',function () {
                                        var ladda = Ladda.create(this);
                                        ladda.start();
                                        jQuery.ajax({
                                            url:      ajaxurl,
                                            data:     {action: 'bookly_complete_payment', payment_id: payment_id},
                                            dataType: 'json',
                                            type:     'POST',
                                            success:  function (response) {
                                                if (response.success) {
                                                    element.trigger('show.bs.modal', [payment_id]);
                                                    if (scope.callback) {
                                                        scope.$apply(function ($scope) {
                                                            $scope.callback({
                                                                payment_id    : payment_id,
                                                                payment_title : response.data.payment_title
                                                            });
                                                        });
                                                    }
                                                    // Reload DataTable.
                                                    var $table = jQuery(e.relatedTarget).closest('table.dataTable');
                                                    if ($table.length) {
                                                        $table.DataTable().ajax.reload();
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }
                        });
                    })
                    .on('hidden.bs.modal', function () {
                        $body.html(spinner);
                        if ((jQuery("#bookly-appointment-dialog").data('bs.modal') || {isShown: false}).isShown) {
                            jQuery('body').addClass('modal-open');
                        }
                    });
            }
        }
    });
})();