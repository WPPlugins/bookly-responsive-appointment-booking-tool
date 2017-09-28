jQuery(function ($) {
    var $modal        = $('#bookly-support-modal'),
        $btnContactUs = $('#bookly-contact-us-btn'),
        $btnFeedback  = $('#bookly-feedback-btn')
    ;

    if ($btnContactUs.data('trigger')) {
        $btnContactUs
            .popover().popover('show')
            .next('.popover')
                .css({right:22+$btnFeedback.outerWidth()+'px',left:'auto'})
                .find('.arrow').removeClass().addClass('popover-arrow').css({right:($btnContactUs.outerWidth()/2)+'px',left:'auto'}).end()
                .find('.popover-content button').on('click', function () {
                    $btnContactUs.popover('hide');
                    $.ajax({
                        url  : ajaxurl,
                        type : 'POST',
                        data : { action : 'bookly_dismiss_contact_us_notice' },
                        success : function(response) {
                            $btnContactUs.attr("data-processed", true);
                        }
                    });
                }).end()
            .end()
            .on('click', function () {
                $btnContactUs.popover('hide');
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : { action : 'bookly_contact_us_btn_clicked' }
                });
            });
    }

    if ($btnFeedback.data('trigger')) {
        $btnFeedback
            .popover().popover('show')
            .next('.popover')
                .css({right:'10px',left:'auto'})
                .find('.arrow').removeClass().addClass('popover-arrow').css({right:($btnFeedback.outerWidth()/2)+'px',left:'auto'}).end()
                .find('.popover-content').css({overflow:'hidden'})
                    .find('button').on('click', function () {
                        $btnFeedback.popover('hide');
                        $.ajax({
                            url  : ajaxurl,
                            type : 'POST',
                            data : { action : 'bookly_dismiss_feedback_notice' }
                        });
                    }).end()
                .end()
            .end()
            .on('click', function () {
                $btnFeedback.popover('hide');
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : { action : 'bookly_dismiss_feedback_notice' }
                });
            });
    }

    $('#bookly-support-send').on('click', function (e) {
        var $name  = $('#bookly-support-name'),
            $email = $('#bookly-support-email'),
            $msg   = $('#bookly-support-msg')
        ;

        // Validation.
        $email.closest('.form-group').toggleClass('has-error', $email.val() == '');
        $msg.closest('.form-group').toggleClass('has-error', $msg.val() == '');

        // Send request.
        if ($modal.find('.has-error').length == 0) {
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action : 'bookly_send_support_request',
                    name   : $name.val(),
                    email  : $email.val(),
                    msg    : $msg.val()
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        $msg.val('');
                        $modal.modal('hide');
                        booklyAlert({success : [response.data.message]});
                    } else {
                        booklyAlert({error : [response.data.message]});
                        if (response.data.invalid_email) {
                            $email.closest('.form-group').addClass('has-error');
                        }
                    }
                },
                complete : function () {
                    ladda.stop();
                }
            });
        }
    });
});