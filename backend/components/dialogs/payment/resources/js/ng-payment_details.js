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
                    .on('show.bs.modal refresh', function (e, payment_id) {
                        if (payment_id === undefined) {
                            if (e.relatedTarget) {
                                payment_id = e.relatedTarget.getAttribute('data-payment_id');
                                var payment_bind = e.relatedTarget.getAttribute('data-payment_bind'),
                                    customer_id  = e.relatedTarget.getAttribute('data-customer_id');
                            } else if (element.data('payment_id')) {
                                payment_id = element.data('payment_id');
                                var payment_bind = element.data('payment_bind'),
                                    customer_id  = element.data('customer_id');
                            }
                        }
                        jQuery.ajax({
                            url:      ajaxurl,
                            data:     {action: 'bookly_get_payment_details', payment_id: payment_id, csrf_token : BooklyL10n.csrf_token},
                            dataType: 'json',
                            success:  function (response) {
                                if (response.success) {
                                    $body.html(response.data.html);
                                    if (payment_bind) {
                                        jQuery('.bookly-js-details-main-controls').hide();
                                        jQuery('.bookly-js-details-bind-controls').show();
                                    }
                                    $body.find('#bookly-complete-payment').on('click',function () {
                                        var ladda = Ladda.create(this);
                                        ladda.start();
                                        jQuery.ajax({
                                            url:      ajaxurl,
                                            data:     {action: 'bookly_complete_payment', payment_id: payment_id, csrf_token : BooklyL10n.csrf_token},
                                            dataType: 'json',
                                            type:     'POST',
                                            success:  function (response) {
                                                if (response.success) {
                                                    element.trigger('refresh', [payment_id]);
                                                    if (scope.callback) {
                                                        scope.$apply(function ($scope) {
                                                            $scope.callback({
                                                                payment_action: 'complete',
                                                                payment_id    : payment_id,
                                                                payment_title : response.data.payment_title
                                                            });
                                                        });
                                                    }
                                                    // Reload DataTable.
                                                    var $table = jQuery('table#bookly-payments-list.dataTable');
                                                    if ($table.length) {
                                                        $table.DataTable().ajax.reload();
                                                    }
                                                }
                                            }
                                        });
                                    });
                                    jQuery('#bookly-js-attach-payment', $body).on('click', function () {
                                        var ladda = Ladda.create(this);
                                        ladda.start();

                                        jQuery.ajax({
                                            url     : ajaxurl,
                                            data    : {action: 'bookly_get_payment_info', payment_id: payment_id, csrf_token: BooklyL10n.csrf_token},
                                            dataType: 'json',
                                            type    : 'POST',
                                            success : function (response) {
                                                if (response.success) {
                                                    if (scope.callback) {
                                                        scope.$apply(function ($scope) {
                                                            $scope.callback({
                                                                payment_action: 'bind',
                                                                payment_id    : payment_id,
                                                                payment_title : response.data.payment_title,
                                                                payment_type  : response.data.payment_type,
                                                                customer_id   : customer_id
                                                            });
                                                        });
                                                    }
                                                }
                                            }
                                        });
                                        jQuery(element).modal('hide');
                                    });
                                    var $adjust_button  = jQuery('#bookly-js-adjustment-button', $body),
                                        $adjust_field   = jQuery('#bookly-js-adjustment-field', $body),
                                        $adjust_reason  = jQuery('#bookly-js-adjustment-reason', $body),
                                        $adjust_amount  = jQuery('#bookly-js-adjustment-amount', $body),
                                        $adjust_tax     = jQuery('#bookly-js-adjustment-tax', $body),
                                        $adjust_apply   = jQuery('#bookly-js-adjustment-apply', $body),
                                        $adjust_cancel  = jQuery('#bookly-js-adjustment-cancel', $body);
                                    $adjust_button.on('click', function () {
                                        $adjust_field.show();
                                        $adjust_reason.focus();
                                    });
                                    $adjust_cancel.on('click', function () {
                                        $adjust_field.hide();
                                    });
                                    $adjust_apply.on('click', function () {
                                        $body.html('<div class="bookly-loading"></div>');
                                        jQuery.ajax({
                                            url     : ajaxurl,
                                            data    : {
                                                action    : 'bookly_pro_add_payment_adjustment',
                                                payment_id: payment_id,
                                                reason: $adjust_reason.val(),
                                                amount: $adjust_amount.val(),
                                                tax   : $adjust_tax.val() || 0,
                                                csrf_token: BooklyL10n.csrf_token
                                            },
                                            dataType: 'json',
                                            type    : 'POST',
                                            success : function (response) {
                                                if (response.success) {
                                                    element.trigger('refresh', [payment_id]);
                                                    // Reload DataTable.
                                                    var $table = jQuery('table#bookly-payments-list.dataTable');
                                                    if ($table.length) {
                                                        $table.DataTable().ajax.reload();
                                                    }
                                                }
                                            }
                                        });
                                    });
                                } else {
                                    $body.html(response.data.html);
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