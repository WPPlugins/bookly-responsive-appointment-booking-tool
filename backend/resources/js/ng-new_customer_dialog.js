;(function() {

angular.module('newCustomerDialog', []).directive('newCustomerDialog', function() {
  return {
    restrict : 'A',
    replace  : true,
    scope    : {
      callback  : '&newCustomerDialog',
      backdrop  : '@',
      btn_class : '@btnClass'
    },
    templateUrl : ajaxurl + '?action=ab_get_ng_new_customer_dialog_template',
    // The linking function will add behavior to the template.
    link: function(scope, element, attrs) {
      // Init properties.
      var init = function() {
        // Form fields.
        scope.form = {
          name  : '',
          phone : '',
          email : ''
        };
        // Form errors.
        scope.errors = {
          name : {
            required : false
          }
        };
        // Loading indicator.
        scope.loading = false;
      };

      // Run init.
      init();

      // On 'Cancel' button click.
      scope.closeDialog = function() {
        // Close the dialog.
        element.children('#ab_new_customer_dialog').modal('hide');
        // Re-init all properties.
        init();
      };

      /**
       * Send form to server.
       */
      scope.processForm = function() {
        scope.errors  = {};
        scope.loading = true;
        jQuery.ajax({
          url  : ajaxurl,
          type : 'POST',
          data : jQuery.extend({ action : 'ab_save_customer' }, scope.form),
          dataType : 'json',
          success : function ( response ) {
            scope.$apply(function(scope) {
              if (response.status === 'ok') {
                // Send new customer to the parent scope.
                scope.callback({customer : response.customer});
                // Close the dialog.
                scope.closeDialog();
              } else {
                // Set errors.
                jQuery.each(response.errors, function(field, errors) {
                  scope.errors[field] = {};
                  jQuery.each(errors, function(key, error) {
                    scope.errors[field][error] = true;
                  });
                });
              }
              scope.loading = false;
            });
          },
          error : function() {
            scope.$apply(function(scope) {
              scope.loading = false;
            });
          }
        });
      };
    }
  };
});

})();