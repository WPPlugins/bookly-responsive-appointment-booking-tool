jQuery(function ($) {
    $('body').booklyHelp();
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