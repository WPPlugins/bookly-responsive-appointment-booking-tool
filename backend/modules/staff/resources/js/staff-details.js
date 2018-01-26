jQuery(function ($) {

    var Details = function($container, options) {
        var obj  = this;
        jQuery.extend(obj.options, options);

        if (Object.keys(obj.options.get_details).length === 0) {
            // backend united edit & details in one request.
            initDetails($container);
        } else {
            // get details content.
            $container.html('<div class="bookly-loading"></div>');
            $.ajax({
                url         : ajaxurl,
                data        : obj.options.get_details,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    $container.html(response.data.html);
                    initDetails($container);
                }
            });
        }

        function initDetails($container) {
            var $staff_full_name = $('#bookly-full-name', $container),
                $staff_wp_user = $('#bookly-wp-user', $container),
                $staff_email   = $('#bookly-email', $container),
                $staff_phone   = $('#bookly-phone', $container),
                $location_row  = $('.locations-row', $container)
            ;

            if (obj.options.intlTelInput.enabled) {
                $staff_phone.intlTelInput({
                    preferredCountries: [obj.options.intlTelInput.country],
                    initialCountry: obj.options.intlTelInput.country,
                    geoIpLookup: function (callback) {
                        $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                            var countryCode = (resp && resp.country) ? resp.country : '';
                            callback(countryCode);
                        });
                    },
                    utilsScript: obj.options.intlTelInput.utils
                });
            }

            $staff_wp_user.on('change', function () {
                if (this.value) {
                    $staff_full_name.val($staff_wp_user.find(':selected').text());
                    $staff_email.val($staff_wp_user.find(':selected').data('email'));
                }
            });

            $('input.bookly-js-all-locations, input.bookly-location', $container).on('change', function () {
                if ($(this).hasClass('bookly-js-all-locations')) {
                    $location_row.find('.bookly-location').prop('checked', $(this).prop('checked'));
                } else {
                    $location_row.find('.bookly-js-all-locations').prop('checked', $location_row.find('.bookly-location:not(:checked)').length == 0);
                }
                updateLocationsButton();
            });

            $('button:reset', $container).on('click', function () {
                setTimeout(updateLocationsButton, 0);
            });

            function updateLocationsButton() {
                var locations_checked = $location_row.find('.bookly-location:checked').length;
                if (locations_checked == 0) {
                    $location_row.find('.bookly-locations-count').text(obj.options.l10n.selector.nothing_selected);
                } else if (locations_checked == 1) {
                    $location_row.find('.bookly-locations-count').text($location_row.find('.bookly-location:checked').data('location_name'));
                } else {
                    if (locations_checked == $location_row.find('.bookly-location').length) {
                        $location_row.find('.bookly-locations-count').text(obj.options.l10n.selector.all_selected);
                    } else {
                        $location_row.find('.bookly-locations-count').text(locations_checked + '/' + $location_row.find('.bookly-location').length);
                    }
                }
            }

            updateLocationsButton();

            // Save staff member details.
            $('#bookly-details-save', $container).on('click',function(e){
                e.preventDefault();
                var $form = $(this).closest('form'),
                    data  = $form.serializeArray(),
                    ladda = Ladda.create(this),
                    $staff_phone = $('#bookly-phone',$form),
                    phone;
                try {
                    phone = BooklyL10n.intlTelInput.enabled ? $staff_phone.intlTelInput('getNumber') : $staff_phone.val();
                    if (phone == '') {
                        phone = $staff_phone.val();
                    }
                } catch (error) {  // In case when intlTelInput can't return phone number.
                    phone = $staff_phone.val();
                }
                data.push({name: 'action', value: 'bookly_update_staff'});
                data.push({name: 'phone',  value: phone});
                ladda.start();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    xhrFields: {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success: function (response) {
                        if (response.success) {
                            obj.options.booklyAlert({success: [obj.options.l10n.saved]});
                            $('[bookly-js-staff-name-' + obj.options.get_details.id + ']').text($('#bookly-full-name', $form).val());
                            if (typeof obj.options.renderWpUsers === 'function') {
                                obj.options.renderWpUsers(response.data.wp_users);
                            }
                        } else {
                            obj.options.booklyAlert({error: [response.data.error]});
                        }
                        ladda.stop();
                    }
                });
            });
        }

    };

    Details.prototype.options = {
        intlTelInput: {},
        get_details : {
            action: 'bookly_get_staff_details',
            id    : -1,
            csrf_token: ''
        },
        l10n        : {},
        booklyAlert : window.booklyAlert
    };

    window.BooklyStaffDetails = Details;
});