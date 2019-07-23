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

            $container
                .on('change', 'select,input,textarea', function () {
                    has_changes = true;
                })
                .on('click', '.bookly-js-google-calendar-row a', function (e) {
                    var url = $(this).attr('href');
                    if (has_changes) {
                        e.preventDefault();
                        $unsaved_changes.modal('show');
                        $unsaved_changes.data('url', url);
                    }
                })
                .on('click', '.bookly-js-outlook-calendar-row a', function (e) {
                    var url = $(this).attr('href');
                    if (has_changes) {
                        e.preventDefault();
                        $unsaved_changes.modal('show');
                        $unsaved_changes.data('url', url);
                    }
                })
                .on('change', '[name=google_disconnect]', function () {
                    has_changes = true;
                    $('.bookly-js-google-calendars-list', $container).toggle(!this.checked);
                }).on('change', '[name=outlook_disconnect]', function () {
                    has_changes = true;
                    $('.bookly-js-outlook-calendars-list', $container).toggle(!this.checked);
                })
                .on('click', '.bookly-pretty-indicator', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var frame = wp.media({
                        library: {type: 'image'},
                        multiple: false
                    });
                    frame
                        .on('select', function () {
                            var selection = frame.state().get('selection').toJSON(),
                                img_src;
                            if (selection.length) {
                                if (selection[0].sizes['thumbnail'] !== undefined) {
                                    img_src = selection[0].sizes['thumbnail'].url;
                                } else {
                                    img_src = selection[0].url;
                                }
                                $container.find('[name=attachment_id]').val(selection[0].id).trigger('change');
                                $('#bookly-js-staff-avatar').find('.bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                                $('.bookly-thumb-delete').show();
                                $(this).hide();
                                has_changes = true;
                            }
                        })
                        .on('close', function(){
                            $('body').addClass('modal-open');
                        });

                    frame.open();
                })
                // Delete staff avatar
                .on('click', '.bookly-thumb-delete', function () {
                    var $thumb = $(this).parents('.bookly-js-image');
                    $thumb.attr('style', '');
                    $container.find('[name=attachment_id]').val('').trigger('change');
                    has_changes = true;
                })
                // Save staff member details.
                .on('click', '#bookly-details-save', function (e) {
                    e.preventDefault();
                    var ladda = Ladda.create(this);
                    ladda.start();
                    // for BooklyPro listener in archive.js
                    // When button disabled, listeners don't process
                    $(this).removeAttr('disabled');

                    save(function (response) {
                        if (response.success) {
                            obj.options.savingStatus({success: [obj.options.l10n.saved]});
                            $('[bookly-js-staff-name-' + obj.options.get_details.id + ']').text($('#bookly-full-name', $form).val());
                            if (typeof obj.options.renderWpUsers === 'function') {
                                obj.options.renderWpUsers(response.data.wp_users);
                            }
                        } else {
                            obj.options.savingStatus({error: [response.data.error]});
                        }
                        ladda.stop();
                    });
                })
                .on('click', 'button:reset', function () {
                    setTimeout(function () {
                        $staffLocations.booklyDropdown('reset');
                    }, 0);
                });

            $unsaved_changes_save.on('click', function () {
                var ladda = Ladda.create(this);
                ladda.start();
                save(function (response) {
                    if (response.success) {
                        window.location.href = $unsaved_changes.data('url');
                    } else {
                        obj.options.savingStatus({error: [response.data.error]});
                    }
                    ladda.stop();
                });
            });
            $unsaved_changes_ignore.on('click', function () {
                window.location.href = $unsaved_changes.data('url');
            });

            $unsaved_changes
                .on('hidden.bs.modal', function () {
                    jQuery('body').addClass('modal-open');
                });

            function save(callback) {
                var data         = $form.serializeArray(),
                    $staff_phone = $('#bookly-phone', $form),
                    phone;
                try {
                    phone = obj.options.intlTelInput.enabled ? $staff_phone.intlTelInput('getNumber') : $staff_phone.val();
                    if (phone == '') {
                        phone = $staff_phone.val();
                    }
                } catch (error) {  // In case when intlTelInput can't return phone number.
                    phone = $staff_phone.val();
                }
                data.push({name: 'action', value: 'bookly_update_staff'});
                data.push({name: 'phone', value: phone});
                data.push({name: 'tab', value: phone});

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
        savingStatus : function (data) {
            $(document.body).trigger('staff_edit.save', [data]);
        }
    };

    window.BooklyStaffDetails = Details;
});