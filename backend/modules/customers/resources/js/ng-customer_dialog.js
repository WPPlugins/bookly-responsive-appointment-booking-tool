;(function() {

    angular.module('customerDialog', []).directive('customerDialog', function() {
        return {
            restrict : 'A',
            replace  : true,
            scope    : {
                callback  : '&customerDialog',
                form      : '=customer'
            },
            templateUrl : 'bookly-customer-dialog.tpl',
            // The linking function will add behavior to the template.
            link: function(scope, element, attrs) {
                // Init properties.
                var init = function() {
                    // Form fields.
                    if (!scope.form) {
                        scope.form = {
                            id         : '',
                            wp_user_id : '',
                            name       : '',
                            phone      : '',
                            email      : '',
                            notes      : ''
                        };
                    }
                    if (BooklyL10nCustDialog.intlTelInput.enabled) {
                        element.find('#phone').intlTelInput({
                            preferredCountries: [BooklyL10nCustDialog.intlTelInput.country],
                            defaultCountry: BooklyL10nCustDialog.intlTelInput.country,
                            geoIpLookup: function (callback) {
                                jQuery.get(ajaxurl, {action: 'bookly_ip_info'}, function () {
                                }, 'json').always(function (resp) {
                                    var countryCode = (resp && resp.country) ? resp.country : '';
                                    callback(countryCode);
                                });
                            },
                            utilsScript: BooklyL10nCustDialog.intlTelInput.utils
                        });
                    }
                    // Form errors.
                    scope.errors = {
                        name: {required: false}
                    };
                    // Loading indicator.
                    scope.loading = false;

                    jQuery('#bookly-customer-dialog').modal('hide')
                        .on('hidden.bs.modal', function () {
                            if (jQuery('#bookly-appointment-dialog').length) {
                                jQuery('body').addClass('modal-open');
                            }
                        });
                };

                // Run init.
                init();

                // On 'Cancel' button click.
                scope.closeDialog = function () {
                    // Close the dialog.
                    jQuery('#bookly-customer-dialog').modal('hide');
                    // Re-init all properties.
                    init();
                };

                /**
                 * Send form to server.
                 */
                scope.processForm = function() {
                    scope.errors  = {};
                    scope.loading = true;
                    scope.form.phone = BooklyL10nCustDialog.intlTelInput.enabled
                        ? element.find('#phone').intlTelInput('getNumber')
                        : element.find('#phone').val();
                    jQuery.ajax({
                        url  : ajaxurl,
                        type : 'POST',
                        data : jQuery.extend({ action : 'bookly_save_customer' }, scope.form),
                        dataType : 'json',
                        success : function ( response ) {
                            scope.$apply(function(scope) {
                                if (response.success) {
                                    response.customer.custom_fields = [];
                                    response.customer.extras = [];
                                    response.customer.status = BooklyL10nCustDialog.default_status;
                                    // Send new customer to the parent scope.
                                    scope.callback({customer : response.customer});
                                    scope.form = {
                                        id         : '',
                                        wp_user_id : '',
                                        name       : '',
                                        phone      : '',
                                        email      : '',
                                        notes      : ''
                                    };
                                    // Close the dialog.
                                    scope.closeDialog();
                                } else {
                                    // Set errors.
                                    jQuery.each(response.errors, function(field, errors) {
                                        scope.errors[field] = {};
                                        jQuery.each(errors, function(key, error) {
                                            scope.errors[field][error] = true;
                                        });
                                    });
                                }
                                scope.loading = false;
                            });
                        },
                        error : function() {
                            scope.$apply(function(scope) {
                                scope.loading = false;
                            });
                        }
                    });
                };
            }
        };
    });

})();