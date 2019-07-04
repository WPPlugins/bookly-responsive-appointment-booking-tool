;(function() {

    angular.module('customerDialog', ['ui.date']).directive('customerDialog', function() {
        return {
            restrict : 'A',
            replace  : true,
            scope    : {
                callback : '&customerDialog',
                form     : '=customer'
            },
            templateUrl : 'bookly-customer-dialog.tpl',
            // The linking function will add behavior to the template.
            link: function(scope, element, attrs) {
                // Init properties.
                // Form fields.
                if (!scope.form) {
                    scope.form = {
                        id                 : '',
                        wp_user_id         : '',
                        group_id           : '',
                        full_name          : '',
                        first_name         : '',
                        last_name          : '',
                        phone              : '',
                        email              : '',
                        country            : '',
                        state              : '',
                        postcode           : '',
                        city               : '',
                        street             : '',
                        street_number      : '',
                        additional_address : '',
                        info_fields        : [],
                        notes              : '',
                        birthday           : ''
                    };
                    BooklyL10nCustDialog.infoFields.forEach(function (field) {
                        scope.form.info_fields.push({id: field.id, value: field.type === 'checkboxes' ? [] : ''});
                    });
                }
                // Form errors.
                scope.errors = {
                    name: {required: false}
                };
                scope.$watch('form', function(newValue, oldValue) {
                    if (newValue.name) {
                        scope.errors.name.required = false;
                    }
                });
                // Loading indicator.
                scope.loading = false;

                // Init intlTelInput.
                if (BooklyL10nCustDialog.intlTelInput.enabled) {
                    element.find('#phone').intlTelInput({
                        preferredCountries: [BooklyL10nCustDialog.intlTelInput.country],
                        initialCountry: BooklyL10nCustDialog.intlTelInput.country,
                        geoIpLookup: function (callback) {
                            jQuery.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                                var countryCode = (resp && resp.country) ? resp.country : '';
                                callback(countryCode);
                            });
                        },
                        utilsScript: BooklyL10nCustDialog.intlTelInput.utils
                    });
                }

                // Init select2 for wp_users.
                jQuery('#wp_user')
                    .val(null)
                    .on('select2:unselecting', function(e) {
                        e.preventDefault();
                        jQuery(this).val(null).trigger('change');
                    })
                    .select2({
                        width: '100%',
                        theme: 'bootstrap',
                        allowClear: true,
                        dropdownParent: jQuery('#bookly-customer-dialog'),
                        language: {
                            noResults: function () {
                                return BooklyL10nCustDialog.noResultFound;
                            }
                        }
                    });

                // Do stuff on modal hide.
                element.on('hidden.bs.modal', function () {
                    // Fix scroll issues when another modal is shown.
                    if (jQuery('.modal-backdrop').length) {
                        jQuery('body').addClass('modal-open');
                    }
                });
                scope.changeWpUser = function () {
                    var $user = jQuery('#wp_user option:selected'),
                        email = $user.attr('data-email') != undefined ? $user.attr('data-email') : '',
                        first_name = $user.attr('data-first-name') != undefined ? $user.attr('data-first-name') : '',
                        last_name = $user.attr('data-last-name') != undefined ? $user.attr('data-last-name') : '',
                        phone = $user.attr('data-phone') != undefined ? $user.attr('data-phone') : '',
                        display_name = $user.text().trim();
                    if (BooklyL10nCustDialog.first_last_name == 1) {
                        if (!first_name.length && !last_name.length) {
                            var name_parts = display_name.split(' ');
                            first_name = name_parts[0];
                            name_parts.splice(0, 1);
                            last_name = name_parts.join(' ');
                        }
                        if (first_name.length) {
                            scope.form.first_name = first_name;
                        }
                        if (last_name.length) {
                            scope.form.last_name = last_name;
                        }
                    } else {
                        if (first_name.length || last_name.length) {
                            scope.form.full_name = (first_name + ' ' + last_name).trim();
                        } else {
                            scope.form.full_name = display_name;
                        }
                    }
                    if (email.length) {
                        scope.form.email = email;
                    }
                    if (phone.length) {
                        scope.form.phone = phone;
                    }
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
                        data : jQuery.extend({ action : 'bookly_save_customer', csrf_token : BooklyL10nCustDialog.csrf_token }, scope.form),
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
                                        id                 : '',
                                        wp_user_id         : '',
                                        group_id           : '',
                                        full_name          : '',
                                        first_name         : '',
                                        last_name          : '',
                                        phone              : '',
                                        email              : '',
                                        country            : '',
                                        state              : '',
                                        postcode           : '',
                                        city               : '',
                                        street             : '',
                                        street_number      : '',
                                        additional_address : '',
                                        info_fields        : [],
                                        notes              : '',
                                        birthday           : ''
                                    };
                                    // Close the dialog.
                                    element.modal('hide');
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

                /**
                 * Datepicker options.
                 */
                scope.datePickerOptions = jQuery.extend({
                        beforeShow: function (input, inst) {
                            jQuery(document).off('focusin.bs.modal');
                        },
                        onClose: function () {
                            jQuery(document).on('focusin.bs.modal');
                        },
                    },
                    BooklyL10nCustDialog.datePicker);

                /**
                 * Toggle checkbox info field.
                 */
                scope.toggleCheckbox = function (i, value) {
                    var idx = scope.form.info_fields[i].value.indexOf(value);

                    // Is currently selected.
                    if (idx > -1) {
                        scope.form.info_fields[i].value.splice(idx, 1);
                    }
                    // Is newly selected.
                    else {
                        scope.form.info_fields[i].value.push(value);
                    }
                };
            }
        };
    });

})();