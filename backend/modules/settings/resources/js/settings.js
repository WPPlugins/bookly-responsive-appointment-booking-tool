jQuery(function ($) {
    var $form                = $('#business-hours'),
        $final_step_url      = $('input[name=bookly_gen_final_step_url]'),
        $final_step_url_mode = $('#bookly_settings_final_step_url_mode'),
        $help_btn            = $('#bookly-help-btn')
        ;

    booklyAlert(BooklyL10n.alert);

    Ladda.bind('button[type=submit]', {timeout: 2000});

    $('.bookly-limitation').on('click', function (e) {
        e.preventDefault();
        Ladda.stopAll();
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).prop('disabled', true);
    });
    $('#bookly_cart_enabled,#bookly_wc_enabled,#bookly_pmt_coupons').on('change', function (e) {
        $(this).val('0');
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).find('option:gt(0)').prop('disabled', true);
    });

    $('#bookly_gc_client_id,#bookly_gc_client_secret,#bookly_gc_two_way_sync,#bookly_gc_limit_events,#bookly_gc_event_title').on('focus', function () {
        $(this).prop('disabled',true);
        booklyAlert({error: [BooklyL10n.limitations]});
    });
    $('.select_start', $form).on('change', function () {
        var $flexbox = $(this).closest('.bookly-flexbox'),
            $end_select = $('.select_end', $flexbox),
            start_time = this.value;

        if (start_time) {
            $flexbox.find('.bookly-hide-on-off').show();

            // Hides end time options with value less than in the start time.
            var frag      = document.createDocumentFragment();
            var old_value = $end_select.val();
            var new_value = null;
            $('option', $end_select).each(function () {
                if (this.value <= start_time) {
                    var span = document.createElement('span');
                    span.style.display = 'none';
                    span.appendChild(this.cloneNode(true));
                    frag.appendChild(span);
                } else {
                    frag.appendChild(this.cloneNode(true));
                    if (new_value === null || old_value == this.value) {
                        new_value = this.value;
                    }
                }
            });
            $end_select.empty().append(frag).val(new_value);
        } else { // OFF
            $flexbox.find('.bookly-hide-on-off').hide();
        }
    }).each(function () {
        $(this).data('default_value', this.value);
    }).trigger('change');

    // Reset.
    $('#bookly-hours-reset', $form).on('click', function () {
        $('.select_start', $form).each(function () {
            $(this).val($(this).data('default_value')).trigger('change');
        });
    });

    // Customers Tab
    var $default_country      = $('#bookly_cst_phone_default_country'),
        $default_country_code = $('#bookly_cst_default_country_code');

    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $default_country.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $default_country.val(BooklyL10n.default_country);

    $default_country.on('change', function () {
        $default_country_code.val($default_country.find('option:selected').data('code'));
    });

    // Company Tab
    $('#bookly-company-reset').on('click', function () {
        var $div = $('#bookly-js-logo .bookly-js-image'),
            $input = $('[name=bookly_co_logo_attachment_id]');
        $div.attr('style', $div.data('style'));
        $input.val($input.data('default'));
    });

    // Cart Tab
    $('#bookly_cart_show_columns').sortable({
        axis : 'y',
        handle : '.bookly-js-handle'
    });

    // Payment Tab
    $('#bookly_pmt_paypal').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-paypal').toggle(this.value != 'disabled');
    }).change();

    $('#bookly_pmt_authorize_net').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.authorize-net').toggle(this.value != 'disabled');
    }).change();

    $('#bookly_pmt_stripe').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-stripe').toggle(this.value == 1);
    }).change();

    $('#bookly_pmt_2checkout').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-2checkout').toggle(this.value != 'disabled');
    }).change();

    $('#bookly_pmt_payu_latam').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-payu_latam').toggle(this.value != 'disabled');
    }).change();

    $('#bookly_pmt_payson').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-payson').toggle(this.value != 'disabled');
    }).change();

    $('#bookly_pmt_mollie').change(function () {
        if (this.value != 'disabled') {
            $(this).val('disabled');
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).find('option:gt(0)').prop('disabled', true);
        }
        $('.bookly-mollie').toggle(this.value != 'disabled');
    }).change();

    $('#bookly-payments-reset').on('click', function (event) {
        setTimeout(function () {
            $('#bookly_pmt_paypal,#bookly_pmt_authorize_net,#bookly_pmt_stripe,#bookly_pmt_2checkout,#bookly_pmt_payu_latam,#bookly_pmt_payson,#bookly_pmt_mollie').change();
        }, 50);
    });

    $('#bookly-customer-reset').on('click', function (event) {
        $default_country.val($default_country.data('country'));
    });

    if ($final_step_url.val()) { $final_step_url_mode.val(1); }
    $final_step_url_mode.change(function () {
        $(this).val(0);
        booklyAlert({error: [BooklyL10n.limitations]});
        $(this).find('option:gt(0)').prop('disabled', true);
        $final_step_url.hide().val('');
    });

    // Change link to Help page according to activated tab.
    var help_link = $help_btn.attr('href');
    $('.bookly-nav li[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        $help_btn.attr('href', help_link + e.target.getAttribute('data-target').substring(1).replace(/_/g, '-'));
    });
    // Activate tab.
    $('li[data-target="#bookly_settings_' + BooklyL10n.current_tab + '"]').tab('show');

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
                $('[name=bookly_co_logo_attachment_id]').val(selection[0].id);
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
        $('[name=bookly_co_logo_attachment_id]').val('');
    });
});