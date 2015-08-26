;(function() {
    var module = angular.module('customers', ['ui.utils', 'ui.date', 'newCustomerDialog', 'ngSanitize']);

    module.factory('dataSource', function($q, $rootScope) {
        var ds = {
            customers : [],
            total     : 0,
            pages     : [],
            form      : {
                new_customer : {
                    name       : null,
                    wp_user_id : null,
                    phone      : null,
                    email      : null,
                    notes      : null
                }
            },
            loadData  : function(params) {
                var deferred = $q.defer();
                jQuery.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : jQuery.extend({ action : 'ab_get_customers' }, params),
                    dataType : 'json',
                    success : function(response) {
                        if (response.success) {

                            ds.customers = response.data.customers;
                            ds.total     = response.data.total;
                            ds.pages     = [];
                            ds.paginator = {beg : false, end: false};
                            var neighbor = 5;
                            var beg      = Math.max(1, response.data.active_page - neighbor);
                            var end      = Math.min(response.data.pages, (response.data.active_page + neighbor));
                            if (beg > 1) {
                                ds.paginator.beg = true;
                                beg++;
                            }
                            for (var i = beg; i < end; i++) {
                                ds.pages.push({ number : i, active : response.data.active_page == i });
                            }
                            if (end >= response.data.pages) {
                                ds.pages.push({number: response.data.pages, active: response.data.active_page == response.data.pages});
                            } else {
                                ds.paginator.end = {number: response.data.pages, active: false};
                            }
                            for (var i = 0; i < ds.customers.length; ++ i) {
                                for (var j = 0; j < BooklyL10n.wp_users.length; ++ j) {
                                    if (ds.customers[i].wp_user_id == BooklyL10n.wp_users[j].ID) {
                                        ds.customers[i].wp_user = BooklyL10n.wp_users[j];
                                        break;
                                    }
                                }
                            }
                        }
                        $rootScope.$apply(deferred.resolve);
                    },
                    error : function() {
                        ds.customers = [];
                        ds.total     = 0;
                        $rootScope.$apply(deferred.resolve);
                    }
                });
                return deferred.promise;
            }
        };
        ds.wp_users = BooklyL10n.wp_users;
        return ds;
    });

    module.factory('intlTelInputSrv', function() {
        var srv = {
            elements: {},
            init: function(id, element) {
                if (srv.elements[id]) {
                    srv.destroy(id);
                }
                srv.elements[id] = element;
                srv.elements[id].intlTelInput({
                    preferredCountries: [BooklyL10n.country],
                    defaultCountry: BooklyL10n.country,
                    geoIpLookup: function(callback) {
                        jQuery.get(ajaxurl, {action: 'ab_ip_info'}, function() {}, 'json' ).always(function(resp) {
                            var countryCode = (resp && resp.country) ? resp.country : '';
                            callback(countryCode);
                        });
                    },
                    utilsScript: BooklyL10n.intlTelInput_utils
                });
            },
            destroy: function(id) {
                if (srv.elements[id]) {
                    srv.elements[id].val(srv.getNumber(id));
                    srv.elements[id].intlTelInput('destroy');
                    delete srv.elements[id];
                }
            },
            getNumber: function(id) {
                return srv.elements[id] ? srv.elements[id].intlTelInput('getNumber') : null;
            }
        };

        return srv;
    });

    module.controller('customersCtrl', function($scope, dataSource, intlTelInputSrv) {
        // Set up initial data.
        var params = {
            page   : 1,
            sort   : 'name',
            order  : 'asc',
            filter : ''
        };
        $scope.loading   = true;
        $scope.css_class = {
            name               : 'asc',
            wp_user            : '',
            phone              : '',
            email              : '',
            notes              : '',
            last_appointment   : '',
            total_appointments : '',
            payments           : ''
        };
        // Set up data source (data will be loaded in reload function).
        $scope.dataSource = dataSource;

        $scope.reload = function( opt ) {
            $scope.loading = true;
            if (opt !== undefined) {
                if (opt.sort !== undefined) {
                    if (params.sort === opt.sort) {
                        // Toggle order when sorting by the same field.
                        params.order = params.order === 'asc' ? 'desc' : 'asc';
                    } else {
                        params.order = 'asc';
                    }
                    $scope.css_class = {
                        name               : '',
                        wp_user            : '',
                        phone              : '',
                        email              : '',
                        notes              : '',
                        last_appointment   : '',
                        total_appointments : '',
                        payments           : ''
                    };
                    $scope.css_class[opt.sort] = params.order;
                }
                jQuery.extend(params, opt);
            }
            dataSource.loadData(params).then(function() {
                $scope.loading = false;
            });
        };

        var filter_delay = null;
        $scope.$watch('filter', function() {
            if (filter_delay !== null) {
                clearTimeout(filter_delay);
            }
            filter_delay = setTimeout(function() {
                filter_delay = null;
                $scope.$apply(function($scope) {
                    $scope.reload({filter: $scope.filter});
                });
            }, 400);
        });

        /**
         * Edit customer.
         *
         * @param object customer
         */
        $scope.saveCustomer = function(customer) {
            customer.edit_name    = false;
            customer.edit_wp_user = false;
            customer.edit_phone   = false;
            customer.edit_email   = false;
            customer.edit_notes   = false;
            customer.errors       = {};

            $scope.loading = true;
            jQuery.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action     : 'ab_save_customer',
                    id         : customer.id,
                    wp_user_id : customer.wp_user ? customer.wp_user.ID : null,
                    name       : customer.name,
                    phone      : customer.phone,
                    email      : customer.email,
                    notes      : customer.notes
                },
                dataType : 'json',
                success  : function(response) {
                    $scope.$apply(function($scope) {
                        if ( response.success == false) {
                            jQuery.each(response.errors, function(field, errors) {
                                customer.errors[field]    = {};
                                customer['edit_' + field] = true;
                                jQuery.each(errors, function(key, error) {
                                    customer.errors[field][error] = true;
                                });
                            });
                        }
                        $scope.loading = false;
                    });
                },
                error : function(response) {
                    $scope.$apply(function($scope) {
                        $scope.loading = false;
                    });
                }
            });
        };

        $scope.saveCustomerPhone = function(customer) {
            if (customer.edit_phone) {
                customer.phone = intlTelInputSrv.getNumber(customer.id);
                $scope.saveCustomer(customer);
            }
        };

        /**
         * Callback for creating new customer.
         *
         * @param object customer
         */
        $scope.createCustomer = function(customer) {
            dataSource.customers.push(customer);
            $scope.reload(params.page);
        };

        /**
         * Delete customer.
         */
        $scope.deleteCustomers = function() {
            var ids = [];
            jQuery('table input[type=checkbox]:checked').each(function() {
                ids.push(jQuery(this).data('customer_id'));
            });
            if (ids.length) {
                if (delete_customers_choice === null) {
                    $modal.data('customer_ids', ids).modal('show');
                } else {
                    deleteCustomers(ids, delete_customers_choice);
                }
            } else {
                alert(BooklyL10n.please_select_at_least_one_row);
            }
        };

        /**
         * Popup for deleting customer.
         */
        var delete_customers_choice = null;
        var deleteCustomers = function(ids, with_wp_user) {
            $scope.loading = true;
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ab_delete_customer',
                    ids   : ids,
                    with_wp_user: with_wp_user ? 1 : 0
                },
                dataType  : 'json',
                success   : function (response) {
                    $scope.$apply(function ($scope) {
                        $scope.reload();
                    });
                }
            });
        };
        var $modal = jQuery('#ab-customer-delete');
        $modal
            .on('click', '.ab-yes', function () {
                $modal.modal('hide');
                if ( jQuery('#ab-remember-my-choice').prop('checked') ) {
                    delete_customers_choice = true;
                }
                deleteCustomers($modal.data('customer_ids'), true);
            })
            .on('click', '.ab-no', function () {
                if ( jQuery('#ab-remember-my-choice').prop('checked') ) {
                    delete_customers_choice = false;
                }
                deleteCustomers($modal.data('customer_ids'), false);
            });

    });

    /**
     * Directive for setting focus to element.
     */
    module.directive('focusMe', function($timeout) {
        return {
            link: function(scope, element, attrs) {
                scope.$watch(attrs.focusMe, function(value) {
                    if (value) {
                        $timeout(function() {
                            element[0].focus();
                        });
                    }
                });
            }
        };
    });

    module.directive('intlTelInput', ['intlTelInputSrv', function(intlTelInputSrv) {
        return function(scope, element, attrs) {
            scope.$watch(attrs.intlTelInput, function(value) {
                if (value) {
                    intlTelInputSrv.init(scope.$eval(attrs.intlTelInputId), element);
                } else {
                    intlTelInputSrv.destroy(scope.$eval(attrs.intlTelInputId));
                }
            });
        };
    }]);

    module.directive('clickOutside', ['$document', function ($document) {
        return {
            restrict: 'A',
            scope: {
                clickOutside: '&'
            },
            link: function ($scope, elem, attr) {
                $document.on('click', function (e) {
                    var element;

                    if (!e.target) return;

                    for (element = e.target; element; element = element.parentNode) {
                        if (element == elem.get(0)) {
                            return;
                        }
                    }

                    $scope.$apply(function($scope) {
                        $scope.$eval($scope.clickOutside);
                    });
                });
            }
        };
    }]);

    module.filter('nl2br', function() {
        return function(input) {
            return ('' + input).split('\n').join('<br>');
        };
    });

})();