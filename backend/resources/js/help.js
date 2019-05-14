jQuery(function ($) {
    $('body').booklyHelp();

    /**
     * Loading overlay plugin
     * @param busy
     */
    $.fn.loading = function (busy) {
        var $t = $(this);
        if ($t.length <= 0) return;
        var key = $t.data('bookly-loading-key');
        if (key === undefined) {
            key = Math.random().toString(36).substr(2, 9);
            $t.data('bookly-loading-key', key);
        }
        var $overlay = $('#bookly-js-loading-overlay-' + key);
        if (busy) {
            var zIndex   = $t.css('z-index') === 'auto' ? 2 : parseFloat($t.css('z-index')) + 1,
                $spinner = $('#bookly-js-loading-spin-' + key);
            if ($overlay.length === 0) {
                $spinner = $('<div/>')
                    .css({position: 'absolute'})
                    .attr('id', 'bookly-js-loading-spin-' + key)
                    .html('<i class="fas fa-spin fa-spinner fa-4x"></i>');
                $overlay = $('<div/>')
                    .css({display: 'none', position: 'absolute', background: '#eee'})
                    .attr('id', 'bookly-js-loading-overlay-' + key)
                    .append($spinner);

                $('body').append($overlay);
            }

            $overlay.css({
                opacity: 0.5,
                zIndex : zIndex,
                top    : $t.offset().top,
                left   : $t.offset().left,
                width  : $t.outerWidth(),
                height : $t.outerHeight()
            }).fadeIn();

            var topOverlay = (($t.height() / 2) - 32);
            if (topOverlay < 0) topOverlay = 0;
            $spinner.css({
                top : topOverlay,
                left: (($t.width() / 2) - 32)
            });

        } else {
            $overlay.fadeOut();
        }
    };
});

jQuery.fn.booklyHelp = function() {
    this.find('.help-block').each(function () {
        var $help  = jQuery(this),
            $label = $help.prev('label'),
            $icon  = jQuery('<a href="#" class="dashicons dashicons-editor-help bookly-color-gray bookly-vertical-middle"></a>');

        $label.append($icon);
        $icon.on('click', function(e) {
            e.preventDefault();
            $help.toggle();
        });
        $help.hide();
    });
};