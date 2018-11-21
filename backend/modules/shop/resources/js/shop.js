jQuery(function ($) {
    var $sort = $('#bookly-shop-sort'),
        $shop = $('#bookly-shop'),
        $loading = $('#bookly-shop-loading'),
        $template = $('#bookly-shop-template')
    ;
    $('.bookly-js-select').select2({
        width                  : '100%',
        theme                  : 'bootstrap',
        allowClear             : true,
        minimumResultsForSearch: -1
    });
    $sort.on('change', function () {
        $loading.show();
        $shop.hide();
        $.ajax({
            url     : ajaxurl,
            type    : 'GET',
            data    : {
                action    : 'bookly_get_shop_data',
                csrf_token: BooklyL10n.csrf_token,
                sort      : $sort.val()
            },
            dataType: 'json',
            success : function (response) {
                if (response.data.shop.length) {
                    $shop.html('');
                    $.each(response.data.shop, function (id, plugin) {
                        var rating = '';
                        for (var i = 0; i < 5; i++) {
                            if (plugin.rating - i > 0.5) {
                                rating += '<i class="dashicons dashicons-star-filled"></i>';
                            } else if (plugin.rating - i > 0) {
                                rating += '<i class="dashicons dashicons-star-half"></i>';
                            } else {
                                rating += '<i class="dashicons dashicons-star-empty"></i>';
                            }
                        }
                        $shop.append(
                            $template.clone().show().html()
                                .replace(/{{rating}}/g, rating)
                                .replace(/{{(.+?)}}/g, function (match) {
                                    return plugin[match.substring(2, match.length - 2)];
                                })
                        );
                    });

                }
                $shop.show();
                $loading.hide();
            }
        });
    }).trigger('change');
});