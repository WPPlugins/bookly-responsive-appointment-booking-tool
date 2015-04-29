(function($) {

    // Methods that can be executed via
    // $('.ab-popup-wrapper').ap_popup('method-name');
    var methods = {
      close: function() {
        this.find('.ab-popup').hide();
        this.find('.ab-clear-text').val('');
      },
      closeAll: function() {
        methods.close.apply($('body'));
      },
      open: function() {
        this.find('.ab-popup').show();
        this.find('.ab-clear-text').focus();
      },
      init: function() {
        return this.each(function() {
          var $wrapper = $(this),
              $opener  = $wrapper.find('.ab-popup-trigger'),
              $closer  = $wrapper.find('.ab-popup-close');
          $opener.on('click', function(e) {
            // Keep body.onlick from being executed.
            e.stopPropagation();
            // Close all other popups.
            methods.closeAll();
            // Open the current one.
            methods.open.apply($wrapper);
          });
          $closer.on('click', function(e) {
            e.preventDefault();
            methods.close.apply($wrapper);
          });
        });
      }
    };

    // On body click close all popups.
    $('body')
      .on('click', '.ab-popup', function(e) {
        // Do not close if click was inside popup box.
        e.stopPropagation();
      })
      .on('click', function(e) {
        methods.closeAll();
      });

    // Add plugin to jQuery.
    $.fn.ab_popup = function(method) {
      if (methods[method]) {
        return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
      } else if (typeof method === 'object' || !method) {
        return methods.init.apply(this, arguments);
      } else {
        $.error('Method ' +  method + ' does not exist on jQuery.ap_popup');
      }
    };
})(jQuery);

jQuery(function($) {
  $('.ab-popup-wrapper').ab_popup();
});