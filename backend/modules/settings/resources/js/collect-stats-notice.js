jQuery(function ($) {
    var $alert = $('#bookly-collect-stats-notice');

    $alert
        .on('click', '.bookly-js-disallow-stats', function () {
            $.post(ajaxurl, {action: 'bookly_dismiss_collect_stats_notice', csrf_token: SupportL10n.csrf_token});
            $alert.alert('close');
        })
        .on('click', '.bookly-js-allow-stats', function () {
            $.post(ajaxurl, {action: 'bookly_allow_collect_stats', csrf_token: SupportL10n.csrf_token});
            $alert.alert('close');
        });
});
