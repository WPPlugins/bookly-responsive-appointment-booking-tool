jQuery(function($) {

    Ladda.bind( 'button[type=submit]' );

    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    /* exclude checkboxes in form */
    var $checkboxes = $('.bookly-js-collapse .panel-title > input:checkbox');

    $checkboxes.change(function () {
        $(this).parents('.panel-heading').next().collapse(this.checked ? 'show' : 'hide');
    });

    $('[data-toggle="popover"]').popover({
        html: true,
        placement: 'top',
        trigger: 'hover',
        template: '<div class="popover bookly-font-xs" style="width: 220px" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
    });

    booklyAlert(BooklyL10n.alert);

    $(':checkbox').on('change', function () {
        if ($(this).prop('checked')) {
            booklyAlert({error: [BooklyL10n.limitations]});
            $(this).prop('checked', false).prop('readonly', true);
        }
    });

    $('.ab-test-email-notifications').on('click',function () {
        booklyAlert({error: [BooklyL10n.limitations]});
    });
});