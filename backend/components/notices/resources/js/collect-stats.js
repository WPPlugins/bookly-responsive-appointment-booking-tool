jQuery(function ($) {
    var $notice = $('#bookly-collect-stats-notice');
    $notice.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: $notice.data('action'), csrf_token : SupportL10n.csrf_token});
    });
    $notice.find('#bookly-enable-collecting-stats-btn').on('click', function () {
        $.post(ajaxurl, {action: 'bookly_enable_collecting_stats', csrf_token : SupportL10n.csrf_token});
    });
});