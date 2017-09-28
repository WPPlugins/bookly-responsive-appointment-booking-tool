jQuery(function ($) {
    var $alert = $('#bookly-subscribe-notice');
    $('#bookly-subscribe-btn').on('click', function () {
        $alert.find('.input-group').removeClass('has-error');
        var ladda = Ladda.create(this);
        ladda.start();
        $.post(ajaxurl, {action: 'bookly_subscribe', email: $('#bookly-subscribe-email').val()}, function (response) {
            ladda.stop();
            if (response.success) {
                $alert.alert('close');
                booklyAlert({success : [response.data.message]});
            } else {
                $alert.find('.input-group').addClass('has-error');
                booklyAlert({error : [response.data.message]});
            }
        });
    });
    $alert.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: 'bookly_dismiss_subscribe_notice'}, function () {
            // Indicator for Selenium that request has completed.
            $('.bookly-js-subscribe-notice').remove();
        });
    });
});