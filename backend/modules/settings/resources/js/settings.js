jQuery(function ($) {
    var $form                = $('#business-hours'),
        $final_step_url      = $('input[name=ab_settings_final_step_url]'),
        $final_step_url_mode = $('#ab_settings_final_step_url_mode')
        ;

    booklyAlert(BooklyL10n.alert);

    Ladda.bind('button[type=submit]', {timeout: 2000});

    $('.bookly-limitation').on('click', function (e) {
        e.preventDefault();
        Ladda.stopAll();
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).prop('disabled', true);
    });
    $('#ab_settings_step_cart_enabled,#ab_woocommerce_enabled,#ab_settings_coupons').on('change', function (e) {
        $(this).val('0');
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).find('option:gt(0)').prop('disabled', true);
    });

    $('#ab_settings_google_client_id,#ab_settings_google_client_secret,#ab_settings_google_two_way_sync,#ab_settings_google_limit_events,#ab_settings_google_event_title').on('focus', function () {
        $(this).prop('disabled',true);
        booklyAlert({error: [BooklyL10n.limitations]});
    });
    $('.select_start', $form).on('change', function () {
        var $flexbox = $(this).parents('.bookly-flexbox'),
            $end_select = $('.select_end', $flexbox),
            $start_select = $(this);

        if ($start_select.val()) {
            $flexbox.find('.hide-on-non-working-day').show();

            var start_time = $start_select.val();

            $('span > option', $end_select).each(function () {
                $(this).unwrap();
            });

            // Hides end time options with value less than in the start time
            $('option', $end_select).each(function () {
                if ($(this).val() <= start_time) {
                    $(this).wrap('<span>').parent().hide();
                }
            });

            if (start_time >= $end_select.val()) {
                $('option:visible:first', $end_select).attr('selected', true);
            }
        } else { // OFF
            $flexbox.find('.hide-on-non-working-day').hide();
        }
    }).each(function () {
        var $row = $(this).parent(),
            $end_select = $('.select_end', $row);

        $(this).data('default_value', $(this).val());
        $end_select.data('default_value', $end_select.val());

        // Hides end select for "OFF" days
        if (!$(this).val()) {
            $end_select.hide();
            $('span', $row).hide();
        }
    }).trigger('change');

    // Reset.
    $('#ab-hours-reset', $form).on('click', function ( e ) {
        e.preventDefault();
        $('.select_start', $form).each(function () {
            $(this).val($(this).data('default_value'));
            $(this).trigger('click');
        });

        $('.select_end', $form).each(function () {
            $(this).val($(this).data('default_value'));
        });

        $('.select_start', $form).trigger('change');
    });

    // Customers Tab
    var $default_country      = $('#ab_settings_phone_default_country'),
        $default_country_code = $('#ab_sms_default_country_code');

    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $default_country.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $default_country.val($default_country.data('country'));

    $default_country.on('change', function () {
        $default_country_code.val($default_country.find('option:selected').data('code'));
    });

    // Company Tab
    $('#ab-settings-company-reset').on('click', function () {
        var $div = $('#bookly-js-logo .bookly-js-image'),
            $input = $('[name=ab_settings_company_logo_attachment_id]');
        $div.attr('style', $div.data('style'));
        $input.val($input.data('default'));
    });

    // Cart Tab
    $('#ab_cart_show_columns').sortable({
        axis : 'y',
        handle : '.bookly-js-handle'
    });

    // Payment Tab
    $('#ab_paypal_type').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-paypal-ec').toggle(this.value != 'disabled');
    }).change();

    $('#ab_authorizenet_type').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.authorizenet').toggle(this.value != 'disabled');
    }).change();

    $('#ab_stripe').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-stripe').toggle(this.value == 1);
    }).change();

    $('#ab_2checkout').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-2checkout').toggle(this.value != 'disabled');
    }).change();

    $('#ab_payulatam').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-payulatam').toggle(this.value != 'disabled');
    }).change();

    $('#ab_payson').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-payson').toggle(this.value != 'disabled');
    }).change();

    $('#ab_mollie').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.ab-mollie').toggle(this.value != 'disabled');
    }).change();

    $('#ab-payments-reset').on('click', function (event) {
        setTimeout(function () {
            $('#ab_paypal_type,#ab_authorizenet_type,#ab_stripe,#ab_2checkout,#ab_payulatam,#ab_payson,#ab_mollie').change();
        }, 50);
    });

    $('#ab-customer-reset').on('click', function (event) {
        $default_country.val($default_country.data('country'));
    });

    if ($final_step_url.val()) { $final_step_url_mode.val(1); }
    $final_step_url_mode.change(function () {
        $(this).val(0);
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).find('option:gt(0)').prop('disabled', true);
        $final_step_url.hide().val('');
    });

    $('li[data-target="#ab_settings_' + BooklyL10n.current_tab + '"]').tab('show');

    $('#bookly-js-logo .bookly-pretty-indicator').on('click', function(){
        var frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection').toJSON(),
                img_src
                ;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                $('[name=ab_settings_company_logo_attachment_id]').val(selection[0].id);
                $('#bookly-js-logo .bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                $('#bookly-js-logo .bookly-thumb-delete').show();
                $(this).hide();
            }
        });

        frame.open();
    });

    $('#bookly-js-logo .bookly-thumb-delete').on('click', function () {
        var $thumb = $(this).parents('.bookly-js-image');
        $thumb.attr('style', '');
        $('[name=ab_settings_company_logo_attachment_id]').val('');
    });
});