(function ($) {
    'use strict';

    $ = $ && $.hasOwnProperty('default') ? $['default'] : $;

    var opt = {};

    /**
     * Start Ladda on given button.
     */
    function laddaStart(elem) {
        var ladda = Ladda.create(elem);
        ladda.start();
        return ladda;
    }

    /**
     * Scroll to element if it is not visible.
     *
     * @param $elem
     */
    function scrollTo($elem) {
        var elemTop   = $elem.offset().top;
        var scrollTop = $(window).scrollTop();
        if (elemTop < $(window).scrollTop() || elemTop > scrollTop + window.innerHeight) {
            $('html,body').animate({ scrollTop: (elemTop - 24) }, 500);
        }
    }

    /**
     * Complete step.
     */
    function stepComplete(params) {
        var data = $.extend({
                action    : 'bookly_render_complete',
                csrf_token: BooklyL10n.csrf_token,
            }, params),
            $container = opt[params.form_id].$container;
        $.ajax({
            url: BooklyL10n.ajaxurl,
            data: data,
            dataType: 'json',
            xhrFields: {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success: function (response) {
                if (response.success) {
                    if (response.final_step_url && !data.error) {
                        document.location.href = response.final_step_url;
                    } else {
                        $container.html(response.html);
                        scrollTo($container);
                    }
                }
            }
        });
    }

    /**
     * Payment step.
     */
    function stepPayment(params) {
        var $container = opt[params.form_id].$container;
        $.ajax({
            type       : 'POST',
            url        : BooklyL10n.ajaxurl,
            data       : {action: 'bookly_render_payment', csrf_token : BooklyL10n.csrf_token, form_id: params.form_id, page_url: document.URL.split('#')[0]},
            dataType   : 'json',
            xhrFields  : {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success    : function (response) {
                if (response.success) {
                    // If payment step is disabled.
                    if (response.disabled) {
                        save(params.form_id);
                        return;
                    }

                    $container.html(response.html);
                    scrollTo($container);
                    if (opt[params.form_id].status.booking == 'cancelled') {
                        opt[params.form_id].status.booking = 'ok';
                    }
                    // Init stripe intents form
                    if ($container.find('#bookly-stripe-card-field').length) {
                        if (response.stripe_publishable_key) {
                            var stripe = Stripe(response.stripe_publishable_key, {
                                betas: ['payment_intent_beta_3']
                            });
                            var elements = stripe.elements();
                            var stripe_card = elements.create("card");

                            stripe_card.mount("#bookly-stripe-card-field");
                        } else {
                            $container.find('.bookly-stripe #bookly-stripe-card-field').hide();
                            $container.find('.pay-card .bookly-js-next-step').prop('disabled', true);
                            $container.find('.bookly-stripe .bookly-js-card-error').text('Please call Stripe() with your publishable key. You used an empty string.');
                        }
                    }

                    var $payments  = $('.bookly-payment', $container),
                        $apply_coupon_button = $('.bookly-js-apply-coupon', $container),
                        $coupon_input = $('input.bookly-user-coupon', $container),
                        $coupon_error = $('.bookly-js-coupon-error', $container),
                        $deposit_mode = $('input[type=radio][name=bookly-full-payment]', $container),
                        $coupon_info_text = $('.bookly-info-text-coupon', $container),
                        $buttons = $('.bookly-gateway-buttons,form.bookly-authorize_net,form.bookly-stripe', $container)
                    ;
                    $payments.on('click', function() {
                        $buttons.hide();
                        $('.bookly-gateway-buttons.pay-' + $(this).val(), $container).show();
                        if ($(this).val() == 'card') {
                            $('form.bookly-' + $(this).data('form'), $container).show();
                        }
                    });
                    $payments.eq(0).trigger('click');

                    $deposit_mode.on('change', function () {
                        var data = {
                            action       : 'bookly_deposit_payments_apply_payment_method',
                            csrf_token   : BooklyL10n.csrf_token,
                            form_id      : params.form_id,
                            deposit_full : $(this).val()
                        };
                        $(this).hide();
                        $(this).prev().css('display', 'inline-block');
                        $.ajax({
                            type       : 'POST',
                            url        : BooklyL10n.ajaxurl,
                            data       : data,
                            dataType   : 'json',
                            xhrFields  : {withCredentials: true},
                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                            success    : function (response) {
                                if (response.success) {
                                    stepPayment({form_id: params.form_id});
                                }
                            }
                        });
                    });

                    $apply_coupon_button.on('click', function (e) {
                        var ladda = laddaStart(this);
                        $coupon_error.text('');
                        $coupon_input.removeClass('bookly-error');

                        var data = {
                            action      : 'bookly_coupons_apply_coupon',
                            csrf_token  : BooklyL10n.csrf_token,
                            form_id     : params.form_id,
                            coupon_code : $coupon_input.val()
                        };

                        $.ajax({
                            type        : 'POST',
                            url         : BooklyL10n.ajaxurl,
                            data        : data,
                            dataType    : 'json',
                            xhrFields   : {withCredentials: true},
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success     : function (response) {
                                if (response.success) {
                                    stepPayment({form_id: params.form_id});
                                } else {
                                    $coupon_error.html(opt[params.form_id].errors[response.error]);
                                    $coupon_input.addClass('bookly-error');
                                    $coupon_info_text.html(response.text);
                                    scrollTo($coupon_error);
                                    ladda.stop();
                                }
                            },
                            error : function () {
                                ladda.stop();
                            }
                        });
                    });

                    $('.bookly-js-next-step', $container).on('click', function (e) {
                        var ladda = laddaStart(this),
                            $form
                        ;
                        if ($('.bookly-payment[value=local]', $container).is(':checked') || $(this).hasClass('bookly-js-coupon-payment')) {
                            // handle only if was selected local payment !
                            e.preventDefault();
                            save(params.form_id);

                        } else if ($('.bookly-payment[value=card]', $container).is(':checked')) {
                            if ($('.bookly-payment[data-form=stripe]', $container).is(':checked')) {
                                $.ajax({
                                    type       : 'POST',
                                    url        : BooklyL10n.ajaxurl,
                                    data       : {
                                        action    : 'bookly_stripe_create_intent',
                                        csrf_token: BooklyL10n.csrf_token,
                                        form_id   : params.form_id
                                    },
                                    dataType   : 'json',
                                    xhrFields  : {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success    : function (response) {
                                        if (response.success) {
                                            stripe.handleCardPayment(
                                                response.intent_secret,
                                                stripe_card
                                            ).then(function (result) {
                                                if (result.error) {
                                                    $.ajax({
                                                        type       : 'POST',
                                                        url        : BooklyL10n.ajaxurl,
                                                        data       : {
                                                            action    : 'bookly_stripe_failed_payment',
                                                            csrf_token: BooklyL10n.csrf_token,
                                                            form_id   : params.form_id,
                                                            intent_id : response.intent_id
                                                        },
                                                        dataType   : 'json',
                                                        xhrFields  : {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success    : function (response) {
                                                            if (response.success) {
                                                                ladda.stop();
                                                                $container.find('.bookly-stripe .bookly-js-card-error').text(result.error.message);
                                                            }
                                                        }
                                                    });
                                                } else {
                                                    $.ajax({
                                                        type       : 'POST',
                                                        url        : BooklyL10n.ajaxurl,
                                                        data       : {
                                                            action    : 'bookly_stripe_process_payment',
                                                            csrf_token: BooklyL10n.csrf_token,
                                                            form_id   : params.form_id,
                                                            intent_id : response.intent_id
                                                        },
                                                        dataType   : 'json',
                                                        xhrFields  : {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success    : function (response) {
                                                            if (response.success) {
                                                                stepComplete({form_id: params.form_id});
                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                        } else {
                                            ladda.stop();
                                            $container.find('.bookly-stripe .bookly-js-card-error').text(response.error_message);
                                        }
                                    }
                                });
                            } else {
                                var card_action = 'bookly_authorize_net_aim_payment';
                                $form = $container.find('.bookly-authorize_net');
                                e.preventDefault();

                                var data = {
                                    action    : card_action,
                                    csrf_token: BooklyL10n.csrf_token,
                                    card      : {
                                        number   : $form.find('input[name="card_number"]').val(),
                                        cvc      : $form.find('input[name="card_cvc"]').val(),
                                        exp_month: $form.find('select[name="card_exp_month"]').val(),
                                        exp_year : $form.find('select[name="card_exp_year"]').val()
                                    },
                                    form_id   : params.form_id
                                };

                                var cardPayment = function (data) {
                                    $.ajax({
                                        type       : 'POST',
                                        url        : BooklyL10n.ajaxurl,
                                        data       : data,
                                        dataType   : 'json',
                                        xhrFields  : {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success    : function (response) {
                                            if (response.success) {
                                                stepComplete({form_id: params.form_id});
                                            } else if (response.error == 'cart_item_not_available') {
                                                handleErrorCartItemNotAvailable(response, params.form_id);
                                            } else if (response.error == 'payment_error') {
                                                ladda.stop();
                                                $form.find('.bookly-js-card-error').text(response.error_message);
                                            }
                                        }
                                    });
                                };
                                cardPayment(data);
                            }
                        } else if (
                               $('.bookly-payment[value=paypal]',     $container).is(':checked')
                            || $('.bookly-payment[value=2checkout]',  $container).is(':checked')
                            || $('.bookly-payment[value=payu_biz]',   $container).is(':checked')
                            || $('.bookly-payment[value=payu_latam]', $container).is(':checked')
                            || $('.bookly-payment[value=payson]',     $container).is(':checked')
                            || $('.bookly-payment[value=mollie]',     $container).is(':checked')
                        ) {
                            e.preventDefault();
                            $form = $(this).closest('form');
                            if ($form.find('input.bookly-payment-id').length > 0 ) {
                                $.ajax({
                                    type       : 'POST',
                                    url        : BooklyL10n.ajaxurl,
                                    xhrFields  : {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    data       : {
                                        action:       'bookly_pro_save_pending_appointment',
                                        csrf_token:   BooklyL10n.csrf_token,
                                        form_id:      params.form_id,
                                        payment_type: $form.data('gateway')
                                    },
                                    dataType   : 'json',
                                    success    : function (response) {
                                        if (response.success) {
                                            $form.find('input.bookly-payment-id').val(response.payment_id);
                                            $form.submit();
                                        } else if (response.error == 'cart_item_not_available') {
                                            handleErrorCartItemNotAvailable(response,params.form_id);
                                        }
                                    }
                                });
                            } else  {
                                $.ajax({
                                    type       : 'POST',
                                    url        : BooklyL10n.ajaxurl,
                                    xhrFields  : {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    data       : {action: 'bookly_check_cart', csrf_token : BooklyL10n.csrf_token, form_id: params.form_id},
                                    dataType   : 'json',
                                    success    : function (response) {
                                        if (response.success) {
                                            $form.submit();
                                        } else if (response.error == 'cart_item_not_available') {
                                            handleErrorCartItemNotAvailable(response,params.form_id);
                                        }
                                    }
                                });
                            }
                        }
                    });

                    $('.bookly-js-back-step', $container).on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        stepDetails({form_id: params.form_id});
                    });
                }
            }
        });
    }

    /**
     * Save appointment.
     */
    function save(form_id) {
        $.ajax({
            type        : 'POST',
            url         : BooklyL10n.ajaxurl,
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            data        : { action : 'bookly_save_appointment', csrf_token : BooklyL10n.csrf_token, form_id : form_id },
            dataType    : 'json'
        }).done(function(response) {
            if (response.success) {
                stepComplete({form_id: form_id});
            } else if (response.error == 'cart_item_not_available') {
                handleErrorCartItemNotAvailable(response, form_id);
            }
        });
    }

    /**
     * Handle error with code 3 which means one of the cart item is not available anymore.
     *
     * @param response
     * @param form_id
     */
    function handleErrorCartItemNotAvailable(response, form_id) {
        if (!opt[form_id].skip_steps.cart) {
            stepCart({form_id: form_id}, {
                failed_key : response.failed_cart_key,
                message    : opt[form_id].errors[response.error]
            });
        } else {
            stepTime({form_id: form_id}, opt[form_id].errors[response.error]);
        }
    }

    /**
     * Details step.
     */
    function stepDetails(params) {
        var data = $.extend({
                action    : 'bookly_render_details',
                csrf_token: BooklyL10n.csrf_token,
            }, params),
            $container = opt[params.form_id].$container;
        $.ajax({
            url         : BooklyL10n.ajaxurl,
            data        : data,
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success) {
                    $container.html(response.html);
                    scrollTo($container);

                    var intlTelInput          = response.intlTelInput,
                        update_details_dialog = response.update_details_dialog,
                        woocommerce           = response.woocommerce;

                    if (opt[params.form_id].hasOwnProperty('google_maps') && opt[params.form_id].google_maps.enabled) {
                        booklyInitGooglePlacesAutocomplete($container);
                    }

                    $(document.body).trigger('bookly.render.step_detail', [$container]);
                    // Init.
                    var phone_number                = '',
                        $guest_info                 = $('.bookly-js-guest',                 $container),
                        $phone_field                = $('.bookly-js-user-phone-input',      $container),
                        $email_field                = $('.bookly-js-user-email',            $container),
                        $email_confirm_field        = $('.bookly-js-user-email-confirm',    $container),
                        $birthday_day_field         = $('.bookly-js-select-birthday-day',   $container),
                        $birthday_month_field       = $('.bookly-js-select-birthday-month', $container),
                        $birthday_year_field        = $('.bookly-js-select-birthday-year',  $container),

                        $address_country_field      = $('.bookly-js-address-country',       $container),
                        $address_state_field        = $('.bookly-js-address-state',         $container),
                        $address_postcode_field     = $('.bookly-js-address-postcode',      $container),
                        $address_city_field         = $('.bookly-js-address-city',          $container),
                        $address_street_field       = $('.bookly-js-address-street',        $container),
                        $address_street_number_field= $('.bookly-js-address-street_number',         $container),
                        $address_additional_field   = $('.bookly-js-address-additional_address',    $container),

                        $address_country_error      = $('.bookly-js-address-country-error',             $container),
                        $address_state_error        = $('.bookly-js-address-state-error',               $container),
                        $address_postcode_error     = $('.bookly-js-address-postcode-error',            $container),
                        $address_city_error         = $('.bookly-js-address-city-error',                $container),
                        $address_street_error       = $('.bookly-js-address-street-error',              $container),
                        $address_street_number_error= $('.bookly-js-address-street_number-error',       $container),
                        $address_additional_error   = $('.bookly-js-address-additional_address-error',  $container),

                        $birthday_day_error         = $('.bookly-js-select-birthday-day-error',   $container),
                        $birthday_month_error       = $('.bookly-js-select-birthday-month-error', $container),
                        $birthday_year_error        = $('.bookly-js-select-birthday-year-error',  $container),
                        $full_name_field            = $('.bookly-js-full-name',                   $container),
                        $first_name_field           = $('.bookly-js-first-name',                  $container),
                        $last_name_field            = $('.bookly-js-last-name',                   $container),
                        $notes_field                = $('.bookly-js-user-notes',                  $container),
                        $custom_field               = $('.bookly-custom-field',                   $container),
                        $info_field                 = $('.bookly-js-info-field',                  $container),
                        $phone_error                = $('.bookly-js-user-phone-error',            $container),
                        $email_error                = $('.bookly-js-user-email-error',            $container),
                        $email_confirm_error        = $('.bookly-js-user-email-confirm-error',   $container),
                        $name_error                 = $('.bookly-js-full-name-error',             $container),
                        $first_name_error           = $('.bookly-js-first-name-error',            $container),
                        $last_name_error            = $('.bookly-js-last-name-error',             $container),
                        $captcha                    = $('.bookly-js-captcha-img',                 $container),
                        $custom_error               = $('.bookly-custom-field-error',             $container),
                        $info_error                 = $('.bookly-js-info-field-error',            $container),
                        $modals                     = $('.bookly-js-modal',                       $container),
                        $login_modal                = $('.bookly-js-login',                       $container),
                        $cst_modal                  = $('.bookly-js-cst-duplicate',               $container),
                        $next_btn                   = $('.bookly-js-next-step',                   $container),

                        $errors                     = $([
                            $birthday_day_error,
                            $birthday_month_error,
                            $birthday_year_error,
                            $address_country_error,
                            $address_state_error,
                            $address_postcode_error,
                            $address_city_error,
                            $address_street_error,
                            $address_street_number_error,
                            $address_additional_error,
                            $name_error,
                            $first_name_error,
                            $last_name_error,
                            $phone_error,
                            $email_error,
                            $email_confirm_error,
                            $custom_error,
                            $info_error
                        ]).map($.fn.toArray),

                        $fields                     = $([
                            $birthday_day_field,
                            $birthday_month_field,
                            $birthday_year_field,
                            $address_city_field,
                            $address_country_field,
                            $address_postcode_field,
                            $address_state_field,
                            $address_street_field,
                            $address_street_number_field,
                            $address_additional_field,
                            $full_name_field,
                            $first_name_field,
                            $last_name_field,
                            $phone_field,
                            $email_field,
                            $email_confirm_field,
                            $custom_field,
                            $info_field
                        ]).map($.fn.toArray)
                    ;

                    // Populate form after login.
                    var populateForm = function(response) {
                        $full_name_field.val(response.data.full_name).removeClass('bookly-error');
                        $first_name_field.val(response.data.first_name).removeClass('bookly-error');
                        $last_name_field.val(response.data.last_name).removeClass('bookly-error');

                        if (response.data.birthday) {

                            var dateParts = response.data.birthday.split('-'),
                                year  = parseInt(dateParts[0]),
                                month = parseInt(dateParts[1]),
                                day   = parseInt(dateParts[2]);

                            $birthday_day_field.val(day).removeClass('bookly-error');
                            $birthday_month_field.val(month).removeClass('bookly-error');
                            $birthday_year_field.val(year).removeClass('bookly-error');
                        }

                        if (response.data.phone) {
                            $phone_field.removeClass('bookly-error');
                            if (intlTelInput.enabled) {
                                $phone_field.intlTelInput('setNumber', response.data.phone);
                            } else {
                                $phone_field.val(response.data.phone);
                            }
                        }

                        if (response.data.country) {
                            $address_country_field.val(response.data.country).removeClass('bookly-error');
                        }
                        if (response.data.state) {
                            $address_state_field.val(response.data.state).removeClass('bookly-error');
                        }
                        if (response.data.postcode) {
                            $address_postcode_field.val(response.data.postcode).removeClass('bookly-error');
                        }
                        if (response.data.city) {
                            $address_city_field.val(response.data.city).removeClass('bookly-error');
                        }
                        if (response.data.street) {
                            $address_street_field.val(response.data.street).removeClass('bookly-error');
                        }
                        if (response.data.street_number) {
                            $address_street_number_field.val(response.data.street_number).removeClass('bookly-error');
                        }
                        if (response.data.additional_address) {
                            $address_additional_field.val(response.data.additional_address).removeClass('bookly-error');
                        }

                        $email_field.val(response.data.email).removeClass('bookly-error');
                        if (response.data.info_fields) {
                            response.data.info_fields.forEach(function (field) {
                                var $info_field = $container.find('.bookly-js-info-field-row[data-id="' + field.id + '"]');
                                switch ($info_field.data('type')) {
                                    case 'checkboxes':
                                        field.value.forEach(function (value) {
                                            $info_field.find('.bookly-js-info-field').filter(function () {
                                                return this.value == value;
                                            }).prop('checked', true);
                                        });
                                        break;
                                    case 'radio-buttons':
                                        $info_field.find('.bookly-js-info-field').filter(function () {
                                            return this.value == field.value;
                                        }).prop('checked', true);
                                        break;
                                    default:
                                        $info_field.find('.bookly-js-info-field').val(field.value);
                                        break;
                                }
                            });
                        }
                        $errors.filter(':not(.bookly-custom-field-error)').html('');
                    };

                    if (intlTelInput.enabled) {
                        $phone_field.intlTelInput({
                            preferredCountries: [intlTelInput.country],
                            initialCountry: intlTelInput.country,
                            geoIpLookup: function (callback) {
                                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                                    var countryCode = (resp && resp.country) ? resp.country : '';
                                    callback(countryCode);
                                });
                            },
                            utilsScript: intlTelInput.utils
                        });
                    }
                    // Init modals.
                    $('body > .bookly-js-modal.' + params.form_id).remove();
                    $modals
                        .addClass(params.form_id).appendTo('body')
                        .on('click', '.bookly-js-close', function (e) {
                            e.preventDefault();
                            $(e.delegateTarget).removeClass('bookly-in')
                                .find('form').trigger('reset').end()
                                .find('input').removeClass('bookly-error').end()
                                .find('.bookly-label-error').html('')
                            ;
                        })
                    ;
                    // Login modal.
                    $('.bookly-js-login-show', $container).on('click', function(e) {
                        e.preventDefault();
                        $login_modal.addClass('bookly-in');
                    });
                    $('button:submit', $login_modal).on('click', function (e) {
                        e.preventDefault();
                        var ladda = Ladda.create(this);
                        ladda.start();
                        $.ajax({
                            type        : 'POST',
                            url         : BooklyL10n.ajaxurl,
                            data        : {
                                action     : 'bookly_wp_user_login',
                                csrf_token : BooklyL10n.csrf_token,
                                form_id    : params.form_id,
                                log        : $login_modal.find('[name="log"]').val(),
                                pwd        : $login_modal.find('[name="pwd"]').val(),
                                rememberme : $login_modal.find('[name="rememberme"]').prop('checked') ? 1 : 0
                            },
                            dataType    : 'json',
                            xhrFields   : { withCredentials: true },
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success: function (response) {
                                if (response.success) {
                                    BooklyL10n.csrf_token = response.data.csrf_token;
                                    $guest_info.fadeOut('slow');
                                    populateForm(response);
                                    $login_modal.removeClass('bookly-in');
                                } else if (response.error == 'incorrect_username_password') {
                                    $login_modal.find('input').addClass('bookly-error');
                                    $login_modal.find('.bookly-label-error').html(opt[params.form_id].errors[response.error]);
                                }
                                ladda.stop();
                            }
                        });
                    });
                    // Customer duplicate modal.
                    $('button:submit', $cst_modal).on('click', function (e) {
                        e.preventDefault();
                        $cst_modal.removeClass('bookly-in');
                        $next_btn.trigger('click', [1]);
                    });
                    // Facebook login button.
                    if (opt[params.form_id].hasOwnProperty('facebook') && opt[params.form_id].facebook.enabled && typeof FB !== 'undefined') {
                        FB.XFBML.parse($('.bookly-js-fb-login-button', $container).parent().get(0));
                        opt[params.form_id].facebook.onStatusChange = function (response) {
                            if (response.status === 'connected') {
                                opt[params.form_id].facebook.enabled = false;
                                opt[params.form_id].facebook.onStatusChange = undefined;
                                $guest_info.fadeOut('slow', function () {
                                    // Hide buttons in all Bookly forms on the page.
                                    $('.bookly-js-fb-login-button').hide();
                                });
                                FB.api('/me', {fields: 'id,name,first_name,last_name,email'}, function (userInfo) {
                                    $.ajax({
                                        type: 'POST',
                                        url: BooklyL10n.ajaxurl,
                                        data: $.extend(userInfo, {
                                            action: 'bookly_pro_facebook_login',
                                            csrf_token: BooklyL10n.csrf_token,
                                            form_id: params.form_id
                                        }),
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            if (response.success) {
                                                populateForm(response);
                                            }
                                        }
                                    });
                                });
                            }
                        };
                    }

                    $next_btn.on('click', function(e, force_update_customer) {
                        e.preventDefault();
                        var info_fields = [],
                            custom_fields = {},
                            checkbox_values,
                            captcha_ids = [],
                            ladda = laddaStart(this)
                        ;
                        // Customer information fields.
                        $('div.bookly-js-info-field-row', $container).each(function() {
                            var $this = $(this);
                            switch ($this.data('type')) {
                                case 'text-field':
                                    info_fields.push({
                                        id     : $this.data('id'),
                                        value  : $this.find('input.bookly-js-info-field').val()
                                    });
                                    break;
                                case 'textarea':
                                    info_fields.push({
                                        id     : $this.data('id'),
                                        value  : $this.find('textarea.bookly-js-info-field').val()
                                    });
                                    break;
                                case 'checkboxes':
                                    checkbox_values = [];
                                    $this.find('input.bookly-js-info-field:checked').each(function () {
                                        checkbox_values.push(this.value);
                                    });
                                    info_fields.push({
                                        id     : $this.data('id'),
                                        value  : checkbox_values
                                    });
                                    break;
                                case 'radio-buttons':
                                    info_fields.push({
                                        id     : $this.data('id'),
                                        value  : $this.find('input.bookly-js-info-field:checked').val() || null
                                    });
                                    break;
                                case 'drop-down':
                                    info_fields.push({
                                        id     : $this.data('id'),
                                        value  : $this.find('select.bookly-js-info-field').val()
                                    });
                                    break;
                            }
                        });
                        // Custom fields.
                        $('.bookly-custom-fields-container', $container).each(function () {
                            var $cf_container = $(this),
                                key = $cf_container.data('key'),
                                custom_fields_data = [];
                            $('div.bookly-custom-field-row', $cf_container).each(function() {
                                var $this = $(this);
                                switch ($this.data('type')) {
                                    case 'text-field':
                                    case 'file':
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : $this.find('input.bookly-custom-field').val()
                                        });
                                        break;
                                    case 'textarea':
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : $this.find('textarea.bookly-custom-field').val()
                                        });
                                        break;
                                    case 'checkboxes':
                                        checkbox_values = [];
                                        $this.find('input.bookly-custom-field:checked').each(function () {
                                            checkbox_values.push(this.value);
                                        });
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : checkbox_values
                                        });
                                        break;
                                    case 'radio-buttons':
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : $this.find('input.bookly-custom-field:checked').val() || null
                                        });
                                        break;
                                    case 'drop-down':
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : $this.find('select.bookly-custom-field').val()
                                        });
                                        break;
                                    case 'captcha':
                                        custom_fields_data.push({
                                            id     : $this.data('id'),
                                            value  : $this.find('input.bookly-custom-field').val()
                                        });
                                        captcha_ids.push($this.data('id'));
                                        break;
                                }
                            });
                            custom_fields[key] = {custom_fields: JSON.stringify(custom_fields_data)};
                        });

                        try {
                            phone_number = intlTelInput.enabled ? $phone_field.intlTelInput('getNumber') : $phone_field.val();
                            if (phone_number == '') {
                                phone_number = $phone_field.val();
                            }
                        } catch (error) {  // In case when intlTelInput can't return phone number.
                            phone_number = $phone_field.val();
                        }
                        var data = {
                            action                : 'bookly_session_save',
                            csrf_token            : BooklyL10n.csrf_token,
                            form_id               : params.form_id,
                            full_name             : $full_name_field.val(),
                            first_name            : $first_name_field.val(),
                            last_name             : $last_name_field.val(),
                            phone                 : phone_number,
                            email                 : $email_field.val(),
                            email_confirm         : $email_confirm_field.val(),
                            birthday              : {
                                day          : $birthday_day_field.val(),
                                month        : $birthday_month_field.val(),
                                year         : $birthday_year_field.val()
                            },
                            country               : $address_country_field.val(),
                            state                 : $address_state_field.val(),
                            postcode              : $address_postcode_field.val(),
                            city                  : $address_city_field.val(),
                            street                : $address_street_field.val(),
                            street_number         : $address_street_number_field.val(),
                            additional_address    : $address_additional_field.val(),
                            address_iso: {
                                country: $address_country_field.data('short'),
                                state:   $address_state_field.data('short'),
                            },
                            info_fields           : info_fields,
                            notes                 : $notes_field.val(),
                            cart                  : custom_fields,
                            captcha_ids           : JSON.stringify(captcha_ids),
                            force_update_customer : !update_details_dialog || force_update_customer
                        };
                        $.ajax({
                            type        : 'POST',
                            url         : BooklyL10n.ajaxurl,
                            data        : data,
                            dataType    : 'json',
                            xhrFields   : { withCredentials: true },
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success     : function (response) {
                                // Error messages
                                $errors.empty();
                                $fields.removeClass('bookly-error');

                                if (response.success) {
                                    if (woocommerce.enabled) {
                                        var data = {
                                            action     : 'bookly_pro_add_to_woocommerce_cart',
                                            csrf_token : BooklyL10n.csrf_token,
                                            form_id    : params.form_id
                                        };
                                        $.ajax({
                                            type        : 'POST',
                                            url         : BooklyL10n.ajaxurl,
                                            data        : data,
                                            dataType    : 'json',
                                            xhrFields   : { withCredentials: true },
                                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                            success     : function (response) {
                                                if (response.success) {
                                                    window.location.href = woocommerce.cart_url;
                                                } else {
                                                    ladda.stop();
                                                    stepTime({form_id: params.form_id}, opt[params.form_id].errors[response.error]);
                                                }
                                            }
                                        });
                                    } else {
                                        stepPayment({form_id: params.form_id});
                                    }
                                } else {
                                    var $scroll_to = null;
                                    if (response.appointments_limit_reached) {
                                        stepComplete({form_id: params.form_id, error: 'appointments_limit_reached'});
                                    } else {
                                        ladda.stop();

                                        var invalidClass = 'bookly-error',
                                            validateFields = [
                                                {
                                                    name: 'full_name',
                                                    errorElement: $name_error,
                                                    formElement: $full_name_field
                                                },
                                                {
                                                    name: 'first_name',
                                                    errorElement: $first_name_error,
                                                    formElement: $first_name_field
                                                },
                                                {
                                                    name: 'last_name',
                                                    errorElement: $last_name_error,
                                                    formElement: $last_name_field
                                                },
                                                {
                                                    name: 'phone',
                                                    errorElement: $phone_error,
                                                    formElement: $phone_field
                                                },
                                                {
                                                    name: 'email',
                                                    errorElement: $email_error,
                                                    formElement: $email_field
                                                },
                                                {
                                                    name: 'email_confirm',
                                                    errorElement: $email_confirm_error,
                                                    formElement: $email_confirm_field
                                                },
                                                {
                                                    name: 'birthday_day',
                                                    errorElement: $birthday_day_error,
                                                    formElement: $birthday_day_field
                                                },
                                                {
                                                    name: 'birthday_month',
                                                    errorElement: $birthday_month_error,
                                                    formElement: $birthday_month_field
                                                },
                                                {
                                                    name: 'birthday_year',
                                                    errorElement: $birthday_year_error,
                                                    formElement: $birthday_year_field
                                                },
                                                {
                                                    name: 'country',
                                                    errorElement: $address_country_error,
                                                    formElement: $address_country_field
                                                },
                                                {
                                                    name: 'state',
                                                    errorElement: $address_state_error,
                                                    formElement: $address_state_field
                                                },
                                                {
                                                    name: 'postcode',
                                                    errorElement: $address_postcode_error,
                                                    formElement: $address_postcode_field
                                                },
                                                {
                                                    name: 'city',
                                                    errorElement: $address_city_error,
                                                    formElement: $address_city_field
                                                },
                                                {
                                                    name: 'street',
                                                    errorElement: $address_street_error,
                                                    formElement: $address_street_field
                                                },
                                                {
                                                    name: 'street_number',
                                                    errorElement: $address_street_number_error,
                                                    formElement: $address_street_number_field
                                                },
                                                {
                                                    name: 'additional_address',
                                                    errorElement: $address_additional_error,
                                                    formElement: $address_additional_field
                                                }
                                            ];

                                        validateFields.forEach(function(field) {
                                            if (!response[field.name]) {
                                                return;
                                            }

                                            field.errorElement.html(response[field.name]);
                                            field.formElement.addClass(invalidClass);

                                            if ($scroll_to === null) {
                                                $scroll_to = field.formElement;
                                            }
                                        });

                                        if (response.info_fields) {
                                            $.each(response.info_fields, function (field_id, message) {
                                                var $div = $('div.bookly-js-info-field-row[data-id="' + field_id + '"]', $container);
                                                $div.find('.bookly-js-info-field-error').html(message);
                                                $div.find('.bookly-js-info-field').addClass('bookly-error');
                                                if ($scroll_to === null) {
                                                    $scroll_to = $div.find('.bookly-js-info-field');
                                                }
                                            });
                                        }
                                        if (response.custom_fields) {
                                            $.each(response.custom_fields, function (key, fields) {
                                                $.each(fields, function (field_id, message) {
                                                    var $custom_fields_collector = $('.bookly-custom-fields-container[data-key="' + key + '"]', $container);
                                                    var $div = $('[data-id="' + field_id + '"]', $custom_fields_collector);
                                                    $div.find('.bookly-custom-field-error').html(message);
                                                    $div.find('.bookly-custom-field').addClass('bookly-error');
                                                    if ($scroll_to === null) {
                                                        $scroll_to = $div.find('.bookly-custom-field');
                                                    }
                                                });
                                            });
                                        }
                                        if (response.customer) {
                                            $cst_modal
                                                .find('.bookly-js-modal-body').html(response.customer).end()
                                                .addClass('bookly-in')
                                            ;
                                        }
                                    }
                                    if ($scroll_to !== null) {
                                        scrollTo($scroll_to);
                                    }
                                }
                            }
                        });
                    });

                    $('.bookly-js-back-step', $container).on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        if (!opt[params.form_id].skip_steps.cart) {
                            stepCart({form_id: params.form_id});
                        } else if (opt[params.form_id].no_time) {
                            if (opt[params.form_id].no_extras) {
                                stepService({form_id: params.form_id});
                            } else {
                                stepExtras({form_id: params.form_id});
                            }
                        } else if (!opt[params.form_id].skip_steps.repeat) {
                            stepRepeat({form_id: params.form_id});
                        } else if (!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_extras) {
                            stepExtras({form_id: params.form_id});
                        } else {
                            stepTime({form_id: params.form_id});
                        }
                    });

                    $('.bookly-js-captcha-refresh',  $container).on('click', function() {
                        $captcha.css('opacity','0.5');
                        $.ajax({
                            type        : 'POST',
                            url         : BooklyL10n.ajaxurl,
                            data        : {action: 'bookly_custom_fields_captcha_refresh', form_id: params.form_id, csrf_token : BooklyL10n.csrf_token},
                            dataType    : 'json',
                            xhrFields   : {withCredentials: true},
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success     : function (response) {
                                if (response.success) {
                                    $captcha.attr('src', response.data.captcha_url).on('load', function() {
                                        $captcha.css('opacity', '1');
                                    });
                                }
                            }
                        });
                    });
                }
            }
        });

        /**
         * global function to init google places
         */
        function booklyInitGooglePlacesAutocomplete(bookly_forms)
        {
            var bookly_forms = bookly_forms || $('.bookly-form .bookly-details-step');

            bookly_forms.each(function() {
                initGooglePlacesAutocomplete($(this));
            });
        }

        /**
         * Addon: Google Maps Address
         * @param {jQuery} [$container]
         * @returns {boolean}
         */
        function initGooglePlacesAutocomplete($container)
        {
            var autocompleteInput = $container.find('.bookly-js-cst-address-autocomplete');

            if (!autocompleteInput.length) {
                return false;
            }

            var autocomplete = new google.maps.places.Autocomplete(
                autocompleteInput[0], {
                    types: ['geocode']
                }
                ),
                autocompleteFields = [
                    {
                        selector: '.bookly-js-address-country',
                        val: function() {
                            return getFieldValueByType('country');
                        },
                        short: function() {
                            return getFieldValueByType('country',true);
                        }
                    },
                    {
                        selector: '.bookly-js-address-postcode',
                        val: function() {
                            return getFieldValueByType('postal_code');
                        }
                    },
                    {
                        selector: '.bookly-js-address-city',
                        val: function() {
                            return getFieldValueByType('locality') || getFieldValueByType('administrative_area_level_3');
                        }
                    },
                    {
                        selector: '.bookly-js-address-state',
                        val: function() {
                            return getFieldValueByType('administrative_area_level_1');
                        },
                        short: function() {
                            return getFieldValueByType('administrative_area_level_1',true);
                        }
                    },
                    {
                        selector: '.bookly-js-address-street',
                        val: function() {
                            return getFieldValueByType('route');
                        }
                    },
                    {
                        selector: '.bookly-js-address-street_number',
                        val: function() {
                            return getFieldValueByType('street_number');
                        }
                    }
                ];

            var getFieldValueByType = function(type, useShortName)
            {
                var addressComponents = autocomplete.getPlace().address_components;

                for (var i = 0; i < addressComponents.length; i++) {
                    var addressType = addressComponents[i].types[0];

                    if (addressType === type) {
                        return useShortName ? addressComponents[i]['short_name'] : addressComponents[i]['long_name'];
                    }
                }

                return '';
            };

            autocomplete.addListener('place_changed', function() {
                autocompleteFields.forEach(function(field) {
                    var element = $container.find(field.selector);

                    if (element.length === 0) {
                        return;
                    }
                    element.val(field.val());
                    if (typeof field.short == 'function') {
                        element.data('short', field.short());
                    }
                });
            });
        }
    }

    /**
     * Cart step.
     */
    function stepCart(params, error) {
        if (opt[params.form_id].skip_steps.cart) {
            stepDetails(params);
        } else {
            if (params && params.from_step) {
                // Record previous step if it was given in params.
                opt[params.form_id].cart_prev_step = params.from_step;
            }
            var data = $.extend({
                    action: 'bookly_render_cart',
                    csrf_token: BooklyL10n.csrf_token,
                }, params),
                $container = opt[params.form_id].$container;
            $.ajax({
                url: BooklyL10n.ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        if (error){
                            $('.bookly-label-error', $container).html(error.message);
                            $('tr[data-cart-key="'+ error.failed_key +'"]', $container).addClass('bookly-label-error');
                        } else {
                            $('.bookly-label-error', $container).hide();
                        }
                        scrollTo($container);
                        $('.bookly-js-next-step', $container).on('click', function () {
                            laddaStart(this);
                            stepDetails({form_id: params.form_id});
                        });
                        $('.bookly-add-item', $container).on('click', function () {
                            laddaStart(this);
                            stepService({form_id: params.form_id, new_chain : true});
                        });
                        // 'BACK' button.
                        $('.bookly-js-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            laddaStart(this);
                            switch (opt[params.form_id].cart_prev_step) {
                                case 'service': stepService({form_id: params.form_id}); break;
                                case 'extras':  stepExtras({form_id: params.form_id});  break;
                                case 'time':    stepTime({form_id: params.form_id});    break;
                                case 'repeat':  stepRepeat({form_id: params.form_id});  break;
                                default:        stepService({form_id: params.form_id});
                            }
                        });
                        $('.bookly-js-actions button', $container).on('click', function () {
                            laddaStart(this);
                            var $this = $(this),
                                $cart_item = $this.closest('tr');
                            switch ($this.data('action')) {
                                case 'drop':
                                    $.ajax({
                                        url: BooklyL10n.ajaxurl,
                                        data: {
                                            action     : 'bookly_cart_drop_item',
                                            csrf_token : BooklyL10n.csrf_token,
                                            form_id    : params.form_id,
                                            cart_key   : $cart_item.data('cart-key')
                                        },
                                        dataType: 'json',
                                        xhrFields: {withCredentials: true},
                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                        success: function (response) {
                                            if (response.success) {
                                                var remove_cart_key = $cart_item.data('cart-key'),
                                                    $trs_to_remove  = $('tr[data-cart-key="'+remove_cart_key+'"]', $container)
                                                ;
                                                $cart_item.delay(300).fadeOut(200, function () {
                                                    if (response.data.total_waiting_list) {
                                                        $('.bookly-js-waiting-list-price', $container).html(response.data.waiting_list_price);
                                                        $('.bookly-js-waiting-list-deposit', $container).html(response.data.waiting_list_deposit);
                                                    } else {
                                                        $('.bookly-js-waiting-list-price', $container).closest('tr').remove();
                                                    }
                                                    $('.bookly-js-subtotal-price', $container).html(response.data.subtotal_price);
                                                    $('.bookly-js-subtotal-deposit', $container).html(response.data.subtotal_deposit);
                                                    $('.bookly-js-pay-now-deposit', $container).html(response.data.pay_now_deposit);
                                                    $('.bookly-js-pay-now-tax', $container).html(response.data.pay_now_tax);
                                                    $('.bookly-js-total-price', $container).html(response.data.total_price);
                                                    $('.bookly-js-total-tax', $container).html(response.data.total_tax);
                                                    $trs_to_remove.remove();
                                                    if ($('tr[data-cart-key]').length == 0) {
                                                        $('.bookly-js-back-step', $container).hide();
                                                        $('.bookly-js-next-step', $container).hide();
                                                    }
                                                });
                                            }
                                        }
                                    });
                                    break;
                                case 'edit':
                                    stepService({form_id: params.form_id, edit_cart_item : $cart_item.data('cart-key')});
                                    break;
                            }
                        });
                    }
                }
            });
        }
    }

    /**
     * Repeat step.
     */
    function stepRepeat(params, error) {
        if (opt[params.form_id].skip_steps.repeat) {
            stepCart(params, error);
        } else {
            var data = $.extend({
                    action    : 'bookly_render_repeat',
                    csrf_token: BooklyL10n.csrf_token,
                }, params),
                $container = opt[params.form_id].$container;
            $.ajax({
                url         : BooklyL10n.ajaxurl,
                data        : data,
                dataType    : 'json',
                xhrFields   : { withCredentials: true },
                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                success     : function (response) {
                    if (response.success) {
                        $container.html(response.html);
                        scrollTo($container);

                        var $repeat_enabled   = $('.bookly-js-repeat-appointment-enabled', $container),
                            $next_step        = $('.bookly-js-next-step', $container),
                            $repeat_container = $('.bookly-js-repeat-variants-container', $container),
                            $variants         = $('[class^="bookly-js-variant"]', $repeat_container),
                            $repeat_variant   = $('.bookly-js-repeat-variant', $repeat_container),
                            $button_get_schedule = $('.bookly-js-get-schedule', $repeat_container),
                            $variant_weekly   = $('.bookly-js-variant-weekly', $repeat_container),
                            $variant_monthly  = $('.bookly-js-repeat-variant-monthly', $repeat_container),
                            $date_until       = $('.bookly-js-repeat-until', $repeat_container),
                            $repeat_times     = $('.bookly-js-repeat-times', $repeat_container),
                            $monthly_specific_day = $('.bookly-js-monthly-specific-day', $repeat_container),
                            $monthly_week_day = $('.bookly-js-monthly-week-day', $repeat_container),
                            $repeat_every_day = $('.bookly-js-repeat-daily-every', $repeat_container),
                            $week_day         = $('.bookly-js-week-day', $repeat_container),
                            $schedule_container = $('.bookly-js-schedule-container', $container),
                            $days_error       = $('.bookly-js-days-error', $repeat_container),
                            $schedule_slots   = $('.bookly-js-schedule-slots',$schedule_container),
                            $intersection_info = $('.bookly-js-intersection-info', $schedule_container),
                            $info_help  = $('.bookly-js-schedule-help', $schedule_container),
                            $info_wells = $('.bookly-well', $schedule_container),
                            $pagination = $('.bookly-pagination', $schedule_container),
                            $schedule_row_template = $('.bookly-schedule-row-template .bookly-schedule-row', $schedule_container),
                            pages_warning_info = response.pages_warning_info,
                            short_date_format = response.short_date_format,
                            bound_date = {min: response.date_min || true, max: response.date_max || true},
                            schedule = []
                        ;
                        var repeat = {
                            prepareButtonNextState : function () {
                                // Disable/Enable next button
                                var is_disabled = $next_step.prop('disabled'),
                                    new_prop_disabled = schedule.length == 0;
                                for (var i = 0; i < schedule.length; i++) {
                                    if (is_disabled) {
                                        if (!schedule[i].deleted) {
                                            new_prop_disabled = false;
                                            break;
                                        }
                                    } else if (schedule[i].deleted) {
                                        new_prop_disabled = true;
                                    } else {
                                        new_prop_disabled = false;
                                        break;
                                    }
                                }
                                $next_step.prop('disabled', new_prop_disabled);
                            },
                            addTimeSlotControl : function ($schedule_row, options, preferred_time, selected_time) {
                                var $time = '';
                                if(options.length) {
                                    var prefer;
                                    $time = $('<select/>');
                                    $.each(options, function (index, option) {
                                        var $option = $('<option/>');
                                        $option.text(option.title).val(option.value);
                                        if (option.disabled) {
                                            $option.attr('disabled', 'disabled');
                                        }
                                        $time.append($option);
                                        if (!prefer && !option.disabled) {
                                            if (option.title == preferred_time) {
                                                // Select by time title.
                                                $time.val(option.value);
                                                prefer = true;
                                            } else if (option.title == selected_time) {
                                                $time.val(option.value);
                                            }
                                        }
                                    });
                                }
                                $schedule_row.find('.bookly-js-schedule-time').html($time);
                                $schedule_row.find('div.bookly-label-error').toggle(!options.length);
                            },
                            renderSchedulePage : function (page) {
                                var $row,
                                    count = schedule.length,
                                    rows_on_page = 5,
                                    start = rows_on_page * page - rows_on_page,
                                    warning_pages = [];
                                $schedule_slots.html('');
                                for (var i = start, j = 0; j < rows_on_page && i < count; i++, j++) {
                                    $row = $schedule_row_template.clone();
                                    $row.data('datetime', schedule[i].datetime);
                                    $row.data('index', schedule[i].index);
                                    $('> div:first-child', $row).html(schedule[i].index);
                                    $('.bookly-schedule-date', $row).html(schedule[i].display_date);
                                    if (schedule[i].all_day_service_time !== undefined) {
                                        $('.bookly-js-schedule-time', $row).hide();
                                        $('.bookly-js-schedule-all-day-time', $row).html(schedule[i].all_day_service_time).show();
                                    } else {
                                        $('.bookly-js-schedule-time', $row).html(schedule[i].display_time).show();
                                        $('.bookly-js-schedule-all-day-time', $row).hide();
                                    }
                                    if (schedule[i].another_time) {
                                        $('.bookly-schedule-intersect', $row).show();
                                    }
                                    if (schedule[i].deleted) {
                                        $row.find('.bookly-schedule-appointment').addClass('bookly-appointment-hidden');
                                    }
                                    $schedule_slots.append($row);
                                }
                                if (count > rows_on_page) {
                                    var $btn = $('<li/>').html('«');
                                    $btn.on('click', function () {
                                        var page = parseInt($pagination.find('.active').html());
                                        if (page > 1) {
                                            repeat.renderSchedulePage(page - 1);
                                        }
                                    });
                                    $pagination.html($btn);
                                    for (i = 0, j = 1; i < count; i += 5, j++) {
                                        $btn = $('<li/>').html(j);
                                        $pagination.append($btn);
                                        $btn.on('click', function () {
                                            repeat.renderSchedulePage($(this).html());
                                        });
                                    }
                                    $pagination.find('li:eq(' + page + ')').addClass('active');
                                    $btn = $('<li/>').html('»');
                                    $btn.on('click', function () {
                                        var page = parseInt($pagination.find('.active').html());
                                        if (page < count / rows_on_page) {
                                            repeat.renderSchedulePage(page + 1);
                                        }
                                    });
                                    $pagination.append($btn).show();

                                    for (i = 0; i < count; i++) {
                                        if (schedule[i].another_time) {
                                            page = parseInt(i / rows_on_page) + 1;
                                            warning_pages.push(page);
                                            i = page * rows_on_page - 1;
                                        }
                                    }
                                    if (warning_pages.length > 0) {
                                        $intersection_info.html(pages_warning_info.replace('{list}', warning_pages.join(', ')));
                                    }
                                    $info_wells.toggle(warning_pages.length > 0);
                                    $pagination.toggle(count > rows_on_page);
                                } else {
                                    $pagination.hide();
                                    $info_wells.hide();
                                    for (i = 0; i < count; i++) {
                                        if (schedule[i].another_time) {
                                            $info_help.show();
                                            break;
                                        }
                                    }
                                }
                            },
                            renderFullSchedule: function (data) {
                                schedule = data; // it has global scope
                                // Prefer time is display time selected on step time.
                                var preferred_time = null;
                                $.each(schedule, function (index, item) {
                                    if (!preferred_time && !item.another_time) {
                                        preferred_time = item.display_time;
                                    }
                                });
                                repeat.renderSchedulePage(1);
                                $schedule_container.show();

                                $next_step.prop('disabled', schedule.length == 0);
                                $schedule_slots.on('click', 'button[data-action]', function () {
                                    var $schedule_row = $(this).closest('.bookly-schedule-row');
                                    var row_index = $schedule_row.data('index') - 1;
                                    switch ($(this).data('action')) {
                                        case 'drop':
                                            schedule[row_index].deleted = true;
                                            $schedule_row.find('.bookly-schedule-appointment').addClass('bookly-appointment-hidden');
                                            repeat.prepareButtonNextState();
                                            break;
                                        case 'restore':
                                            schedule[row_index].deleted = false;
                                            $schedule_row.find('.bookly-schedule-appointment').removeClass('bookly-appointment-hidden');
                                            $next_step.prop('disabled', false);
                                            break;
                                        case 'edit':
                                            var $date = $('<input type="text"/>'),
                                                $edit_button = $(this),
                                                ladda_round = laddaStart(this);
                                            $schedule_row.find('.bookly-schedule-date').html($date);
                                            $date.pickadate({
                                                min             : bound_date.min,
                                                max             : bound_date.max,
                                                formatSubmit    : 'yyyy-mm-dd',
                                                format          : short_date_format,
                                                clear           : false,
                                                close           : false,
                                                today           : BooklyL10n.today,
                                                monthsFull      : BooklyL10n.months,
                                                weekdaysFull    : BooklyL10n.days,
                                                weekdaysShort   : BooklyL10n.daysShort,
                                                labelMonthNext  : BooklyL10n.nextMonth,
                                                labelMonthPrev  : BooklyL10n.prevMonth,
                                                firstDay        : opt[params.form_id].firstDay,
                                                onSet: function() {
                                                    var exclude = [];
                                                    $.each(schedule, function (index, item) {
                                                        if ((row_index != index) && !item.deleted) {
                                                            exclude.push(item.slots);
                                                        }
                                                    });
                                                    $.ajax({
                                                        url : BooklyL10n.ajaxurl,
                                                        type: 'POST',
                                                        data: {
                                                            action     : 'bookly_recurring_appointments_get_daily_customer_schedule',
                                                            csrf_token : BooklyL10n.csrf_token,
                                                            date       : this.get('select', 'yyyy-mm-dd'),
                                                            form_id    : params.form_id,
                                                            exclude    : exclude
                                                        },
                                                        dataType: 'json',
                                                        xhrFields: {withCredentials: true},
                                                        crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                                        success: function (response) {
                                                            $edit_button.hide();
                                                            ladda_round.stop();
                                                            if (response.data.length) {
                                                                repeat.addTimeSlotControl($schedule_row, response.data[0].options, preferred_time, schedule[row_index].display_time, response.data[0].all_day_service_time);
                                                                $schedule_row.find('button[data-action="save"]').show();
                                                            } else {
                                                                repeat.addTimeSlotControl($schedule_row, [] );
                                                                $schedule_row.find('button[data-action="save"]').hide();
                                                            }
                                                        }
                                                    });
                                                }
                                            });

                                            var slots = JSON.parse(schedule[row_index].slots);
                                            $date.pickadate('picker').set('select', new Date(slots[0][2]));
                                            break;
                                        case 'save':
                                            $(this).hide();
                                            $schedule_row.find('button[data-action="edit"]').show();
                                            var $date_container = $schedule_row.find('.bookly-schedule-date'),
                                                $time_container = $schedule_row.find('.bookly-js-schedule-time'),
                                                $select = $time_container.find('select'),
                                                option = $select.find('option:selected');
                                            schedule[row_index].slots = $select.val();
                                            schedule[row_index].display_date = $date_container.find('input').val();
                                            schedule[row_index].display_time = option.text();
                                            $date_container.html(schedule[row_index].display_date);
                                            $time_container.html(schedule[row_index].display_time);
                                            break;
                                    }
                                });
                            },
                            isDateMatchesSelections: function (current_date) {
                                switch ($repeat_variant.val()) {
                                    case 'daily':
                                        if (($repeat_every_day.val() > 6 || $.inArray(current_date.format('ddd').toLowerCase(), repeat.week_days) != -1) && (current_date.diff(repeat.date_from, 'days') % $repeat_every_day.val() == 0)) {
                                            return true;
                                        }
                                        break;
                                    case 'weekly':
                                    case 'biweekly':
                                        if (($repeat_variant.val() == 'weekly' || current_date.diff(repeat.date_from.clone().startOf('isoWeek'), 'weeks') % 2 == 0) && ($.inArray(current_date.format('ddd').toLowerCase(), repeat.checked_week_days) != -1)) {
                                            return true;
                                        }
                                        break;
                                    case 'monthly':
                                        switch ($variant_monthly.val()) {
                                            case 'specific':
                                                if (current_date.format('D') == $monthly_specific_day.val()) {
                                                    return true;
                                                }
                                                break;
                                            case 'last':
                                                if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && current_date.clone().endOf('month').diff(current_date, 'days') < 7) {
                                                    return true;
                                                }
                                                break;
                                            default:
                                                var month_diff = current_date.diff(current_date.clone().startOf('month'), 'days');
                                                if (current_date.format('ddd').toLowerCase() == $monthly_week_day.val() && month_diff >= ($variant_monthly.prop('selectedIndex') - 1) * 7 && month_diff < $variant_monthly.prop('selectedIndex') * 7) {
                                                    return true;
                                                }
                                        }
                                        break;
                                }

                                return false;
                            },
                            updateRepeatDate: function () {
                                var number_of_times = 0,
                                    repeat_times = $repeat_times.val(),
                                    date_from = bound_date.min.slice(),
                                    date_until = $date_until.pickadate('picker').get('select'),
                                    moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date).add(5, 'years');
                                date_from[1]++;
                                repeat.date_from = moment(date_from.join(','), 'YYYY,M,D');

                                repeat.week_days = [];
                                $monthly_week_day.find('option').each(function () {
                                    repeat.week_days.push($(this).val());
                                });

                                repeat.checked_week_days = [];
                                $week_day.each(function () {
                                    if ($(this).prop('checked')) {
                                        repeat.checked_week_days.push($(this).val());
                                    }
                                });

                                var current_date = repeat.date_from.clone();
                                do {
                                    if (repeat.isDateMatchesSelections(current_date)) {
                                        number_of_times++;
                                    }
                                    current_date.add(1, 'days');
                                } while (number_of_times < repeat_times && current_date.isBefore(moment_until));
                                $date_until.val(current_date.subtract(1, 'days').format('MMMM D, YYYY'));
                                $date_until.pickadate('picker').set('select', new Date(current_date.format('YYYY'), current_date.format('M') - 1, current_date.format('D')));
                            },
                            updateRepeatTimes: function () {
                                var number_of_times = 0,
                                    date_from = bound_date.min.slice(),
                                    date_until = $date_until.pickadate('picker').get('select'),
                                    moment_until = moment().year(date_until.year).month(date_until.month).date(date_until.date);

                                date_from[1]++;
                                repeat.date_from = moment(date_from.join(','), 'YYYY,M,D');

                                repeat.week_days = [];
                                $monthly_week_day.find('option').each(function () {
                                    repeat.week_days.push($(this).val());
                                });

                                repeat.checked_week_days = [];
                                $week_day.each(function () {
                                    if ($(this).prop('checked')) {
                                        repeat.checked_week_days.push($(this).val());
                                    }
                                });

                                var current_date = repeat.date_from.clone();
                                do {
                                    if (repeat.isDateMatchesSelections(current_date)) {
                                        number_of_times++;
                                    }
                                    current_date.add(1, 'days');
                                } while (current_date.isBefore(moment_until));
                                $repeat_times.val(number_of_times);
                            }
                        };

                        $date_until.pickadate({
                            formatSubmit    : 'yyyy-mm-dd',
                            format          : opt[params.form_id].date_format,
                            min             : bound_date.min,
                            max             : bound_date.max,
                            clear           : false,
                            close           : false,
                            today           : BooklyL10n.today,
                            monthsFull      : BooklyL10n.months,
                            weekdaysFull    : BooklyL10n.days,
                            weekdaysShort   : BooklyL10n.daysShort,
                            labelMonthNext  : BooklyL10n.nextMonth,
                            labelMonthPrev  : BooklyL10n.prevMonth,
                            firstDay        : opt[params.form_id].firstDay
                        });

                        var open_repeat_onchange = $repeat_enabled.on('change', function () {
                            $repeat_container.toggle($(this).prop('checked'));
                            if ($(this).prop('checked')) {
                                repeat.prepareButtonNextState();
                            } else {
                                $next_step.prop('disabled', false);
                            }
                        });
                        if (response.repeated) {
                            var repeat_data = response.repeat_data;
                            var repeat_params = repeat_data.params;

                            $repeat_enabled.prop('checked', true);
                            $repeat_variant.val(repeat_data.repeat);
                            var until = repeat_data.until.split('-');
                            $date_until.pickadate('set').set('select', new Date(until[0], until[1]-1, until[2]));
                            switch (repeat_data.repeat) {
                                case 'daily':
                                    $repeat_every_day.val(repeat_params.every);
                                    break;
                                case 'weekly':
                                //break skipped
                                case 'biweekly':
                                    $('.bookly-js-week-days input.bookly-js-week-day', $repeat_container)
                                        .prop('checked', false)
                                        .parent()
                                        .removeClass('active');
                                    repeat_params.on.forEach(function(val) {
                                        $('.bookly-js-week-days input.bookly-js-week-day[value='+val+']', $repeat_container)
                                            .prop('checked', true)
                                            .parent()
                                            .addClass('active');
                                    });
                                    break;
                                case 'monthly':
                                    if (repeat_params.on === 'day') {
                                        $variant_monthly.val('specific');
                                        $('.bookly-js-monthly-specific-day[value='+repeat_params.day+']', $repeat_container).prop('checked', true);
                                    } else {
                                        $variant_monthly.val(repeat_params.on);
                                        $monthly_week_day.val(repeat_params.weekday);
                                    }
                                    break;
                            }
                            repeat.renderFullSchedule(response.schedule);
                        }
                        open_repeat_onchange.trigger('change');

                        if (!response.could_be_repeated) {
                            $repeat_enabled.attr('disabled', true);
                        }

                        $repeat_variant.on('change', function () {
                            $variants.hide();
                            $repeat_container.find('.bookly-js-variant-' + this.value).show();
                            repeat.updateRepeatTimes();
                        }).trigger('change');

                        $variant_monthly.on('change', function () {
                            $monthly_week_day.toggle(this.value != 'specific');
                            $monthly_specific_day.toggle(this.value == 'specific');
                            repeat.updateRepeatTimes();
                        }).trigger('change');

                        $week_day.on('change', function () {
                            var $this = $(this);
                            if ($this.is(':checked')) {
                                $this.parent().not("[class*='active']").addClass('active');
                            } else {
                                $this.parent().removeClass('active');
                            }
                            repeat.updateRepeatTimes();
                        });

                        $monthly_specific_day.val(response.date_min[2]);

                        $monthly_specific_day.on('change', function () {
                            repeat.updateRepeatTimes();
                        });

                        $monthly_week_day.on('change', function () {
                            repeat.updateRepeatTimes();
                        });

                        $date_until.on('change', function () {
                            repeat.updateRepeatTimes();
                        });

                        $repeat_every_day.on('change', function () {
                            repeat.updateRepeatTimes();
                        });

                        $repeat_times.on('change', function () {
                            repeat.updateRepeatDate();
                        });

                        $button_get_schedule.on('click', function () {
                            $schedule_container.hide();
                            var data = {
                                    action     : 'bookly_recurring_appointments_get_customer_schedule',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : params.form_id,
                                    repeat     : $repeat_variant.val(),
                                    until      : $date_until.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                    params     : {}
                                },
                                ladda = laddaStart(this);

                            switch (data.repeat) {
                                case 'daily':
                                    data.params = {every: $repeat_every_day.val()};
                                    break;
                                case 'weekly':
                                case 'biweekly':
                                    data.params.on = [];
                                    $('.bookly-js-week-days input.bookly-js-week-day:checked', $variant_weekly).each(function () {
                                        data.params.on.push(this.value);
                                    });
                                    if (data.params.on.length == 0) {
                                        $days_error.toggle(true);
                                        ladda.stop();
                                        return false;
                                    } else {
                                        $days_error.toggle(false);
                                    }
                                    break;
                                case 'monthly':
                                    if ($variant_monthly.val() == 'specific') {
                                        data.params = {on: 'day', day: $monthly_specific_day.val()};
                                    } else {
                                        data.params = {on: $variant_monthly.val(), weekday: $monthly_week_day.val()};
                                    }
                                    break;
                            }
                            $schedule_slots.off('click');
                            $.ajax({
                                url : BooklyL10n.ajaxurl,
                                type: 'POST',
                                data: data,
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (response.success) {
                                        repeat.renderFullSchedule(response.data);
                                        ladda.stop();
                                    }
                                }
                            });
                        });

                        $('.bookly-js-back-step', $container).on('click', function (e) {
                            e.preventDefault();
                            laddaStart(this);
                            $.ajax({
                                type: 'POST',
                                url: BooklyL10n.ajaxurl,
                                data: {
                                    action: 'bookly_session_save',
                                    csrf_token: BooklyL10n.csrf_token,
                                    form_id: params.form_id,
                                    unrepeat: 1
                                },
                                dataType: 'json',
                                xhrFields: {withCredentials: true},
                                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                success: function (response) {
                                    if (!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_extras) {
                                        stepExtras({form_id: params.form_id});
                                    } else {
                                        stepTime({form_id: params.form_id});
                                    }
                                }
                            });
                        });

                        $('.bookly-js-go-to-cart', $container).on('click', function(e) {
                            e.preventDefault();
                            laddaStart(this);
                            stepCart({form_id: params.form_id, from_step : 'repeat'});
                        });

                        $('.bookly-js-next-step', $container).on('click', function (e) {
                            laddaStart(this);
                            if ($repeat_enabled.is(':checked')) {
                                var slots_to_send = [];
                                var repeat = 0;
                                schedule.forEach(function (item) {
                                    if (!item.deleted) {
                                        var slots = JSON.parse(item.slots);
                                        slots_to_send = slots_to_send.concat(slots);
                                        repeat++;
                                    }
                                });
                                $.ajax({
                                    type: 'POST',
                                    url: BooklyL10n.ajaxurl,
                                    data: {
                                        action: 'bookly_session_save',
                                        csrf_token: BooklyL10n.csrf_token,
                                        form_id: params.form_id,
                                        slots: JSON.stringify(slots_to_send),
                                        repeat: repeat
                                    },
                                    dataType: 'json',
                                    xhrFields: {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success: function (response) {
                                        stepCart({form_id: params.form_id, add_to_cart : true, from_step : 'repeat'});
                                    }
                                });
                            } else {
                                $.ajax({
                                    type: 'POST',
                                    url: BooklyL10n.ajaxurl,
                                    data: {
                                        action: 'bookly_session_save',
                                        csrf_token: BooklyL10n.csrf_token,
                                        form_id: params.form_id,
                                        unrepeat: 1
                                    },
                                    dataType: 'json',
                                    xhrFields: {withCredentials: true},
                                    crossDomain: 'withCredentials' in new XMLHttpRequest(),
                                    success: function (response) {
                                        stepCart({form_id: params.form_id, add_to_cart: true, from_step : 'repeat'});
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
    }

    /**
     * Time step.
     */
    var xhr_render_time = null;
    function stepTime(params, error_message) {
        if (opt[params.form_id].no_time || opt[params.form_id].skip_steps.time) {
            if (!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_extras) {
                stepExtras({form_id: params.form_id});
            } else if (!opt[params.form_id].skip_steps.cart) {
                stepCart({form_id: params.form_id,add_to_cart: true, from_step: (params && params.prev_step) ? params.prev_step : 'service'});
            } else {
                stepDetails({form_id: params.form_id, add_to_cart : true});
            }
            return;
        }
        var data = {
                action    : 'bookly_render_time',
                csrf_token: BooklyL10n.csrf_token,
            },
            $container = opt[params.form_id].$container;
        if (opt[params.form_id].skip_steps.service && opt[params.form_id].use_client_time_zone) {
            // If Service step is skipped then we need to send time zone offset.
            data.time_zone        = opt[params.form_id].timeZone;
            data.time_zone_offset = opt[params.form_id].timeZoneOffset;
        }
        $.extend(data, params);

        // Build slots html
        function prepareSlotsHtml(slots_data, selected_date) {
            var response = {};
            $.each(slots_data, function (group, group_slots) {

                var html = '<button class="bookly-day" value="' + group + '">' + group_slots.title + '</button>';
                $.each(group_slots.slots, function (id, slot) {
                    html += '<button value="' + JSON.stringify(slot.data).replace(/"/g, '&quot;') + '" data-group="' + group + '" class="bookly-hour' + (slot.status == 'waiting-list' ? ' bookly-slot-in-waiting-list' : (slot.status == 'booked' ? ' booked' : '')) + '"' + (slot.status == 'booked' ? ' disabled' : '') + '>' +
                        '<span class="ladda-label bookly-time-main' + (slot.data[0][2] == selected_date ? ' bookly-bold' : '') + '">' +
                        '<i class="bookly-hour-icon"><span></span></i>' + slot.time_text + '</span>' +
                        '<span class="bookly-time-additional' + (slot.status == 'waiting-list' ? ' bookly-waiting-list' : '') + '"> ' + slot.additional_text + '</span>' +
                        '</button>';
                });
                response[group] = html;
            });

            return response;
        }

        function dropAjax() {
            if (xhr_render_time != null) {
                xhr_render_time.abort();
                xhr_render_time = null;
            }
        }

        xhr_render_time = $.ajax({
            url         : BooklyL10n.ajaxurl,
            data        : data,
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success == false) {
                    // The session doesn't contain data.
                    stepService({form_id: params.form_id});
                    return;
                }
                BooklyL10n.csrf_token = response.csrf_token;

                $container.html(response.html);
                var $columnizer_wrap    = $('.bookly-columnizer-wrap', $container),
                    $columnizer         = $('.bookly-columnizer', $columnizer_wrap),
                    $time_next_button   = $('.bookly-time-next',  $container),
                    $time_prev_button   = $('.bookly-time-prev',  $container),
                    $current_screen     = null,
                    slot_height         = 36,
                    column_width        = response.time_slots_wide ? 205 : 127,
                    column_class        = response.time_slots_wide ? 'bookly-column bookly-column-wide' : 'bookly-column',
                    columns             = 0,
                    screen_index        = 0,
                    has_more_slots      = response.has_more_slots,
                    form_hidden         = false,
                    show_calendar       = response.show_calendar,
                    is_rtl              = response.is_rtl,
                    $screens,
                    slots_per_column,
                    columns_per_screen,
                    show_day_per_column = response.day_one_column,
                    slots               = prepareSlotsHtml( response.slots_data, response.selected_date )
                ;
                // 'BACK' button.
                $('.bookly-js-back-step', $container).on('click', function (e) {
                    e.preventDefault();
                    laddaStart(this);
                    if (!opt[params.form_id].skip_steps.extras && !opt[params.form_id].no_extras) {
                        if (opt[params.form_id].step_extras == 'before_step_time') {
                            stepExtras({form_id: params.form_id});
                        } else {
                            stepService({form_id: params.form_id});
                        }
                    } else {
                        stepService({form_id: params.form_id});
                    }
                }).toggle(!opt[params.form_id].skip_steps.service || !opt[params.form_id].skip_steps.extras);

                $('.bookly-js-go-to-cart', $container).on('click', function(e) {
                    e.preventDefault();
                    laddaStart(this);
                    stepCart({form_id: params.form_id, from_step : 'time'});
                });

                // Time zone switcher.
                $('.bookly-js-time-zone-switcher', $container).on('change', function (e) {
                    opt[params.form_id].timeZone       = this.value;
                    opt[params.form_id].timeZoneOffset = undefined;
                    showSpinner();
                    dropAjax();
                    stepTime({
                        form_id: params.form_id,
                        time_zone: opt[params.form_id].timeZone
                    });
                });

                if (show_calendar) {
                    // Init calendar.
                    var $input = $('.bookly-js-selected-date', $container);
                    $input.pickadate({
                        formatSubmit  : 'yyyy-mm-dd',
                        format        : opt[params.form_id].date_format,
                        min           : response.date_min || true,
                        max           : response.date_max || true,
                        weekdaysFull  : BooklyL10n.days,
                        weekdaysShort : BooklyL10n.daysShort,
                        monthsFull    : BooklyL10n.months,
                        firstDay      : opt[params.form_id].firstDay,
                        clear         : false,
                        close         : false,
                        today         : false,
                        disable       : response.disabled_days,
                        closeOnSelect : false,
                        klass : {
                            picker: 'picker picker--opened picker--focused'
                        },
                        onSet: function(e) {
                            if (e.select) {
                                var date = this.get('select', 'yyyy-mm-dd');
                                if (slots[date]) {
                                    // Get data from response.slots.
                                    $columnizer.html(slots[date]).css('left', '0px');
                                    columns = 0;
                                    screen_index = 0;
                                    $current_screen = null;
                                    initSlots();
                                    $time_prev_button.hide();
                                    $time_next_button.toggle($screens.length != 1);
                                } else {
                                    // Load new data from server.
                                    dropAjax();
                                    stepTime({form_id: params.form_id, selected_date : date});
                                    showSpinner();
                                }
                            }
                            this.open();   // Fix ultimate-member plugin
                        },
                        onClose: function() {
                            this.open(false);
                        },
                        onRender: function() {
                            var date = new Date(Date.UTC(this.get('view').year, this.get('view').month));
                            $('.picker__nav--next', $container).on('click', function() {
                                date.setUTCMonth(date.getUTCMonth() + 1);
                                dropAjax();
                                stepTime({form_id: params.form_id, selected_date : date.toJSON().substr(0, 10)});
                                showSpinner();
                            });
                            $('.picker__nav--prev', $container).on('click', function() {
                                date.setUTCMonth(date.getUTCMonth() - 1);
                                dropAjax();
                                stepTime({form_id: params.form_id, selected_date : date.toJSON().substr(0, 10)});
                                showSpinner();
                            });
                        }
                    });
                    // Insert slots for selected day.
                    var date = $input.pickadate('picker').get('select', 'yyyy-mm-dd');
                    $columnizer.html(slots[date]);
                } else {
                    // Insert all slots.
                    var slots_data = '';
                    $.each(slots, function(group, group_slots) {
                        slots_data += group_slots;
                    });
                    $columnizer.html(slots_data);
                }

                if (response.has_slots) {
                    if (error_message) {
                        $container.find('.bookly-label-error').html(error_message);
                    } else {
                        $container.find('.bookly-label-error').hide();
                    }

                    // Calculate number of slots per column.
                    slots_per_column = parseInt($(window).height() / slot_height, 10);
                    if (slots_per_column < 4) {
                        slots_per_column = 4;
                    } else if (slots_per_column > 10) {
                        slots_per_column = 10;
                    }

                    columns_per_screen = parseInt($columnizer_wrap.width() / column_width, 10);

                    if (columns_per_screen > 10) {
                        columns_per_screen = 10;
                    } else if (columns_per_screen == 0) {
                        // Bookly form display hidden.
                        form_hidden = true;
                        columns_per_screen = 4;
                    }

                    initSlots();

                    if (!has_more_slots && $screens.length == 1) {
                        $time_next_button.hide();
                    }

                    var hammertime = $('.bookly-time-step', $container).hammer({ swipe_velocity: 0.1 });

                    hammertime.on('swipeleft', function() {
                        if ($time_next_button.is(':visible')) {
                            $time_next_button.trigger('click');
                        }
                    });

                    hammertime.on('swiperight', function() {
                        if ($time_prev_button.is(':visible')) {
                            $time_prev_button.trigger('click');
                        }
                    });

                    $time_next_button.on('click', function (e) {
                        $time_prev_button.show();
                        if ($screens.eq(screen_index + 1).length) {
                            $columnizer.animate(
                                { left: (is_rtl ? '+' : '-') + ( screen_index + 1 ) * $current_screen.width() },
                                { duration: 800 }
                            );

                            $current_screen = $screens.eq(++ screen_index);
                            $columnizer_wrap.animate(
                                { height: $current_screen.height() },
                                { duration: 800 }
                            );

                            if (screen_index + 1 == $screens.length && !has_more_slots) {
                                $time_next_button.hide();
                            }
                        } else if (has_more_slots) {
                            // Do ajax request when there are more slots.
                            var $button = $('> button:last', $columnizer);
                            if ($button.length == 0) {
                                $button = $('.bookly-column:hidden:last > button:last', $columnizer);
                                if ($button.length == 0) {
                                    $button = $('.bookly-column:last > button:last', $columnizer);
                                }
                            }

                            // Render Next Time
                            var data = {
                                    action     : 'bookly_render_next_time',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : params.form_id,
                                    last_slot  : $button.val()
                                },
                                ladda = laddaStart(this);

                            $.ajax({
                                type : 'POST',
                                url  : BooklyL10n.ajaxurl,
                                data : data,
                                dataType : 'json',
                                xhrFields : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success : function (response) {
                                    if (response.success) {
                                        if (response.has_slots) { // if there are available time
                                            has_more_slots = response.has_more_slots;
                                            var slots_data = '';
                                            $.each(prepareSlotsHtml(response.slots_data, response.selected_date), function(group, group_slots) {
                                                slots_data += group_slots;
                                            });
                                            var $html = $(slots_data);
                                            // The first slot is always a day slot.
                                            // Check if such day slot already exists (this can happen
                                            // because of time zone offset) and then remove the first slot.
                                            var $first_day = $html.eq(0);
                                            if ($('button.bookly-day[value="' + $first_day.attr('value') + '"]', $container).length) {
                                                $html = $html.not(':first');
                                            }
                                            $columnizer.append($html);
                                            initSlots();
                                            $time_next_button.trigger('click');
                                        } else { // no available time
                                            $time_next_button.hide();
                                        }
                                    } else { // no available time
                                        $time_next_button.hide();
                                    }
                                    ladda.stop();
                                }
                            });
                        }
                    });

                    $time_prev_button.on('click', function () {
                        $time_next_button.show();
                        $current_screen = $screens.eq(-- screen_index);
                        $columnizer.animate(
                            { left: (is_rtl ? '+' : '-') + screen_index * $current_screen.width() },
                            { duration: 800 }
                        );
                        $columnizer_wrap.animate(
                            { height: $current_screen.height() },
                            { duration: 800 }
                        );
                        if (screen_index === 0) {
                            $time_prev_button.hide();
                        }
                    });
                }
                if (params === undefined) {     // Scroll when returning to the step Time.
                    scrollTo($container);
                }

                function showSpinner() {
                    $('.bookly-time-screen,.bookly-not-time-screen', $container).addClass('bookly-spin-overlay');
                    var opts = {
                        lines : 11, // The number of lines to draw
                        length: 11, // The length of each line
                        width : 4,  // The line thickness
                        radius: 5   // The radius of the inner circle
                    };
                    if ($screens) {
                        new Spinner(opts).spin($screens.eq(screen_index).get(0));
                    } else {
                        // Calendar not available month.
                        new Spinner(opts).spin($('.bookly-not-time-screen', $container).get(0));
                    }
                }

                function initSlots() {
                    var $buttons    = $('> button', $columnizer),
                        slots_count = 0,
                        max_slots   = 0,
                        $button,
                        $column,
                        $screen;

                    if (show_day_per_column) {
                        /**
                         * Create columns for 'Show each day in one column' mode.
                         */
                        while ($buttons.length > 0) {
                            // Create column.
                            if ($buttons.eq(0).hasClass('bookly-day')) {
                                slots_count = 1;
                                $column = $('<div class="' + column_class + '" />');
                                $button = $($buttons.splice(0, 1));
                                $button.addClass('bookly-js-first-child');
                                $column.append($button);
                            } else {
                                slots_count ++;
                                $button = $($buttons.splice(0, 1));
                                // If it is last slot in the column.
                                if (!$buttons.length || $buttons.eq(0).hasClass('bookly-day')) {
                                    $button.addClass('bookly-last-child');
                                    $column.append($button);
                                    $columnizer.append($column);
                                } else {
                                    $column.append($button);
                                }
                            }
                            // Calculate max number of slots.
                            if (slots_count > max_slots) {
                                max_slots = slots_count;
                            }
                        }
                    } else {
                        /**
                         * Create columns for normal mode.
                         */
                        while (has_more_slots ? $buttons.length > slots_per_column : $buttons.length) {
                            $column = $('<div class="' + column_class + '" />');
                            max_slots = slots_per_column;
                            if (columns % columns_per_screen == 0 && !$buttons.eq(0).hasClass('bookly-day')) {
                                // If this is the first column of a screen and the first slot in this column is not day
                                // then put 1 slot less in this column because createScreens adds 1 more
                                // slot to such columns.
                                -- max_slots;
                            }
                            for (var i = 0; i < max_slots; ++ i) {
                                if (i + 1 == max_slots && $buttons.eq(0).hasClass('bookly-day')) {
                                    // Skip the last slot if it is day.
                                    break;
                                }
                                $button = $($buttons.splice(0, 1));
                                if (i == 0) {
                                    $button.addClass('bookly-js-first-child');
                                } else if (i + 1 == max_slots) {
                                    $button.addClass('bookly-last-child');
                                }
                                $column.append($button);
                            }
                            $columnizer.append($column);
                            ++ columns;
                        }
                    }
                    /**
                     * Create screens.
                     */
                    var $columns = $('> .bookly-column', $columnizer);

                    while (has_more_slots ? $columns.length >= columns_per_screen : $columns.length) {
                        $screen = $('<div class="bookly-time-screen"/>');
                        for (var i = 0; i < columns_per_screen; ++i) {
                            $column = $($columns.splice(0, 1));
                            if (i == 0) {
                                $column.addClass('bookly-js-first-column');
                                var $first_slot = $column.find('.bookly-js-first-child');
                                // In the first column the first slot is time.
                                if (!$first_slot.hasClass('bookly-day')) {
                                    var group = $first_slot.data('group'),
                                        $group_slot = $('button.bookly-day[value="' + group + '"]:last', $container);
                                    // Copy group slot to the first column.
                                    $column.prepend($group_slot.clone());
                                }
                            }
                            $screen.append($column);
                        }
                        $columnizer.append($screen);
                    }
                    $screens = $('.bookly-time-screen', $columnizer);
                    if ($current_screen === null) {
                        $current_screen = $screens.eq(0);
                    }

                    $('button.bookly-time-skip', $container).off('click').on('click', function (e) {
                        laddaStart(this);
                        if (!opt[params.form_id].skip_steps.cart) {
                            stepCart({form_id: params.form_id, add_to_cart: true, from_step: 'time'});
                        } else {
                            stepDetails({form_id: params.form_id, add_to_cart : true});
                        }
                    });

                    // On click on a slot.
                    var xhr_session_save = null;
                    $('button.bookly-hour', $container).off('click').on('click', function (e) {
                        if ( xhr_session_save != null) {
                            xhr_session_save.abort();
                            xhr_session_save = null;
                        }
                        e.preventDefault();
                        var $this = $(this),
                            data = {
                                action     : 'bookly_session_save',
                                csrf_token : BooklyL10n.csrf_token,
                                form_id    : params.form_id,
                                slots      : this.value
                            };
                        $this.attr({'data-style': 'zoom-in','data-spinner-color':'#333','data-spinner-size':'40'});
                        laddaStart(this);
                        xhr_session_save = $.ajax({
                            type : 'POST',
                            url  : BooklyL10n.ajaxurl,
                            data : data,
                            dataType  : 'json',
                            xhrFields : { withCredentials: true },
                            crossDomain : 'withCredentials' in new XMLHttpRequest(),
                            success : function (response) {
                                if(!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_extras) {
                                    stepExtras({form_id: params.form_id});
                                } else if (!opt[params.form_id].skip_steps.repeat) {
                                    stepRepeat({form_id: params.form_id});
                                } else if (!opt[params.form_id].skip_steps.cart) {
                                    stepCart({form_id: params.form_id, add_to_cart : true, from_step : 'time'});
                                } else {
                                    stepDetails({form_id: params.form_id, add_to_cart : true});
                                }
                            }
                        });
                    });

                    // Columnizer width & height.
                    $('.bookly-time-step', $container).width(columns_per_screen * column_width);
                    $columnizer_wrap.height(form_hidden
                        ? $('.bookly-column.bookly-js-first-column button', $current_screen).length * (slot_height + 3)
                        : $current_screen.height());
                    form_hidden = false;
                }
            }
        });
    }

    /**
     * Extras step.
     */
    function stepExtras(params) {
        var data = {
                action    : 'bookly_render_extras',
                csrf_token: BooklyL10n.csrf_token,
            },
            $container = opt[params.form_id].$container;
        if (opt[params.form_id].skip_steps.service && opt[params.form_id].use_client_time_zone) {
            // If Service step is skipped then we need to send time zone offset.
            data.time_zone        = opt[params.form_id].timeZone;
            data.time_zone_offset = opt[params.form_id].timeZoneOffset;
        }
        $.extend(data, params);
        $.ajax({
            url: BooklyL10n.ajaxurl,
            data: data,
            dataType: 'json',
            xhrFields: {withCredentials: true},
            crossDomain: 'withCredentials' in new XMLHttpRequest(),
            success: function (response) {
                if (response.success) {
                    BooklyL10n.csrf_token = response.csrf_token;
                    $container.html(response.html);
                    if (params === undefined) { // Scroll when returning to the step Extras.
                        scrollTo($container);
                    }
                    var $next_step = $('.bookly-js-next-step', $container),
                        $back_step = $('.bookly-js-back-step', $container),
                        $goto_cart = $('.bookly-js-go-to-cart', $container),
                        $extras_items = $('.bookly-js-extras-item', $container),
                        $extras_summary = $('.bookly-js-extras-summary span', $container),
                        currency = response.currency,
                        $this,
                        $input;

                    var extrasChanged = function($extras_item, quantity) {
                        var $input = $extras_item.find('input'),
                            $total = $extras_item.find('.bookly-js-extras-total-price'),
                            total_price = quantity * parseFloat($extras_item.data('price'));

                        $total.text(currency.format.replace('1', total_price.toFixed(currency.precision)));
                        $input.val(quantity);
                        $extras_item.find('.bookly-js-extras-thumb').toggleClass('bookly-extras-selected', quantity > 0);

                        // Updating summary
                        var amount = 0;
                        $extras_items.each(function (index, elem) {
                            var $this = $(this),
                                multiplier = $this.closest('.bookly-js-extras-container').data('multiplier');
                            amount += parseFloat($this.data('price')) * $this.find('input').val() * multiplier;
                        });
                        if (amount) {
                            $extras_summary.html(' + ' + currency.format.replace('1', amount.toFixed(currency.precision)));
                        } else {
                            $extras_summary.html('');
                        }
                    };

                    $extras_items.each(function (index, elem) {
                        var $this = $(this);
                        var $input = $this.find('input');
                        $this.find('.bookly-js-extras-thumb').on('click', function () {
                            extrasChanged($this, $input.val() > 0 ? 0 : 1);
                        });
                        $this.find('.bookly-js-count-control').on('click', function() {
                            var count = parseInt($input.val());
                            count = $(this).hasClass('bookly-js-extras-increment')
                                ? Math.min($this.data('max_quantity'), count + 1)
                                : Math.max(0, count - 1);
                            extrasChanged($this, count);
                        });
                    });

                    $goto_cart.on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        stepCart({form_id: params.form_id, from_step : 'extras'});
                    });

                    $next_step.on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        var extras = {};
                        $('.bookly-js-extras-container', $container).each(function () {
                            var $extras_container = $(this);
                            var chain_id = $extras_container.data('chain');
                            var chain_extras = {};
                            // Get checked extras for chain.
                            $extras_container.find('.bookly-js-extras-item').each(function (index, elem) {
                                $this = $(this);
                                $input = $this.find('input');
                                if ($input.val() > 0) {
                                    chain_extras[$this.data('id')] = $input.val();
                                }
                            });
                            extras[chain_id] = JSON.stringify(chain_extras);
                        });
                        $.ajax({
                            type : 'POST',
                            url  : BooklyL10n.ajaxurl,
                            data : {
                                action     : 'bookly_session_save',
                                csrf_token : BooklyL10n.csrf_token,
                                form_id    : params.form_id,
                                extras     : extras
                            },
                            dataType: 'json',
                            xhrFields: {withCredentials: true},
                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                            success: function (response) {
                                if(opt[params.form_id].step_extras == 'before_step_time') {
                                    stepTime({form_id: params.form_id, prev_step: 'extras'});
                                } else if (!opt[params.form_id].skip_steps.repeat) {
                                    stepRepeat({form_id: params.form_id});
                                } else if (!opt[params.form_id].skip_steps.cart) {
                                    stepCart({form_id: params.form_id, add_to_cart : true, from_step : 'time'});
                                } else {
                                    stepDetails({form_id: params.form_id, add_to_cart : true});
                                }
                            }
                        });
                    });
                    $back_step.on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        if (opt[params.form_id].step_extras == 'after_step_time' && !opt[params.form_id].no_time) {
                            stepTime({form_id: params.form_id, prev_step: 'extras'});
                        } else {
                            stepService({form_id: params.form_id});
                        }
                    });
                }
            }
        });
    }

    /**
     * Service step.
     */
    function stepService(params) {
        if (opt[params.form_id].skip_steps.service) {
            if (!opt[params.form_id].skip_steps.extras && opt[params.form_id].step_extras == 'before_step_time') {
                stepExtras(params);
            } else {
                stepTime(params);
            }
            return;
        }
        var data = {
                action    : 'bookly_render_service',
                csrf_token: BooklyL10n.csrf_token,
            },
            $container = opt[params.form_id].$container;
        if (opt[params.form_id].use_client_time_zone) {
            data.time_zone        = opt[params.form_id].timeZone;
            data.time_zone_offset = opt[params.form_id].timeZoneOffset;
        }
        $.extend(data, params);
        $.ajax({
            url         : BooklyL10n.ajaxurl,
            data        : data,
            dataType    : 'json',
            xhrFields   : { withCredentials: true },
            crossDomain : 'withCredentials' in new XMLHttpRequest(),
            success     : function (response) {
                if (response.success) {
                    BooklyL10n.csrf_token = response.csrf_token;
                    $container.html(response.html);
                    if (params === undefined) { // Scroll when returning to the step Service. default value {new_chain : true}
                        scrollTo($container);
                    }

                    var $chain_item_draft          = $('.bookly-js-chain-item.bookly-js-draft', $container),
                        $select_location           = $('.bookly-js-select-location', $container),
                        $select_category           = $('.bookly-js-select-category', $container),
                        $select_service            = $('.bookly-js-select-service', $container),
                        $select_employee           = $('.bookly-js-select-employee', $container),
                        $select_duration           = $('.bookly-js-select-units-duration', $container),
                        $select_nop                = $('.bookly-js-select-number-of-persons', $container),
                        $select_quantity           = $('.bookly-js-select-quantity', $container),
                        $date_from                 = $('.bookly-js-date-from', $container),
                        $week_day                  = $('.bookly-js-week-day', $container),
                        $select_time_from          = $('.bookly-js-select-time-from', $container),
                        $select_time_to            = $('.bookly-js-select-time-to', $container),
                        $next_step                 = $('.bookly-js-next-step', $container),
                        $mobile_next_step          = $('.bookly-js-mobile-next-step', $container),
                        $mobile_prev_step          = $('.bookly-js-mobile-prev-step', $container),
                        locations                  = response.locations,
                        categories                 = response.categories,
                        services                   = response.services,
                        staff                      = response.staff,
                        chain                      = response.chain,
                        required                   = response.required,
                        defaults                   = opt[params.form_id].defaults,
                        services_per_location      = response.services_per_location,
                        last_chain_key             = 0,
                        category_selected          = false,
                        service_name_with_duration = response.service_name_with_duration,
                        show_ratings               = response.show_ratings;

                    // Init Pickadate.
                    $date_from.pickadate({
                        formatSubmit    : 'yyyy-mm-dd',
                        format          : opt[params.form_id].date_format,
                        min             : response.date_min || true,
                        max             : response.date_max || true,
                        clear           : false,
                        close           : false,
                        today           : BooklyL10n.today,
                        monthsFull      : BooklyL10n.months,
                        weekdaysFull    : BooklyL10n.days,
                        weekdaysShort   : BooklyL10n.daysShort,
                        labelMonthNext  : BooklyL10n.nextMonth,
                        labelMonthPrev  : BooklyL10n.prevMonth,
                        firstDay        : opt[params.form_id].firstDay,
                        onSet           : function(timestamp) {
                            if ($.isNumeric(timestamp.select)) {
                                // Checks appropriate day of the week
                                var date = new Date(timestamp.select);
                                $('.bookly-js-week-day[value="' + (date.getDay() + 1) + '"]:not(:checked)', $container).attr('checked', true).trigger('change');
                            }
                        }
                    });

                    $('.bookly-js-go-to-cart', $container).on('click', function (e) {
                        e.preventDefault();
                        laddaStart(this);
                        stepCart({form_id: params.form_id,from_step : 'service'});
                    });

                    // insert data into select
                    var setSelect = function($select, data, value) {
                        // reset select
                        $('option:not([value=""])', $select).remove();
                        // and fill the new data
                        var docFragment = document.createDocumentFragment();

                        function valuesToArray(obj) {
                            return Object.keys(obj).map(function (key) { return obj[key]; });
                        }

                        function compare(a, b) {
                            if (parseInt(a.pos) < parseInt(b.pos))
                                return -1;
                            if (parseInt(a.pos) > parseInt(b.pos))
                                return 1;
                            return 0;
                        }

                        // sort select by position
                        data = valuesToArray(data).sort(compare);

                        $.each(data, function(key, object) {
                            var option = document.createElement('option');
                            option.value = object.id;
                            option.text = object.name;
                            docFragment.appendChild(option);
                        });
                        $select.append(docFragment);
                        // set default value of select
                        if ($select.find('option[value="' + value + '"]').length) {
                            $select.val(value);
                        }
                    };

                    var setSelects = function($chain_item, location_id, category_id, service_id, staff_id) {
                        var _location_id = (services_per_location && location_id) ? location_id : 0;
                        var _staff = {}, _services = {}, _categories = {}, _nop = {}, _max_capacity = null, _min_capacity = null;
                        $.each(staff, function(id, staff_member) {
                            if (!location_id || locations[location_id].staff.hasOwnProperty(id)) {
                                if (!service_id) {
                                    if (!category_id) {
                                        _staff[id] = $.extend({}, staff_member);
                                    } else {
                                        $.each(staff_member.services, function(s_id) {
                                            if (services[s_id].category_id == category_id) {
                                                _staff[id] = $.extend({}, staff_member);
                                                return false;
                                            }
                                        });
                                    }
                                } else if (staff_member.services.hasOwnProperty(service_id)) {
                                    $.each(staff_member.services[service_id].locations, function(loc_id, loc_srv) {
                                        if (_location_id && _location_id != loc_id) {
                                            return true;
                                        }
                                        _min_capacity = _min_capacity ? Math.min(_min_capacity, loc_srv.min_capacity) : loc_srv.min_capacity;
                                        _max_capacity = _max_capacity ? Math.max(_max_capacity, loc_srv.max_capacity) : loc_srv.max_capacity;
                                        _staff[id] = {
                                            id   : id,
                                            name : staff_member.name + (
                                                loc_srv.price != null && (_location_id || !services_per_location)
                                                    ? ' (' + loc_srv.price + ')'
                                                    : ''
                                            ),
                                            pos  : staff_member.pos
                                        };
                                    });
                                }
                            }
                        });
                        if (!location_id) {
                            _categories = categories;
                            $.each(services, function(id, service) {
                                if (!category_id || service.category_id == category_id) {
                                    if (!staff_id || staff[staff_id].services.hasOwnProperty(id)) {
                                        _services[id] = service;
                                    }
                                }
                            });
                        } else {
                            var category_ids = [],
                                service_ids  = [];
                            if (services_per_location) {
                                $.each(staff, function (st_id) {
                                    $.each(staff[st_id].services, function (s_id) {
                                        if (staff[st_id].services[s_id].locations.hasOwnProperty(_location_id)) {
                                            category_ids.push(services[s_id].category_id);
                                            service_ids.push(s_id);
                                        }
                                    });
                                });
                            } else {
                                $.each(locations[location_id].staff, function(st_id) {
                                    $.each(staff[st_id].services, function(s_id) {
                                        category_ids.push(services[s_id].category_id);
                                        service_ids.push(s_id);
                                    });
                                });
                            }
                            $.each(categories, function(id, category) {
                                if ($.inArray(parseInt(id), category_ids) > -1) {
                                    _categories[id] = category;
                                }
                            });
                            $.each(services, function(id, service) {
                                if ($.inArray(id, service_ids) > -1) {
                                    if (!category_id || service.category_id == category_id) {
                                        if (!staff_id || staff[staff_id].services.hasOwnProperty(id)) {
                                            _services[id] = service;
                                        }
                                    }
                                }
                            });
                        }
                        var nop = $('.bookly-js-select-number-of-persons', $chain_item).val() || 1;
                        var max_capacity = service_id
                            ? (staff_id
                                ? (staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id) ?
                                        staff[staff_id].services[service_id].locations[_location_id].max_capacity :
                                        1
                                )
                                : _max_capacity ? _max_capacity : 1)
                            : 1;
                        var min_capacity = service_id
                            ? (staff_id
                                ? (staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id) ?
                                        staff[staff_id].services[service_id].locations[_location_id].min_capacity :
                                        1
                                )
                                : _min_capacity ? _min_capacity : 1)
                            : 1;
                        for (var i = min_capacity; i <= max_capacity; ++ i) {
                            _nop[i] = { id: i, name: i, pos: i };
                        }
                        if (nop > max_capacity) {
                            nop = max_capacity;
                        }
                        if (nop < min_capacity || !opt[params.form_id].form_attributes.show_number_of_persons) {
                            nop = min_capacity;
                        }
                        // Add ratings to staff names
                        if (show_ratings) {
                            $.each(staff, function (id, staff_member) {
                                if (_staff.hasOwnProperty(staff_member.id)) {
                                    if (service_id) {
                                        if (staff_member.services.hasOwnProperty(service_id) && staff_member.services[service_id].rating) {
                                            _staff[staff_member.id].name = '★' + staff_member.services[service_id].rating + ' ' + _staff[staff_member.id].name;
                                        }
                                    } else if (staff_member.rating) {
                                        _staff[staff_member.id].name = '★' + staff_member.rating + ' ' + _staff[staff_member.id].name;
                                    }
                                }
                            });
                        }
                        setSelect($chain_item.find('.bookly-js-select-category'), _categories, category_id);
                        setSelect($chain_item.find('.bookly-js-select-service'), _services, service_id);
                        setSelect($chain_item.find('.bookly-js-select-employee'), _staff, staff_id);
                        setSelect($chain_item.find('.bookly-js-select-number-of-persons'), _nop, nop);
                    };

                    $container.off('click').off('change');

                    // Location select change
                    $container.on('change', '.bookly-js-select-location', function () {
                        var $chain_item = $(this).closest('.bookly-js-chain-item'),
                            location_id = this.value,
                            category_id = $chain_item.find('.bookly-js-select-category').val(),
                            service_id  = $chain_item.find('.bookly-js-select-service').val(),
                            staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                        ;

                        // Validate selected values.
                        if (location_id) {
                            var _location_id = services_per_location ? location_id : 0;
                            if (staff_id) {
                                if (!locations[location_id].staff.hasOwnProperty(staff_id)) {
                                    staff_id = '';
                                } else if (service_id && !staff[staff_id].services[service_id].locations.hasOwnProperty(_location_id)) {
                                    staff_id = '';
                                }
                            }
                            if (service_id) {
                                var valid = false;
                                $.each(locations[location_id].staff, function(id) {
                                    if (staff[id].services.hasOwnProperty(service_id) && staff[id].services[service_id].locations.hasOwnProperty(_location_id)) {
                                        valid = true;
                                        return false;
                                    }
                                });
                                if (!valid) {
                                    service_id = '';
                                }
                            }
                            if (category_id) {
                                var valid = false;
                                $.each(locations[location_id].staff, function(id) {
                                    $.each(staff[id].services, function(s_id) {
                                        if (services[s_id].category_id == category_id) {
                                            valid = true;
                                            return false;
                                        }
                                    });
                                    if (valid) {
                                        return false;
                                    }
                                });
                                if (!valid) {
                                    category_id = '';
                                }
                            }
                        }
                        setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
                    });

                    // Category select change
                    $container.on('change', '.bookly-js-select-category', function () {
                        var $chain_item = $(this).closest('.bookly-js-chain-item'),
                            location_id = $chain_item.find('.bookly-js-select-location').val(),
                            category_id = this.value,
                            service_id  = $chain_item.find('.bookly-js-select-service').val(),
                            staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                        ;

                        // Validate selected values.
                        if (category_id) {
                            category_selected = true;
                            if (service_id) {
                                if (services[service_id].category_id != category_id) {
                                    service_id = '';
                                }
                            }
                            if (staff_id) {
                                var valid = false;
                                $.each(staff[staff_id].services, function(id) {
                                    if (services[id].category_id == category_id) {
                                        valid = true;
                                        return false;
                                    }
                                });
                                if (!valid) {
                                    staff_id = '';
                                }
                            }
                        } else {
                            category_selected = false;
                        }
                        setSelects($chain_item, location_id, category_id, service_id, staff_id);
                    });

                    var updateServiceDurationSelect = function($chain_item, service_id, staff_id, location_id) {
                        var $units_duration = $chain_item.find('.bookly-js-select-units-duration'),
                            current_duration = $units_duration.val();
                        $units_duration.find('option').remove();
                        if (service_id) {
                            var getUnitsByStaffId = function (staff_id) {
                                if (!staff_id || services_per_location && !location_id) {
                                    return services[service_id].hasOwnProperty('units')
                                        ? services[service_id]['units']
                                        : [{'value': '', 'title': '-'}];
                                }

                                var locationId = location_id ? location_id : 0,
                                    staffLocations = staff[staff_id].services[service_id].locations;
                                if (staffLocations === undefined) {
                                    return [{'value': '', 'title': '-'}];
                                }
                                var staffLocation = staffLocations.hasOwnProperty(locationId) ? staffLocations[locationId] : staffLocations[0];
                                return staffLocation.units || [{'value': '', 'title': '-'}];
                            };

                            // add slots for picked service
                            $.each(getUnitsByStaffId(staff_id), function (i, item) {
                                $units_duration.append($('<option>', {
                                    value: item.value,
                                    text: item.title
                                }));
                            });
                            if ($units_duration.find('option[value="' + current_duration + '"]').length != 0) {
                                $units_duration.val(current_duration);
                            }
                        } else {
                            $units_duration.append($('<option>', {
                                value: '',
                                text: '-'
                            }));
                        }
                    };

                    // Service select change
                    $container.on('change', '.bookly-js-select-service', function () {
                        var $chain_item = $(this).closest('.bookly-js-chain-item'),
                            location_id = $chain_item.find('.bookly-js-select-location').val(),
                            category_id = category_selected
                                ? $chain_item.find('.bookly-js-select-category').val()
                                : '',
                            service_id  = this.value,
                            staff_id    = $chain_item.find('.bookly-js-select-employee').val()
                        ;

                        // Validate selected values.
                        if (service_id) {
                            if (staff_id && !staff[staff_id].services.hasOwnProperty(service_id)) {
                                staff_id = '';
                            }
                        }
                        setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        if (service_id) {
                            $chain_item.find('.bookly-js-select-category').val(services[service_id].category_id);
                        }
                        updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
                    });

                    // Staff select change
                    $container.on('change', '.bookly-js-select-employee', function() {
                        var $chain_item = $(this).closest('.bookly-js-chain-item'),
                            location_id = $chain_item.find('.bookly-js-select-location').val(),
                            category_id = $('.bookly-js-select-category', $chain_item).val(),
                            service_id  = $chain_item.find('.bookly-js-select-service').val(),
                            staff_id    = this.value
                        ;

                        setSelects($chain_item, location_id, category_id, service_id, staff_id);
                        updateServiceDurationSelect($chain_item, service_id, staff_id, location_id);
                    });

                    // Set up draft selects.
                    if (service_name_with_duration) {
                        $.each(services, function(id, service) {
                            service.name = service.name + ' ( ' + service.duration + ' )';
                        });
                    }

                    setSelect($select_location, locations);
                    setSelect($select_category, categories);
                    setSelect($select_service, services);
                    if (show_ratings) {
                        var _staff = {};
                        $.each(staff, function (id, staff_member) {
                            _staff[id] = $.extend({}, staff_member);
                            if (staff_member.rating) {
                                _staff[id].name = '★' + staff_member.rating + ' ' + _staff[id].name;
                            }
                        });
                        setSelect($select_employee, _staff);
                    } else {
                        setSelect($select_employee, staff);
                    }
                    $select_location.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_locations);
                    $select_category.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_categories);
                    $select_service.closest('.bookly-form-group').toggle(!(opt[params.form_id].form_attributes.hide_services && defaults.service_id));
                    $select_employee.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_staff_members);
                    $select_duration.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_service_duration);
                    $select_nop.closest('.bookly-form-group').toggle(opt[params.form_id].form_attributes.show_number_of_persons);
                    $select_quantity.closest('.bookly-form-group').toggle(!opt[params.form_id].form_attributes.hide_quantity);
                    if (defaults.location_id) {
                        $select_location.val(defaults.location_id).trigger('change');
                    }
                    if (defaults.category_id) {
                        $select_category.val(defaults.category_id).trigger('change');
                    }
                    if (defaults.service_id) {
                        $select_service.val(defaults.service_id).trigger('change');
                    }
                    if (defaults.staff_id) {
                        $select_employee.val(defaults.staff_id).trigger('change');
                    }

                    if (opt[params.form_id].form_attributes.hide_date) {
                        $('.bookly-js-available-date', $container).hide();
                    }
                    if (opt[params.form_id].form_attributes.hide_week_days) {
                        $('.bookly-js-week-days', $container).hide();
                    }
                    if (opt[params.form_id].form_attributes.hide_time_range) {
                        $('.bookly-js-time-range', $container).hide();
                    }

                    // Create chain items.
                    $.each(chain, function(key, chain_item) {
                        var $chain_item = $chain_item_draft
                            .clone()
                            .data('chain_key', key)
                            .removeClass('bookly-js-draft')
                            .css('display', 'table');
                        $chain_item_draft.find('select').each(function (i, select) {
                            $chain_item.find('select:eq(' + i + ')').val(select.value);
                        });
                        last_chain_key = key;
                        if (key == 0) {
                            $chain_item.find('.bookly-js-actions button[data-action="drop"]').remove();
                        }
                        $('.bookly-js-chain-item:last', $container).after($chain_item);
                        if (!opt[params.form_id].form_attributes.hide_locations && chain_item.location_id) {
                            $('.bookly-js-select-location', $chain_item).val(chain_item.location_id).trigger('change');
                        }
                        if (chain_item.service_id) {
                            $('.bookly-js-select-service', $chain_item).val(chain_item.service_id).trigger('change');
                            if (opt[params.form_id].form_attributes.hide_categories) {
                                if (opt[params.form_id].form_attributes.hasOwnProperty('const_category_id')) {
                                    // Keep category selected by 'admin'.
                                    $('.bookly-js-select-category', $chain_item).val(opt[params.form_id].form_attributes.const_category_id);
                                } else {
                                    // Deselect category to keep full list of services.
                                    $('.bookly-js-select-category', $chain_item).val('');
                                }
                            } else {
                                $('.bookly-js-select-category', $chain_item).val(services[chain_item.service_id].category_id).trigger('change');
                            }
                        }
                        if (!opt[params.form_id].form_attributes.hide_staff_members && chain_item.staff_ids.length == 1 && chain_item.staff_ids[0]) {
                            $('.bookly-js-select-employee', $chain_item).val(chain_item.staff_ids[0]).trigger('change');
                        }
                        if (chain_item.number_of_persons > 1) {
                            $('.bookly-js-select-number-of-persons', $chain_item).val(chain_item.number_of_persons);
                        }
                        if (chain_item.units > 1) {
                            $('.bookly-js-select-units-duration', $chain_item).val(chain_item.units);
                        }
                        if (chain_item.quantity > 1) {
                            $('.bookly-js-select-quantity', $chain_item).val(chain_item.quantity);
                        }
                    });

                    $container.on('click', '.bookly-js-mobile-step-1 .bookly-js-add-chain', function () {
                        var $new_chain = $chain_item_draft.clone();
                        $chain_item_draft.find('select').each(function (i, select) {
                            $new_chain.find('select:eq(' + i + ')').val(select.value);
                        });
                        $('.bookly-js-chain-item:last', $container)
                            .after(
                                $new_chain
                                    .data('chain_key', ++ last_chain_key)
                                    .removeClass('bookly-js-draft')
                                    .css('display', 'table')
                            );
                    });
                    $container.on('click', '.bookly-js-mobile-step-1 .bookly-js-actions button[data-action="drop"]', function () {
                        $(this).closest('.bookly-js-chain-item').remove();
                    });

                    // change week days
                    $week_day.on('change', function () {
                        var $this = $(this);
                        if ($this.is(':checked')) {
                            $this.parent().not("[class*='active']").addClass('active');
                        } else {
                            $this.parent().removeClass('active');
                        }
                    });

                    // time from
                    $select_time_from.on('change', function () {
                        var start_time       = $(this).val(),
                            end_time         = $select_time_to.val(),
                            $last_time_entry = $('option:last', $select_time_from);

                        $select_time_to.empty();

                        // case when we click on the not last time entry
                        if ($select_time_from[0].selectedIndex < $last_time_entry.index()) {
                            // clone and append all next "time_from" time entries to "time_to" list
                            $('option', this).each(function () {
                                if ($(this).val() > start_time) {
                                    $select_time_to.append($(this).clone());
                                }
                            });
                            // case when we click on the last time entry
                        } else {
                            $select_time_to.append($last_time_entry.clone()).val($last_time_entry.val());
                        }

                        var first_value = $('option:first', $select_time_to).val();
                        $select_time_to.val(end_time >= first_value ? end_time : first_value);
                    });

                    var stepServiceValidator = function() {
                        $('.bookly-js-select-service-error',  $container).hide();
                        $('.bookly-js-select-employee-error', $container).hide();
                        $('.bookly-js-select-location-error', $container).hide();

                        var valid            = true,
                            $select_service  = null,
                            $select_employee = null,
                            $select_location = null,
                            $scroll_to       = null;

                        $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                            var $chain = $(this);
                            $select_service  = $('.bookly-js-select-service',  $chain);
                            $select_employee = $('.bookly-js-select-employee', $chain);
                            $select_location = $('.bookly-js-select-location', $chain);

                            $select_service.removeClass('bookly-error');
                            $select_employee.removeClass('bookly-error');
                            $select_location.removeClass('bookly-error');

                            // service validation
                            if (!$select_service.val()) {
                                valid = false;
                                $select_service.addClass('bookly-error');
                                $('.bookly-js-select-service-error', $chain).show();
                                $scroll_to = $select_service;
                            }
                            if (required.hasOwnProperty('location') && required.location && !$select_location.val()) {
                                valid = false;
                                $select_location.addClass('bookly-error');
                                $('.bookly-js-select-location-error', $chain).show();
                                $scroll_to = $select_location;
                            }
                            if (required.staff && !$select_employee.val()) {
                                valid = false;
                                $select_employee.addClass('bookly-error');
                                $('.bookly-js-select-employee-error', $chain).show();
                                $scroll_to = $select_employee;
                            }
                        });

                        $date_from.removeClass('bookly-error');
                        // date validation
                        if (!$date_from.val()) {
                            valid = false;
                            $date_from.addClass('bookly-error');
                            if ($scroll_to === null) {
                                $scroll_to = $date_from;
                            }
                        }

                        // week days
                        if (!$('.bookly-js-week-day:checked', $container).length) {
                            valid = false;
                            if ($scroll_to === null) {
                                $scroll_to = $week_day;
                            }
                        }

                        if ($scroll_to !== null) {
                            scrollTo($scroll_to);
                        }

                        return valid;
                    };

                    // "Next" click
                    $next_step.on('click', function (e) {
                        e.preventDefault();

                        if (stepServiceValidator()) {

                            laddaStart(this);

                            // Prepare chain data.
                            var chain = {},
                                has_extras = 0,
                                time_requirements = 0,
                                _time_requirements = {'required': 2, 'optional': 1, 'off': 0};
                            $('.bookly-js-chain-item:not(.bookly-js-draft)', $container).each(function () {
                                var $chain_item = $(this),
                                    staff_ids = [],
                                    _service = services[$('.bookly-js-select-service', $chain_item).val()];
                                if ($('.bookly-js-select-employee', $chain_item).val()) {
                                    staff_ids.push($('.bookly-js-select-employee', $chain_item).val());
                                } else {
                                    $('.bookly-js-select-employee', $chain_item).find('option').each(function () {
                                        if (this.value) {
                                            staff_ids.push(this.value);
                                        }
                                    });
                                }

                                chain[$chain_item.data('chain_key')] = {
                                    location_id       : $('.bookly-js-select-location', $chain_item).val(),
                                    service_id        : $('.bookly-js-select-service', $chain_item).val(),
                                    staff_ids         : staff_ids,
                                    units             : $('.bookly-js-select-units-duration', $chain_item).val() || 1,
                                    number_of_persons : $('.bookly-js-select-number-of-persons', $chain_item).val() || 1,
                                    quantity          : $('.bookly-js-select-quantity', $chain_item).val() ? $('.bookly-js-select-quantity', $chain_item).val() : 1
                                };
                                time_requirements = Math.max(time_requirements, _time_requirements[_service.hasOwnProperty('time_requirements') ? _service.time_requirements : 'required']);
                                has_extras += _service.has_extras;
                            });

                            // Prepare days.
                            var days = [];
                            $('.bookly-js-week-days .active input.bookly-js-week-day', $container).each(function() {
                                days.push(this.value);
                            });
                            $.ajax({
                                type : 'POST',
                                url  : BooklyL10n.ajaxurl,
                                data : {
                                    action     : 'bookly_session_save',
                                    csrf_token : BooklyL10n.csrf_token,
                                    form_id    : params.form_id,
                                    chain      : chain,
                                    date_from  : $date_from.pickadate('picker').get('select', 'yyyy-mm-dd'),
                                    days       : days,
                                    time_from  : $select_time_from.val(),
                                    time_to    : $select_time_to.val(),
                                    no_extras  : has_extras == 0
                                },
                                dataType    : 'json',
                                xhrFields   : { withCredentials: true },
                                crossDomain : 'withCredentials' in new XMLHttpRequest(),
                                success     : function (response) {
                                    opt[params.form_id].no_time = time_requirements == 0;
                                    opt[params.form_id].no_extras = has_extras == 0;
                                    if (opt[params.form_id].skip_steps.extras) {
                                        stepTime({form_id: params.form_id});
                                    } else {
                                        if (has_extras == 0 || opt[params.form_id].step_extras == 'after_step_time') {
                                            stepTime({form_id: params.form_id});
                                        } else {
                                            stepExtras({form_id: params.form_id});
                                        }
                                    }
                                }
                            });
                        }
                    });

                    $mobile_next_step.on('click', function (e,skip_scroll) {
                        if (stepServiceValidator()) {
                            if (opt[params.form_id].skip_steps.service_part2) {
                                laddaStart(this);
                                $next_step.trigger('click');
                            } else {
                                $('.bookly-js-mobile-step-1', $container).hide();
                                $('.bookly-js-mobile-step-2', $container).css('display', 'block');
                                if (skip_scroll != true) {
                                    scrollTo($container);
                                }
                            }
                        }

                        return false;
                    });

                    if (opt[params.form_id].skip_steps.service_part1) {
                        // Skip scrolling
                        $mobile_next_step.trigger('click', [true]);
                        $mobile_prev_step.remove();
                    } else {
                        $mobile_prev_step.on('click', function () {
                            $('.bookly-js-mobile-step-1', $container).show();
                            $('.bookly-js-mobile-step-2', $container).hide();
                            if ($select_service.val()) {
                                $('.bookly-js-select-service', $container).parent().removeClass('bookly-error');
                            }
                            return false;
                        });
                    }
                }
            } // ajax success
        }); // ajax
    }

    /**
     * Main Bookly function.
     *
     * @param options
     */
    window.bookly = function(options) {
        opt[options.form_id] = options;

        opt[options.form_id].$container         = $('#bookly-form-' + options.form_id);
        opt[options.form_id].timeZone           = typeof Intl === 'object' ? Intl.DateTimeFormat().resolvedOptions().timeZone : undefined;
        opt[options.form_id].timeZoneOffset     = new Date().getTimezoneOffset();
        opt[options.form_id].skip_steps.service = options.skip_steps.service_part1 && options.skip_steps.service_part2;

        // initialize
        if (options.status.booking == 'finished') {
            stepComplete({form_id: options.form_id});
        } else if (options.status.booking == 'cancelled') {
            stepPayment({form_id: options.form_id});
        } else {
            stepService({form_id: options.form_id, new_chain : true});
        }
        if (options.hasOwnProperty('facebook') && options.facebook.enabled) {
            initFacebookLogin(options);
        }

        // init google places

        if (options.hasOwnProperty('google_maps') && options.google_maps.enabled) {
            var apiKey = options.google_maps.api_key,
                src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places';

            importScript(src, true);
        }
        if (options.hasOwnProperty('stripe') && options.stripe.enabled) {
            importScript('https://js.stripe.com/v3/', true);
        }
    };

    /**
     * Init Facebook login.
     */
    function initFacebookLogin(options) {
        if (typeof FB !== 'undefined') {
            FB.init({
                appId: options.facebook.appId,
                status: true,
                version: 'v2.12'
            });
            FB.getLoginStatus(function (response) {
                if (response.status === 'connected') {
                    options.facebook.enabled = false;
                    FB.api('/me', {fields: 'id,name,first_name,last_name,email,link'}, function (userInfo) {
                        $.ajax({
                            type: 'POST',
                            url: BooklyL10n.ajaxurl,
                            data: $.extend(userInfo, {
                                action: 'bookly_pro_facebook_login',
                                csrf_token: BooklyL10n.csrf_token,
                                form_id: options.form_id
                            }),
                            dataType: 'json',
                            xhrFields: {withCredentials: true},
                            crossDomain: 'withCredentials' in new XMLHttpRequest(),
                            success: function (response) {
                            }
                        });
                    });
                } else {
                    FB.Event.subscribe('auth.statusChange', function (response) {
                        if (options.facebook.onStatusChange) {
                            options.facebook.onStatusChange(response);
                        }
                    });
                }
            });
        }
    }

    function importScript(src, async, onLoad) {
        var script = document.createElement("script");
        script.type = "text\/javascript";

        if (async !== undefined) {
            script.async = async;
        }
        if (onLoad instanceof Function) {
            script.onload = onLoad;
        }

        document.head.appendChild(script);
        script.src = src;
    }

}(jQuery));
