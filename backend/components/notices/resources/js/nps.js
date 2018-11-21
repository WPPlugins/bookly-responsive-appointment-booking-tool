jQuery(function ($) {
    var $alert  = $('#bookly-nps-notice'),
        $quiz   = $('#bookly-nps-quiz'),
        $stars  = $('#bookly-nps-stars'),
        $msg    = $('#bookly-nps-msg'),
        $email  = $('#bookly-nps-email'),
        $form   = $('#bookly-nps-form'),
        $thanks = $('#bookly-nps-thanks')
    ;

    // Init stars.
    $stars.barrating({
        theme: 'bootstrap-stars',
        allowEmpty: false,
        onSelect: function (value, text, event) {
            if (value <= 7) {
                $form.show();
            } else {
                $.post(ajaxurl, {action: 'bookly_nps_send', csrf_token : SupportL10n.csrf_token, rate: value});
                $quiz.hide();
                $form.hide();
                $thanks.show();
            }
        }
    });

    $('#bookly-nps-btn').on('click', function () {
        $alert.find('.form-group').removeClass('has-error');
        if ($msg.val() == '') {
            $msg.closest('.form-group').addClass('has-error');
        } else {
            var ladda = Ladda.create(this);
            ladda.start();
            $.post(
                ajaxurl,
                {
                    action     : 'bookly_nps_send',
                    csrf_token : SupportL10n.csrf_token,
                    rate       : $stars.val(),
                    msg        : $msg.val(),
                    email      : $email.val()
                },
                function (response) {
                    ladda.stop();
                    if (response.success) {
                        $alert.alert('close');
                        booklyAlert({success : [response.data.message]});
                    }
                }
            );
        }
    });

    $alert.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: 'bookly_dismiss_nps_notice', csrf_token : SupportL10n.csrf_token}, function () {
            // Indicator for Selenium that request has completed.
            $('.bookly-js-nps-notice').remove();
        });
    });
});