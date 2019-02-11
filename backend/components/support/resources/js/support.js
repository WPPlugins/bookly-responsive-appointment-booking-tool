jQuery(function ($) {
    var $modal        = $('#bookly-support-modal'),
        $btnContactUs = $('#bookly-contact-us-btn'),
        $btnFeedback  = $('#bookly-feedback-btn')
    ;

    if ($btnContactUs.data('trigger')) {
        $btnContactUs
            .popover().popover('show')
            .next('.popover')
                .find('.arrow').removeClass().addClass('popover-arrow').end()
                .find('.popover-content button').on('click', function () {
                    $btnContactUs.popover('hide');
                    $.ajax({
                        url  : ajaxurl,
                        type : 'POST',
                        data : { action : 'bookly_dismiss_contact_us_notice', csrf_token : BooklySupportL10n.csrfToken },
                        success : function(response) {
                            $btnContactUs.attr('data-processed', true);
                        }
                    });
                }).end()
            .end()
            .on('click', function () {
                $btnContactUs.popover('hide');
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : { action : 'bookly_contact_us_btn_clicked', csrf_token : BooklySupportL10n.csrfToken }
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
                            data : { action : 'bookly_dismiss_feedback_notice', csrf_token : BooklySupportL10n.csrfToken }
                        });
                    }).end()
                .end()
            .end()
            .on('click', function () {
                $btnFeedback.popover('hide');
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : { action : 'bookly_dismiss_feedback_notice', csrf_token : BooklySupportL10n.csrfToken }
                });
            });
    }

    $('#bookly-support-send').on('click', function () {
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
                    action     : 'bookly_send_support_request',
                    csrf_token : BooklySupportL10n.csrfToken,
                    name       : $name.val(),
                    email      : $email.val(),
                    msg        : $msg.val()
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

    $('#bookly-js-mark-read-all-messages')
        .on('click', function (e) {
            e.preventDefault();
            var $link = $(this),
                ladda = Ladda.create($('#bookly-bell').get(0)),
                $dropdown = $link.closest(".dropdown-menu");

            $dropdown.prev().dropdown('toggle');
            ladda.start();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_mark_read_all_messages',
                    csrf_token: BooklySupportL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('.bookly-js-new-messages-count').remove();
                        $link.closest('li').remove();
                        $('a', $dropdown).each(function () {
                            $(this).html($(this).text());
                        });
                    }
                },
                complete: function () {
                    ladda.stop();
                }
            });
        });

    $('[data-action=feature-request]')
        .on('click', function () {
            if ($(this).data('target')) {
                window.open($(this).data('target'), '_blank');
            } else {
                $('#bookly-feature-requests-modal').modal('show');
            }
        });

    $('.bookly-js-proceed-to-demo')
        .on('click', function () {
            var $modal = $('#bookly-demo-site-info-modal'),
                target = $(this).data('target');

            if ($('#bookly-js-dont-show-again', $modal).prop('checked')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_dismiss_demo_site_description',
                        csrf_token: BooklySupportL10n.csrfToken
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('[data-action=show-demo]').data('target', target);
                        }
                    }
                });
            }
            $modal.modal('hide');
            window.open(target, '_blank');
        });

    $('[data-action=show-demo]')
        .on('click', function () {
            if ($(this).data('target')) {
                window.open($(this).data('target'), '_blank');
            } else {
                $('#bookly-demo-site-info-modal').modal('show');
            }
        });

    $('.bookly-js-proceed-requests')
        .on('click', function () {
            var $modal = $('#bookly-feature-requests-modal');
            if ($('#bookly-js-dont-show-again', $modal).prop('checked')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'bookly_dismiss_feature_requests_description',
                        csrf_token: BooklySupportL10n.csrfToken
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('[data-action=feature-request]').data('target', BooklySupportL10n.featuresRequestUrl);
                        }
                    }
                });
            }
            $modal.modal('hide');
            window.open(BooklySupportL10n.featuresRequestUrl, '_blank');
        });
});