function booklyAlert(alert) {
    var types = {
        success : 'alert-success',
        error   : 'alert-danger'
    };

    // Check if there are messages in alert.
    var not_empty = false;
    for (var type in alert) {
        if (types.hasOwnProperty(type) && alert[type].length) {
            not_empty = true;
            break;
        }
    }

    if (not_empty) {
        var $container = jQuery('#bookly-alert');
        if ($container.length == 0) {
            $container = jQuery('<div id="bookly-alert" class="bookly-alert"></div>').appendTo('#bookly-tbs');
        }
        for (var type in alert) {
            var class_name;
            if (types.hasOwnProperty(type)) {
                class_name = types[type];
            } else {
                continue;
            }
            alert[type].forEach(function (message) {
                var $alert = jQuery('<div class="alert"><i class="alert-icon"></i><button type="button" class="close" data-dismiss="alert"></button></div>');
                $alert
                    .addClass(class_name)
                    .append('<div class="alert-title">' + message + '</div>')
                    .appendTo($container);

                if (type == 'success') {
                    setTimeout(function() {
                        $alert.remove();
                    }, 5000);
                }
            });
        }
    }
}