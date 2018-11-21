jQuery(function ($) {
    var $notice = $('#bookly-lite-rebranding-notice');
    $notice.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: $notice.data('action'), csrf_token : SupportL10n.csrf_token});
    });
});