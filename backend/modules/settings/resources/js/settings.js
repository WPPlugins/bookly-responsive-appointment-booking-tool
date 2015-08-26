jQuery(function ($) {
    var $form                = $('#business-hours'),
        $all_tabs            = $('#ab_settings_company, #ab_settings_payments, #ab_settings_hours, #ab_settings_holidays, #ab_settings_purchase_code, #ab_settings_google_calendar, #ab_settings_general, #ab_settings_woocommerce, #ab_settings_customers'),
        $all_forms           = $('#company-form, #payments-form, #hours-form, #holidays-form, #purchase-code-form, #general-form, #google-calendar-form, #woocommerce-form, #customers-form'),
        $final_step_url      = $('input[name=ab_settings_final_step_url]'),
        $final_step_url_mode = $('#ab_settings_final_step_url_mode');

    Ladda.bind( 'button[type=submit]' );

    $('.select_start', $form).on('change', function () {
        var $row = $(this).parent(),
            $end_select = $('.select_end', $row),
            $start_select = $(this);

        if ($start_select.val()) {
            $end_select.show();
            $('span', $row).show();

            var start_time = $start_select.val();

            $('span > option', $end_select).each(function () {
                $(this).unwrap();
            });

            // Hides end time options with value less than in the start time
            $('option', $end_select).each(function () {
                if ($(this).val() <= start_time) {
                    $(this).wrap("<span>").parent().hide();
                }
            });

            if (start_time >= $end_select.val()) {
                $('option:visible:first', $end_select).attr('selected', true);
            }
        } else { // OFF
            $end_select.hide();
            $('span', $row).hide();
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

    // Tabs Onclick Handlers.
    $all_tabs.on('click', function() {
        $('.ab-active').removeClass('ab-active');
        $(this).addClass('ab-active');
    });
    $('#ab_settings_company').on('click', function() {
        $all_forms.addClass('hidden');
        $('#company-form').removeClass('hidden');
    });
    $('#ab_settings_payments').on('click', function() {
        $all_forms.addClass('hidden');
        $('#payments-form').removeClass('hidden');
    });
    $('#ab_settings_hours').on('click', function() {
        $all_forms.addClass('hidden');
        $('#hours-form').removeClass('hidden');
    });
    $('#ab_settings_holidays').on('click', function() {
        $all_forms.addClass('hidden');
        $('#holidays-form').removeClass('hidden');
    });
    $('#ab_settings_purchase_code').on('click', function() {
        $all_forms.addClass('hidden');
        $('#purchase-code-form').removeClass('hidden');
    });
    $('#ab_settings_general').on('click', function() {
        $all_forms.addClass('hidden');
        $('#general-form').removeClass('hidden');
    });
    $('#ab_settings_woocommerce').on('click', function() {
        $all_forms.addClass('hidden');
        $('#woocommerce-form').removeClass('hidden');
    });
    $('#ab_settings_google_calendar').on('click', function() {
        $all_forms.addClass('hidden');
        $('#google-calendar-form').removeClass('hidden');
    });

    // Customers Tab
    var $customers_tab        = $('#ab_settings_customers'),
        $default_country      = $('#ab_settings_phone_default_country'),
        $default_country_code = $('#ab_sms_default_country_code');

    $customers_tab.on('click', function() {
        $all_forms.addClass('hidden');
        $('#customers-form').removeClass('hidden');
    }).on('click.fill', function() {
        $.each($.fn.intlTelInput.getCountryData(), function( index, value ) {
            $default_country.append('<option value="'+value.iso2 + '" data-code="' + value.dialCode + '">'+ value.name + ' +' + value.dialCode + '</option>' );
        });
        $(this).unbind('click.fill');
        $default_country.val( $default_country.data('country') );
    });

    $default_country.on('change', function() {
        $default_country_code.val($default_country.find('option:selected').data('code'));
    });

    if ($customers_tab.hasClass('ab-active')){
        $customers_tab.trigger('click.fill');
    }

    // Company Tab
    $('#ab-delete-logo').on('click', function() {
        $('#ab-show-logo').hide(300).append('<input type="hidden" id="ab-remove-logo" name="ab_remove_logo" value="1" />');
    });
    $('#ab_settings_company_logo').on('change', function() { $('#ab-remove-logo').remove(); });

    $('#ab-settings-company-reset').on('click', function() {
        $('#ab-remove-logo').remove();
        $('#ab-show-logo').show(300);
    });

    $('#ab_settings_google_client_id,#ab_settings_google_client_secret,#ab_settings_google_two_way_sync,#ab_settings_google_limit_events,#ab_settings_google_event_title,#ab_woocommerce,#ab_settings_coupons,#ab_paypal_type,#ab_authorizenet_type,#ab_stripe,#ab_2checkout').on('click', function(){
       $('#lite_notice').modal('show');
    });

    $('.ab-popover').popover({
        trigger : 'hover'
    });

    $('.ab-popover-ext').popover({
        content: function() {
            return $('#' + $(this).data('ext_id')).html();
        },
        html: true
    });

    if ($final_step_url.val()) { $final_step_url_mode.val(1); }
    $final_step_url_mode.change(function(){

        if (this.value == 0){
            $final_step_url.hide().val('');
        }else{
            $final_step_url.show();
        }
    });

});