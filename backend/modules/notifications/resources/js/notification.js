jQuery(function($) {

    Ladda.bind( 'button[type=submit]' );

    // menu fix for WP 3.8.1
    $('#toplevel_page_ab-system > ul').css('margin-left', '0px');

    /* exclude checkboxes in form */
    var $checkboxes = $('.ab-notifications .panel-title > input:checkbox[id!=_active]');

    $checkboxes.change(function () {
        $(this).parents('.panel-heading').next().collapse(this.checked ? 'show' : 'hide');
        $('#lite_notice').modal('show');
    });

    // filter sender name and email
    var escapeXSS = function (infected) {
        var regexp = /([<|(]("[^"]*"|'[^']*'|[^'">])*[>|)])/gi;
        return infected.replace(regexp, '');
    };
    $('input.ab-sender').on('change', function() {
        var $val = $(this).val();
        $(this).val(escapeXSS($val));
    });

    $('.ab-popover').popover({
        trigger : 'hover'
    });

});