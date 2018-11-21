jQuery(function ($) {

    var Details = function ($container, options) {
        var obj = this;
        jQuery.extend(obj.options, options);

        if (Object.keys(obj.options.get_details).length === 0) {
            // backend united edit & details in one request.
            initDetails($container);
        } else {
            // get details content.
            $container.html('<div class="bookly-loading"></div>');
            $.ajax({
                url        : ajaxurl,
                data       : obj.options.get_details,
                dataType   : 'json',
                xhrFields  : {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success    : function (response) {
                    $container.html(response.data.html);
                    initDetails($container);
                }
            });
        }

        function initDetails($container) {
            var $form                   = $('.bookly-js-staff-details', $container),
                $staff_full_name        = $('#bookly-full-name', $container),
                $staff_wp_user          = $('#bookly-wp-user', $container),
                $staff_email            = $('#bookly-email', $container),
                $staff_phone            = $('#bookly-phone', $container),
                $staffLocations         = $('#bookly-js-locations', $container),
                $unsaved_changes        = $('.bookly-js-unsaved-changes'),
                $unsaved_changes_save   = $('.bookly-js-unsaved-changes .bookly-js-save-changes'),
                $unsaved_changes_ignore = $('.bookly-js-unsaved-changes .bookly-js-ignore-changes'),
                has_changes             = false
            ;

            if (obj.options.intlTelInput.enabled) {
                $staff_phone.intlTelInput({
                    preferredCountries: [obj.options.intlTelInput.country],
                    initialCountry    : obj.options.intlTelInput.country,
                    geoIpLookup       : function (callback) {
                        $.get('https://ipinfo.io', function () {
                        }, 'jsonp').always(function (resp) {
                            var countryCode = (resp && resp.country) ? resp.country : '';
                            callback(countryCode);
                        });
                    },
                    utilsScript       : obj.options.intlTelInput.utils
                });
            }

            $staff_wp_user.on('change', function () {
                if (this.value) {
                    $staff_full_name.val($staff_wp_user.find(':selected').text());
                    $staff_email.val($staff_wp_user.find(':selected').data('email'));
                }
            });

            $staffLocations.booklyDropdown();

            $('button:reset', $container).on('click', function () {
                setTimeout(function () {
                    $staffLocations.booklyDropdown('reset');
                }, 0);
            });

            $container.on('change', 'select,input,textarea', function () {
                has_changes = true;
            });
            $container.on('click', '.bookly-js-google-calendar-row a', function (e) {
                var url = $(this).attr('href');
                if (has_changes) {
                    e.preventDefault();
                    $unsaved_changes.modal('show');
                    $unsaved_changes.data('url', url);
                }
            });
            $unsaved_changes_save.on('click', function () {
                var ladda = Ladda.create(this);
                ladda.start();
                save(function (response) {
                    if (response.success) {
                        window.location.href = $unsaved_changes.data('url');
                    } else {
                        obj.options.booklyAlert({error: [response.data.error]});
                    }
                    ladda.stop();
                });
            });
            $unsaved_changes_ignore.on('click', function () {
                window.location.href = $unsaved_changes.data('url');
            });

            // Save staff member details.
            $('#bookly-details-save', $container).on('click', function (e) {
                e.preventDefault();
                var ladda = Ladda.create(this);
                ladda.start();
                // for BooklyPro listener in archive.js
                // When button disabled, listeners don't process
                $(this).removeAttr('disabled');
                save(function (response) {
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
                });
            });

            function save(callback) {
                var data         = $form.serializeArray(),
                    $staff_phone = $('#bookly-phone', $form),
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
                data.push({name: 'phone', value: phone});

                $.ajax({
                    type       : 'POST',
                    url        : ajaxurl,
                    data       : data,
                    dataType   : 'json',
                    xhrFields  : {withCredentials: true},
                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                    success    : function (response) {
                        has_changes = false;
                        callback(response);
                    }
                });
            }
        }

    };

    Details.prototype.options = {
        intlTelInput: {},
        get_details : {
            action    : 'bookly_get_staff_details',
            id        : -1,
            csrf_token: ''
        },
        l10n        : {},
        booklyAlert : window.booklyAlert
    };

    window.BooklyStaffDetails = Details;
});